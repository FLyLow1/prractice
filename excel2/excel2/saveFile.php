<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', '自测题');

$pdo=new PDO('mysql:host=localhost;dbname=cl;charset=utf8','root','root');
$sql="select * from exercise";
$arrayData=$pdo->query($sql)->fetchAll();
// echo '<pre/>';
// print_r($data);
$spreadsheet->getActiveSheet()
    ->fromArray(
        $arrayData,  // 从数据库中读取的数据
        NULL,        // Array values with this value will not be set
        'A2'         // 数据区域从A2单元格开始
    );

$writer = new Xlsx($spreadsheet);
$writer->save('hello world.xlsx');
