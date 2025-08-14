<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcedPasswordResetController extends Controller
{
    public function show()
    {
        return view('auth.forced-password-reset');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'force_password_reset' => false, x,
        ]);

        // Clear any cached user data
        Auth::setUser($user->fresh());

        // Redirect to the dashboard
        return redirect('/')->with('status', 'Password updated successfully! Welcome to your dashboard.');
    }
}
