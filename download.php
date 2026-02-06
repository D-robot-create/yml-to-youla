<?php
// download.php
session_start();
require_once 'config.php';

$feedId = $_GET['id'] ?? '';

if (empty($feedId)) {
    header('HTTP/1.1 400 Bad Request');
    die('Feed ID required');
}

$feedPath = __DIR__ . "/feeds/{$feedId}.xml";

if (!file_exists($feedPath)) {
    header('HTTP/1.1 404 Not Found');
    die('Feed not found');
}

// Обновляем время последнего доступа в базе
$dbPath = __DIR__ . '/feeds/feeds.db';
if (file_exists($dbPath)) {
    try {
        $db = new SQLite3($dbPath);
        $db->exec("UPDATE feeds SET last_updated = CURRENT_TIMESTAMP WHERE id = '{$feedId}'");
        $db->close();
    } catch (Exception $e) {
        // Игнорируем ошибки базы данных при скачивании
    }
}

// Отдаем файл
header('Content-Type: application/xml; charset=utf-8');
header('Content-Disposition: inline; filename="youla_feed_' . $feedId . '.xml"');
header('Content-Length: ' . filesize($feedPath));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');
header('Pragma: no-cache');

readfile($feedPath);