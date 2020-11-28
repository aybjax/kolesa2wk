<?php
namespace App\Middleware;

use App\Middleware\Base\ValidatorBaseTemplate;
use App\Validator\SaveValidator;
use Valitron\Validator;

/**
 * Имплементация класса ValidatorBaseTemplate
 * Определяет правила валидации для запроса /save
 *
 * @package App\Middleware
 */
class SaveValidatorMiddleware extends ValidatorBaseTemplate
{
    /**
     * Метод для определения правил валидации для запроса /save
     *
     * @param  array     $data
     * @return Validator
     */
    protected function initializeValidatorWithRules(array $data) : Validator
    {
        return SaveValidator::getValidator($data);
    }

    /**
     * Метод для чистки сообщения ошибки валидации
     *
     * @param  Validator $validator
     * @return array
     */
    protected function parseError(Validator $validator): array
    {
        return SaveValidator::getValidationErrors($validator);
    }
}
