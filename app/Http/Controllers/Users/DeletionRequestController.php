<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use App\Services\AccountDeletionRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DeletionRequestController extends Controller
{
    public function __construct(
        protected AccountDeletionRequestService $deletionRequestService
    ) {}

    public function index(): View
    {
        $this->authorize('reviewDeletionRequests', User::class);

        return view('users.deletion-requests', [
            'pending' => $this->deletionRequestService->pending(),
            'reviewed' => $this->deletionRequestService->recentlyReviewed(),
        ]);
    }

    public function approve(AccountDeletionRequest $deletionRequest): RedirectResponse
    {
        $this->authorize('reviewDeletionRequests', User::class);

        $result = $this->deletionRequestService->approve($deletionRequest, Auth::user());

        return redirect()->route('users.deletion-requests.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function reject(Request $request, AccountDeletionRequest $deletionRequest): RedirectResponse
    {
        $this->authorize('reviewDeletionRequests', User::class);

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->deletionRequestService->reject($deletionRequest, Auth::user(), $data['admin_note'] ?? null);

        return redirect()->route('users.deletion-requests.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
