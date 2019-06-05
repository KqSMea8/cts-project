    <?php
    require_once T3PPATH . "/alipay/lib/alipay_core.function.php";
    require_once T3PPATH . "/alipay/lib/alipay_rsa.function.php";
    /** 
     * RSA算法类 
     * 签名及密文编码：base64字符串/十六进制字符串/二进制字符串流 
     * 填充方式: PKCS1Padding（加解密）/NOPadding（解密） 
     * 
     * Notice:Only accepts a single block. Block size is equal to the RSA key size!  
     * 如密钥长度为1024 bit，则加密时数据需小于128字节，加上PKCS1Padding本身的11字节信息，所以明文需小于117字节 
     * 
     */  
    class Comm_Weibo_Rsa {  
      
        private $ali_public_key_path ;  
        private $private_key_path ;
        private $data = '' ; 
      
        /** 
         * 自定义错误处理 
         */  
        private function _error($msg){  
            die('RSA Error:' . $msg); //TODO  
        }  
      
        /** 
         * 构造函数 
         * 
         * @param string 公钥文件（验签和加密时传入） 
         * @param string 私钥文件（签名和解密时传入） 
         */  
        public function __construct(array $para){  
        	if ($para) {
        		$para = paraFilter($para) ;
        		$this->data = createLinkstring($para) ;
        	}
        	$this->ali_public_key_path = T3PPATH . "/alipay/key/alipay_public_key.pem" ;
        	$this->private_key_path = T3PPATH . "/alipay/key/rsa_private_key.pem" ;
        }  
      
      
        /** 
         * 生成签名 
         * 
         * @param string 商户私钥文件路径 
         * @param  
         * @return 签名值 
         */  
        public function sign(){  
            $ret = false; 
            $ret = rsaSign($this->data, $this->private_key_path) ;
            
            return $ret;  
        }  

        /**
		 * RSA验签
		 * @param $data 待签名数据
		 * @param $ali_public_key_path 支付宝的公钥文件路径
		 * @param $sign 要校对的的签名结果
		 * return 验证结果
		 */
      	public function verify($sign)
      	{
      		$ret = rsaVerify($this->data, $this->ali_public_key_path, $sign) ;
      		return $ret;
      	}
    }  