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
    ->setTitle("REPORTE GENERAL DE VENTAS") // Titulo
    ->setSubject("REPORTE GENERAL DE VENTAS") //Asunto
    ->setDescription("REPORTE GENERAL DE VENTAS") //Descripci?n
    ->setKeywords("REPORTE GENERAL DE VENTAS") //Etiquetas
    ->setCategory("REPORTE GENERAL DE VENTAS"); //Categorias

$tituloReporte = "REPORTE GENERAL DE VENTAS DESDE: ".fecha_formato($_GET['fini'],false,true).' '."HASTA: ".fecha_formato($_GET['ffin'],false,true);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);
// Se agregan los titulos del reporte
$objPHPExcel->setActiveSheetIndex(0)
    ->mergeCells('A1:I1');

$titulosColumnas = array('Folio', 'Fecha','Vendedor','Cliente','Telefono','Subtotal','Descuento','Total Remision','Envío','Estatus');
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
  $sql = 'SELECT * FROM remisiones INNER JOIN remisionesd ON rd_remision = r_id WHERE rd_articulo != "0" AND DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus != "N"';
  if($_GET['cliente']) $sql.=' AND r_cliente IN (SELECT c_id FROM crm_clientes WHERE 
    (c_nmb LIKE "%'.$_GET['cliente'].'%" OR c_alias LIKE "%'.$_GET['cliente'].'%" OR c_apellidos LIKE "%'.$_GET['cliente'].'%") )';
  if($_GET['vendedor']) $sql.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
  if($_GET['estatus']){
    if($_GET['estatus'] == "V") $sql.=' AND r_estatus IN ("A","F","FE")';
    else if ($_GET['estatus'] == "F") $sql.=' AND r_estatus IN ("F","FE")';
    else if ($_GET['estatus'] == "P") $sql.=' AND r_estatus IN ("P","D")';
    else $sql.=' AND r_estatus = "'.$_GET['estatus'].'" ';
  }
  $sql.='GROUP BY r_id ORDER BY r_faplica DESC';
  $resultrem = setq($sql);
  //echo $sql;
  

  $totalsubtotal = 0;
  $totaldescuento = 0;
  $totalfinal = 0;
  $totalenvios = 0;
  $numrems = 0;

  while($rowal = $resultrem->fetch_array()){
    $numrems++;
    $i++;
    if($rowal['r_estatus'] == "A"){
      $objPHPExcel->getActiveSheet()->getStyle("J".$i)->applyFromArray($Cazul);
      $nmb = "Credito";
    }
    elseif($rowal['r_estatus'] == "C"){
      $objPHPExcel->getActiveSheet()->getStyle("J".$i)->applyFromArray($estiloX);
      $nmb = "Cancelada";
    }
    elseif($rowal['r_estatus'] == "P" || $rowal['r_estatus'] == "D"){
        $objPHPExcel->getActiveSheet()->getStyle("J".$i)->applyFromArray($Cnaranja);
        $nmb = "Pendiente";
    }
    elseif($rowal['r_estatus'] == "F"){
        $objPHPExcel->getActiveSheet()->getStyle("J".$i)->applyFromArray($Cverde);
        $nmb = "Liquidada";
    }
    elseif($rowal['r_estatus'] == "FE"){
        $objPHPExcel->getActiveSheet()->getStyle("J".$i)->applyFromArray($Cteal);
        $nmb = "Enviada";
    }
    
    $subtotal = ($rowal['r_total']-$rowal['r_iva']+$rowal['r_mdescuento']-$rowal['r_precioenvio']);
    $totalrem = ($rowal['r_total']-$rowal['r_iva']-$rowal['r_precioenvio']);

    $cliente = busca($rowal['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb').' '.busca($rowal['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
    $telefono = busca($rowal['r_cliente'], 'crm_clientes', 'c_id', 'c_telefono2');

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $rowal['r_folio']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, date('d/m/Y',strtotime($rowal['r_faplica'])));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, busca($rowal['r_encargado'],'usuarios','u_id','CONCAT(u_nmb, " ", u_apellidos)'));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $cliente);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $telefono);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, $subtotal);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, $rowal['r_mdescuento']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, $totalrem);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, $rowal['r_precioenvio']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$i, $nmb);

    $objPHPExcel->getActiveSheet()->getStyle("F".$i.':'."I".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
    $objPHPExcel->getActiveSheet()->getStyle("E".$i.':'."E".$i)->getNumberFormat()->setFormatCode('##0');

    $totalsubtotal+=$subtotal;
    $totaldescuento+=$rowal['r_mdescuento'];
    $totalfinal+=$totalrem;
    $totalenvios+=$rowal['r_precioenvio'];
  }
  if($totalsubtotal > 0) {
    $pordesc = ($totaldescuento*100)/$totalsubtotal;
    $porfinal = ($totalfinal*100)/$totalsubtotal;
  } else {
    $pordesc = 0;
    $porfinal = 0;
  }

  $sqlnif = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON rc_remision = r_id INNER JOIN articulos ON rc_articulo = a_id WHERE r_estatus IN ("A","F","FE")
  AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") AND DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"';
  if($_GET['vendedor']) $sqlnif.=' AND r_encargado = "'.$_GET['vendedor'].'"';
  $sqlnif.= ' GROUP BY r_id';
  $resultnif = setq($sqlnif);
  $infvf = $resultnif -> num_rows;  

  $sqlni = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON rc_remision = r_id INNER JOIN articulos ON rc_articulo = a_id WHERE r_estatus IN ("A","F","FE")
  AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") AND DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"';
  if($_GET['vendedor']) $sqlni.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
  $resultni = setq($sqlni);
  list($infv) = $resultni -> fetch_array();  

  $i++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,"Totales monto");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i,$totalsubtotal);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i,$totaldescuento);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i,$totalfinal);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i,$totalenvios);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$i,"N. ventas: ".$numrems);
  $objPHPExcel->getActiveSheet()->getStyle("F".$i.':'."I".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
  $i++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,"Totales porcentajes");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i,'100%');
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i,number_format($pordesc, 2).'%');
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i,number_format($porfinal, 2).'%');
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$i,"N. ventas con inflable: ".$infvf);
  $i++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$i,"Inflables vendidos: ".$infv);
  
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
header('Content-Disposition: attachment; filename="REPORTE GENERAL DE VENTAS.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>