<?php
/**
 * Created by PhpStorm.
 * User: renxingliang
 * Date: 2018/11/15
 * Time: 11:02
 */
$code=$_POST['code'];
$redis=new Redis();
$redis->connect('127.0.0.1',6379);
$code1=$redis->get('code');
echo $code.$code1;
if(!isset($code1)){
    echo "验证码已失效";
}
if($code!=$code1){
    echo "验证码错误";
}
$username=$_POST['username'];
$password=$_POST['password'];
$phone=$_POST['phone'];
echo "注册成功";
