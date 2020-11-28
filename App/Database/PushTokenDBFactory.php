<?php
namespace App\Database;

use App\Database\Exception\StartServerException;

/**
 * Класс для создания класса
 *  которая взаимодействует с бд
 */
class PushTokenDBFactory
{
    /**
     * Текст ошибки PDO
     *
     * @const string
     */
    public const TABLE_CREATION_ERROR = "Table creation failed";

    /**
     * Текст ошибки PDO
     *
     * @const string
     */
    public const CONNECTION_FAILED_ERROR = "Connection failed";

    /**
     * Соединение с бд
     *
     * @var PDO
     */
    protected static $dbConnection;

    /**
     * Класс который взаимодействует с бд
     *
     * @var \App\Database\Concrete\PushTokenDB
     */
    protected static $db;

    /**
     * Создает экземпляр класса PushTokenDB
     *  Кидает исключения при ошибке, кроме случаев
     *   существованя базы данных и таблицы
     *
     * @return PushTokenDB
     * @throws StartServerException
     */
    public static function createDatabase() : PushTokenDB
    {
        $args = self::getDatabaseCredentials();

        try {
            self::$dbConnection = self::connectDB($args);
        } catch (\PDOException $e) {
            throw new StartServerException(self::CONNECTION_FAILED_ERROR);
        }

        try {
            self::createTables();
        } catch (\PDOException $e) {
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] !== PushTokenDB::TABLE_EXISTS) {
                throw new StartServerException(self::TABLE_CREATION_ERROR);
            }
        }

        $redis    = PushTokenRedisFactory::createDatabase();
        self::$db = new PushTokenDB(self::$dbConnection, $redis);

        return self::$db;
    }

    /**
     * Создает аргумент для бд PDO
     *
     * @return array
     */
    protected static function getDatabaseCredentials(): array
    {
        $hostName       = \getenv("DATABASE_SERVER_NAME");
        $port           = \getenv("DATABASE_PORT");
        $dbName         = \getenv("DATABASE_NAME");
        $dbUser         = \getenv("DATABASE_USER");
        $dbUserPassword = \getenv("DATABASE_PASSWORD");

        return [
            "mysql:host=" . $hostName . ":" . $port . ";dbname=" . $dbName,
            $dbUser,
            $dbUserPassword,
        ];
    }

    /**
     * Создает экземпляр PDO
     *
     * @param  array $args
     * @return \PDO
     */
    protected static function connectDB(array $args) : \PDO
    {
        $dbConnection = new \PDO(...$args);
        $dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $dbConnection;
    }

    /**
     * Создает таблицу в бд
     */
    protected static function createTables() : void
    {
        $query = "
            CREATE TABLE Token (
            user_id BIGINT UNSIGNED,
            device_id VARCHAR(256) NOT NULL UNIQUE,
            token VARCHAR(256) NOT NULL UNIQUE,
            os VARCHAR(10),
            version VARCHAR(10)
        ) 
        ";

        self::$dbConnection->exec($query);
    }
}
