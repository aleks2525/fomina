<?php
header('Content-Type: application/json');

// Подключение к базе данных
$conn = new mysqli("localhost", "047054032_1", "w*y(t6dbj3Jg", "j993990_doctor_schedule");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Функция для получения расписания
function getSchedule($generationId) {
    global $conn;
    $sql = "SELECT * FROM schedule WHERE generation_id = ? ORDER BY day, time";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $generationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = [];
    while ($row = $result->fetch_assoc()) {
        $schedule[] = $row;
    }
    return $schedule;
}

// Функция для симуляции записи
function simulateAppointment($generationId, $day, $time, $duration, $patientName) {
    global $conn;
    
    // Проверка доступности слота
    $sql = "SELECT * FROM schedule WHERE generation_id = ? AND day = ? AND time = ? AND available = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $generationId, $day, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return ['error' => 'Выбранное время недоступно'];
    }
    
    // Создание записи
    $sql = "INSERT INTO appointments (generation_id, day, start_time, duration, patient_name) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisis", $generationId, $day, $time, $duration, $patientName);
    $stmt->execute();
    
    // Обновление доступности слотов
    $sql = "UPDATE schedule SET available = 0 WHERE generation_id = ? AND day = ? AND time >= ? AND time < DATE_ADD(?, INTERVAL ? MINUTE)";
    $stmt = $conn->prepare($sql);
    $endTime = date('H:i', strtotime($time) + $duration * 60);
    $stmt->bind_param("iissi", $generationId, $day, $time, $time, $duration);
    $stmt->execute();
    
    return ['success' => 'Запись создана успешно'];
}

// Обработка запросов
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action == 'schedule' && isset($_GET['id'])) {
            echo json_encode(getSchedule($_GET['id']));
        } else {
            echo json_encode(['error' => 'Invalid action or missing id']);
        }
        break;
    
    case 'POST':
        if ($action == 'simulate') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['generation_id'], $data['day'], $data['time'], $data['duration'], $data['patient_name'])) {
                echo json_encode(simulateAppointment(
                    $data['generation_id'],
                    $data['day'],
                    $data['time'],
                    $data['duration'],
                    $data['patient_name']
                ));
            } else {
                echo json_encode(['error' => 'Missing required fields']);
            }
        } else {
            echo json_encode(['error' => 'Invalid action']);
        }
        break;
    
    default:
        echo json_encode(['error' => 'Invalid request method']);
        break;
}

$conn->close();