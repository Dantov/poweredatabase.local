<?php
/**
 * Created by PhpStorm.
 * User: Dant
 * Date: 18.12.2019
 * Time: 22:16
 */

namespace app\models;

use Couchbase\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style;

class TestExcel
{

    public $alph = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA'];

    public function testExcel()
    {

        $workingCenters = require_once _inclDIR_. "workingCenters2.php";

        //debug($workingCenters);

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Some TEST title');

        $sheet->setCellValue('A1', 'Артикул');
        $sheet->setCellValue('B1', 'Кол-во');
        $sheet->setCellValue('C1', 'Р-Ряд');


        //$columnIndex1, $row1, $columnIndex2, $row2
        $wcCount = count( $workingCenters ); //23
        for ( $columnIndex1 = 4; $columnIndex1 < ($wcCount*2)+4; $columnIndex1++ )
        {
            $columnIndex1Plus = $columnIndex1 + 1;
            $sheet->mergeCellsByColumnAndRow($columnIndex1,1,$columnIndex1Plus,1);
            $sheet->mergeCellsByColumnAndRow($columnIndex1,2,$columnIndex1Plus,2);
            $sheet->mergeCellsByColumnAndRow($columnIndex1,3,$columnIndex1Plus,3);
        }

        $columnIndex = 4;
        foreach ( $workingCenters as $workingCenter )
        {
            $sheet->setCellValueByColumnAndRow($columnIndex, 1, $workingCenter['name']);
            $sheet->setCellValueByColumnAndRow($columnIndex, 2, $workingCenter['title']);
            $sheet->setCellValueByColumnAndRow($columnIndex, 3, $workingCenter['deadline']);
            $columnIndex = $columnIndex+2;
        }

        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(15);
        for ( $columnIndex = 4; $columnIndex < ($wcCount*2)+4; $columnIndex++ )
        {
            $sheet->getColumnDimensionByColumn($columnIndex)->setWidth(13);
        }

        $fontStyleTop = array(
            'name'      	=> 'Calibri',
            'size'     	    => 12,
            'bold'      	=> true,
            'italic'    	=> false,
            //'underline' 	=> Style\Font::UNDERLINE_DOUBLE,
            'strike'    	=> false,
            'superScript' 	=> false,
            'subScript' 	=> false,
            'color'     	=> array(
                'rgb' => 'E0FFFF'
            )
        );
        $fontStyleRow2 = [
            'size'  => 8,
            'color' => [
                'rgb' => '2F4F4F'
            ]
        ];
        $fontStyleRow3 = [
            'size'  => 9,
            'color' => [
                'rgb' => '2F4F4F'
            ]
        ];

        // кол-во получившихся колонок
        $allAvailableColumns = ($wcCount*2)+3;

        $sheet->getStyleByColumnAndRow(1, 1, $allAvailableColumns, 1)->getFont()->applyFromArray($fontStyleTop);
        $sheet->getStyleByColumnAndRow(1, 2, $allAvailableColumns, 2)->getFont()->applyFromArray($fontStyleRow2);
        $sheet->getStyleByColumnAndRow(1, 3, $allAvailableColumns, 3)->getFont()->applyFromArray($fontStyleRow3);

        $borderStyleArray = array(
            'borders' => array(
                'outline' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
                'horizontal' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,//BORDER_THICK
                    'color' => array('rgb' => '000000'),
                ),
                'vertical' => array(
                    'borderStyle' => Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );

        $sheet->getStyleByColumnAndRow(1, 2, $allAvailableColumns, 3)->applyFromArray($borderStyleArray);



        $sheet->getStyleByColumnAndRow(1, 1, $allAvailableColumns, 1)
            ->getAlignment()
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);

        $sheet->getStyleByColumnAndRow(1, 2, $allAvailableColumns, 2)
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);

        $sheet->getStyleByColumnAndRow(1, 3, $allAvailableColumns, 3)
            ->getAlignment()
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);

        // фоновый цвет первой строки
        $sheet->getStyleByColumnAndRow(1, 1, $allAvailableColumns, 1)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('4682B4');
        // фоновый цвет второй строки
        $sheet->getStyleByColumnAndRow(1, 2, $allAvailableColumns, 2)
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('7FFFD4');


        //$sheet->mergeCells('C1:F1');

        //$sheet->setCellValueByColumnAndRow(2, 2, 'Hello World !22');

        //$sheet->mergeCellsByColumnAndRow(4,1,5,1);


        /*
        $sheet->getStyleByColumnAndRow(1,1)
            ->getAlignment()
            ->setHorizontal(Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(Style\Alignment::VERTICAL_CENTER);

        */
/*
        $sheet->getStyle('D3:F6')
            ->getFill()
            ->setFillType(Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FF69B4');
        */

        //$sheet->getStyle('A2:Z2')->applyFromArray($borderStyleArray);


        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

        echo json_encode('data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,'.base64_encode($xlsData));
    }


}