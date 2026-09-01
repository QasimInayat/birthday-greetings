<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // List admin users
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('id')
            ->paginate(10);

        return view('users.index', compact('users', 'search'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name'              => trim($validated['name']),
            'email'             => strtolower(trim($validated['email'])),
            'password'          => $validated['password'],
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User "' . $validated['name'] . '" created. They can sign in immediately.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            // Blank leaves the existing password untouched.
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user->name  = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User "' . $user->name . '" updated.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Deleting yourself would end your own session mid-request.
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account. Ask another admin to remove it.');
        }

        // Never leave the portal with no way in - registration is disabled.
        if (User::count() <= 1) {
            return redirect()->route('users.index')
                ->with('error', 'This is the only account. Deleting it would lock everyone out of the portal.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User "' . $name . '" deleted.');
    }
}
