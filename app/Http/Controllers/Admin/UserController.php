<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // ==========================================
    // SUPERVISORS (PENGAWAS)
    // ==========================================

    public function indexSupervisors(Request $request)
    {
        $search = $request->input('search');
        $query = User::where('role', UserRole::SUPERVISOR);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $supervisors = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('pages.admin.users.supervisors.index', compact('supervisors', 'search'));
    }

    public function createSupervisor()
    {
        return view('pages.admin.users.supervisors.create');
    }

    public function storeSupervisor(StoreUserRequest $request)
    {
        $user = $this->storeUser($request, UserRole::SUPERVISOR);

        return redirect()->route('admin.users.supervisors.index')
            ->with('success', 'Pengawas pertandingan berhasil ditambahkan.');
    }

    public function editSupervisor($id)
    {
        $user = User::where('role', UserRole::SUPERVISOR)->findOrFail($id);
        return view('pages.admin.users.supervisors.edit', compact('user'));
    }

    public function updateSupervisor(UpdateUserRequest $request, $id)
    {
        $user = User::where('role', UserRole::SUPERVISOR)->findOrFail($id);
        $this->updateUser($request, $user);

        return redirect()->route('admin.users.supervisors.index')
            ->with('success', 'Data pengawas pertandingan berhasil diubah.');
    }

    public function destroySupervisor($id)
    {
        $user = User::where('role', UserRole::SUPERVISOR)->findOrFail($id);
        $this->deleteUser($user);

        return redirect()->route('admin.users.supervisors.index')
            ->with('success', 'Pengawas pertandingan berhasil dihapus.');
    }

    // ==========================================
    // TEAM ADMINS (ADMIN TIM)
    // ==========================================

    public function indexTeamAdmins(Request $request)
    {
        $search = $request->input('search');
        $query = User::with('team')->where('role', UserRole::TEAM_ADMIN);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $teamAdmins = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('pages.admin.users.team_admins.index', compact('teamAdmins', 'search'));
    }

    public function createTeamAdmin()
    {
        $teams = Team::where('status', 'active')->orderBy('name')->get();
        return view('pages.admin.users.team_admins.create', compact('teams'));
    }

    public function storeTeamAdmin(StoreUserRequest $request)
    {
        $user = $this->storeUser($request, UserRole::TEAM_ADMIN);

        return redirect()->route('admin.users.team-admins.index')
            ->with('success', 'Admin Tim berhasil ditambahkan.');
    }

    public function editTeamAdmin($id)
    {
        $user = User::where('role', UserRole::TEAM_ADMIN)->findOrFail($id);
        $teams = Team::where('status', 'active')->orderBy('name')->get();
        return view('pages.admin.users.team_admins.edit', compact('user', 'teams'));
    }

    public function updateTeamAdmin(UpdateUserRequest $request, $id)
    {
        $user = User::where('role', UserRole::TEAM_ADMIN)->findOrFail($id);
        $this->updateUser($request, $user);

        return redirect()->route('admin.users.team-admins.index')
            ->with('success', 'Data Admin Tim berhasil diubah.');
    }

    public function destroyTeamAdmin($id)
    {
        $user = User::where('role', UserRole::TEAM_ADMIN)->findOrFail($id);
        $this->deleteUser($user);

        return redirect()->route('admin.users.team-admins.index')
            ->with('success', 'Admin Tim berhasil dihapus.');
    }

    // ==========================================
    // ADMINISTRATORS (ADMIN)
    // ==========================================

    public function indexAdmins(Request $request)
    {
        $search = $request->input('search');
        $query = User::where('role', UserRole::ADMIN);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('name')->paginate(10)->withQueryString();

        return view('pages.admin.users.admins.index', compact('admins', 'search'));
    }

    public function createAdmin()
    {
        return view('pages.admin.users.admins.create');
    }

    public function storeAdmin(StoreUserRequest $request)
    {
        $user = $this->storeUser($request, UserRole::ADMIN);

        return redirect()->route('admin.users.admins.index')
            ->with('success', 'Administrator berhasil ditambahkan.');
    }

    public function editAdmin($id)
    {
        $user = User::where('role', UserRole::ADMIN)->findOrFail($id);
        return view('pages.admin.users.admins.edit', compact('user'));
    }

    public function updateAdmin(UpdateUserRequest $request, $id)
    {
        $user = User::where('role', UserRole::ADMIN)->findOrFail($id);
        $this->updateUser($request, $user);

        return redirect()->route('admin.users.admins.index')
            ->with('success', 'Data administrator berhasil diubah.');
    }

    public function destroyAdmin($id)
    {
        $user = User::where('role', UserRole::ADMIN)->findOrFail($id);
        
        // Prevent deleting own account
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->deleteUser($user);

        return redirect()->route('admin.users.admins.index')
            ->with('success', 'Administrator berhasil dihapus.');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    protected function storeUser($request, UserRole $role)
    {
        return DB::transaction(function () use ($request, $role) {
            $data = $request->validated();
            
            // Hash password
            $data['password'] = Hash::make($data['password']);
            $data['role'] = $role;

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('users', 'public');
                $data['photo'] = $path;
            }

            $user = User::create($data);

            // Write Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Membuat user baru: ' . $user->name . ' (' . $user->role->label() . ')',
                'subject_type' => 'User',
                'subject_id' => $user->id,
                'new_values' => $user->only(['name', 'email', 'username', 'role', 'team_id', 'status', 'phone']),
            ]);

            return $user;
        });
    }

    protected function updateUser($request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $data = $request->validated();
            $oldValues = $user->only(['name', 'email', 'username', 'role', 'team_id', 'status', 'phone', 'photo']);

            // Update password only if provided
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                if ($user->photo) {
                    Storage::disk('public')->delete($user->photo);
                }
                $path = $request->file('photo')->store('users', 'public');
                $data['photo'] = $path;
            }

            $user->update($data);

            // Write Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Mengubah data user: ' . $user->name . ' (' . $user->role->label() . ')',
                'subject_type' => 'User',
                'subject_id' => $user->id,
                'old_values' => $oldValues,
                'new_values' => $user->only(['name', 'email', 'username', 'role', 'team_id', 'status', 'phone', 'photo']),
            ]);
        });
    }

    protected function deleteUser(User $user)
    {
        DB::transaction(function () use ($user) {
            $oldValues = $user->only(['name', 'email', 'username', 'role', 'team_id', 'status']);
            
            // Delete photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $user->delete();

            // Write Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Menghapus user: ' . $user->name . ' (' . $user->role->label() . ')',
                'subject_type' => 'User',
                'subject_id' => $user->id,
                'old_values' => $oldValues,
            ]);
        });
    }
}
