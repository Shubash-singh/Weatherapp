<?php

$host = 'localhost';
$db   = 'weather_db';
$user = 'username';
$pass = 'password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

$location = $_GET['location'];

$apiKey = "549af35f1bc0a37c9f89cd92da254737";
$response = file_get_contents("http://api.openweathermap.org/data/2.5/weather?q={$location}&units=metric&appid={$apiKey}");
$data = json_decode($response, true);

$output = [
    'temperature' => $data['main']['temp'],
    'description' => $data['weather'][0]['description']
];

$stmt = $pdo->prepare("INSERT INTO weather_data (location, date, temperature, description) VALUES (?, CURDATE(), ?, ?) ON DUPLICATE KEY UPDATE temperature=?, description=?");
$stmt->execute([$location, $output['temperature'], $output['description'], $output['temperature'], $output['description']]);

$stmt = $pdo->prepare("SELECT date, temperature, description FROM weather_data WHERE location = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY date DESC");
$stmt->execute([$location]);
$pastData = $stmt->fetchAll();

$output['past'] = $pastData;

echo json_encode($output);

?>
