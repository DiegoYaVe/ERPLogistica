<?php 
session_start();
date_default_timezone_set('America/Mexico_City');
include ('../funciones.php');
require_once ('../lib/excel/Classes/PHPExcel.php');
//ini_set('display_errors', 1);

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

$Cteal = array(
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
            'argb' => '47BE7D')
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
$Cazul = array(
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
            'argb' => '2980B9')
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
if(empty($_GET['estatus'])) $_GET['estatus'] = 'V';
if(empty($_GET['vendedor'])) $_GET['vendedor'] = NULL;
//if(empty($_GET['empresa'])) $empresa = 1; else $empresa = $_GET['empresa'];


if (PHP_SAPI == 'cli')
    die('Este archivo solo se puede ver desde un navegador web');

$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("JD CEO") // Nombre del autor
    ->setLastModifiedBy("JD CEO") //Ultimo usuario que lo modific?
    ->setTitle("REPORTE DE FORMAS DE PAGO") // Titulo
    ->setSubject("REPORTE DE FORMAS DE PAGO") //Asunto
    ->setDescription("REPORTE DE FORMAS DE PAGO") //Descripci?n
    ->setKeywords("REPORTE DE FORMAS DE PAGO") //Etiquetas
    ->setCategory("REPORTE DE FORMAS DE PAGO"); //Categorias

$tituloReporte = "REPORTE DE FORMAS DE PAGO DESDE: ".fecha_formato($_GET['fini'],false,true).' '."HASTA: ".fecha_formato($_GET['ffin'],false,true);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);
// Se agregan los titulos del reporte
$objPHPExcel->setActiveSheetIndex(0)
    ->mergeCells('A1:I1');

$titulosColumnas = array('Folio', 'Fecha','Vendedor','Cliente','Total remisión','Monto','Comisión','Total ingreso','Forma de pago');
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
  $fpago = $_GET['fpago'];
  if($fpago != '' && $fpago != 'EC' && $fpago != 'TC' && $fpago != 'TD'){
    $sql4 = ' AND i_fpago = "'.$fpago.'"';
  }
  if($_GET['vendedor']) {
    $sql2.=' AND r_encargado = "'.$_GET['vendedor'].'"';
    $sql3.=' AND cd_ugen = "'.$_GET['vendedor'].'"';
  }
  $total = 0;
  $totalcomision = 0;
  $totalingresos = 0;
  $sql = 'SELECT cx_referencia AS remision, i_fecha AS fecha, i_monto AS monto, cf_descripcion as fpago, "I" AS tipo, i_comision AS comision FROM ingresos
            INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso INNER JOIN cxcobrar ON cx_id = ic_cxcobrar INNER JOIN cfdi_fpago ON i_fpago = cf_id 
            WHERE ic_cxcobrar IN (SELECT cx_id FROM cxcobrar INNER JOIN remisiones ON r_id = cx_referencia WHERE cx_tipo = "R" AND r_estatus NOT IN ("N", "C")'.$sql2.')
            AND i_estatus NOT IN ("C", "N", "P") AND DATE(i_fecha) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"'.$sql4;
  $sql.=' UNION SELECT cd_remision AS remision, cd_fgen AS fecha, cd_total AS monto, cd_id AS fpago, "C" AS tipo, cd_comision AS comision
            FROM cajasd INNER JOIN remisiones ON r_id = cd_remision WHERE DATE(cd_fgen) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus NOT IN ("N", "C")'.$sql3.'';
  $sql.='ORDER BY fecha DESC';
  $result = setq($sql);
  while($rowal = $result->fetch_array()){
    $sqlrem = 'SELECT r_folio, r_total, r_cliente, r_encargado FROM remisiones WHERE r_id = "'.$rowal['remision'].'"';
    $resultrem = setq($sqlrem);
    list($folio, $totalrem, $cliente, $encargado) = $resultrem -> fetch_array();
    $nmb = busca($cliente, 'crm_clientes', 'c_id', 'c_nmb');
    $apellidos = busca($cliente, 'crm_clientes', 'c_id', 'c_apellidos');
    $nmbcl = $nmb.' '.$apellidos;
    $nmbencargado = busca($encargado,'usuarios','u_id','CONCAT(u_nmb," ",u_apellidos)');
    if($fpago != "EC" && $fpago != "TD" && $fpago != "TC" && $rowal['tipo'] == "I"){
      $i++;
      $totaling = $rowal['monto']+$rowal['comision'];
      $totalcomision += $rowal['comision'];
      $totalingresos += $totaling;
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $folio);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, fecha_formato($rowal['fecha'],true,true));
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $nmbencargado);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $nmbcl);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $totalrem);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, number_format($rowal['monto'],2));
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, number_format($rowal['comision'],2));
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, number_format($totaling,2));
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, $rowal['fpago']);
      $objPHPExcel->getActiveSheet()->getStyle("E".$i.':'."H".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
      $total+=$rowal['monto'];
      
    }
    if(($fpago == "" || $fpago == "EC" || $fpago == "TD" || $fpago == "TC") && $rowal['tipo'] == "C"){
      $sqlc = 'SELECT cd_efectivo, cd_tarjeta, cd_comision FROM cajasd WHERE cd_id = "'.$rowal['fpago'].'"';
      $resultc = setq($sqlc);
      list($efectivo, $tarjeta, $comision) = $resultc -> fetch_array();
      if(($fpago == "" || $fpago == "EC") && $efectivo > 0){
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $folio);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, fecha_formato($rowal['fecha'],true,true));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $nmbencargado);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $nmbcl);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $totalrem);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, number_format($efectivo,2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, number_format('0',2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, number_format($efectivo,2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, 'EFECTIVO EN CAJA');
        $objPHPExcel->getActiveSheet()->getStyle("E".$i.':'."H".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
        $totalingresos += $efectivo;
        $total+=$efectivo;
      }
      if(($fpago == "" || $fpago == "TC" && $comision > 0) && $tarjeta > 0){
        $i++;
        $totaling = $tarjeta + $comision;
        $totalcomision += $comision;
        $totalingresos += $totaling;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $folio);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, fecha_formato($rowal['fecha'],true,true));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $nmbencargado);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $nmbcl);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $totalrem);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, number_format($tarjeta,2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, number_format($comision,2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, number_format($totaling,2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, 'TARJETA DE CRÉDITO EN CAJA');
        $objPHPExcel->getActiveSheet()->getStyle("E".$i.':'."H".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
        $total+=$tarjeta;
      } else if(($fpago == "" || $fpago == "TD") && $tarjeta > 0){
        $i++;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $folio);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, fecha_formato($rowal['fecha'],true,true));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $nmbencargado);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $nmbcl);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $totalrem);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, number_format($tarjeta,2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, number_format('0',2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, number_format($tarjeta,2));
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, 'TARJETA DE DÉBITO EN CAJA');
        $objPHPExcel->getActiveSheet()->getStyle("E".$i.':'."H".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
        $total+=$tarjeta;
      }
    }
    
  }

  $i++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,"Totales monto");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i,number_format($total, 2));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i,number_format($totalcomision, 2));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i,number_format($totalingresos, 2));

  
// formato de moneda
//  $objPHPExcel->getActiveSheet()->getStyle('K'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

$objPHPExcel->getActiveSheet()->setTitle('VENTAS');

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
    //die('HOLA');
foreach(range('A','Z') as $columnID) { $objPHPExcel->getActiveSheet()->getColumnDimension($columnID) ->setAutoSize(true); }
$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,3);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="REPORTE DE FORMAS DE PAGO.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>