<?php
/**
 * Created by PhpStorm.
 * User: renxingliang
 * Date: 2018/11/21
 * Time: 8:23
 */
$no=$_POST['no']; //切片序列号
$name=$_POST['name']; //文件名
$cnt=$_POST['cnt'];
$file=$_FILES['blob'];
$filename=$file['tmp_name'];
$destination='./'.$name.'_'.$no;
move_uploaded_file($filename,$destination);
if($no==$cnt){
    $str='';
    for ($i=1;$i<=$cnt;$i++){
        $filename='./'.$name.'_'.$i;
        $str.=file_get_contents($filename);
    }
    file_put_contents('./'.$name,$str);
    for ($i=1;$i<=$cnt;$i++){
        $filename='./'.$name.'_'.$i;
        unlink($filename);
    }
}