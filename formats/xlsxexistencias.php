<?php
//ini_set('display_errors',1);
session_start();
require_once '../lib/excel/Classes/PHPExcel.php';
include ('../funciones.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        //'wrap' => TRUE
    )
);

$estiloX = array(
    'font' => array(
        'name'      => 'KALINGA',
        'bold'      => false,
        'italic'    => false,
        'strike'    => false,
        'size' =>7,
        'color'     => array(
            'rgb' => '000000'
        )
    ),
    'fill' => array(
      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array(
            'argb' => 'ffffff')
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
        'size' =>9,
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
        //'wrap' => true
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
            'argb' => 'FFFFFF'
        )
    ),
    'borders' => array(
        'top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
        'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN)
    ),
    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        //'wrap' => TRUE
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
        //'wrap' => TRUE
    )
);

    if(!empty($_GET['categoria'])) $categoria = $_GET['categoria']; else $categoria = NULL;
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
    ->mergeCells('A1:H1');

$titulosColumnas = array('ALMACEN', 'ARTÍCULO', 'MODELO','CATEGORIA','LIBRE','APARTADO','FÍSICO','POR REPONER');
$letra = "A";
foreach($titulosColumnas as $titc){
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue($letra.'2',$titc);
  $letra++;
}

  $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray($estiloTituloReporte);
  $objPHPExcel->getActiveSheet()->getStyle('A1:H2')->applyFromArray($estiloTituloColumnas);

  $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:H1');

  //$a=0;
  $i = 2; //Numero de fila donde se va a comenzar a rellenar
  $col = "A";
  $sqlal = 'SELECT * FROM almacenes where a_estatus="A"';
  if($almacenes) $sqlal.=' AND a_id = "'.$almacenes.'" ';
  $resultal = setq($sqlal) or die($sqlal);
  while ($rowal = $resultal->fetch_array()) {
    $datosla = existenciaxestatus($rowal['a_id'], "LA");
    //$datosla = json_decode($la); 
    $datosva = existenciaxestatus($rowal['a_id'], "VA");
    //$datosva = json_decode($va); 
    $datosvp = existenciaxestatus($rowal['a_id'], "VP");
    $sqlex = 'SELECT a_id id, a_nmb nmb, cat_nmb categoria, cat_id catid FROM articulos INNER JOIN categorias ON a_categoria = cat_id WHERE a_tipoprod != "M" AND a_inventariado = "1" ORDER BY a_nmb ASC';
    $resultex = setq($sqlex) or die($sqlex);
  
    $libres = array();
    $apartados = array();
    $fisic = array();
    $xreponer = array();
  
    while ($row = $resultex->fetch_array()){
        $dif = 0;
        $var = busca($row['id'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
        if($var){ 
          $sqlvar = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$row['id'].'"';
          $resultvar = setq($sqlvar);
          while($rowvar = $resultvar -> fetch_array()){
            $articulo = $row['nmb'].' '.$rowvar['av_nmb'];
            $modelo = $rowvar['av_modelo'];
            $existencia = existenciaModelo($row['id'], $rowal['a_id'], $rowvar['av_modelo']);
            $libre = $existencia;
            if(floatval($libre) < 1){ 
                $dif = abs($libre);
                $libre = 0;
              }
            $vendido_abonado = $datosva[$row['id']][$modelo];
            $vendido_pagado = $datosvp[$row['id']][$modelo];
            $reponer = busca($row['id'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_modelo = "'.$rowvar['av_modelo'].'" AND xp_almacen = "'.$rowal['a_id'].'" AND xp_articulo', 'xp_cantidad');             
            if(floatval($reponer) < 1){ 
                $reponer = 0;
              }
            $disponibles = $libre + $vendido_abonado + $vendido_pagado - $reponer;
            if($disponibles < 0) $disponibles = 0;
          
          $i++;
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $rowal['a_nmb']);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, $articulo);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $modelo);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $row['categoria']);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $libre);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, $vendido_abonado+$vendido_pagado);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, $disponibles);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, $reponer);
          $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':H'.$i)->applyFromArray($estiloX);
          if(!isset($libres[$row['catid']])) $libres[$row['catid']] = 0;
          $libres[$row['catid']]+=$libre;
          if(!isset($apartados[$row['catid']])) $apartados[$row['catid']] = 0;
          $apartados[$row['catid']]+=$vendido_abonado+$vendido_pagado;
          if(!isset($fisic[$row['catid']])) $fisic[$row['catid']] = 0;
          $fisic[$row['catid']]+=$disponibles;
          if(!isset($xreponer[$row['catid']])) $xreponer[$row['catid']] = 0;
          $xreponer[$row['catid']]+=$reponer;
       }
      } else {
        $articulo = $row['nmb'];
        $modelo = "";
        //echo 'datos LA: '.$datosla[$row['id']][$row['modelo']];
        //echo 'articulo: '.$row['id'].' modelo: '.$row['modelo'];
        $existencia = existencia($row['id'], $rowal['a_id']);
        $libre = $existencia;
        if(floatval($libre) < 1){ 
            $dif = abs($libre);
            $libre = 0;
          }
        $vendido_abonado = $datosva[$row['id']][$modelo];
        $vendido_pagado = $datosvp[$row['id']][$modelo];
        $reponer = busca($row['id'], 'existencia_pendiente', 'xp_estatus = "N" AND xp_almacen = "'.$rowal['a_id'].'" AND xp_articulo', 'xp_cantidad');
        if(floatval($reponer) < 1){ 
            $reponer = 0;
          }
        $disponibles = $libre + $vendido_abonado+ $vendido_pagado - $reponer;
        if($disponibles < 0) $disponibles = 0;
        $i++;
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $rowal['a_nmb']);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, $articulo);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, $modelo);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, $row['categoria']);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, $libre);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F".$i, $vendido_abonado + $vendido_pagado);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G".$i, $disponibles);
          $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H".$i, $reponer);
          $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':H'.$i)->applyFromArray($estiloX);
          if(!isset($libres[$row['catid']])) $libres[$row['catid']] = 0;
          $libres[$row['catid']]+=$libre;
          if(!isset($apartados[$row['catid']])) $apartados[$row['catid']] = 0;
          $apartados[$row['catid']]+=$vendido_abonado+$vendido_pagado;
          if(!isset($fisic[$row['catid']])) $fisic[$row['catid']] = 0;
          $fisic[$row['catid']]+=$disponibles;
          if(!isset($xreponer[$row['catid']])) $xreponer[$row['catid']] = 0;
          $xreponer[$row['catid']]+=$reponer;
      }
      
    }
    
  }
  $a=1;
  $sqlcategoria = 'SELECT * FROM categorias WHERE cat_estatus = "A"  ORDER BY cat_nmb';
  $resultcategoria = setq($sqlcategoria);
  $tlibres = 0;
  $tapartados = 0;
  $tfisic = 0;
  $txreponer = 0;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$a, utf8_decode("RESUMEN"));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("K".$a, "TOTAL EXISTENCIA");
  $objPHPExcel->setActiveSheetIndex(0)
    ->mergeCells('K1:N1');
    $objPHPExcel->getActiveSheet()->getStyle('J1:K1')->applyFromArray($estiloInformacion);
  $objPHPExcel->getActiveSheet()->getStyle('J2:N2')->applyFromArray($estiloTituloColumnas);
  $a++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$a, "CATEGORIA");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("K".$a, "LIBRE");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("L".$a, "APARTADO");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("M".$a, "FISICO");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("N".$a, "POR REPONER");
  $a++;
  while($rowc = $resultcategoria->fetch_array()){

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$a, utf8_decode($rowc['cat_nmb']));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("K".$a, number_format($libres[$rowc['cat_id']],0));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("L".$a, number_format($apartados[$rowc['cat_id']],0));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("M".$a, number_format($fisic[$rowc['cat_id']],0));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("N".$a, number_format($xreponer[$rowc['cat_id']],0));
    $objPHPExcel->getActiveSheet()->getStyle('J'.$a.':N'.$a)->applyFromArray($estiloX);
    $a++;
    $tlibres+= $libres[$rowc['cat_id']];
    $tapartados+= $apartados[$rowc['cat_id']];
    $tfisic+= $fisic[$rowc['cat_id']];
    $txreponer+= $xreponer[$rowc['cat_id']];
  }
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J".$a, utf8_decode("TOTALES"));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("K".$a, number_format($tlibres,0));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("L".$a, number_format($tapartados,0));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("M".$a, number_format($tfisic,0));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("N".$a, number_format($txreponer,0));
  $objPHPExcel->getActiveSheet()->getStyle('J'.$a.':N'.$a.'')->applyFromArray($Cgris);

  /* $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getAlignment()->setWrapText(true);
  $objPHPExcel->getActiveSheet()->getStyle('A2:N2')->getAlignment()->setWrapText(true);
  // Al finalizar el ciclo, ajusta el ancho de las columnas
  foreach (range('A', 'N') as $columnID) {
    if ($columnID != 'A') { // Omitir específicamente la primera fila
        $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
    }
} */

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