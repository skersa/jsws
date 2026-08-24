<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'admin');
        })->withCount(['auctions', 'bids']);

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('email', 'like', '%'.$request->q.'%')
                    ->orWhere('phone', 'like', '%'.$request->q.'%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        if ($request->filled('verified')) {
            $request->verified === 'yes'
                ? $query->where('is_verified', true)
                : $query->where('is_verified', false);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'verified' => User::where('is_verified', true)->count(),
            'pending' => User::where('is_verified', false)->count(),
            'sellers' => User::whereHas('roles', fn ($q) => $q->where('name', 'seller'))->count(),
            'buyers' => User::whereHas('roles', fn ($q) => $q->where('name', 'buyer'))->count(),
            'admins' => User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count(),
        ];

        $badge = fn ($name) => match ($name) {
            'admin' => 'pf-badge-warning',
            'seller' => 'pf-badge-cyan',
            default => 'pf-badge-success',
        };

        return Inertia::render('Admin/Users/Index', [
            'stats'   => $stats,
            'roles'   => Role::all()->map(fn ($r) => ['name' => $r->name, 'label' => ucfirst($r->name)])->values(),
            'filters' => [
                'q'        => (string) $request->input('q', ''),
                'role'     => (string) $request->input('role', ''),
                'verified' => (string) $request->input('verified', ''),
            ],
            'users' => [
                'data' => collect($users->items())->map(fn ($u) => [
                    'id'             => $u->id,
                    'name'           => $u->name,
                    'email'          => $u->email,
                    'avatar_url'     => $u->avatar ? Storage::url($u->avatar) : null,
                    'initial'        => mb_substr($u->name, 0, 1),
                    'roles'          => $u->roles->map(fn ($r) => ['label' => ucfirst($r->name), 'badge' => $badge($r->name)])->values(),
                    'auctions_count' => $u->auctions_count,
                    'bids_count'     => $u->bids_count,
                    'is_verified'    => (bool) $u->is_verified,
                    'created_human'  => $u->created_at->format('d M Y'),
                    'is_self'        => $u->id === auth()->id(),
                    'show_url'       => route('admin.users.show', $u),
                    'edit_url'       => route('admin.users.edit', $u),
                    'verify_url'     => route('admin.users.verify', $u),
                    'unverify_url'   => route('admin.users.unverify', $u),
                    'destroy_url'    => route('admin.users.destroy', $u),
                ])->values(),
                'links'     => $users->linkCollection()->toArray(),
                'has_pages' => $users->hasPages(),
                'total'     => $users->total(),
                'from'      => $users->firstItem(),
                'to'        => $users->lastItem(),
            ],
            'create_url' => route('admin.users.create'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles'      => Role::where('name', '!=', 'admin')->get()->map(fn ($r) => ['name' => $r->name, 'label' => ucfirst($r->name)])->values(),
            'store_url'  => route('admin.users.store'),
            'index_url'  => route('admin.users.index'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:30', 'unique:users,username'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'exists:roles,name'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'avatar'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required'     => 'Ad Soyad zorunludur.',
            'email.required'    => 'E-posta zorunludur.',
            'email.unique'      => 'Bu e-posta zaten kullanılıyor.',
            'username.unique'   => 'Bu kullanıcı adı zaten kullanılıyor.',
            'role.required'     => 'Rol seçimi zorunludur.',
            'password.required' => 'Şifre zorunludur.',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->filled('username')
            ? Str::slug($request->username, '_')
            : $this->generateUsername($request->email);
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->is_verified = $request->boolean('is_verified');

        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();
        $user->syncRoles([$request->role]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', $user->name . ' oluşturuldu.');
    }

    private function generateUsername(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '_');
        if ($base === '') {
            $base = 'user';
        }
        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i;
            $i++;
        }

        return $username;
    }

    public function show(User $user)
    {
        $user->load('roles')
            ->loadCount(['auctions', 'bids', 'watchlist', 'purchases', 'sales']);

        $user->load([
            'auctions' => fn ($q) => $q->latest()->take(5)->with('cover'),
            'bids'     => fn ($q) => $q->with('auction')->latest()->take(5),
        ]);

        $roleKey = $user->roles->first()?->name ?? 'user';
        $roleLabel = match ($roleKey) { 'admin' => '👑 Admin', 'seller' => '🏪 Onaylı Satıcı', default => '🛍️ Üye' };

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'username'    => $user->username,
                'avatar_url'  => $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=155eef&color=fff&size=256',
                'role_label'  => $roleLabel,
                'is_verified' => (bool) $user->is_verified,
                'is_self'     => $user->id === auth()->id(),
                'created_at'  => $user->created_at->format('d M Y, H:i'),
                'auctions_count' => $user->auctions_count,
                'bids_count'     => $user->bids_count,
                'watchlist_count' => $user->watchlist_count,
                'edit_url'    => route('admin.users.edit', $user),
                'index_url'   => route('admin.users.index'),
                'verify_url'  => route('admin.users.verify', $user),
                'unverify_url' => route('admin.users.unverify', $user),
                'destroy_url' => route('admin.users.destroy', $user),
                'auctions'    => $user->auctions->map(fn ($a) => [
                    'title'        => $a->title,
                    'cover'        => $a->coverUrl(),
                    'price'        => (float) ($a->current_price ?? 0),
                    'status'       => $a->status,
                    'status_label' => match ($a->status) { 'active' => 'Aktif', 'draft' => 'Taslak', 'ended' => 'Bitti', 'cancelled' => 'İptal', default => $a->status },
                    'created'      => $a->created_at->format('d M Y'),
                ])->values(),
                'bids'        => $user->bids->map(fn ($b) => [
                    'title'      => Str::limit($b->auction->title ?? '—', 55),
                    'amount'     => (float) $b->amount,
                    'time_human' => $b->created_at->diffForHumans(),
                ])->values(),
            ],
        ]);
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $roleKey = $user->roles->first()?->name ?? 'user';
        $roleLabel = match ($roleKey) { 'admin' => '👑 Admin', 'seller' => '🏪 Onaylı Satıcı', default => '🛍️ Üye' };

        return Inertia::render('Admin/Users/Edit', [
            'roles' => Role::all()->map(fn ($r) => ['name' => $r->name, 'label' => ucfirst($r->name)])->values(),
            'user'  => [
                'id'          => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'username'    => $user->username,
                'bio'         => $user->bio,
                'role'        => $roleKey,
                'role_label'  => $roleLabel,
                'is_verified' => (bool) $user->is_verified,
                'avatar_url'  => $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=155eef&color=fff&size=256',
                'auctions_count' => $user->auctions()->count(),
                'bids_count'     => $user->bids()->count(),
                'created_year'   => $user->created_at->format('Y'),
                'update_url'  => route('admin.users.update', $user),
                'show_url'    => route('admin.users.show', $user),
                'index_url'   => route('admin.users.index'),
            ],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'exists:roles,name'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Rules\Password::defaults()];
        }

        $request->validate($rules, [
            'name.required' => 'Ad Soyad zorunludur.',
            'email.unique' => 'Bu e-posta zaten kullanılıyor.',
            'role.required' => 'Rol seçimi zorunludur.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->is_verified = $request->boolean('is_verified');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        if ($request->role) {
            $user->syncRoles([$request->role]);
        }

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Kullanıcı başarıyla güncellendi.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['message' => 'Kendi hesabınızı silemezsiniz.'], 422);
            }
            return back()->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $name = $user->name;
        $user->delete();

        $msg = $name . ' silindi.';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['message' => $msg]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', $msg);
    }

    public function verify(User $user)
    {
        $user->update(['is_verified' => true]);

        return back()->with('success', $user->name.' doğrulandı.');
    }

    public function unverify(User $user)
    {
        $user->update(['is_verified' => false]);

        return back()->with('success', $user->name.' doğrulaması kaldırıldı.');
    }
}
