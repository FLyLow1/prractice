<?php 
header("content-type:text/html;charset=gbk");
$cd=curl_init();
curl_setopt($cd,CURLOPT_URL,"https://pvp.qq.com/");
curl_setopt($cd,CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($cd,CURLOPT_RETURNTRANSFER,1);
$res=curl_exec($cd);
// var_dump($res);
$para='#<li>[\S\s]+<a href=".*" target="_blank" class="fl news-type">(.*)</a>[\S\s]+<a href=".*" target="_blank" class="fl news-txt" style="color:" onclick=".*">(.*)</a>[\S\s]+<em class="fr news-time">(.*)</em>[\S\s]+</li>#Uis';
preg_match_all($para,$res,$data);

// echo "<pre>";
// var_dump($title);
$pdo=new PDO("mysql:host=127.0.0.1;dbname=cl","root","root");
// $pdo->exec("SET CHARACTER SET utf8");
$pdo->query("set names utf8");
$title=$data[1];
$countt=$data[2];
$sj=$data[3];
for ($i=0; $i <count($title) ; $i++) { 
	$sql="insert into wang (title,countt,sj) values ('$title[$i]','$countt[$i]','$sj[$i]')";

	$pdo->exec($sql);
}

 ?>