<?php
//ini_set('display_errors', 'On');
error_reporting(E_ALL);
session_start();
include('../db.php');
include('../funciones.php');
require('../lib/fpdf/fpdf.php');

class PDF extends FPDF
{

}

// Creación del objeto de la clase heredada
$pdf = new PDF('L','mm','A4');
$pdf->AddPage();
$_SESSION['empresa'] = 1;

    if(isset($_GET['nomina']))
        $id = $_GET['nomina'];
    else
        $id = $_GET['id'];

   include_once("../modulos/config.php");
    $empresa = new modelConfig();
    $empresa->select($_SESSION['empresa']);

    include_once("../nomina/nomina.php");
    $nomina = new modelnomina();
    $nomina->selectnomina($id);


    $emp = $_SESSION['empresa'];

    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(265,10,busca($emp,'configuracion','c_consecutivo','c_nmb'),0,0,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Ln();
    $pdf->Cell(265,5,date('d/m/y',strtotime($nomina->fini)));
    $pdf->Ln();
    $pdf->Cell(265,5,"RFC: ".$empresa->rfc);
    $pdf->Ln();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(265,10,utf8_decode("Concentrado de nómina"),0,0,"C");
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln();
    $pdf->Cell(265,5,"Nomina: ".$nomina->nnom);
    $pdf->Ln();
    $pdf->Cell(265,5,"Periodo de pago: ".date('d-m-Y',strtotime($nomina->fini)).' al '.date('d-m-Y',strtotime($nomina->ffin)));
    $pdf->Ln();

    $pdf->SetFillColor(121, 121, 255);
    $pdf->SetDrawColor(0,0,0);    // Arial bold 15
   /**************************************************************************/
/*
  include_once("modulos/empleados.php");
    $empleadop = new modelempleados();
    $empleadop->select($empleado);

*/

    $pdf->Ln();
    $sql = 'SELECT DISTINCT(d_clave),d_concepto,d_tipo FROM nominad
            WHERE d_nomina = "'.$id.'" AND d_tipo IN ("P","N") AND (d_importe > 0 OR d_exento > 0)
            AND d_empleado IN (SELECT empleado FROM nom_emp WHERE nomina = "'.$id.'")';
    $resultp = setq($sql) or die($sql);
    $resultpn = setq($sql) or die($sql);
    $totexento = 0;
    $totp = 0;
    $totop = 0;
    if($resultpn->num_rows > 0){
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(265,8,'PERCEPCIONES',0,0,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        $totp = 0;
        $totexento = 0;
        while($row = $resultp->fetch_array()){
          $exentop = busca($row['d_clave'],'nominad','d_tipo IN ("P","N") AND d_empleado IN (SELECT empleado FROM nom_emp WHERE nomina = "'.$id.'") AND d_nomina = "'.$id.'" AND d_clave','SUM(d_exento)');
          $percepciones = busca($row['d_clave'],'nominad','d_tipo IN ("P","N") AND d_empleado IN (SELECT empleado FROM nom_emp WHERE nomina = "'.$id.'") AND d_nomina = "'.$id.'" AND d_clave','SUM(d_importe)');

          if($row['d_tipo'] != "H"){
            $pdf->Cell(30);
            $pdf->Cell(45,5,$row['d_clave']);
            $pdf->Cell(150,5,$row['d_concepto']);
            $pdf->Cell(25,5,"$ ".number_format($percepciones+$exentop,2),0,0,'R');
            $pdf->Ln();
          }

            $totexento+=$exentop;
            $totp+=$percepciones;
        }

            $pdf->Cell(75);
            $pdf->Cell(150,5,"Total Gravado de percepciones ",0,0,"R");
            $pdf->Cell(25,5,"$ ".number_format($totp,2),0,0,'R');
            $pdf->Ln();
            $pdf->Cell(75);
            $pdf->Cell(150,5,"Total Exento de percepciones ",0,0,"R");
            $pdf->Cell(25,5,"$ ".number_format($totexento,2),0,0,'R');
            $pdf->Ln();
            $pdf->Cell(75);
            $pdf->Cell(150,5,"Total de percepciones ",0,0,"R");
            $pdf->Cell(25,5,"$ ".number_format($totp+$totexento,2),0,0,'R');
            $pdf->Ln();
    }

    $pdf->Ln();
    $sql = 'SELECT DISTINCT(d_clave),d_concepto,d_tipo FROM nominad
            WHERE d_nomina = "'.$id.'" AND d_tipo IN ("O") AND d_importe > 0
            AND d_empleado IN (SELECT empleado FROM nom_emp WHERE nomina = "'.$id.'")';
    $resultp = setq($sql) or die($sql);
    $resultpn = setq($sql) or die($sql);
    if($resultpn->num_rows > 0){
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(265,8,'OTROS PAGOS',0,0,'C');
        $pdf->Ln();
        $totop=0;
        $pdf->SetFont('Arial','',8);
        while($row = $resultp->fetch_array()){
          $exentop = busca($row['d_clave'],'nominad','d_tipo IN ("O") AND d_empleado IN (SELECT empleado FROM nom_emp WHERE nomina = "'.$id.'")  AND d_nomina = "'.$id.'" AND d_clave','SUM(d_exento)');
          $percepciones = busca($row['d_clave'],'nominad','d_tipo IN ("O") AND d_empleado IN (SELECT empleado FROM nom_emp WHERE nomina = "'.$id.'")  AND d_nomina = "'.$id.'" AND d_clave','SUM(d_importe)');

            $pdf->Cell(30);
            $pdf->Cell(45,5,$row['d_clave']);
            $pdf->Cell(150,5,$row['d_concepto']);
            $pdf->Cell(25,5,"$ ".number_format($percepciones,2),0,0,'R');
            $pdf->Ln();
            $totop+=$percepciones;
        }

            $pdf->Cell(75);
            $pdf->Cell(150,5,"Total Gravado de Otros Pagos ",0,0,"R");
            $pdf->Cell(25,5,"$ ".number_format($totop,2),0,0,'R');
            $pdf->Ln();
    }

    $pdf->Ln();
    $sql = 'SELECT DISTINCT(d_clave),d_concepto FROM nominad
            WHERE d_nomina = "'.$id.'" AND d_tipo IN ("D","I") AND d_importe > 0
            AND d_empleado IN (SELECT empleado FROM nom_emp WHERE nomina = "'.$id.'")';
    $resultd = setq($sql) or die($sql);
    $resultdn = setq($sql) or die($sql);
    $totd = 0;
    if($resultdn->num_rows > 0){
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(265,8,'DEDUCCIONES',0,0,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
        while($row = $resultd->fetch_array()){
            $deducciones = busca($row['d_clave'],'nominad','d_tipo IN ("D","I") AND d_empleado IN (SELECT empleado FROM nom_emp WHERE nomina = "'.$id.'")  AND d_nomina = "'.$id.'" AND d_clave','SUM(d_importe)');
            $pdf->Cell(30);
            $pdf->Cell(45,5,$row['d_clave']);
            $pdf->Cell(150,5,$row['d_concepto']);
            $pdf->Cell(25,5,"$ ".number_format(busca($row['d_clave'],'nominad','d_tipo IN ("D","I") AND d_nomina = "'.$id.'" AND d_clave','SUM(d_importe)'),2),0,0,'R');
            $pdf->Ln();
            $totd+=$deducciones;
        }
            $pdf->Cell(75);
            $pdf->Cell(150,5,"Total de deducciones ",0,0,"R");
            $pdf->Cell(25,5,"$ ".number_format($totd,2),0,0,'R');
            $pdf->Ln();
    }

    $total = $totp+$totexento-$totd+$totop;
    $subs = busca($id,'nominad','d_tipo = "O" AND d_tipodp = "2" AND d_nomina','SUM(d_importe)');
    $exento = busca($id,'nominad','d_tipo IN ("N","P","H") AND d_nomina','SUM(d_exento)');
    $gravable = $totp;

          $pdf->Cell(265,8,'TOTALES',0,0,'C');
          $pdf->Ln();
          $pdf->Cell(70);
/*
          $pdf->Cell(40,5,'TOTAL EN EFECTIVO',0,0,'C');
          $pdf->Cell(40,5,"$ ".number_format($total,2),0,0,'R');
*/
          $pdf->Ln();
          $pdf->Cell(70);
          $pdf->Cell(40,5,'NETO PAGADO',0,0,'C');
          $pdf->Cell(40,5,"$ ".number_format($total,2),0,0,'R');
//          $pdf->Cell(40,5,"$ ".number_format($totp+$totd,2),0,0,'R');
          $pdf->Ln();
          $pdf->Cell(70);
          $pdf->Cell(40,5,'Total Gravable',0,0,'C');
          $pdf->Cell(40,5,"$ ".number_format($gravable,2),0,0,'R');
          $pdf->Ln();
          $pdf->Cell(70);
          $pdf->Cell(40,5,'Total Exento',0,0,'C');
          $pdf->Cell(40,5,"$ ".number_format($totexento,2),0,0,'R');
          $pdf->Ln();
          $pdf->Cell(70);
          $pdf->Cell(40,5,'Subs. Empleo',0,0,'C');
          $pdf->Cell(40,5,"$ ".number_format($subs,2),0,0,'R');

   /**************************************************************************/
    $sqli = 'SELECT DISTINCT(d_empleado) empleado
             FROM nominad INNER JOIN empleados ON e_id = d_empleado
             WHERE d_nomina = "'.$id.'" ORDER BY e_nempleado';
    $resulti = setq($sqli) or die($sqli);

    $p=0;

    $pdf->AddPage();
    while($rowi = $resulti->fetch_array()){

    $empleado = $rowi['empleado'];
    $nomina = $_GET['id'];

    include_once("../modulos/config.php");
    $empresa = new modelConfig();
    $empresa->select($_SESSION['empresa']);

    include_once("../modulos/empleados.php");
    $nom = new modelempleados();
    $nom->select($empleado);

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"No. Empleado: ",1,0,"L");
   $pdf->Cell(50,4,"Puesto: ",1,0,"L");
   $pdf->Cell(60,4,"Nombre: ",1,0,"L");
   $pdf->Cell(35,4,"Percepciones: ",1,0,"L");
   $pdf->Cell(20,4,"Gravadas: ",1,0,"L");
   $pdf->Cell(20,4,"Exentas: ",1,0,"L");
   $pdf->Cell(35,4,"Deducciones: ",1,0,"L");
   $pdf->Cell(20,4,"Gravadas: ",1,0,"L");
   $pdf->Cell(20,4,"Neto: ",1,0,"L");
   $pdf->Ln();
   $pdf->Cell(20,4,$nom->nempleado,0,0,"L");
   $pdf->Cell(50,4,utf8_decode(substr($nom->puesto,0,30)),0,0,"L");
   $pdf->Cell(60,4,utf8_decode($nom->nmb),0,0,"L");


   $totalper=0;
   $totalded=0;

   $sqlp = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
            AND d_tipo IN ("N","P","H","O") ORDER BY d_tipo ASC,d_id DESC';
   $resultpn = setq($sqlp) or die($sqlp);
   list($maxp) = $resultpn->fetch_array();

   $sqld = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
            AND d_tipo IN ("D","I") ORDER BY d_tipo ASC,d_id DESC';
   $resultdn = setq($sqld) or die($sqld);
   list($maxd) = $resultdn->fetch_array();
   $maxlim = max($maxp,$maxd);

   for($lim=0;$lim<=$maxlim;$lim++){

     if($lim>0) $pdf->Ln().$pdf->Cell(130);

     $sqlp = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
              AND d_tipo IN ("N","P","H","O") ORDER BY d_tipo ASC,d_id DESC LIMIT '.$lim.',1';
     $resultp = setq($sqlp) or die($sqlp);

     while($rowp = $resultp->fetch_array()){
       $totalper+=$rowp['d_importe'];

          $pdf->SetFont('Arial','',6);
          $pdf->Cell(35,4,substr($rowp['d_concepto'],0,27),0,0,"L");
          $pdf->SetFont('Arial','',6);
          $pdf->Cell(20,4,number_format($rowp['d_importe'],2),0,0,"R");
          $pdf->Cell(20,4,number_format($rowp['d_exento'],2),0,0,"R");

     }

     $sqld = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
              AND d_tipo IN ("D","I") ORDER BY d_tipo ASC,d_id DESC LIMIT '.$lim.',1';
     $resultd = setq($sqld) or die($sqld);

        while($rowd = $resultd->fetch_array()){
            $totalded+=$rowd['d_importe'];

          $pdf->SetFont('Arial','',6);
          $pdf->Cell(35,4,substr($rowd['d_concepto'],0,27),0,0,"L");
          $pdf->SetFont('Arial','',6);
          $pdf->Cell(20,4,number_format($rowd['d_importe'],2),0,0,"R");
     }

   }
   $totalneto = $totalper-$totalded;

   $pdf->Cell(130).$pdf->Cell(20,4,number_format($totalneto,2),0,0,"R");

   $pdf->Ln(8);

}
    $pdf->Output();
   //    $pdf->Output('cfd/'.$_SESSION['empresa'].'/pdf/nomina/'.busca($empleadop->id,'nom_emp','nomina = "'.$id.'" AND empleado','folio').'.pdf');

$pdf->Close();
?>