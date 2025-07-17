<?php
ini_set('display_errors', 1);
session_start();
include('../db.php');
include('../funciones.php');
require('../lib/fpdf/fpdf.php');
require('../lib/QR/phpqrcode.php');
$_SESSION['empresa'] = 1;

class PDF extends FPDF
{

}

// Creación del objeto de la clase heredada
$pdf = new PDF('L','mm','A4');
$pdf->AddPage();

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
    $pdf->Cell(265,10,utf8_decode(busca($emp,'empresas','e_id','e_nmb')),0,0,'C');
    $pdf->SetFont('Arial','',10);
    $pdf->Ln();
    $pdf->Cell(265,4,date('d/m/y',strtotime($nomina->fcreo)));
    $pdf->Ln();
    $pdf->Cell(265,4,"RFC: ".$empresa->rfc);
    $pdf->Ln();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(265,10,utf8_decode("Concentrado de nómina"),0,0,"C");
    $pdf->SetFont('Arial','B',10);
    $pdf->Ln();
    $pdf->Cell(265,4,"Nomina: ".$nomina->nnom);
    $pdf->Ln();
    $pdf->Cell(265,4,"Periodo de pago: ".date('d-m-Y',strtotime($nomina->fini)).' al '.date('d-m-Y',strtotime($nomina->ffin)));
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
            $pdf->Cell(45,4,$row['d_clave']);
            $pdf->Cell(150,4,$row['d_concepto']);
            $pdf->Cell(25,4,"$ ".number_format($percepciones+$exentop,2),0,0,'R');
            $pdf->Ln();
          }

            $totexento+=$exentop;
            $totp+=$percepciones;
        }

            $pdf->Ln(1);
            $pdf->Cell(75);
            $pdf->Cell(150,4,"Total Gravado de percepciones ",0,0,"R");
            $pdf->Cell(25,4,"$ ".number_format($totp,2),0,0,'R');
            $pdf->Ln();
            $pdf->Cell(75);
            $pdf->Cell(150,4,"Total Exento de percepciones ",0,0,"R");
            $pdf->Cell(25,4,"$ ".number_format($totexento,2),0,0,'R');
            $pdf->Ln();
            $pdf->Cell(75);
            $pdf->Cell(150,4,"Total de percepciones ",0,0,"R");
            $pdf->Cell(25,4,"$ ".number_format($totp+$totexento,2),0,0,'R');
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
            $pdf->Cell(45,4,$row['d_clave']);
            $pdf->Cell(150,4,$row['d_concepto']);
            $pdf->Cell(25,4,"$ ".number_format($percepciones,2),0,0,'R');
            $pdf->Ln();
            $totop+=$percepciones;
        }

            $pdf->Ln(1);
            $pdf->Cell(75);
            $pdf->Cell(150,4,"Total Gravado de Otros Pagos ",0,0,"R");
            $pdf->Cell(25,4,"$ ".number_format($totop,2),0,0,'R');
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
            $pdf->Cell(45,4,$row['d_clave']);
            $pdf->Cell(150,4,$row['d_concepto']);
            $pdf->Cell(25,4,"$ ".number_format(busca($row['d_clave'],'nominad','d_tipo IN ("D","I") AND d_nomina = "'.$id.'" AND d_clave','SUM(d_importe)'),2),0,0,'R');
            $pdf->Ln();
            $totd+=$deducciones;
        }
            $pdf->Ln(1);
            $pdf->Cell(75);
            $pdf->Cell(150,4,"Total de deducciones ",0,0,"R");
            $pdf->Cell(25,4,"$ ".number_format($totd,2),0,0,'R');
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
          $pdf->Cell(40,4,'TOTAL EN EFECTIVO',0,0,'C');
          $pdf->Cell(40,4,"$ ".number_format($total,2),0,0,'R');
*/
          $pdf->Ln();
          $pdf->Cell(70);
          $pdf->Cell(40,4,'NETO PAGADO',0,0,'C');
          $pdf->Cell(40,4,"$ ".number_format($total,2),0,0,'R');
