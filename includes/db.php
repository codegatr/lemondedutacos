<?php
declare(strict_types=1);

/**
 * PDO veritabanı bağlantısı (singleton)
 * PHP 8.3+ uyumlu, hazırlıklı sorgu odaklı.
 */

require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_NAME,
        DB_CHARSET
    );
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci",
        ]);
    } catch (PDOException $e) {
        if (DEBUG) {
            die('DB error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
        error_log('DB connection failed: ' . $e->getMessage());
        die('Veritabanı bağlantısı kurulamadı. Lütfen daha sonra tekrar deneyin.');
    }
    return $pdo;
}
