@props([
    'code' => '01d',
    'hero' => false,
    'class' => '',
])

@php
    $c = (string) $code;
    $isClear = in_array($c, ['01d', '01n', 'clear', 'sunny'], true);
    $isPartlyCloudy = in_array($c, ['02d', '02n', 'partly_cloudy'], true);
    $isCloudy = in_array($c, ['03d', '03n', '04d', '04n', 'cloudy'], true);
    $isRain = in_array($c, ['09d', '09n', '10d', '10n', '11d', '11n', 'rain', 'light_rain'], true);
    $isSnow = in_array($c, ['13d', '13n', 'snow'], true);
    $isMist = in_array($c, ['50d', '50n', 'mist', 'fog'], true);

    // If none matched, default based on partly cloudy or clear
    if (! $isClear && ! $isPartlyCloudy && ! $isCloudy && ! $isRain && ! $isSnow && ! $isMist) {
        $isPartlyCloudy = true;
    }
@endphp

@if($hero)
    {{-- Large atmospheric hero illustration with animated Sun & Cloud layers --}}
    <div class="relative flex items-center justify-center {{ $class }}" aria-hidden="true">
        @if($isClear)
            {{-- Pure Sun Hero --}}
            <svg class="size-48 sm:size-64 animate-sun-scale drop-shadow-md" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="88" fill="#EFB88B" fill-opacity="0.08"/>
                <circle cx="100" cy="100" r="74" fill="#EFB88B" fill-opacity="0.12"/>
                <circle cx="100" cy="100" r="58" fill="url(#hero_sun_grad)"/>
                <circle cx="100" cy="100" r="87.5" stroke="#D4AA7B" stroke-opacity="0.22"/>
                <defs>
                    <linearGradient id="hero_sun_grad" x1="60" y1="50" x2="140" y2="150" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#F9DEC0"/>
                        <stop offset="0.6" stop-color="#F3C79D"/>
                        <stop offset="1" stop-color="#E9A873"/>
                    </linearGradient>
                </defs>
            </svg>
        @elseif($isCloudy)
            {{-- Pure Cloud Hero --}}
            <svg class="size-52 sm:size-72 animate-cloud-drift drop-shadow-lg" viewBox="0 0 280 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M53.2 142.4C53.2 99.7 120.4 92.6 142.8 131.7" stroke="white" stroke-width="3.36" stroke-linecap="round" stroke-opacity="0.63"/>
                <path d="M65.5 178.4C7.8 178.4 5 98.9 54.3 95.9C51.5 47.4 138.8 44.4 150 93C183.8 69.4 220.4 93 220.4 122.5C271.1 113.6 282.4 178.4 223.3 178.4H65.5Z" fill="url(#hero_cloud_grad)"/>
                <defs>
                    <linearGradient id="hero_cloud_grad" x1="117" y1="58" x2="117" y2="178" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#FEFEF9"/>
                        <stop offset="0.52" stop-color="#E9EEE3"/>
                        <stop offset="1" stop-color="#BFCCB9"/>
                    </linearGradient>
                </defs>
            </svg>
        @elseif($isRain)
            {{-- Rain Cloud Hero --}}
            <div class="relative size-52 sm:size-72">
                <svg class="absolute inset-0 size-full animate-cloud-drift drop-shadow-lg" viewBox="0 0 280 220" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.65">
                        <line x1="165" y1="185" x2="148" y2="216" stroke="#405F4C" stroke-width="4" stroke-linecap="round"/>
                        <line x1="130" y1="185" x2="113" y2="216" stroke="#405F4C" stroke-width="4" stroke-linecap="round"/>
                        <line x1="95" y1="185" x2="78" y2="216" stroke="#405F4C" stroke-width="4" stroke-linecap="round"/>
                    </g>
                    <path d="M53.2 132.4C53.2 89.7 120.4 82.6 142.8 121.7" stroke="white" stroke-width="3.36" stroke-linecap="round" stroke-opacity="0.63"/>
                    <path d="M65.5 168.4C7.8 168.4 5 88.9 54.3 85.9C51.5 37.4 138.8 34.4 150 83C183.8 59.4 220.4 83 220.4 112.5C271.1 103.6 282.4 168.4 223.3 168.4H65.5Z" fill="url(#hero_rain_cloud_grad)"/>
                    <defs>
                        <linearGradient id="hero_rain_cloud_grad" x1="117" y1="48" x2="117" y2="168" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FEFEF9"/>
                            <stop offset="0.52" stop-color="#E2E7DE"/>
                            <stop offset="1" stop-color="#A8B7A4"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        @else
            {{-- Default Partly Cloudy (Sun behind Cloud) as in Figma 3:9734 --}}
            <div class="relative size-56 sm:size-72">
                {{-- Sun Layer (Scales continuously) --}}
                <div class="absolute right-2 sm:right-6 top-1 sm:top-2 animate-sun-scale">
                    <svg class="size-36 sm:size-48" viewBox="0 0 182 182" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="91" cy="91" r="91" fill="#EFB88B" fill-opacity="0.07"/>
                        <circle cx="91" cy="91" r="75.6" fill="#EFB88B" fill-opacity="0.094"/>
                        <circle cx="91" cy="91" r="58.8" fill="url(#hero_partly_sun)"/>
                        <circle cx="91" cy="91" r="90.5" stroke="#D4AA7B" stroke-opacity="0.22"/>
                        <defs>
                            <linearGradient id="hero_partly_sun" x1="44" y1="32" x2="138" y2="150" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#F9DEC0"/>
                                <stop offset="0.6" stop-color="#F3C79D"/>
                                <stop offset="1" stop-color="#E9A873"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                {{-- Cloud Layer (Drifts horizontally) --}}
                <div class="absolute left-0 bottom-2 sm:bottom-4 animate-cloud-drift">
                    <svg class="w-56 sm:w-72 drop-shadow-lg" viewBox="0 0 283 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M53.2 87.2C53.2 44.5 120.4 37.4 142.8 76.5" stroke="white" stroke-width="3.36" stroke-linecap="round" stroke-opacity="0.63"/>
                        <path d="M65.5 143.2C7.8 143.2 5 63.7 54.3 60.7C51.5 12.2 138.8 9.2 150 57.8C183.8 34.2 220.4 57.8 220.4 87.2C271.1 78.4 282.4 143.2 223.3 143.2H65.5Z" fill="url(#hero_partly_cloud)"/>
                        <defs>
                            <linearGradient id="hero_partly_cloud" x1="117" y1="23" x2="117" y2="143" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FEFEF9"/>
                                <stop offset="0.52" stop-color="#E9EEE3"/>
                                <stop offset="1" stop-color="#BFCCB9"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
        @endif
    </div>
