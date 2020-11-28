<?php
namespace App\Middleware;

use App\Middleware\Base\ValidatorBaseTemplate;
use App\Validator\DeleteValidator;
use Valitron\Validator;

/**
 * Имплементация класса ValidatorBaseTemplate
 * Определяет правила валидации для запроса /delete
 *
 * @package App\Middleware
 */
class DeleteValidatorMiddleware extends ValidatorBaseTemplate
{
    /**
     * Метод для определения правил валидации для запроса /delete
     *
     * @param  array     $data
     * @return Validator
     */
    protected function initializeValidatorWithRules(array $data) : Validator
    {
        return DeleteValidator::getValidator($data);
    }

    /**
     * Метод для чистки сообщения ошибки валидации
     *
     * @param  Validator $validator
     * @return array
     */
    protected function parseError(Validator $validator): array
    {
        return DeleteValidator::getValidationErrors($validator);
    }
}
