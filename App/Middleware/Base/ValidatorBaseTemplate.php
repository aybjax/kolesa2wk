<?php
namespace App\Middleware\Base;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Valitron\Validator;

/**
 * Базовый класс шаблон для классов валидаторов
 * Различается только в одном абстрактном методе: initializeValidatorWithRules
 *
 * @package App\Middleware\Base
 */
abstract class ValidatorBaseTemplate
{
    /**
     * код статуса client error
     */
    public const CLIENT_ERROR = 400;

    /**
     * Имеет основные шаблонные команды
     *
     * @param  Request        $request
     * @param  RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $validator  = $this->initializeValidatorWithRules($request->getParsedBody());

        if ($validator->validate()) {
            return $handler->handle($request);
        }

        $statusCode = self::CLIENT_ERROR;
        $response   = $this->getErrorContent($validator);

        return $this->appendHeader($response, $statusCode);
    }

    /**
     * Абстрактный метод
     * Определяет правтла валидации
     *
     * @param  array     $data
     * @return Validator
     */
    abstract protected function initializeValidatorWithRules(array $data) : Validator;

    /**
     * Добавляет header запроса
     *
     * @param  Response $response
     * @param  int      $statusCode
     * @return Response
     */
    protected function appendHeader(Response $response, int $statusCode):ResponseInterface
    {
        return $response->withHeader(
            'Content-Type',
            'application/json'
        )->withStatus($statusCode);
    }

    /**
     * Рефакторит и добавляет текст ошибки валидации к ответу
     *
     * @param  Validator         $validator
     * @return ResponseInterface
     */
    protected function getErrorContent(Validator $validator):ResponseInterface
    {
        $response       = new Response();
        $validatorError = $this->parseError($validator);

        $msg = [
            "status"     => "error",
            "validation" => $validatorError,
            "message"    => "Переданы не все данные",
        ];

        $response->getBody()->write(\json_encode($msg, JSON_UNESCAPED_UNICODE));

        return $response;
    }

    /**
     * Метод для чистки сообщения ошибки валидации
     *
     * @param  Validator $validator
     * @return array
     */
    abstract protected function parseError(Validator $validator) : array;
}
