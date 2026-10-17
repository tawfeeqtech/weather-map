<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherSearchTest extends TestCase
{
    public function test_search_displays_current_conditions_and_exactly_five_daily_forecasts(): void
    {
        config()->set('services.openweathermap.api_key', 'test-weather-api-key');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response($this->currentWeatherPayload()),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload()),
        ]);

        $response = $this->post(route('weather.search'), [
            'city' => 'London',
            'lang' => 'en',
        ]);

        $response->assertOk()
            ->assertSee('Weather in London GB')
            ->assertSee('>18</span>', false)
            ->assertSee('°C')
            ->assertSee('clear sky')
            ->assertSee('72%')
            ->assertSee('light rain')
            ->assertSee('21°')
            ->assertSee('10°');

        $this->assertSame(5, substr_count($response->getContent(), '<article'));

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/weather?')
            && str_contains($request->url(), 'q=London')
            && str_contains($request->url(), 'units=metric')
            && str_contains($request->url(), 'lang=en')
            && str_contains($request->url(), 'appid=test-weather-api-key'));

        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/forecast?')
            && str_contains($request->url(), 'q=London')
            && str_contains($request->url(), 'units=metric'));

        $this->assertStringNotContainsString('test-weather-api-key', $response->getContent());
    }

    public function test_invalid_city_input_shows_an_inline_message_without_calling_the_weather_api(): void
    {
        Http::preventStrayRequests();

        $response = $this->post(route('weather.search'), [
            'city' => '!!!',
            'lang' => 'en',
        ]);

        $response->assertUnprocessable()
            ->assertSee('Enter a valid city or region name, then try again.')
            ->assertSee('role="alert"', false);

        Http::assertNothingSent();
    }

    public function test_empty_city_input_is_required_and_does_not_call_the_weather_api(): void
    {
        Http::preventStrayRequests();

        $response = $this->post(route('weather.search'), [
            'city' => '',
            'lang' => 'en',
        ]);

        $response->assertUnprocessable()
            ->assertSee('Enter a valid city or region name, then try again.');

        Http::assertNothingSent();
    }

    public function test_an_unrecognized_city_shows_a_retryable_inline_error(): void
    {
        config()->set('services.openweathermap.api_key', 'test-weather-api-key');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response(['message' => 'city not found'], 404),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload()),
        ]);

        $response = $this->post(route('weather.search'), [
            'city' => 'Nowhere',
            'lang' => 'en',
        ]);

        $response->assertNotFound()
            ->assertSee('We could not find weather for that place.')
            ->assertSee('value="Nowhere"', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function currentWeatherPayload(): array
    {
        return [
            'name' => 'London',
            'sys' => ['country' => 'GB'],
            'main' => ['temp' => 18.4, 'humidity' => 72],
            'wind' => ['speed' => 4.6],
            'weather' => [['description' => 'clear sky', 'icon' => '01d']],
        ];
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
                'main' => ['temp_min' => 10 + ($interval % 5), 'temp_max' => 17 + ($interval % 5)],
                'weather' => [[
                    'description' => $interval === 0 ? 'light rain' : 'clear sky',
                    'icon' => $interval === 0 ? '10d' : '01d',
                ]],
            ];
        }

        return [
            'city' => ['timezone' => 0],
            'list' => $entries,
        ];
    }
}
