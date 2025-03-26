<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function index()
    {
        return view('weather.index');
    }

    // public function fetchWeather(Request $request)
    // {
    //     $city = $request->input('city');
    //     $apiKey = env('WEATHER_API_KEY');

    //     $response = Http::withoutVerifying()->get("https://api.weatherapi.com/v1/forecast.json", [
    //         'key' => $apiKey,
    //         'q' => $city,
    //         'days' => 1,
    //         'aqi' => 'no',
    //         'alerts' => 'no'
    //     ]);

    //     if ($response->successful()) {
    //         $data = $response->json();

    //         if (!isset($data['forecast'])) {
    //             return back()->with('error', 'Forecast data not found for this city.');
    //         }

    //         return view('weather.index', ['weather' => $data]);
    //     } else {
    //         return back()->with('error', 'City not found or API issue.');
    //     }
    // }

    public function fetchWeather(Request $request)
    {
        $city = $request->input('city');
        $apiKey = env('WEATHER_API_KEY');

        $response = Http::withoutVerifying()->get("https://api.weatherapi.com/v1/forecast.json", [
            'key' => $apiKey,
            'q' => $city,
            'days' => 1,
            'aqi' => 'no',
            'alerts' => 'no'
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (!isset($data['forecast'])) {
                return back()->with('error', 'Forecast data not found for this city.');
            }

            // Convert sunrise & sunset to 24-hour format for JavaScript
            $data['forecast']['forecastday'][0]['astro']['sunrise'] = date("H:i", strtotime($data['forecast']['forecastday'][0]['astro']['sunrise']));
            $data['forecast']['forecastday'][0]['astro']['sunset'] = date("H:i", strtotime($data['forecast']['forecastday'][0]['astro']['sunset']));

            return view('weather.index', ['weather' => $data]);
        } else {
            return back()->with('error', 'City not found or API issue.');
        }
    }
    
}