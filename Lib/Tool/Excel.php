<?php
/**
 * Tool_Excel 
 * 导出excel数据
 * @author    xiamengyu<mengyu5@staff.sina.com.cn> 
 * @created   2014-6-16
 * @copyright copyright(2013) weibo.com all rights reserved
 */
require_once T3PPATH . "/PHPExcel/PHPExcel.php";
require_once T3PPATH . "/PHPExcel/PHPExcel/IOFactory.php";
require_once T3PPATH . "/PHPExcel/PHPExcel/Writer/Excel5.php";

class Tool_Excel{

    /**
     * exportExcel 
     * 导出excel文件到页面
     * @param mixed $data 
     * @static
     * @return void
     *
     * $data = array('title','name','data');
     * $data['name'] = '文件名';
     * $data['title'] = array('A1'=>'1,1值', 'B1'=>'1,2值');
     * $data['data'] = array(array('A'=>'2,1值', 'B'=>'2,2值',... ) ,...);
     */
    public static function exportExcel($data){
        try{
            $resultPHPExcel = new PHPExcel();
            //设置标题
            foreach($data['title'] as $key => $val){
                $resultPHPExcel->getActiveSheet()->setCellValue($key, $val);
            }
            //设置内容
            $num = 2;
            foreach($data['data'] as $key => $val){
                foreach($val as $sub_key => $sub_val){
                    $resultPHPExcel->getActiveSheet()->setCellValue($sub_key . $num, $sub_val); 
                }
                $num++;
            }
            $outputFileName = $data['name'];
            $xlsWriter = new PHPExcel_Writer_Excel5($resultPHPExcel); 
            header("Content-Type: application/force-download"); 
            header("Content-Type: application/octet-stream"); 
            header("Content-Type: application/download"); 
            header('Content-Disposition:inline;filename="'.$outputFileName.'"'); 
            header("Content-Transfer-Encoding: binary"); 
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
            header("Pragma: no-cache"); 
            $xlsWriter->save( "php://output" );
            ob_flush();flush();
        }catch(Exception $e){
            Tool_Log::fatal($e->getMessage());
        }
    }
}
