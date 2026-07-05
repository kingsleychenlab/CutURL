<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CutURL') · Local URL shortener</title>
    <meta name="description" content="CutURL — a local-first, open-source URL shortener that stores links in a JSON file. No account, no cloud, no database.">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236d8bff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='6' cy='6' r='3'/%3E%3Ccircle cx='6' cy='18' r='3'/%3E%3Cline x1='20' y1='4' x2='8.12' y2='15.88'/%3E%3Cline x1='14.47' y1='14.48' x2='20' y2='20'/%3E%3Cline x1='8.12' y1='8.12' x2='12' y2='12'/%3E%3C/svg%3E">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="brand" aria-label="CutURL home">
                <span class="brand-mark" aria-hidden="true">
                    <x-icon name="scissors" :size="18" />
                </span>
                <span class="brand-name">Cut<span class="brand-accent">URL</span></span>
            </a>

            <nav class="site-nav" aria-label="Primary">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">Shorten</a>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
                <a href="https://github.com" class="nav-link nav-github" rel="noopener noreferrer" target="_blank" aria-label="GitHub repository">
                    <x-icon name="github" :size="16" />
                    <span class="nav-github-text">GitHub</span>
                </a>
                <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Toggle color theme" title="Toggle theme">
                    <span class="theme-icon theme-icon-sun"><x-icon name="sun" :size="17" /></span>
                    <span class="theme-icon theme-icon-moon"><x-icon name="moon" :size="17" /></span>
                </button>
            </nav>
        </div>
    </header>

    <main id="main" class="container">
        @if (session('status'))
            <div class="flash flash-success" role="status">
                <x-icon name="check" :size="16" /> <span>{{ session('status') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flash flash-error" role="alert">
                <x-icon name="alert" :size="16" /> <span>{{ session('error') }}</span>
            </div>
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
