<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DispatchesQueueOrSync;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileDestroyRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\FileUpload;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use DispatchesQueueOrSync;

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
        $user->fill($validated)->save();

        if ($hasNewFile) {
            $this->queueProfilePictureUpload($request, $user);
        }

        $redirect = redirect()->route('profile.show')->with('success', 'Profile updated successfully.');

        if ($hasNewFile) {
            $redirect->with('profile_uploading', true);
        }

        return $redirect;
    }

    /**
     * Save the file to local storage and dispatch the background upload job.
     */
    private function queueProfilePictureUpload(ProfileUpdateRequest $request, User $user): void
    {
        $file = $request->file('profile_picture');
        if (!$file) {
            return;
        }

        // 1. Move the uploaded file to private local storage temporarily
        $tempPath = $file->store('temp', 'local');

        // 2. Queue job when worker exists, otherwise run sync (serverless fallback)
        $this->dispatchJob(new FileUpload(
            tempPath: $tempPath,
            directory: "users/{$user->id}",
            userId: (string) $user->id,
            modelClass: User::class,
            modelId: (string) $user->id,
            field: 'profile_picture',
            oldFileUrl: $user->getOriginal('profile_picture')
        ));
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
            $this->fileService->deleteFile($user->profile_picture);
        }

        $user->profile_picture = null;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile picture removed successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(ProfileDestroyRequest $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::logout();

        if ($user) {
            $user->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }
}
