<?php
/**
 * Created by PhpStorm.
 * User: hhp1614
 * Date: 18.6.30
 * Time: 1:18
 */

header('content-type:text/html;charset=utf-8');

/*
掌握满足单例模式的必要条件
(1)私有的构造方法-为了防止在类外使用new关键字实例化对象
(2)私有的成员属性-为了防止在类外引入这个存放对象的属性
(3)私有的克隆方法-为了防止在类外通过clone成生另一个对象
(4)公有的静态方法-为了让用户进行实例化对象的操作
*/
class MySqliDB
{
    //私有的属性
    private static $dbcon = false;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $db;
    private $charset;
    private $link;

    //私有的构造方法
    private function __construct($config = array())
    {
        $this->host = isset($config['host']) ? $config['host'] : 'localhost';
        $this->port = isset($config['port']) ? $config['port'] : '3306';
        $this->user = isset($config['user']) ? $config['user'] : 'root';
        $this->pass = isset($config['pass']) ? $config['pass'] : 'root';
        $this->db = isset($config['db']) ? $config['db'] : 'blogs';
        $this->charset = isset($config['charset']) ? $config['charset'] : 'utf8';
        //连接数据库
        $this->db_connect();
        //选择数据库
        $this->db_usedb();
        //设置字符集
        $this->db_charset();
    }

    //连接数据库
    private function db_connect()
    {
        $this->link = mysqli_connect($this->host . ':' . $this->port, $this->user, $this->pass);
        if (!$this->link) {
            echo "数据库连接失败<br>";
            echo "错误编码" . mysqli_errno($this->link) . "<br>";
            echo "错误信息" . mysqli_error($this->link) . "<br>";
            exit;
        }
    }

    //设置字符集
    private function db_charset()
    {
        mysqli_query($this->link, "set names {$this->charset}");
    }

    //选择数据库
    private function db_usedb()
    {
        mysqli_query($this->link, "use {$this->db}");
    }

    //私有的克隆
    private function __clone()
    {
        die('clone is not allowed');
    }

    //公用的静态方法
    public static function getInstance()
    {
        if (self::$dbcon == false) {
            self::$dbcon = new self;
        }
        return self::$dbcon;
    }

    //执行sql语句的方法
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
     * 获得最后一条记录id
     * @return int|string id
     */
    public function getInsertId()
    {
        return mysqli_insert_id($this->link);
    }

    /**
     * @param $table string 表名
     * @param $columns array|string 列名称
     * @param $where array|string 条件
     * @return int 受影响的行数
     */
    public function getCondition($table, $where) {
        $condition = '';
        if (is_array($where)) {
            foreach ($where as $w_key => $w_val) {
                $condition = "$w_key = '$w_val'";
            }
        } else {
            $condition = $where;
        }
        $sql = "select * from $table where $condition";
        $query = $this->query($sql);
        $list = array();
        while ($r = $this->getFormSource($query)) {
            $list[] = $r;
        }
        return $list;
    }

    /**
     * 查询某个字段
     * @param string $sql
     * @return string|int
     */
    public function getOne($sql)
    {
        $query = $this->query($sql);
        return mysqli_free_result($query);
    }

    /**
     * 获取一行记录
     * @param string $sql
     * @param string $type
     * @return array 一维数组
     */
    public function getRow($sql, $type = "assoc")
    {
        $query = $this->query($sql);
        if (!in_array($type, array("assoc", 'array', "row"))) {
            die("mysqli_query error");
        }
        $func_name = "mysqli_fetch_" . $type;
        return $func_name($query);
    }

    /**
     * 获取一条记录,前置条件通过资源获取一条记录
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
     * 获取多条数据
     * @param string $sql
     * @return array 二维数组
     */
    public function getAll($sql)
    {
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
     * @param string|array $data [数据]
     * @return int 最新添加的id
     */
    public function insert($table, $data)
    {
        //遍历数组，得到每一个字段和字段的值
        $key_str = '';
        $v_str = '';
        foreach ($data as $key => $v) {
            if (empty($v)) {
                die("error");
            }
            //$key的值是每一个字段s一个字段所对应的值
            $key_str .= $key . ',';
            $v_str .= "'$v',";
        }
        $key_str = trim($key_str, ',');
        $v_str = trim($v_str, ',');
        //判断数据是否为空
        $sql = "insert into $table ($key_str) values ($v_str)";
        $this->query($sql);
        //返回上一次增加操做产生ID值
        return $this->getInsertId();
    }

    /**
     * 删除一条数据
     * @param string $table 表名
     * @param array | string 条件(多条件请传字符串)
     * @return int 受影响的行数
     */
    public function deleteOne($table, $where)
    {
        if (is_array($where)) {
            foreach ($where as $key => $val) {
                $condition = $key . '=' . $val;
            }
        } else {
            $condition = $where;
        }
        $sql = "delete from $table where $condition";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }

    /**
     * 删除多条数据方法
     * @param string $table 表名
     * @param array $where ('id'=>'1') 条件
     * @return int 受影响的行数
     */
    public function deleteAll($table, $where)
    {
        if (is_array($where)) {
            foreach ($where as $key => $val) {
                if (is_array($val)) {
                    $condition = $key . ' in (' . implode(',', $val) . ')';
                } else {
                    $condition = $key . '=' . $val;
                }
            }
        } else {
            $condition = $where;
        }
        $sql = "delete from $table where $condition";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }

    /**
     * 更新一条数据
     * @param string $table 表名
     * @param $data 数据
     * @param string $where 条件
     * @return int 受影响的行数
     */
    public function update($table, $data, $where)
    {
        //遍历数组，得到每一个字段和字段的值
        $str = '';
        foreach ($data as $key => $v) {
            $str .= "$key='$v',";
        }
        $str = rtrim($str, ',');
        //修改SQL语句
        $sql = "update $table set $str where $where";
        $this->query($sql);
        //返回受影响的行数
        return mysqli_affected_rows($this->link);
    }
}
