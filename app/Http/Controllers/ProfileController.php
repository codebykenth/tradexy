<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly FileService $fileService
    ) {}

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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $validated = $request->validated();
        $hasNewFile = $request->hasFile('profile_picture');

        // Unset to prevent fill() from putting UploadedFile into the property
        unset($validated['profile_picture']);
        $user->fill($validated);

        if ($hasNewFile) {
            $user->profile_picture = $this->uploadProfilePicture($request);
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload profile picture to Firebase Storage.
     */
    private function uploadProfilePicture(ProfileUpdateRequest $request): ?string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $this->fileService->updateFile(
            $user->profile_picture,
            $request->file('profile_picture'),
            "users/{$user->id}",
            'profile'
        );
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->profile_picture) {
            $this->fileService->deleteFile($user->profile_picture, "users/{$user->id}", 'profile');
        }

        $user->profile_picture = null;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile picture removed successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        $user->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
