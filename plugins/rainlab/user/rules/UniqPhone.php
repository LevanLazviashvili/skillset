<?php namespace rainlab\User\Rules;

use Lang;
use Illuminate\Contracts\Validation\Rule;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;

/**
 * Reserved keyword rule.
 *
 * Validates for the use of any PHP-reserved keywords or constants, as specified from the PHP Manual
 * http://php.net/manual/en/reserved.keywords.php
 * http://php.net/manual/en/reserved.other-reserved-words.php
 */
class UniqPhone implements Rule
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
        $value2 = $value;
        $prefixes = ['+995', '995'];
        foreach ($prefixes AS $prefix) {
            $value2 = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $value2);
        }
        $existingUser = (new User)->whereIn('username', [$value, $value2])->where('status_id', '>=', 0)->where('id', '!=', config('auth.UserID'))->first();
        if ($existingUser) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The username has already been taken.';
    }
}
