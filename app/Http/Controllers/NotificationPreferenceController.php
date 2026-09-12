<?php

namespace App\Http\Controllers;

use App\Services\NotificationPreferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function __construct(
        protected NotificationPreferenceService $preferenceService
    ) {}

    public function update(Request $request): RedirectResponse
    {
        $this->preferenceService->updatePreferences(
            $request->user(),
            $request->input('preferences', [])
        );

        return redirect()->route('profile.edit')->with('status', 'notifications-updated');
    }
}
