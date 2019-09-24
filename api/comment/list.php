<?php
/**
 * 评论列表
 * User: hhp1614
 * Date: 18.7.1
 * Time: 23:04
 */

include_once '../../index.php';

class CommentList
{
    // mysqli
    private $db;
    // 存储请求数据
    private $req;
    // 存储返回数据
    private $res = [];
    // 表名
    private $tableComment = 'blog_comment';

    function __construct()
    {
        // 实例化DB类
        $this->db = DB::getInstance();
        // 提取请求参数
        $this->request();
        // 获取数据
        $this->getList();
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
            'aid' => empty($temp['aid']) ? null : $temp['aid']
        ];
    }

    private function getList()
    {
        // TODO: 分页
        $aid = $this->req['aid'];
        if ($aid) {
            $data = $this->db->select($this->tableComment, "aid = $aid");
        } else {
            $data = $this->db->select($this->tableComment);
        }

        if (!$data) {
            $this->res['msg'] = '数据获取失败';
            $this->res['status'] = false;
            echo json_encode($this->res);
            return;
        }

        $this->res['data'] = $data;
        $this->res['status'] = true;
        echo json_encode($this->res);
    }
}

new CommentList;