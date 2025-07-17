<?php
//ini_set('display_errors',1);
$_SESSION['empresa'] = 1;
ini_set("memory_limit","32M");
session_start();
require('lib/fpdf/fpdf.php');
include 'lib/QR/phpqrcode.php';
//include('funciones.php');
date_default_timezone_set("America/Mexico_City");

class PDF extends FPDF
{
function header(){
    $empleado = $_GET['empleado'];
    $nomina = $_GET['nomina'];

    $emp = 1;
    include_once('modulos/config.php');
    $empresa = new modelconfig();
    $empresa->select($emp);

    include_once("nomina/nomina.php");
    $nom = new modelnomina();
    $nom->selectnominat($empleado,$nomina);

    if(file_exists(''.$empresa->logo)) $img = ''.$empresa->logo;
    else $img = NULL;

    $imgfondo = NULL;
    $this->SetFillColor(121, 121, 255);
    $this->SetDrawColor(121, 121, 255);    // Arial bold 15

    if($img != NULL)
        $this->Image($img,5,8,40,20);

    $this->SetTextColor(0, 0, 0);
    $this->SetFont('Arial','',12);

   $this->Cell(50);
   $this->Cell(75,5,utf8_decode($empresa->nmb),0,0,"C");

   $this->SetFont('Arial','',10);
   $this->SetTextColor(255,255,255);
   $this->Cell(65,5,utf8_decode("Recibo de Nómina"),1,0,"C",1);

   $this->Ln();
   $this->SetTextColor(0, 0, 0);
   $this->Cell(50);
   $this->Cell(75,5,"R.F.C. ".$empresa->rfc,0,0,"C");

   $this->SetFont('Arial','',8);
   $this->Cell(25,5,"Serie y Folio","LR",0,"L");
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,5,$nom->folio,"LR",0,"R");

