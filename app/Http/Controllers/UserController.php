<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        );

        $statistics = $this->userManagementService->getStatistics();
        $summary = $this->userManagementService->getSummary();

        return view('users.index', [
            'users' => $users,
            'statistics' => $statistics,
            'summary' => $summary,
            'roles' => User::ROLES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => User::ROLES,
            'permissions' => config('permissions'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userManagementService->create($request->validated());

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => User::ROLES,
            'permissions' => config('permissions'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userManagementService->update($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->userManagementService->archive($user);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function rolesPermissions(): View
    {
        $this->authorize('viewAny', User::class);

        $userCounts = User::query()
            ->where('status', 'Active')
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('users.roles', ['permissions' => config('permissions'), 'userCounts' => $userCounts,]);
    }
}
