<?php
/**
 * 用户注册
 * @Author: hhp1614
 * @Date:   2018-06-30 07:06:48
 * @Last Modified by:   hhp1614
 * @Last Modified time: 2018-06-30 09:08:53
 */

include_once '../../index.php';

class Register
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
        // 添加用户
        $this->addUser();
    }

    /**
     * 提取请求参数
     * username, password, role
     */
    private function request()
    {
        $json = json_decode(file_get_contents('php://input'));
        $temp = $json ? get_object_vars($json) : $_POST;
        $this->req = [
            'username' => empty($temp['username']) ? null : $temp['username'],
            'password' => empty($temp['password']) ? null : $temp['password'],
            'role' => empty($temp['role']) ? 2 : $temp['role'],
            'register_time' => time(),
            'register_ip' => GetIP::get_client_ip()
        ];
    }

    /**
     * 校验请求数据
     * @param array $arr 请求数据
     * @return bool
     */
    private function verify($arr)
    {
        $bool = false;
        $usernameLen = strlen($arr['username']);
        $passwordLen = strlen($arr['password']);
        $role = $arr['role'];
        if ($usernameLen < 3) {
            $this->res['msg'] = '用户名不能少于3个字符';
            $this->res['status'] = false;
            $bool = true;
        } elseif ($passwordLen < 6) {
            $this->res['msg'] = '密码不能少于6个字符';
            $this->res['status'] = false;
            $bool = true;
        } elseif (!in_array($role, [1, 2])) {
            $this->res['msg'] = '角色参数不正确';
            $this->res['status'] = false;
            $bool = true;
        }
        return $bool;
    }

    /**
     * 判断用户名是否存在
     * @return bool
     */
    private function hasUsername()
    {
        $username = $this->req['username'];
        $data = $this->db->select($this->tableUser, "username = '$username'");
        if ($data) {
            $this->res['msg'] = '用户名已存在';
            $this->res['status'] = false;
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加用户
     */
    private function addUser()
    {
        $bool = $this->verify($this->req);
        if ($bool) {
            echo json_encode($this->res);
            return;
        }
        if ($this->hasUsername()) {
            echo json_encode($this->res);
            return;
        }
        $line = $this->db->insert($this->tableUser, $this->req);
        if ($line) {
            $this->res['msg'] = '注册成功';
            $this->res['status'] = true;
            echo json_encode($this->res);
            // TODO: token
            return;
        }
        $this->res['msg'] = '注册失败';
        $this->res['status'] = false;
        echo json_encode($this->res);
    }
}

$register = new Register;

