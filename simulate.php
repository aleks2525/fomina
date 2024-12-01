<?php
// Подключение к базе данных
$conn = new mysqli("localhost", "047054032_1", "w*y(t6dbj3Jg", "j993990_doctor_schedule");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$generationId = $_GET['id'];

// Симуляция записи
$sql = "SELECT * FROM schedule WHERE generation_id = ? AND available = 1 ORDER BY RAND()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $generationId);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $duration = rand(1, 12) * 15; 
    $patientName = "Patient " . rand(1000, 9999);
    
    $sql = "INSERT INTO appointments (generation_id, day, start_time, duration, patient_name) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisis", $generationId, $row['day'], $row['time'], $duration, $patientName);
    $stmt->execute();
    
    // Обновление доступности слотов
    $sql = "UPDATE schedule SET available = 0 WHERE generation_id = ? AND day = ? AND time >= ? AND time < DATE_ADD(?, INTERVAL ? MINUTE)";
    $stmt = $conn->prepare($sql);
    $endTime = date('H:i', strtotime($row['time']) + $duration * 60);
    $stmt->bind_param("iissi", $generationId, $row['day'], $row['time'], $row['time'], $duration);
    $stmt->execute();

    $appointments[] = [
        'day' => $row['day'],
        'start_time' => $row['time'],
        'duration' => $duration,
        'patient_name' => $patientName
    ];
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		  <link href="favicon.png" rel="icon">

    <title>Результаты симуляции записи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
<img src="logo.png" alt=""  class="img-fluid ">
    <h1>Результаты симуляции записи</h1>
    <p>ID генерации: <?php echo $generationId; ?></p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>День</th>
                <th>Время начала</th>
                <th>Продолжительность</th>
                <th>Пациент</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appointment): ?>
                <tr>
                    <td><?php echo $appointment['day']; ?></td>
                    <td><?php echo $appointment['start_time']; ?></td>
                    <td><?php echo $appointment['duration']; ?> мин</td>
                    <td><?php echo $appointment['patient_name']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="optimize.php?id=<?php echo $generationId; ?>" class="btn btn-primary">Оптимизировать расписание</a><br/><br/><br/>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>