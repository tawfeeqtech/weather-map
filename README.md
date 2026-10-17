# Weather App

A single-page weather application project built with Laravel. Its approved requirements describe current weather, a 5-day forecast, city/region search, optional browser geolocation, and an Arabic/English interface with RTL support.

> **Implementation status:** The product requirements and implementation plan are approved, but the current application is still at the Laravel starter stage. The `/` route renders the default welcome view; weather search, forecast data, geolocation, translations, and OpenWeatherMap integration have not yet been implemented.

## Project Documents

- [Intent and product decisions](intent/weather-app.md)
- [Product requirements (PRD)](docs/PRD-weather-app.md)
- [Implementation plan](docs/ai-sdlc/weather-app-plan.md)
- [Agent guidance](AGENTS.md)
- [Claude Code notes](CLAUDE.md)

## Technology

- PHP 8.3+
- Laravel 13
- Blade templates
- Tailwind CSS 4 with Vite
- OpenWeatherMap is the planned weather data provider

## Requirements

Install PHP 8.3 or later, Composer, Node.js, and npm.

## Local Setup

From the project root:

```sh
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate
npm run build
```

On Windows PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

Start the Laravel server:

```sh
php artisan serve
```

In a separate terminal, start Vite when working on frontend assets:

```sh
npm run dev
```

Alternatively, use the configured Composer development command:

```sh
composer run dev
```

## Weather API Configuration

OpenWeatherMap integration is part of the approved requirements but is not yet wired into the current application. When implementing it:

1. Create an API key through OpenWeatherMap.
2. Add it to the local `.env` file as `OPENWEATHERMAP_API_KEY=your_key_here`.
3. Keep the key on the Laravel server; do not prefix it with `VITE_`, commit it, or place it in frontend code.

The app should request location only through the browser Geolocation API after user permission. If permission is denied or location is unavailable, users should be able to search for a city or region manually. No IP-based geolocation or browser-side search/location persistence should be added.

## Product Scope

The planned page displays current temperature in Celsius, condition and icon, humidity, wind speed, and a 5-day forecast with daily highs/lows. It supports Arabic and English, with RTL layout for Arabic, and displays API or input errors inline on the same page.

Accounts, authentication, local search history, Fahrenheit, additional About/Contact pages, hourly forecasts, charts, atmospheric pressure/visibility, and severe-weather alerts are out of scope unless the product requirements are revised.

## Tests and Formatting

```sh
php artisan test
npm run build
vendor/bin/pint --test
```

Weather API tests should mock HTTP responses rather than depend on live OpenWeatherMap access. See the [implementation plan](docs/ai-sdlc/weather-app-plan.md) for the proposed test mapping.
