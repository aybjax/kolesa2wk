<?php
namespace App\Database;

use Predis\Client;

/**
 * Класс для создания PushTokenRedis
 *
 * @package App\Database
 */
class PushTokenRedisFactory
{
    protected static $redis;

    /**
     * Класс который взаимодействует с бд
     *
     * @var \App\Database\Concrete\PushTokenRedis
     */
    protected static $db;

    /**
     * Возвращает экземпляр PushTokenRedis
     *  для работы с бд
     *
     * @return PushTokenRedis
     */
    public static function createDatabase() : PushTokenRedis
    {
        if (self::$db !== null) {
            return self::$db;
        }

        $host        = \getenv("REDIS_NAME");
        $port        = \getenv("REDIS_PORT");
        self::$redis = new Client(['host' => $host, 'port' => $port]);

        try {
            self::$redis->connect();
        } catch (\RedisException $e) {
            die($e->getMessage());
        }

        self::$db = new PushTokenRedis(self::$redis);

        return self::$db;
    }
}
