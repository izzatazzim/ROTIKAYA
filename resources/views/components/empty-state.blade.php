@props([
    'title' => 'No data available',
    'message' => 'There is nothing to display right now.',
    'icon' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

<div class="rounded-xl border border-dashed border-white/10 bg-[#141414] px-6 py-8 text-center">
    @if ($icon)
        <div class="mb-3 flex justify-center text-gray-400">
            {!! $icon !!}
        </div>
    @endif
    <h3 class="text-base font-semibold text-white">{{ $title }}</h3>
    <p class="mt-1 text-sm text-gray-400">{{ $message }}</p>
    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="mt-4 inline-flex rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
            {{ $actionLabel }}
        </a>
    @endif
</div>
