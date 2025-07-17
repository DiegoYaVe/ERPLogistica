<?php
//ini_set('display_errors',1);
require_once '../lib/excel/Classes/PHPExcel.php';
include ('../funciones.php');

 $estiloTituloReporte = array(
    'font' => array(
        'name'      => 'CALINGA',
        'bold'      => false,
        'italic'    => false,
        'strike'    => false,
        'size' =>11,
        'color'     => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array(
            'argb' => '858BFF')
  ),
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    )
);

$estiloX = array(
    'font' => array(
        'name'      => 'CALINGA',
        'bold'      => false,
        'italic'    => false,
        'strike'    => false,
        'size' =>10,
        'color'     => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array(
            'argb' => 'F70B0B')
  ),
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    )
);

$estiloN = array(
    'font' => array(
        'name'      => 'CALINGA',
        'bold'      => false,
        'italic'    => false,
        'strike'    => false,
        'size' =>10,
        'color'     => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array(
            'argb' => 'F7AC0B')
  ),
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    )
);

$estiloF = array(
    'font' => array(
        'name'      => 'CALINGA',
        'bold'      => false,
        'italic'    => false,
        'strike'    => false,
        'size' =>10,
        'color'     => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array(
            'argb' => '24F70B')
  ),
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    )
);

$estiloGanancias = array(
    'font' => array(
        'name'      => 'CALINGA',
        'bold'      => false,
        'italic'    => false,
        'strike'    => false,
        'size' =>10,
        'color'     => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array(
            'argb' => '24F70B')
  ),
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    )
);



$estiloTituloColumnas = array(
    'font' => array(
        'name'      => 'CALINGA',
        'bold'      => false,
        'italic'    => false,
        'strike'    => false,
        'size' =>10,
        'color'     => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array(
            'argb' => '858BFF')
  ),
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    )
);

$estiloInformacion = array(
    'font' => array(
        'name'  => 'CALINGA',
        'color' => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
  'color' => array(
            'argb' => 'FFFFFF')
  ),
    'borders' => array(
    'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
      'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
      'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
      'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        /*'left' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
      'color' => array(
              'rgb' => '3a2a47'
            )
        )*/
    )
);
$estiloInformacion1 = array(
    'font' => array(
        'name'  => 'CALINGA',
        'color' => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
  'color' => array(
            'argb' => 'FFFFFF')
  ),
    'borders' => array(
    'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
      'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
      'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
      'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        /*'left' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
      'color' => array(
              'rgb' => '3a2a47'
            )
        )*/
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    )
);

$idd = $_GET['idd'];
$sql = 'SELECT * FROM mailing_envios INNER JOIN mailing_campanas ON mc_id = me_campana INNER JOIN mailing_campanasd ON me_correo = md_id WHERE md_id = "'.$idd.'"';
$result = setq($sql);
$row = $result->fetch_array(); 



if (PHP_SAPI == 'cli')
    die('Este archivo solo se puede ver desde un navegador web');

$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("T&E") // Nombre del autor
    ->setLastModifiedBy("T&E") //Ultimo usuario que lo modific�
    ->setTitle("REPORTE DE ENVIOS") // Titulo
    ->setSubject("REPORTE DE ENVIOS") //Asunto
    ->setDescription("REPORTE DE ENVIOS") //Descripci�n
    ->setKeywords("REPORTE DE ENVIOS") //Etiquetas
    ->setCategory("REPORTE DE ENVIOS"); //Categorias

$tituloReporte = 'REPORTE DE ENVIOS DE CAMPAÑA "'.$row['mc_nmb'].'" - "'.$row['md_asunto'].'": ';

// Se agregan los titulos del reporte
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);




$objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(true);


$col = "A";
$totaldia = 0;

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2','NO.');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2','DESTINATARIO');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2','CAMPAÑA');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2','MAIL');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2','FECHA');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2','CORREO');
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2','TIPO DE REGISTRO');


$objPHPExcel->getActiveSheet()->getStyle('A1:'.'G1')->applyFromArray($estiloTituloReporte);
$objPHPExcel->getActiveSheet()->getStyle('A2:'.'G2')->applyFromArray($estiloTituloColumnas);

foreach(range('A','Z') as $columnID) { $objPHPExcel->getActiveSheet()->getColumnDimension($columnID) ->setAutoSize(true); }



  $i = 2; //Numero de fila donde se va a comenzar a rellenar
  $j = 1;

  $sqluc = 'SELECT * FROM mailing_envios INNER JOIN mailing_campanas ON mc_id = me_campana INNER JOIN mailing_campanasd ON me_correo = md_id WHERE md_id = "'.$idd.'"';
  $resultuc = setq($sqluc) or die($sqluc);
  while($rowuc = $resultuc->fetch_array()){
    $col = "A";
    $i++;
    if($rowuc['me_origen'] == "CL"){
      $sql = 'SELECT * FROM clientes WHERE c_id = "'.$rowuc['me_registro'].'"';
      $result = setq($sql);
      $rowcl = $result->fetch_array();

      $nmb = $rowcl['c_nmb'];
      $campana = $rowuc['mc_nmb'];
      $asunto = $rowuc['md_asunto'];
      $fecha =  fecha_formato($rowuc['me_fecha'],false,false);
      $correoreg = $rowuc['me_correoreg'];
      $origen = "CLIENTES";


    }elseif($rowuc['me_origen'] == "RD"){
      $sql = 'SELECT * FROM registros_distribuidores WHERE rd_id = "'.$rowuc['me_registro'].'"';
      $result = setq($sql);
      $rowcl = $result->fetch_array();

      $nmb = $rowcl['rd_nmb'];
      $campana = $rowuc['mc_nmb'];
      $asunto = $rowuc['md_asunto'];
      $fecha =  fecha_formato($rowuc['me_fecha'],false,false);
      $correoreg = $rowuc['me_correoreg'];
      $origen = "REGISTRO DISTRIBUIDOR";
    }  
    
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col++.$i, $j++);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col++.$i, $nmb);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col++.$i, $campana);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col++.$i, $asunto);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col++.$i, $fecha);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col++.$i, $correoreg);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col++.$i, $origen);

    }



    //escala 35%

    
  $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:'.'G1');

  $col = "A";

  $i++;




$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setAutoSize(TRUE);

$objPHPExcel->getActiveSheet()->setTitle('REPORTE DE ENVIOS');

// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
//$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(35);
foreach(range('A','Z') as $columnID) { $objPHPExcel->getActiveSheet()->getColumnDimension($columnID) ->setAutoSize(true); }
$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,3);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="REPORTE DE ENVIOS DE CAMPAÑA "'.$row['mc_nmb'].'" - "'.$row['md_asunto'].'".xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>