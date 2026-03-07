<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function show(): View
    {
        return view('profile.show', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->fill($request->validated());

        if ($request->has('profile_picture')) {
            $user->profile_picture = $this->uploadProfilePicture($request);
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload profile picture to FreeImage.host.
     */
    private function uploadProfilePicture(ProfileUpdateRequest $request): ?string
    {
        try {
            $base64 = base64_encode(
                file_get_contents($request->file('profile_picture')->path())
            );

            $response = Http::asForm()->post('https://freeimage.host/api/1/upload', [
                'key' => config('services.freeimg.key'),
                'source' => $base64,
                'format' => 'json',
            ]);

            return $response->json('image.display_url');
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Change the user's password.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Password changed successfully.');
    }

    /**
     * Remove the user's profile picture.
     */
    public function removeProfilePicture(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $user->profile_picture = null;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile picture removed successfully.');
    }
}
