<?php
/**
 * 文章删除
 * User: hhp1614
 * Date: 18.7.1
 * Time: 20:54
 */

include_once '../../index.php';

class ArticleDelete
{
    // mysqli
    private $db;
    // 存储请求数据
    private $req;
    // 存储返回数据
    private $res = [];
    // 表名
    private $tableUser = 'blog_user';
    private $tableArticle = 'blog_article';

    function __construct()
    {
        // 实例化DB类
        $this->db = DB::getInstance();
        // 提取请求参数
        $this->request();
        // token校验
        $this->tokenVerify();
        // 获取数据
        $this->doDelete();
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
            return;
        }

        $query = $this->db->select($this->tableUser, "token = '$token'", "timeout");
        if (!$query) {
            $this->res['msg'] = '没有权限';
            $this->res['status'] = false;
            echo json_encode($this->res);
            return;
        }

        $timeout = (int)$query[0]['timeout'];
        if (time() > $timeout) {
            $this->res['msg'] = '登录已过期';
            $this->res['status'] = false;
            echo json_encode($this->res);
            return;
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
            'id' => empty($temp['id']) ? null : $temp['id']
        ];
    }

    private function doDelete()
    {
        $id = (int)$this->req['id'];
        $data = $this->db->delete($this->tableArticle, "id = $id");

        if (!$data) {
            $this->res['msg'] = '数据删除失败';
            $this->res['status'] = false;
            echo json_encode($this->res);
            return;
        }

        $this->res['msg'] = '数据删除成功';
        $this->res['status'] = true;
        echo json_encode($this->res);
    }
}

new ArticleDelete;
