<?php namespace skillset\Offers\Rules;

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
class ArrayOfWorkers implements Rule
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
        $WorkersCount = (new Worker())->where('user_type', 1)->whereIn('id', $value)->count();
        return $WorkersCount == count($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'one of worker IDs is wrong';
    }
}
