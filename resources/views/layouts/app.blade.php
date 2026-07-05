<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CutURL') · Local URL shortener</title>
    <meta name="description" content="CutURL — a local-first, open-source URL shortener that stores links in a JSON file. No account, no cloud, no database.">
    <link rel="preconnect" href="{{ url('/') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✂️</text></svg>">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="brand" aria-label="CutURL home">
                <span class="brand-mark" aria-hidden="true">✂</span>
                <span class="brand-name">Cut<span class="brand-accent">URL</span></span>
            </a>

            <nav class="site-nav" aria-label="Primary">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">Shorten</a>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                <a href="https://github.com" class="nav-link nav-github" rel="noopener noreferrer" target="_blank">
                    <span aria-hidden="true">★</span> GitHub
                </a>
                <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Toggle color theme" title="Toggle theme">
                    <span class="theme-icon" aria-hidden="true">◐</span>
                </button>
            </nav>
        </div>
    </header>

    <main id="main" class="container">
        @if (session('status'))
            <div class="flash flash-success" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="flash flash-error" role="alert">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <p>
                <strong>CutURL</strong> — local-first, open-source URL shortener.
                Links are stored in <code>storage/app/cuturl/links.json</code>.
            </p>
            <p class="footer-muted">No account · No cloud · No database</p>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
