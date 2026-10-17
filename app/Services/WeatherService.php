<?php

namespace App\Services;

use App\Exceptions\WeatherServiceException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class WeatherService
{
    private const string API_BASE_URL = 'https://api.openweathermap.org/data/2.5';

    /**
     * @return array{
     *     current: array{
     *         city: string,
     *         country: string,
     *         temperature: float,
     *         description: string,
     *         icon: string,
     *         humidity: int,
     *         wind_speed: float
     *     },
     *     forecast: list<array{
     *         date: string,
     *         high: float,
     *         low: float,
     *         description: string,
     *         icon: string
     *     }>
     * }
     */
    public function getWeatherForCity(string $city, string $language): array
    {
        return $this->fetchWeather(['q' => $city], $language);
    }

    /**
     * @return array{
     *     current: array{
     *         city: string,
     *         country: string,
     *         temperature: float,
     *         description: string,
     *         icon: string,
     *         humidity: int,
     *         wind_speed: float
     *     },
     *     forecast: list<array{
     *         date: string,
     *         high: float,
     *         low: float,
     *         description: string,
     *         icon: string
     *     }>
     * }
     */
    public function getWeatherForCoordinates(float $latitude, float $longitude, string $language): array
    {
        return $this->fetchWeather([
            'lat' => $latitude,
            'lon' => $longitude,
        ], $language);
    }

    /**
     * @param  array{q?: string, lat?: float, lon?: float}  $location
     * @return array{
     *     current: array{
     *         city: string,
     *         country: string,
     *         temperature: float,
     *         description: string,
     *         icon: string,
     *         humidity: int,
     *         wind_speed: float
     *     },
     *     forecast: list<array{
     *         date: string,
     *         high: float,
     *         low: float,
     *         description: string,
     *         icon: string
     *     }>
     * }
     */
    private function fetchWeather(array $location, string $language): array
    {
        $apiKey = config('services.openweathermap.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new WeatherServiceException('unavailable');
        }

        $query = [
            ...$location,
            'appid' => $apiKey,
            'units' => 'metric',
            'lang' => $language,
        ];

        try {
            $currentResponse = $this->requestWeatherData('/weather', $query);

            if ($currentResponse->notFound()) {
                throw new WeatherServiceException('not_found', 404);
            }

            if (! $currentResponse->successful()) {
                throw new WeatherServiceException('unavailable');
            }

            $forecastResponse = $this->requestWeatherData('/forecast', $query);
        } catch (ConnectionException) {
            throw new WeatherServiceException('unavailable');
        }

        if ($forecastResponse->notFound()) {
            throw new WeatherServiceException('not_found', 404);
        }

        if (! $forecastResponse->successful()) {
            throw new WeatherServiceException('unavailable');
        }

        $currentPayload = $currentResponse->json();
        $forecastPayload = $forecastResponse->json();

        if (! is_array($currentPayload) || ! is_array($forecastPayload)) {
            throw new WeatherServiceException('unavailable');
        }

        return [
            'current' => $this->normalizeCurrentWeather($currentPayload),
            'forecast' => $this->normalizeForecast($forecastPayload),
        ];
    }

    /**
     * @param  array<string, string|float>  $query
     */
    private function requestWeatherData(string $endpoint, array $query): Response
    {
        return Http::connectTimeout(2)
            ->timeout(4)
            ->retry(
                2,
                100,
                fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429)),
                throw: false,
            )
            ->get(self::API_BASE_URL.$endpoint, $query);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     city: string,
     *     country: string,
     *     temperature: float,
     *     description: string,
     *     icon: string,
     *     humidity: int,
     *     wind_speed: float
     * }
     */
    private function normalizeCurrentWeather(array $payload): array
    {
        $condition = $payload['weather'][0] ?? null;

        if (
            ! is_array($condition)
            || ! isset($payload['name'], $payload['main']['temp'], $payload['main']['humidity'], $payload['wind']['speed'])
            || ! isset($condition['description'], $condition['icon'])
        ) {
            throw new WeatherServiceException('unavailable');
        }

        return [
            'city' => (string) $payload['name'],
            'country' => (string) ($payload['sys']['country'] ?? ''),
            'temperature' => (float) $payload['main']['temp'],
            'description' => (string) $condition['description'],
            'icon' => (string) $condition['icon'],
            'humidity' => (int) $payload['main']['humidity'],
            'wind_speed' => (float) $payload['wind']['speed'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{date: string, high: float, low: float, description: string, icon: string}>
     */
    private function normalizeForecast(array $payload): array
    {
        if (! is_array($payload['list'] ?? null)) {
            throw new WeatherServiceException('unavailable');
        }

        $timezoneOffset = (int) ($payload['city']['timezone'] ?? 0);
        $days = [];

        foreach ($payload['list'] as $entry) {
            if (
                ! is_array($entry)
                || ! isset($entry['dt'], $entry['main']['temp_min'], $entry['main']['temp_max'])
                || ! is_array($entry['weather'][0] ?? null)
                || ! isset($entry['weather'][0]['description'], $entry['weather'][0]['icon'])
            ) {
                continue;
            }

            $date = CarbonImmutable::createFromTimestampUTC((int) $entry['dt'])
                ->addSeconds($timezoneOffset)
                ->toDateString();

            if (! isset($days[$date])) {
                $days[$date] = [
                    'date' => $date,
                    'high' => (float) $entry['main']['temp_max'],
                    'low' => (float) $entry['main']['temp_min'],
                    'description' => (string) $entry['weather'][0]['description'],
                    'icon' => (string) $entry['weather'][0]['icon'],
                ];

                continue;
            }

            $days[$date]['high'] = max($days[$date]['high'], (float) $entry['main']['temp_max']);
            $days[$date]['low'] = min($days[$date]['low'], (float) $entry['main']['temp_min']);
        }

        $forecast = array_slice(array_values($days), 0, 5);

        if (count($forecast) !== 5) {
            throw new WeatherServiceException('unavailable');
        }

        return $forecast;
    }
}
