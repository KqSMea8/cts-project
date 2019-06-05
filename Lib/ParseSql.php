<?php 

/**
* 解析mysql语句
*/
class Lib_ParseSql
{
    public static function parseField($fields)
    {
        if (empty($fields)) {
            return '*';
        }

        if (is_string($fields)) {
            return $fields;
        }

        if (is_array($fields)) {
            return implode(',', $fields);
        }

        return '*';
    }

    public static function parseWhere($wheres = array())
    {
        $where_value_list = array();

        $where_str = '';
        if (!empty($wheres) && is_array($wheres)) {
            foreach ($wheres as $where) {
                if (self::checkItemIsAllset($where)) {
                    $key = '`' . $where[0] . '`';
                    $oprete = strtoupper($where[1]);
                    $value = $where[2];

                    if ($oprete == 'IN') {
                        $where_str .= $key . ' ' . $oprete . " ({$value})";
                    } elseif ($oprete == 'LIKE') {
                        $where_str .= $key . ' ' . $oprete . " '%{$value}%'";
                    } else {
                        $where_str .= $key . ' ' . $oprete . ' ?';
                        $where_value_list[] = $value;
                    }
                    $where_str .= ' AND ';
                }
                
            }
            $where_str = rtrim($where_str, ' AND ');
        }

        return array($where_str, $where_value_list);
    }

    public static function parseGroupby($groupbys = array())
    {
        $groupby_str = '';
        if (!empty($groupbys) && is_array($groupbys)) {
            foreach ($groupbys as $groupby) {
                if (self::checkItemIsAllset($groupby)) {
                    $groupby_str .= implode(' ', $groupby);
                    $groupby_str .= ', ';
                }
            }
            $groupby_str = rtrim($groupby_str, ', ');
        }

        return $groupby_str;
    }

    public static function parseOrderby($orderbys = array())
    {
        if (empty($orderbys)) {
            return '';
        }

        if (is_string($orderbys)) {
            return $orderbys;
        }
        
        $orderby_str = '';
        if (!empty($orderbys) && is_array($orderbys)) {
            foreach ($orderbys as $orderby) {
                if (self::checkItemIsAllset($orderby)) {
                    $orderby_str .= implode(' ', $orderby);
                    $orderby_str .= ', ';
                } 
            }
            $orderby_str = rtrim($orderby_str, ', ');
        }

        return $orderby_str;
    }

    public static function checkItemIsAllset($list)
    {
        if (empty($list)) {
            return false;
        }

        foreach ($list as $item) {
            if (is_null($item)) {
                return false;
            }
        }

        return true;
    }
}
