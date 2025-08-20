<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function show(string $token)
    {
        $user = User::where('invitation_token', $token)
            ->where('invitation_accepted_at', null)
            ->first();

        if (! $user || $user->isInvitationExpired()) {
            return redirect('/')->withErrors(['token' => 'Invalid or expired invitation link.']);
        }

        return view('auth.register', [
            'user' => $user,
        ]);
    }

    public function store(Request $request, string $token)
    {
        $user = User::where('invitation_token', $token)
            ->where('invitation_accepted_at', null)
            ->first();

        if (! $user->isInvitationExpired()) {
            return redirect('/')->withErrors(['token' => 'Invalid or expired invitation link.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->acceptInvitation([
            'name' => $request->name,
            'password' => $request->password,
        ]);
        Auth::login($user);

        return redirect('/admin')->with('success', 'Registration completed successfully.');
    }
}
