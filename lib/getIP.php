<?php
/**
 * Created by PhpStorm.
 * User: hhp1614
 * Date: 18.7.1
 * Time: 3:24
 */

class GetIP
{
    /**
     * 获取客户端IP地址
     * @return string
     */
    public static function get_client_ip() {
        if(getenv('HTTP_CLIENT_IP')){
            $client_ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) {
            $client_ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) {
            $client_ip = getenv('REMOTE_ADDR');
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }
        return $client_ip;
    }

    /**
     * 获取服务器端IP地址
     * @return string
     */
    public static function get_server_ip() {
        if (isset($_SERVER)) {
            if($_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }
}