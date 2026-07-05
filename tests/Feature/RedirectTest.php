<?php

namespace Tests\Feature;

use Tests\TestCase;

class RedirectTest extends TestCase
{
    public function test_short_code_redirects_to_original_url(): void
    {
        $link = $this->storage()->create([
            'original_url' => 'https://example.com/target',
            'custom_alias' => 'go',
        ]);

        $response = $this->get('/'.$link['short_code']);

        $response->assertRedirect('https://example.com/target');
    }

    public function test_click_count_increments_after_redirect(): void
    {
        $link = $this->storage()->create([
            'original_url' => 'https://example.com',
            'custom_alias' => 'count',
        ]);

        $this->get('/count');
        $this->get('/count');

        $updated = $this->storage()->findByCode('count');
        $this->assertSame(2, $updated['click_count']);
        $this->assertNotNull($updated['last_clicked_at']);
    }

    public function test_expired_link_does_not_redirect(): void
    {
        $this->storage()->create([
            'original_url' => 'https://example.com',
            'custom_alias' => 'old',
            'expires_at' => now()->subDay()->toIso8601String(),
        ]);

        $response = $this->get('/old');

        $response->assertStatus(410);
        $response->assertSee('This link has expired');

        // Click count must NOT increment for an expired link.
        $this->assertSame(0, $this->storage()->findByCode('old')['click_count']);
    }

    public function test_nonexistent_short_code_returns_404(): void
    {
        $response = $this->get('/does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('404');
    }

    public function test_unsafe_stored_url_is_not_redirected(): void
    {
        // Simulate a hand-edited JSON file containing an unsafe target.
        $this->storage()->save([[
            'id' => 'x',
            'original_url' => 'javascript:alert(1)',
            'short_code' => 'bad',
            'custom_alias' => 'bad',
            'click_count' => 0,
            'expires_at' => null,
            'last_clicked_at' => null,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]]);

        $response = $this->get('/bad');

        $response->assertStatus(422);
        $response->assertSee('can’t be opened', false);
    }
}
