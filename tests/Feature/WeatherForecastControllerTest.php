<?php

namespace Tests\Feature;

use App\Ai\Agents\WeatherForecastAgent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherForecastControllerTest extends TestCase
{
    private function freezeClock(): CarbonImmutable
    {
        $now = CarbonImmutable::parse('2026-10-21 12:00:00', 'UTC');
        CarbonImmutable::setTestNow($now);

        return $now;
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow(null);
        parent::tearDown();
    }

    public function test_valid_near_future_forecast_submission_renders_within_page(): void
    {
        $now = $this->freezeClock();
        $targetDate = $now->addDays(2)->toDateString();

        config()->set('services.openweathermap.api_key', 'owm-secret-key-12345');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response($this->currentWeatherPayload('Amman', 'JO')),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload($now)),
        ]);

        WeatherForecastAgent::fake([
            'توقع لطيف ومناسب للأنشطة الخارجية مع درجات حرارة معتدلة.',
        ]);

        $response = $this->post(route('weather.forecast'), [
            'city' => 'Amman',
            'date' => $targetDate,
            'lang' => 'ar',
        ]);

        $response->assertOk()
            ->assertSee('Amman')
            ->assertSee($targetDate)
            ->assertSee('توقع لطيف ومناسب للأنشطة الخارجية')
            ->assertDontSee('owm-secret-key-12345');
    }

    public function test_valid_far_future_forecast_renders_seasonal_estimate(): void
    {
        $now = $this->freezeClock();
        $targetDate = $now->addDays(60)->toDateString();

        config()->set('services.openweathermap.api_key', 'owm-secret-key-12345');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response($this->currentWeatherPayload('London', 'GB')),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload($now)),
        ]);

        WeatherForecastAgent::fake([
            'Expect chilly winter weather in London with frequent overcast skies.',
        ]);

        $response = $this->post(route('weather.forecast'), [
            'city' => 'London',
            'date' => $targetDate,
            'lang' => 'en',
        ]);

        $response->assertOk()
            ->assertSee('London')
            ->assertSee($targetDate)
            ->assertSee('Seasonal Climate Estimate')
            ->assertSee('Expect chilly winter weather in London')
            ->assertDontSee('owm-secret-key-12345');
    }

    public function test_invalid_city_returns_inline_error(): void
    {
        $now = $this->freezeClock();
        $targetDate = $now->addDays(2)->toDateString();

        Http::preventStrayRequests();

        $response = $this->post(route('weather.forecast'), [
            'city' => '$$$',
            'date' => $targetDate,
            'lang' => 'en',
        ]);

        $response->assertUnprocessable()
            ->assertSee('Enter a valid city or region name, then try again.')
            ->assertSee('value="$$$"', false);

        Http::assertNothingSent();
    }

    public function test_invalid_date_format_returns_inline_error(): void
    {
        Http::preventStrayRequests();

        $response = $this->post(route('weather.forecast'), [
            'city' => 'Cairo',
            'date' => 'invalid-date',
            'lang' => 'en',
        ]);

        $response->assertUnprocessable()
            ->assertSee('Please select a valid date in YYYY-MM-DD format.');

        Http::assertNothingSent();
    }

    public function test_past_date_returns_inline_error(): void
    {
        $now = $this->freezeClock();
        $pastDate = $now->subDays(2)->toDateString();

        $response = $this->post(route('weather.forecast'), [
            'city' => 'Cairo',
            'date' => $pastDate,
            'lang' => 'ar',
        ]);

        $response->assertUnprocessable()
            ->assertSee('يجب اختيار تاريخ في المستقبل (بدءًا من الغد).');
    }

    public function test_unknown_city_returns_not_found_inline_error(): void
    {
        $now = $this->freezeClock();
        $targetDate = $now->addDays(2)->toDateString();

        config()->set('services.openweathermap.api_key', 'owm-secret-key-12345');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response(['message' => 'city not found'], 404),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response(['message' => 'city not found'], 404),
        ]);

        $response = $this->post(route('weather.forecast'), [
            'city' => 'UnknownPlaceXYZ',
            'date' => $targetDate,
            'lang' => 'en',
        ]);

        $response->assertNotFound()
            ->assertSee('We could not find weather for that place.');
    }

    /**
     * @return array<string, mixed>
     */
    private function currentWeatherPayload(string $city = 'Amman', string $country = 'JO'): array
    {
        return [
            'name' => $city,
            'sys' => ['country' => $country],
            'main' => ['temp' => 20.0, 'humidity' => 50],
            'wind' => ['speed' => 3.5],
            'weather' => [['description' => 'clear sky', 'icon' => '01d']],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function forecastPayload(CarbonImmutable $now): array
    {
        $start = $now->startOfDay()->timestamp;
        $entries = [];

        for ($interval = 0; $interval < 40; $interval++) {
            $entries[] = [
                'dt' => $start + ($interval * 10800),
                'main' => ['temp_min' => 14.0, 'temp_max' => 24.0],
                'weather' => [[
                    'description' => 'clear sky',
                    'icon' => '01d',
                ]],
            ];
        }

        return [
            'city' => ['timezone' => 0],
            'list' => $entries,
        ];
    }
}
