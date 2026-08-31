<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'min:8', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'terms' => 'accepted',
        ], [
            'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy to create an account.',
        ]);

        unset($validated['terms']);
        $validated['terms_accepted_at'] = now();

        $user = User::create($validated);

        Auth::login($user, true);

        return redirect('/dashboard');
    }
}
