<?php

namespace App\Services;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;

class NightCalculatorService
{
    public const METHOD_SUNSET_TO_FAJR = 'sunset_to_fajr';
    public const METHOD_SUNSET_TO_SUNRISE = 'sunset_to_sunrise';

    /**
     * Calculate night divisions for a given date, coordinates, and timezone.
     *
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param Carbon|string $date Date of the sunset starting the night
     * @param string|null $timezone Timezone identifier (defaults to UTC if invalid)
     * @param string $method 'sunset_to_fajr' (default) or 'sunset_to_sunrise'
     * @return array
     */
    public function calculate(
        float $lat,
        float $lng,
        $date,
        ?string $timezone = 'UTC',
        string $method = self::METHOD_SUNSET_TO_FAJR
    ): array {
        $tz = $this->resolveTimezone($timezone);
        $carbonDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date, $tz);
        
        $noonToday = (new DateTime($carbonDate->format('Y-m-d') . ' 12:00:00', $tz))->getTimestamp();
        $noonTomorrow = $noonToday + 86400;

        $infoToday = date_sun_info($noonToday, $lat, $lng);
        $infoTomorrow = date_sun_info($noonTomorrow, $lat, $lng);

        // Sunset starts the night
        $sunsetTs = $infoToday['sunset'] ?? ($noonToday + 21600);
        $sunset = (new DateTime('@' . $sunsetTs))->setTimezone($tz);

        // Fajr (astronomical twilight begin)
        $fajrTs = $infoTomorrow['astronomical_twilight_begin'] ?? ($noonTomorrow - 25200);
        // Fallback if polar regions or invalid twilight: 1.5 hours before sunrise
        $sunriseTs = $infoTomorrow['sunrise'] ?? ($noonTomorrow - 21600);
        if ($fajrTs === false || $fajrTs >= $sunriseTs) {
            $fajrTs = $sunriseTs - 5400; // 90 minutes before sunrise fallback
        }

        $fajr = (new DateTime('@' . $fajrTs))->setTimezone($tz);
        $sunrise = (new DateTime('@' . $sunriseTs))->setTimezone($tz);

        // Determine night end depending on method
        $nightEnd = ($method === self::METHOD_SUNSET_TO_SUNRISE) ? $sunrise : $fajr;
        $nightEndTs = $nightEnd->getTimestamp();

        // Total night duration in seconds
        $nightDuration = max(1, $nightEndTs - $sunset->getTimestamp());
        $oneThirdSec = (int) round($nightDuration / 3);
        $halfSec = (int) round($nightDuration / 2);

        // Key milestones
        $firstThirdEnd = (clone $sunset)->modify("+{$oneThirdSec} seconds");
        $midnight = (clone $sunset)->modify("+{$halfSec} seconds");
        $secondThirdEnd = (clone $firstThirdEnd)->modify("+{$oneThirdSec} seconds");
        $lastThirdStart = clone $secondThirdEnd;
        $lastThirdEnd = clone $nightEnd;

        // Current status relative to now
        $now = new DateTime('now', $tz);
        $status = $this->determineStatus($now, $sunset, $firstThirdEnd, $secondThirdEnd, $lastThirdEnd);

