<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherGeolocationTest extends TestCase
{
    public function test_valid_coordinates_return_current_weather_and_daily_forecast(): void
    {
        config()->set('services.openweathermap.api_key', 'test-weather-api-key');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response([
                'name' => 'Cairo',
                'sys' => ['country' => 'EG'],
                'main' => ['temp' => 29, 'humidity' => 35],
                'wind' => ['speed' => 3.2],
                'weather' => [['description' => 'سماء صافية', 'icon' => '01d']],
            ]),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload()),
        ]);

        $response = $this->post(route('weather.locate'), [
            'latitude' => '30.0444',
            'longitude' => '31.2357',
            'lang' => 'ar',
        ]);

        $response->assertOk()
            ->assertSee('الطقس في Cairo EG')
            ->assertSee('سماء صافية')
            ->assertSee('dir="rtl"', false);

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/weather?')
            && str_contains($request->url(), 'lat=30.0444')
            && str_contains($request->url(), 'lon=31.2357')
            && str_contains($request->url(), 'lang=ar'));
    }

    public function test_out_of_range_coordinates_show_an_inline_error_without_api_requests(): void
    {
        Http::preventStrayRequests();

        $response = $this->post(route('weather.locate'), [
            'latitude' => '91',
            'longitude' => '0',
            'lang' => 'en',
        ]);

        $response->assertUnprocessable()
            ->assertSee('The location coordinates are invalid.')
            ->assertSee('role="alert"', false);

        Http::assertNothingSent();
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
                'main' => ['temp_min' => 20, 'temp_max' => 31],
                'weather' => [['description' => 'سماء صافية', 'icon' => '01d']],
            ];
        }

        return [
            'city' => ['timezone' => 7200],
            'list' => $entries,
        ];
    }
}
