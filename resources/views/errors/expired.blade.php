@extends('layouts.app')

@section('title', 'Link expired')

@section('content')
    <section class="state-page card">
        <p class="state-emoji" aria-hidden="true">⌛</p>
        <h1 class="state-title">This link has expired</h1>
        <p class="state-text">
            The short link <code>/{{ $link['short_code'] ?? '' }}</code> is no longer active.
            Its owner set an expiration date that has now passed, so it will not redirect.
        </p>
        <div class="state-actions">
            <a href="{{ route('home') }}" class="btn btn-primary">Create a new link</a>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">View dashboard</a>
        </div>
    </section>
@endsection
