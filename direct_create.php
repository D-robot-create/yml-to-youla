<?php
// direct_create.php
require_once 'config.php';
require_once 'converter.php';

echo "<pre>";

// Тестовые данные
$ymlUrl = "https://iskrussian.ru/bitrix/catalog_export/wholesale_kabel_krd.xml";
$feedName = "Прямое создание фида";
$interval = 30;

echo "1. Загружаем YML фид...\n";
$converter = new YMLConverter();
$ymlData = $converter->fetchYMLFeed($ymlUrl);

if (!$ymlData) {
    echo "ОШИБКА: Не удалось загрузить YML\n";
    exit;
}

echo "Успешно! Товаров: " . $converter->countItems($ymlData) . "\n\n";

echo "2. Создаем фид...\n";
$feedId = 'direct_' . time();

try {
    $feedPath = $converter->generateYoulaFeed($ymlData, $feedId);
    
    if (!file_exists($feedPath)) {
        throw new Exception("Файл не создан");
    }
    
    echo "Фид создан: {$feedPath}\n";
    echo "Размер: " . filesize($feedPath) . " байт\n\n";
    
    echo "3. Проверяем содержимое...\n";
    $content = file_get_contents($feedPath);
    echo "Первые 1000 символов:\n";
    echo htmlspecialchars(substr($content, 0, 1000)) . "\n\n";
    
    echo "4. Проверяем XML валидность...\n";
    $xml = simplexml_load_string($content);
    if ($xml) {
        $offers = $xml->shop->offers->offer ?? [];
        echo "Товаров в XML: " . count($offers) . "\n";
        echo "Первый товар:\n";
        if (count($offers) > 0) {
            $first = $offers[0];
            echo "  ID: " . ($first['id'] ?? 'N/A') . "\n";
            echo "  Название: " . ($first->name ?? 'N/A') . "\n";
            echo "  Цена: " . ($first->price ?? 'N/A') . "\n";
        }
    } else {
        echo "ОШИБКА: XML невалиден\n";
    }
    
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo "Трейс:\n" . $e->getTraceAsString() . "\n";
}

echo "\n5. Проверяем базу данных...\n";
$dbPath = __DIR__ . '/feeds/feeds.db';

if (file_exists($dbPath)) {
    try {
        $db = new SQLite3($dbPath);
        
        // Пробуем добавить запись
        $youlaUrl = "http://converter.iskkrd.ru/yml-to-youla/download.php?id={$feedId}";
        
        $stmt = $db->prepare("
            INSERT INTO feeds (id, name, yml_url, youla_url, update_interval, items_count) 
            VALUES (:id, :name, :yml_url, :youla_url, :interval, :count)
        ");
        
        $stmt->bindValue(':id', $feedId);
        $stmt->bindValue(':name', $feedName);
        $stmt->bindValue(':yml_url', $ymlUrl);
        $stmt->bindValue(':youla_url', $youlaUrl);
        $stmt->bindValue(':interval', $interval);
        $stmt->bindValue(':count', $converter->countItems($ymlData));
        
        if ($stmt->execute()) {
            echo "Запись добавлена в БД!\n";
            
            // Проверяем
            $result = $db->query("SELECT COUNT(*) as total FROM feeds");
            $row = $result->fetchArray(SQLITE3_ASSOC);
            echo "Всего фидов в БД: " . ($row['total'] ?? 0) . "\n";
        } else {
            echo "Ошибка БД: " . $db->lastErrorMsg() . "\n";
        }
        
        $db->close();
        
    } catch (Exception $e) {
        echo "Ошибка БД: " . $e->getMessage() . "\n";
    }
} else {
    echo "Файл БД не существует: {$dbPath}\n";
}

echo "</pre>";