<?php

namespace Tests\Feature;

use App\Exceptions\WeatherServiceException;
use App\Services\WeatherForecastService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\AnonymousAgent;
use Tests\TestCase;

class WeatherForecastServiceTest extends TestCase
{
    private function freezeTestClock(): CarbonImmutable
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

    public function test_near_future_date_uses_open_weather_map_data_and_groq_summary(): void
    {
        $now = $this->freezeTestClock();
        $targetDate = $now->addDays(2)->toDateString();

        config()->set('services.openweathermap.api_key', 'test-weather-key');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response($this->currentWeatherPayload('Amman', 'JO')),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload($now)),
        ]);

        AnonymousAgent::fake([
            "الطقس سيكون لطيفاً ومشمساً بدرجة حرارة تصل إلى 23 درجة مئوية.\n---\nنوصي بارتداء ملابس خفيفة والاستمتاع بالهواء الطلق.",
        ]);

        /** @var WeatherForecastService $service */
        $service = app(WeatherForecastService::class);
        $result = $service->getForecastForDate('Amman', $targetDate, 'ar');

        $this->assertSame('Amman', $result['city']);
        $this->assertSame('JO', $result['country']);
        $this->assertSame($targetDate, $result['date']);
        $this->assertFalse($result['is_seasonal_estimate']);
        $this->assertNotNull($result['high']);
        $this->assertNotNull($result['low']);
        $this->assertNotNull($result['description']);
        $this->assertNotNull($result['icon']);
        $this->assertFalse($result['ai_failed']);
        $this->assertStringContainsString('الطقس سيكون لطيفاً', (string) $result['ai_summary']);
        $this->assertStringContainsString('الطقس سيكون لطيفاً', (string) $result['ai_overview']);
        $this->assertStringContainsString('نوصي بارتداء ملابس خفيفة', (string) $result['ai_tips']);

        AnonymousAgent::assertPrompted(function ($prompt) use ($targetDate): bool {
            return $prompt->contains('Amman') && $prompt->contains($targetDate);
        });
    }

    public function test_far_future_date_provides_seasonal_estimate(): void
    {
        $now = $this->freezeTestClock();
        $targetDate = $now->addDays(45)->toDateString();

        config()->set('services.openweathermap.api_key', 'test-weather-key');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response($this->currentWeatherPayload('Paris', 'FR')),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload($now)),
        ]);

        AnonymousAgent::fake([
            'Expect cool and possibly rainy autumnal conditions in Paris around this time. A warm coat and an umbrella are advised.',
        ]);

        /** @var WeatherForecastService $service */
        $service = app(WeatherForecastService::class);
        $result = $service->getForecastForDate('Paris', $targetDate, 'en');

        $this->assertSame('Paris', $result['city']);
        $this->assertSame('FR', $result['country']);
        $this->assertSame($targetDate, $result['date']);
        $this->assertTrue($result['is_seasonal_estimate']);
        $this->assertNull($result['high']);
        $this->assertNull($result['low']);
        $this->assertFalse($result['ai_failed']);
        $this->assertStringContainsString('cool and possibly rainy', (string) $result['ai_summary']);

        AnonymousAgent::assertPrompted(function ($prompt) use ($targetDate): bool {
            return $prompt->contains('Paris') && $prompt->contains($targetDate) && $prompt->contains('seasonal');
        });
    }

    public function test_past_date_is_rejected(): void
    {
        $now = $this->freezeTestClock();
        $targetDate = $now->subDays(1)->toDateString();

        $this->expectException(WeatherServiceException::class);

        /** @var WeatherForecastService $service */
        $service = app(WeatherForecastService::class);
        $service->getForecastForDate('Amman', $targetDate, 'en');
    }

    public function test_today_date_is_rejected_as_future_date(): void
    {
        $now = $this->freezeTestClock();
        $targetDate = $now->toDateString();

        $this->expectException(WeatherServiceException::class);

        /** @var WeatherForecastService $service */
        $service = app(WeatherForecastService::class);
        $service->getForecastForDate('Amman', $targetDate, 'en');
    }

    public function test_date_beyond_one_year_is_rejected(): void
    {
        $now = $this->freezeTestClock();
        $targetDate = $now->addDays(400)->toDateString();

        $this->expectException(WeatherServiceException::class);

        /** @var WeatherForecastService $service */
        $service = app(WeatherForecastService::class);
        $service->getForecastForDate('Amman', $targetDate, 'en');
    }

    public function test_groq_failure_on_near_term_date_returns_weather_with_ai_failed_flag(): void
    {
        $now = $this->freezeTestClock();
        $targetDate = $now->addDays(2)->toDateString();

        config()->set('services.openweathermap.api_key', 'test-weather-key');
        Http::preventStrayRequests();
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response($this->currentWeatherPayload('Cairo', 'EG')),
            'api.openweathermap.org/data/2.5/forecast*' => Http::response($this->forecastPayload($now)),
        ]);

        AnonymousAgent::fake(function (): never {
            throw new \RuntimeException('Groq rate limit exceeded');
        });

        /** @var WeatherForecastService $service */
        $service = app(WeatherForecastService::class);
        $result = $service->getForecastForDate('Cairo', $targetDate, 'en');

        $this->assertSame('Cairo', $result['city']);
        $this->assertFalse($result['is_seasonal_estimate']);
        $this->assertTrue($result['ai_failed']);
        $this->assertNull($result['ai_summary']);
        $this->assertNotNull($result['high']);
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
                    'description' => 'scattered clouds',
                    'icon' => '03d',
                ]],
            ];
        }

        return [
            'city' => ['timezone' => 0],
            'list' => $entries,
        ];
    }
}
