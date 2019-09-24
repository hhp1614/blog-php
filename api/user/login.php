<?php
/**
 * 用户登录
 * @Author: hhp1614
 * @Date:   2018-06-30 07:06:48
 * @Last Modified by:   hhp1614
 * @Last Modified time: 2018-06-30 07:09:58
 */

include_once '../../index.php';

class Login
{
    // mysqli
    private $db;
    // 存储请求数据
    private $req;
    // 存储返回数据
    private $res = [];
    // 表名
    private $tableUser = 'blog_user';

    function __construct()
    {
        // 实例化DB类
        $this->db = DB::getInstance();
        // 提取请求参数
        $this->request();
        // 登录校验
        $this->verify();
    }

    /**
     * 提取请求参数
     * username, password
     */
    private function request()
    {
        $json = json_decode(file_get_contents('php://input'));
        $temp = $json ? get_object_vars($json) : $_POST;
        $this->req = [
            'username' => empty($temp['username']) ? null : $temp['username'],
            'password' => empty($temp['password']) ? null : $temp['password']
        ];
    }

    private function updateDB($data)
    {
        $update = Session::update();
        $update['login_time'] = time();
        $update['login_ip'] = GetIP::get_client_ip();
        $id = $data['id'];
        $this->db->update('blog_user', $update, "id = '$id'");
        return $update;
    }

    /**
     * 登录校验
     */
    private function verify()
    {
        $username = $this->req['username'];
        $data = $this->db->select($this->tableUser, "username = '$username'");
        if ($data) {
            $data = $data[0];
            if ($data['password'] == $this->req['password']) {
                $update = $this->updateDB($data);

                $data['token'] = $update['token'];
                $data['timeout'] = $update['timeout'];
                $data['login_time'] = $update['login_time'];
                $data['login_ip'] = $update['login_ip'];

                $this->res['data'] = $data;
                $this->res['msg'] = '登录成功';
                $this->res['status'] = true;
                echo json_encode($this->res);
            } else {
                $this->res['msg'] = '用户名或密码错误';
                $this->res['status'] = false;
                echo json_encode($this->res);
            }
        } else {
            $this->res['msg'] = '用户名或密码错误';
            $this->res['status'] = false;
            echo json_encode($this->res);
        }
    }
}

new Login;