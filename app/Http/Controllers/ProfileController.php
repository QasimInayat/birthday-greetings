<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // Your own account
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    // Name and email
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->name  = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Your profile has been updated.');
    }

    // Password change, confirming the current one first
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Enter your current password.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'That is not your current password.'])
                ->with('error', 'Password not changed.');
        }

        $user->password = $validated['password'];
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Your password has been changed.');
    }
}
