<?php
/**
 * mysqli 操作
 * @Author: hhp1614
 * @Date:   2018-06-30 07:06:48
 * @Last Modified by:   hhp1614
 * @Last Modified time: 2018-06-30 08:34:34
 */

class DB
{
    private static $connect;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $db;
    private $charset;
    private $link;

    private function __construct($config = array())
    {
        $this->host = isset($config['host']) ? $config['host'] : 'localhost';
        $this->port = isset($config['port']) ? $config['port'] : '3306';
        $this->user = isset($config['user']) ? $config['user'] : 'root';
        $this->pass = isset($config['pass']) ? $config['pass'] : 'root';
        $this->db = isset($config['db']) ? $config['db'] : 'blogs';
        $this->charset = isset($config['charset']) ? $config['charset'] : 'UTF8';
        //连接数据库
        $this->connectDB();
        //选择数据库
        $this->useDB();
        //设置字符集
        $this->charsetDB();
    }

    /**
     * 连接数据库
     */
    private function connectDB()
    {
        $this->link = mysqli_connect($this->host . ':' . $this->port, $this->user, $this->pass);
        if (!$this->link) {
            echo "数据库连接失败<br>";
            echo "错误编码" . mysqli_errno($this->link) . "<br>";
            echo "错误信息" . mysqli_error($this->link) . "<br>";
            exit;
        }
    }

    /**
     * 设置字符集
     */
    private function charsetDB()
    {
        mysqli_query($this->link, "set names {$this->charset}");
    }

    /**
     * 选择数据库
     */
    private function useDB()
    {
        mysqli_query($this->link, "use {$this->db}");
    }

    /**
     * DB公用的静态方法
     * @return $this
     */
    public static function getInstance()
    {
        if (self::$connect == false) {
            self::$connect = new self;
        }
        return self::$connect;
    }

    /**
     * 执行 sql 语句的方法
     * @param $sql
     * @return bool | mysqli_result
     */
    public function query($sql)
    {
        $res = mysqli_query($this->link, $sql);
        if (!$res) {
            echo "sql语句执行失败<br>";
            echo "错误编码是" . mysqli_errno($this->link) . "<br>";
            echo "错误信息是" . mysqli_error($this->link) . "<br>";
        }
        return $res;
    }

    //打印数据
    public function p($arr)
    {
        echo "<pre>";
        print_r($arr);
        echo "</pre>";
    }

    public function v($arr)
    {
        echo "<pre>";
        var_dump($arr);
        echo "</pre>";
    }

    /**
     * 获取记录
     * @param string $query
     * @param string $type
     * @return mixed
     */
    public function getFormSource($query, $type = "assoc")
    {
        if (!in_array($type, array("assoc", "array", "row"))) {
            die("mysqli_query error");
        }
        $func_name = "mysqli_fetch_" . $type;
        return $func_name($query);
    }

    /**
     * 获得最后一条记录id
     * @return int|string id
     */
    public function getInsertId()
    {
        return mysqli_insert_id($this->link);
    }

    /**
     * @param string $table 表名
     * @param string $condition 条件
     * @param string $columns 列
     * @return array 查询结果
     */
    public function select($table, $condition = '', $columns = '*')
    {
        // 初始化查询语句
        $sql = "select $columns from $table;";
        if ($condition) {
            $sql = "select $columns from $table where $condition;";
        }
        $query = $this->query($sql);
        $list = array();
        while ($r = $this->getFormSource($query)) {
            $list[] = $r;
        }
        return $list;
    }

    /**
     * 定义添加数据的方法
     * @param string $table 表名
     * @param string|array $data 数据
     * @return int 最新添加的id
     */
    public function insert($table, $data)
    {
        $kStr = '';
        $vStr = '';
        //遍历数组，得到每一个字段和字段的值
        foreach ($data as $k => $v) {
            if (empty($v)) {
                die("error");
            }
            //$key的值是每一个字段s一个字段所对应的值
            $kStr .= $k . ',';
            $vStr .= "'$v',";
        }
        $kStr = trim($kStr, ',');
        $vStr = trim($vStr, ',');
        //判断数据是否为空
        $sql = "insert into $table ($kStr) values ($vStr);";
        $this->query($sql);
        //返回上一次增加操做产生ID值
        return $this->getInsertId();
    }

    /**
     * 更新一条数据
     * @param string $table 表名
     * @param string | array $data 数据
     * @param string $where 条件
     * @return int 受影响的行数
     */
    public function update($table, $data, $where)
    {
        //遍历数组，得到每一个字段和字段的值
        $str = '';
        foreach ($data as $key => $v) {
            $str .= "$key = '$v',";
        }
        $str = rtrim($str, ',');
        //修改SQL语句
        $sql = "update $table set $str where $where;";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }

    /**
     * 删除一条数据方法
     * @param string $table 表名
     * @param array | string $where 条件(多条件请传字符串)
     * @return int 受影响的行数
     */
    public function delete($table, $where)
    {
        $condition = '';
        if (is_array($where)) {
            foreach ($where as $key => $val) {
                $condition .= $key . '=' . $val;
            }
        } else {
            $condition = $where;
        }
        $sql = "delete from $table where $condition;";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }
}
