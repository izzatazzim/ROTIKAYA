@extends('layouts.app')

@section('content')
    <h1 class="rtk-title mb-3">Roles &amp; Access</h1>
    <p class="mb-6 text-sm text-gray-400">
        This page documents the access permissions for each role. To modify role assignments, contact the system administrator.
    </p>

    <div class="space-y-6">
        @foreach ($roles as $roleKey => $roleConfig)
            <section class="rtk-card">
                <header class="mb-4">
                    <h2 class="text-base font-semibold text-white">{{ $roleConfig['label'] ?? ucfirst(str_replace('_', ' ', $roleKey)) }}</h2>
                    <p class="mt-1 text-sm text-gray-400">{{ $roleConfig['description'] ?? 'No description provided.' }}</p>
                </header>

                <div class="rtk-table-wrap">
                    <table class="rtk-table">
                        <thead>
                            <tr>
                                <th class="w-1/3">Permission Key</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($roleConfig['permissions'] ?? []) as $permission)
                                <tr>
                                    <td class="mono text-white">{{ $permission }}</td>
                                    <td class="text-gray-300">{{ $permissionDescriptions[$permission] ?? 'Description missing.' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-gray-400">No permissions documented for this role.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>
@endsection
