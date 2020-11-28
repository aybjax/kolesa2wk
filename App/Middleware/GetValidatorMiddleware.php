<?php
namespace App\Middleware;

use App\Middleware\Base\ValidatorBaseTemplate;
use App\Validator\GetValidator;
use Valitron\Validator;

/**
 * Имплементация класса ValidatorBaseTemplate
 * Определяет правила валидации для запроса /get
 *
 * @package App\Middleware
 */
class GetValidatorMiddleware extends ValidatorBaseTemplate
{
    /**
     * Метод для определения правил валидации для запроса /get
     *
     * @param  array|null $data
     * @return Validator
     */
    protected function initializeValidatorWithRules(?array $data) : Validator
    {
        return GetValidator::getValidator($data);
    }

    /**
     * Метод для чистки сообщения ошибки валидации
     *
     * @param  Validator $validator
     * @return array
     */
    protected function parseError(Validator $validator): array
    {
        return GetValidator::getValidationErrors($validator);
    }
}
