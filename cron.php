<?php
// cron.php - файл для запуска по расписанию
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';
require_once 'scheduler.php';

// Проверяем, запущен ли через CLI (cron) или через веб
if (php_sapi_name() === 'cli') {
    echo "Запуск обновления фидов через CLI...\n";
} else {
    // Для веб-доступа можно добавить проверку ключа
    $cronKey = $_GET['key'] ?? '';
    $validKey = 'your-secret-cron-key'; // Замените на свой ключ
    
    if ($cronKey !== $validKey) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid cron key']);
        exit;
    }
}

// Запускаем обновление
$scheduler = new FeedScheduler();
$result = $scheduler->checkAndUpdateFeeds();

// Логируем результат
$logFile = __DIR__ . '/logs/cron.log';
$logEntry = date('Y-m-d H:i:s') . " - Updated: " . ($result['updated'] ?? 0) . " feeds\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

if (php_sapi_name() === 'cli') {
    print_r($result);
} else {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}