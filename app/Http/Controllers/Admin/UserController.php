<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('resident');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('resident', function ($r) use ($search) {
                        $r->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('category')) {
            $query->whereHas('resident', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        $users = $query
            ->orderByRaw("CASE role WHEN 'admin' THEN 0 WHEN 'personnel' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|unique:users,username',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,personnel,resident',
        ]);

        $data = [
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ];

        $pin = null;
        if ($validated['role'] === 'resident') {
            $credentialService = app(CredentialService::class);
            $data['tracking_number'] = $credentialService->generateTrackingNumber();
            $data['pin'] = Hash::make($pin = $credentialService->generatePin());
            $data['password'] = $data['pin'];
        }

        User::create($data)->assignRole($validated['role']);

        $return = request('return', 'users');
        $route = match ($return) {
            'accounts' => 'admin.accounts.index',
            'staff' => 'admin.staff.index',
            default => 'admin.users.index',
        };

        $message = 'User created successfully.';
        if ($pin !== null) {
            $message .= " Tracking Number: {$data['tracking_number']} - PIN: {$pin}";
        }

        return redirect()->route($route)->with('success', $message);
    }

    public function show(User $user)
    {
        $user->load('resident.idVerifications', 'resident.documentRequests.documentType');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email,'.$user->id,
            'username' => 'nullable|string|unique:users,username,'.$user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,personnel,resident',
            'is_active' => 'boolean',
        ]);

        $data = [
            'email' => $validated['email'],
            'username' => $validated['username'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? false,
        ];

        if ($validated['role'] === 'resident') {
            $credentialService = app(CredentialService::class);
            if (empty($user->tracking_number)) {
                $data['tracking_number'] = $credentialService->generateTrackingNumber();
            }
            if (empty($user->pin)) {
                $data['pin'] = Hash::make($credentialService->generatePin());
            }
        }

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles($validated['role']);

        $return = request('return', 'users');
        $route = match ($return) {
            'accounts' => 'admin.accounts.index',
            'staff' => 'admin.staff.index',
            default => 'admin.users.index',
        };

        return redirect()->route($route)->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        $return = request('return', 'users');
        $route = match ($return) {
            'accounts' => 'admin.accounts.index',
            'staff' => 'admin.staff.index',
            default => 'admin.users.index',
        };

        return redirect()->route($route)->with('success', 'User deleted successfully.');
    }

    public function accounts()
    {
        $users = User::with('resident')
            ->where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.accounts.index', compact('users'));
    }

    public function staff()
    {
        $users = User::with('resident')
            ->where('role', 'personnel')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.staff.index', compact('users'));
    }

    public function export(Request $request)
    {
        $query = User::with('resident');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('resident', function ($r) use ($search) {
                        $r->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('category')) {
            $query->whereHas('resident', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        return Excel::download(new UsersExport($users), 'users_export_'.now()->format('Y-m-d').'.xlsx');
    }
}