   $this->Ln();
   $this->Cell(50);
   $this->Cell(75,4,$empresa->calle.' '.$empresa->exterior.' '.$empresa->interior,0,0,"C");
   $this->SetFont('Arial','',8);
   $this->Cell(25,4,utf8_decode("Fecha de emisión"),"LR",0,"L");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,str_replace(" ","T",date('Y-m-d H:i:s',strtotime($nom->fechatim))),"LR",0,"R");

   $this->Ln();
   $this->Cell(50);
   $this->Cell(75,4,' COL. '.$empresa->colonia.' '.$empresa->ciudad.' '.$empresa->estado,0,0,"C");
   $this->SetFont('Arial','',8);
   $this->Cell(25,4,utf8_decode("Certificado CSD"),"LR",0,"L");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,"00001000000400858260","LR",0,"R");

   $_SESSION['empresa'] = 1;
   $sellocfd =  sacarcadena('Sello="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $foliosat =  sacarcadena('SelloSAT="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $uuid =  sacarcadena('UUID="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $certisat =  sacarcadena('NoCertificadoSAT="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $fechatimbrado =  sacarcadena('FechaTimbrado="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $salarioct =  sacarcadena('SalarioBaseCotApor="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $salariodi =  sacarcadena('SalarioDiarioIntegrado="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));

   $this->Ln();
   $this->Cell(50);
   $this->Cell(75,4,"C.P. ".$empresa->cp.' Tel. '.$empresa->telefono,0,0,"C");
   $this->SetFont('Arial','',8);
   $this->Cell(25,4,utf8_decode("Certificado SAT"),"LR",0,"L");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,$certisat,"LR",0,"R");

   $this->Ln();
   $this->Cell(50);
   $this->SetFont('Arial','',7);
   $this->Cell(75,4,utf8_decode("Régimen Físcal"),0,0,"L");
   $this->SetFont('Arial','',8);
   $this->Cell(25,4,utf8_decode("Folio SAT"),"LR",0,"L");
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,substr($uuid,0,23),"LR",0,"R");

   $this->Ln();
   $this->Cell(50);
   $this->SetFont('Arial',"",7);
   $this->Cell(75,4,utf8_decode(busca($empresa->regimen,'cfdi_regimenfiscal','cr_id','cr_nmb')),0,0,"C");
   $this->SetFont('Arial','',8);
   $this->Cell(25,4,"","LR",0,"L");
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,substr($uuid,23,23),"LR",0,"R");

   $this->Ln();
   $this->Cell(125);
   $this->SetFont('Arial','',8);
   $this->Cell(25,4,utf8_decode("Certificación"),"LBR",0,"L");
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,$fechatimbrado,"LBR",0,"R");
   $this->Ln(10);

   $this->SetFont('Arial','',10);
   $this->Cell(190,6,"Empleado","TLR",1);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"No. EMPLEADO: ","L",0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,$nom->nempleado,0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"DEPARTAMENTO: ",0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode($nom->departamento),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("INICIO DE RELACIÓN LABORAL: "),0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,$nom->fechasegsoc,"R",0,"R");
   $this->Ln();

   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"NOMBRE: ","L",0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode(substr($nom->nmb,0,25)),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"PUESTO: ",0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode(substr(busca($nom->puesto,'nomina_puestos','np_id','np_nmb'),0,25)),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("PERIODO DE PAGO: "),0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,busca($nom->tpagos,'nomina_periodicidad','np_clave','np_nmb'),"R",0,"R");
   $this->Ln();

   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"","L",0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode(substr($nom->nmb,25,25)),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4," ",0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode(substr($nom->puesto,25,25)),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("SALARIO BASE COT. APOR.: "),0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,"$ ".number_format(busca($empleado,'empleados_sueldo','es_estatus = "A" AND es_empleado','es_sueldo'),2),"R",0,"R");
   $this->Ln();

   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"RFC","L",0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode($nom->rfc),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"RIESGO DEL PUESTO",0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode($nom->regpuesto),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("SALARIO DIARIO INTEGRADO: "),0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,"$ ".number_format(busca($empleado,'empleados_sueldo','es_estatus = "A" AND es_empleado','es_salariodi'),2),"R",0,"R");
   $this->Ln();

   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"CURP","L",0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode($nom->curp),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("FECHA DE PAGO: "),0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,date('Y-m-d',strtotime($nom->ffin)),"R",0,"R");
   $this->Ln();

   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"No. SEGURIDAD SOCIAL","L",0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode($nom->nss),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"CLABE ",0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode(substr($nom->banco,25,25)),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("FECHA INICIAL DE PAGO: "),0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,date('Y-m-d',strtotime($nom->fini)),"R",0,"R");
   $this->Ln();

   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"REGISTRO PATRONAL","L",0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode($empresa->registrop),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"TIPO DE JORNADA ",0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode(busca($nom->jornada,'nomina_jornada','nj_clave','nj_nmb')),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("FECHA FINAL DE PAGO: "),0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,date('Y-m-d',strtotime($nom->ffin)),"R",0,"R");
   $this->Ln();

   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("TIPO DE RÉGIMEN"),"L",0,"L");
   $this->SetFont('Arial',"",7);
   $this->Cell(40,4,utf8_decode(substr(busca($nom->regimen,'nomina_regimen','nr_clave','nr_nmb'),0,18)),0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,"ANTIGUEDAD ",0,0,"L");
   $this->SetFont('Arial','',7);

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

   $this->Cell(40,4,$antig,0,0,"R").$this->Cell(5,4);
   $this->SetFont('Arial','B',7);
   $this->Cell(20,4,utf8_decode("No. DÍAS PAGADOS: "),0,0,"L");
   $this->SetFont('Arial','',7);
   $this->Cell(40,4,utf8_decode($diaspag.' días'),"R",0,"R");
   $this->Ln();

   $this->SetFont('Arial','B',7);
   $this->Cell(30,4,utf8_decode("TIPO DE CONTRATO"),"BL",0,"L");
   $this->SetFont('Arial',"",7);
   $this->Cell(140,4,utf8_decode(busca($nom->contrato,'nomina_contratos','nc_clave','nc_nmb')),"B",0,"L").$this->Cell(20,4,"",'BR');
   $this->Ln(10);
   }

