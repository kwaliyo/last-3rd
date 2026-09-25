<?php

namespace Tests\Feature;

use App\Livewire\NightTimeComponent;
use App\Services\NightCalculatorService;
use Livewire\Livewire;
use Tests\TestCase;

class NightTimeComponentTest extends TestCase
{
    public function test_home_page_renders_night_time_component_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Last 3rd');
        $response->assertSee('Tahajjud Calculator');
    }

    public function test_night_route_renders_successfully(): void
    {
        $response = $this->get('/night');
        $response->assertStatus(200);
        $response->assertSee('Last 3rd');
    }

    public function test_it_can_switch_city_and_recalculate(): void
    {
        Livewire::test(NightTimeComponent::class)
            ->call('selectCity', 0) // Makkah
            ->assertSet('location.name', 'Makkah, Saudi Arabia')
            ->assertSee('Makkah, Saudi Arabia')
            ->assertSee('Asia/Riyadh');
    }

    public function test_it_can_toggle_calculation_method(): void
    {
        Livewire::test(NightTimeComponent::class)
            ->call('setMethod', NightCalculatorService::METHOD_SUNSET_TO_SUNRISE)
            ->assertSet('method', NightCalculatorService::METHOD_SUNSET_TO_SUNRISE)
            ->assertSee('Sunrise');
    }

    public function test_it_can_toggle_time_format(): void
    {
        Livewire::test(NightTimeComponent::class)
            ->assertSet('timeFormat', '12h')
            ->call('toggleTimeFormat')
            ->assertSet('timeFormat', '24h');
    }
}
