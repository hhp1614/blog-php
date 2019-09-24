<?php
/**
 * Session
 * User: hhp1614
 * Date: 18.7.1
 * Time: 2:31
 */

class Session
{
    public function __construct()
    {
        session_start();
    }

    public static function newToken()
    {
        $str = md5(uniqid(md5(microtime(true)), true));  //生成一个不会重复的字符串
        $str = sha1($str);  //加密
        return $str;
    }

    public static function update()
    {
        $token = Session::newToken();
        $timeout = strtotime("+7 days");
        return ['token' => $token, 'timeout' => $timeout];
    }

    public static function getToken()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        foreach ($headers as $k => $v) {
            if ($k == 'Token') {
                return $headers['Token'];
            }
        }
        return false;
    }

    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public static function get($name)
    {
        if (isset($_SESSION[$name]))
            return $_SESSION[$name];
        else
            return false;
    }

    public static function del($name)
    {
        unset($_SESSION[$name]);
    }

    public static function destroy()
    {
        $_SESSION = array();
        session_destroy();
    }
}

new Session;
