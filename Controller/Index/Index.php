<?php
class Controller_Index_Index extends Controller_Abstract {

    protected $autoRedirect = false;
    protected $needLogin = false;

    public function run() {

        $info = Model_User::getUser();

        $this->outputHtml($info, 'index');
        return;
    }
}