//          $pdf->Cell(40,4,"$ ".number_format($totp+$totd,2),0,0,'R');
          $pdf->Ln();
          $pdf->Cell(70);
          $pdf->Cell(40,4,'Total Gravable',0,0,'C');
          $pdf->Cell(40,4,"$ ".number_format($gravable,2),0,0,'R');
          $pdf->Ln();
          $pdf->Cell(70);
          $pdf->Cell(40,4,'Total Exento',0,0,'C');
          $pdf->Cell(40,4,"$ ".number_format($totexento,2),0,0,'R');
          $pdf->Ln();
          $pdf->Cell(70);
          $pdf->Cell(40,4,'Subs. Empleo',0,0,'C');
          $pdf->Cell(40,4,"$ ".number_format($subs,2),0,0,'R');

   /**************************************************************************/
    $sqli = 'SELECT DISTINCT(d_empleado) empleado
             FROM nominad INNER JOIN nom_emp ON d_nomina = nomina AND d_empleado = empleado
             INNER JOIN empleados ON e_id = d_empleado
             WHERE d_nomina = "'.$id.'" ORDER BY e_nempleado';
    $resulti = setq($sqli) or die($sqli);

    $p=0;

    while($rowi = $resulti->fetch_array()){
    $pdf->AddPage('P','A4');

    $empleado = $rowi['empleado'];
    $nomina = $_GET['id'];

   include_once("../modulos/config.php");
    $empresa = new modelConfig();
    $empresa->select($_SESSION['empresa']);

    include_once("../nomina/nomina.php");
    $nom = new modelnomina();
    $nom->selectnominat($empleado,$nomina);

//    $img = 'images/logo.jpg';
    $imgfondo = NULL;
    $pdf->SetFillColor(121, 121, 255);
    $pdf->SetDrawColor(121, 121, 255);    // Arial bold 15

    if($img != NULL)
        $pdf->Image($img,4,8,40,20);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial','',10);

   $pdf->Cell(10);
   $pdf->Cell(115,4,$empresa->nmb,0,0,"C");

   $pdf->SetFont('Arial','',10);
   $pdf->SetTextColor(255,255,255);
   $pdf->Cell(65,4,utf8_decode("Recibo de Nómina"),1,0,"C",1);

   $pdf->Ln();
   $pdf->SetTextColor(0, 0, 0);
   $pdf->Cell(10);
   $pdf->Cell(115,4,"R.F.C. ".$empresa->rfc,0,0,"C");

   $pdf->SetFont('Arial','',8);
   $pdf->Cell(25,4,"Serie y Folio","LR",0,"L");
   $pdf->SetTextColor(0, 0, 0);
   $pdf->Cell(40,4,$nom->folio,"LR",0,"R");

   $pdf->Ln();
   $pdf->Cell(10);
   $pdf->Cell(115,4,$empresa->calle.' '.$empresa->nume.' '.$empresa->numi.' COL. '.$empresa->colonia,0,0,"C");
   $pdf->SetFont('Arial','',8);
   $pdf->Cell(25,4,utf8_decode("Fecha de emisión"),"LR",0,"L");
   $pdf->SetFont('Arial','',8);
   $pdf->SetTextColor(0, 0, 0);
   $pdf->Cell(40,4,str_replace(" ","T",date('Y-m-d H:i:s',strtotime($nom->fechatim))),"LR",0,"R");

   $pdf->Ln();
   $pdf->Cell(10);
   $pdf->Cell(115,4,$empresa->municipio.' '.$empresa->estado,0,0,"C");
   $pdf->SetFont('Arial','',8);
   $pdf->Cell(25,4,utf8_decode("Certificado CSD"),"LR",0,"L");
   $pdf->SetFont('Arial','',8);
   $pdf->SetTextColor(0, 0, 0);
   $pdf->Cell(40,4,"00001000000400858260","LR",0,"R");

   $sellocfd =  sacarcadena('Sello="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $foliosat =  sacarcadena('SelloSAT="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $uuid =  sacarcadena('UUID="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $certisat =  sacarcadena('NoCertificadoSAT="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $fechatimbrado =  sacarcadena('FechaTimbrado="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $salarioct =  sacarcadena('SalarioBaseCotApor="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $salariodi =  sacarcadena('SalarioDiarioIntegrado="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));

   $pdf->Ln();
   $pdf->Cell(10);
   $pdf->Cell(115,4,"C.P. ".$empresa->cp.' Tel. '.$empresa->telefono,0,0,"C");
   $pdf->SetFont('Arial','',8);
   $pdf->Cell(25,4,utf8_decode("Certificado SAT"),"LR",0,"L");
   $pdf->SetFont('Arial','',8);
   $pdf->SetTextColor(0, 0, 0);
   $pdf->Cell(40,4,$certisat,"LR",0,"R");

   $pdf->Ln();
   $pdf->Cell(10);
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(115,4,utf8_decode("Régimen Físcal"),0,0,"L");
   $pdf->SetFont('Arial','',8);
   $pdf->Cell(25,4,utf8_decode("Folio SAT"),"LR",0,"L");
   $pdf->SetTextColor(0, 0, 0);
   $pdf->Cell(40,4,substr($uuid,0,23),"LR",0,"R");

   $pdf->Ln();
   $pdf->Cell(10);
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(115,4,utf8_decode(busca($empresa->regimene,'cfdi_regimenfiscal','cr_id','cr_nmb')),0,0,"C");
   $pdf->SetFont('Arial','',8);
   $pdf->Cell(25,4,"","LR",0,"L");
   $pdf->SetTextColor(0, 0, 0);
   $pdf->Cell(40,4,substr($uuid,23,23),"LR",0,"R");

   $pdf->Ln();
   $pdf->Cell(125);
   $pdf->SetFont('Arial','',8);
   $pdf->Cell(25,4,utf8_decode("Certificación"),"LBR",0,"L");
   $pdf->SetTextColor(0, 0, 0);
   $pdf->Cell(40,4,$fechatimbrado,"LBR",0,"R");
   $pdf->Ln(10);

   $pdf->SetFont('Arial','',10);
   $pdf->Cell(190,6,"Empleado","TLR",1);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"No. EMPLEADO: ","L",0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,$nom->nempleado,0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"DEPARTAMENTO: ",0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode($nom->departamento),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("INICIO DE RELACIÓN LABORAL: "),0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,$nom->fecha,"R",0,"R");
   $pdf->Ln();

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"NOMBRE: ","L",0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode(substr($nom->nmb,0,25)),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"PUESTO: ",0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode(substr($nom->puesto,0,25)),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("PERIODO DE PAGO: "),0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,busca($nom->tpagos,'nomina_periodicidad','np_clave','np_nmb'),"R",0,"R");
   $pdf->Ln();

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"","L",0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode(substr($nom->nmb,25,25)),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4," ",0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode(substr($nom->puesto,25,25)),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("SALARIO BASE COT. APOR.: "),0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,"$ ".number_format($salarioct,2),"R",0,"R");
   $pdf->Ln();

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"RFC","L",0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode($nom->rfc),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"RIESGO DEL PUESTO",0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode($nom->regpuesto),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("SALARIO DIARIO INTEGRADO: "),0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,"$ ".number_format($salariodi,2),"R",0,"R");
   $pdf->Ln();

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"CURP","L",0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode($nom->curp),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("FECHA DE PAGO: "),0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,date('Y-m-d',strtotime($nom->fechatim)),"R",0,0);
   $pdf->Ln();

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"No. SEGURIDAD SOCIAL","L",0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode($nom->nss),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"CLABE ",0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode(substr($nom->banco,25,25)),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("FECHA INICIAL DE PAGO: "),0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,date('Y-m-d',strtotime($nom->fini)),"R",0,"R");
   $pdf->Ln();

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"REGISTRO PATRONAL","L",0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode($empresa->registrop),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"TIPO DE JORNADA ",0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,utf8_decode(busca($nom->jornada,'nomina_jornada','nj_clave','nj_nmb')),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("FECHA FINAL DE PAGO: "),0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,date('Y-m-d',strtotime($nom->ffin)),"R",0,"R");
   $pdf->Ln();

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("TIPO DE RÉGIMEN"),"L",0,"L");
   $pdf->SetFont('Arial',"",7);
   $pdf->Cell(40,4,utf8_decode(substr(busca($nom->regimen,'nomina_regimen','nr_clave','nr_nmb'),0,18)),0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,"ANTIGUEDAD ",0,0,"L");
   $pdf->SetFont('Arial','',7);

   $finir = date('Y-m-d',strtotime($nom->fechacontratot));
   $ffinr = date('Y-m-d',strtotime($nom->ffin));
   $anti	= (strtotime($finir)-strtotime($ffinr))/86400;
   $anti = abs($anti);
   $anti = floor($anti);
   $anti++;
   $antig='P'.floor($anti/7).'W';

   $fini = date('Y-m-d',strtotime($nom->fini));
   $ffin = date('Y-m-d',strtotime($nom->ffin));
   $diaspag	= (strtotime($fini)-strtotime($ffin))/86400;
   $diaspag = abs($diaspag);
   $diaspag = floor($diaspag);
   $diaspag++;

   $pdf->Cell(40,4,$antig,0,0,"R").$pdf->Cell(5,4);
   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("No. DÍAS PAGADOS: "),0,0,"L");
   $pdf->SetFont('Arial','',7);
   $pdf->Cell(40,4,$diaspag,"R",0,"R");
   $pdf->Ln();

   $pdf->SetFont('Arial','B',7);
   $pdf->Cell(20,4,utf8_decode("TIPO DE CONTRATO"),"BL",0,"L");
   $pdf->SetFont('Arial',"",7);
   $pdf->Cell(40,4,utf8_decode(substr($nom->contrato,0,20)),"B",0,"R").$pdf->Cell(130,4,"",'BR');
   $pdf->Ln(10);

