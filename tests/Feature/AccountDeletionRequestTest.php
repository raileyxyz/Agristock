<?php

use App\Enums\DeletionRequestStatus;
use App\Enums\Status;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Notification;

function submitDeletionRequest($test, User $user, array $overrides = [])
{
    return $test->actingAs($user)->post('/profile/deletion-request', array_merge([
        'reason' => 'I no longer work on the farm.',
        'password' => 'password',
    ], $overrides));
}

function makeDeletionRequest(User $user): AccountDeletionRequest
{
    return AccountDeletionRequest::create([
        'user_id' => $user->id,
        'reason' => 'Leaving the company.',
        'status' => DeletionRequestStatus::PENDING,
    ]);
}

it('lets manager and staff send a deletion request and notifies the admins', function (string $role) {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->{$role}()->create();

    submitDeletionRequest($this, $user)
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', 'deletion-requested');

    $request = AccountDeletionRequest::where('user_id', $user->id)->first();

    expect($request)->not->toBeNull()
        ->and($request->status)->toBe(DeletionRequestStatus::PENDING)
        ->and($request->reason)->toBe('I no longer work on the farm.')
        ->and($user->fresh()->status)->toBe(Status::ACTIVE);

    Notification::assertSentTo($admin, AppNotification::class, fn ($n) => $n->type === 'deletion_requested');
})->with(['manager', 'staff']);

it('requires a reason and the current password to send a deletion request', function () {
    $user = User::factory()->staff()->create();

    submitDeletionRequest($this, $user, ['reason' => 'too short'])
        ->assertSessionHasErrors('reason', null, 'userDeletion');

    submitDeletionRequest($this, $user, ['password' => 'wrong-password'])
        ->assertSessionHasErrors('password', null, 'userDeletion');

    expect(AccountDeletionRequest::count())->toBe(0);
});

it('does not allow a second pending request', function () {
    User::factory()->admin()->create();
    $user = User::factory()->staff()->create();

    submitDeletionRequest($this, $user);
    submitDeletionRequest($this, $user)->assertSessionHas('status', 'deletion-request-failed');

    expect(AccountDeletionRequest::where('user_id', $user->id)->count())->toBe(1);
});

it('does not allow an admin to send a deletion request', function () {
    $admin = User::factory()->admin()->create();

    submitDeletionRequest($this, $admin)->assertSessionHas('status', 'deletion-request-failed');

    expect(AccountDeletionRequest::count())->toBe(0);
});

it('lets an unverified user send a deletion request', function () {
    User::factory()->admin()->create();
    $user = User::factory()->staff()->unverified()->create();

    submitDeletionRequest($this, $user)->assertSessionHas('status', 'deletion-requested');

    expect(AccountDeletionRequest::count())->toBe(1);
});

it('lets only an admin open the deletion requests page', function (string $role, int $expected) {
    $actor = User::factory()->{$role}()->create();

    $this->actingAs($actor)->get('/users/deletion-requests')->assertStatus($expected);
})->with([['admin', 200], ['manager', 403], ['staff', 403]]);

it('shows the pending request and its reason to the admin', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->staff()->create(['name' => 'Requesting Person']);
    makeDeletionRequest($user);

    $this->actingAs($admin)->get('/users/deletion-requests')
        ->assertOk()
        ->assertSee('Requesting Person')
        ->assertSee('Leaving the company.');
});

it('marks the account as Deleted when the admin approves', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $user = User::factory()->staff()->create();
    $request = makeDeletionRequest($user);

    $this->actingAs($admin)->post("/users/deletion-requests/{$request->id}/approve")
        ->assertRedirect(route('users.deletion-requests.index'))
        ->assertSessionHas('success');

    expect(User::find($user->id)->status)->toBe(Status::DELETED)
        ->and($request->fresh()->status)->toBe(DeletionRequestStatus::APPROVED)
        ->and($request->fresh()->decided_by)->toEqual($admin->id);

    Notification::assertSentTo($otherAdmin, AppNotification::class, fn ($n) => $n->type === 'user_deleted');
    Notification::assertNotSentTo($admin, AppNotification::class, fn ($n) => $n->type === 'user_deleted');
});

