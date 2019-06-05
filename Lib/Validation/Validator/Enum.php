<?php 

class Lib_Validation_Validator_Enum implements Lib_Validation_Validator_Interface
{
    public function base($data, $rule)
    {
        return true;
    }

    public function min($data, $rule)
    {
        return $data < $rule ? false : true;
    }

    public function max($data, $rule)
    {
        return $data > $rule ? false : true;
    }

    public function enum($data, $rule)
    {
        return is_array($rule) && in_array($data, $rule) ? true : false;
    }

}
