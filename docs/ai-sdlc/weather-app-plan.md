# Implementation Plan: Weather App

## Status
- Source intent: `intent/weather-app.md`
- Source PRD: `docs/PRD-weather-app.md`
- Approval: Approved

## Goal and Scope

Build a single-page Laravel weather application that displays current weather and a 5-day forecast for any city or region worldwide. The app uses the OpenWeatherMap API, supports browser geolocation with manual search fallback, and provides a bilingual (Arabic/English) interface with RTL support.

**In scope:**
- Current weather display (temperature, description, icon, humidity, wind speed)
- 5-day daily forecast (high/low temperature, weather condition, icon)
- Manual city/region search
- Browser Geolocation API with fallback to manual search
- Arabic/English language switcher with full RTL support
- Celsius-only temperatures
- Inline error handling (no separate error page)
- Clean, minimalist Tailwind CSS design

**Explicit exclusions:**
- User accounts, authentication, or profiles
- Search history or localStorage
- Hourly forecast
- Atmospheric pressure and visibility
- Charts or graphs
- Severe weather alerts
- IP-based geolocation
- About/Contact pages
- Fahrenheit option

## Open Questions and Constraints

**Constraints:**
- OpenWeatherMap free tier: 60 calls/minute — sufficient for expected traffic
- API key must be stored in `.env` as `OPENWEATHERMAP_API_KEY`
- No hardcoded API keys anywhere in source code
- Single-page application only

**Assumptions:**
- User will create their own OpenWeatherMap API key
- Browser Geolocation API is available in target browsers
- Modern evergreen browsers only (Chrome, Firefox, Safari, Edge — latest 2 versions)

**None blocking.**

## Implementation Steps

### Step 1: Environment Configuration
**Purpose:** Add OpenWeatherMap API key configuration to the Laravel app.

**Files:**
- `.env` — add `OPENWEATHERMAP_API_KEY=your_key_here`
- `.env.example` — add `OPENWEATHERMAP_API_KEY=`
- `config/services.php` — add OpenWeatherMap service config

**Completion check:** `config('services.openweathermap.api_key')` returns the key from `.env`.

---

### Step 2: Create WeatherService
**Purpose:** Encapsulate all OpenWeatherMap API interactions in a dedicated service class.

**Files:**
- `app/Services/WeatherService.php` — new service class

**Responsibilities:**
- `getCurrentWeather(string $city): array` — fetch current weather by city name
- `getCurrentWeatherByCoords(float $lat, float $lon): array` — fetch current weather by coordinates
- `getForecast(string $city): array` — fetch 5-day forecast by city name
- `getForecastByCoords(float $lat, float $lon): array` — fetch 5-day forecast by coordinates
- Handle API errors and throw meaningful exceptions
- Use Laravel's HTTP client with timeout and retry

**Completion check:** Service can fetch data from OpenWeatherMap API and return structured arrays.

---

### Step 3: Create WeatherController
**Purpose:** Handle HTTP requests and coordinate between the service and the view.

**Files:**
- `app/Http/Controllers/WeatherController.php` — new controller

**Responsibilities:**
- `index()` — render the main page (empty initial state)
- `search(Request $request)` — handle city search, validate input, call service, return view with data
- `locate(Request $request)` — handle geolocation coordinates, call service, return view with data
- Handle exceptions and pass error messages to the view

**Completion check:** Controller validates input, calls the service, and passes data (or errors) to the view.

---

### Step 4: Define Routes
**Purpose:** Register web routes for the weather app.

**Files:**
- `routes/web.php` — add routes

**Routes:**
- `GET /` — main page (WeatherController@index)
- `POST /weather/search` — city search (WeatherController@search)
- `POST /weather/locate` — geolocation (WeatherController@locate)

**Completion check:** All three routes are registered and accessible.

---

### Step 5: Create Blade Layout
**Purpose:** Base layout with Tailwind CSS, language switcher, and RTL support.

**Files:**
- `resources/views/layouts/app.blade.php` — new layout template

**Responsibilities:**
- Include Tailwind CSS (via Vite or CDN)
- Language switcher component (toggles between `en` and `ar`)
- Set `dir` attribute dynamically based on locale (`rtl` for Arabic, `ltr` for English)
- Set `lang` attribute dynamically
- Minimalist design with weather-inspired colors (light blues, whites, soft grays)
- Page title and meta tags

**Completion check:** Layout renders correctly with proper `dir` and `lang` attributes for both languages.

---

### Step 6: Create Weather Page (Main View)
**Purpose:** Single-page weather display with search, geolocation, current weather, and forecast.

**Files:**
- `resources/views/weather/index.blade.php` — main weather page

**Responsibilities:**
- Search form (text input + search button)
- Geolocation button (triggers browser Geolocation API)
- Current weather section (temperature, description, icon, humidity, wind speed, location name)
- 5-day forecast section (day name, high/low temp, weather icon, condition)
- Error message display (inline, no redirect)
- Responsive grid layout for forecast days
- Loading state while fetching data

**Completion check:** Page displays all weather data correctly and handles empty/error states.

---

