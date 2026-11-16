<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Brick\Math\BigInteger;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function count(Session $session): int
    {
        $token = $this->token($session);

        if ($token === null) {
            return 0;
        }

        return (int) CartItem::query()
            ->whereHas('cart', fn (Builder $query) => $this->active($query, $token))
            ->sum('quantity');
    }

    public function summary(Session $session): array
    {
        $cart = $this->find($session);
        $items = $cart?->items()->with([
            'variant.product' => fn ($query) => $query->withTrashed()->with(['category', 'coverImage']),
        ])->orderBy('id')->get() ?? collect();

        $total = BigInteger::zero();

        $lines = $items->map(function (CartItem $item) use (&$total) {
            $variant = $item->variant;
            $product = $variant?->product;
            $active = $product && ! $product->trashed() && $product->is_active && $product->category->is_active;
            $available = $active && $item->quantity > 0 && $product->quantityLimit($variant->stock) >= $item->quantity;
            $price = $active ? $variant->effectivePrice() : null;
            $subtotal = $available ? BigInteger::of($price)->multipliedBy($item->quantity) : null;

            if ($subtotal !== null) {
                $total = $total->plus($subtotal);
            }

            return compact('item', 'variant', 'product', 'active', 'available', 'price', 'subtotal');
        });

        return [
            'lines' => $lines,
            'total' => $total,
            'count' => $items->sum('quantity'),
            'hasUnavailable' => $lines->contains(fn (array $line) => ! $line['available']),
        ];
    }

    public function add(Session $session, int $variantId, int $quantity): void
    {
        $token = DB::transaction(function () use ($session, $variantId, $quantity) {
            $cart = $this->find($session, lock: true) ?? new Cart(['cart_token' => (string) Str::uuid()]);
            $variant = $this->lockAvailableVariant($variantId);
            $item = $cart->exists ? $cart->items()->firstWhere('product_variant_id', $variantId) : null;
            $newQuantity = ($item?->quantity ?? 0) + $quantity;
            $this->validateStock($variant, $newQuantity);
            $this->touch($cart);

            if ($item) {
                $item->update(['quantity' => $newQuantity]);
            } else {
                $cart->items()->create(['product_variant_id' => $variantId, 'quantity' => $newQuantity]);
            }

            return $cart->cart_token;
        }, 3);

        $session->put('cart_token', $token);
    }

    public function update(Session $session, int $itemId, int $quantity): void
    {
        DB::transaction(function () use ($session, $itemId, $quantity) {
            $cart = $this->find($session, lock: true);
            $item = $cart?->items()->find($itemId);
            abort_if($item === null, 404);

            $variant = $this->lockAvailableVariant($item->product_variant_id);
            $this->validateStock($variant, $quantity);
            $item->update(['quantity' => $quantity]);
            $this->touch($cart);
        }, 3);
    }

    public function remove(Session $session, int $itemId): void
    {
        DB::transaction(function () use ($session, $itemId) {
            $cart = $this->find($session, lock: true);
            $item = $cart?->items()->find($itemId);
            abort_if($item === null, 404);

            $item->delete();
            $this->touch($cart);
        }, 3);
    }

    public function clear(Session $session): void
    {
        DB::transaction(function () use ($session) {
            $cart = $this->find($session, lock: true);

            if ($cart) {
                $cart->items()->delete();
                $this->touch($cart);
            }
        }, 3);
    }

    private function token(Session $session): ?string
    {
        $token = $session->get('cart_token');

        return is_string($token) && Str::isUuid($token) ? $token : null;
    }

    private function active(Builder $query, string $token): Builder
    {
        return $query->where('cart_token', $token)->where('expires_at', '>', now());
    }

    private function find(Session $session, bool $lock = false): ?Cart
    {
        $token = $this->token($session);

        if ($token === null) {
            return null;
        }

        $query = $this->active(Cart::query(), $token);

        return ($lock ? $query->lockForUpdate() : $query)->first();
    }

    private function touch(Cart $cart): void
    {
        $now = now();
        $cart->fill([
            'last_activity_at' => $now,
            'expires_at' => $now->copy()->addHours(config('store.cart_lifetime_hours')),
        ])->save();
    }

    private function lockAvailableVariant(int $id): ProductVariant
    {
        $productId = ProductVariant::query()->whereKey($id)->value('product_id');
        $product = $productId ? Product::visibleInStore()->lockForUpdate()->find($productId) : null;
        $variant = $product?->variants()->lockForUpdate()->find($id);

        if ($variant === null) {
            throw ValidationException::withMessages(['variant_id' => 'Varian produk ini sudah tidak tersedia.']);
        }

        return $variant->setRelation('product', $product);
    }

    private function validateStock(ProductVariant $variant, int $quantity): void
    {
        if ($quantity > $variant->product->quantityLimit($variant->stock)) {
            if ($variant->product->is_preorder) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah pre-order melebihi batas.']);
            }

            $message = $variant->stock === 0
                ? 'Stok varian ini habis.'
                : "Stok varian ini tersisa {$variant->stock} unit. Sesuaikan jumlah di keranjang.";

            throw ValidationException::withMessages(['quantity' => $message]);
        }
    }
}
