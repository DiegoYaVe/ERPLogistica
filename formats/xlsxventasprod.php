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
    ->setTitle("REPORTE DE VENTAS POR PRODUCTO") // Titulo
    ->setSubject("REPORTE DE VENTAS POR PRODUCTO") //Asunto
    ->setDescription("REPORTE DE VENTAS POR PRODUCTO") //Descripci?n
    ->setKeywords("REPORTE DE VENTAS POR PRODUCTO") //Etiquetas
    ->setCategory("REPORTE DE VENTAS POR PRODUCTO"); //Categorias

$tituloReporte = "REPORTE DE VENTAS POR PRODUCTO DESDE: ".fecha_formato($_GET['fini'],false,true).' '."HASTA: ".fecha_formato($_GET['ffin'],false,true);
$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$tituloReporte);
// Se agregan los titulos del reporte
$objPHPExcel->setActiveSheetIndex(0)
    ->mergeCells('A1:I1');

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
  if($_GET['categoria']) $sql.=' AND a_categoria = "'.$_GET['categoria'].'"';
  $sql.=' ORDER BY r_faplica DESC';

  $resultremd = setq($sql);

  $sqls = 'SELECT SUM(r_total-r_iva-r_precioenvio) FROM remisiones
           WHERE DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus IN ("A","F","FE")';
  if($_GET['vendedor']) $sqls.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
  $results = setq($sqls);
  list($sumaprod) = $results->fetch_array();
  $totalventa = $sumaprod;

  $sql2 = 'SELECT DISTINCT(rd_articulo) articulo, rd_nmbarticulo,rd_modelo, a_tipoprod, a_categoria, r_encargado
            FROM remisiones INNER JOIN remisionesd ON r_id = rd_remision INNER JOIN articulos ON a_id = rd_articulo
            WHERE  DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE")';
  if($_GET['categoria']) $sql2.=' AND a_categoria = "'.$_GET['categoria'].'"';          
  if($_GET['vendedor']) $sql2.=' AND r_encargado = "'.$_GET['vendedor'].'" ORDER BY rd_nmbarticulo';
  $sql2.=' GROUP BY rd_articulo, rd_modelo';
  $result2 = setq($sql2);
  while($row2 = $result2->fetch_array()){
    $cant[$row2['articulo']][$row2['rd_modelo']] = 0;
    $import[$row2['articulo']][$row2['rd_modelo']] = 0;
    $vcat[$row2['r_encargado']][$row2['a_categoria']] = 0;
  }

  $resultremd = setq($sql);
  while($row = $resultremd->fetch_array()){
    $cant[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad'];
    $aplicadesc = busca($row['rd_articulo'], 'articulos', 'a_id', 'a_descuento');
    if($row['r_descuento'] == 0)
      $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*(($row['rd_precio']));
    else{
      if($aplicadesc == "1") $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*($row['rd_precio']-($row['rd_precio']*($row['r_descuento']/100)));
      else $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*(($row['rd_precio']));
    }
    $vcat[$row['r_encargado']][$row['a_categoria']]+=$row['rd_cantidad'];
  }
  $numrems++;
  
  $result = setq($sql);
  $sumacant = 0;
  $sumatot = 0;
  $sumaporc = 0;
  
  $result2 = setq($sql2);
  while($row = $result2->fetch_array()){
    $porc = ($import[$row['articulo']][$row['rd_modelo']]/$totalventa)*100;
    echo '<tr>
              <th>'.$row['rd_modelo'].'</th>
              <th>'.$row['rd_nmbarticulo'].'</th>
              <th style="text-align:right;">'.number_format($cant[$row['articulo']][$row['rd_modelo']],0).'</th>
              <th style="text-align:right;">$ '.number_format($import[$row['articulo']][$row['rd_modelo']],2).'</th>
              <th style="text-align:right;">'.number_format($porc,2).'%</th>
            </tr>';

    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("A".$i, $row['rd_modelo']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i, $row['rd_nmbarticulo']);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i, number_format($cant[$row['articulo']][$row['rd_modelo']],0));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i, number_format($import[$row['articulo']][$row['rd_modelo']],2));
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i, number_format($porc,2).'%');

    $objPHPExcel->getActiveSheet()->getStyle("D".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)'); //.':'."I".$i

    $sumacant+=$cant[$row['articulo']][$row['rd_modelo']];
    $sumatot+=$import[$row['articulo']][$row['rd_modelo']];
    $sumaporc+=$porc;
    $i++;
  }

  $i++;
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$i,"Totales");
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C".$i,number_format($sumacant,0));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D".$i,number_format($sumatot,2));
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E".$i,number_format($sumaporc,2));
  $objPHPExcel->getActiveSheet()->getStyle("D".$i)->getNumberFormat()->setFormatCode('_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)');
  
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
header('Content-Disposition: attachment; filename="REPORTE DE VENTAS POR PRODUCTO.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>