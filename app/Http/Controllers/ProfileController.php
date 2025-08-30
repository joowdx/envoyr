<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showCompleteForm()
    {
        return view ('auth.complete-profile');
    }

    public function storeCompleteForm(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->name = $request->input('name');
        $user->save();

        return redirect()->intended('/');
    }
}
