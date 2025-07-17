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

if(!isset($_GET['fini'])) $_GET['fini'] = date('Y-m-d',strtotime('first day of this month'));
  if(!isset($_GET['ffin'])) $_GET['ffin'] = date('Y-m-d',strtotime('last day of this month'));
  if(!isset($_GET['estatus'])) $_GET['estatus'] = "V";
  if(!isset($_GET['vendedor'])) $_GET['vendedor'] = NULL;
  if(!isset($_GET['cliente'])) $_GET['cliente'] = NULL;
  if(!isset($_GET['categoria'])) $_GET['categoria'] = NULL;
//if(empty($_GET['empresa'])) $empresa = 1; else $empresa = $_GET['empresa'];

if (PHP_SAPI == 'cli')
    die('Este archivo solo se puede ver desde un navegador web');

$objPHPExcel = new PHPExcel();

$objPHPExcel->getProperties()->setCreator("JD CEO") // Nombre del autor
    ->setLastModifiedBy("JD CEO") //Ultimo usuario que lo modific?
    ->setTitle("REPORTE DE VENTAS POR PRODUCTO") // Titulo
    ->setSubject("REPORTE DE VENTAS POR PRODUCTO") //Asunto
    ->setDescription("REPORTE DE VENTAS POR PRODUCTO") //Descripci?n
    ->setKeywords("REPORTE DE VENTAS POR PRODUCTO") //Etiquetas
    ->setCategory("REPORTE DE VENTAS POR PRODUCTO"); //Categorias


// *************** INICIO DE LA TABLA DE ARTICULOS VENDIDOS******************   

$tituloReporte = "REPORTE DE VENTAS POR PRODUCTO DESDE: ".fecha_formato($_GET['fini'],false,true).' '."HASTA: ".fecha_formato($_GET['ffin'],false,true);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);
// Se agregan los titulos del reporte
$objPHPExcel->setActiveSheetIndex(0)
    ->mergeCells('A1:E1');

$titulosColumnas = array('Modelo', 'Producto','Cantidad','Importe','% del total');
$letra = "A";
foreach($titulosColumnas as $titc){
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letra.'2',$titc);
  $letra++;
}

  $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray($estiloTituloReporte);
  $objPHPExcel->getActiveSheet()->getStyle('A1:E2')->applyFromArray($estiloTituloReporte);

  $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:E1');

  //$a=0;
  $i = 2; //Numero de fila donde se va a comenzar a rellenar
  $col = "A";

  $sql = 'SELECT r_folio,rd_articulo,rd_modelo,rd_cantidad,remisiones.r_descuento,rd_precio, a_categoria, r_encargado
          FROM remisiones INNER JOIN remisionesd ON r_id = rd_remision INNER JOIN articulos ON rd_articulo = a_id 
          WHERE DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus IN ("A","F","FE")';

  if($_GET['vendedor']) $sql.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
  if($_GET['categoria']) {
    if($_GET['categoria'] == "T") $sql .= ' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
    else $sql.=' AND a_categoria = "'.$_GET['categoria'].'"';
  }
  $sql.=' ORDER BY r_faplica DESC';

  $resultremd = setq($sql);

  $sqls = 'SELECT SUM(r_total-r_iva-r_precioenvio) FROM remisiones
           WHERE DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus IN ("A","F","FE")';
  if($_GET['vendedor']) $sqls.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
  $results = setq($sqls);
  list($sumaprod) = $results->fetch_array();
  $totalventa = $sumaprod;

  $sql2 = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, r_encargado, a_nmb FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision 
              INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE") AND rc_ligado IS NULL';
  if($_GET['categoria']) {
      if($_GET['categoria'] == "T") $sql2 .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
      else $sql2.=' AND a_categoria = "'.$_GET['categoria'].'"';
  }            
  if($_GET['asesor']) $sql2.=' AND r_encargado = "'.$_GET['asesor'].'"';
  $sql2.=' GROUP BY rc_articulo, rc_modelo, r_encargado ORDER BY a_nmb ASC';
  
  $result2 = setq($sql);
  while($row2 = $result2->fetch_array()){
    $cant[$row2['rd_articulo']][$row2['rd_modelo']] = 0;
    $import[$row2['rd_articulo']][$row2['rd_modelo']] = 0;
    $vcat[$row2['r_encargado']][$row2['a_categoria']] = 0;
  }

  $result = setq($sql2);
  while($row = $result -> fetch_array()){
    $cant[$row['articulo']][$row['rc_modelo']]+= $row['cantidad'];
    $vcat[$row['r_encargado']][$row['a_categoria']]+= $row['cantidad'];
  }

  $resultremd = setq($sql);
  while($row = $resultremd->fetch_array()){
    $aplicadesc = busca($row['rd_articulo'], 'articulos', 'a_id', 'a_descuento');
    if($row['r_descuento'] == 0)
      $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*(($row['rd_precio']));
    else{
      if($aplicadesc == "1") $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*($row['rd_precio']-($row['rd_precio']*($row['r_descuento']/100)));
      else $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*(($row['rd_precio']));
    }
  }
  $numrems++;
  
  $result = setq($sql);
  $sumacant = 0;
  $sumatot = 0;
  $sumaporc = 0;
  $sql3 = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, r_encargado, a_nmb FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision 
          INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE") AND rc_ligado IS NULL';