@else
    {{-- Daily Forecast Card Weather Symbol (48x48 on mobile, 72x72 on desktop) --}}
    <div class="relative flex items-center justify-center {{ $class }}" aria-hidden="true">
        @if($isClear)
            {{-- Sunny Day Icon (Figma 3:9769) --}}
            <svg class="size-12 sm:size-16" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="35" cy="31" r="23.4" fill="#EFB88B" fill-opacity="0.07"/>
                <circle cx="35" cy="31" r="19.4" fill="#EFB88B" fill-opacity="0.094"/>
                <circle cx="35" cy="31" r="15.1" fill="url(#day_sun_{{ $c }})"/>
                <circle cx="35" cy="31" r="22.9" stroke="#D4AA7B" stroke-opacity="0.22"/>
                <defs>
                    <linearGradient id="day_sun_{{ $c }}" x1="22.8" y1="15.5" x2="22.8" y2="45.7" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#F9DEC0"/>
                        <stop offset="0.6" stop-color="#F3C79D"/>
                        <stop offset="1" stop-color="#E9A873"/>
                    </linearGradient>
                </defs>
            </svg>
        @elseif($isCloudy)
            {{-- Cloudy Day Icon (Figma 3:9786) --}}
            <svg class="size-12 sm:size-16" viewBox="0 0 75 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M13.7 41.8C13.7 30.8 31 29 36.7 39" stroke="white" stroke-width="0.86" stroke-linecap="round" stroke-opacity="0.63"/>
                <path d="M16.9 56.2C2 56.2 1.3 35.7 14 35C13.2 22.5 35.7 21.7 38.6 34.2C47.3 28.1 56.7 34.2 56.7 41.8C69.7 39.5 72.6 56.2 57.4 56.2H16.9Z" fill="url(#day_cloud_{{ $c }})"/>
                <defs>
                    <linearGradient id="day_cloud_{{ $c }}" x1="30.1" y1="25.2" x2="30.1" y2="56.2" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#FEFEF9"/>
                        <stop offset="0.52" stop-color="#E9EEE3"/>
                        <stop offset="1" stop-color="#BFCCB9"/>
                    </linearGradient>
                </defs>
            </svg>
        @elseif($isRain)
            {{-- Light Rain Day Icon (Figma 3:9802) --}}
            <svg class="size-12 sm:size-16" viewBox="0 0 75 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g opacity="0.64">
                    <path d="M47.5 59.8L42.5 69.1" stroke="#405F4C" stroke-width="2.52" stroke-linecap="round"/>
                    <path d="M35.3 59.8L30.2 69.1" stroke="#405F4C" stroke-width="2.52" stroke-linecap="round"/>
                    <path d="M23 59.8L18 69.1" stroke="#405F4C" stroke-width="2.52" stroke-linecap="round"/>
                </g>
                <path d="M13.7 41.8C13.7 30.8 31 29 36.7 39" stroke="white" stroke-width="0.86" stroke-linecap="round" stroke-opacity="0.63"/>
                <path d="M16.9 56.2C2 56.2 1.3 35.7 14 35C13.2 22.5 35.7 21.7 38.6 34.2C47.3 28.1 56.7 34.2 56.7 41.8C69.7 39.5 72.6 56.2 57.4 56.2H16.9Z" fill="url(#day_rain_{{ $c }})"/>
                <defs>
                    <linearGradient id="day_rain_{{ $c }}" x1="30.1" y1="25.2" x2="30.1" y2="56.2" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#FEFEF9"/>
                        <stop offset="0.52" stop-color="#E9EEE3"/>
                        <stop offset="1" stop-color="#BFCCB9"/>
                    </linearGradient>
                </defs>
            </svg>
        @else
            {{-- Partly Cloudy Day Icon (Figma 3:9749) --}}
            <svg class="size-12 sm:size-16" viewBox="0 0 75 72" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="51.5" cy="30.6" r="23.4" fill="#EFB88B" fill-opacity="0.07"/>
                <circle cx="51.5" cy="30.6" r="19.4" fill="#EFB88B" fill-opacity="0.094"/>
                <circle cx="51.5" cy="30.6" r="15.1" fill="url(#day_partly_sun_{{ $c }})"/>
                <circle cx="51.5" cy="30.6" r="22.9" stroke="#D4AA7B" stroke-opacity="0.22"/>
                <path d="M13.7 41.8C13.7 30.8 31 29 36.7 39" stroke="white" stroke-width="0.86" stroke-linecap="round" stroke-opacity="0.63"/>
                <path d="M16.9 56.2C2 56.2 1.3 35.7 14 35C13.2 22.5 35.7 21.7 38.6 34.2C47.3 28.1 56.7 34.2 56.7 41.8C69.7 39.5 72.6 56.2 57.4 56.2H16.9Z" fill="url(#day_partly_cloud_{{ $c }})"/>
                <defs>
                    <linearGradient id="day_partly_sun_{{ $c }}" x1="39.4" y1="15.5" x2="39.4" y2="45.7" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#F9DEC0"/>
                        <stop offset="0.6" stop-color="#F3C79D"/>
                        <stop offset="1" stop-color="#E9A873"/>
                    </linearGradient>
                    <linearGradient id="day_partly_cloud_{{ $c }}" x1="30.1" y1="25.2" x2="30.1" y2="56.2" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#FEFEF9"/>
                        <stop offset="0.52" stop-color="#E9EEE3"/>
                        <stop offset="1" stop-color="#BFCCB9"/>
                    </linearGradient>
                </defs>
            </svg>
        @endif
    </div>
@endif
