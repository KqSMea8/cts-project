<?php
/**
 * Comm_Weibo_Product{ 
 * 商品相关curl
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-9-26
 * @copyright copyright(2013) weibo.com all rights reserved
 */
class Comm_Weibo_Product{
    private $apiKey = 'fac837511ae2417cf318';
    private $categoryUrl = 'http://api.sc.weibo.com/v2/biz/operate/category/info';

    public function get_curl_result($url, $platform_key, $data = array(), $method = 'POST') {
        try
        {
            $request = new Comm_HttpRequest($url);
            $request->set_method($method);
            foreach($data as $key => $val)
            {
                $request->add_post_field($key, $val);
            }
            
            $sign = Tool_Sign::generate_sign($data, $platform_key);
            $request->add_post_field('sign', $sign);
            $res = $request->send();
            return $request->get_response_content();
        } catch(Exception $e)
        {
            Tool_Log::fatal($e->getMessage());
            return false;
        }
    }

    /**
     * getCategory 
     * 获取商品类目
     * @param mixed $data 
     * @return void
     */
    public function getCategory($data){
        $url = $this->categoryUrl;
        $key = $this->apiKey;
        $result = $this->get_curl_result($url, $key, $data);
        $res = json_decode($result, true);

        if ($res['error_code'] == 100000){
            return $res['data'];
        }else{
            return false;
            Tool_Log::fatal('get category error:' . $result);
        }
    }
}
