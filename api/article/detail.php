<?php
/**
 * 文章详情
 * User: hhp1614
 * Date: 18.7.1
 * Time: 20:37
 */

include_once '../../index.php';

class ArticleDetail
{
    // mysqli
    private $db;
    // 存储请求数据
    private $req;
    // 存储返回数据
    private $res = [];
    // 表名
    private $tableArticle = 'blog_article';

    function __construct()
    {
        // 实例化DB类
        $this->db = DB::getInstance();
        // 提取请求参数
        $this->request();
        // 获取数据
        $this->getDetail();
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

    private function getDetail()
    {
        $id = (int)$this->req['id'];
        $data = $this->db->select($this->tableArticle, "id = $id");

        if (!$data) {
            $this->res['msg'] = '数据获取失败';
            $this->res['status'] = false;
            echo json_encode($this->res);
            return;
        }

        $this->res['data'] = $data[0];
        $this->res['status'] = true;
        echo json_encode($this->res);
    }
}

new ArticleEdit;