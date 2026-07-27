<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Themicly\Shopcrafty\Models\User;

/**
 * Owner-only team management: invite staff/owners, toggle access, and remove
 * members. Guards against locking yourself or the last owner out.
 */
class StaffManager extends Component
{
    public bool $showForm = false;

    public string $name = '';

    public string $email = '';

    public string $role = 'staff';

    /** @var array<int, string> Permissions granted to the new staff member. */
    public array $permissions = [];

    /** Inline permission editing for an existing staff member. */
    public ?int $editingId = null;

    /** @var array<int, string> */
    public array $editPermissions = [];

    /** Authorization travels with the component, not just the route (CUS-08). */
    protected function guard(): void
    {
        abort_unless(Auth::user()?->isOwner() ?? false, 403);
    }

    public function mount(): void
    {
        $this->guard();
    }

    public function create()
    {
        $this->guard();

        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in([User::ROLE_OWNER, User::ROLE_STAFF])],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(array_keys(User::PERMISSIONS))],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            // Owners implicitly hold every permission, so only persist for staff.
            'permissions' => $data['role'] === User::ROLE_STAFF ? array_values($data['permissions']) : [],
            'status' => 'active',
            'password' => Str::random(24),
        ]);

        $this->reset('name', 'email', 'role', 'showForm', 'permissions');
        $this->role = 'staff';
        $this->dispatch('toast', message: 'Team member added — they can set a password via "forgot password".', type: 'success');
    }

    /** Open the inline permission editor for a staff member. */
    public function editPermissions(int $id): void
    {
        $this->guard();

        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->editPermissions = (array) ($user->permissions ?? []);
    }

    public function savePermissions(): void
    {
        $this->guard();

        if (! $this->editingId) {
            return;
        }

        $valid = array_values(array_intersect($this->editPermissions, array_keys(User::PERMISSIONS)));
        User::whereKey($this->editingId)->where('role', User::ROLE_STAFF)->update(['permissions' => $valid]);

        $this->reset('editingId', 'editPermissions');
        $this->dispatch('toast', message: 'Permissions updated', type: 'success');
    }

    public function toggleStatus(int $id): void
    {
        $this->guard();

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            $this->dispatch('toast', message: "You can't change your own access.", type: 'danger');

            return;
        }

        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);
        $this->dispatch('toast', message: 'Access updated', type: 'success');
    }

    public function remove(int $id): void
    {
        $this->guard();

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            $this->dispatch('toast', message: "You can't remove yourself.", type: 'danger');

            return;
        }

        if ($user->isOwner() && User::where('role', User::ROLE_OWNER)->count() <= 1) {
            $this->dispatch('toast', message: 'There must be at least one owner.', type: 'danger');

            return;
        }

        $user->delete();
        $this->dispatch('toast', message: 'Team member removed', type: 'success');
    }

    public function render()
    {
        return View::make('settings::livewire.staff-manager', [
            'members' => User::orderBy('role')->orderBy('name')->get(),
            'permissionOptions' => User::PERMISSIONS,
        ]);
    }
}
