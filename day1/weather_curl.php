<?php

$apiKey = '7c21977a3a598454c3946e42a42f102e'; 

$citiesFile = 'city.list.json'; 

$egyptCities = [];
if (file_exists($citiesFile)) {
    $jsonData = file_get_contents($citiesFile);
    $cities = json_decode($jsonData, true);

    foreach ($cities as $city) {
        if ($city['country'] === 'EG') {
            $egyptCities[] = $city;
        }
    }
} else {
    die('City list file not found! Make sure city.list.json is present.');
}


$weatherData = null;
if (isset($_GET['city_id'])) {
    $cityId = $_GET['city_id'];

    $url = "http://api.openweathermap.org/data/2.5/weather?id={$cityId}&appid={$apiKey}&units=metric";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
    }

    curl_close($ch);

    if (isset($error_msg)) {
        $weatherData = ['error' => $error_msg];
    } else {
        $weatherData = json_decode($response, true);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Egypt Weather  </title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .weather-box { margin-top: 20px; border: 1px solid #ddd; padding: 15px; width: 400px; }
    </style>
</head>
<body>

<h2>Select an Egyptian city to check the weather:</h2>
<p>Using Curl</p>
<form method="get">
    <select name="city_id">
        <?php foreach ($egyptCities as $city): ?>
            <option value="<?= htmlspecialchars($city['id']) ?>"
                <?= (isset($_GET['city_id']) && $_GET['city_id'] == $city['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars("EG >> " . $city['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Get Weather</button>
</form>

<?php if ($weatherData): ?>
    <div class="weather-box">
        <?php if (isset($weatherData['error'])): ?>
            <p>Error: <?= htmlspecialchars($weatherData['error']) ?></p>
        <?php elseif (isset($weatherData['main'])): ?>
            <h3>Weather in: <?= htmlspecialchars($weatherData['name']) ?></h3>
            <p>
                Time: <?= date('l h:i A', $weatherData['dt']) ?><br>
                Temperature: <?= $weatherData['main']['temp_min'] ?>°C ~ <?= $weatherData['main']['temp_max'] ?>°C<br>
                Humidity: <?= $weatherData['main']['humidity'] ?>%<br>
                Wind: <?= $weatherData['wind']['speed'] ?> km/h<br>
                Condition: <?= $weatherData['weather'][0]['description'] ?>
            </p>
        <?php else: ?>
            <p>Weather data not available.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
