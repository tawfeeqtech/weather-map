<?php

namespace Tests\Feature;

use Tests\TestCase;

class WeatherPageTest extends TestCase
{
    public function test_weather_page_loads_with_an_english_empty_state(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Your forecast starts here')
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('Use my location');
    }

    public function test_weather_page_can_be_loaded_in_arabic_with_right_to_left_direction(): void
    {
        $response = $this->get('/?lang=ar');

        $response->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('توقعات الطقس تبدأ هنا');
    }
}