if($_GET['categoria']) {
  if($_GET['categoria'] == "T") $sql3 .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
  else $sql3.=' AND a_categoria = "'.$_GET['categoria'].'"';
}            
if($_GET['asesor']) $sql3.=' AND r_encargado = "'.$_GET['asesor'].'"';
$sql3.=' GROUP BY rc_articulo, rc_modelo ORDER BY a_nmb ASC';
  $result2 = setq($sql3);
  while($row = $result2->fetch_array()){
    $i++;
    $porc = ($import[$row['articulo']][$row['rc_modelo']]/$totalventa)*100;
    $nmb = $row['a_nmb'];
    if($row['rc_modelo']){
      $nmb.= ' '.busca($row['rc_modelo'], 'articulos_variantes', 'av_articulo = "'.$row['articulo'].'" AND av_modelo' ,'av_nmb');
    }

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $row['rc_modelo']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, $nmb);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $cant[$row['articulo']][$row['rc_modelo']]);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $import[$row['articulo']][$row['rc_modelo']]);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, number_format($porc,2).'%');

    $objPHPExcel->getActiveSheet()->getStyle("D".$i.':'."D".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');

    $sumacant+=$cant[$row['articulo']][$row['rc_modelo']];
    $sumatot+=$import[$row['articulo']][$row['rc_modelo']];
    $sumaporc+=$porc;
  }

  $i++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i,"Totales");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i,$sumacant);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i,$sumatot);
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,number_format($sumaporc, 2).'%');
  $objPHPExcel->getActiveSheet()->getStyle("D".$i.':'."D".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
// formato de moneda
//  $objPHPExcel->getActiveSheet()->getStyle('K'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

// *************** FIN DE LA TABLA DE ARTICULOS VENDIDOS******************   

// *************** INICIO DE LA TABLA DE PAQUETES VENDIDOS******************

$tituloReporte = "PAQUETES VENDIDOS";
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1',$tituloReporte);
// Se agregan los titulos del reporte
$objPHPExcel->setActiveSheetIndex(0)
    ->mergeCells('G1:J1');

$titulosColumnas = array('Paquete', 'Cantidad', 'Importe', '% al total');
$letra = "G";
foreach($titulosColumnas as $titc){
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letra.'2',$titc);
  $letra++;
}

$objPHPExcel->getActiveSheet()->getStyle('G1:J1')->applyFromArray($estiloTituloReporte);
$objPHPExcel->getActiveSheet()->getStyle('G1:J2')->applyFromArray($estiloTituloReporte);

$objPHPExcel->setActiveSheetIndex(0)->mergeCells('G1:J1');

$i=2;
$sumpack = 0;
$summon = 0;
$sumport = 0;
$sql = 'SELECT rd_nmbarticulo, SUM(rd_cantidad) cantidad, SUM(rd_cantidad*rd_precio) precio FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision INNER JOIN articulos ON a_id = rd_articulo
    WHERE a_tipoprod = "M" AND DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE")';
