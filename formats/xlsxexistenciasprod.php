<?php
//ini_set('display_errors',1);
session_start();
require_once '../lib/excel/Classes/PHPExcel.php';
include ('../funciones.php');
ini_set('display_errors', 1);
 $estiloTituloReporte = array(
    'font' => array(
        'name'      => 'KALINGA',
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
        'name'      => 'KALINGA',
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
$Cnaranja = array(
    'font' => array(
        'name'      => 'KALINGA',
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

$Cgris = array(
    'font' => array(
        'name'      => 'KALINGA',
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
            'argb' => 'CDCDCD')
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

$Cverde = array(
    'font' => array(
        'name'      => 'KALINGA',
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
$Ccafe = array(
    'font' => array(
        'name'      => 'KALINGA',
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
            'argb' => '663300')
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
        'name'      => 'KALINGA',
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
        'name'  => 'KALINGA',
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
    )
);
$estiloInformacion1 = array(
    'font' => array(
        'name'  => 'KALINGA',
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
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'rotation' => 0,
        'wrap' => TRUE
    )
);

    
    if(!empty($_GET['almacen'])) { $almacenes = $_GET['almacen']; } else $almacenes = NULL;
    $almacenusuario = traealmacenusuario($_SESSION['uid']);
    if($almacenusuario != "0") $almacenes = $almacenusuario;
    else $almacenes = $almacenes;



if (PHP_SAPI == 'cli')
    die('Este archivo solo se puede ver desde un navegador web');

$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("JD CEO") // Nombre del autor
    ->setLastModifiedBy("JD CEO") //Ultimo usuario que lo modific?
    ->setTitle("REPORTE EXISTENCIAS") // Titulo
    ->setSubject("REPORTE EXISTENCIAS") //Asunto
    ->setDescription("REPORTE EXISTENCIAS") //Descripci?n
    ->setKeywords("REPORTE EXISTENCIAS") //Etiquetas
    ->setCategory("REPORTE EXISTENCIAS"); //Categorias
 
$tituloReporte = "REPORTE EXISTENCIAS DE ALMACEN";
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);
// Se agregan los titulos del reporte
$objPHPExcel->setActiveSheetIndex(0)
    ->mergeCells('A1:D1');

$titulosColumnas = array('ALMACEN', 'ARTÍCULO', 'TIPO','EXISTENCIA');
$letra = "A";
foreach($titulosColumnas as $titc){
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letra.'2',$titc);
  $letra++;
}

  $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estiloTituloReporte);
  $objPHPExcel->getActiveSheet()->getStyle('A1:D2')->applyFromArray($estiloTituloReporte);

  $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');

  //$a=0;
  $i = 2; //Numero de fila donde se va a comenzar a rellenar
  $col = "A";
 $sql2 = '';

  $sqlal = 'SELECT * FROM pr_almacenes where pa_estatus="A"';
  if($almacenes) $sqlal.=' AND pa_id = "'.$almacenes.'" ';
  $resultal = setq($sqlal) or die($sqlal);
  while ($rowal = $resultal->fetch_array()) {
    $tiposm = array('A'=>'Área', 'P'=>'Pieza', 'V'=>'Volumen');
    $tiposu = array('A'=>'mts cuadrados', 'P'=>'unidades', 'V'=>'mts cúbicos');
    $sqlex = 'SELECT pmp_id id, pmp_nmb nmb, pmp_tipo tipo FROM pr_materiaprima WHERE pmp_estatus = "A"';
    if($_REQUEST['articulo']) $sqlex.=' AND (pmp_nmb LIKE "%'.$_REQUEST['articulo'].'%")';
    $sqlex .= 'ORDER BY pmp_nmb ASC';
    $resultex = setq($sqlex) or die($sqlex);
    while ($row = $resultex->fetch_array()){
      $articulo = $row['nmb'];
      $existencia = existenciamp($row['id'], $rowal['pa_id']);
      $i++;
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $rowal['pa_nmb']);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, $articulo);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $tiposm[$row['tipo']]);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, number_format($existencia, 0) . ' '.$tiposu[$row['tipo']]);
    }
  }

// formato de moneda
//

$objPHPExcel->getActiveSheet()->setTitle('EXISTENCIAS');

// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
$objPHPExcel->setActiveSheetIndex(0);

// Inmovilizar paneles
//$objPHPExcel->getActiveSheet(0)->freezePane('A4');
$objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
    //$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(40);
foreach(range('A','Z') as $columnID) { $objPHPExcel->getActiveSheet()->getColumnDimension($columnID) ->setAutoSize(true); }
$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,3);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="REPORTE EXISTENCIAS.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>