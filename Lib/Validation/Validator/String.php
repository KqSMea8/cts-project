<?php 

class Lib_Validation_Validator_String implements Lib_Validation_Validator_Interface
{
    public function base($data, $rule)
    {
        return is_string($data) ? true : false;
    }

    public function min($data, $rule)
    {
        return $this->strLen($data) < $rule ? false : true;
    }

    public function max($data, $rule)
    {
        return $this->strLen($data) > $rule ? false : true;
    }

    public function strLen($str)
    {
        return mb_strlen($str, 'UTF-8');
    }
}
