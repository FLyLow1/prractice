<?php
/**
 * Created by PhpStorm.
 * User: renxingliang
 * Date: 2018/11/16
 * Time: 10:54
 */
class Simplepdo{
//使用pdo连接数据库 封装增删改查

//定义私有属性
    private $database;
    private $host;
    private $username;
    private $passwprd;
    private  $db;
    function __construct($database,$username,$password)
    {
        $this->host='localhost';
        $this->database=$database;
        $this->username=$username;
        $this->passwprd=$password;
        $this->connect();
    }
     private function connect(){
         $this->db= new \PDO("mysql:host=".$this->host.";dbname=$this->database","$this->username","$this->passwprd");
    }
    //查 第一个参数表名, 第二个参数是要获取的字段(用 , 隔开),第三个参数是条件
    public function query($tablename,$column="*",$where=''){
        $sql="select $column from $tablename WHERE $where";
        $res=$this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $res;
     }
    //查 第一个参数表名, 第二个参数是要获取的字段(用 , 隔开),第三个参数是条件  (只获取一条数据)
    public function getRow($tablename,$column='*',$where=''){
    //组装sql语句
        $sql = "select $column from $tablename where $where";
    //查询
        $res = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $res;
    }
//    /**
//     *  向数据库中添加一条信息
//     *  参数：表名 一维关联数组
//     *  返回: 布尔值
//     */
    public function insert($tablename,$arr){
       //拿到数组之后先处理数组  过滤字段
        //取出表中的字段
        $sql = "select COLUMN_NAME from INFORMATION_SCHEMA.Columns where table_name = '$tablename' and table_schema ='$this->database'";
        $columns = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $cols = array(); //存储表中的全部字段
        foreach($columns as $key=>$val){
            $cols[] = $val['COLUMN_NAME'];
        }
//将要入库的数组进行键值分离
        $keys = array();
        $values = '';
        foreach($arr as $k=>$v){
            if(!in_array($k,$cols)){
                unset($arr[$k]);
            }else{
                $keys[] = $k;
                $values .= "'".$v."',";
            }
        }
        $column = implode(',',$keys);
        $values = substr($values,0,-1);
//拼接sql语句
        $sql = "insert into $tablename ($column) values ($values)";
        $res = $this->db->exec($sql);
        return $res;
    }

    /**
     *   删除数据
     *   参数：表名 条件(不含where)
     *   返回：布尔
     */
    public function delete($tablename,$where){
        $sql = "delete $tablename  where $where";
        $res = $this->db->exec($sql);
        return $res;
    }

}