function Footer()
{
    $this->SetFont('Arial','B',6);
    // Go to 1.5 cm from bottom
    $this->SetTextColor(204, 0, 0);
    // Print centered page number
    $this->SetY(270);
    $this->Cell(190,5,utf8_decode("Este documento es una representación gráfica de un CFDI"),0,0,'C');
    $this->SetTextColor(0, 0, 0);
}
}
    $empleado = $_GET['empleado'];
    $nomina = $_GET['nomina'];

    include_once("nomina/nomina.php");
    $nom = new modelnomina();
    $nom->selectnominat($empleado,$nomina);

    $pdf = new PDF("P","mm", "Letter");
    $pdf->AddPage();
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
    $pdf->Cell(12,4,"Importe",1,0,"C",true);
    $pdf->Cell(12,4,"Importe",1,0,"C",true);

    $pdf->Ln(3);
    $pdf->Cell(13,4,"Percepcion",1,0,"C",true);
    $pdf->Cell(12,4,"",1,0,"C",true);
    $pdf->Cell(45,4,"",1,0,"C",true);
    $pdf->Cell(12,4,"Gravable",1,0,"C",true);
    $pdf->Cell(12,4,"Exento",1,0,"C",true);
    $pdf->Cell(3,4);
    $pdf->Cell(13,4,utf8_decode("Deducción"),1,0,"C",true);
    $pdf->Cell(12,4,"",1,0,"C",true);
    $pdf->Cell(45,4,"",1,0,"C",true);
    $pdf->Cell(12,4,"Gravable",1,0,"C",true);
    $pdf->Cell(12,4,"Exento",1,0,"C",true);

    $sqlp = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
            AND d_tipo IN ("N","P","O") ORDER BY d_tipo ASC,d_id DESC';
    $resultp = setq($sqlp) or die($sqlp);
    $resultpn = setq($sqlp) or die($sqlp);
    $numper = $resultpn->num_rows;

    $sqld = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
            AND d_tipo = "D" ORDER BY d_tipo ASC,d_id DESC';
    $resultd = setq($sqld) or die($sqld);
    $resultdn = setq($sqld) or die($sqld);
    $numded = $resultdn->num_rows;

    $sqlo = 'SELECT * FROM nominad WHERE d_nomina = "'.$nomina.'" AND d_empleado = "'.$empleado.'"
            AND d_tipo IN ("O") ORDER BY d_tipo ASC,d_id DESC';
    $resulto = setq($sqlo) or die($sqlo);
    $resulton = setq($sqlo) or die($sqlo);
    $numotp = $resulton->num_rows;

    $pdf->SetTextColor(0,0,0);

    $yp = 109;
    $pdf->SetY($yp);

    $exentop=0;
    $gravadop=0;
     while($rowp = $resultp->fetch_array()){
            $pdf->SetFont('Arial','B',6);

        $pdf->Cell(13,3,busca($rowp['d_tipodp'],'nomina_percepciones','np_id','np_clave'), "LR",0,"C");
        $pdf->Cell(12,3,$rowp['d_clave'], "LR",0,"C");
        $pdf->SetFont('Arial','B',5);
        $pdf->Cell(45,3,substr(utf8_decode($rowp['d_concepto']),0,39), "LR",0,"L");
        if($rowp['d_tipo'] != "O"){
            $exentop+=$rowp['d_exento'];
            $gravadop+=$rowp['d_importe'];
            $pdf->Cell(12,3,"$ ".number_format($rowp['d_importe'],2), "LR",0,"R");
            $pdf->Cell(12,3,"$ ".number_format($rowp['d_exento'],2), "LR",0,"R");
        }
        else{
            $exentop+=$rowp['d_importe'];
            $pdf->Cell(12,3,"", "LR",0,"R");
            $pdf->Cell(12,3,"$ ".number_format($rowp['d_importe'],2), "LR",0,"R");

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

    $yd = 109;
    $pdf->SetXY(107,$yd);

    $exentod=0;
    $gravadod=0;
     while($rowd = $resultd->fetch_array()){
        $pdf->SetFont('Arial','B',6);
        $exentod+=$rowd['d_exento'];
        $gravadod+=$rowd['d_importe'];

        $pdf->Cell(13,3,busca($rowd['d_tipodp'],'nomina_deducciones','nd_id','nd_clave'), "LR",0,"C");
        $pdf->Cell(12,3,$rowd['d_clave'], "LR",0,"C");
        $pdf->SetFont('Arial','B',5);
        $pdf->Cell(45,3,substr(utf8_decode($rowd['d_concepto']),0,39), "LR",0,"L");
        $pdf->Cell(12,3,"$ ".number_format($rowd['d_importe'],2), "LR",0,"R");
        $pdf->Cell(12,3,"$ ".number_format($rowd['d_exento'],2), "LR",0,"R");

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
     $pdf->Cell(12,3,"$ ".number_format($gravadop,2),1,0,"R");
     $pdf->Cell(12,3,"$ ".number_format($exentop,2),1,0,"R");

          $pdf->Cell(3);

          $completad=(7-$numded)*3;
          $pdf->SetXY(107,$yd);
          $pdf->Cell(13,$completad,"", "LRB",0,"C");
          $pdf->Cell(12,$completad,"", "LRB",0,"C");
          $pdf->SetFont('Arial','B',5);
          $pdf->Cell(45,$completad,substr(utf8_decode($rowp['d_concepto']),39,39), "LRB",0,"L");
          $pdf->Cell(12,$completad,"", "LRB",0,"C");
          $pdf->Cell(12,$completad,"", "LRB",0,"C");
          $yd=$yd+$completad;
     $pdf->SetXY(107,$yd);
     $pdf->Cell(70,3,"SUMA DEDUCCIONES",1,0,"C");
     $pdf->Cell(12,3,"$ ".number_format($gravadod,2),1,0,"R");
     $pdf->Cell(12,3,"$ ".number_format($exentod,2),1,0,"R");

     $totper = $gravadop+$exentop;
     $totded = $gravadod+$exentod;
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

   $sellocfd =  sacarcadena('Sello="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $foliosat =  sacarcadena('SelloSAT="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $uuid =  sacarcadena('UUID="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $certisat =  sacarcadena('NoCertificadoSAT="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $fechatimbrado =  sacarcadena('FechaTimbrado="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $salarioct =  sacarcadena('SalarioBaseCotApor="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));
   $salariodi =  sacarcadena('SalarioDiarioIntegrado="','"',file_get_contents('cfd/'.$_SESSION['empresa'].'/xml/nomina/'.$nom->folio.'.xml'));

    if (!is_dir('cfd/'.$_SESSION['empresa'].'')) {
        @mkdir('cfd/'.$_SESSION['empresa'].'', 0777);
    }
     if (!is_dir('cfd/'.$_SESSION['empresa'].'/qr')) {
        @mkdir('cfd/'.$_SESSION['empresa'].'/qr', 0777);
    }
     if (!is_dir('cfd/'.$_SESSION['empresa'].'/qr/nomina')) {
        @mkdir('cfd/'.$_SESSION['empresa'].'/qr/nomina', 0777);
    }

    $y = $yd+35;
    QRcode::png('?re='.busca("1",'empresas','e_id','e_rfc').'&rr='.busca($empleado,'empleados','e_id','e_rfc').'&tt='.number_format($total,2,'.','').'&id='.$uuid,'cfd/'.$_SESSION['empresa'].'/qr/nomina/'.$nom->folio.'.png');
    $pdf->Image('cfd/'.$_SESSION['empresa'].'/qr/nomina/'.$nom->folio.'.png',145,$y,50,50);


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
    $pdf->Cell(130).$pdf->Cell(60,5,"Firma del Empleado",'T',0,"C");

    if (!is_dir('cfd/'.$_SESSION['empresa'].'/pdf')) {
        @mkdir('cfd/'.$_SESSION['empresa'].'/pdf', 0777);
    }

    if (!is_dir('cfd/'.$_SESSION['empresa'].'/pdf/nomina')) {
        @mkdir('cfd/'.$_SESSION['empresa'].'/pdf/nomina', 0777);
    }

//    $pdf->Output();
    $pdf->Output('cfd/'.$_SESSION['empresa'].'/pdf/nomina/'.$nom->folio.'.pdf');
    $pdf->Close();
?>