<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #1E2022;
            color: white;
            font-family: Arial, sans-serif;
            transition: background 0.5s ease-in-out;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
        }
        .weather-card {
            background: #2A2D34;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .weather-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .weather-icon img {
            width: 100px;
        }
        .temperature {
            font-size: 3rem;
            font-weight: bold;
        }
        .details {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }
        .forecast-container {
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px 0;
        }
        .forecast {
            display: flex;
        }
        .forecast-item {
            flex: 0 0 80px;
            text-align: center;
            margin: 5px;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 8px;
            transition: box-shadow 0.3s ease-in-out;
        }
        .glow {
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.8);
            font-weight: bold;
        }
        .chart-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="weather-card">
            <form action="/weather" method="POST" class="mb-3">
                @csrf
                <div class="input-group">
                    <input type="text" name="city" class="form-control" placeholder="Enter city name" required>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>

            @isset($weather)
                <div class="weather-header">
                    <h3>{{ $weather['location']['name'] }}, {{ $weather['location']['country'] }}</h3>
                    <div class="weather-icon">
                        <img src="https:{{ $weather['current']['condition']['icon'] }}" alt="Weather Icon">
                    </div>
                </div>
                <p class="temperature">{{ $weather['current']['temp_c'] }}°C</p>
                <p>{{ $weather['current']['condition']['text'] }}</p>
                <p>🕒 Local Time: {{ $weather['location']['localtime'] }}</p>
                <div class="details">
                    <p>Humidity: {{ $weather['current']['humidity'] }}%</p>
                    <p>Wind Speed: {{ $weather['current']['wind_kph'] }} km/h</p>
                </div>
                
                <!-- Forecast Section -->
                <div class="forecast-container">
                    <div class="forecast">
                        @foreach($weather['forecast']['forecastday'][0]['hour'] as $hour)
                            <div class="forecast-item" data-hour="{{ date('H', strtotime($hour['time'])) }}">
                                <p>{{ date('g A', strtotime($hour['time'])) }}</p>
                                <p>{{ $hour['temp_c'] }}°C</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Temperature Chart -->
                <div class="chart-container">
                    <canvas id="tempChart"></canvas>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        // Get current time and forecast elements
                        const localTime = "{{ $weather['location']['localtime'] }}";
                        const currentHour = new Date(localTime).getHours();
                        const forecastItems = document.querySelectorAll('.forecast-item');
                        let currentItem = null;

                        forecastItems.forEach(item => {
                            if (parseInt(item.dataset.hour) === currentHour) {
                                item.classList.add('glow');
                                currentItem = item;
                            }
                        });

                        // Auto-scroll forecast to current hour
                        if (currentItem) {
                            currentItem.scrollIntoView({ behavior: "smooth", inline: "center" });
                        }

                        // Change background based on sunrise and sunset
                        const sunrise = "{{ $weather['forecast']['forecastday'][0]['astro']['sunrise'] }}";
                        const sunset = "{{ $weather['forecast']['forecastday'][0]['astro']['sunset'] }}";
                        const sunriseHour = parseInt(sunrise.split(':')[0]);
                        const sunsetHour = parseInt(sunset.split(':')[0]) + 12;

                        if (currentHour >= sunriseHour && currentHour < sunsetHour) {
                            document.body.style.background = "linear-gradient(to bottom, #87CEFA, #1E90FF)";
                        } else {
                            document.body.style.background = "linear-gradient(to bottom, #2C3E50, #1E2022)";
                        }
                    });

                    @isset($weather)
                    // Temperature chart
                    const ctx = document.getElementById('tempChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json(array_map(fn($h) => date('g A', strtotime($h['time'])), $weather['forecast']['forecastday'][0]['hour'])),
                            datasets: [{
                                label: 'Temperature (°C)',
                                data: @json(array_map(fn($h) => $h['temp_c'], $weather['forecast']['forecastday'][0]['hour'])),
                                borderColor: 'lightblue',
                                fill: false,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                    @endisset
                </script>
            @endisset
        </div>
    </div>
</body>
</html>
