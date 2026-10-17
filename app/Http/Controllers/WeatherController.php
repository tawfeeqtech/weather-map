<?php

namespace App\Http\Controllers;

use App\Exceptions\WeatherServiceException;
use App\Services\WeatherService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class WeatherController extends Controller
{
    public function __construct(private readonly WeatherService $weatherService) {}

    public function index(Request $request): View
    {
        $language = $this->setLanguage($request->query('lang'));

        return view('weather.index', [
            'language' => $language,
            'weather' => null,
            'cityQuery' => '',
            'error' => null,
        ]);
    }

    public function search(Request $request): View|Response
    {
        $language = $this->setLanguage($request->input('lang'));
        $validator = Validator::make($request->all(), [
            'city' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\p{L}\p{M}\p{N} .,\x27’()\-]+$/u',
            ],
        ]);

        if ($validator->fails()) {
            return $this->weatherPage(
                language: $language,
                cityQuery: (string) $request->input('city', ''),
                error: __('weather.errors.invalid_search'),
                status: 422,
            );
        }

        $city = trim((string) $validator->validated()['city']);

        if ($city === '') {
            return $this->weatherPage(
                language: $language,
                cityQuery: '',
                error: __('weather.errors.invalid_search'),
                status: 422,
            );
        }

        try {
            $weather = $this->weatherService->getWeatherForCity($city, $language);

            return $this->weatherPage($language, $city, weather: $weather);
        } catch (WeatherServiceException $exception) {
            return $this->weatherPage(
                language: $language,
                cityQuery: $city,
                error: __('weather.errors.'.$exception->reason),
                status: $exception->status,
            );
        }
    }

    public function locate(Request $request): View|Response
    {
        $language = $this->setLanguage($request->input('lang'));
        $validator = Validator::make($request->all(), [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        if ($validator->fails()) {
            return $this->weatherPage(
                language: $language,
                cityQuery: '',
                error: __('weather.errors.invalid_location'),
                status: 422,
            );
        }

        $coordinates = $validator->validated();

        try {
            $weather = $this->weatherService->getWeatherForCoordinates(
                (float) $coordinates['latitude'],
                (float) $coordinates['longitude'],
                $language,
            );

            return $this->weatherPage($language, '', weather: $weather);
        } catch (WeatherServiceException $exception) {
            return $this->weatherPage(
                language: $language,
                cityQuery: '',
                error: __('weather.errors.'.$exception->reason),
                status: $exception->status,
            );
        }
    }

    private function setLanguage(mixed $candidate): string
    {
        $language = in_array($candidate, ['en', 'ar'], true)
            ? $candidate
            : (string) config('app.locale', 'en');

        if (! in_array($language, ['en', 'ar'], true)) {
            $language = 'en';
        }

        App::setLocale($language);

        return $language;
    }

    private function weatherPage(
        string $language,
        string $cityQuery,
        ?array $weather = null,
        ?string $error = null,
        int $status = 200,
    ): View|Response {
        $view = view('weather.index', [
            'language' => $language,
            'weather' => $weather,
            'cityQuery' => $cityQuery,
            'error' => $error,
        ]);

        return $status === 200 ? $view : response($view, $status);
    }
}
