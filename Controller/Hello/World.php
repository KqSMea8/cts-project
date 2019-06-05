<?php
/**
 * Created by PhpStorm.
 * User: zhongfeng
 * Date: 2018/9/3
 * Time: 下午3:34
 */
class Controller_Hello_World extends Controller_Abstract {
    public function init(){

    }

    public function run(){

//        $uid = $this->params['uid'];
//        $score = 1000;
//        $orderId = "wbdh_".time();
//        $apiUG = new Api_UG();
//        $result = $apiUG->addScore($uid, $score, $orderId);
//        print_r($result);



        $this->outputJson(
            array(
                'code'=>100000,
                'msg'=>'success',
                'data'=>array(

                ),
            )
        );
    }

}