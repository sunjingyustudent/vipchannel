<?php
/**
 *  检测数组变量是否存在，不存在则返回NULL
 * @DateTime 2016-11-01T10:18:07+0800
 * @param    [type]    string
 */
function is_array_set($array, $key, $default = null, $xss = true)
{
    if ($xss == true) {
        return isset($array[$key]) ? trim($array[$key]) : $default;
    }
    return isset($array[$key]) ? $array[$key] : $default;
}
