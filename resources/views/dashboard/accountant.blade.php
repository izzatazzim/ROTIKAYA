@extends('layouts.app')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3 mb-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-white">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Overview of sales and payment activity</p>
        </div>
        <form method="POST" action="{{ route('reminders.trigger') }}">
            @csrf
            <button type="submit" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-red-700">
                Trigger Reminders
            </button>
        </form>
    </div>
    @include('dashboard._content')
@endsection
