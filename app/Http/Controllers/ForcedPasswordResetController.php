<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
            'force_password_reset' => false,
        ]);

        return redirect('/')->with('status', 'Password updated successfully!');
    }
}
