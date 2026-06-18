@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight text-white mb-6">Record New Sale</h1>

    <form method="POST" action="{{ route('sales.store') }}" enctype="multipart/form-data"
          class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 max-w-2xl">
        @csrf

        <!-- Customer -->
        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Customer <span class="text-red-400 text-base font-normal">*</span></label>
            <div class="flex items-center gap-3">
                <select name="client_id" required
                        class="flex-1 bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500">
                    <option value="">Select a customer...</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('clients.index') }}"
                   class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    +
                </a>
            </div>
        </div>

        <!-- Amount -->
        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Amount</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-mono">RM</span>
                <input name="amount" type="number" step="0.01" placeholder="0.00" required
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg pl-12 pr-4 py-3 text-white font-mono focus:outline-none focus:border-red-500">
            </div>
        </div>

        <!-- Upload Contract -->
        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Upload Contract</label>
            <div class="relative">
                <input type="file" name="contract" accept=".pdf"
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-red-600 file:text-white file:cursor-pointer focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500">
            </div>
            <p class="mt-1.5 text-xs text-gray-400">Upload the signed agreement (PDF). Optional but recommended for record-keeping.</p>
        </div>

        <!-- Campaign Name -->
        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Campaign Name</label>
            <input name="campaign_name" placeholder="Enter campaign name" required
                   class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-red-500">
        </div>

        <!-- Sales Rep -->
        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Sales Rep <span class="text-red-400 text-base font-normal">*</span></label>
            <select name="salesperson_id" required
                    class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500">
                <option value="">Select a sales rep...</option>
                @foreach ($salespeople as $salesperson)
                    <option value="{{ $salesperson->id }}">{{ $salesperson->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Date Range -->
        <div class="mb-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Start Date</label>
                <input name="start_date" type="date"
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-red-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">End Date</label>
                <input name="end_date" type="date"
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-red-500">
            </div>
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Status</label>
            <select name="status" required
                    class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-red-500">
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg transition">
                Record Sale
            </button>
            <a href="{{ route('invoices.index') }}" class="rounded-lg border border-white/10 bg-white/5 px-8 py-3 text-sm font-semibold text-gray-300 hover:text-white transition">
                Cancel
            </a>
        </div>
    </form>
@endsection
