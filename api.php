<?php
header('Content-Type: application/json; charset=utf-8');

// Включаем отладку
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/api_errors.log');

// Глобально отключаем SSL проверки
stream_context_set_default([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

// Инициализируем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Устанавливаем CSRF токен если его нет
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = 'csrf_' . bin2hex(random_bytes(16));
}

require_once 'config.php';
require_once 'converter.php';

// Включение CORS для API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");

// Обработка OPTIONS запроса для CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Логирование всех запросов
error_log("=== API REQUEST [" . date('Y-m-d H:i:s') . "] ===");
error_log("Method: {$_SERVER['REQUEST_METHOD']}");
error_log("URI: {$_SERVER['REQUEST_URI']}");
error_log("IP: {$_SERVER['REMOTE_ADDR']}");

class API {
    private $db;
    
    public function __construct() {
        $this->initDatabase();
    }
    
    private function initDatabase() {
        $dbPath = __DIR__ . '/feeds/feeds.db';
        
        try {
            // Создаем директорию если не существует
            if (!is_dir(dirname($dbPath))) {
                mkdir(dirname($dbPath), 0755, true);
            }
            
            $this->db = new SQLite3($dbPath);
            $this->db->busyTimeout(5000);
            $this->db->enableExceptions(true);
            
            // Создаем таблицы если их нет
            $this->createTables();
            
            error_log("Database initialized successfully at: {$dbPath}");
            
        } catch (Exception $e) {
            error_log("Database init error: " . $e->getMessage());
            throw new Exception("Cannot initialize database: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS feeds (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            yml_url TEXT NOT NULL,
            youla_url TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
            next_update DATETIME DEFAULT CURRENT_TIMESTAMP,
            update_interval INTEGER DEFAULT 30,
            status TEXT DEFAULT 'active',
            items_count INTEGER DEFAULT 0
        );
        
        CREATE TABLE IF NOT EXISTS feed_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            feed_id TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ";
        
        $this->db->exec($sql);
    }
    
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];
        
        error_log("Action: {$action}, Method: {$method}");
        
        try {
            switch ($action) {
                case 'feeds':
                    if ($method === 'GET') {
                        $this->getFeeds();
                    } elseif ($method === 'POST') {
                        $this->createFeed();
                    } else {
                        $this->sendError('Method not allowed', 405);
                    }
                    break;
                    
                case 'feed':
                    if ($method === 'GET') {
                        $this->getFeed();
                    } elseif ($method === 'PUT') {
                        $this->updateFeed();
                    } elseif ($method === 'DELETE') {
                        $this->deleteFeed();
                    } else {
                        $this->sendError('Method not allowed', 405);
                    }
                    break;
                    
                case 'update':
                    if ($method === 'POST') {
                        $this->manualUpdate();
                    } else {
                        $this->sendError('Method not allowed', 405);
                    }
                    break;
                    
                case 'stats':
                    if ($method === 'GET') {
                        $this->getStats();
                    } else {
                        $this->sendError('Method not allowed', 405);
                    }
                    break;
                    
                case 'logs':
                    if ($method === 'GET') {
                        $this->getLogs();
                    } else {
                        $this->sendError('Method not allowed', 405);
                    }
                    break;
                    
                case 'cron':
                    if ($method === 'GET') {
                        $this->runCron();
                    } else {
                        $this->sendError('Method not allowed', 405);
                    }
                    break;
                    
                case 'test':
                    if ($method === 'POST') {
                        $this->testUrl();
                    } else {
                        $this->sendError('Method not allowed', 405);
                    }
                    break;
                    
                case 'health':
                    $this->healthCheck();
                    break;
                    
                case 'csrf':
                    $this->getCsrfToken();
                    break;
                    
                default:
                    $this->sendResponse([
                        'service' => 'YML to Youla Converter API',
                        'version' => '1.0',
                        'endpoints' => [
                            'GET /api.php?action=feeds' => 'Get feeds list',
                            'POST /api.php?action=feeds' => 'Create new feed',
                            'GET /api.php?action=feed&id={id}' => 'Get feed details',
                            'PUT /api.php?action=feed&id={id}' => 'Update feed',
                            'DELETE /api.php?action=feed&id={id}' => 'Delete feed',
                            'POST /api.php?action=update&id={id}' => 'Manual update feed',
                            'GET /api.php?action=stats' => 'Get statistics',
                            'GET /api.php?action=logs' => 'Get logs',
                            'GET /api.php?action=cron' => 'Run cron updates',
                            'POST /api.php?action=test' => 'Test YML URL',
                            'GET /api.php?action=health' => 'Health check',
                            'GET /api.php?action=csrf' => 'Get CSRF token'
                        ]
                    ]);
            }
        } catch (Exception $e) {
            error_log("API Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->sendError('Internal server error', 500);
        }
    }
    
    private function getFeeds() {
        error_log("Getting feeds list");
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;
        
        try {
            // Получаем общее количество
            $totalResult = $this->db->query("SELECT COUNT(*) as total FROM feeds");
            $totalRow = $totalResult->fetchArray(SQLITE3_ASSOC);
            $total = $totalRow['total'] ?? 0;
            
            // Получаем фиды с пагинацией
            $stmt = $this->db->prepare("
                SELECT * FROM feeds 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
            $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
            
            $result = $stmt->execute();
            $feeds = [];
            
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                // Добавляем информацию о файле
                $feedPath = __DIR__ . "/feeds/{$row['id']}.xml";
                $row['file_exists'] = file_exists($feedPath);
                $row['file_size'] = $row['file_exists'] ? filesize($feedPath) : 0;
                $row['file_url'] = $this->getBaseUrl() . "/download.php?id=" . $row['id'];
                
                $feeds[] = $row;
            }
            
            error_log("Found {$total} feeds, returning " . count($feeds) . " for page {$page}");
            
            $this->sendResponse([
                'success' => true,
                'data' => $feeds,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $total > 0 ? ceil($total / $limit) : 1
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting feeds: " . $e->getMessage());
            $this->sendError('Error getting feeds list', 500);
        }
    }
    
    private function createFeed() {
        error_log("Starting feed creation process");
        
        try {
            // Получаем данные
            $input = file_get_contents('php://input');
            error_log("Raw input: " . substr($input, 0, 500));
            
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON parse error: " . json_last_error_msg());
                $this->sendError('Invalid JSON format: ' . json_last_error_msg(), 400);
            }
            
            if (empty($data['yml_url'])) {
                $this->sendError('YML URL is required', 400);
            }
            
            $ymlUrl = trim($data['yml_url']);
            $feedName = trim($data['name'] ?? 'Мой фид');
            $interval = intval($data['update_interval'] ?? 30);
            
            // Валидация
            if (!filter_var($ymlUrl, FILTER_VALIDATE_URL)) {
                $this->sendError('Invalid URL format', 400);
            }
            
            if ($interval < 5 || $interval > 1440) {
                $this->sendError('Update interval must be between 5 and 1440 minutes', 400);
            }
            
            error_log("Creating feed: {$feedName}, URL: {$ymlUrl}, Interval: {$interval}");
            
            // Создаем конвертер
            $converter = new YMLConverter();
            
            // Загружаем YML фид
            error_log("Fetching YML data from: {$ymlUrl}");
            $ymlData = $converter->fetchYMLFeed($ymlUrl);
            
            if (!$ymlData) {
                error_log("Failed to fetch YML data");
                $this->sendError('Cannot fetch YML feed. Please check the URL and try again.', 400);
            }
            
            error_log("YML data fetched successfully");
            
            // Генерируем уникальный ID
            $feedId = 'feed_' . time() . '_' . substr(md5($ymlUrl . microtime()), 0, 8);
            
            // Создаем фид для Юлы
            error_log("Generating Youla feed with ID: {$feedId}");
            $feedPath = $converter->generateYoulaFeed($ymlData, $feedId);
            
            if (!file_exists($feedPath)) {
                throw new Exception("Failed to create feed file at: {$feedPath}");
            }
            
            // Считаем количество товаров
            $itemsCount = $converter->countItems($ymlData);
            error_log("Feed generated with {$itemsCount} items at: {$feedPath}");
            
            // Сохраняем в базу данных
            $youlaUrl = $this->getBaseUrl() . "/download.php?id=" . $feedId;
            
            $stmt = $this->db->prepare("
                INSERT INTO feeds (
                    id, name, yml_url, youla_url, update_interval, 
                    items_count, next_update, status
                ) VALUES (
                    :id, :name, :yml_url, :youla_url, :interval,
                    :count, datetime('now', '+' || :interval || ' minutes'), 'active'
                )
            ");
            
            $stmt->bindValue(':id', $feedId, SQLITE3_TEXT);
            $stmt->bindValue(':name', $feedName, SQLITE3_TEXT);
            $stmt->bindValue(':yml_url', $ymlUrl, SQLITE3_TEXT);
            $stmt->bindValue(':youla_url', $youlaUrl, SQLITE3_TEXT);
            $stmt->bindValue(':interval', $interval, SQLITE3_INTEGER);
            $stmt->bindValue(':count', $itemsCount, SQLITE3_INTEGER);
            
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $this->db->lastErrorMsg());
            }
            
            // Логируем успешное создание
            $this->logEvent($feedId, "Фид успешно создан. Товаров: {$itemsCount}");
            
            error_log("Feed created successfully: {$feedId}");
            
            $this->sendResponse([
                'success' => true,
                'feed_id' => $feedId,
                'feed_url' => $youlaUrl,
                'items_count' => $itemsCount,
                'file_size' => filesize($feedPath),
                'next_update' => date('Y-m-d H:i:s', time() + ($interval * 60)),
                'message' => 'Фид успешно создан'
            ]);
            
        } catch (Exception $e) {
            error_log("Error creating feed: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Удаляем файл если он был создан
            if (isset($feedId) && !empty($feedId)) {
                $feedPath = __DIR__ . "/feeds/{$feedId}.xml";
                if (file_exists($feedPath)) {
                    @unlink($feedPath);
                    error_log("Cleaned up feed file: {$feedPath}");
                }
            }
            
            $this->sendError('Ошибка создания фида: ' . $e->getMessage(), 500);
        }
    }
    
    private function getFeed() {
        $feedId = $_GET['id'] ?? '';
        
        if (empty($feedId)) {
            $this->sendError('Feed ID is required', 400);
        }
        
        error_log("Getting feed details: {$feedId}");
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM feeds WHERE id = :id");
            $stmt->bindValue(':id', $feedId, SQLITE3_TEXT);
            $result = $stmt->execute();
            $feed = $result->fetchArray(SQLITE3_ASSOC);
            
            if (!$feed) {
                $this->sendError('Feed not found', 404);
            }
            
            // Добавляем дополнительную информацию
            $feedPath = __DIR__ . "/feeds/{$feedId}.xml";
            $feed['file_exists'] = file_exists($feedPath);
            
            if ($feed['file_exists']) {
                $feed['file_size'] = filesize($feedPath);
                $feed['file_mtime'] = date('Y-m-d H:i:s', filemtime($feedPath));
                
                // Пробуем посчитать товары в XML
                try {
                    $xml = @simplexml_load_file($feedPath);
                    if ($xml) {
                        $feed['xml_items_count'] = count($xml->shop->offers->offer ?? []);
                    }
                } catch (Exception $e) {
                    $feed['xml_items_count'] = 0;
                }
            }
            
            $this->sendResponse([
                'success' => true,
                'data' => $feed
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting feed: " . $e->getMessage());
            $this->sendError('Error getting feed details', 500);
        }
    }
    
    private function updateFeed() {
        $feedId = $_GET['id'] ?? '';
        
        if (empty($feedId)) {
            $this->sendError('Feed ID is required', 400);
        }
        
        error_log("Updating feed: {$feedId}");
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendError('Invalid JSON format', 400);
            }
            
            // Проверяем существование фида
            $stmt = $this->db->prepare("SELECT id FROM feeds WHERE id = :id");
            $stmt->bindValue(':id', $feedId, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            if (!$result->fetchArray()) {
                $this->sendError('Feed not found', 404);
            }
            
            $updates = [];
            $params = [':id' => $feedId];
            
            // Обновляем название если указано
            if (isset($data['name']) && !empty(trim($data['name']))) {
                $updates[] = "name = :name";
                $params[':name'] = trim($data['name']);
            }
            
            // Обновляем интервал если указан
            if (isset($data['update_interval'])) {
                $interval = intval($data['update_interval']);
                if ($interval >= 5 && $interval <= 1440) {
                    $updates[] = "update_interval = :interval";
                    $updates[] = "next_update = datetime('now', '+' || :interval || ' minutes')";
                    $params[':interval'] = $interval;
                }
            }
            
            // Обновляем статус если указан
            if (isset($data['status']) && in_array($data['status'], ['active', 'inactive', 'paused'])) {
                $updates[] = "status = :status";
                $params[':status'] = $data['status'];
            }
            
            if (empty($updates)) {
                $this->sendError('No valid fields to update', 400);
            }
            
            $sql = "UPDATE feeds SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if (!$stmt->execute()) {
                $this->sendError('Database error: ' . $this->db->lastErrorMsg(), 500);
            }
            
            $this->logEvent($feedId, "Настройки фида обновлены");
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Настройки фида обновлены'
            ]);
            
        } catch (Exception $e) {
            error_log("Error updating feed: " . $e->getMessage());
            $this->sendError('Error updating feed', 500);
        }
    }
    
    private function deleteFeed() {
        $feedId = $_GET['id'] ?? '';
        
        if (empty($feedId)) {
            $this->sendError('Feed ID is required', 400);
        }
        
        error_log("Deleting feed: {$feedId}");
        
        try {
            // Удаляем XML файл
            $feedPath = __DIR__ . "/feeds/{$feedId}.xml";
            if (file_exists($feedPath)) {
                if (!unlink($feedPath)) {
                    error_log("Warning: Could not delete feed file: {$feedPath}");
                }
            }
            
            // Удаляем из базы данных
            $stmt = $this->db->prepare("DELETE FROM feeds WHERE id = :id");
            $stmt->bindValue(':id', $feedId, SQLITE3_TEXT);
            
            if (!$stmt->execute()) {
                $this->sendError('Database error: ' . $this->db->lastErrorMsg(), 500);
            }
            
            // Удаляем логи фида
            $this->db->exec("DELETE FROM feed_logs WHERE feed_id = '{$feedId}'");
            
            $this->logEvent($feedId, "Фид удален");
            
            error_log("Feed deleted: {$feedId}");
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Фид успешно удален'
            ]);
            
        } catch (Exception $e) {
            error_log("Error deleting feed: " . $e->getMessage());
            $this->sendError('Error deleting feed', 500);
        }
    }
    
    private function manualUpdate() {
        $feedId = $_GET['id'] ?? '';
        
        if (empty($feedId)) {
            $this->sendError('Feed ID is required', 400);
        }
        
        error_log("Manual update for feed: {$feedId}");
        
        try {
            // Получаем информацию о фиде
            $stmt = $this->db->prepare("SELECT * FROM feeds WHERE id = :id");
            $stmt->bindValue(':id', $feedId, SQLITE3_TEXT);
            $result = $stmt->execute();
            $feed = $result->fetchArray(SQLITE3_ASSOC);
            
            if (!$feed) {
                $this->sendError('Feed not found', 404);
            }
            
            $converter = new YMLConverter();
            
            // Загружаем YML фид
            $ymlData = $converter->fetchYMLFeed($feed['yml_url']);
            
            if (!$ymlData) {
                throw new Exception("Cannot fetch YML feed");
            }
            
            // Генерируем новый фид
            $feedPath = $converter->generateYoulaFeed($ymlData, $feedId);
            $itemsCount = $converter->countItems($ymlData);
            
            // Обновляем базу данных
            $stmt = $this->db->prepare("
                UPDATE feeds 
                SET last_updated = CURRENT_TIMESTAMP,
                    next_update = datetime('now', '+' || update_interval || ' minutes'),
                    items_count = :count
                WHERE id = :id
            ");
            
            $stmt->bindValue(':id', $feedId, SQLITE3_TEXT);
            $stmt->bindValue(':count', $itemsCount, SQLITE3_INTEGER);
            
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $this->db->lastErrorMsg());
            }
            
            $this->logEvent($feedId, "Ручное обновление выполнено. Товаров: {$itemsCount}");
            
            error_log("Manual update successful for feed: {$feedId}, items: {$itemsCount}");
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Фид успешно обновлен',
                'items_count' => $itemsCount,
                'next_update' => date('Y-m-d H:i:s', time() + ($feed['update_interval'] * 60))
            ]);
            
        } catch (Exception $e) {
            error_log("Manual update error for feed {$feedId}: " . $e->getMessage());
            $this->logEvent($feedId, "Ошибка обновления: " . $e->getMessage());
            $this->sendError('Ошибка обновления фида: ' . $e->getMessage(), 500);
        }
    }
    
    private function getStats() {
        error_log("Getting system stats");
        
        try {
            // Общая статистика
            $totalFeeds = $this->db->querySingle("SELECT COUNT(*) FROM feeds") ?: 0;
            $activeFeeds = $this->db->querySingle("SELECT COUNT(*) FROM feeds WHERE status = 'active'") ?: 0;
            $totalItems = $this->db->querySingle("SELECT SUM(items_count) FROM feeds WHERE items_count IS NOT NULL") ?: 0;
            
            // Статистика по обновлениям
            $today = date('Y-m-d');
            $todayUpdates = $this->db->querySingle("SELECT COUNT(*) FROM feeds WHERE date(last_updated) = '{$today}'") ?: 0;
            
            // Использование диска
            $feedsDir = __DIR__ . '/feeds';
            $totalSize = 0;
            $feedFiles = 0;
            
            if (file_exists($feedsDir)) {
                foreach (new DirectoryIterator($feedsDir) as $file) {
                    if ($file->isFile() && $file->getExtension() === 'xml') {
                        $totalSize += $file->getSize();
                        $feedFiles++;
                    }
                }
            }
            
            // Следующие обновления
            $nextUpdates = [];
            $result = $this->db->query("
                SELECT id, name, next_update 
                FROM feeds 
                WHERE status = 'active' 
                ORDER BY next_update ASC 
                LIMIT 5
            ");
            
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $nextUpdates[] = $row;
            }
            
            $this->sendResponse([
                'success' => true,
                'stats' => [
                    'total_feeds' => $totalFeeds,
                    'active_feeds' => $activeFeeds,
                    'feed_files' => $feedFiles,
                    'total_items' => $totalItems,
                    'today_updates' => $todayUpdates,
                    'disk_usage_mb' => round($totalSize / 1024 / 1024, 2),
                    'disk_usage_bytes' => $totalSize,
                    'next_updates' => $nextUpdates,
                    'server_time' => date('Y-m-d H:i:s'),
                    'server_timezone' => date_default_timezone_get(),
                    'php_version' => phpversion(),
                    'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                    'memory_limit' => ini_get('memory_limit')
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting stats: " . $e->getMessage());
            $this->sendError('Ошибка получения статистики', 500);
        }
    }
    
    private function getLogs() {
        $feedId = $_GET['feed_id'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = min(100, max(1, intval($_GET['limit'] ?? 50)));
        $offset = ($page - 1) * $limit;
        
        try {
            $where = '';
            $params = [];
            
            if ($feedId) {
                $where = "WHERE feed_id = :feed_id";
                $params[':feed_id'] = $feedId;
            }
            
            // Общее количество
            $countSql = "SELECT COUNT(*) as total FROM feed_logs {$where}";
            $countStmt = $this->db->prepare($countSql);
            
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            
            $totalResult = $countStmt->execute();
            $totalRow = $totalResult->fetchArray(SQLITE3_ASSOC);
            $total = $totalRow['total'] ?? 0;
            
            // Получаем логи
            $sql = "SELECT * FROM feed_logs {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, SQLITE3_INTEGER);
            $stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
            
            $result = $stmt->execute();
            $logs = [];
            
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $logs[] = $row;
            }
            
            $this->sendResponse([
                'success' => true,
                'data' => $logs,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => $total > 0 ? ceil($total / $limit) : 1
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting logs: " . $e->getMessage());
            $this->sendError('Ошибка получения логов', 500);
        }
    }
    
    private function runCron() {
        error_log("Running cron task");
        
        require_once 'scheduler.php';
        
        try {
            $scheduler = new FeedScheduler();
            $result = $scheduler->checkAndUpdateFeeds();
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Cron task executed',
                'result' => $result
            ]);
            
        } catch (Exception $e) {
            error_log("Cron error: " . $e->getMessage());
            $this->sendError('Cron execution error: ' . $e->getMessage(), 500);
        }
    }
    
    private function testUrl() {
        error_log("Testing URL");
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendError('Invalid JSON format', 400);
            }
            
            $url = $data['url'] ?? '';
            
            if (empty($url)) {
                $this->sendError('URL is required', 400);
            }
            
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $this->sendError('Invalid URL format', 400);
            }
            
            error_log("Testing URL: {$url}");
            
            $converter = new YMLConverter();
            $result = $converter->fetchYMLFeed($url);
            
            if ($result) {
                $itemsCount = $converter->countItems($result);
                
                // Получаем пример товара
                $sample = $this->getSampleProduct($result);
                
                $this->sendResponse([
                    'success' => true,
                    'message' => 'YML фид доступен и корректен',
                    'items_count' => $itemsCount,
                    'sample_product' => $sample,
                    'is_valid' => true
                ]);
            } else {
                $this->sendError('Не удалось загрузить YML фид. Проверьте URL и доступность.', 400);
            }
            
        } catch (Exception $e) {
            error_log("URL test error: " . $e->getMessage());
            $this->sendError('Ошибка проверки URL: ' . $e->getMessage(), 500);
        }
    }
    
    private function getSampleProduct($ymlData) {
        $shop = $ymlData['shop'] ?? $ymlData['yml_catalog']['shop'] ?? [];
        $offers = $shop['offers']['offer'] ?? [];
        
        if (is_array($offers) && count($offers) > 0) {
            $sample = $offers[0];
            return [
                'name' => $sample['name'] ?? 'Не указано',
                'price' => $sample['price'] ?? '0',
                'currency' => $sample['currencyId'] ?? 'RUR',
                'category_id' => $sample['categoryId'] ?? '',
                'available' => ($sample['available'] ?? 'false') == 'true'
            ];
        }
        
        return null;
    }
    
    private function healthCheck() {
        error_log("Health check");
        
        $checks = [
            'database' => false,
            'feeds_directory' => false,
            'logs_directory' => false,
            'php_version' => phpversion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'allow_url_fopen' => ini_get('allow_url_fopen'),
            'curl_enabled' => function_exists('curl_init')
        ];
        
        // Проверка базы данных
        try {
            $result = $this->db->querySingle("SELECT 1");
            $checks['database'] = ($result === 1);
        } catch (Exception $e) {
            $checks['database_error'] = $e->getMessage();
        }
        
        // Проверка директорий
        $checks['feeds_directory'] = is_writable(FEEDS_DIR);
        $checks['logs_directory'] = is_writable(LOGS_DIR);
        
        // Проверка свободного места
        $checks['free_disk_space'] = round(disk_free_space(__DIR__) / 1024 / 1024 / 1024, 2) . ' GB';
        
        $this->sendResponse([
            'success' => true,
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => $checks
        ]);
    }
    
    private function getCsrfToken() {
        error_log("Getting CSRF token");
        
        $this->sendResponse([
            'success' => true,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }
    
    private function logEvent($feedId, $message) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO feed_logs (feed_id, message) 
                VALUES (:feed_id, :message)
            ");
            $stmt->bindValue(':feed_id', $feedId, SQLITE3_TEXT);
            $stmt->bindValue(':message', $message, SQLITE3_TEXT);
            $stmt->execute();
            
            // Также пишем в файловый лог
            $logFile = __DIR__ . '/logs/feeds.log';
            $logEntry = date('Y-m-d H:i:s') . " [{$feedId}] {$message}\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);
            
        } catch (Exception $e) {
            error_log("Log event error: " . $e->getMessage());
        }
    }
    
    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['SCRIPT_NAME']);
        
        // Убираем дублирующиеся слеши
        $path = rtrim($path, '/');
        
        return $protocol . '://' . $host . $path;
    }
    
    private function sendResponse($data) {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => true,
            'message' => $message,
            'code' => $code
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Запуск API
try {
    $api = new API();
    $api->handleRequest();
} catch (Exception $e) {
    error_log("Fatal API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => 'Internal server error: ' . $e->getMessage(),
        'code' => 500
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}