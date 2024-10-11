<?php namespace rainlab\User\Rules;

use Lang;
use Illuminate\Contracts\Validation\Rule;
use RainLab\User\Models\Worker;

/**
 * Reserved keyword rule.
 *
 * Validates for the use of any PHP-reserved keywords or constants, as specified from the PHP Manual
 * http://php.net/manual/en/reserved.keywords.php
 * http://php.net/manual/en/reserved.other-reserved-words.php
 */
class IDNumber implements Rule
{

    public function validate($attribute, $value, $params)
    {
        return $this->passes($attribute, $value);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (in_array(strlen($value), [9,11])) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Incorrect id number';
    }
}
