<?php

namespace common\widgets;

use Yii;

class PhpExcel extends \yii\bootstrap\Widget
{

    /*
    * 导出excel方法
    */
    public static function sendExcel($data) {

        include_once dirname(dirname(dirname(__FILE__))) . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel.php";
        include_once dirname(dirname(dirname(__FILE__))) . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel/IOFactory.php";
        include_once dirname(dirname(dirname(__FILE__))) . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel/Writer/Excel5.php";
        $resultPHPExcel = new \PHPExcel();
        $xlsWriter = new \PHPExcel_Writer_Excel5($resultPHPExcel);

        $objProps = $resultPHPExcel->getProperties();
        $objProps->setCreator("Xl");
        $objProps->setLastModifiedBy("Xl");
        $objProps->setTitle("学生报表");
        $objProps->setSubject("学生报表");
        $objProps->setCategory("Test");

        $resultPHPExcel->getActiveSheet()->setCellValue('A1', '学生姓名');
        $resultPHPExcel->getActiveSheet()->setCellValue('B1', '手机号');
        $resultPHPExcel->getActiveSheet()->setCellValue('C1', '微信号');
        $resultPHPExcel->getActiveSheet()->setCellValue('D1', '渠道');
        $resultPHPExcel->getActiveSheet()->setCellValue('E1', '创建时间');
        $resultPHPExcel->getActiveSheet()->setCellValue('F1', '最后一次上课时间');
        $resultPHPExcel->getActiveSheet()->setCellValue('G1', '总购买课时数');
        $resultPHPExcel->getActiveSheet()->setCellValue('H1', '剩余课时数');
        $resultPHPExcel->getActiveSheet()->setCellValue('I1', '备注');
        $resultPHPExcel->getActiveSheet()->setCellValue('J1', '销售顾问');
        $i = 2;

        foreach($data as &$row) {
            $resultPHPExcel->getActiveSheet()->setCellValue('A' . $i, $row['nick']);
            $resultPHPExcel->getActiveSheet()->setCellValue('B' . $i, $row['mobile']);
            $resultPHPExcel->getActiveSheet()->setCellValue('C' . $i, $row['wechat_name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('D' . $i, $row['channel_name']);
            $resultPHPExcel->getActiveSheet()->setCellValue('E' . $i, date('Y-m-d', $row['time_created']));
            $resultPHPExcel->getActiveSheet()->setCellValue('F' . $i, $row['last_time']);
            $resultPHPExcel->getActiveSheet()->setCellValue('G' . $i, $row['total_class']);
            $resultPHPExcel->getActiveSheet()->setCellValue('H' . $i, $row['left_class']);
            $resultPHPExcel->getActiveSheet()->setCellValue('I' . $i, $row['remark']);
            $resultPHPExcel->getActiveSheet()->setCellValue('J' . $i, $row['kefu_name']);
            $i ++;
        }

        $resultPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $resultPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition:attachment;filename=file.xls");
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Cache-Control: max-age=0');
        header("Pragma: no-cache");
        $xlsWriter->save('php://output');
        exit;
    }

    public static function getExcel($title, $data, $columnMap, $fileName = '', $is_excel = 0, $width=10)
    {
        include_once dirname(dirname(dirname(__FILE__)))
            . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel.php";
        include_once dirname(dirname(dirname(__FILE__)))
            . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel/IOFactory.php";
        include_once dirname(dirname(dirname(__FILE__)))
            . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel/Writer/Excel5.php";

        $resultPHPExcel = new \PHPExcel();
        $xlsWriter = new \PHPExcel_Writer_Excel5($resultPHPExcel);

        $objProps = $resultPHPExcel->getProperties();
        $objProps->setCreator("tec");
        $objProps->setLastModifiedBy("tec");
        $objProps->setTitle($title);
        $objProps->setSubject($title);
        $objProps->setDescription("");
        $objProps->setKeywords("");
        $objProps->setCategory("");

        array_unshift($data, $columnMap);

        $resultPHPExcel->getActiveSheet()->fromArray($data, null, 'A1');

        for($col = 'A'; $col !== 'G'; $col++)
        {
            $resultPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth($width);
        }

        if($is_excel == 1)
        {
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition:attachment;filename=".time().".xls");
            header("Content-Transfer-Encoding: binary");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Cache-Control: max-age=0');
            header("Pragma: no-cache");

            $xlsWriter->save('php://output');

            exit;
        }

        $fileName = empty($fileName) ? '/tmp/'. time() . rand(1000, 99999) .'.xls'
            : $fileName;

        $xlsWriter->save($fileName);

        return $fileName;
    }

    public static function readExcel($filepath)
    {
        include_once dirname(dirname(dirname(__FILE__)))
            . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel.php";
        include_once dirname(dirname(dirname(__FILE__)))
            . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel/IOFactory.php";
        include_once dirname(dirname(dirname(__FILE__)))
            . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel/Reader/Excel5.php";
        include_once dirname(dirname(dirname(__FILE__)))
            . "/crm/web/phpExcel/PHPExcel_1.8.0_doc/Classes/PHPExcel/Reader/Excel2007.php";

        $objReader = new \PHPExcel_Reader_Excel5();
        if(!$objReader ->canRead($filepath)){
            $objReader = new \PHPExcel_Reader_Excel2007();
            if(!$objReader ->canRead($filepath)){
                return 100;//如果两种excel都执行不了，报格式错误
            }
        }
        $objPHPExcel = $objReader->load($filepath);
        $objWorksheet = $objPHPExcel->getSheet(0);
        $highestRow = $objWorksheet->getHighestRow();//最大行数，为数字
        $highestColumn = $objWorksheet->getHighestColumn();//最大列数 为字母
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn); //将字母变为数字
        $tableData = [];
        for($row = 1;$row<=$highestRow;$row++){
            for($col=0;$col< $highestColumnIndex;$col++){
                $tableData[$row][$col] = $objWorksheet->getCellByColumnAndRow($col,$row)->getValue();
            }
        }
        @unlink($filepath);
        return $tableData;
    }


}