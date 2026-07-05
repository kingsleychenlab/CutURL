<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_lists_stored_links(): void
    {
        $this->storage()->create([
            'original_url' => 'https://laravel.com',
            'custom_alias' => 'laravel',
        ]);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('laravel');
        $response->assertSee('https://laravel.com');
    }

    public function test_search_filters_links_on_the_server(): void
    {
        $this->storage()->create(['original_url' => 'https://apple.com', 'custom_alias' => 'apple']);
        $this->storage()->create(['original_url' => 'https://banana.com', 'custom_alias' => 'banana']);

        $response = $this->get('/dashboard?q=banana');

        $response->assertOk();
        $response->assertSee('banana');
        $response->assertDontSee('apple.com');
    }

    public function test_delete_link_works(): void
    {
        $link = $this->storage()->create([
            'original_url' => 'https://example.com',
            'custom_alias' => 'delete-me',
        ]);

        $response = $this->delete('/links/'.$link['id']);

        $response->assertRedirect(route('dashboard'));
        $this->assertNull($this->storage()->findByCode('delete-me'));
        $this->assertCount(0, $this->storage()->all());
    }

    public function test_clear_all_links_works(): void
    {
        $this->storage()->create(['original_url' => 'https://a.com', 'custom_alias' => 'a']);
        $this->storage()->create(['original_url' => 'https://b.com', 'custom_alias' => 'b']);

        $response = $this->delete('/links');

        $response->assertRedirect(route('dashboard'));
        $this->assertCount(0, $this->storage()->all());
    }
}
