<?php

namespace App\Services;

use App\Ai\Agents\WeatherForecastAgent;
use App\Exceptions\WeatherServiceException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherForecastService
{
    public function __construct(
        private readonly WeatherService $weatherService
    ) {}

    /**
     * @return array{
     *     city: string,
     *     country: string,
     *     date: string,
     *     day_name: string,
     *     is_seasonal_estimate: bool,
     *     high: ?float,
     *     low: ?float,
     *     description: ?string,
     *     icon: ?string,
     *     ai_summary: ?string,
     *     ai_failed: bool
     * }
     */
    public function getForecastForDate(string $city, string $dateString, string $language): array
    {
        try {
            $targetDate = CarbonImmutable::createFromFormat('Y-m-d', $dateString)->startOfDay();
        } catch (Throwable) {
            throw new WeatherServiceException('invalid_date', 422);
        }

        $today = CarbonImmutable::today();
        $diffInDays = (int) $today->diffInDays($targetDate, false);

        if ($diffInDays < 1) {
            throw new WeatherServiceException('past_date', 422);
        }

        if ($diffInDays > 365) {
            throw new WeatherServiceException('date_out_of_range', 422);
        }

        $weather = $this->weatherService->getWeatherForCity($city, $language);
        $canonicalCity = $weather['current']['city'];
        $country = $weather['current']['country'];
        $dayName = $targetDate->format('l');

        $matchedForecast = null;
        if ($diffInDays <= 5) {
            foreach ($weather['forecast'] as $day) {
                if ($day['date'] === $targetDate->toDateString()) {
                    $matchedForecast = $day;
                    break;
                }
            }
        }

        if ($matchedForecast !== null) {
            $isSeasonal = false;
            $high = (float) $matchedForecast['high'];
            $low = (float) $matchedForecast['low'];
            $description = (string) $matchedForecast['description'];
            $icon = (string) $matchedForecast['icon'];

            $prompt = sprintf(
                'Location: %s, %s. Target date: %s. Meteorological forecast: High %.1f°C, Low %.1f°C, Condition: %s. Respond in %s. Provide 2-3 friendly sentences explaining what to expect and give a practical tip.',
                $canonicalCity,
                $country,
                $targetDate->toDateString(),
                $high,
                $low,
                $description,
                $language === 'ar' ? 'Arabic' : 'English'
            );
        } else {
            $isSeasonal = true;
            $high = null;
            $low = null;
            $description = null;
            $icon = '02d';

            $prompt = sprintf(
                'Location: %s, %s. Target date: %s (in %d days). This date is beyond the 5-day radar forecast window. Provide a friendly 2-3 sentence seasonal climate estimate in %s based on historical patterns for this city and season, including a practical tip. Clearly state that it is a seasonal estimate.',
                $canonicalCity,
                $country,
                $targetDate->toDateString(),
                $diffInDays,
                $language === 'ar' ? 'Arabic' : 'English'
            );
        }

        $aiSummary = null;
        $aiFailed = false;

        try {
            $response = (new WeatherForecastAgent)->prompt($prompt);
            $aiSummary = trim((string) $response->text);

            if ($aiSummary === '') {
                $aiFailed = true;
                $aiSummary = null;
            }
        } catch (Throwable $exception) {
            Log::warning('WeatherForecastAgent execution failed', [
                'city' => $canonicalCity,
                'date' => $targetDate->toDateString(),
                'error' => $exception->getMessage(),
            ]);
            $aiFailed = true;
            $aiSummary = null;
        }

        if ($isSeasonal && $aiFailed) {
            throw new WeatherServiceException('ai_unavailable', 503);
        }

        return [
            'city' => $canonicalCity,
            'country' => $country,
            'date' => $targetDate->toDateString(),
            'day_name' => $dayName,
            'is_seasonal_estimate' => $isSeasonal,
            'high' => $high,
            'low' => $low,
            'description' => $description,
            'icon' => $icon,
            'ai_summary' => $aiSummary,
            'ai_failed' => $aiFailed,
        ];
    }
}
