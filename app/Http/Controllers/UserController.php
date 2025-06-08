<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Position;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'employee.department', 'employee.position']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $roles = Role::where('is_active', true)->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        
        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|max:20|unique:users',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'address' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'employee_id' => $request->employee_id,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'status' => 'active',
        ]);

        // Assign role
        $user->roles()->attach($request->role_id, [
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
                        ->with('success', 'User berhasil dibuat.');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'employee.department', 'employee.position']);
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $user->load('roles');
        
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'employee_id' => 'required|string|max:20|unique:users,employee_id,' . $user->id,
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $data = $request->only([
            'employee_id', 'username', 'email', 'first_name', 'last_name',
            'phone', 'gender', 'birth_date', 'address', 'status'
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')
                        ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', 'User berhasil dihapus.');
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        if ($user->roles()->where('role_id', $role->id)->exists()) {
            return back()->with('error', 'User sudah memiliki role ini.');
        }

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return back()->with('success', "Role {$role->display_name} berhasil ditambahkan.");
    }

    public function removeRole(User $user, Role $role)
    {
        if ($user->roles()->count() <= 1) {
            return back()->with('error', 'User harus memiliki minimal satu role.');
        }

        $user->roles()->detach($role->id);

        return back()->with('success', "Role {$role->display_name} berhasil dihapus.");
    }
}
