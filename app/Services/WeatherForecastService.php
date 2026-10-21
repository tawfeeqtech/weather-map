<?php

namespace App\Services;

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

        $instructions = <<<'INSTRUCTIONS'
You are an expert meteorological and climate assistant for a bilingual weather application.
Your goal is to provide a structured, friendly, and practical explanation of the weather for the requested city and future date.

Format your response in exactly two distinct sections separated by three dashes on a new line (---):
Section 1: 1-2 friendly sentences describing the sky conditions, temperatures in Celsius, and general atmospheric feel.
---
Section 2: 1-2 actionable, practical recommendations regarding clothing, outdoor plans, or comfort.

Rules:
1. Speak strictly in the language requested by the user prompt ('ar' for Arabic or 'en' for English).
2. All temperatures must be expressed in Celsius (°C). Never mention Fahrenheit.
3. If the date is a near-term forecast supported by radar/meteorological data, explain what to expect based on the provided numbers.
4. If the date is far in the future (>5 days ahead), explain seasonal climate patterns and clearly state that it is a seasonal estimate.
5. Do NOT write section titles or markdown headers like "Section 1:". Just output the content of section 1, then "---", then the content of section 2.
INSTRUCTIONS;

        $provider = 'groq';
        $model = (string) config('ai.providers.groq.models.text.default', env('GROQ_MODEL', 'llama-3.3-70b-versatile'));

        $aiSummary = null;
        $aiOverview = null;
        $aiTips = null;
        $aiFailed = false;

        try {
            $response = \Laravel\Ai\agent($instructions)->prompt(
                $prompt,
                provider: $provider,
                model: $model,
            );
            $aiSummary = trim((string) $response->text);

            if ($aiSummary === '') {
                $aiFailed = true;
                $aiSummary = null;
            } else {
                if (str_contains($aiSummary, '---')) {
                    $segments = array_map('trim', explode('---', $aiSummary, 2));
                    $aiOverview = $segments[0] !== '' ? $segments[0] : null;
                    $aiTips = $segments[1] !== '' ? $segments[1] : null;
                } elseif (str_contains($aiSummary, "\n\n")) {
                    $segments = array_map('trim', explode("\n\n", $aiSummary, 2));
                    $aiOverview = $segments[0] !== '' ? $segments[0] : null;
                    $aiTips = $segments[1] !== '' ? $segments[1] : null;
                }

                $aiOverview ??= $aiSummary;
            }
        } catch (Throwable $exception) {
            Log::warning('AI weather forecast generation failed', [
                'city' => $canonicalCity,
                'date' => $targetDate->toDateString(),
                'error' => $exception->getMessage(),
            ]);
            $aiFailed = true;
            $aiSummary = null;
            $aiOverview = null;
            $aiTips = null;
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
            'ai_overview' => $aiOverview,
            'ai_tips' => $aiTips,
            'ai_failed' => $aiFailed,
        ];
    }
}
