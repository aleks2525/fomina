<?php
// Подключение к базе данных
$conn = new mysqli("localhost", "047054032_1", "w*y(t6dbj3Jg", "j993990_doctor_schedule");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$generationId = $_GET['id'];

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

?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		  <link href="favicon.png" rel="icon">

    <title>Оптимизация расписания</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .appointment {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ddd;
            transition: all 0.5s ease;
        }

        .confirmed {
            background-color: #d4edda;
        }

        .cancelled {
            background-color: #f8d7da;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
	<img src="logo.png" alt=""  class="img-fluid ">
        <h1>Оптимизация расписания</h1>
        <p>ID генерации: <?php echo $generationId; ?></p>
        <div class="mb-3">
            <label for="cancel-probability" class="form-label">Введите вероятность отмены или переноса записи пациентом (%)</label>
            <input type="number" class="form-control" id="cancel-probability" min="5" max="20" value="10">
        </div>
        <button id="optimize-button" class="btn btn-primary">Оптимизировать</button>

        <div id="appointments-container" class="mt-4"></div>
    </div>

    <!-- Модальное окно -->
    <div class="modal fade" id="scrollModal" tabindex="-1" aria-labelledby="scrollModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scrollModalLabel">Оптимизация завершена</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body text-center">
                    <a href="pochasam.php?id=<?php echo $generationId; ?>" class="btn btn-secondary mb-2">Показать по часам</a>
                    <br>
                    <a href="index.php" class="btn btn-primary">Вернуться на главную</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
  function animateOptimization(appointments) {
    const container = document.getElementById('appointments-container');
    container.innerHTML = '';
    
    // Анимация добавления записей
    appointments.forEach((appointment, index) => {
        const element = document.createElement('div');
        element.textContent = `День ${appointment.day}, ${appointment.start_time} - ${appointment.patient_name}`;
        element.classList.add('appointment');
        element.classList.add(appointment.status);
        container.appendChild(element);

        setTimeout(() => {
            element.style.transform = `translateY(${index * 30}px)`;
        }, index * 100);
    });

    // Показать модальное окно с небольшим отложением после анимации
    const totalAnimationTime = appointments.length * 100 + 1000; // Время на анимацию + дополнительные 1000 мс
    setTimeout(() => {
        console.log('Показ модального окна'); // Отладка
        const scrollModal = new bootstrap.Modal(document.getElementById('scrollModal'));
        scrollModal.show();
    }, totalAnimationTime);
}

document.getElementById('optimize-button').addEventListener('click', () => {
    const generationId = <?php echo $generationId; ?>;
    const cancelProbability = document.getElementById('cancel-probability').value;

    fetch('optimize_ajax.php', {
        method: 'POST',
        body: JSON.stringify({ generation_id: generationId, cancel_probability: cancelProbability }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        animateOptimization(data);
    })
    .catch(error => {
        console.error('Ошибка при запросе на сервер:', error); // Обработка возможных ошибок
    });
});

    </script>
</body>

</html>

