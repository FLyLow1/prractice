<?php
/**
 * Created by PhpStorm.
 * User: renxingliang
 * Date: 2018/11/16
 * Time: 11:11
 */
require('SimplePDO.php');
$db=new Simplepdo('cl','root','root');
$data=$db->insert();
//echo "<pre>";
//print_r($data);
