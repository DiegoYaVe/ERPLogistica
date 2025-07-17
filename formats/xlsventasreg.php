<?php
//ini_set('display_errors',1);
session_start();
require_once '../lib/excel/Classes/PHPExcel.php';
include ('../funciones.php');

$objPHPExcel = new PHPExcel();
$objWorksheet = $objPHPExcel->getActiveSheet();

$fini = $_GET['fini'];
$ffin = $_GET['ffin'];

if($fini || $ffin ){
  $totalnovendidos = busca('V','registros_jdceo',' DATE(rj_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND rj_estatus !','COUNT(*)');
  $totalvendidos = busca('V','registros_jdceo',' DATE(rj_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND  rj_estatus','COUNT(*)');
  $totalreg = busca('1','registros_jdceo',' DATE(rj_fecha) BETWEEN "'.$fini.'" AND "'.$ffin.'" AND 1',' COUNT(*)');
}

$estatust = array("V"=>"Vendidos","OTROS"=>"No Vendidos");



$porcentaje[] = '';
$label[] = 'Total';

$porcentaje[] = ($totalnovendidos*100)/$totalreg;
$porcentaje[] = ($totalvendidos*100)/$totalreg;

$label[] = '% '.$estatust['OTROS'];
$label[] = '% '.$estatust['V'];

$index[] = '("N","P","F","X","R","T")';
$index[] = '("V")';

$letra = 'C';
 $objWorksheet->fromArray(
  array(
    $label,
    $porcentaje,
  )
); 



//	Set the Labels for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
$dataseriesLabels1 = array(
	new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$A$2', null, 1),	//	2010
  //'PRUEBA',
);
//	Set the X-Axis Labels
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
$xAxisTickValues1 = array(
	new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$1:$'.$letra.'$1', null, 4),	//	Q1 to Q4
/*   new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$1', null, 4),	//	Q1 to Q4
  new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$C$1', null, 4),	//	Q1 to Q4  */
);
//	Set the Data values for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
$dataSeriesValues1 = array(
	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$2:$'.$letra.'$2', null, 4),
/* 	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$2', null, 4),
	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$C$2', null, 4), */
);


//	Build the dataseries
$series1 = new PHPExcel_Chart_DataSeries(
	PHPExcel_Chart_DataSeries::TYPE_PIECHART,				// plotType
	NULL,
	range(0, count($dataSeriesValues1)-1),					// plotOrder
	$dataseriesLabels1,										// plotLabel
	$xAxisTickValues1,										// plotCategory
	$dataSeriesValues1										// plotValues
);

//	Set up a layout object for the Pie chart
$layout1 = new PHPExcel_Chart_Layout();
$layout1->setShowVal(FALSE);
$layout1->setShowPercent(TRUE);

//	Set the series in the plot area
$plotarea1 = new PHPExcel_Chart_PlotArea($layout1, array($series1));
//	Set the chart legend
$legend1 = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, null, false);

$title1 = new PHPExcel_Chart_Title('Porcentaje de Estatus');


//	Create the chart
$chart1 = new PHPExcel_Chart(
	'chart1',		// name
	$title1,		// title
	$legend1,		// legend
	$plotarea1,		// plotArea
	true,			// plotVisibleOnly
	0,				// displayBlanksAs
	null,			// xAxisLabel
	null			// yAxisLabel		- Pie charts don't have a Y-Axis
);

$chart1->setTopLeftPosition('A1');
$chart1->setBottomRightPosition('P25');

//	Add the chart to the worksheet
$objWorksheet->addChart($chart1);

// Save Excel 2007 file
//echo date('H:i:s') , " Write to Excel2007 format" , EOL;
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->setIncludeCharts(TRUE);
//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Porcentaje de Ventas.xlsx"');
//header('Cache-Control: max-age=0');

//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');


?>