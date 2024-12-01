<?php
// Подключение к базе данных
$conn = new mysqli("localhost", "047054032_1", "w*y(t6dbj3Jg", "j993990_doctor_schedule");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Генерация 
$workingDays = rand(3, 7);
$workingHours = [
    ['start' => '09:00', 'end' => '19:00']
];
$selectedHours = $workingHours[array_rand($workingHours)];

// Генерация расписания
$schedule = [];
for ($day = 1; $day <= $workingDays; $day++) {
    $currentTime = strtotime($selectedHours['start']);
    $endTime = strtotime($selectedHours['end']);
    
    while ($currentTime < $endTime) {
        $time = date('H:i', $currentTime);
        $schedule[$day][] = [
            'time' => $time,
            'available' => (rand(1, 100) <= 50) // 
        ];
        $currentTime += 900; // 15 минут
    }
}

// Сохранение в базу данных
$generationId = time(); // 
foreach ($schedule as $day => $slots) {
    foreach ($slots as $slot) {
        $sql = "INSERT INTO schedule (generation_id, day, time, available) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $available = $slot['available'] ? 1 : 0;
        $stmt->bind_param("iisi", $generationId, $day, $slot['time'], $available);
        $stmt->execute();
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		  <link href="favicon.png" rel="icon">

	
    <title>Сгенерированное расписание</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
<img src="logo.png" alt=""  class="img-fluid ">
    <h1>Сгенерированное расписание</h1>
    <p>ID генерации: <?php echo $generationId; ?></p>
    <p>Рабочие дни: <?php echo $workingDays; ?></p>
    <p>Рабочие часы: <?php echo $selectedHours['start'] . ' - ' . $selectedHours['end']; ?></p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>День</th>
                <th>Время</th>
                <th>Доступность</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedule as $day => $slots): ?>
                <?php foreach ($slots as $slot): ?>
                    <tr>
                        <td><?php echo $day; ?></td>
                        <td><?php echo $slot['time']; ?></td>
                        <td><?php echo $slot['available'] ? 'Доступно' : 'Занято'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="simulate.php?id=<?php echo $generationId; ?>" class="btn btn-primary">Симуляция записи</a>
	
	<br/><br/>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>