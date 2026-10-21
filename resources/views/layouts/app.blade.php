<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $language === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F8F6F0">
    <meta name="description" content="{{ __('weather.tagline') }}">
    <title>{{ __('weather.app_name') }} — {{ __('weather.tagline') }}</title>

    {{-- Google Font: Tajawal (required by Figma specification for Arabic, English and Numerals) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F8F6F0] font-sans text-[#283D34] antialiased selection:bg-[#E8EDE3] selection:text-[#283D34]">
    <div class="mx-auto min-h-screen w-full max-w-[1152px] px-5 py-6 sm:px-8 sm:py-10">
        {{-- Header matching Figma 3:9689 (Desktop) & 3:9837 (Mobile) --}}
        <header class="animate-entrance delay-0 mb-8 flex items-center justify-between gap-4 sm:mb-12">
            <a href="{{ route('weather.index', ['lang' => $language]) }}" class="group inline-flex items-center gap-3 rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-[#6B8060] focus-visible:ring-offset-4 focus-visible:ring-offset-[#F8F6F0]">
                <span class="text-3xl sm:text-[38px] font-normal leading-none tracking-tight text-[#283D34] transition group-hover:opacity-90">
                    {{ __('weather.app_name') }}
                </span>
            </a>

            {{-- Language switcher with exact Figma pill treatment --}}
            <nav aria-label="{{ __('weather.language') }}" class="flex items-center gap-1.5 text-sm sm:text-base">
                <a
                    href="{{ route('weather.index', ['lang' => 'en']) }}"
                    hreflang="en"
                    lang="en"
                    @class([
                        'inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-full px-3.5 py-1.5 font-medium transition outline-none focus-visible:ring-2 focus-visible:ring-[#6B8060] focus-visible:ring-offset-2 focus-visible:ring-offset-[#F8F6F0]',
                        'bg-[#E8EDE3] font-bold text-[#283D34] shadow-xs' => $language === 'en',
                        'text-[#606B61] hover:text-[#283D34] hover:bg-[#F1F4EE]' => $language !== 'en',
                    ])
                    @if($language === 'en') aria-current="page" @endif
                >
                    {{ __('weather.switch_to_english') }}
                </a>

                <span class="text-[#606B61] select-none text-sm" aria-hidden="true">|</span>

                <a
                    href="{{ route('weather.index', ['lang' => 'ar']) }}"
                    hreflang="ar"
                    lang="ar"
                    @class([
                        'inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-full px-3.5 py-1.5 font-medium transition outline-none focus-visible:ring-2 focus-visible:ring-[#6B8060] focus-visible:ring-offset-2 focus-visible:ring-offset-[#F8F6F0]',
                        'bg-[#E8EDE3] font-bold text-[#283D34] shadow-xs' => $language === 'ar',
                        'text-[#606B61] hover:text-[#283D34] hover:bg-[#F1F4EE]' => $language !== 'ar',
                    ])
                    @if($language === 'ar') aria-current="page" @endif
                >
                    {{ __('weather.switch_to_arabic') }}
                </a>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="mt-16 border-t border-[#D6DCD0]/60 py-8 text-center text-xs font-medium text-[#606B61]">
            <p>{{ __('weather.app_name') }} <span aria-hidden="true">·</span> {{ __('weather.tagline') }}</p>
        </footer>
    </div>
</body>
</html>
