<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Guests are redirected to login — this app has no public homepage.
     * (Originally Laravel's stock scaffold test asserting a 200, which this
     * app never returns for a guest; updated to match actual behavior.)
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
