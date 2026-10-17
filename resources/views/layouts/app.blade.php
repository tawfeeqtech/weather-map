<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#eff7ff">
    <meta name="description" content="{{ __('weather.tagline') }}">
    <title>{{ __('weather.app_name') }} — {{ __('weather.tagline') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-sky-50 font-sans text-slate-900 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-36 start-1/4 size-96 rounded-full bg-sky-200/50 blur-3xl"></div>
        <div class="absolute end-0 top-1/3 size-80 rounded-full bg-cyan-100/70 blur-3xl"></div>
    </div>

    <div class="mx-auto min-h-screen w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
        <header class="mb-10 flex items-start justify-between gap-4 sm:mb-14">
            <a href="{{ route('weather.index', ['lang' => $language]) }}" class="group inline-flex items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-sky-700">
                <span class="grid size-11 place-items-center rounded-2xl bg-sky-600 text-white shadow-lg shadow-sky-900/10" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 18a4 4 0 0 1-.6-7.95A5.5 5.5 0 0 1 17 8.5a4.75 4.75 0 0 1 .25 9.5H7Z" />
                        <path stroke-linecap="round" d="M9 21v-1m6 1v-1" />
                    </svg>
                </span>
                <span>
                    <span class="block text-lg font-bold tracking-tight text-slate-950">{{ __('weather.app_name') }}</span>
                    <span class="hidden text-xs text-slate-500 sm:block">{{ __('weather.tagline') }}</span>
                </span>
            </a>

            <nav aria-label="{{ __('weather.language') }}" class="flex items-center gap-1 rounded-full border border-white/80 bg-white/80 p-1 shadow-sm backdrop-blur">
                <a href="{{ route('weather.index', ['lang' => 'en']) }}" hreflang="en" lang="en" @class([
                    'rounded-full px-3 py-2 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700',
                    'bg-sky-100 text-sky-900' => $language === 'en',
                    'text-slate-600 hover:bg-slate-100' => $language !== 'en',
                ]) @if($language === 'en') aria-current="page" @endif>{{ __('weather.switch_to_english') }}</a>
                <a href="{{ route('weather.index', ['lang' => 'ar']) }}" hreflang="ar" lang="ar" @class([
                    'rounded-full px-3 py-2 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700',
                    'bg-sky-100 text-sky-900' => $language === 'ar',
                    'text-slate-600 hover:bg-slate-100' => $language !== 'ar',
                ]) @if($language === 'ar') aria-current="page" @endif>{{ __('weather.switch_to_arabic') }}</a>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="mt-14 border-t border-sky-100/80 py-6 text-center text-xs text-slate-500">
            {{ __('weather.app_name') }} <span aria-hidden="true">·</span> °C
        </footer>
    </div>
</body>
</html>
