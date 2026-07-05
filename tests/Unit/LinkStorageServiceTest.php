<?php

namespace Tests\Unit;

use Tests\TestCase;

class LinkStorageServiceTest extends TestCase
{
    public function test_storage_file_is_created_automatically_if_missing(): void
    {
        $this->assertFileDoesNotExist($this->storagePath);

        // Any read should transparently create the folder + file.
        $links = $this->storage()->all();

        $this->assertSame([], $links);
        $this->assertFileExists($this->storagePath);
    }

    public function test_generated_codes_are_unique_and_correct_length(): void
    {
        $service = $this->storage();
        $codes = [];

        for ($i = 0; $i < 50; $i++) {
            $code = $service->generateUniqueCode();
            $this->assertSame(6, strlen($code));
            $this->assertNotContains($code, $codes);
            $codes[] = $code;
        }
    }

    public function test_malformed_json_does_not_crash_and_degrades_gracefully(): void
    {
        $this->storage()->ensureStorageExists();
        file_put_contents($this->storagePath, '{ this is : not valid json ]');

        // Should recover to an empty list rather than throwing.
        $this->assertSame([], $this->storage()->all());
    }

    public function test_reserved_codes_are_not_available(): void
    {
        $service = $this->storage();

        $this->assertTrue($service->isReserved('dashboard'));
        $this->assertTrue($service->isReserved('API')); // case-insensitive
        $this->assertFalse($service->aliasAvailable('storage'));
        $this->assertTrue($service->aliasAvailable('totally-free-alias'));
    }

    public function test_invalid_alias_detection(): void
    {
        $service = $this->storage();

        $this->assertTrue($service->isValidAlias('good_alias-123'));
        $this->assertFalse($service->isValidAlias('has spaces'));
        $this->assertFalse($service->isValidAlias('emoji😀'));
        $this->assertFalse($service->isValidAlias('slash/inside'));
    }

    public function test_expiry_detection(): void
    {
        $service = $this->storage();

        $this->assertFalse($service->isExpired(['expires_at' => null]));
        $this->assertFalse($service->isExpired(['expires_at' => now()->addDay()->toIso8601String()]));
        $this->assertTrue($service->isExpired(['expires_at' => now()->subDay()->toIso8601String()]));
    }
}
