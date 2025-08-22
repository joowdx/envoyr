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
        if (! $user->isPendingInvitation()) {
            return redirect('/')->withErrors(['token' => 'Invalid or expired invitation link.']);
        }

        return view('auth.register', [
            'user' => $user,
            'needsDesignation' => is_null($user->designation), // Add this line
        ]);
    }

    public function store(Request $request, User $user)
    {
        // Check if user is pending invitation
        if (! $user->isPendingInvitation()) {
            return redirect('/')->withErrors(['token' => 'Invalid or expired invitation link.']);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ];

        if (is_null($user->designation)) {
            $rules['designation'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $acceptanceData = [
            'name' => $request->name,
            'password' => $request->password,
        ];

        if (is_null($user->designation) && $request->designation) {
            $acceptanceData['designation'] = $request->designation;
        }

        $user->acceptInvitation($acceptanceData);

        Auth::login($user);

        return redirect('/')->with('success', 'Registration completed successfully.');
    }
}
