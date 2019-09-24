<?php
/**
 * 添加评论
 * User: hhp1614
 * Date: 18.7.1
 * Time: 17:11
 */

include_once '../../index.php';

class CommentAdd
{
    // mysqli
    private $db;
    // 存储请求数据
    private $req;
    // 存储返回数据
    private $res = [];
    // 表名
    private $tableUser = 'blog_user';
    private $tableComment = 'blog_comment';

    function __construct()
    {
        // 实例化DB类
        $this->db = DB::getInstance();
        // 提取请求参数
        $this->request();
        // token校验
        $this->tokenVerify();
        // 添加评论
        $this->add();
    }

    /**
     * token校验
     */
    private function tokenVerify()
    {
        $token = Session::getToken();
        if (!$token) {
            $this->res['msg'] = '没有权限';
            $this->res['status'] = false;
            echo json_encode($this->res);
            exit;
        }

        $query = $this->db->select($this->tableUser, "token = '$token'", "timeout");
        if (!$query) {
            $this->res['msg'] = '没有权限';
            $this->res['status'] = false;
            echo json_encode($this->res);
            exit;
        }

        $timeout = (int)$query[0]['timeout'];
        if (time() > $timeout) {
            $this->res['msg'] = '登录已过期';
            $this->res['status'] = false;
            echo json_encode($this->res);
            exit;
        }

        $timeout = strtotime("+7 days");
        $this->db->update($this->tableUser, ['timeout' => $timeout], "token = '$token'");
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
            'uid' => empty($temp['uid']) ? null : $temp['uid'],
            'aid' => empty($temp['aid']) ? null : $temp['aid'],
            'role' => empty($temp['role']) ? null : $temp['role'],
            'username' => empty($temp['username']) ? null : $temp['username'],
            'content' => empty($temp['content']) ? null : $temp['content'],
            'comment_time' => time(),
        ];
    }

    /**
     * 添加评论
     */
    private function add()
    {
        foreach ($this->req as $k => $v) {
            if (!$v) {
                $this->res['msg'] = $k.'不能为空';
                $this->res['status'] = false;
                echo json_encode($this->res);
                return;
            }
        }

        $res = $this->db->insert($this->tableComment, $this->req);
        if (!$res) {
            $this->res['msg'] = '评论失败';
            $this->res['status'] = false;
            echo json_encode($this->res);
            return;
        }

        $this->res['msg'] = '评论成功';
        $this->res['status'] = false;
        echo json_encode($this->res);
    }
}

new CommentAdd;