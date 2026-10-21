@extends('layouts.app')

@section('content')
<div class="space-y-8 sm:space-y-10">
    {{-- Search section matching Figma 3:9699 (Desktop) & 3:9847 (Mobile) --}}
    <section class="animate-entrance delay-90 w-full" aria-labelledby="search-label">
        <label id="search-label" for="city" class="block text-sm font-bold text-[#283D34] mb-2 sm:mb-2.5">
            {{ __('weather.search_label') }}
        </label>

        {{-- Single responsive form controls: Row 1 [Input + Search] on mobile, single flex row on desktop --}}
        <div class="space-y-3">
            <form id="search-form" method="POST" action="{{ route('weather.search') }}" class="w-full">
                @csrf
                <input type="hidden" name="lang" value="{{ $language }}">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    {{-- Row 1 on mobile: [ City Input ] [ Search Button ] | Single flex row on desktop --}}
                    <div class="flex flex-1 items-center gap-2.5 sm:gap-3">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4" aria-hidden="true">
                                <svg class="size-5 {{ $error ? 'stroke-[#923E30]' : 'stroke-[#606B61]' }}" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.5 17.5L13.88 13.88M15.83 9.17A6.67 6.67 0 1 1 2.5 9.17a6.67 6.67 0 0 1 13.33 0Z" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <input
                                id="city"
                                name="city"
                                type="search"
                                value="{{ $cityQuery }}"
                                placeholder="{{ __('weather.search_placeholder') }}"
                                maxlength="100"
                                autocomplete="off"
                                required
                                @class([
                                    'w-full h-[52px] rounded-xl bg-[#FDFCF8] px-4 ps-11 text-base text-[#283D34] placeholder:text-[#606B61] transition outline-none',
                                    'border border-[#923E30] focus:ring-2 focus:ring-[#923E30]/20' => $error,
                                    'border border-[#D6DCD0] focus:border-[#6B8060] focus:ring-2 focus:ring-[#6B8060]/20' => ! $error,
                                ])
                                aria-invalid="{{ $error ? 'true' : 'false' }}"
                                @if($error) aria-describedby="search-error" @endif
                            >
                        </div>

                        {{-- Search button: next to input on mobile & desktop --}}
                        <button
                            id="search-button"
                            type="submit"
                            data-searching="{{ __('weather.searching') }}"
                            data-default-label="{{ __('weather.search_button') }}"
                            class="inline-flex h-[52px] min-w-[96px] sm:min-w-[124px] cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#405F4C] px-5 sm:px-6 text-[15px] font-bold text-[#FDFCF8] transition hover:bg-[#2F493B] active:bg-[#283D34] outline-none focus-visible:ring-2 focus-visible:ring-[#6B8060] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-[#647262]"
                        >
                            <svg id="search-spinner" class="hidden size-4.5 animate-spinner stroke-white" viewBox="0 0 24 24" fill="none" stroke-width="2.5" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span id="search-label-text">{{ __('weather.search_button') }}</span>
                        </button>
                    </div>

                    {{-- Use My Location: row 2 on mobile (w-full), inline on desktop (sm:w-auto) --}}
                    <button
                        id="locate-button"
                        type="button"
                        data-locating="{{ __('weather.locating') }}"
                        data-default-label="{{ __('weather.locate_button') }}"
                        data-unavailable="{{ __('weather.location_unavailable') }}"
                        data-denied="{{ __('weather.location_denied') }}"
                        class="inline-flex h-[52px] w-full sm:w-auto sm:min-w-[212px] cursor-pointer items-center justify-center gap-2.5 rounded-xl border border-[#D6DCD0] bg-[#FDFCF8] px-5 text-[15px] font-bold text-[#405F4C] transition hover:bg-[#E8EDE3] active:bg-[#DDE5D6] outline-none focus-visible:ring-2 focus-visible:ring-[#6B8060] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg class="size-5 stroke-[#405F4C]" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M1.67 10h2.5M1.67 10a8.33 8.33 0 1 0 16.66 0M1.67 10a8.33 8.33 0 0 1 16.66 0M10 18.33v-2.5M10 1.67v2.5M18.33 10h-2.5M12.5 10a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        <span id="locate-label-text">{{ __('weather.locate_button') }}</span>
                    </button>
                </div>
            </form>

            {{-- Hidden location submit form for geolocation requests --}}
            <form id="location-form" method="POST" action="{{ route('weather.locate') }}" class="hidden">
                @csrf
                <input type="hidden" name="lang" value="{{ $language }}">
                <input id="latitude" type="hidden" name="latitude">
                <input id="longitude" type="hidden" name="longitude">
            </form>

            {{-- Error state: Placed directly below Search as specified in Figma 3:10278 --}}
            @if($error)
                <div id="search-error" class="flex items-center gap-2 pt-1 text-sm font-medium text-[#923E30]" role="alert">
                    <svg class="size-4 shrink-0 stroke-[#923E30]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M8 5.33V8M8 10.67h.01M14.67 8A6.67 6.67 0 1 1 1.33 8a6.67 6.67 0 0 1 13.34 0Z" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                    <span>{{ $error }}</span>
                </div>
            @endif

            {{-- Dynamic Geolocation status/error message --}}
            <div id="location-status" class="hidden items-center gap-2 pt-1 text-sm font-medium text-[#606B61]" role="status" aria-live="polite"></div>
        </div>
    </section>

    {{-- Interactive Loading State (Skeleton UI matching Figma 3:10368) --}}
    <div id="weather-loading-skeleton" class="hidden space-y-8 sm:space-y-10" aria-busy="true" aria-label="{{ __('weather.searching') }}">
        {{-- Skeleton for Current Weather card: 1:1 reserved height 449px (desktop) / 344px (mobile) --}}
        <div class="relative overflow-hidden rounded-[24px] bg-gradient-to-br from-[#EDF0E8] to-[#E2E9DA] p-6 sm:p-9 shadow-xs min-h-[344px] sm:min-h-[449px] flex flex-col justify-between">
            <div class="space-y-3">
                <div class="h-3.5 w-28 rounded-full bg-[#BFCCB8]/60 animate-skeleton"></div>
                <div class="h-7 w-64 rounded-full bg-[#BFCCB8]/70 animate-skeleton"></div>
            </div>

            <div class="my-6 flex items-center justify-between">
                <div class="space-y-3">
                    <div class="h-24 sm:h-32 w-44 sm:w-60 rounded-2xl bg-[#BFCCB8]/60 animate-skeleton"></div>
                    <div class="h-4 w-32 rounded-full bg-[#BFCCB8]/60 animate-skeleton"></div>
                </div>
                <div class="size-28 sm:size-48 rounded-full bg-[#BFCCB8]/50 animate-skeleton"></div>
            </div>

            <div class="border-t border-[#BFCCB8] pt-4 flex gap-8">
                <div class="h-10 w-28 rounded-xl bg-[#BFCCB8]/50 animate-skeleton"></div>
                <div class="h-10 w-28 rounded-xl bg-[#BFCCB8]/50 animate-skeleton"></div>
            </div>
        </div>

        {{-- Skeleton for 5-day Forecast --}}
        <div>
            <div class="h-8 w-48 rounded-full bg-[#D6DCD0]/70 mb-4 animate-skeleton"></div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-5 sm:gap-4">
                @for($i = 0; $i < 5; $i++)
                    <div class="h-[92px] sm:h-[264px] rounded-2xl border border-[#D6DCD0] bg-[#FDFCF8] p-4 flex sm:flex-col items-center justify-between animate-skeleton">
                        <div class="h-4 w-16 rounded-full bg-[#D6DCD0]/80"></div>
                        <div class="size-12 rounded-full bg-[#D6DCD0]/60"></div>
                        <div class="h-3.5 w-20 rounded-full bg-[#D6DCD0]/60 hidden sm:block"></div>
                        <div class="h-6 w-16 rounded-full bg-[#D6DCD0]/80"></div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Weather Content (when loaded) --}}
    <div id="weather-content">
        @if($weather)
            <div class="space-y-8 sm:space-y-10">
                {{-- Current Weather Card matching Figma 3:9710 (Desktop) & 3:9858 (Mobile) --}}
                <section class="animate-entrance delay-180 relative overflow-hidden rounded-[24px] bg-gradient-to-br from-[#EDF0E8] to-[#E2E9DA] p-6 sm:p-9 shadow-xs" aria-labelledby="current-heading">
                    <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
                        {{-- Conditions text & metrics --}}
                        <div class="flex-1 space-y-4 sm:space-y-6">
                            {{-- Location header --}}
                            <div>
                                <p class="text-[13px] font-normal leading-tight text-[#606B61]">{{ __('weather.current_heading') }}</p>
                                <h2 id="current-heading" class="mt-1 text-2xl sm:text-[27px] font-medium leading-snug tracking-tight text-[#283D34]">
                                    {{ __('weather.today_in', ['location' => trim($weather['current']['city'].' '.$weather['current']['country'])]) }}
                                </h2>
                            </div>

                            {{-- Temperature reading & Condition --}}
                            <div class="flex flex-col">
                                <div class="inline-flex items-baseline gap-1">
                                    <span class="text-7xl sm:text-[148px] font-normal leading-none tracking-tight text-[#283D34]">{{ round($weather['current']['temperature']) }}</span>
                                    <span class="text-3xl sm:text-[30px] font-normal text-[#283D34] align-top select-none">{{ __('weather.celsius') }}</span>
                                </div>
                                <p class="mt-1 sm:mt-2 text-base sm:text-lg font-normal capitalize text-[#606B61]">
                                    {{ $weather['current']['description'] }}
                                </p>
                            </div>

                            {{-- Weather details (Humidity & Wind) with exact 1px top border --}}
                            <dl class="flex items-center gap-8 border-t border-[#BFCCB8] pt-4.5">
                                {{-- Humidity --}}
                                <div class="flex items-center gap-2.5">
                                    <svg class="size-5 shrink-0 stroke-[#405F4C]" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M10.47 5.5a9.2 9.2 0 0 1 1.2-3c.42 2.08 1.67 4.08 3.33 5.42 1.67 1.33 2.5 2.92 2.5 4.58a5.9 5.9 0 0 1-.97 3.24 6.04 6.04 0 0 1-6 2.49 6.2 6.2 0 0 1-2.96-1.57M5.83 13.58a3.34 3.34 0 0 0 3.34-3.37c0-.97-.48-1.89-1.43-2.66-.95-.78-1.67-1.93-1.91-3.14-.24 1.21-.95 2.37-1.91 3.14-.96.77-1.42 1.7-1.42 2.66a3.34 3.34 0 0 0 3.33 3.37Z" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                    <div>
                                        <dt class="text-xs font-normal text-[#606B61] leading-none">{{ __('weather.humidity') }}</dt>
                                        <dd class="mt-1 text-[17px] font-medium leading-none text-[#283D34]">
                                            {{ $weather['current']['humidity'] }}{{ __('weather.percent') }}
                                        </dd>
                                    </div>
                                </div>

                                {{-- Wind Speed --}}
                                <div class="flex items-center gap-2.5">
                                    <svg class="size-5 shrink-0 stroke-[#405F4C]" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M10.67 16.33a1.9 1.9 0 0 0 2.35-.8 1.8 1.8 0 0 0-.27-2.2H1.67M14.58 6.67a2.5 2.5 0 0 1 3.75 2.34 2.5 2.5 0 0 1-2.08 2.32H1.67M8.17 3.67a1.9 1.9 0 0 1 2.35.8 1.8 1.8 0 0 1-.27 2.2H1.67" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                    <div>
                                        <dt class="text-xs font-normal text-[#606B61] leading-none">{{ __('weather.wind_speed') }}</dt>
                                        <dd class="mt-1 text-[17px] font-medium leading-none text-[#283D34]">
                                            {{ number_format($weather['current']['wind_speed'], 1) }} <span class="text-xs font-normal text-[#606B61]">{{ __('weather.wind_unit') }}</span>
                                        </dd>
                                    </div>
                                </div>
                            </dl>
                        </div>

                        {{-- Atmospheric Illustration on the right (Desktop) or side (Mobile) --}}
                        <div class="flex items-center justify-center sm:pe-4">
                            <x-weather-icon :code="$weather['current']['icon']" :hero="true" />
                        </div>
                    </div>
                </section>

                {{-- Five-Day Forecast Section matching Figma 3:9742 (Desktop) & 3:9890 (Mobile) --}}
                <section class="animate-entrance delay-300" aria-labelledby="forecast-heading">
                    <h2 id="forecast-heading" class="text-2xl sm:text-[30px] font-normal tracking-tight text-[#283D34] mb-4">
                        {{ __('weather.forecast_heading') }}
                    </h2>

                    {{-- Five equal read-only columns on Desktop, five rows on Mobile (no lift on forecast cards) --}}
                    <div class="flex flex-col gap-3 sm:grid sm:grid-cols-5 sm:gap-4">
                        @foreach($weather['forecast'] as $index => $day)
                            @php
                                $forecastDate = \Carbon\CarbonImmutable::parse($day['date'])->locale($language);
                                $dayName = $forecastDate->format('l');
                                $delayClass = match($index) {
                                    0 => 'delay-380',
                                    1 => 'delay-460',
                                    2 => 'delay-540',
                                    3 => 'delay-620',
                                    default => 'delay-700',
                                };
                            @endphp
                            <article class="animate-entrance {{ $delayClass }} rounded-2xl border border-[#D6DCD0] bg-[#FDFCF8] p-4 sm:p-5 text-start sm:text-center transition duration-200">
                                {{-- Mobile horizontal row vs Desktop vertical stack --}}
                                <div class="flex items-center justify-between sm:flex-col sm:justify-start sm:gap-2">
                                    {{-- Day Name & Condition --}}
                                    <div class="sm:mb-1">
                                        <h3 class="text-base font-bold text-[#283D34]">{{ __('weather.days.'.$dayName) }}</h3>
                                        <p class="text-xs text-[#606B61] capitalize sm:hidden">{{ $day['description'] }}</p>
                                    </div>

                                    {{-- Weather Icon --}}
                                    <div class="my-0 sm:my-2">
                                        <x-weather-icon :code="$day['icon']" />
                                    </div>

                                    {{-- Condition (Desktop only) --}}
                                    <p class="hidden sm:block min-h-5 text-sm font-normal text-[#606B61] capitalize">
                                        {{ $day['description'] }}
                                    </p>

                                    {{-- High & Low Temperature Range --}}
                                    <div class="flex items-center gap-3 sm:mt-3 sm:justify-center sm:gap-4">
                                        {{-- High --}}
                                        <div class="text-end sm:text-center">
                                            <span class="sr-only">{{ __('weather.high') }}</span>
                                            <span class="block text-[11px] text-[#606B61] font-normal leading-none sm:mb-1">{{ __('weather.high') }}</span>
                                            <span class="text-xl sm:text-[30px] font-normal leading-none text-[#283D34]">
                                                {{ round($day['high']) }}°<span class="text-xs sm:text-sm">C</span>
                                            </span>
                                        </div>

                                        {{-- Low --}}
                                        <div class="text-end sm:text-center">
                                            <span class="sr-only">{{ __('weather.low') }}</span>
                                            <span class="block text-[11px] text-[#606B61] font-normal leading-none sm:mb-1">{{ __('weather.low') }}</span>
                                            <span class="text-xl sm:text-[30px] font-normal leading-none text-[#606B61]">
                                                {{ round($day['low']) }}°<span class="text-xs sm:text-sm">C</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>
        @else
            {{-- Initial / Empty State matching Figma aesthetic --}}
            <section class="animate-entrance delay-180 mx-auto mt-6 max-w-xl rounded-[24px] border border-[#D6DCD0] bg-[#FDFCF8] p-8 sm:p-12 text-center shadow-xs" aria-label="{{ __('weather.search_heading') }}">
                <div class="mx-auto flex size-20 items-center justify-center rounded-full bg-[#E8EDE3]/80 text-[#405F4C]" aria-hidden="true">
                    <svg class="size-10 stroke-[#405F4C]" viewBox="0 0 24 24" fill="none" stroke-width="1.6" stroke-linecap="round">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-xl sm:text-2xl font-medium tracking-tight text-[#283D34]">
                    {{ __('weather.empty_heading') }}
                </h2>
                <p class="mx-auto mt-2.5 max-w-md text-sm sm:text-base leading-relaxed text-[#606B61]">
                    {{ __('weather.empty_message') }}
                </p>
            </section>
        @endif
    </div>

    {{-- Dedicated Future Date AI Forecast Section --}}
    <section class="animate-entrance delay-380 mt-10 rounded-[28px] border border-[#D6DCD0] bg-[#FDFCF8] p-6 sm:p-9 shadow-xs" aria-labelledby="future-heading">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h2 id="future-heading" class="text-xl sm:text-2xl font-bold tracking-tight text-[#283D34] flex items-center gap-2">
                    <svg class="size-6 text-[#405F4C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                        <path d="M5 3v4"/>
                        <path d="M19 17v4"/>
                        <path d="M3 5h4"/>
                        <path d="M17 19h4"/>
                    </svg>
                    <span>{{ __('weather.future_heading') }}</span>
                </h2>
                <p class="mt-1 text-sm text-[#606B61]">
                    {{ __('weather.future_tagline') }}
                </p>
            </div>
        </div>

        <form id="future-forecast-form" method="POST" action="{{ route('weather.forecast') }}" class="mt-6">
            @csrf
            <input type="hidden" name="lang" value="{{ $language }}">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 sm:gap-4">
                {{-- City Input --}}
                <div class="sm:col-span-5">
                    <label for="future_city" class="block text-xs font-bold text-[#283D34] mb-1.5">
                        {{ __('weather.future_city_label') }}
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5" aria-hidden="true">
                            <svg class="size-4.5 stroke-[#606B61]" viewBox="0 0 20 20" fill="none" stroke-width="1.7" stroke-linecap="round">
                                <path d="M10 18.33s6.67-5.5 6.67-10.83a6.67 6.67 0 1 0-13.34 0C3.33 12.83 10 18.33 10 18.33Z"/>
                                <circle cx="10" cy="7.5" r="2.5"/>
                            </svg>
                        </div>
                        <input
                            id="future_city"
                            name="city"
                            type="text"
                            value="{{ $futureCity }}"
                            placeholder="{{ __('weather.search_placeholder') }}"
                            maxlength="100"
                            autocomplete="off"
                            required
                            class="w-full h-[52px] rounded-xl border border-[#D6DCD0] bg-[#FDFCF8] px-4 ps-10 text-base text-[#283D34] placeholder:text-[#606B61] transition outline-none focus:border-[#6B8060] focus:ring-2 focus:ring-[#6B8060]/20"
                        >
                    </div>
                </div>

                {{-- Date Input --}}
                <div class="sm:col-span-4">
                    <label for="future_date" class="block text-xs font-bold text-[#283D34] mb-1.5">
                        {{ __('weather.future_date_label') }}
                    </label>
                    <input
                        id="future_date"
                        name="date"
                        type="date"
                        value="{{ $futureDate }}"
                        min="{{ \Carbon\CarbonImmutable::tomorrow()->toDateString() }}"
                        max="{{ \Carbon\CarbonImmutable::today()->addDays(365)->toDateString() }}"
                        required
                        class="w-full h-[52px] rounded-xl border border-[#D6DCD0] bg-[#FDFCF8] px-4 text-base text-[#283D34] transition outline-none focus:border-[#6B8060] focus:ring-2 focus:ring-[#6B8060]/20"
                    >
                </div>

                {{-- Submit Button --}}
                <div class="sm:col-span-3 flex items-end">
                    <button
                        id="future-button"
                        type="submit"
                        data-generating="{{ __('weather.future_generating') }}"
                        data-default-label="{{ __('weather.future_button') }}"
                        class="inline-flex h-[52px] w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#405F4C] px-5 text-[15px] font-bold text-[#FDFCF8] transition hover:bg-[#2F493B] active:bg-[#283D34] outline-none focus-visible:ring-2 focus-visible:ring-[#6B8060] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-[#647262]"
                    >
                        <svg id="future-spinner" class="hidden size-4.5 animate-spinner stroke-white" viewBox="0 0 24 24" fill="none" stroke-width="2.5" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span id="future-label-text">{{ __('weather.future_button') }}</span>
                    </button>
                </div>
            </div>
        </form>

        {{-- Error State for Future Forecast --}}
        @if($futureError)
            <div id="future-error" class="mt-4 flex items-center gap-2 rounded-xl border border-[#923E30]/20 bg-[#923E30]/10 p-4 text-sm font-medium text-[#923E30]" role="alert">
                <svg class="size-4.5 shrink-0 stroke-[#923E30]" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M8 5.33V8M8 10.67h.01M14.67 8A6.67 6.67 0 1 1 1.33 8a6.67 6.67 0 0 1 13.34 0Z" stroke-width="1.7" stroke-linecap="round"/>
                </svg>
                <span>{{ $futureError }}</span>
            </div>
        @endif

        {{-- Future Forecast Result Card --}}
        @if($futureForecast)
            <div class="mt-6 rounded-2xl border border-[#D6DCD0] bg-white p-5 sm:p-7 shadow-xs space-y-5">
                {{-- Card Top Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-[#D6DCD0]/60 pb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xl sm:text-2xl font-bold text-[#283D34]">
                                {{ $futureForecast['city'] }}@if($futureForecast['country']), <span class="text-base text-[#606B61] font-normal">{{ $futureForecast['country'] }}</span>@endif
                            </h3>
                        </div>
                        <p class="text-sm font-medium text-[#606B61] mt-0.5">
                            {{ __('weather.days.'.$futureForecast['day_name']) ?? $futureForecast['day_name'] }} · {{ $futureForecast['date'] }}
                        </p>
                    </div>

                    <div>
                        @if($futureForecast['is_seasonal_estimate'])
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-800 border border-amber-500/20">
                                <svg class="size-3.5 fill-amber-600" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                {{ __('weather.future_estimate_badge') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#405F4C]/10 px-3 py-1 text-xs font-semibold text-[#405F4C] border border-[#405F4C]/20">
                                <svg class="size-3.5 stroke-[#405F4C]" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                {{ __('weather.future_radar_badge') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Numerical Data (if available for near forecast) --}}
                @if(! $futureForecast['is_seasonal_estimate'] && $futureForecast['high'] !== null)
                    <div class="flex items-center justify-between sm:justify-start sm:gap-8 bg-[#FDFCF8] rounded-xl p-4 border border-[#D6DCD0]/70">
                        <div class="flex items-center gap-3">
                            <x-weather-icon :code="$futureForecast['icon']" />
                            <div>
                                <p class="text-sm font-semibold capitalize text-[#283D34]">{{ $futureForecast['description'] }}</p>
                                <p class="text-xs text-[#606B61]">{{ __('weather.forecast_heading') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div>
                                <span class="block text-[11px] text-[#606B61]">{{ __('weather.high') }}</span>
                                <span class="text-xl sm:text-2xl font-bold text-[#283D34]">{{ round($futureForecast['high']) }}°C</span>
                            </div>
                            <div>
                                <span class="block text-[11px] text-[#606B61]">{{ __('weather.low') }}</span>
                                <span class="text-xl sm:text-2xl font-medium text-[#606B61]">{{ round($futureForecast['low']) }}°C</span>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-[#606B61] italic">
                        {{ __('weather.future_seasonal_notice') }}
                    </p>
                @endif

                {{-- Interactive AI Insights Section --}}
                <div class="space-y-4">
                    {{-- Section Bar with pulsing AI badge --}}
                    <div class="flex items-center justify-between border-b border-[#D6DCD0]/70 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-[#405F4C] to-[#283D34] text-white shadow-xs">
                                <svg class="size-4.5 animate-sparkle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                                    <path d="M5 3v4M19 17v4M3 5h4M17 19h4"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm sm:text-base font-bold text-[#283D34] leading-tight">{{ __('weather.ai_summary_title') }}</h4>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E8EDE3] px-3 py-1 text-xs font-semibold text-[#405F4C] border border-[#BFCCB8]/60">
                            <span class="size-2 rounded-full bg-[#405F4C] animate-pulse"></span>
                            Groq AI
                        </span>
                    </div>

                    @if($futureForecast['ai_failed'])
                        <div class="rounded-2xl border border-[#923E30]/20 bg-[#923E30]/10 p-4 text-sm font-medium text-[#923E30]">
                            {{ __('weather.ai_unavailable_notice') }}
                        </div>
                    @else
                        {{-- Distinct Interactive Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-4">
                            {{-- Card 1: Outlook & Atmosphere --}}
                            <article class="group relative overflow-hidden rounded-2xl border border-[#D6DCD0] bg-gradient-to-br from-[#FDFCF8] via-[#FAFBF8] to-[#EFF3EB] p-5 shadow-xs transition duration-300 hover:-translate-y-1 hover:shadow-md hover:border-[#BFCCB8]">
                                <div class="flex items-start gap-3.5">
                                    {{-- Animated Weather/Atmosphere SVG Icon --}}
                                    <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-[#E8EDE3] text-[#405F4C] shadow-xs transition group-hover:scale-105">
                                        <svg class="size-6 animate-breeze" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="4" class="animate-pulse-slow"/>
                                            <path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" opacity="0.6"/>
                                            <path class="animate-cloud-drift" d="M17 17a4 4 0 0 0 0-8h-.5A5 5 0 0 0 7 11a3.5 3.5 0 0 0 1 7h9z"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-xs font-bold uppercase tracking-wider text-[#405F4C] mb-1.5 flex items-center gap-1.5">
                                            <span>{{ __('weather.ai_overview_title') }}</span>
                                        </h5>
                                        <p class="text-[14px] sm:text-[15px] font-normal leading-relaxed text-[#283D34]">
                                            {{ $futureForecast['ai_overview'] ?? $futureForecast['ai_summary'] }}
                                        </p>
                                    </div>
                                </div>
                            </article>

                            {{-- Card 2: Daily Tips & Recommendations --}}
                            @if(!empty($futureForecast['ai_tips']))
                                <article class="group relative overflow-hidden rounded-2xl border border-[#D6DCD0] bg-gradient-to-br from-[#FDFCF8] via-[#FAF9F5] to-[#F5EFE6] p-5 shadow-xs transition duration-300 hover:-translate-y-1 hover:shadow-md hover:border-[#E5CDB2]">
                                    <div class="flex items-start gap-3.5">
                                        {{-- Animated Glowing Lightbulb / Tip SVG Icon --}}
                                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-700 shadow-xs transition group-hover:scale-105">
                                            <svg class="size-6 animate-pulse-slow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
                                                <path d="M9 18h6"/>
                                                <path d="M10 22h4"/>
                                            </svg>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-1.5 flex items-center gap-1.5">
                                                <span>{{ __('weather.ai_tips_title') }}</span>
                                            </h5>
                                            <p class="text-[14px] sm:text-[15px] font-normal leading-relaxed text-[#283D34]">
                                                {{ $futureForecast['ai_tips'] }}
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Disclaimer --}}
                <p class="text-xs text-[#606B61] flex items-center gap-1.5 pt-1">
                    <svg class="size-3.5 shrink-0 stroke-[#606B61]" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>{{ __('weather.future_disclaimer') }}</span>
                </p>
            </div>
        @endif
    </section>
</div>
@endsection
