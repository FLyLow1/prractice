<?php
include 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 加载一个Exccel文件
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(TRUE);
$spreadsheet = $reader->load("1607phpA.xlsx");

// $worksheet = $spreadsheet->getActiveSheet();
// $row=$worksheet->getHighestRow();			// 数据区域的行数
// $column=$worksheet->getHighestColumn();		// 数据区域的列数

// // 获取Excel文件中指定范围内的数据
// $dataArray = $spreadsheet->getActiveSheet()
//     ->rangeToArray(
//         //'A1:H6',     // The worksheet range that we want to retrieve
//         'A1:'.$column.$row,     // The worksheet range that we want to retrieve
//         NULL,        // Value that should be returned for empty cells
//         TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
//         TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
//         TRUE         // Should the array be indexed by cell row and cell column
//     );

// echo '<pre/>';
// print_r($dataArray);

// PDO入库

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(TRUE);
$spreadsheet = $reader->load("1607phpA.xlsx");

$worksheet = $spreadsheet->getActiveSheet();
// Get the highest row number and column letter referenced in the worksheet
$highestRow = $worksheet->getHighestRow(); // e.g. 10
$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
// Increment the highest column letter
$highestColumn++;

for ($row = 1; $row <= $highestRow; ++$row) {
    for ($col = 'A'; $col != $highestColumn; ++$col) {
        $data[$row][]=$worksheet->getCell($col . $row)->getValue();
    }
}
echo '<pre/>';
print_r($data);