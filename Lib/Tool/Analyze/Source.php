<?php
class Tool_Analyze_Source {
    /**
     * 渲染扩展APP的来源
     * @param Do_Status $status
     */
    public static function format_source($source, $annotations = NULL){
    	$mblog_app_with_extinfo = Comm_Config::get("source");
        $format_source = $source;
        $source_content = strip_tags($source);
        if(is_array($annotations) && count($annotations)){
            foreach ($annotations as $item){
                if (isset($item['source'])) {
                    $item = $item['source'];
                }
                if (isset($item['appid']) && in_array($item['appid'], $mblog_app_with_extinfo)){
                    $title = $source_content . '-' . $item['name'];
                    $format_source = '<a title="' . $title . '" target="_blank" href="' . $item['url']. '" >';
                    $show_source = Tool_Formatter_Content::substr_cn($title, 16);
                    $format_source .=  $show_source . '</a>';
                    break;
                }
                else{
                	$format_source = str_replace('<a', '<a target="_blank"', $format_source);
                }
            }
        }else{
        	$format_source = str_replace('<a', '<a target="_blank"', $format_source);
        }
        //当source = 未通过审核应用时  过滤链接
        if (strpos($format_source,'未通过审核应用')) {
        	$format_source = preg_replace('|<a(.*?)href=".*?"(.*?)>|','',$format_source);
        }
        return $format_source;
    }
    
}