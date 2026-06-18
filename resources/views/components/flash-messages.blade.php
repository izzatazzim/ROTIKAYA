@php
    $flashMap = [
        'success' => [
            'border' => 'border-emerald-500/30',
            'bg' => 'bg-emerald-950/40',
            'text' => 'text-emerald-200',
            'icon' => 'M5 13l4 4L19 7',
        ],
        'error' => [
            'border' => 'border-red-500/30',
            'bg' => 'bg-red-950/40',
            'text' => 'text-red-200',
            'icon' => 'M12 8v4m0 4h.01M4.93 19h14.14A2 2 0 0020.8 16L13.73 3.88a2 2 0 00-3.46 0L3.2 16a2 2 0 001.73 3z',
        ],
        'warning' => [
            'border' => 'border-amber-500/30',
            'bg' => 'bg-amber-950/40',
            'text' => 'text-amber-200',
            'icon' => 'M12 8v4m0 4h.01M4.93 19h14.14A2 2 0 0020.8 16L13.73 3.88a2 2 0 00-3.46 0L3.2 16a2 2 0 001.73 3z',
        ],
        'info' => [
            'border' => 'border-blue-500/30',
            'bg' => 'bg-blue-950/40',
            'text' => 'text-blue-200',
            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];
@endphp

@foreach ($flashMap as $key => $styles)
    @if (session()->has($key))
        <div
            x-data="{show: true}"
            x-init="setTimeout(() => show = false, 5000)"
            x-show="show"
            x-transition
            class="mb-6 rounded-xl border px-4 py-3 {{ $styles['border'] }} {{ $styles['bg'] }} {{ $styles['text'] }}"
        >
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $styles['icon'] }}"/>
                </svg>
                <div class="flex-1">{{ session($key) }}</div>
                <button type="button" @click="show = false" class="text-current/70 hover:text-current transition">×</button>
            </div>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div
        x-data="{show: true}"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition
        class="mb-6 rounded-xl border border-red-500/30 bg-red-950/40 px-4 py-3 text-red-200"
    >
        <div class="flex items-start gap-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 19h14.14A2 2 0 0020.8 16L13.73 3.88a2 2 0 00-3.46 0L3.2 16a2 2 0 001.73 3z"/>
            </svg>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" @click="show = false" class="text-current/70 hover:text-current transition">×</button>
        </div>
    </div>
@endif
