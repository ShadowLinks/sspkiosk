<?php

namespace Tests\Feature;

use Tests\TestCase;

class KioskUnavailableRouteTest extends TestCase
{
    public function test_unavailable_page_renders_without_kiosk_session(): void
    {
        $this->get(route('kiosk.reset.unavailable'))
            ->assertOk()
            ->assertViewIs('kiosk.reset.unavailable');
    }
}
