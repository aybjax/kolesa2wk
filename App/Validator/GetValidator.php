<?php
namespace App\Validator;

use App\Validator\Base\ValidatorBase;
use Valitron\Validator;

/**
 * Класс валидации метода GET
 *
 * @package App\Validator
 */
class GetValidator extends ValidatorBase
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

        $validator->rule('requiredWithout', 'device_id', 'user_id')->message('required')
            ->rule('requiredWithout', 'user_id', 'device_id')->message('required');

        return $validator;
    }
}
