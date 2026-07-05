@extends('layouts.app')

@section('title', 'Shorten a URL')

@section('content')
    <section class="hero">
        <span class="hero-badge">Local-first · Open-source</span>
        <h1 class="hero-title">Shorten links on <span class="text-accent">your machine</span>.</h1>
        <p class="hero-subtitle">
            CutURL turns long URLs into short ones and stores everything in a single
            local JSON file. No sign-up, no tracking servers, no database.
        </p>
    </section>

    @if (session('created_link'))
        @php($created = session('created_link'))
        @php($shortUrl = session('short_url'))
        <section class="card result-card" aria-live="polite">
            <div class="result-header">
                <span class="result-check" aria-hidden="true">✓</span>
                <h2 class="result-title">Your short link is ready</h2>
            </div>

            <div class="result-row" data-copy-group>
                <input
                    type="text"
                    class="result-input"
                    id="short-url"
                    value="{{ $shortUrl }}"
                    readonly
                    aria-label="Generated short URL"
                >
                <button type="button" class="btn btn-copy" data-copy-target="#short-url">
                    <span class="copy-label">Copy</span>
                </button>
                <a href="{{ $shortUrl }}" class="btn btn-ghost" target="_blank" rel="noopener noreferrer">Open ↗</a>
            </div>

            <dl class="result-meta">
                <div>
                    <dt>Destination</dt>
                    <dd class="truncate" title="{{ $created['original_url'] }}">{{ $created['original_url'] }}</dd>
                </div>
                <div>
                    <dt>Short code</dt>
                    <dd><code>{{ $created['short_code'] }}</code></dd>
                </div>
                @if (!empty($created['expires_at']))
                    <div>
                        <dt>Expires</dt>
                        <dd>{{ \Illuminate\Support\Carbon::parse($created['expires_at'])->format('M j, Y · g:i A') }}</dd>
                    </div>
                @endif
            </dl>
        </section>
    @endif

    <section class="card form-card">
        <form method="POST" action="{{ route('shorten') }}" class="shorten-form" novalidate>
            @csrf

            <div class="field">
                <label for="original_url" class="field-label">Long URL <span class="req">*</span></label>
                <input
                    type="url"
                    name="original_url"
                    id="original_url"
                    class="field-input {{ $errors->has('original_url') ? 'has-error' : '' }}"
                    placeholder="https://example.com/a/very/long/path?with=params"
                    value="{{ old('original_url') }}"
                    autocomplete="off"
                    autofocus
                    required
                >
                @error('original_url')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field-grid">
                <div class="field">
                    <label for="custom_alias" class="field-label">Custom alias <span class="field-hint">optional</span></label>
                    <div class="alias-input">
                        <span class="alias-prefix">{{ rtrim(url('/'), '/') }}/</span>
                        <input
                            type="text"
                            name="custom_alias"
                            id="custom_alias"
                            class="field-input {{ $errors->has('custom_alias') ? 'has-error' : '' }}"
                            placeholder="my-link"
                            value="{{ old('custom_alias') }}"
                            pattern="[A-Za-z0-9_-]+"
                            autocomplete="off"
                        >
                    </div>
                    @error('custom_alias')
                        <p class="field-error">{{ $message }}</p>
                    @else
                        <p class="field-note">Letters, numbers, hyphens and underscores only.</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="expires_at" class="field-label">Expiration <span class="field-hint">optional</span></label>
                    <input
                        type="datetime-local"
                        name="expires_at"
                        id="expires_at"
                        class="field-input {{ $errors->has('expires_at') ? 'has-error' : '' }}"
                        value="{{ old('expires_at') }}"
                    >
                    @error('expires_at')
                        <p class="field-error">{{ $message }}</p>
                    @else
                        <p class="field-note">Leave blank for a link that never expires.</p>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">Shorten URL</button>
            </div>
        </form>
    </section>

    <p class="storage-note">
        <span aria-hidden="true">🗄️</span>
        CutURL stores links locally in a JSON file. No account, no cloud, no database.
    </p>
@endsection
