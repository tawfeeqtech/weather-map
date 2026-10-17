# Project Guidelines

## Project

This repository is a Laravel web application for a simple, single-page weather experience. The approved product requirements are in [`intent/weather-app.md`](intent/weather-app.md) and [`docs/PRD-weather-app.md`](docs/PRD-weather-app.md). The implementation sequence and proposed verification are in [`docs/ai-sdlc/weather-app-plan.md`](docs/ai-sdlc/weather-app-plan.md).

Treat the intent and PRD as product requirements, this file as engineering guidance, and the source code as the authority on what is currently implemented. Do not describe planned features as complete.

## Product Boundaries

- The app has one weather page, with current conditions and a 5-day daily forecast.
- Support manual city/region search and browser geolocation. Ask for browser permission; if location access is denied or unavailable, let the user search manually.
- Use OpenWeatherMap for weather data. Keep its API key server-side in `.env` as `OPENWEATHERMAP_API_KEY`; never hardcode it or expose it in browser assets.
- Support Arabic and English, including a language switcher and correct RTL layout for Arabic.
- Display temperatures in Celsius only.
- Show API, search, and location errors clearly within the page; do not introduce a separate error page for these states.
- Do not add accounts, authentication, search history, localStorage, IP-based geolocation, hourly forecasts, charts, severe-weather alerts, Fahrenheit, or About/Contact pages unless the approved requirements change.
- Treat pressure/visibility, hourly forecasts, charts, and severe-weather alerts as future nice-to-haves, not current scope.

## Implementation Guidance

- Use the existing Laravel, PHP, Blade, Vite, and Tailwind CSS 4 setup. Reuse existing dependencies and patterns; do not add Bootstrap or introduce packages without a demonstrated need.
- Keep weather API calls and the API key on the Laravel server. Validate search terms and coordinates before making upstream requests, and handle upstream failures without losing the user's ability to retry or search manually.
- Keep browser JavaScript focused on geolocation and necessary page interactivity. Do not persist location or search data in the browser.
- Make UI changes responsive, accessible by keyboard, and compatible with both LTR English and RTL Arabic.
- Update relevant documentation when product behavior or setup changes.

## Build and Test

- Run the PHP test suite with `php artisan test` (or `composer test`).
- Compile frontend assets with `npm run build`.
- Format/check PHP with `vendor/bin/pint --test`; apply formatting with `vendor/bin/pint` when needed.
- For local development, use `composer run dev` or run the Laravel and Vite development servers as appropriate.
- Add or update tests for new behavior. Prefer focused feature tests for routes, validation, localization, and inline errors; mock OpenWeatherMap HTTP responses instead of relying on live API calls.

## Current Implementation Note

The approved weather requirements are ahead of the current application code. The current `/` route still renders Laravel's starter `welcome` view; verify the code before assuming weather search, geolocation, translations, or API integration exist.
