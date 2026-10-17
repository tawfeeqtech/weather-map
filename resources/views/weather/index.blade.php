@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-3xl text-center">
        <p class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">{{ __('weather.search_heading') }}</p>
        <h1 class="text-3xl font-bold tracking-tight text-slate-950 sm:text-5xl">{{ __('weather.tagline') }}</h1>

        <form id="search-form" method="POST" action="{{ route('weather.search') }}" class="mt-8 rounded-3xl border border-white/90 bg-white/90 p-3 shadow-xl shadow-sky-900/5 backdrop-blur sm:p-4">
            @csrf
            <input type="hidden" name="lang" value="{{ $language }}">
            <label for="city" class="sr-only">{{ __('weather.search_label') }}</label>
            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    id="city"
                    name="city"
                    type="search"
                    value="{{ $cityQuery }}"
                    placeholder="{{ __('weather.search_placeholder') }}"
                    maxlength="100"
                    autocomplete="off"
                    required
                    class="min-h-13 min-w-0 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-start text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:bg-white focus:ring-4 focus:ring-sky-100"
                >
                <button id="search-button" type="submit" data-searching="{{ __('weather.searching') }}" class="inline-flex min-h-13 items-center justify-center gap-2 rounded-2xl bg-sky-600 px-6 font-semibold text-white transition hover:bg-sky-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700 active:bg-sky-800">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle><path stroke-linecap="round" d="m16 16 4 4"></path>
                    </svg>
                    <span id="search-label">{{ __('weather.search_button') }}</span>
                </button>
            </div>
        </form>

        <form id="location-form" method="POST" action="{{ route('weather.locate') }}" class="mt-4">
            @csrf
            <input type="hidden" name="lang" value="{{ $language }}">
            <input id="latitude" type="hidden" name="latitude">
            <input id="longitude" type="hidden" name="longitude">
            <button
                id="locate-button"
                type="button"
                data-locating="{{ __('weather.locating') }}"
                data-default-label="{{ __('weather.locate_button') }}"
                data-unavailable="{{ __('weather.location_unavailable') }}"
                data-denied="{{ __('weather.location_denied') }}"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-sky-800 transition hover:bg-white/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700"
            >
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                    <circle cx="12" cy="10" r="2.5"></circle>
                </svg>
                <span id="locate-label">{{ __('weather.locate_button') }}</span>
            </button>
            <p id="location-status" class="mt-2 min-h-5 text-sm text-slate-600" role="status" aria-live="polite"></p>
        </form>
    </section>

    @if($error)
        <div class="mx-auto mt-8 flex max-w-3xl items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-start text-sm leading-6 text-rose-900" role="alert">
            <svg viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle><path stroke-linecap="round" d="M12 8v4m0 4h.01"></path>
            </svg>
            <p>{{ $error }}</p>
        </div>
    @endif

    @if($weather)
        <section class="mx-auto mt-10 max-w-5xl" aria-labelledby="current-heading">
            <div class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-sky-600 via-sky-600 to-cyan-700 p-6 text-white shadow-xl shadow-sky-900/15 sm:p-9">
                <div class="flex flex-col justify-between gap-8 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-sm font-medium text-sky-100">{{ __('weather.current_heading') }}</p>
                        <h2 id="current-heading" class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">
                            {{ __('weather.today_in', ['location' => trim($weather['current']['city'].' '.$weather['current']['country'])]) }}
                        </h2>
                        <p class="mt-2 text-lg capitalize text-sky-100">{{ $weather['current']['description'] }}</p>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <img
                            src="https://openweathermap.org/img/wn/{{ $weather['current']['icon'] }}@2x.png"
                            alt=""
                            width="100"
                            height="100"
                            class="size-20 sm:size-24"
                        >
                        <p class="text-6xl font-light tracking-tight sm:text-7xl">
                            <span>{{ round($weather['current']['temperature']) }}</span><span class="text-3xl align-top">{{ __('weather.celsius') }}</span>
                        </p>
                    </div>
                </div>

                <dl class="mt-8 grid grid-cols-2 gap-3 border-t border-white/20 pt-6 sm:max-w-lg sm:grid-cols-2">
                    <div class="rounded-2xl bg-white/10 px-4 py-3">
                        <dt class="text-sm text-sky-100">{{ __('weather.humidity') }}</dt>
                        <dd class="mt-1 text-xl font-semibold">{{ $weather['current']['humidity'] }}{{ __('weather.percent') }}</dd>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-3">
                        <dt class="text-sm text-sky-100">{{ __('weather.wind_speed') }}</dt>
                        <dd class="mt-1 text-xl font-semibold">{{ number_format($weather['current']['wind_speed'], 1) }} <span class="text-sm font-medium">{{ __('weather.wind_unit') }}</span></dd>
                    </div>
                </dl>
            </div>

            <section class="mt-8" aria-labelledby="forecast-heading">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 id="forecast-heading" class="text-xl font-bold tracking-tight text-slate-950">{{ __('weather.forecast_heading') }}</h2>
                    <span class="text-sm font-medium text-slate-500">{{ __('weather.celsius') }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                    @foreach($weather['forecast'] as $day)
                        @php
                            $forecastDate = \Carbon\CarbonImmutable::parse($day['date'])->locale($language);
                            $dayName = $forecastDate->format('l');
                        @endphp
                        <article class="rounded-3xl border border-white bg-white/90 p-4 text-center shadow-sm shadow-sky-900/5 sm:p-5">
                            <h3 class="text-sm font-semibold text-slate-700">{{ __('weather.days.'.$dayName) }}</h3>
                            <img
                                src="https://openweathermap.org/img/wn/{{ $day['icon'] }}@2x.png"
                                alt=""
                                width="72"
                                height="72"
                                loading="lazy"
                                class="mx-auto my-1 size-16"
                            >
                            <p class="min-h-10 text-xs capitalize leading-5 text-slate-500">{{ $day['description'] }}</p>
                            <div class="mt-3 flex items-center justify-center gap-3 text-sm">
                                <span class="font-bold text-slate-900"><span class="sr-only">{{ __('weather.high') }} </span>{{ round($day['high']) }}°</span>
                                <span class="font-medium text-slate-500"><span class="sr-only">{{ __('weather.low') }} </span>{{ round($day['low']) }}°</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </section>
    @else
        <section class="mx-auto mt-10 max-w-xl rounded-3xl border border-sky-100 bg-white/55 px-6 py-8 text-center sm:mt-14" aria-label="{{ __('weather.search_heading') }}">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-sky-100 text-sky-700" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-7" fill="none" stroke="currentColor" stroke-width="1.7">
                    <circle cx="12" cy="12" r="4"></circle><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-semibold text-slate-900">{{ __('weather.empty_heading') }}</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">{{ __('weather.empty_message') }}</p>
        </section>
    @endif
@endsection
