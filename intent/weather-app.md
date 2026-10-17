# Intent — Weather App

## Overview
A simple web application that displays the current weather and a multi-day forecast. No user accounts or login are required. Users can either search for a specific city/region or allow the app to detect their current location and show the weather immediately.

## Target Audience
Anyone who wants to quickly check the weather — whether in their own city, while traveling, or for any other location. It is a general-purpose application with no specific user segment.

## Core Features (Must-have)

### Current Weather
- Current temperature
- Weather description (sunny, cloudy, rainy, etc.)
- Weather icon
- Humidity
- Wind speed

### 5-Day Forecast
- Daily summary: high and low temperature
- Weather condition for each day

## Nice-to-have Features (Can be added later)
- Atmospheric pressure and visibility
- Hourly forecast
- Simple charts
- Severe weather alerts

## Technology Stack

| Layer      | Technology                                   |
|------------|----------------------------------------------|
| Backend    | Laravel (PHP)                                |
| Frontend   | Blade templates                              |
| Styling    | Tailwind CSS (no Bootstrap)                  |
| JavaScript | Simple — for Geolocation and interactivity   |
| API        | OpenWeatherMap                               |

## Geolocation
- Use the **Browser Geolocation API** (JavaScript)
- Request permission from the user to access their location
- Send latitude/longitude coordinates to the backend to fetch weather data
- **Fallback:** If the user denies permission or location cannot be determined → fall back to manual city/region search
- Do **not** use IP-based geolocation

## API Key
- Store the OpenWeatherMap API key in the `.env` file as an environment variable
- Never hardcode the key in the source code
- The user will create their own API key on the OpenWeatherMap website

## Language & Interface
- Support both **Arabic** and **English**
- Ability to switch between the two languages
- Full RTL support for Arabic

## Local Storage
- No local storage (no localStorage)
- Keep the application as simple as possible — no search history

## Design
- Clean and minimalist design
- Focus on clarity and ease of use
- Soft weather-inspired color palette (light blues, whites, soft grays)
- No specific logo or app name required — a simple name can be suggested
- No particular reference apps — follow modern best practices for weather interfaces using Tailwind CSS

## Additional Decisions
- On weather data fetch failure, display a clear error message within the same page (no separate error page)
- Temperature is displayed in **Celsius only**
- Single-page application only (no About, Contact, or other pages)

## Open Questions
- None

**Status:** Approved