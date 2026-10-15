<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AccountDeletionRequestService;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function __construct(
        protected NotificationPreferenceService $preferenceService,
        protected AccountDeletionRequestService $deletionRequestService
    ) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'notificationGroups' => $this->preferenceService->getPreferencesForUser($request->user()),
            'deletionRequest' => $this->deletionRequestService->latestFor($request->user()),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->safe()->except('current_password'));

        if (! $request->user()->isDirty()) {
            return Redirect::route('profile.edit')->with('status', 'profile-unchanged');
        }

        $emailChanged = $request->user()->isDirty('email');

        if ($emailChanged) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        $redirect = Redirect::route('profile.edit')->with('status', 'profile-updated');

        if ($emailChanged) {
            try {
                $request->user()->sendEmailVerificationNotification();
                $redirect->with('email_verification', 'sent');
            } catch (\Throwable $e) {
                Log::error('Failed to send email verification', [
                    'user_id' => $request->user()->id,
                    'error' => $e->getMessage(),
                ]);
                $redirect->with('email_verification', 'failed');
            }
        }

        return $redirect;
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    /**
     * Send a request to the administrators to delete the user's account.
     */
    public function requestDeletion(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'reason' => ['required', 'string', 'min:10', 'max:500'],
            'password' => ['required', 'current_password'],
        ]);

        $result = $this->deletionRequestService->request($request->user(), $request->input('reason'));

        return Redirect::route('profile.edit')
            ->with('status', $result['success'] ? 'deletion-requested' : 'deletion-request-failed')
            ->with('deletion_message', $result['message']);
    }
}
