@extends('layouts.app')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-white">Users</h1>
            <p class="text-sm text-gray-500 mt-1">Manage team members and access</p>
        </div>
        <a href="#create-user" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-6 rounded-lg transition">
            + Add User
        </a>
    </div>

    <!-- Add User Form -->
    <form id="create-user" method="POST" action="{{ route('users.store') }}" class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 mb-6">
        @csrf
        <h2 class="text-base font-semibold text-white mb-4">Add New User</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Name</label>
                <input name="name" placeholder="Full name" required
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
                <input name="email" type="email" placeholder="email@example.com" required
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Password</label>
                <input name="password" type="password" placeholder="Min 8 characters" required
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Role <span class="text-red-400">*</span></label>
                <select name="role_id" required
                        class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500">
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="mt-4 w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-6 rounded-lg transition">
            Add User
        </button>
    </form>

    <!-- Strategy B: Mobile cards (below md) -->
    <div class="md:hidden space-y-3 mb-4">
        @forelse ($users as $user)
            <div class="rounded-xl border border-white/[0.06] bg-[#141414] p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="min-w-0">
                        <p class="font-medium text-white truncate" title="{{ $user->name }}">{{ $user->name }}</p>
                        <p class="text-sm text-gray-400 truncate" title="{{ $user->email }}">{{ $user->email }}</p>
                    </div>
                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold uppercase shrink-0
                        {{ $user->role?->name === 'admin' ? 'bg-red-500/10 text-red-400 ring-1 ring-red-500/20' :
                           ($user->role?->name === 'accountant' ? 'bg-blue-500/10 text-blue-400 ring-1 ring-blue-500/20' : 'bg-gray-500/10 text-gray-400 ring-1 ring-gray-500/20') }}">
                        {{ $user->role?->name ?? 'Unknown' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono text-gray-500">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('users.edit', $user) }}"
                           class="text-sm text-gray-400 hover:text-white hover:underline transition">Edit</a>
                        @if (auth()->id() !== $user->id)
                            <div x-data="{ open: false }">
                                <button type="button" @click="open = true"
                                        class="text-sm text-red-500/70 hover:text-red-400 hover:underline transition">Delete</button>
                                <div x-show="open" x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                     @keydown.escape.window="open = false">
                                    <div class="w-full max-w-sm rounded-xl border border-white/10 bg-[#141414] p-6 shadow-2xl">
                                        <h3 class="text-base font-semibold text-white mb-2">Delete {{ $user->name }}?</h3>
                                        <p class="text-sm text-gray-400 mb-6">This cannot be undone.</p>
                                        <div class="flex gap-3 justify-end">
                                            <button type="button" @click="open = false"
                                                    class="rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition">Cancel</button>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="rounded-lg bg-red-600 hover:bg-red-700 px-4 py-2 text-sm font-semibold text-white transition">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm py-4 text-center">No users found.</p>
        @endforelse
    </div>

    <!-- Strategy B: Desktop table (md+) -->
    <div class="hidden md:block bg-[#141414] border border-white/[0.06] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 hidden lg:table-cell">Last Login</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition">
                            <td class="px-4 py-3 text-white font-medium max-w-[160px]">
                                <span class="block truncate" title="{{ $user->name }}">{{ $user->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-300 max-w-[200px]">
                                <span class="block truncate" title="{{ $user->email }}">{{ $user->email }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold uppercase
                                    {{ $user->role?->name === 'admin' ? 'bg-red-500/10 text-red-400 ring-1 ring-red-500/20' :
                                       ($user->role?->name === 'accountant' ? 'bg-blue-500/10 text-blue-400 ring-1 ring-blue-500/20' : 'bg-gray-500/10 text-gray-400 ring-1 ring-gray-500/20') }}">
                                    {{ $user->role?->name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-400 hidden lg:table-cell">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-4">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="text-sm text-gray-400 hover:text-white hover:underline transition">Edit</a>
                                    @if (auth()->id() !== $user->id)
                                        <div x-data="{ open: false }">
                                            <button type="button" @click="open = true"
                                                    class="text-sm text-red-500/70 hover:text-red-400 hover:underline transition">Delete</button>
                                            <div x-show="open" x-cloak
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                                 @keydown.escape.window="open = false">
                                                <div class="w-full max-w-sm rounded-xl border border-white/10 bg-[#141414] p-6 shadow-2xl">
                                                    <h3 class="text-base font-semibold text-white mb-2">Delete {{ $user->name }}?</h3>
                                                    <p class="text-sm text-gray-400 mb-6">This cannot be undone.</p>
                                                    <div class="flex gap-3 justify-end">
                                                        <button type="button" @click="open = false"
                                                                class="rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition">Cancel</button>
                                                        <form method="POST" action="{{ route('users.destroy', $user) }}">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                    class="rounded-lg bg-red-600 hover:bg-red-700 px-4 py-2 text-sm font-semibold text-white transition">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