### Step 7: Add JavaScript for Geolocation
**Purpose:** Handle browser Geolocation API interaction.

**Files:**
- `resources/js/weather.js` — new JavaScript file (or inline in Blade)

**Responsibilities:**
- Geolocation button click handler
- Call `navigator.geolocation.getCurrentPosition()`
- On success: send coordinates to `/weather/locate` via form submission or fetch
- On error/denial: show message prompting manual search
- Loading state during geolocation request

**Completion check:** Geolocation button requests permission, sends coordinates on success, and falls back gracefully on denial.

---

### Step 8: Create Language Files
**Purpose:** Translation strings for Arabic and English.

**Files:**
- `lang/en/weather.php` — English translations
- `lang/ar/weather.php` — Arabic translations

**Translation keys:**
- App title, search placeholder, search button, geolocation button
- Weather labels (temperature, humidity, wind speed, forecast)
- Error messages (location not found, API failure, geolocation denied)
- Day names (Monday–Sunday)

**Completion check:** All UI text is translatable and displays correctly in both languages.

---

### Step 9: Set Up Tailwind CSS
**Purpose:** Install and configure Tailwind CSS for styling.

**Files:**
- `package.json` — add Tailwind dependency
- `vite.config.js` — add Tailwind plugin
- `resources/css/app.css` — Tailwind directives
- `tailwind.config.js` — custom weather color palette (optional)

**Completion check:** Tailwind classes work in Blade templates and styles are applied correctly.

---

### Step 10: Configure Vite
**Purpose:** Set up Vite for asset compilation.

**Files:**
- `vite.config.js` — ensure CSS and JS entry points are configured

**Completion check:** `npm run build` compiles assets successfully.

---

## Acceptance Criteria and Test Mapping

| Acceptance criterion | Planned change | Test or verification |
|---|---|---|
| AC-1: User can search for any city and see current weather + 5-day forecast | WeatherService + WeatherController + weather/index.blade.php | Feature test: `WeatherSearchTest` — submit city name, assert response contains temperature and forecast |
| AC-2: User can detect location via browser geolocation | WeatherController@locate + weather.js | Feature test: `WeatherGeolocationTest` — mock coordinates, assert response contains weather data |
| AC-3: Interface is bilingual with correct RTL | Language files + layout + weather page | Feature test: `WeatherLanguageTest` — switch language, assert translated text and `dir` attribute |
| AC-4: Error states handled with inline messages | WeatherController + weather/index.blade.php | Feature test: `WeatherErrorTest` — invalid city, assert error message in response |
| AC-5: No user registration or login | N/A (no auth routes) | Manual verification: no auth middleware on routes |
| AC-6: UI responsive on mobile/tablet/desktop | Tailwind responsive classes | Manual verification: test on multiple viewport sizes |
| AC-7: API key stored in .env, never exposed | config/services.php + .env | Code review: no hardcoded keys; `config('services.openweathermap.api_key')` works |

---

## Test List

### Existing tests to run
```bash
php artisan test
```

### Proposed tests to add

| Test file | Test name | Purpose |
|---|---|---|
| `tests/Feature/WeatherSearchTest.php` | `test_search_returns_weather_for_valid_city` | Verify city search returns weather data |
| `tests/Feature/WeatherSearchTest.php` | `test_search_shows_error_for_invalid_city` | Verify error message for invalid city |
| `tests/Feature/WeatherSearchTest.php` | `test_search_requires_city_input` | Verify validation for empty input |
| `tests/Feature/WeatherGeolocationTest.php` | `test_locate_returns_weather_for_valid_coordinates` | Verify geolocation returns weather data |
| `tests/Feature/WeatherGeolocationTest.php` | `test_locate_shows_error_for_invalid_coordinates` | Verify error for invalid coordinates |
| `tests/Feature/WeatherLanguageTest.php` | `test_english_locale_displays_english_text` | Verify English translations |
| `tests/Feature/WeatherLanguageTest.php` | `test_arabic_locale_displays_arabic_text_and_rtl` | Verify Arabic translations and RTL |
| `tests/Feature/WeatherPageTest.php` | `test_weather_page_loads_successfully` | Verify main page loads |
| `tests/Unit/WeatherServiceTest.php` | `test_service_fetches_current_weather` | Verify WeatherService API call |
| `tests/Unit/WeatherServiceTest.php` | `test_service_fetches_forecast` | Verify WeatherService forecast call |

### Running tests
```bash
php artisan test --filter=WeatherSearchTest
php artisan test --filter=WeatherGeolocationTest
php artisan test --filter=WeatherLanguageTest
php artisan test --filter=WeatherPageTest
php artisan test --filter=WeatherServiceTest
```

---

## Rollback and Recovery

Not applicable. This is a new feature with no existing data or migrations to roll back. If issues arise, the weather routes and controller can be removed without affecting the rest of the application.

---

## ADR Draft

No ADR required. All architectural decisions are straightforward and follow standard Laravel conventions. The only decision worth noting is using a dedicated `WeatherService` class to encapsulate API logic, which follows the existing Laravel service pattern and keeps the controller thin.
