@extends('layouts.app')

@section('title', 'Invalid link')

@section('content')
    <section class="state-page card">
        <p class="state-emoji" aria-hidden="true">⚠️</p>
        <h1 class="state-title">This link can’t be opened</h1>
        <p class="state-text">
            The short link <code>/{{ $link['short_code'] ?? '' }}</code> points to a destination
            that isn’t a valid <code>http://</code> or <code>https://</code> URL, so CutURL
            refused to redirect you. This usually means the stored link data was edited by hand.
        </p>
        <div class="state-actions">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to dashboard</a>
            <a href="{{ route('home') }}" class="btn btn-ghost">Create a new link</a>
        </div>
    </section>
@endsection
