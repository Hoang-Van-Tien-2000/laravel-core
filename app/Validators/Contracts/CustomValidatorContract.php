<?php

namespace App\Validators\Contracts;

use Core\Validators\Concerns\BaseValidatesAttributes;
use Illuminate\Contracts\Translation\Translator;
use Core\Validators\Contracts\BaseValidatorContract;

class CustomValidatorContract extends BaseValidatorContract
{
    use BaseValidatesAttributes;

    /**
     * @param Translator $translator
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     */
    public function __construct(Translator $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
    }

    /**
     * Validate that a required attribute exists.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function validateRequiredSelect($attribute, $value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif ((is_array($value) || $value instanceof \Countable) && count($value) < 1) {
            return false;
        }

        return true;
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validateNumber($attribute, $value, $parameters)
    {
        $regex = "/^[0-9]+$/";

        return preg_match($regex, $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public static function validateMaxLength($attribute, $value, $parameters)
    {
        return mb_strlen($value) <= $parameters[0];
    }

    protected function replaceMaxLength($message, $attribute, $rule, $parameters)
    {
        return str_replace(':max', $this->getDisplayableAttribute($parameters[0]), $message);
    }
}