it('blocks a deleted user from logging in and keeps the record', function () {
    $user = User::factory()->staff()->create(['status' => 'Deleted']);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    expect(User::find($user->id))->not->toBeNull();
});

it('logs out a deleted user who still has a session', function () {
    $user = User::factory()->staff()->create(['status' => 'Deleted']);

    $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));
});

it('forbids manager and staff from approving or declining a request', function (string $role) {
    $actor = User::factory()->{$role}()->create();
    $user = User::factory()->staff()->create();
    $request = makeDeletionRequest($user);

    $this->actingAs($actor)->post("/users/deletion-requests/{$request->id}/approve")->assertForbidden();
    $this->actingAs($actor)->post("/users/deletion-requests/{$request->id}/reject")->assertForbidden();

    expect($user->fresh()->status)->toBe(Status::ACTIVE)
        ->and($request->fresh()->status)->toBe(DeletionRequestStatus::PENDING);
})->with(['manager', 'staff']);

it('keeps the account active and notifies the requester when the admin declines', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $user = User::factory()->manager()->create();
    $request = makeDeletionRequest($user);

    $this->actingAs($admin)->post("/users/deletion-requests/{$request->id}/reject", [
        'admin_note' => 'Please talk to me first.',
    ])->assertSessionHas('success');

    expect($user->fresh()->status)->toBe(Status::ACTIVE)
        ->and($request->fresh()->status)->toBe(DeletionRequestStatus::REJECTED)
        ->and($request->fresh()->admin_note)->toBe('Please talk to me first.');

    Notification::assertSentTo($user, AppNotification::class, fn ($n) => $n->type === 'deletion_decided'
        && str_contains($n->body, 'Please talk to me first.'));
});

it('does not review the same request twice', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->staff()->create();
    $request = makeDeletionRequest($user);
    $request->update(['status' => DeletionRequestStatus::APPROVED]);

    $this->actingAs($admin)->post("/users/deletion-requests/{$request->id}/approve")
        ->assertSessionHas('error');

    expect($user->fresh()->status)->toBe(Status::ACTIVE);
});

it('does not let an admin edit or archive a deleted account', function () {
    $admin = User::factory()->admin()->create();
    $deleted = User::factory()->staff()->create(['status' => 'Deleted']);

    $this->actingAs($admin)->put("/users/{$deleted->id}", [
        'name' => $deleted->name,
        'email' => $deleted->email,
        'role' => 'Staff',
        'status' => 'Active',
    ])->assertForbidden();

    $this->actingAs($admin)->delete("/users/{$deleted->id}")->assertForbidden();

    expect($deleted->fresh()->status)->toBe(Status::DELETED);
});

it('rejects Deleted as a status in the edit form', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create();

    $this->actingAs($admin)->put("/users/{$target->id}", [
        'name' => $target->name,
        'email' => $target->email,
        'role' => 'Staff',
        'status' => 'Deleted',
    ])->assertSessionHasErrors('status');

    expect($target->fresh()->status)->toBe(Status::ACTIVE);
});

it('filters the users list by active, archived and deleted', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->staff()->create(['name' => 'Alive Staff']);
    User::factory()->staff()->archived()->create(['name' => 'Shelved Staff']);
    User::factory()->staff()->create(['name' => 'Removed Staff', 'status' => 'Deleted']);

    $this->actingAs($admin)->get('/users?status=deleted')
        ->assertSee('Removed Staff')->assertDontSee('Alive Staff')->assertDontSee('Shelved Staff');

    $this->actingAs($admin)->get('/users?status=archived')
        ->assertSee('Shelved Staff')->assertDontSee('Removed Staff')->assertDontSee('Alive Staff');

    $this->actingAs($admin)->get('/users?status=active')
        ->assertSee('Alive Staff')->assertDontSee('Removed Staff')->assertDontSee('Shelved Staff');
});
