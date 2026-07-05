@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <section class="page-head">
        <div>
            <h1 class="page-title">Local links</h1>
            <p class="page-subtitle">
                {{ $totalCount }} {{ \Illuminate\Support\Str::plural('link', $totalCount) }} stored in
                <code>storage/app/cuturl/links.json</code>.
            </p>
        </div>

        @if ($totalCount > 0)
            <form method="POST" action="{{ route('links.clear') }}" data-confirm="Delete ALL local links? This cannot be undone.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <x-icon name="trash" :size="16" /> Clear all links
                </button>
            </form>
        @endif
    </section>

    <form method="GET" action="{{ route('dashboard') }}" class="search-bar" role="search">
        <div class="search-field">
            <x-icon name="search" :size="17" class="search-icon" />
            <input
                type="search"
                name="q"
                id="dashboard-search"
                class="field-input"
                placeholder="Search by original URL or short code…"
                value="{{ $query }}"
                aria-label="Search links"
                autocomplete="off"
            >
        </div>
        <button type="submit" class="btn btn-secondary">Search</button>
        @if ($query !== '')
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    @if (count($links) === 0)
        <div class="empty-state card">
            @if ($query !== '')
                <span class="state-icon" aria-hidden="true"><x-icon name="search-x" :size="28" /></span>
                <h2>No matches for “{{ $query }}”</h2>
                <p>Try a different search term, or <a href="{{ route('dashboard') }}">view all links</a>.</p>
            @else
                <span class="state-icon" aria-hidden="true"><x-icon name="link" :size="28" /></span>
                <h2>No links yet</h2>
                <p>Create your first short link from the <a href="{{ route('home') }}">home page</a>.</p>
            @endif
        </div>
    @else
        <div class="table-wrap card" data-searchable>
            <table class="links-table">
                <thead>
                    <tr>
                        <th scope="col">Short link</th>
                        <th scope="col">Destination</th>
                        <th scope="col" class="num">Clicks</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                        <th scope="col" class="actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($links as $link)
                        @php($shortUrl = url('/'.$link['short_code']))
                        @php($expired = !empty($link['expires_at']) && \Illuminate\Support\Carbon::parse($link['expires_at'])->isPast())
                        <tr data-row data-search="{{ strtolower($link['original_url'].' '.$link['short_code']) }}">
                            <td>
                                <div class="cell-short">
                                    <a href="{{ $shortUrl }}" target="_blank" rel="noopener noreferrer" class="short-code">/{{ $link['short_code'] }}</a>
                                    <button
                                        type="button"
                                        class="btn btn-icon btn-copy"
                                        data-copy-value="{{ $shortUrl }}"
                                        title="Copy short URL"
                                        aria-label="Copy short URL for {{ $link['short_code'] }}"
                                    >
                                        <x-icon name="copy" :size="15" class="copy-icon-default" />
                                        <x-icon name="check" :size="15" class="copy-icon-done" />
                                    </button>
                                </div>
                            </td>
                            <td class="cell-dest">
                                <a href="{{ $link['original_url'] }}" target="_blank" rel="noopener noreferrer" class="truncate" title="{{ $link['original_url'] }}">
                                    {{ $link['original_url'] }}
                                </a>
                            </td>
                            <td class="num">{{ $link['click_count'] }}</td>
                            <td>
                                @if ($expired)
                                    <span class="badge badge-expired">Expired</span>
                                @else
                                    <span class="badge badge-active">Active</span>
                                @endif
                                @if (!empty($link['expires_at']))
                                    <div class="cell-expiry" title="Expires">
                                        <x-icon name="clock" :size="12" />
                                        {{ \Illuminate\Support\Carbon::parse($link['expires_at'])->format('M j, Y') }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($link['created_at'])->format('M j, Y') }}</td>
                            <td class="actions-col">
                                <form method="POST" action="{{ route('links.destroy', $link['id']) }}" data-confirm="Delete this link?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-danger-ghost" title="Delete link" aria-label="Delete link {{ $link['short_code'] }}">
                                        <x-icon name="trash" :size="15" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="no-results" data-no-results hidden>No links match your filter.</p>
        </div>
    @endif
@endsection
