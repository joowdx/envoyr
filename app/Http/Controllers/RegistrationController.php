<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function show(User $user)
    {
        // Check if user is pending invitation
        if (!$user->isPendingInvitation()) {
            return redirect('/')->withErrors(['token' => 'Invalid or expired invitation link.']);
        }

        return view('auth.register', [
            'user' => $user,
        ]);
    }

    public function store(Request $request, User $user)
    {
        // Check if user is pending invitation
        if (!$user->isPendingInvitation()) {
            return redirect('/')->withErrors(['token' => 'Invalid or expired invitation link.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'designation' => 'required|string|max:255',
        ]);

        $user->acceptInvitation([
            'name' => $request->name,
            'password' => $request->password,
            'designation' => $request->designation,
        ]);

        Auth::login($user);

        return redirect('/')->with('success', 'Registration completed successfully.');
    }
}
