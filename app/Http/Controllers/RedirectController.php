<?php

namespace App\Http\Controllers;

use App\Services\LinkStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller
{
    public function __construct(protected LinkStorageService $storage) {}

    /**
     * Resolve a short code:
     *  - unknown code        -> clean 404 page
     *  - expired link        -> clean "expired" page (no redirect)
     *  - otherwise           -> increment click count and redirect
     */
    public function __invoke(string $code): RedirectResponse|View|Response
    {
        $link = $this->storage->findByCode($code);

        if ($link === null) {
            // Fall through to Laravel's 404 handling / custom 404 view.
            abort(404);
        }

        if ($this->storage->isExpired($link)) {
            return response()
                ->view('errors.expired', ['link' => $link], Response::HTTP_GONE);
        }

        // Defensive: never redirect to anything other than http(s), even if the
        // JSON file was hand-edited to contain an unsafe target (e.g. javascript:).
        $scheme = strtolower((string) parse_url($link['original_url'] ?? '', PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return response()
                ->view('errors.invalid', ['link' => $link], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->storage->incrementClickCount($code);

        return redirect()->away($link['original_url']);
    }
}
