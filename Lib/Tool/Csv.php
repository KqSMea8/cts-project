<?php
/**
 * csv 文件下载类.直接输出数据流提供csv文件下载
 *
$arr = array(
    array(234,345,45,345,345),
    array(234,345,45,345,345),
    array(234,345,45,345,345),
    array(234,345,45,345,345),
);

$obj = new csv( 'csv', 'utf-8' );
$obj->put_row(array('编号1','编号2','编号3','编号4','编号5'));
$obj->put_rows($arr);
$obj->put_rows($arr);
 *
 * @copyright  copyright(2011) weibo.com all rights reserved
 * @author  xiaowu <xiaowu1@staff.sina.com.cn>
 * @package helper
 */
class Tool_Csv{
	private $h_out = null;	//
    private $charset = null;
	const CONTENT_TYPE_EXL = 'xls';
	const CONTENT_TYPE_TXT = 'txt';
	
	private $error_data = ''; // iconv error data
    /**
     * 
     * @param string $file_name     下载后文件显示名称,不含扩展名
     * @param string $charset       设置下载数据的原字符集,内部会自动转码为gbk
     */
    function __construct($file_name , $charset='utf-8', $content_type = 'xls'){       
        $this->h_out = fopen( "php://output", 'w');
        $this->charset = strtolower($charset);
        if($content_type == self::CONTENT_TYPE_EXL){
        	header('Content-Type: application/vnd.ms-excel');
        }elseif($content_type == self::CONTENT_TYPE_TXT){
        	header("Content-type: text/csv");
        }
        header("Content-Disposition: attachment; filename=$file_name.csv");
    }

    /**
     * 输出一行
     * @param array $row 一维数组
     */
    function put_row($row){       
        if( $this->charset != 'gbk' ){
            foreach( $row as $k=>&$v ){
                // TODO added by liuyu6
                // 主要是 解决 从mysql取字符UTF8->GBK/GB2312等后，会乱码的问题
                // 设置一个日志标志，方便文本提取等处理，如|ICONV-ERROR-NAME|
//                 if($k == 12) {
//                     set_error_handler(array('self', 'csv_error_handler_name'));
//                 }elseif($k == 13) {
//                     set_error_handler(array('self', 'csv_error_handler_address'));
//                 }else {
//                     set_error_handler(array('self', 'csv_error_handler_other'));
//                 }
                $v = iconv( $this->charset, 'gbk//IGNORE', $v);
            }
        }
        fputcsv( $this->h_out, $row );
    }

    /**
     * 一次输出多行
     * @param array $rows 二维数组
     */
    function put_rows( $rows ){
        foreach( $rows as $row ){
            $this->put_row($row);
        }
    }

    function __destruct(){
        fclose($this->h_out);
    }
    
    public static function csv_error_handler_name($errno, $errstr, $errfile, $errline ,$errcontext) {       
        switch ($errno) {
            case E_NOTICE:
                Tool_Log::warning('|ICONV-NAME| ' . $errno . ' ' . $errstr . ' ' . $errfile . ' ' . $errline . ' uid=' . $errcontext['row'][1] . ' oid=' . $errcontext['row'][7] . ' v=' . $errcontext['v']);
                break;
            default:
                break;
        }
        
        return true;
    }
    public static function csv_error_handler_address($errno, $errstr, $errfile, $errline ,$errcontext) {
        switch ($errno) {
            case E_NOTICE:
                Tool_Log::warning('|ICONV-ADDRESS| ' . $errno . ' ' . $errstr . ' ' . $errfile . ' ' . $errline . ' uid=' . $errcontext['row'][1] . ' oid=' . $errcontext['row'][7] . ' v=' . $errcontext['v']);
                break;
            default:
                break;
        }
        
        return true;
    }
    public static function csv_error_handler_other($errno, $errstr, $errfile, $errline ,$errcontext) {
        switch ($errno) {
            case E_NOTICE:
                Tool_Log::warning('|ICONV-OTHERS| ' . $errno . ' ' . $errstr . ' ' . $errfile . ' ' . $errline . ' uid=' . $errcontext['row'][1] . ' oid=' . $errcontext['row'][7] . ' v=' . $errcontext['v']);
                break;
            default:
                break;
        }
        
        return true;
    }
}

/**
 *
$arr = array(
    array(234,345,45,345,345),
    array(234,345,45,345,345),
    array(234,345,45,345,345),
    array(234,345,45,345,345),
);

$obj = new csv( 'csv', 'utf-8' );
$obj->put_row(array('编号','姓名','辈子','测试','电视卡'));
$obj->put_rows($arr);
$obj->put_rows($arr);

 */