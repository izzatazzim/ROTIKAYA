@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="rtk-title">Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Configure system preferences</p>
    </div>

    <div class="max-w-4xl space-y-8">
        <section class="settings-section rtk-card">
            <header class="mb-5">
                <h2 class="text-base font-semibold text-white">Billing Defaults</h2>
                <p class="mt-1 text-sm text-gray-400">
                    Configure default payment terms and reminder timing for newly managed invoices.
                </p>
            </header>

            <div class="settings-section-body">
                <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-300">Default payment due <span class="text-red-400">*</span><span class="font-normal text-gray-400"> (days after invoice)</span></label>
                        <input
                            name="default_payment_term_days"
                            type="number"
                            value="{{ old('default_payment_term_days', $settings?->default_payment_term_days ?? 30) }}"
                            class="rtk-input focus:ring-2 focus:ring-red-500/50"
                        >
                        <p class="mt-1.5 text-xs text-gray-400">When you create a new invoice, this is how many days the customer has to pay before it becomes overdue.</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-300">When to send overdue reminders <span class="text-red-400">*</span><span class="font-normal text-gray-400"> (in days)</span></label>
                        <input
                            name="reminder_intervals"
                            value="{{ old('reminder_intervals', implode(',', $settings?->reminder_intervals ?? [15,30,45])) }}"
                            class="rtk-input focus:ring-2 focus:ring-red-500/50"
                        >
                        <p class="mt-1.5 text-xs text-gray-400">We'll send a WhatsApp reminder this many days after an invoice becomes overdue. Example: 15,30,45 means three reminders at 15 days, 30 days, and 45 days late.</p>
                        @error('reminder_intervals')
                            <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-300">Invoice Template</label>
                        <textarea name="invoice_template" rows="5" class="rtk-textarea">{{ old('invoice_template', $settings?->invoice_template) }}</textarea>
                        <p class="mt-1 text-xs text-gray-400">Template body used as the default invoice text for generated documents.</p>
                    </div>

                    <div class="flex justify-end">
                        <button class="rtk-btn" type="submit">Save Settings</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="settings-section rtk-card">
            <header class="mb-5">
                <h2 class="text-base font-semibold text-white">Database Backup</h2>
                <p class="mt-1 text-sm text-gray-400">
                    Run on-demand backups stored in `storage/app/backups`; the system keeps the latest 30 files automatically.
                </p>
            </header>

            <div class="settings-section-body">
                <div class="mb-4 rounded-lg border border-white/[0.06] bg-white/[0.02] p-4 text-sm text-gray-300">
                    @if ($lastBackup)
                        <p><span class="text-gray-400">Last backup:</span> {{ $lastBackup->completed_at?->format('Y-m-d H:i:s') }}</p>
                        <p><span class="text-gray-400">Filename:</span> {{ $lastBackup->filename }}</p>
                        <p><span class="text-gray-400">Size:</span> {{ number_format($lastBackup->file_size) }} bytes</p>
                    @else
                        <p>No successful backup found yet.</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('settings.backup.run') }}" class="mb-6" x-data="{loading:false}" @submit="loading=true">
                    @csrf
                    <div class="flex justify-end">
                        <button type="submit" class="rtk-btn inline-flex items-center gap-2 disabled:cursor-not-allowed disabled:opacity-60" :disabled="loading">
                            <svg x-show="loading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4"></circle>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                            </svg>
                            <span x-text="loading ? 'Running Backup...' : 'Run Backup Now'"></span>
                        </button>
                    </div>
                </form>

                <h3 class="mb-3 text-lg font-semibold text-white">Recent Backups (Last 10)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/[0.06] text-left text-gray-400">
                                <th class="py-2 pr-3">Completed At</th>
                                <th class="py-2 pr-3">Filename</th>
                                <th class="py-2 pr-3">Size</th>
                                <th class="py-2 pr-3">Trigger</th>
                                <th class="py-2 pr-3">Status</th>
                                <th class="py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backups as $backup)
                                <tr class="border-b border-white/[0.04] text-gray-200">
                                    <td class="py-2 pr-3">{{ $backup->completed_at?->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-2 pr-3 font-mono text-xs">{{ $backup->filename }}</td>
                                    <td class="py-2 pr-3">{{ number_format($backup->file_size) }} bytes</td>
                                                    <td class="py-2 pr-3 capitalize">{{ $backup->trigger_type === 'manual' ? 'Manual' : 'Scheduled' }}</td>
                                    <td class="py-2 pr-3">
                                        @if ($backup->status === 'success')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Completed</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20" title="{{ $backup->error_message }}">Failed</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        @if ($backup->status === 'success')
                                            <a href="{{ route('settings.backup.download', $backup) }}" class="text-sm text-gray-400 hover:text-white hover:underline transition">Download</a>
                                        @else
                                            <span class="text-gray-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-gray-400">No backups yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
