<?php

namespace App\Livewire;

use App\Services\NightCalculatorService;
use Carbon\Carbon;
use DateTime;
use Livewire\Component;

class NightTimeComponent extends Component
{
    /**
     * Selected location.
     */
    public array $location = [
        'name' => 'Abuja, Nigeria',
        'lat' => 9.0765,
        'lng' => 7.3986,
        'timezone' => 'Africa/Lagos',
    ];

    /**
     * Active calculation method ('sunset_to_fajr' or 'sunset_to_sunrise').
     */
    public string $method = NightCalculatorService::METHOD_SUNSET_TO_FAJR;

    /**
     * Time display format ('12h' or '24h').
     */
    public string $timeFormat = '12h';

    /**
     * Active view tab ('today' or 'week').
     */
    public string $activeTab = 'today';

    /**
     * Selected reference date string ('Y-m-d').
     */
    public string $selectedDate;

    /**
     * City search query or selection.
     */
    public string $searchQuery = '';

    /**
     * Preset curated global cities for quick selection.
     */
    public array $presetCities = [
        ['name' => 'Makkah, Saudi Arabia', 'lat' => 21.3891, 'lng' => 39.8579, 'timezone' => 'Asia/Riyadh'],
        ['name' => 'Madinah, Saudi Arabia', 'lat' => 24.5247, 'lng' => 39.5692, 'timezone' => 'Asia/Riyadh'],
        ['name' => 'Jerusalem, Palestine', 'lat' => 31.7683, 'lng' => 35.2137, 'timezone' => 'Asia/Jerusalem'],
        ['name' => 'Cairo, Egypt', 'lat' => 30.0444, 'lng' => 31.2357, 'timezone' => 'Africa/Cairo'],
        ['name' => 'Istanbul, Turkey', 'lat' => 41.0082, 'lng' => 28.9784, 'timezone' => 'Europe/Istanbul'],
        ['name' => 'Abuja, Nigeria', 'lat' => 9.0765, 'lng' => 7.3986, 'timezone' => 'Africa/Lagos'],
        ['name' => 'Lagos, Nigeria', 'lat' => 6.5244, 'lng' => 3.3792, 'timezone' => 'Africa/Lagos'],
        ['name' => 'Kano, Nigeria', 'lat' => 12.0022, 'lng' => 8.5920, 'timezone' => 'Africa/Lagos'],
        ['name' => 'London, UK', 'lat' => 51.5074, 'lng' => -0.1278, 'timezone' => 'Europe/London'],
        ['name' => 'Paris, France', 'lat' => 48.8566, 'lng' => 2.3522, 'timezone' => 'Europe/Paris'],
        ['name' => 'Dubai, UAE', 'lat' => 25.2048, 'lng' => 55.2708, 'timezone' => 'Asia/Dubai'],
        ['name' => 'New York, USA', 'lat' => 40.7128, 'lng' => -74.0060, 'timezone' => 'America/New_York'],
        ['name' => 'Toronto, Canada', 'lat' => 43.6532, 'lng' => -79.3832, 'timezone' => 'America/Toronto'],
        ['name' => 'Jakarta, Indonesia', 'lat' => -6.2088, 'lng' => 106.8456, 'timezone' => 'Asia/Jakarta'],
        ['name' => 'Kuala Lumpur, Malaysia', 'lat' => 3.1390, 'lng' => 101.6869, 'timezone' => 'Asia/Kuala_Lumpur'],
        ['name' => 'Karachi, Pakistan', 'lat' => 24.8607, 'lng' => 67.0011, 'timezone' => 'Asia/Karachi'],
    ];

    public function mount(): void
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    /**
     * Select a preset city.
     */
    public function selectCity(int $index): void
    {
        if (isset($this->presetCities[$index])) {
            $this->location = $this->presetCities[$index];
        }
    }

    /**
     * Auto-detect location from browser geolocation.
     */
    public function setGeolocation(float $lat, float $lng, ?string $timezone = null): void
    {
        $this->location = [
            'name' => 'Detected Location (' . round($lat, 2) . '°, ' . round($lng, 2) . '°)',
            'lat' => $lat,
            'lng' => $lng,
            'timezone' => $timezone ?: date_default_timezone_get(),
        ];
    }

    /**
     * Navigate date offset.
     */
    public function changeDate(int $days): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDays($days)->format('Y-m-d');
    }

    /**
     * Reset date to today.
     */
    public function goToToday(): void
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    /**
     * Toggle calculation method.
     */
    public function setMethod(string $method): void
    {
        if (in_array($method, [NightCalculatorService::METHOD_SUNSET_TO_FAJR, NightCalculatorService::METHOD_SUNSET_TO_SUNRISE], true)) {
            $this->method = $method;
        }
    }

    /**
     * Toggle time format.
     */
    public function toggleTimeFormat(): void
    {
        $this->timeFormat = $this->timeFormat === '12h' ? '24h' : '12h';
    }

    /**
     * Format DateTime object into 12h or 24h string.
     */
    public function formatTime(?DateTime $dt): string
    {
        if (!$dt) {
            return '--:--';
        }
        return $this->timeFormat === '12h' ? $dt->format('g:i A') : $dt->format('H:i');
    }

    /**
     * Format a duration in seconds into human-readable text.
     */
    public function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        }
        if ($hours > 0) {
            return "{$hours}h";
        }
        return "{$minutes}m";
    }

    public function render(NightCalculatorService $calculator)
    {
        $todayForecast = $calculator->calculate(
            $this->location['lat'],
            $this->location['lng'],
            $this->selectedDate,
            $this->location['timezone'],
            $this->method
        );

        $weekForecasts = [];
        $baseDate = Carbon::parse($this->selectedDate);
        for ($i = 0; $i < 7; $i++) {
            $date = $baseDate->copy()->addDays($i);
            $weekForecasts[] = $calculator->calculate(
                $this->location['lat'],
                $this->location['lng'],
                $date,
                $this->location['timezone'],
                $this->method
            );
        }

        return view('livewire.night-time-component', [
            'todayForecast' => $todayForecast,
            'weekForecasts' => $weekForecasts,
        ])->layout('components.layouts.app', [
            'title' => 'Last 3rd | ' . $this->location['name'],
        ]);
    }
}
