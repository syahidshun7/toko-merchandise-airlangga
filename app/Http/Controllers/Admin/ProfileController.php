<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile', ['admin' => $request->user()]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email', 'password']);
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $request->user()->update($data);
        $request->session()->regenerate();

        return to_route('admin.profile.edit')->with('status', 'Profil diperbarui.');
    }
}
