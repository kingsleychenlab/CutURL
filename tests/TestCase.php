<?php

namespace Tests;

use App\Services\LinkStorageService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Isolated JSON storage file used for the duration of one test.
     */
    protected string $storagePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Point CutURL at a throwaway JSON file so tests never touch real data.
        $this->storagePath = storage_path(
            'framework/testing/cuturl_'.uniqid().'/links.json'
        );

        config(['cuturl.storage_path' => $this->storagePath]);

        // Generous limit so functional tests aren't throttled.
        config(['cuturl.shorten_rate_limit' => 1000]);
    }

    protected function tearDown(): void
    {
        // Clean up the temporary storage directory.
        if (isset($this->storagePath)) {
            $dir = dirname($this->storagePath);
            if (is_dir($dir)) {
                @unlink($this->storagePath);
                @rmdir($dir);
            }
        }

        parent::tearDown();
    }

    /**
     * Convenience accessor for the storage service bound to the test path.
     */
    protected function storage(): LinkStorageService
    {
        return app(LinkStorageService::class);
    }
}
