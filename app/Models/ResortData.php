<?php

namespace App\Models;

use PDO;
use PDOStatement;

/**
 * Existing schema data access, centralized without changing table definitions.
 * The controller keeps PDO transactions and SQL semantics from the original app.
 */
final class ResortData
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $database = config('mnb.database');
            self::$connection = new PDO(
                'mysql:host='.$database['host'].';dbname='.$database['name'].';charset=utf8mb4',
                $database['user'],
                $database['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        }

        return self::$connection;
    }

    public static function query(string $sql, array $parameters = []): PDOStatement
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($parameters);

        return $statement;
    }
}
