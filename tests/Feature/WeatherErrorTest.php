<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherErrorTest extends TestCase
{
    public function test_missing_api_key_fails_gracefully_without_making_an_upstream_request(): void
    {
        config()->set('services.openweathermap.api_key', null);
        Http::preventStrayRequests();

        $response = $this->post(route('weather.search'), [
            'city' => 'Oslo',
            'lang' => 'en',
        ]);

        $response->assertStatus(502)
            ->assertSee('Weather data is temporarily unavailable.')
            ->assertSee('value="Oslo"', false);

        Http::assertNothingSent();
    }

    public function test_upstream_weather_failure_is_shown_inline_and_keeps_the_search_value(): void
    {
        config()->set('services.openweathermap.api_key', 'test-weather-api-key');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response(['message' => 'internal error'], 503),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload()),
        ]);

        $response = $this->post(route('weather.search'), [
            'city' => 'Oslo',
            'lang' => 'en',
        ]);

        $response->assertStatus(502)
            ->assertSee('Weather data is temporarily unavailable.')
            ->assertSee('value="Oslo"', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function forecastPayload(): array
    {
        $start = CarbonImmutable::parse('2026-10-17 00:00:00', 'UTC')->timestamp;
        $entries = [];

        for ($interval = 0; $interval < 40; $interval++) {
            $entries[] = [
                'dt' => $start + ($interval * 10800),
                'main' => ['temp_min' => 5, 'temp_max' => 11],
                'weather' => [['description' => 'cloudy', 'icon' => '04d']],
            ];
        }

        return [
            'city' => ['timezone' => 0],
            'list' => $entries,
        ];
    }
}
