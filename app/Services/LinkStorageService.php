<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * LinkStorageService
 *
 * The single source of truth for CutURL links. Every link is stored in one
 * local JSON file (no database). This service owns all reads and writes to
 * that file and is deliberately the only place that touches it, so the JSON
 * shape stays consistent across the whole application.
 */
class LinkStorageService
{
    /**
     * Absolute path to the JSON storage file.
     */
    protected string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? (string) config('cuturl.storage_path');
    }

    /**
     * Ensure the storage directory and JSON file both exist. Called before
     * every read/write so the app works on a fresh checkout with no setup.
     */
    public function ensureStorageExists(): void
    {
        $directory = dirname($this->path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (! file_exists($this->path)) {
            $this->writeRaw([]);
        }
    }

    /**
     * Return every stored link. If the JSON file is missing, empty or
     * corrupted, we recover gracefully by returning an empty list instead of
     * crashing the whole application.
     */
    public function all(): array
    {
        $this->ensureStorageExists();

        $contents = @file_get_contents($this->path);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            // Malformed JSON — log and degrade gracefully rather than crash.
            logger()->warning('CutURL: links.json is malformed, ignoring its contents.', [
                'path' => $this->path,
            ]);

            return [];
        }

        // Filter out any non-array rows that may have crept in.
        return array_values(array_filter($decoded, 'is_array'));
    }

    /**
     * Persist the full list of links back to disk (pretty-printed, locked).
     */
    public function save(array $links): void
    {
        $this->ensureStorageExists();
        $this->writeRaw(array_values($links));
    }

    /**
     * Create and store a new link. Returns the created link record.
     *
     * @param  array{original_url:string, custom_alias?:string|null, expires_at?:string|null}  $data
     */
    public function create(array $data): array
    {
        $links = $this->all();

        $alias = isset($data['custom_alias']) && $data['custom_alias'] !== ''
            ? $data['custom_alias']
            : null;

        $code = $alias ?? $this->generateUniqueCode();

        $now = Carbon::now()->toIso8601String();

        $link = [
            'id' => (string) Str::uuid(),
            'original_url' => $data['original_url'],
            'short_code' => $code,
            'custom_alias' => $alias,
            'click_count' => 0,
            'expires_at' => $data['expires_at'] ?? null,
            'last_clicked_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $links[] = $link;
        $this->save($links);

        return $link;
    }

    /**
     * Find a single link by its unique id, or null.
     */
    public function findById(string $id): ?array
    {
        foreach ($this->all() as $link) {
            if (($link['id'] ?? null) === $id) {
                return $link;
            }
        }

        return null;
    }

    /**
     * Find a single link by its short code (case-sensitive), or null.
     */
    public function findByCode(string $code): ?array
    {
        foreach ($this->all() as $link) {
            if (($link['short_code'] ?? null) === $code) {
                return $link;
            }
        }

        return null;
    }

    /**
     * A code is available if it is neither reserved nor already in use.
     */
    public function aliasAvailable(string $code): bool
    {
        if ($this->isReserved($code)) {
            return false;
        }

        return $this->findByCode($code) === null;
    }

    /**
     * Whether the given code collides with a reserved application route.
     */
    public function isReserved(string $code): bool
    {
        $reserved = array_map('strtolower', (array) config('cuturl.reserved_codes', []));

        return in_array(strtolower($code), $reserved, true);
    }

    /**
     * Whether a custom alias only uses allowed characters and length.
     */
    public function isValidAlias(string $alias): bool
    {
        $maxLength = (int) config('cuturl.alias_max_length', 64);

        if ($alias === '' || mb_strlen($alias) > $maxLength) {
            return false;
        }

        return (bool) preg_match((string) config('cuturl.alias_pattern'), $alias);
    }

    /**
     * Generate a random short code that is not reserved and not already taken.
     */
    public function generateUniqueCode(): string
    {
        $length = (int) config('cuturl.code_length', 6);
        $alphabet = (string) config('cuturl.code_alphabet');
        $max = mb_strlen($alphabet) - 1;

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, $max)];
            }
        } while ($this->isReserved($code) || $this->findByCode($code) !== null);

        return $code;
    }

    /**
     * Increment the click count for a code and stamp last_clicked_at.
     * Returns the updated link, or null if the code does not exist.
     */
    public function incrementClickCount(string $code): ?array
    {
        $links = $this->all();
        $updated = null;

        foreach ($links as $index => $link) {
            if (($link['short_code'] ?? null) === $code) {
                $now = Carbon::now()->toIso8601String();
                $links[$index]['click_count'] = (int) ($link['click_count'] ?? 0) + 1;
                $links[$index]['last_clicked_at'] = $now;
                $links[$index]['updated_at'] = $now;
                $updated = $links[$index];
                break;
            }
        }

        if ($updated !== null) {
            $this->save($links);
        }

        return $updated;
    }

    /**
     * Delete a link by id. Returns true if a link was removed.
     */
    public function delete(string $id): bool
    {
        $links = $this->all();
        $remaining = array_values(array_filter(
            $links,
            fn ($link) => ($link['id'] ?? null) !== $id
        ));

        if (count($remaining) === count($links)) {
            return false;
        }

        $this->save($remaining);

        return true;
    }

    /**
     * Remove every stored link.
     */
    public function clear(): void
    {
        $this->save([]);
    }

    /**
     * Return all links, newest first, optionally filtered by a search term
     * matched against the original URL or the short code.
     */
    public function search(?string $query = null): array
    {
        $links = $this->all();

        if ($query !== null && trim($query) !== '') {
            $needle = mb_strtolower(trim($query));

            $links = array_filter($links, function ($link) use ($needle) {
                $haystack = mb_strtolower(
                    ($link['original_url'] ?? '').' '.($link['short_code'] ?? '')
                );

                return str_contains($haystack, $needle);
            });
        }

        // Newest first for a friendly dashboard ordering.
        usort($links, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return array_values($links);
    }

    /**
     * Whether a stored link has an expiry in the past.
     */
    public function isExpired(array $link): bool
    {
        if (empty($link['expires_at'])) {
            return false;
        }

        return Carbon::parse($link['expires_at'])->isPast();
    }

    /**
     * Low-level, lock-protected write of the raw links array to disk.
     */
    protected function writeRaw(array $links): void
    {
        $json = json_encode(
            array_values($links),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        file_put_contents($this->path, $json.PHP_EOL, LOCK_EX);
    }
}
