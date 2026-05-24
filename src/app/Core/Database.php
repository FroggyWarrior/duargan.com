<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instances = [];
    private $pdo;

    private function __construct($config) {
        try {
            $this->pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}",
                $config['user'],
                $config['password']
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance($type = 'content') {
        if (!isset(self::$instances[$type])) {
            $config = self::getConfig($type);
            self::$instances[$type] = new self($config);
        }
        return self::$instances[$type];
    }

    private static function getConfig($type) {
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'duargan_music';

        if ($type === 'admin') {
            return [
                'host' => $host,
                'port' => $port,
                'dbname' => $dbname,
                'user' => getenv('WRITER_DB_USER') ?: 'duargan_writer',
                'password' => getenv('WRITER_DB_PASSWORD') ?: 'writer_password'
            ];
        } else { // content (read-only)
            return [
                'host' => $host,
                'port' => $port,
                'dbname' => $dbname,
                'user' => getenv('READER_DB_USER') ?: 'duargan_reader',
                'password' => getenv('READER_DB_PASSWORD') ?: 'reader_password'
            ];
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}