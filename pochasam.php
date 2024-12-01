<?php
// Подключение к базе данных
$conn = new mysqli("localhost", "047054032_1", "w*y(t6dbj3Jg", "j993990_doctor_schedule");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$generationId = $_GET['id'];

// Получение записей
$sql = "SELECT * FROM appointments WHERE generation_id = ? ORDER BY day, start_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $generationId);
$stmt->execute();
$result = $stmt->get_result();

$schedule = [];
while ($row = $result->fetch_assoc()) {
    $day = $row['day'];
    $hour = date('H:00', strtotime($row['start_time']));
    $schedule[$day][$hour] = true;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		  <link href="favicon.png" rel="icon">

	
	    <title>Расписание по часам</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hour-slot {
            width: 100px;
            height: 50px;
            border: 1px solid #ddd;
            display: inline-block;
            text-align: center;
            line-height: 50px;
        }
        .occupied {
            background-color: #d4edda;
        }
    </style>
</head>
<body>
<div class="container mt-5">
<img src="logo.png" alt=""  class="img-fluid ">
    <h1>Расписание по часам</h1>
    <p>ID генерации: <?php echo $generationId; ?></p>

    <div class="schedule-container">
        <?php
        $days = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
        $hours = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

        foreach ($days as $dayIndex => $dayName) {
            echo "<h3>$dayName</h3>";
            foreach ($hours as $hour) {
                $class = isset($schedule[$dayIndex + 1][$hour]) ? 'occupied' : '';
                echo "<div class='hour-slot $class'>$hour</div>";
            }
            echo "<br>";
        }
        ?>
    </div>

    <a href="index.php" class="btn btn-primary mt-3">Вернуться на главную</a><br/><br/><br/>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
	