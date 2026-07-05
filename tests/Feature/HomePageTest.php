<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_homepage_loads(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Shorten URL');
        $response->assertSee('No account, no cloud, no database');
    }

    public function test_dashboard_loads_with_empty_state(): void
    {
        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('No links yet');
    }
}
