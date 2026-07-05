@extends('layouts.app')

@section('title', 'Not found')

@section('content')
    <section class="state-page card">
        <span class="state-icon" aria-hidden="true"><x-icon name="compass" :size="30" /></span>
        <h1 class="state-title">404 — nothing here</h1>
        <p class="state-text">
            We couldn’t find a short link for that address. It may have been deleted,
            it may never have existed, or the code was mistyped.
        </p>
        <div class="state-actions">
            <a href="{{ route('home') }}" class="btn btn-primary">Shorten a URL</a>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">View all links</a>
        </div>
    </section>
@endsection