$pdf->SetFont('Arial','B',9);
    $pdf->SetFillColor(121, 121, 255);
    $pdf->SetDrawColor(121, 121, 255);    // Arial bold 15
    $pdf->SetTextColor(255,255,255);

    $pdf->Cell(94,4,"Percepciones",1,0,"C",true);
    $pdf->Cell(3,4);
    $pdf->Cell(94,4,"Deducciones",1,0,"C",true);
    $pdf->Ln();

    $pdf->SetFont('Arial','B',6);
    $pdf->Cell(13,4,"Tipo",1,0,"C",true);
    $pdf->Cell(12,4,"Clave",1,0,"C",true);
    $pdf->Cell(45,4,"Concepto",1,0,"C",true);
    $pdf->Cell(12,4,"Importe",1,0,"C",true);
    $pdf->Cell(12,4,"Importe",1,0,"C",true);
    $pdf->Cell(3,4);
    $pdf->Cell(13,4,"Tipo",1,0,"C",true);
    $pdf->Cell(12,4,"Clave",1,0,"C",true);
    $pdf->Cell(45,4,"Concepto",1,0,"C",true);
    $pdf->Cell(24,4,"Importe",1,0,"C",true);

    $pdf->Ln(3);
    $pdf->Cell(13,4,"Percepcion",1,0,"C",true);
    $pdf->Cell(12,4,"",1,0,"C",true);
    $pdf->Cell(45,4,"",1,0,"C",true);
    $pdf->Cell(12,4,"Exento",1,0,"C",true);
    $pdf->Cell(12,4,"Gravable",1,0,"C",true);
    $pdf->Cell(3,4);
    $pdf->Cell(13,4,utf8_decode("Deducción"),1,0,"C",true);
    $pdf->Cell(12,4,"",1,0,"C",true);
    $pdf->Cell(45,4,"",1,0,"C",true);
    $pdf->Cell(24,4,"",1,0,"C",true);

    $sqlp = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
            AND d_tipo IN ("N","P","H","O") ORDER BY d_tipo ASC,d_id DESC';
    $resultp = setq($sqlp) or die($sqlp);
    $resultpn = setq($sqlp) or die($sqlp);
    $numper = $resultpn->num_rows;

    $sqld = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
            AND d_tipo IN ("D","I") ORDER BY d_tipo ASC,d_id DESC';
    $resultd = setq($sqld) or die($sqld);
    $resultdn = setq($sqld) or die($sqld);
    $numded = $resultdn->num_rows;

    $pdf->SetTextColor(0,0,0);

    $yp = 107;
    $pdf->SetY($yp);

    $exentop=0;
    $gravadop=0;
     while($rowp = $resultp->fetch_array()){
        $pdf->SetFont('Arial','B',6);
        if($rowp['d_tipo'] == "O")
            $exentop+=$rowp['d_importe'];
        else{
            $exentop+=$rowp['d_exento'];
            $gravadop+=$rowp['d_importe'];
        }

        $pdf->Cell(13,3,busca($rowp['d_tipodp'],'nomina_percepciones','np_id','np_clave'), "LR",0,"C");
        $pdf->Cell(12,3,$rowp['d_clave'], "LR",0,"C");
        $pdf->SetFont('Arial','B',5);

        $pdf->Cell(45,3,substr(utf8_decode($rowp['d_concepto']),0,39), "LR",0,"L");
        if($rowp['d_tipo'] != "O"){
            $pdf->Cell(12,3,"$ ".number_format($rowp['d_exento'],2), "LR",0,"C");
            $pdf->Cell(12,3,"$ ".number_format($rowp['d_importe'],2), "LR",0,"C");
        }
        else{
            $pdf->Cell(12,3,"$ ".number_format($rowp['d_importe'],2), "LR",0,"C");
            $pdf->Cell(12,3,"$ ".number_format(0,2), "LR",0,"C");

        }

        if(strlen($rowp['d_concepto']) > 39){
          $numper++;
        $yp+=3;
        $pdf->SetY($yp);
          $pdf->Cell(13,3,"", "LR",0,"C");
          $pdf->Cell(12,3,"", "LR",0,"C");
          $pdf->SetFont('Arial','B',5);
          $pdf->Cell(45,3,substr(utf8_decode($rowp['d_concepto']),39,39), "LR",0,"L");
          $pdf->Cell(12,3,"", "LR",0,"C");
          $pdf->Cell(12,3,"", "LR",0,"C");
        }
        $yp+=3;
        $pdf->SetY($yp);
     }

    $yd = 107;
    $pdf->SetXY(107,$yd);

    $exentod=0;
    $gravadod=0;
     while($rowd = $resultd->fetch_array()){
            $pdf->SetFont('Arial','B',6);
     $gravadod+=$rowd['d_importe'];

        $pdf->Cell(13,3,busca($rowd['d_tipodp'],'nomina_deducciones','nd_id','nd_clave'), "LR",0,"C");
        $pdf->Cell(12,3,$rowd['d_clave'], "LR",0,"C");
        $pdf->SetFont('Arial','B',5);
        $pdf->Cell(45,3,substr(utf8_decode($rowd['d_concepto']),0,39), "LR",0,"L");
        $pdf->Cell(24,3,"$ ".number_format($rowd['d_importe'],2), "LR",0,"C");

        if(strlen($rowp['d_concepto']) > 39){
        $yd+=3;
        $pdf->SetXY(107,$yd);
          $pdf->Cell(13,3,"", "LR",0,"C");
          $pdf->Cell(12,3,"", "LR",0,"C");
          $pdf->SetFont('Arial','B',5);
          $pdf->Cell(45,3,substr(utf8_decode($rowd['d_concepto']),39,39), "LR",0,"L");
          $pdf->Cell(12,3,"", "LR",0,"C");
          $pdf->Cell(12,3,"", "LR",0,"C");
        }
        $yd+=3;
        $pdf->SetXY(107,$yd);

     }

     $pdf->SetY($yp);
          $completap=(7-$numper)*3;
          $pdf->Cell(13,$completap,"", "LRB",0,"C");
          $pdf->Cell(12,$completap,"", "LRB",0,"C");
          $pdf->SetFont('Arial','B',5);
          $pdf->Cell(45,$completap,substr(utf8_decode($rowp['d_concepto']),39,39), "LRB",0,"L");
          $pdf->Cell(12,$completap,"", "LRB",0,"C");
          $pdf->Cell(12,$completap,"", "LRB",0,"C");
          $yp=$yp+$completap;
     $pdf->SetY($yp);
     $pdf->Cell(70,3,"SUMA PERCEPCIONES",1,0,"C");
     $pdf->Cell(12,3,"$ ".number_format($exentop,2),1,0,"C");
     $pdf->Cell(12,3,"$ ".number_format($gravadop,2),1,0,"C");

          $pdf->Cell(3);

          $completad=(7-$numded)*3;
          $pdf->SetXY(107,$yd);
          $pdf->Cell(13,$completad,"", "LRB",0,"C");
          $pdf->Cell(12,$completad,"", "LRB",0,"C");
          $pdf->SetFont('Arial','B',5);
          $pdf->Cell(45,$completad,substr(utf8_decode($rowp['d_concepto']),39,39), "LRB",0,"L");
          $pdf->Cell(24,$completad,"", "LRB",0,"C");
          $yd=$yd+$completad;
     $pdf->SetXY(107,$yd);
     $pdf->Cell(70,3,"SUMA DEDUCCIONES",1,0,"C");
     $pdf->Cell(24,3,"$ ".number_format($gravadod,2),1,0,"C");

     $totper = $gravadop+$exentop;
     $totded = $gravadod;
     $total = $totper-$totded;
     $decimales = explode(".",number_format($total,2,'.',''));

    $pdf->Ln(3);
    $sqlp = 'SELECT d_importe,d_exento FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
             AND d_tipo IN ("O") AND d_clave = "002" ORDER BY d_tipo ASC,d_id DESC';
    $resultp = setq($sqlp) or die($sqlp);
    list($pagado,$causado) = $resultp->fetch_array();
    if($causado != 0){
     $pdf->Cell(70,3,"SUBSIDIO CAUSADO",1,0,"C");
     $pdf->Cell(12,3,"$ ".number_format($causado,2), 1,0,"R");
    }

    $pdf->SetTextColor(255,255,255);
     $pdf->Ln(10);
     $pdf->Cell(120,4,"Importe con letra",1,0,"L",true);
     $pdf->Cell(5);
     $pdf->Cell(30,4,"Total de percepciones",1,0,"L",true);
     $pdf->SetTextColor(0,0,0);
     $pdf->Cell(35,4,"$ ".number_format($totper,2),1,0,"R");
     $pdf->SetTextColor(255,255,255);
     $pdf->Ln();
     $pdf->Cell(120,2,"","LR",0,"L");
     $pdf->Cell(5);
     $pdf->Cell(30,2);
     $pdf->Cell(35,2);
     $pdf->Ln();
     $pdf->SetTextColor(0,0,0);
     $pdf->Cell(120,4,strtoupper(num2letras($total).' PESOS '.$decimales[1].'/100 M.N.'),"LR",0,"C");
     $pdf->SetTextColor(255,255,255);
     $pdf->Cell(5);
     $pdf->Cell(30,4,"Total de Deducciones",1,0,"L",true);
     $pdf->SetTextColor(0,0,0);
     $pdf->Cell(35,4,"$ ".number_format($totded,2),1,0,"R");
     $pdf->SetTextColor(255,255,255);
     $pdf->Ln();
     $pdf->Cell(120,2,"","LR",0,"L");
     $pdf->Cell(5);
     $pdf->Cell(30,2);
     $pdf->Cell(35,2);
     $pdf->Ln();
     $pdf->SetTextColor(0,0,0);
     $pdf->Cell(120,4,"","BLR",0,"C");
     $pdf->SetTextColor(255,255,255);
     $pdf->Cell(5);
     $pdf->Cell(30,4,"Neto a Pagar",1,0,"L",true);
     $pdf->SetTextColor(0,0,0);
     $pdf->Cell(35,4,"$ ".number_format($total,2),1,0,"R");
     $pdf->Ln(10);

   $sellocfd =  sacarcadena('Sello="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $foliosat =  sacarcadena('SelloSAT="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $uuid =  sacarcadena('UUID="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $certisat =  sacarcadena('NoCertificadoSAT="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $fechatimbrado =  sacarcadena('FechaTimbrado="','"',file_get_contents('../cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));

    if (!is_dir('cfd/qr/nomina')) {
        @mkdir('cfd/qr/nomina', 0777);
    }

    $y = $yd+35;
    QRcode::png('?re='.busca("1",'empresas','e_id','e_rfc').'&rr='.busca($empleado,'empleados','e_id','e_rfc').'&tt='.number_format($total,2,'.','').'&id='.$uuid,'../cfd/'.$_SESSION['empresa'].'/qr/nomina'.$nom->folio.'.png');
    $pdf->Image('../cfd/'.$_SESSION['empresa'].'/qr/nomina'.$nom->folio.'.png',145,$y,50,50);


     $pdf->SetFont('Arial','',5);
     $pdf->Cell(150,4,"Sello Digital Del Emisor");
     $pdf->Ln();
     $pdf->Multicell(130,3,$sellocfd);
     $pdf->Ln(7);

     $pdf->Cell(150,4,"Sello del SAT");
     $pdf->Ln();
     $pdf->Multicell(130,3,$foliosat);
     $pdf->Ln(7);

     $cadenaor = '||1.0|'.$uuid.'|'.$fechatimbrado.'|'.$sellocfd.'|'.$certisat.'||';
     $pdf->Cell(150,4,utf8_decode("Cadena Original del complemento de certificación del SAT"));
     $pdf->Ln();
     $pdf->Multicell(130,3,$cadenaor);
     $pdf->Ln(20);

    $pdf->SetFillColor(0,0,0);
    $pdf->SetDrawColor(0,0,0);    // Arial bold 15
    $pdf->Cell(130).$pdf->Cell(60,4,"Firma del Empleado",'T',0,"C");

   $pdf->SetFont('Arial','B',6);
    // Go to 1.5 cm from bottom
   $pdf->SetTextColor(204, 0, 0);
    // Print centered page number
   $pdf->SetY(270);
   $pdf->Cell(190,4,utf8_decode("Este documento es una representación gráfica de un CFDI"),0,0,'C');
   $pdf->SetTextColor(0, 0, 0);


}
    $pdf->Output();
   //    $pdf->Output('cfd/'.$_SESSION['empresa'].'/pdf/nomina/'.busca($empleadop->id,'nom_emp','nomina = "'.$id.'" AND empleado','folio').'.pdf');

$pdf->Close();
?>