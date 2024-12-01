<?php
header('Content-Type: application/json');

// Подключение к базе данных
$conn = new mysqli("localhost", "047054032_1", "w*y(t6dbj3Jg", "j993990_doctor_schedule");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
$generationId = $data['generation_id'];
$cancelProbability = $data['cancel_probability'];

// Получение всех записей
$sql = "SELECT * FROM appointments WHERE generation_id = ? ORDER BY day, start_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $generationId);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

// Оптимизация
foreach ($appointments as &$appointment) {
    if (rand(1, 100) <= $cancelProbability) {
        $appointment['status'] = 'cancelled';
    } else {
        $appointment['status'] = 'confirmed';
    }
}

// Сортировка подтвержденных записей в начало
usort($appointments, function($a, $b) {
    if ($a['status'] == $b['status']) return 0;
    return ($a['status'] == 'confirmed') ? -1 : 1;
});

// Сохранение оптимизированного расписания
$sql = "UPDATE appointments SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
foreach ($appointments as $appointment) {
    $stmt->bind_param("si", $appointment['status'], $appointment['id']);
    $stmt->execute();
}

// Возврат оптимизированного расписания для анимации
echo json_encode($appointments);