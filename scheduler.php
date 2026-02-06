<?php
require_once 'config.php';
require_once 'converter.php';

class FeedScheduler {
    private $db;

    public function __construct() {
        $this->initDatabase();
    }

    private function initDatabase() {
        $dbPath = __DIR__ . '/feeds/feeds.db';

        if (!is_dir(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0755, true);
        }

        $this->db = new SQLite3($dbPath);
        $this->db->busyTimeout(5000);
        $this->db->enableExceptions(true);
        $this->ensureFeedSchema();
    }

    private function ensureFeedSchema() {
        $columns = [];
        $result = $this->db->query("PRAGMA table_info(feeds)");

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $columns[] = $row['name'];
        }

        $this->addColumnIfMissing($columns, 'feed_slug', "ALTER TABLE feeds ADD COLUMN feed_slug TEXT");
        $this->addColumnIfMissing($columns, 'feed_type', "ALTER TABLE feeds ADD COLUMN feed_type TEXT DEFAULT 'youla'");
        $this->addColumnIfMissing($columns, 'feed_file', "ALTER TABLE feeds ADD COLUMN feed_file TEXT");
    }

    private function addColumnIfMissing($columns, $columnName, $sql) {
        if (!in_array($columnName, $columns, true)) {
            $this->db->exec($sql);
        }
    }

    public function checkAndUpdateFeeds() {
        $result = [
            'updated' => 0,
            'errors' => 0,
            'feeds' => []
        ];

        $stmt = $this->db->prepare("
            SELECT * FROM feeds
            WHERE status = 'active'
              AND datetime(next_update) <= datetime('now')
            ORDER BY next_update ASC
        ");
        $rows = $stmt->execute();

        while ($feed = $rows->fetchArray(SQLITE3_ASSOC)) {
            try {
                $updateResult = $this->updateFeed($feed);
                $result['updated']++;
                $result['feeds'][] = $updateResult;
            } catch (Exception $e) {
                $result['errors']++;
                $this->logEvent($feed['id'], "Ошибка обновления: " . $e->getMessage());
                $this->db->exec("UPDATE feeds SET status = 'error' WHERE id = '{$feed['id']}'");
            }
        }

        return $result;
    }

    private function updateFeed($feed) {
        $converter = new YMLConverter();
        $engine = new FeedConverterEngine($converter);
        $feedType = $feed['feed_type'] ?? 'youla';

        if (!$engine->isSupportedType($feedType)) {
            throw new Exception("Unsupported feed type: {$feedType}");
        }

        $ymlData = $converter->fetchYMLFeed($feed['yml_url']);

        if (!$ymlData) {
            throw new Exception("Cannot fetch YML feed");
        }

        $feedFileBase = $this->buildFeedFileBase($feed);
        $feedPath = $engine->generateFeed($ymlData, $feedFileBase, $feedType);
        $itemsCount = $converter->countItems($ymlData);

        $stmt = $this->db->prepare("
            UPDATE feeds 
            SET last_updated = CURRENT_TIMESTAMP,
                next_update = datetime('now', '+' || update_interval || ' minutes'),
                items_count = :count,
                status = 'active'
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $feed['id'], SQLITE3_TEXT);
        $stmt->bindValue(':count', $itemsCount, SQLITE3_INTEGER);
        $stmt->execute();

        $this->logEvent($feed['id'], "Автообновление выполнено. Товаров: {$itemsCount}");

        return [
            'id' => $feed['id'],
            'feed_file' => $feed['feed_file'] ?? null,
            'feed_path' => $feedPath,
            'items_count' => $itemsCount
        ];
    }

    private function buildFeedFileBase($feed) {
        if (!empty($feed['feed_file'])) {
            return pathinfo($feed['feed_file'], PATHINFO_FILENAME);
        }

        return $feed['id'];
    }

    private function logEvent($feedId, $message) {
        $stmt = $this->db->prepare("
            INSERT INTO feed_logs (feed_id, message)
            VALUES (:feed_id, :message)
        ");
        $stmt->bindValue(':feed_id', $feedId, SQLITE3_TEXT);
        $stmt->bindValue(':message', $message, SQLITE3_TEXT);
        $stmt->execute();

        $logFile = __DIR__ . '/logs/feeds.log';
        $logEntry = date('Y-m-d H:i:s') . " [{$feedId}] {$message}\n";
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
