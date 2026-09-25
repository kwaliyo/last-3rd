<?php

namespace Tests\Unit;

use App\Services\NightCalculatorService;
use PHPUnit\Framework\TestCase;

class NightCalculatorServiceTest extends TestCase
{
    public function test_it_calculates_night_divisions_accurately(): void
    {
        $service = new NightCalculatorService();
        $result = $service->calculate(
            9.0765,
            7.3986,
            '2026-09-25',
            'Africa/Lagos',
            NightCalculatorService::METHOD_SUNSET_TO_FAJR
        );

        $this->assertArrayHasKey('divisions', $result);
        $this->assertArrayHasKey('1/3', $result['divisions']);
        $this->assertArrayHasKey('2/3', $result['divisions']);
        $this->assertArrayHasKey('3/3', $result['divisions']);
        $this->assertArrayHasKey('timestamps', $result);
        $this->assertArrayHasKey('midnight', $result);
        $this->assertArrayHasKey('status', $result);

        // Verify continuity of thirds: end of 1/3 is start of 2/3, end of 2/3 is start of 3/3
        $firstEnd = $result['divisions']['1/3']['end']->getTimestamp();
        $secondStart = $result['divisions']['2/3']['start']->getTimestamp();
        $secondEnd = $result['divisions']['2/3']['end']->getTimestamp();
        $thirdStart = $result['divisions']['3/3']['start']->getTimestamp();
        $thirdEnd = $result['divisions']['3/3']['end']->getTimestamp();

        $this->assertEquals($firstEnd, $secondStart);
        $this->assertEquals($secondEnd, $thirdStart);
        $this->assertEquals($result['timestamps']['night_end']->getTimestamp(), $thirdEnd);
    }

    public function test_it_supports_sunset_to_sunrise_method(): void
    {
        $service = new NightCalculatorService();
        $result = $service->calculate(
            51.5074,
            -0.1278,
            '2026-09-25',
            'Europe/London',
            NightCalculatorService::METHOD_SUNSET_TO_SUNRISE
        );

        $this->assertEquals(
            $result['timestamps']['sunrise']->getTimestamp(),
            $result['timestamps']['night_end']->getTimestamp()
        );
    }
}
