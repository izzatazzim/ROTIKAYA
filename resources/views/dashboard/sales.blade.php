@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-white">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Overview of sales and payment activity</p>
    </div>
    @include('dashboard._content')
@endsection
