<?php

namespace App\Livewire;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NightTimeComponent extends Component
{
    public $forecasts = [];
    public $loading = true;
    public $error = null;
    public $location = [
        'name' => 'Abuja, Nigeria',
        'lat' => 9.0765,
        'lng' => 7.3986
    ];

    public function mount()
    {
        $this->fetchForecastData();
    }

    // public function fetchForecastData()
    // {
    //     try {
    //         for ($i = 0; $i < 7; $i++) {
    //             $date = Carbon::today()->addDays($i)->format('Y-m-d');
    //             $response = Http::get('https://api.sunrise-sunset.org/json', [
    //                 'lat' => $this->location['lat'],
    //                 'lng' => $this->location['lng'],
    //                 'date' => $date,
    //                 'formatted' => 1,
    //             ]);

    //             if ($response->successful()) {
    //                 $data = $response->json();
    //                 $sunsetTime = Carbon::parse($data['results']['astronomical_twilight_end']);
    //                 $sunriseTime = Carbon::parse($data['results']['astronomical_twilight_begin'])->addDay(); // Ensure sunrise is after sunset

    //                 $this->forecasts[] = [
    //                     'date' => $date,
    //                     'sunset' => $sunsetTime,
    //                     'sunrise' => $sunriseTime,
    //                 ];
    //             } else {
    //                 throw new \Exception('Failed to fetch sunset/sunrise data');
    //             }
    //         }
    //         $this->loading = false;
    //     } catch (\Exception $e) {
    //         $this->error = 'Failed to fetch forecast data. Please try again later.';
    //         $this->loading = false;
    //     }
    // }
    public function fetchForecastData()
    {
        try {
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::today()->addDays($i)->format('Y-m-d');
                $cacheKey = "forecast_data_{$this->location['lat']}_{$this->location['lng']}_{$date}";

                // Check if data is already cached
                $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($date) {
                    $response = Http::get('https://api.sunrise-sunset.org/json', [
                        'lat' => $this->location['lat'],
                        'lng' => $this->location['lng'],
                        'date' => $date,
                        'formatted' => 1,
                    ]);

                    if ($response->successful()) {
                        return $response->json();
                    }

                    throw new \Exception('Failed to fetch sunset/sunrise data');
                });

                // Process the cached or fetched data
                $sunsetTime = Carbon::parse($data['results']['astronomical_twilight_end']);
                $sunriseTime = Carbon::parse($data['results']['astronomical_twilight_begin'])->addDay(); // Ensure sunrise is after sunset

                $this->forecasts[] = [
                    'date' => $date,
                    'sunset' => $sunsetTime,
                    'sunrise' => $sunriseTime,
                ];
            }
            $this->loading = false;
        } catch (\Exception $e) {
            $this->error = 'Failed to fetch forecast data. Please try again later.';
            $this->loading = false;
        }
    }


    // public function calculateNightTimes($sunset, $sunrise)
    // {
    //     $nightDuration = $sunset->diffInSeconds($sunrise);
    //     $oneThird = $nightDuration / 3;
    //     $twoThirds = $oneThird * 2;

    //     return [
    //         '1/3' => [
    //             'start' => $sunset->format('h:i A'),
    //             'end' => $sunset->addSeconds($oneThird)->format('h:i A')
    //         ],
    //         '2/3' => [
    //             'start' => $sunset->addSeconds($oneThird)->format('h:i A'),
    //             'end' => $sunset->addSeconds($twoThirds)->format('h:i A')
    //         ],
    //         '3/3' => [
    //             'start' => $sunset->addSeconds($twoThirds)->format('h:i A'),
    //             'end' => $sunrise->format('h:i A')
    //         ],
    //     ];
    // }

    public function calculateNightTimes($sunset, $sunrise)
    {
        $nightDuration = $sunset->diffInSeconds($sunrise);
        $oneThird = $nightDuration / 3;
        $twoThirds = $oneThird * 2;

        return [
            '1/3' => [
                'start' => $sunset->format('h:i A'),
                'end' => $sunset->copy()->addSeconds($oneThird)->format('h:i A')
            ],
            '2/3' => [
                'start' => $sunset->copy()->addSeconds($oneThird)->format('h:i A'),
                'end' => $sunset->copy()->addSeconds($twoThirds)->format('h:i A')
            ],
            '3/3' => [
                'start' => $sunset->copy()->addSeconds($twoThirds)->format('h:i A'),
                'end' => $sunrise->format('h:i A')
            ],
        ];
    }


    public function render()
    {
        return view('livewire.night-time-component');
    }
}
