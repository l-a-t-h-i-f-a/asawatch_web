<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // Akar situs memang mengarahkan ke halaman masuk admin — tidak ada
        // landing page publik di aplikasi ini.
        $response->assertRedirect(route('admin.login'));
    }
}
