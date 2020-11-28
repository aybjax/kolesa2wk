<?php
namespace App\Validator\Base;

use Valitron\Validator;

/**
 * Класс шаблон для валидации
 *
 * @package App\Validator\Base
 */
class ValidatorBase
{
    /**
     * Метод для чистки сообщения ошибки валидации
     *
     * @param  Validator $validator
     * @return array
     */
    public static function getValidationErrors(Validator $validator) : array
    {
        $validatorError = $validator->errors();

        foreach ($validatorError as $key => $val) {
            $validatorError[$key] = $val[0];
        }

        return $validatorError;
    }
}
