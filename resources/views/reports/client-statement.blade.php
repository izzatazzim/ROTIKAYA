@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-semibold tracking-tight text-white mb-6">Customer Statement</h1>

    <div class="bg-[#141414] border border-white/[0.06] rounded-xl p-6 max-w-4xl">
        <h2 class="text-base font-semibold text-white mb-4">Export Customer Statement (PDF)</h2>

        <form method="POST" action="{{ route('reports.client-statement.export') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Customer</label>
                <select name="client_id" required
                        class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
                    <option value="">Select customer...</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-gray-400">Choose the customer whose transaction history you want to export.</p>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date') }}" required
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">End Date</label>
                <input type="date" name="end_date" value="{{ old('end_date') }}" required
                       class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-red-500">
            </div>

            <div class="md:col-span-3">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 px-6 rounded-lg transition">
                    Export Customer Statement (PDF)
                </button>
                <a href="{{ route('reports.index') }}" class="ml-3 text-sm text-gray-300 hover:text-white transition">Back to Reports</a>
            </div>
        </form>
    </div>
@endsection
