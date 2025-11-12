@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
    <div class="container-fluid">
        <div class="text-center mt-5">
            <div class="error mx-auto" data-text="404">404</div>
            <p class="lead text-gray-800 mb-5">Page Not Found!</p>
            <p class="text-gray-500 mb-0">The page you are looking for does not exist.</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-3">← Back to Dashboard</a>
        </div>
    </div>
@endsection
