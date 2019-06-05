<?php 

class Lib_Validation_Validator_Custom
{
    public function base($data, $rule)
    {
        return true;
    }

    public function ip($data, $rule)
    {
        return filter_var($data, FILTER_VALIDATE_IP);
    }

    public function email($data, $rule)
    {
        return filter_var($data, FILTER_VALIDATE_EMAIL);
    }

    public function url($data, $rule)
    {
        return filter_var($data, FILTER_VALIDATE_URL);

    }

}
