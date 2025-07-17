<?php
//ini_set('display_errors',1);
session_start();
require_once '../lib/excel/Classes/PHPExcel.php';
include ('../funciones.php');
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

if(empty($_GET['fini'])) $_GET['fini'] = date('Y-m-d',strtotime('first day of this month'));
if(empty($_GET['ffin'])) $_GET['ffin'] = date('Y-m-d',strtotime('last day of this month'));
if(empty($_GET['proveedor'])) $_GET['proveedor'] = NULL;
if(empty($_GET['almacen'])) $_GET['almacen'] = NULL;
if(empty($_GET['estatus'])) $_GET['estatus'] = NULL;


if (PHP_SAPI == 'cli')
    die('Este archivo solo se puede ver desde un navegador web');

$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("JD CEO") // Nombre del autor
    ->setLastModifiedBy("JD CEO") //Ultimo usuario que lo modific?
    ->setTitle("REPORTE GENERAL DE COMPRAS") // Titulo
    ->setSubject("REPORTE GENERAL DE COMPRAS") //Asunto
    ->setDescription("REPORTE GENERAL DE COMPRAS") //Descripci?n
    ->setKeywords("REPORTE GENERAL DE COMPRAS") //Etiquetas
    ->setCategory("REPORTE GENERAL DE COMPRAS"); //Categorias

$tituloReporte = "REPORTE GENERAL DE COMPRAS DESDE: ".fecha_formato($_GET['fini'],false,true).' '."HASTA: ".fecha_formato($_GET['ffin'],false,true);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);
// Se agregan los titulos del reporte
$objPHPExcel->setActiveSheetIndex(0)
    ->mergeCells('A1:I1');

$titulosColumnas = array('Folio', 'Fecha','Almacen','Vendedor','Proveedor','Subtotal','Descuento','Total Compra','Estatus');
$letra = "A";
foreach($titulosColumnas as $titc){
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letra.'2',$titc);
  $letra++;
}

  $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($estiloTituloReporte);
  $objPHPExcel->getActiveSheet()->getStyle('A1:I2')->applyFromArray($estiloTituloReporte);

  $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:I1');

  //$a=0;
  $i = 2; //Numero de fila donde se va a comenzar a rellenar
  $col = "A";
  $sql = 'SELECT * FROM recepciones WHERE r_empresa = "'.$_SESSION['emp'].'" AND DATE(r_fechaapli) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus != "N" ';
  if($_GET['proveedor']) $sql.=' AND r_proveedor IN (SELECT p_id FROM proveedores WHERE p_empresa = "'.$_SESSION['emp'].'" AND
    (p_nmb LIKE "%'.$_GET['proveedor'].'%" OR p_alias LIKE "%'.$_GET['proveedor'].'%") )';
  if($_GET['almacen']) $sql.=' AND r_almacen = "'.$_GET['almacen'].'" ';
  if($_GET['estatus']) $sql.=' AND r_estatus = "'.$_GET['estatus'].'" ';
  $sql.=' ORDER BY r_fechaapli DESC';
  $resultrem = setq($sql);

  $totalsubtotal = 0;
  $totaldescuento = 0;
  $totalfinal = 0;

  $numrems = 0;
  while($rowal = $resultrem->fetch_array()){
    $numrems++;
    $i++;
    if($rowal['r_estatus'] == "A"){
      $objPHPExcel->getActiveSheet()->getStyle("I".$i)->applyFromArray($Cverde);
      $nmb = "Finalizada";
    }
    elseif($rowal['r_estatus'] == "C"){
      $objPHPExcel->getActiveSheet()->getStyle("I".$i)->applyFromArray($estiloX);
      $nmb = "Cancelada";
    }

    $subtotal = ($rowal['r_subtotal']+$rowal['r_iva']-$rowal['r_descuento']);

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $rowal['r_folio']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, date('d/m/Y',strtotime($rowal['r_fechaapli'])));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, busca($rowal['r_almacen'],'almacenes','a_id','a_siglas'));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $rowal['r_comprador']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, busca($rowal['r_proveedor'],'proveedores','p_id','p_alias'));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, $subtotal);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, $rowal['r_descuento']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, $rowal['r_total']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, $nmb);

    $objPHPExcel->getActiveSheet()->getStyle("F".$i.':'."H".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');

    $totalsubtotal+=$subtotal;
    $totaldescuento+=$rowal['r_descuento'];
    $totalfinal+=$rowal['r_total'];
  }


  $i++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,"SUMATORIAS");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i,$totalsubtotal);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i,$totaldescuento);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i,$totalfinal);
  $objPHPExcel->getActiveSheet()->getStyle("F".$i.':'."H".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');

// formato de moneda
//  $objPHPExcel->getActiveSheet()->getStyle('K'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

$objPHPExcel->getActiveSheet()->setTitle('COMPRAS');

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
header('Content-Disposition: attachment;filename="REPORTE GENERAL DE COMPRAS.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>