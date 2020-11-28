<?php
namespace App\Validator;

use App\Validator\Base\ValidatorBase;
use Valitron\Validator;

/**
 * Класс валидации метода DELETE
 *
 * @package App\Validator
 */
class SaveValidator extends ValidatorBase
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

        $validator->rule('required', 'device_id')->message('required')
            ->rule('required', 'token')->message('required')
            ->rule('required', 'os')->message('required')
            ->rule('required', 'version')->message('required')
            ->rule('requiredWith', 'device_id', 'user_id')->message('required');

        return $validator;
    }
}
