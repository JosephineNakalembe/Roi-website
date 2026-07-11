<?php

namespace Tests\Unit;

use App\Support\DeliveryAreas;
use PHPUnit\Framework\TestCase;

class DeliveryAreasTest extends TestCase
{
    public function test_all_returns_the_fee_map(): void
    {
        $areas = DeliveryAreas::all();

        $this->assertIsArray($areas);
        $this->assertArrayHasKey('Ntinda', $areas);
        $this->assertSame(6000, $areas['Ntinda']);
    }

    public function test_has_detects_known_and_unknown_areas(): void
    {
        $this->assertTrue(DeliveryAreas::has('Kampala Road'));
        $this->assertFalse(DeliveryAreas::has('Nowhere'));
        $this->assertFalse(DeliveryAreas::has(null));
    }

    public function test_fee_returns_amount_or_null(): void
    {
        $this->assertSame(3500, DeliveryAreas::fee('Kampala Road'));
        $this->assertNull(DeliveryAreas::fee('Nowhere'));
        $this->assertNull(DeliveryAreas::fee(null));
    }
}
