<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->call(AdminSeeder::class);

            StoreSetting::query()->firstOrCreate(
                ['id' => 1],
                [
                    'store_name' => 'Toko Merchandise Sekolah',
                    'school_name' => 'SMK TI Airlangga Samarinda',
                ],
            );

            if (config('store.seed_demo_catalog')) {
                $this->call(DemoCatalogSeeder::class);
            }
        });
    }
}