if($_GET['asesor']) $sql.=' AND r_encargado = "'.$_GET['asesor'].'"';
$sql .= ' GROUP BY rd_articulo, rd_modelo';
$result3 = setq($sql);
while($row = $result3 -> fetch_array()){
    $porc =($row['precio']/$totalventa)*100;
    $i++;
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, $row['rd_nmbarticulo']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, number_format($row['cantidad'],0));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, number_format($row['precio'],2));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$i, number_format($porc,2));
    
    $sumpack += $row['cantidad'];
    $summon += $row['precio'];
    $sumport += $porc;
}
$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, "TOTALES");
$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, number_format($sumpack,0));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I".$i, number_format($summon,2));
$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$i, number_format($sumport,2));

$objPHPExcel->getActiveSheet()->setTitle('VENTAS POR PRODUCTO');
// *************** FIN DE LA TABLA DE PAQUETES VENDIDOS******************

// *************** INICIO DE LA TABLA DE VENTAS POR CATEGORIA******************
$tituloReporte = "Ventas por categoria";
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L1',$tituloReporte);

$objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2',"Asesor");
$letra = "M";

$sqlcat = 'SELECT * FROM categorias';
if($_GET['categoria']) {
  if($_GET['categoria'] == "T") $sqlcat .= ' WHERE cat_inflable = "1"';
  else $sqlcat .= ' WHERE cat_id = "'.$_GET['categoria'].'"';
}
$resultcat = setq($sqlcat);
while($row = $resultcat -> fetch_array()){
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letra.'2',$row['cat_nmb']);
  $letra++;
}
$letra--;
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('L1:'.$letra.'1');

$objPHPExcel->getActiveSheet()->getStyle('L1:'.$letra.'1')->applyFromArray($estiloTituloReporte);
$objPHPExcel->getActiveSheet()->getStyle('L1:'.$letra.'2')->applyFromArray($estiloTituloReporte);

$objPHPExcel->setActiveSheetIndex(0)->mergeCells('L1:'.$letra.'1');

$i=2;
$sumpack = 0;
$summon = 0;
$sumport = 0;
$sumcat = array();
$sqlusu = 'SELECT DISTINCT(r_encargado), u_id, u_nmb, u_apellidos FROM remisiones INNER JOIN usuarios ON r_encargado = u_id WHERE DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE")';
if($_GET['asesor']) $sqlusu.=' AND r_encargado = "'.$_GET['asesor'].'"';
$resultu = setq($sqlusu);
while($rowu = $resultu -> fetch_array()){
  $i++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("L".$i, $rowu['u_nmb'].' '.$rowu['u_apellidos']);
  $j='M';
  $resultcat = setq($sqlcat);
  while($rowcat = $resultcat -> fetch_array()){
    if(!$vcat[$rowu['r_encargado']][$rowcat['cat_id']]) $vcat[$rowu['r_encargado']][$rowcat['cat_id']] = 0;
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($j.$i, $vcat[$rowu['r_encargado']][$rowcat['cat_id']]);
    if(!$sumcat[$rowcat['cat_id']]) $sumcat[$rowcat['cat_id']] = 0;
    $sumcat[$rowcat['cat_id']]+= $vcat[$rowu['r_encargado']][$rowcat['cat_id']];
    $j++;
  }
}
$i++;
$j = "M";
$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L".$i, "TOTALES");
$resultcat = setq($sqlcat);
while($rowcat = $resultcat -> fetch_array()){
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($j.$i, number_format($sumcat[$rowcat['cat_id']],0));
  $j++;
}

// *************** FIN DE LA TABLA DE VENTAS POR CATEGORIA******************

$objPHPExcel->getActiveSheet()->setTitle('VENTAS POR PRODUCTO');
$objPHPExcel->setActiveSheetIndex(0);
// Inmovilizar paneles
//$objPHPExcel->getActiveSheet(0)->freezePane('A4');
$objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()
    ->getPageSetup()
    ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(0);
    //$objPHPExcel->getActiveSheet()->getPageSetup()->setScale(40);
    //die('HOLA');
foreach(range('A','Z') as $columnID) { $objPHPExcel->getActiveSheet()->getColumnDimension($columnID) ->setAutoSize(true); }
$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,3);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="REPORTE DE VENTAS POR PRODUCTO.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>