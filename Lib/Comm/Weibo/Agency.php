<?php
/**
 * Comm_Weibo_Car{ 
 * 虚拟商品代理商相关接口
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2015-4-15
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Comm_Weibo_Agency{
    private $carDomain = 'http://huodong.weibo.com/';

    public function get_curl_result($url, $data = array(), $method = 'POST') {
        try
        {
            $request = new Comm_HttpRequest($url);
            $request->set_method($method);
            foreach($data as $key => $val)
            {
                $request->add_query_field($key, $val);
            }
            $res = $request->send();
            return $request->get_response_content();
        } catch(Exception $e)
        {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    public function getAgency($data){
        $url = $this->carDomain . 'qiche/api/getDealersByGoods';
        $result = $this->get_curl_result($url, $data);
        $res = json_decode($result, true);
        if ($res['code'] == 1){
            return $res['data'];
        }else{
            return false;
            Tool_Log::fatal('get agency error:' . $result);
        }
    }
}