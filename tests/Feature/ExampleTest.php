<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_page_serves_the_storefront_shell(): void
    {
        $this->withoutVite();

        $this
            ->get('/')
            ->assertOk()
            ->assertSee('id="app"', false)
            ->assertSee('RESPAWN');
    }
}
