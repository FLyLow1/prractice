<?php
/**
 * Created by PhpStorm.
 * User: renxingliang
 * Date: 2018/11/16
 * Time: 14:09
 */
require("../simplePDO/SimplePDO.php");
$redis=new Redis();
$redis->connect('127.0.0.1',6379);
$city=$_GET['city'];
if($redis->exists($city)){
    $str=$redis->get($city);
    print_r($str) ;

}else{
    $key='b3834006a6f849f5bca2561594f21519';
    $url="https://free-api.heweather.com/s6/weather/forecast?location=$city&key=$key";
    $str=curl_get($url);
    $data=json_decode($str,true);
    $data=$data['HeWeather6'][0]['daily_forecast'];
//    $pdo=new PDO('mysql:host=127.0.0.1;dbname=cl','root','root');
    $db=new Simplepdo('cl','root','root');
    foreach ($data as $key =>$v){
//        $sql="insert into weather (city,tmp_max,tmp_min,riqi) VALUES ('$city','$v[tmp_max]','$v[tmp_min]','$v[date]')";
//        $pdo->exec($sql);
        $astre=array(
            'city'=>$city,
            'tmp_max'=>$v['tmp_max'],
            'tmp_min'=>$v['tmp_min'],
            'riqi'=>$v['date'],
        );

        $db->insert('weather',$astre);
    }
    $str=json_encode($data);
    $redis->set($city,$str);
    print_r($str) ;
}
function curl_get($url){
    $cu=curl_init();
    curl_setopt($cu,CURLOPT_URL,$url);
    curl_setopt($cu,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($cu,CURLOPT_RETURNTRANSFER,1);
    $str=curl_exec($cu);
    curl_close($cu);
    return $str;
}