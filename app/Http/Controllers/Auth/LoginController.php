<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Socialite;
use Str;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember_me');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        $socialiteUser = Socialite::driver($provider)->user();

        // Save profile picture of user
        $profilePictureUrl = match ($provider) {
            'facebook' => $socialiteUser->getAvatar() . '?type=large&access_token=' . $socialiteUser->token,
            'google' => $socialiteUser->getAvatar(),
            default => 'https://via.placeholder.com/150'
        };

        // Find user, if it is not existing create a new user
        $user = User::firstOrCreate(
            ['email' => $socialiteUser->getEmail()],
            [
                'name' => $socialiteUser->getName() ?: $socialiteUser->getEmail(),
                'password' => bcrypt(Str::random(16)), // Generate a random password
                'email_verified_at' => now(),
                'profile_picture' => $profilePictureUrl,
                'provider_id' => $socialiteUser->getId(),
                'provider' => $provider,
            ]
        );

        Auth::login($user);

        return redirect('/dashboard');
    }
}
