<?php

namespace CapsuleLib\Database\Connection;

use PDO;
use PDOException;
use RuntimeException;

class MariaDBConnection
{
    private static ?PDO $pdo = null;
    private static array $config = [
        'host' => 'db', // <- très important !
        'dbname' => 'ssa_dev',
        'user' => 'admin',
        'pass' => 'admin',
        'port' => 3306,
        'charset' => 'utf8mb4'
    ];

    public static function setConfig(array $conf): void
    {
        self::$config = array_merge(self::$config, $conf);
        self::$pdo = null; // reset
    }

    public static function getInstance(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::$config['host'],
                self::$config['port'],
                self::$config['dbname'],
                self::$config['charset']
            );
            try {
                self::$pdo = new PDO($dsn, self::$config['user'], self::$config['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                throw new RuntimeException("Connexion MySQL échouée : " . $e->getMessage(), 0, $e);
            }
        }
        return self::$pdo;
    }
}
