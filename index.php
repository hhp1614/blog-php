<?php
/**
 * @Author: hhp1614
 * @Date:   2018-06-30 07:06:48
 * @Last Modified by:   hhp1614
 * @Last Modified time: 2018-06-30 07:56:16
 */

require_once dirname(__FILE__) . './sql/DB.php';
include_once dirname(__FILE__) . './lib/sqlin.php';
include_once dirname(__FILE__) . './lib/session.php';
include_once dirname(__FILE__) . './lib/getIP.php';

// 设置返回数据格式及编码
header('Content-Type:application/json; charset=utf-8');


/**
 * 跨域相关
 */
// 跨域服务器允许的来源地址（跟请求的Origin进行匹配），可以是*或者某个确切的地址，不允许多个地址
header('Access-Control-Allow-Origin:*');

if ('OPTIONS' ===  $_SERVER['REQUEST_METHOD']) {
    // 跨域服务器允许客户端添加或自定义哪些 http 头
    header('Access-Control-Allow-Headers:*');
    exit(0);
}

