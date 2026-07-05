<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShortenLinkTest extends TestCase
{
    public function test_valid_url_can_be_shortened(): void
    {
        $response = $this->post('/shorten', [
            'original_url' => 'https://example.com/some/long/path',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('created_link');

        $links = $this->storage()->all();
        $this->assertCount(1, $links);
        $this->assertSame('https://example.com/some/long/path', $links[0]['original_url']);
        $this->assertSame(6, strlen($links[0]['short_code']));
        $this->assertSame(0, $links[0]['click_count']);
    }

    public function test_invalid_url_is_rejected(): void
    {
        $response = $this->post('/shorten', [
            'original_url' => 'not-a-real-url',
        ]);

        $response->assertSessionHasErrors('original_url');
        $this->assertCount(0, $this->storage()->all());
    }

    public function test_javascript_scheme_url_is_rejected(): void
    {
        $response = $this->post('/shorten', [
            'original_url' => 'javascript:alert(1)',
        ]);

        $response->assertSessionHasErrors('original_url');
        $this->assertCount(0, $this->storage()->all());
    }

    public function test_empty_url_is_rejected(): void
    {
        $response = $this->post('/shorten', ['original_url' => '']);

        $response->assertSessionHasErrors('original_url');
    }

    public function test_custom_alias_is_used_as_short_code(): void
    {
        $response = $this->post('/shorten', [
            'original_url' => 'https://example.com',
            'custom_alias' => 'my-cool-link',
        ]);

        $response->assertSessionHasNoErrors();

        $link = $this->storage()->findByCode('my-cool-link');
        $this->assertNotNull($link);
        $this->assertSame('my-cool-link', $link['custom_alias']);
    }

    public function test_duplicate_custom_alias_is_rejected(): void
    {
        $this->storage()->create([
            'original_url' => 'https://first.example.com',
            'custom_alias' => 'taken',
        ]);

        $response = $this->post('/shorten', [
            'original_url' => 'https://second.example.com',
            'custom_alias' => 'taken',
        ]);

        $response->assertSessionHasErrors('custom_alias');
        $this->assertCount(1, $this->storage()->all());
    }

    public function test_invalid_alias_characters_are_rejected(): void
    {
        $response = $this->post('/shorten', [
            'original_url' => 'https://example.com',
            'custom_alias' => 'has spaces!',
        ]);

        $response->assertSessionHasErrors('custom_alias');
    }

    public function test_reserved_alias_is_rejected(): void
    {
        $response = $this->post('/shorten', [
            'original_url' => 'https://example.com',
            'custom_alias' => 'dashboard',
        ]);

        $response->assertSessionHasErrors('custom_alias');
    }

    public function test_expiration_date_is_stored(): void
    {
        $future = now()->addDays(3)->format('Y-m-d\TH:i');

        $this->post('/shorten', [
            'original_url' => 'https://example.com',
            'expires_at' => $future,
        ]);

        $link = $this->storage()->all()[0];
        $this->assertNotNull($link['expires_at']);
    }
}
