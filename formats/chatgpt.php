<?php
//ini_set('display_errors',1);
session_start();
require_once '../lib/excel/Classes/PHPExcel.php';
include ('../funciones.php');

$objPHPExcel = new PHPExcel();
$objWorksheet = $objPHPExcel->getActiveSheet();
/*
$objWorksheet->fromArray(
	array(
		array(' ','Basico',	'Ideal',	'Experto'),
		array('Ventas', 12,   15,		21),
	)
);*/

$fini = $_GET['fini'];
$ffin = $_GET['ffin'];

$plan = array("B"=>"Basico","I"=>"Ideal","E"=>"Experto");
$sql = 'SELECT lm_plan,COUNT(*) FROM licencias_renovaciones INNER JOIN licencias_modalidades ON lr_modalidad = lm_id 
WHERE lr_estatus IN ("A","P","X") 
AND lm_tipo = "L" ';
if($fini || $ffin){
  $sql .= ' AND  (DATE(lr_fini) BETWEEN "'.$fini.'" AND "'.$ffin.'" OR DATE(lr_flimite) BETWEEN "'.$fini.'" AND "'.$ffin.'") ';
}
$sql .= ' GROUP BY lm_plan ';
$result = setq($sql);
$label[] = '';
$total[] = 'Total';
$letra = 'A';
while($row = $result->fetch_array()){
  $total[] = $row['COUNT(*)'];
  $label[] = $plan[$row['lm_plan']];
  $index[] = $row['lm_plan']; 
  $letra++;
}

$objWorksheet->fromArray(
  array(
    $label,
    $total,
  )
);

//	Set the Labels for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
$dataseriesLabels = array(
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
$xAxisTickValues = array(
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
$dataSeriesValues = array(
	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$2:$'.$letra.'$2', null, 4),
/* 	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$2', null, 4),
	new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$C$2', null, 4), */
);

//	Build the dataseries
$series = new PHPExcel_Chart_DataSeries(
	PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
	PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,	// plotGrouping
	range(0, count($dataSeriesValues)-1),			// plotOrder
	$dataseriesLabels,								// plotLabel
	$xAxisTickValues,								// plotCategory
	$dataSeriesValues								// plotValues
);
//	Set additional dataseries parameters
//		Make it a horizontal bar rather than a vertical column graph
$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);

//	Set the series in the plot area
$plotarea = new PHPExcel_Chart_PlotArea(null, array($series));
//	Set the chart legend
$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_RIGHT, null, false);

$title = new PHPExcel_Chart_Title('Ventas X Plan');
$yAxisLabel = new PHPExcel_Chart_Title('Total');


//	Create the chart
$chart = new PHPExcel_Chart(
	'chart1',		// name
	$title,			// title
	$legend,		// legend
	$plotarea,		// plotArea
	true,			// plotVisibleOnly
	0,				// displayBlanksAs
	null,			// xAxisLabel
	$yAxisLabel		// yAxisLabel
);

//	Set the position where the chart should appear in the worksheet
$chart->setTopLeftPosition('A1');
$chart->setBottomRightPosition('P25');

//	Add the chart to the worksheet
$objWorksheet->addChart($chart);

// Save Excel 2007 file
//echo date('H:i:s') , " Write to Excel2007 format" , EOL;
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->setIncludeCharts(TRUE);
//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="VentasXPlan.xlsx"');
//header('Cache-Control: max-age=0');

//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');


?>