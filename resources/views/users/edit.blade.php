@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight text-white mb-6">Edit User — {{ $user->name }}</h1>

    <form method="POST" action="{{ route('users.update', $user) }}" class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 max-w-2xl">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            <!-- Name -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Name</label>
                <input name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-red-500">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" required
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-red-500">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Password (leave blank to keep current)</label>
                <input name="password" type="password"
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-red-500">
            </div>

            <!-- Role -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Role <span class="text-red-400">*</span></label>
                <select name="role_id" required
                        class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500">
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected($role->id === $user->role_id)>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Toggle -->
            <div x-data="{ active: true }" class="flex items-center justify-between p-4 bg-white/[0.02] border border-white/10 rounded-lg">
                <span class="text-sm font-semibold text-white">Status</span>
                <label class="inline-flex items-center cursor-pointer">
                    <span class="mr-3 text-sm" :class="active ? 'text-green-400' : 'text-gray-400'" x-text="active ? 'Active' : 'Inactive'"></span>
                    <div class="relative">
                        <input type="hidden" name="status" :value="active ? 'active' : 'inactive'">
                        <div @click="active = !active"
                             class="w-14 h-7 rounded-full transition-colors cursor-pointer"
                             :class="active ? 'bg-red-600' : 'bg-white/10'">
                            <div class="absolute top-1 left-1 w-5 h-5 bg-white rounded-full transition-transform"
                                 :class="active ? 'translate-x-7' : 'translate-x-0'"></div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex gap-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg transition">
                Update User
            </button>
            <a href="{{ route('users.index') }}" class="rounded-lg border border-white/10 bg-white/5 px-8 py-3 text-sm font-semibold text-gray-300 hover:text-white transition">
                Cancel
            </a>
        </div>
    </form>
@endsection
