<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShortenLinkRequest;
use App\Services\LinkStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function __construct(protected LinkStorageService $storage) {}

    /**
     * Homepage with the URL shortening form.
     */
    public function home(): View
    {
        return view('home');
    }

    /**
     * Create a new short link from the submitted form.
     */
    public function shorten(ShortenLinkRequest $request): RedirectResponse
    {
        $link = $this->storage->create([
            'original_url' => $request->input('original_url'),
            'custom_alias' => $request->input('custom_alias'),
            'expires_at' => $this->normaliseExpiry($request->input('expires_at')),
        ]);

        // Flash the created link so the homepage can show the result card,
        // and preserve old input isn't needed on success.
        return redirect()
            ->route('home')
            ->with('created_link', $link)
            ->with('short_url', url('/'.$link['short_code']));
    }

    /**
     * Dashboard listing every locally stored link, with optional search.
     */
    public function dashboard(Request $request): View
    {
        $query = $request->query('q');
        $links = $this->storage->search(is_string($query) ? $query : null);

        return view('dashboard', [
            'links' => $links,
            'query' => is_string($query) ? $query : '',
            'totalCount' => count($this->storage->all()),
        ]);
    }

    /**
     * Delete a single link by id.
     */
    public function destroy(string $id): RedirectResponse
    {
        $deleted = $this->storage->delete($id);

        return redirect()
            ->route('dashboard')
            ->with($deleted ? 'status' : 'error', $deleted
                ? 'Link deleted.'
                : 'That link could not be found.');
    }

    /**
     * Clear every locally stored link.
     */
    public function clear(): RedirectResponse
    {
        $this->storage->clear();

        return redirect()
            ->route('dashboard')
            ->with('status', 'All local links have been cleared.');
    }

    /**
     * Convert a datetime-local value ("2026-07-05T14:30") into ISO-8601,
     * or null when no expiry was provided.
     */
    protected function normaliseExpiry(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value)->toIso8601String();
    }
}