        return [
            'date' => $carbonDate->format('Y-m-d'),
            'formatted_date' => $carbonDate->format('l, F j, Y'),
            'method' => $method,
            'timezone' => $tz->getName(),
            'timestamps' => [
                'sunset' => $sunset,
                'fajr' => $fajr,
                'sunrise' => $sunrise,
                'midnight' => $midnight,
                'night_end' => $nightEnd,
            ],
            'durations' => [
                'total_seconds' => $nightDuration,
                'total_human' => $this->secondsToHuman($nightDuration),
                'third_seconds' => $oneThirdSec,
                'third_human' => $this->secondsToHuman($oneThirdSec),
            ],
            'divisions' => [
                '1/3' => [
                    'label' => 'First Third',
                    'start' => $sunset,
                    'end' => $firstThirdEnd,
                    'duration' => $this->secondsToHuman($oneThirdSec),
                ],
                '2/3' => [
                    'label' => 'Second Third',
                    'start' => $firstThirdEnd,
                    'end' => $secondThirdEnd,
                    'duration' => $this->secondsToHuman($oneThirdSec),
                ],
                '3/3' => [
                    'label' => 'Last Third (Tahajjud)',
                    'is_last_third' => true,
                    'start' => $lastThirdStart,
                    'end' => $lastThirdEnd,
                    'duration' => $this->secondsToHuman($oneThirdSec),
                ],
            ],
            'midnight' => $midnight,
            'status' => $status,
        ];
    }

    /**
     * Determine the current phase of the night.
     */
    protected function determineStatus(
        DateTime $now,
        DateTime $sunset,
        DateTime $firstThirdEnd,
        DateTime $secondThirdEnd,
        DateTime $nightEnd
    ): array {
        $nowTs = $now->getTimestamp();
        $sunsetTs = $sunset->getTimestamp();
        $firstThirdEndTs = $firstThirdEnd->getTimestamp();
        $secondThirdEndTs = $secondThirdEnd->getTimestamp();
        $nightEndTs = $nightEnd->getTimestamp();

        if ($nowTs < $sunsetTs) {
            $secondsUntilSunset = $sunsetTs - $nowTs;
            $secondsUntilLastThird = $secondThirdEndTs - $nowTs;
            return [
                'phase' => 'daytime',
                'phase_label' => 'Daytime (Before Sunset)',
                'is_night' => false,
                'is_last_third' => false,
                'seconds_until_last_third' => $secondsUntilLastThird,
                'seconds_until_sunset' => $secondsUntilSunset,
                'progress_percent' => 0,
            ];
        }

        if ($nowTs >= $sunsetTs && $nowTs < $firstThirdEndTs) {
            $progress = (int) round((($nowTs - $sunsetTs) / ($nightEndTs - $sunsetTs)) * 100);
            return [
                'phase' => 'first_third',
                'phase_label' => 'First Third of the Night',
                'is_night' => true,
                'is_last_third' => false,
                'seconds_until_last_third' => $secondThirdEndTs - $nowTs,
                'progress_percent' => min(100, max(0, $progress)),
            ];
        }

        if ($nowTs >= $firstThirdEndTs && $nowTs < $secondThirdEndTs) {
            $progress = (int) round((($nowTs - $sunsetTs) / ($nightEndTs - $sunsetTs)) * 100);
            return [
                'phase' => 'second_third',
                'phase_label' => 'Second Third of the Night',
                'is_night' => true,
                'is_last_third' => false,
                'seconds_until_last_third' => $secondThirdEndTs - $nowTs,
                'progress_percent' => min(100, max(0, $progress)),
            ];
        }

        if ($nowTs >= $secondThirdEndTs && $nowTs <= $nightEndTs) {
            $progress = (int) round((($nowTs - $sunsetTs) / ($nightEndTs - $sunsetTs)) * 100);
            return [
                'phase' => 'last_third',
                'phase_label' => 'The Blessed Last Third (Tahajjud)',
                'is_night' => true,
                'is_last_third' => true,
                'seconds_remaining_in_last_third' => $nightEndTs - $nowTs,
                'progress_percent' => min(100, max(0, $progress)),
            ];
        }

        return [
            'phase' => 'past_night',
            'phase_label' => 'Night Ended (After Fajr/Sunrise)',
            'is_night' => false,
            'is_last_third' => false,
            'progress_percent' => 100,
        ];
    }

    /**
     * Convert seconds to readable human duration (e.g., "3h 45m").
     */
    public function secondsToHuman(int $seconds): string
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

    /**
     * Resolve and validate timezone string.
     */
    protected function resolveTimezone(?string $tzString): DateTimeZone
    {
        if ($tzString) {
            try {
                return new DateTimeZone($tzString);
            } catch (\Exception $e) {
                // Fall back
            }
        }

        try {
            return new DateTimeZone(date_default_timezone_get());
        } catch (\Exception $e) {
            return new DateTimeZone('UTC');
        }
    }
}
