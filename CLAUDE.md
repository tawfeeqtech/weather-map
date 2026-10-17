# Claude Code Project Notes

Follow the shared project guidance in [`AGENTS.md`](AGENTS.md). Product requirements are maintained in [`intent/weather-app.md`](intent/weather-app.md) and [`docs/PRD-weather-app.md`](docs/PRD-weather-app.md); the planned implementation and test mapping are in [`docs/ai-sdlc/weather-app-plan.md`](docs/ai-sdlc/weather-app-plan.md).

## Working in This Repository

- Treat the intent and PRD as the approved product scope, but confirm implementation status in the source before claiming a feature exists.
- This is a single-page Laravel weather application using Blade, Vite, and Tailwind CSS 4. Use existing dependencies and conventions.
- Preserve the requirements for Arabic/English, Arabic RTL, Celsius-only temperatures, manual city/region search, browser-permission-based geolocation with manual-search fallback, and clear in-page error messages.
- Keep OpenWeatherMap credentials in the server-side `.env` configuration under `OPENWEATHERMAP_API_KEY`. Do not hardcode or expose the key, use IP-based geolocation, or persist search/location data in localStorage.
- Keep out-of-scope features out unless the product documents change: accounts, search history, extra pages, Fahrenheit, hourly forecasts, charts, and severe-weather alerts.
- Update relevant tests and documentation with behavior changes. Mock upstream weather API calls in tests.
- The weather functionality is not yet reflected in the current source: the `/` route still renders Laravel's starter welcome page. Do not assume the planned weather flows are implemented.

## Useful Commands

- `php artisan test` — run the PHP test suite.
- `npm run build` — compile frontend assets.
- `vendor/bin/pint --test` — check PHP formatting.
- `composer run dev` — start the configured local development processes.
