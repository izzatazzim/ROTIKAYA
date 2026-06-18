@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">Customers</h1>
        <p class="text-sm text-gray-500 mt-1">Customer directory and contact information</p>
    </div>

    <!-- Search -->
    <form method="GET" action="{{ route('clients.index') }}" class="mb-6">
        <div class="relative max-w-lg">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input name="search" value="{{ $search ?? '' }}" placeholder="Search customers..."
                   class="w-full bg-[#141414] border border-white/[0.06] rounded-lg pl-10 pr-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500">
        </div>
    </form>

    <!-- Customers List -->
    <div class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 mb-6">
        <ul class="space-y-0">
            @forelse ($clients as $client)
                <li class="flex items-center justify-between gap-3 py-3 border-b border-white/[0.06] last:border-0">
                    <div class="min-w-0 flex-1">
                        <span class="block font-medium text-white truncate" title="{{ strtoupper($client->name) }}">
                            {{ strtoupper($client->name) }}
                        </span>
                        @if($client->company_name)
                            <span class="block text-gray-500 text-sm truncate" title="{{ $client->company_name }}">{{ $client->company_name }}</span>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-1 sm:gap-4 text-sm text-gray-400 shrink-0">
                        @if($client->email)
                            <span class="max-w-[160px] truncate hidden sm:block" title="{{ $client->email }}">{{ $client->email }}</span>
                        @endif
                        @if($client->phone)
                            <span class="font-mono text-xs">{{ $client->phone }}</span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="py-4">
                    <x-empty-state
                        title="No customers found"
                        message="Add your first customer to get started."
                    />
                </li>
            @endforelse
        </ul>
    </div>

    <!-- Pagination -->
    @if(method_exists($clients, 'links'))
        <div class="mb-6">{{ $clients->links() }}</div>
    @endif

    <!-- Add Customer Form -->
    <div id="add-customer" class="bg-[#141414] border border-white/[0.06] rounded-xl p-6">
        <h2 class="text-base font-semibold text-white mb-4">Add New Customer</h2>
        <form method="POST" action="{{ route('clients.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Customer Name <span class="text-red-400">*</span></label>
                    <input name="name" placeholder="Full name" required
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Company Name</label>
                    <input name="company_name" placeholder="Company name"
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
                    <input name="email" type="email" placeholder="email@example.com"
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Phone</label>
                    <input name="phone" placeholder="+60 12 345 6789"
                           class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-red-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Address</label>
                    <textarea name="address" rows="2" placeholder="Full address"
                              class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-red-500 resize-none"></textarea>
                </div>
            </div>
            <button type="submit" class="mt-4 w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg transition">
                Add Customer
            </button>
        </form>
    </div>
@endsection
