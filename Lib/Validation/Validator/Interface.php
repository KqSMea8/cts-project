<?php 

interface Lib_Validation_Validator_Interface
{
    public function base($data, $rule);

    public function min($data, $rule);

    public function max($data, $rule);

}

