<?php
class Tool_Speratepage {
	public static function pagelist($page_no, $page_counts) {
		$page_list = array() ;
		if ($page_counts < 6) {
			for($i = 1; $i <= $page_counts; $i++) {
				$page_list[] = $i ;
			}
		} else {
			if ($page_no < 4) {
				for($i = 1; $i <= 4; $i++) {
					$page_list[] = $i ;
				}
				$page_list[] = '...' ;
				$page_list[] = $page_counts ;
			} elseif($page_no > $page_counts - 3) {
				$page_list[] = 1 ;
				$page_list[] = '...' ;
				for ($i = $page_counts - 3; $i <= $page_counts; $i++) {
					$page_list[] = $i ;
				}
			} else {
				$page_list[] = 1 ;
				$page_list[] = '...' ;
				$page_list[] = $page_no - 1 ;
				$page_list[] = $page_no ;
				$page_list[] = $page_no + 1 ;
				$page_list[] = '...' ;
				$page_list[] = $page_counts ;
			}
		}
		return $page_list ;
	}
}