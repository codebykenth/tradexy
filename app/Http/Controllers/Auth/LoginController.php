<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
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

    public function redirectToProvider(Request $request, string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook']), 404);

        if ($request->has('remember')) {
            session(['social_remember' => true]);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'facebook']), 404);

        $socialiteUser = Socialite::driver($provider)->user();

        // Use avatar URL without embedding access tokens
        $profilePictureUrl = $socialiteUser->getAvatar();

        // Find user by email, then update or create
        $user = User::where('email', $socialiteUser->getEmail())->first();

        if ($user) {
            $user->update([
                'provider_id' => $socialiteUser->getId(),
                'provider' => $provider,
                'profile_picture' => $user->profile_picture ?: $profilePictureUrl,
            ]);
        } else {
            $user = User::create([
                'name' => $socialiteUser->getName() ?: $socialiteUser->getEmail(),
                'email' => $socialiteUser->getEmail(),
                'password' => bcrypt(Str::random(16)),
                'email_verified_at' => now(),
                'profile_picture' => $profilePictureUrl,
                'provider_id' => $socialiteUser->getId(),
                'provider' => $provider,
            ]);
        }

        $remember = session()->pull('social_remember', false);
        Auth::login($user, $remember);
        request()->session()->regenerate();

        return redirect('/dashboard');
    }
}
