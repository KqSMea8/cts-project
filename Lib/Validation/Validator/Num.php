<?php 

class Lib_Validation_Validator_Num implements Lib_Validation_Validator_Interface
{
    public function base($data, $rule)
    {
        return is_numeric($data) ? true : false;
    }

    public function min($data, $rule)
    {
        return $data < $rule ? false : true;
    }

    public function max($data, $rule)
    {
        return $data > $rule ? false : true;
    }
}
