<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Items implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $items = explode(",", $value);

        $index = array_search("",$items);
        if($index !== FALSE){
            unset($items[$index]);
        }

        foreach ($items as $v) {
          $pos = strpos($v, ":");
          if($pos === false){
            return false;
          }

          list($a, $b) = explode(":", $v);
          if ( empty($a) || empty($b) ) {
              return false;
          }

          if ( !preg_match('/^[0-9 ]+$/', trim($a)) || !preg_match('/^[0-9 ]+$/', trim($b)) ) {
              return false;
          }

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
        return 'The items needs be in the format id:quantity, id:quantity';
    }
}
