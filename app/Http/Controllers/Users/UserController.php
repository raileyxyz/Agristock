<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserManagementService $userManagementService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userManagementService->list(
            search: $request->query('search'),
            role: $request->query('role'),
            status: $request->query('status'),
        );

        $statistics = $this->userManagementService->getStatistics();
        $summary = $this->userManagementService->getSummary();

        return view('users.index', [
            'users' => $users,
            'statistics' => $statistics,
            'summary' => $summary,
            'roles' => UserRole::values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', ['roles' => UserRole::values(), 'permissions' => config('permissions'),]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $result = $this->userManagementService->create($request->validated(), Auth::user());

        if (! $result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('users.index')->with('success', $result['message']);
    }

    public function resendInvite(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $result = $this->userManagementService->resendInvite($user);

        return redirect()->route('users.index')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', ['user' => $user, 'roles' => UserRole::values(),'permissions' => config('permissions'),]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $result = $this->userManagementService->update($user, $request->validated(), Auth::user());

        return redirect()->route('users.index')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->userManagementService->archive($user, Auth::user());

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function rolesPermissions(): View
    {
        $this->authorize('viewAny', User::class);

        $userCounts = $this->userManagementService->getRoleUserCounts();

        return view('users.roles', ['permissions' => config('permissions'), 'userCounts' => $userCounts,]);
    }
}
