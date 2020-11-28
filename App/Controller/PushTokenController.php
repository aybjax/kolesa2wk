<?php
namespace App\Controller;

use App\Database\Exception\StartServerException;
use App\Database\PushTokenDBFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Класс контроллер для соединения url c бд
 *
 * @package App\Controller
 */
class PushTokenController
{
    /**
     * Json ответ при ошибке запроса
     *
     * @const string
     */
    public const ERROR_MSG_JSON = '{"status": "error",
        "message": "Ошибка при обращении к базе данных"}';

    /**
     * Json ответ OK
     *
     * @const string
     */
    public const OK_MSG_JSON = '{"status": "ok"}';


    /**
     * Экземпля класса для взаимодействия с бд
     *
     * @var PushTokenDB
     */
    protected $db;

    /**
     * Конструктор PushTokenController
     *
     * @throws StartServerException
     */
    public function __construct()
    {
        try {
            $this->db = PushTokenDBFactory::createDatabase();
        } catch (StartServerException $e) {
            throw new StartServerException($e->getMessage());
        }
    }

    /**
     * Метод для соединения uri /save c бд
     *
     * @param  array      $data
     * @return void
     * @throws \Exception
     */
    protected function save(array $data): void
    {
        $this->db->insertData($data);
    }

    /**
     * Метод для соединения uri /delete c бд
     *
     * @param  array      $data
     * @return void
     * @throws \Exception
     */
    protected function delete(array $data): void
    {
        $this->db->deleteData($data);
    }

    /**
     * Метод для соединения uri /get c бд
     *
     * @param  array      $data
     * @return ?array
     * @throws \Exception
     */
    protected function get(array $data): ?array
    {
        return $this->db->retrieveData($data);
    }

    /**
     * Метод шаблон => одинаков для каждого uri
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @return ResponseInterface
     */
    public function router(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        $data = $request->getParsedBody();
        $uri  = $request->getUri();
        $uri  = $uri->getPath();

        try {
            $result = $this->chooseSpecificCommand($uri, $data);
        } catch (\Exception $e) {
            return $this->returnError($response);
        }

        if ($result !== null) {
            return $this->returnPayload($result, $response);
        }

        return $this->returnOK($response);
    }

    /**
     * Метод для соединения каждого uri c методом этого класса
     * Меняет единственную различную команду в router
     *
     * @param  string     $uri
     * @param  array      $content
     * @return array|null
     * @throws \Exception
     */
    protected function chooseSpecificCommand(string $uri, array $content) : ?array
    {
        switch ($uri) {
            case '/save':
                return $this->save($content);
                break;
            case '/delete':
                return $this->delete($content);
                break;
            case '/get':
                return $this->get($content);
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * Метод возвращает сообщение ошибки
     *
     * @param  ResponseInterface $response
     * @return ResponseInterface
     */
    protected function returnError(ResponseInterface $response) : ResponseInterface
    {
        $response->getBody()->write(self::ERROR_MSG_JSON);

        return $response->withHeader(
            'Content-Type',
            'application/json'
        )->withStatus(500);
    }

    /**
     * Метод возвращает запрос из бд
     *
     * @param  array             $result
     * @param  ResponseInterface $response
     * @return ResponseInterface
     */
    protected function returnPayload(array $result, ResponseInterface $response) : ResponseInterface
    {
        $payload = [
            "status" => "ok",
            "tokens" => $result,
        ];

        $response->getBody()->write(\json_encode($payload));

        return $response->withHeader(
            'Content-Type',
            'application/json'
        )->withStatus(200);
    }

    /**
     * Метод возвращает сообщение ОК
     *
     * @param  ResponseInterface $response
     * @return ResponseInterface
     */
    protected function returnOK(ResponseInterface $response) : ResponseInterface
    {
        $response->getBody()->write(self::OK_MSG_JSON);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
