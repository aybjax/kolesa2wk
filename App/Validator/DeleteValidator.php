<?php
namespace App\Validator;

use App\Validator\Base\ValidatorBase;
use Valitron\Validator;

/**
 * Класс валидации метода DELETE
 *
 * @package App\Validator
 */
class DeleteValidator extends ValidatorBase
{
    /**
     * Возвращает экземпляр класса Validator
     *  с правилами
     *
     * @param  array|null $data
     * @return Validator
     */
    public static function getValidator(?array $data) : Validator
    {
        $validator = new Validator($data);

        $validator->rule('required', 'token')->message('required');

        return $validator;
    }
}
