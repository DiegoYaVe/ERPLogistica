<?php
//ini_set('display_errors','on');
session_start();

ini_set("memory_limit","64M");
require('../lib/fpdf/fpdf.php');

include '../lib/QR/phpqrcode.php';
//include('funciones.php');

date_default_timezone_set("America/Mexico_City");

class PDF extends FPDF
{
function header(){
$id = $_GET['id'];
$ncredito = $_GET['id'];
$emp = busca($_GET['id'],'ncredito','n_id','n_empresa');
$empres = busca($_GET['id'],'ncredito','n_id','n_empresa');

    include_once('../modulos/config.php');
    $empresa = new modelconfig();
    $empresa->select($emp);

    include_once("../modulos/ncredito.php");
    $facts = new Modelncredito();
    $facts->selectncr($id);

    if(file_exists('../'.$empresa->logo) && $emp->logo != NULL) $img = '../'.$empresa->logo;
    else $img = NULL;


/*
   $sellocfd =  str_pad("X",42,"X");
   $foliosat =  str_pad("X",42,"X");
   $uuid =  $uuid =  "12345678-ABCD-0987-EFGH-A1B2C3D4E5F6";;
   $certisat =  "XXXXXXXXXXXXXXXXX";
   $certicsd =  "XXXXXXXXXXXXXXXXX";
   $fechatimbrado =  date('Y-m-d').'T'.date('H:i:s');
   $fechaemision =  date('Y-m-d').'T'.date('H:i:s');
*/
   $version =  "4.0";

   $sellocfd =  sacarcadena('Sello="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $foliosat =  sacarcadena('SelloSAT="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $uuid =  sacarcadena('Version="1.1" UUID="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $certisat =  sacarcadena('NoCertificadoSAT="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $certicsd =  sacarcadena('NoCertificado="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $fechatimbrado =  sacarcadena('FechaTimbrado="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $fechaemision =  sacarcadena('Fecha="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));


    $imgfondo = NULL;
    if($emp != 354)
      $this->SetFillColor(27, 127, 185);
    else
      $this->SetFillColor(235, 65, 41);

    $this->SetDrawColor(0,0,0);    // Arial bold 15

    if($img != NULL)
        $this->Image($img,10,8,35,20);
    if($facts->estatus == "C")
        $this->Image('images/cancel.gif',30,25,164,164);

   $this->SetTextColor(0, 0, 0);
   $this->SetFont('Arial','',9);

   $this->Cell(40);
   $this->Cell(85,5,utf8_decode(substr($empresa->nmb,0,40)),0,0,"C");

   $this->SetFont('Arial','',10);
   $this->SetTextColor(255,255,255);
   $this->Cell(65,5,"Factura",1,0,"C",1);

   $this->Ln();
   $this->Cell(40);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(85,5,utf8_decode(substr($empresa->razons,0,40)),0,0,"C");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(255,255,255);
   $this->Cell(25,5,"Serie y Folio","LR",0,"L",1);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,5,$facts->folio,"LR",0,"R");


   $this->Ln();
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40);
   $this->Cell(85,5,"R.F.C. ".$empresa->rfc,0,0,"C");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(255,255,255);
   $this->Cell(25,4,utf8_decode("Versión"),"LR",0,"L",1);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,str_replace(" ","T",$version),"LR",0,"R");


   $this->Ln();
   $this->Cell(40);
   $this->Cell(85,4,$empresa->calle.' '.$empresa->nume.' '.$empresa->numi,0,0,"C");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(255,255,255);
   $this->Cell(25,4,utf8_decode("Fecha de emisión"),"LR",0,"L",1);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,str_replace(" ","T",$fechaemision),"LR",0,"R");


   $this->Ln();
   $this->Cell(40);
   $this->Cell(85,4,utf8_decode('COL. '.$empresa->colonia.' '.$empresa->ciudad),0,0,"C");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(0, 0, 0);
   $this->SetTextColor(255,255,255);
   $this->Cell(25,4,utf8_decode("Certificado CSD"),"LR",0,"L",1);
   $this->Cell(40,4,$certicsd,"LR",0,"R");

   $this->Ln();
   $this->Cell(40);
   $this->Cell(85,4,utf8_decode($empresa->estado." ".$empresa->cp),0,0,"C");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(255,255,255);
   $this->Cell(25,4,utf8_decode("Certificado SAT"),"LR",0,"L",1);
   $this->SetFont('Arial','',8);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,$certisat,"LR",0,"R");

   $regimen = $empresa->regimen.' - '.busca($empresa->regimen,'cfdi_regimenfiscal','cr_id','cr_nmb');
   $this->Ln();
   $this->Cell(40);
   $this->SetFont('Arial','',7);
   $this->Cell(85,4,utf8_decode(substr($regimen,0,53)),0,0,"C");
   $this->SetTextColor(255,255,255);
   $this->SetFont('Arial','',8);
   $this->Cell(25,4,utf8_decode("Certificación"),"LBR",0,"L",1);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(40,4,$fechatimbrado,"LR",0,"R");

   $this->Ln();
   $this->Cell(40);
   $this->SetFont('Arial','',7);
   $this->Cell(85,4,utf8_decode(substr($regimen,53,50)),0,0,"C");
   $this->SetFont('Arial','',8);
   $this->SetTextColor(0, 0, 0);
   $this->Cell(65,4,$uuid,"LRB",0,"C");



   $this->Ln(7);
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

/*Funciones Creadas para generar salto de linea*/
function SetWidths($w)
{
  //Ajustar la gama del ancho de columna
  $this->widths=$w;
}

function SetAligns($a)
{
  //ajusta la alineacion en el arreglo
  $this->aligns=$a;
}

function Row($data,$bandera=false)
{
  //Calcular la altura de la fila
  $nb=0;
  for($i=0;$i<count($data);$i++)
    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
  $h=4*$nb;
  //Emitir un salto de página primera, si es necesario
  $this->CheckPageBreak($h);
    if($bandera==true) $rellenar ='FD';
    if($bandera==false) $rellenar ='D';
  //Dibuja las celdas de la fila
  for($i=0;$i<count($data);$i++)
  {
    $w=$this->widths[$i];
        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
    //Guardar la posición actual
    $x=$this->GetX();
    $y=$this->GetY();
      //dibuja la tabla
    $this->Rect($x,$y,$w,$h,$rellenar);
    //solo imprime el texto
    $this->MultiCell($w,4,$data[$i],0,$a);
    //Put the position to the right of the cell
    $this->SetXY($x+$w,$y);
  }
  //Ir a la siguiente línea
  $this->Ln($h);
    $bandera=!$bandera;
}

function RowPC($data,$bandera=false)
{
  //Calcular la altura de la fila
  $nb=0;
  for($i=0;$i<count($data);$i++)
    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
  $h=4*$nb;
  //Emitir un salto de página primera, si es necesario
  $this->CheckPageBreak($h);
    if($bandera==true) $rellenar ='FD';
    if($bandera==false) $rellenar ='D';
  //Dibuja las celdas de la fila
  for($i=0;$i<count($data);$i++)
  {
    $w=$this->widths[$i];
        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
    //Guardar la posición actual
    $x=$this->GetX();
    $y=$this->GetY();
      //dibuja la tabla
    $this->Rect($x,$y,$w,$h,$rellenar);
    //solo imprime el texto
    $this->MultiCell($w,4,$data[$i],0,$a);
    //Put the position to the right of the cell
    $this->SetXY($x+$w,$y);
  }
  //Ir a la siguiente línea
  $this->Ln($h);
    $bandera=!$bandera;
}

function CheckPageBreak($h)
{
  //Si la altura h provocaría un desbordamiento, añadir una nueva página de inmediato
  if($this->GetY()+$h>$this->PageBreakTrigger)
    $this->AddPage($this->CurOrientation);
}

function NbLines($w,$txt)
{
  //Calcula el número de líneas de un MultiCell de anchura w tomará
  $cw=&$this->CurrentFont['cw'];
  if($w==0)
    $w=$this->w-$this->rMargin-$this->x;
  $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
  $s=str_replace("\r",'',$txt);
  $nb=strlen($s);
  if($nb>0 and $s[$nb-1]=="\n")
    $nb--;
  $sep=-1;
  $i=0;
  $j=0;
  $l=0;
  $nl=1;
  while($i<$nb)
  {
    $c=$s[$i];
    if($c=="\n")
    {
      $i++;
      $sep=-1;
      $j=$i;
      $l=0;
      $nl++;
      continue;
    }
    if($c==' ')
      $sep=$i;
    $l+=$cw[$c];
    if($l>$wmax)
    {
      if($sep==-1)
      {
        if($i==$j)
          $i++;
      }
      else
        $i=$sep+1;
      $sep=-1;
      $j=$i;
      $l=0;
      $nl++;
    }
    else
      $i++;
  }
  return $nl;
}
/*FIN DE Funciones Creadas para generar salto de linea*/

}

$id = $_GET['id'];
$ncredito = $_GET['id'];
$empresa = busca($_GET['id'],'ncredito','n_id','n_empresa');
$empres = busca($_GET['id'],'ncredito','n_id','n_empresa');

    include_once("../modulos/ncredito.php");
    $facts = new Modelncredito();
    $facts->selectncr($id);

    include_once("../modulos/clientes.php");
    $cliente = new modelclientes();
    $cliente->selectfisc($facts->cliente,$facts->fiscales);

    $orden = 0;

$pdf = new PDF("P","mm", "Letter");
$pdf->AddPage();
$pdf->SetFont('Arial','B',11);

if($empres != 354)
  $pdf->SetFillColor(27, 127, 185);
else
  $pdf->SetFillColor(235, 65, 41);

$pdf->SetDrawColor(0,0,0);    // Arial bold 15
$pdf->SetTextColor(255,255,255);

$pdf->Cell(5);

if($orden == 0)
    $pdf->Cell(185,5,"RECEPTOR",1,0,"C",1);
else
    $pdf->Cell(145,5,"RECEPTOR",1,0,"C",1).$pdf->Cell(40,5,"ORDEN DE COMPRA",1,0,"C",1);

$pdf->Ln();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(5);
$pdf->Cell(30,4,"R.F.C ",0,0,"L");
$pdf->SetFont('Arial','',8);

    if($orden == 0)
        $pdf->Cell(155,4,$cliente->rfc,0,0,"L");
    else
        $pdf->Cell(115,4,utf8_decode($cliente->rfc),0,0,"L").$pdf->Cell(40,4,"","LR",0,"L");

$pdf->Ln();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(5);
$pdf->Cell(30,4,"NOMBRE ",0,0,"L");
$pdf->SetFont('Arial','',8);

if($orden == 0)
    $pdf->Cell(155,4,utf8_decode($cliente->razonsocial),0,0,"L");
else
    $pdf->Cell(115,4,utf8_decode($cliente->razonsocial),0,0,"L").$pdf->Cell(40,4,"","LR",0,"L");


$domcliente = $cliente->calle.' '.$cliente->nume.' '.$cliente->numi.' '.$cliente->colonia.' '.$cliente->municipio.' '.$cliente->estado.' '.$cliente->pais.' '.$cliente->cp;

$pdf->Ln();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(5);
$pdf->Cell(30,4,"DIRECCION ",0,0,"L");
$pdf->SetFont('Arial','',8);

    if($orden == 0){
      if(strlen($domcliente) > 82){
        $pdf->MultiCell(155,4,utf8_decode($domcliente),0,"L");
      }
      else{
        $pdf->Cell(155,4,utf8_decode($domcliente),0,0,"L");
        $pdf->Ln();
      }
    }
    else{
        if(strlen($domcliente) > 60){
          $y = $pdf->GetY();
          $pdf->MultiCell(115,4,utf8_decode($domcliente),"","L");
          $pdf->SetFont('Arial','B',14);
          $pdf->SetXY(160,$y);
          $pdf->MultiCell(40,4,trim(substr($facts->ordenc,13,13)),"LR","C");
          $pdf->SetX(160);
          $pdf->Cell(40,4,"","LR",1,"C");
        }
        else{
          $pdf->Cell(115,4,utf8_decode($domcliente),0,0,"L");
          $pdf->SetFont('Arial','B',14);
          $pdf->Cell(40,4,trim(substr($facts->ordenc,13,13)),"LR",0,"C");
          $pdf->Ln();
        }
    }

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(5);
$pdf->Cell(30,4,utf8_decode("RÉGIMEN FISCAL"),0,0,"L");
$pdf->SetFont('Arial','',8);
$pdf->Cell(155,4,utf8_decode($cliente->regimen.' - '.busca($cliente->regimen,'cfdi_regimenfiscal','cr_id','cr_nmb')),0,0,"L");


//Documentos Relacionados
if(busca($id,'factura_relacion','fr_origen','COUNT(*)') > 0){
  $pdf->SetFont('Arial','B',11);
  $pdf->SetDrawColor(0,0,0);    // Arial bold 15
  $pdf->SetTextColor(255,255,255);
  $pdf->Ln(7);
  $pdf->Cell(5);
  $pdf->Cell(185,5,"ncredito Relacionadas",1,0,"C",1);
  $pdf->Ln();
  $pdf->SetFont('Arial','',7);
  $pdf->SetTextColor(255,255,255);
  $pdf->Cell(5);
  $pdf->Cell(25,5,"Folio",1,0,"C",1);
  $pdf->Cell(65,5,"UUID",1,0,"C",1);
  $pdf->Cell(95,5,"Tipo de Relacion",1,0,"C",1);
  $pdf->SetTextColor(0,0,0);

  $pdf->SetWidths(array('25','65','95'));
  $pdf->Ln();

  $sqlr = 'SELECT * FROM factura_relacion WHERE fr_origen = "'.$id.'"';
  $resultr = setq($sqlr) or die($sqlr);
  while($rwr = $resultr->fetch_array()){
    $folior = busca($rwr['fr_factura'],'ncredito','n_id','n_folio');
    $uuidr = $rwr['fr_uuid'];
    $tipor = busca($rwr['fr_tiporelacion'],'cfdi_relaciones','cr_id','cr_nmb');
    $pdf->Cell(5);
    $pdf->Row(array($folior,$uuidr,utf8_decode($tipor)));
  }
}

$pdf->SetTextColor(0,0,0);
$pdf->Cell(5);
$pdf->Cell(30);
$pdf->SetFont('Arial','',8);

$sqld = 'SELECT * FROM ncreditod WHERE d_ncredito = "'.$id.'" ORDER BY d_id ASC';
$result = setq($sqld) or die($sqld);

$pdf->Ln(7);

$pdf->SetFont('Arial','',6);
$pdf->SetTextColor(255,255,255);

      $pdf->Cell(5);
      $pdf->Cell(20,5,"Cantidad",1,0,"C",1);
      $pdf->Cell(15,5,"Unidad",1,0,"C",1);
      $pdf->Cell(15,5,"Clave Unidad",1,0,"C",1);
      $pdf->Cell(80,5,"Concepto",1,0,"C",1);
      $pdf->Cell(15,5,"Clave",1,0,"C",1);
      $pdf->Cell(20,5,"Precio",1,0,"C",1);
      $pdf->Cell(20,5,"Importe",1,0,"C",1);

  $pdf->SetTextColor(0,0,0);
  $subtotal = 0;
  $iva = 0;

  $pdf->SetWidths(array('20','15','15','15','80','20','20'));

srand(microtime()*1000000);
$bandera=true;
$pdf->Ln();
$iva = 0;
$reti = 0;
$isr = 0;
$descuento = 0;
while($row= $result->fetch_array()){
  $importe = $row['d_costo']*$row['d_cantidad'];
  $descuento+=$row['d_descuento'];

  $pdf->SetFont('Arial','',7);
  $subtotal+=$importe;
  $pdf->Cell(5);

    $pdf->SetAligns(array('C','C','C','C','L','R','R'));
    $unm = busca($row['d_unidad'],'unidades','u_empresa = "'.$_SESSION['emp'].'" AND u_nmb','u_clavesat');
    $pdf->Row(array($row['d_cantidad'],utf8_decode($row['d_unidad']),utf8_decode($unm),utf8_decode($row['d_producto']),utf8_decode(mb_strtoupper($row['d_concepto'])),'$'.number_format($row['d_costo'],2),'$'.number_format($importe,2)));

}
  $iva = busca($ncredito,'ncredito','n_id','n_iva');
  $reti = busca($ncredito,'ncredito','n_id','n_retiva');
  $isr = busca($ncredito,'ncredito','n_id','n_isr');


  $subad = $subtotal;
  $totalf = $subtotal+$iva-$reti-$isr;
//  echo $totalf.'<<<';
  $pdf->Ln();
  $pdf->Cell(5);
  $pdf->Cell(185,5,"","T");

    $amortizacion = "0";
    $montoamortizacion=0;

    if($descuento > 0){
      $titleam = "DESCUENTO";

      $montoamortizacion = $descuento;
      $totalf-=$montoamortizacion;
    }

    if($amortizacion == "1" || $descuento > 0){
      $pdf->Ln();
      $pdf->Cell(5);
      $pdf->Cell(100);
      $pdf->SetTextColor(255,255,255);
      $pdf->SetFont('Arial','B',6);
      $pdf->Cell(2);
      $pdf->Cell(40,5,utf8_decode("SUBTOTAL ANTES DE DESCUENTO"),1,0,"L",1);
      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',9);
      $pdf->Cell(43,5,number_format($subad,2),1,0,"R");

      $pdf->Ln();
      $pdf->Cell(5);
      $pdf->SetTextColor(255,255,255);
      $pdf->Cell(100,5,utf8_decode("CANTIDAD CON LETRA"),1,0,"C","LR");
      if(strlen($titleam) > 25) $lam = 5;
      else $lam = 6;

      $pdf->SetFont('Arial','B',$lam);
      $pdf->Cell(2);
      $pdf->Cell(40,5,utf8_decode($titleam),1,0,"L",1);
      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',9);
      $pdf->Cell(43,5,number_format($montoamortizacion,2),1,0,"R");
    }
    else{
      $pdf->Ln();
      $pdf->SetFont('Arial','B',7);
      $pdf->SetTextColor(255,255,255);
      $pdf->Cell(5);
      $pdf->Cell(100,5,utf8_decode("CANTIDAD CON LETRA"),1,0,"C","LR");
      $pdf->Cell(2);
      $pdf->Cell(40,5,utf8_decode("SUBTOTAL"),1,0,"L",1);
      $pdf->SetTextColor(0,0,0);
      $pdf->SetFont('Arial','',9);
      $pdf->Cell(43,5,number_format($subtotal,2),1,0,"R");

    }

  $decimales = explode(".",number_format($totalf,2,'.',''));

  if($facts->tipocambio == "MXN") $nomm = "PESOS";
  else $nomm = $facts->tipocambio;

  $cantletra = strtoupper(num2letras(number_format($totalf,2,'.',''))).' '.$nomm.' '.$decimales[1].'/100 ';
  if($facts->tipocambio == "MXN") $cantletra.= "M.N.";

  $ini = 0;

  $pdf->Ln();
  $pdf->SetFont('Arial','B',7);
  $pdf->Cell(5);
  $pdf->Cell(100,5,utf8_decode(substr(strtoupper($cantletra),$ini,65)),"LR",0,"C");
  $pdf->Cell(2);
  $pdf->SetTextColor(255,255,255);
  $pdf->Cell(40,5,utf8_decode("IVA"),1,0,"L",1);
  $pdf->SetTextColor(0,0,0);
  $pdf->SetFont('Arial','',9);
  $pdf->Cell(43,5,number_format($iva,2),1,0,"R");
  $ini+=65;

  if($reti > 0){
    $pdf->Ln();
    $pdf->SetFont('Arial','B',7);
    $pdf->Cell(5);
    $pdf->Cell(100,5,utf8_decode(substr(strtoupper($cantletra),$ini,65)),"LR",0,"C");
    $pdf->Cell(2);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(40,5,utf8_decode("RET. IVA"),1,0,"L",1);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(43,5,number_format($reti,2),1,0,"R");
    $ini+=65;
  }

  if($isr > 0){
    $pdf->Ln();
    $pdf->SetFont('Arial','B',7);
    $pdf->Cell(5);
    $pdf->Cell(100,5,utf8_decode(substr(strtoupper($cantletra),$ini,65)),"LR",0,"C");
    $pdf->Cell(2);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(40,5,utf8_decode("ISR"),1,0,"L",1);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(43,5,number_format($isr,2),1,0,"R");
    $ini+=65;
  }

  $pdf->Ln();
  $pdf->SetFont('Arial','B',7);
  $pdf->Cell(5);
  $pdf->Cell(100,5,utf8_decode(substr(strtoupper($cantletra),$ini,65)),"LRB",0,"C");
  $pdf->Cell(2);
  $pdf->SetTextColor(255,255,255);
  $pdf->Cell(40,5,utf8_decode("TOTAL"),1,0,"L",1);

  $pdf->SetTextColor(0,0,0);
  $pdf->SetFont('Arial','',9);
  $pdf->Cell(43,5,"$ ".number_format($totalf,2),1,0,"R");

  $sqlcom = 'SELECT * FROM adendas WHERE a_factura = "'.$id.'"';
  $resultcom = setq($sqlcom) or die($sqlcom);
  $comens = "";
  while($rowcom = $resultcom->fetch_array()){
      $comens.=$rowcom['a_adenda'].' - ';
  }
  $comens = substr($comens,0,-3);

  $pdf->Ln(8);
  $pdf->SetFont('Arial','B',8);
  $pdf->SetTextColor(255,255,255);
  $pdf->Cell(5);
  $pdf->Cell(185,5,"OBSERVACIONES",1,0,"C",1);
  $pdf->Ln();
  $pdf->SetFont('Arial','B',7);
  $pdf->SetTextColor(0,0,0);
  $pdf->Cell(5);
  $pdf->Multicell(185,5,utf8_decode($comens),1);

  $tipocon = "INGRESO";
  $arrmetodopago = array("PUE" => "PAGO EN UNA SOLA EXHIBICIÓN", "PPD" => "PAGO EN PARCIALIDADES O DIFERIDO");
  $pdf->SetFont('Arial','',6);
  $pdf->Cell(5);
  $pdf->Cell(40,3,"EFECTOS FISCALES AL PAGO","L");
  $pdf->Cell(50,3,"TIPO DE COMPROBANTE ".$tipocon,0);
  $pdf->Cell(95,3,utf8_decode('MÉTODO DE PAGO: ').$facts->metodopago.' - '.utf8_decode($arrmetodopago[$facts->metodopago]),"R",0,"R");

  $pdf->Ln();
  $pdf->SetFont('Arial','',6);
  $pdf->Cell(5);
  $pdf->Cell(90,3,utf8_decode("FORMA DE PAGO: ".busca($facts->fpago,'cfdi_fpago','cf_id','cf_nmb').' - '.busca($facts->fpago,'cfdi_fpago','cf_id','cf_descripcion')),"L");
  $pdf->Cell(95,3,utf8_decode("CONDICIÓN DEL PAGO: ".$facts->condiciones),"R",0,"R");

    $pdf->Ln();
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(5);
    $pdf->Cell(15,3,"USO DE CFDI ","BL",0,"L");

    $uso = busca($id,'ncredito','n_id','n_uso');
    $pdf->Cell(120,3,utf8_decode($uso.' - '.mb_strtoupper(busca($uso,'cfdi_uso','cu_id','cu_nmb'))),"B",0,"L");
    $pdf->Cell(50,3,utf8_decode("TIPO DE CAMBIO ".' - '.number_format($facts->valorm,4)),"RB",0,"R");


/*
   $sellocfd =  str_pad("X",42,"X");
   $foliosat =  str_pad("X",42,"X");
   $uuid =  "12345678-ABCD-0987-EFGH-A1B2C3D4E5F6";
   $certisat =  "XXXXXXXXXXXXXXXXX";
   $certicsd =  "XXXXXXXXXXXXXXXXX";
   $fechatimbrado =  date('Y-m-d').'T'.date('H:i:s');
   $fechaemision =  date('Y-m-d').'T'.date('H:i:s');
*/

   $sellocfd =  sacarcadena('sello="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $foliosat =  sacarcadena('selloSAT="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $uuid =  sacarcadena('Version="1.0" UUID="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $certisat =  sacarcadena('NoCertificadoSAT="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $certicsd =  sacarcadena('NoCertificado="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $fechatimbrado =  sacarcadena('FechaTimbrado="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));
   $fechaemision =  sacarcadena('Fecha="','"',file_get_contents('../cfd/'.$empres.'/xml/'.busca($id,'ncredito','n_id','n_folio').'.xml'));


    $cadenaor = '||1.1|'.$uuid.'|'.$fechatimbrado.'|'.$sellocfd.'|'.$certisat.'||';
    $pdf->Ln(8);
    $pdf->SetFont('Arial','B',7);
    $pdf->Cell(5);
    $pdf->Cell(185,3,utf8_decode("Sello Dígital Del CFDI"));
    $pdf->Ln();
    $pdf->SetFont('Arial','B',6);
    $pdf->Cell(5).$pdf->Cell(185,3,substr($sellocfd,0,105)).$pdf->Ln();
    $pdf->Cell(5).$pdf->Cell(185,3,substr($sellocfd,105,105)).$pdf->Ln();
    $pdf->Cell(5).$pdf->Cell(185,3,substr($sellocfd,210,105)).$pdf->Ln();
    $pdf->Cell(5).$pdf->Cell(185,3,substr($sellocfd,315,105));

    $pdf->Ln(6);
    $pdf->SetFont('Arial','B',7);
    $pdf->Cell(5);
    $pdf->Cell(185,3,utf8_decode("Sello del SAT"));
    $pdf->Ln();
    $pdf->SetFont('Arial','B',6);
    $pdf->Cell(5).$pdf->Cell(185,3,substr($foliosat,0,100)).$pdf->Ln();
    $pdf->Cell(5).$pdf->Cell(185,3,substr($foliosat,100,100)).$pdf->Ln();
    $pdf->Cell(5).$pdf->Cell(185,3,substr($foliosat,200,100));

    $y = $pdf->GetY();
    $pdf->Ln(6);
    $pdf->SetFont('Arial','B',7);
    $pdf->Cell(5);
    $pdf->Cell(185,3,utf8_decode("Cadena Original"));
    $pdf->Ln();
    $pdf->SetFont('Arial','B',6);
    $pdf->Cell(5).$pdf->Cell(185,3,substr($cadenaor,0,110)).$pdf->Ln();
    $pdf->Cell(5).$pdf->Cell(185,3,substr($cadenaor,110,110)).$pdf->Ln();
    $pdf->Cell(5).$pdf->Cell(185,3,substr($cadenaor,220,110)).$pdf->Ln();
    $pdf->Cell(5).$pdf->Cell(185,3,substr($cadenaor,330,110));

    if (!is_dir('../cfd/'.$empresa.'/qr')) {
        @mkdir('../cfd/'.$empresa.'/qr', 0777);
    }

    if (!is_dir('../cfd/'.$empresa.'/pdf')) {
        @mkdir('../cfd/'.$empresa.'/pdf', 0777);
    }

    $sqluu = 'UPDATE ncredito SET n_uuid = "'.$uuid.'" WHERE n_id = "'.$id.'"';
    if($uuid != "12345678-ABCD-0987-EFGH-A1B2C3D4E5F6") setq($sqluu) or die($sqluu);

    $u8d = substr($sellocfd,-8);
    QRcode::png('https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id='.$uuid.'&re='.busca($empresa,'empresas','e_id','e_rfc').'&rr='.$cliente->rfc.'&tt='.number_format($totalf,2,'.','').'&fe='.$u8d,'../cfd/'.$empresa.'/qr/'.busca($id,'ncredito','n_id','n_folio').'.png');
    $pdf->Image('../cfd/'.$empresa.'/qr/'.busca($id,'ncredito','n_id','n_folio').'.png',170,($y-15),30,30);


  $pdf->Output('../cfd/'.$empresa.'/pdf/'.busca($id,'ncredito','n_id','n_folio').'.pdf');
    $pdf->Output();
    $pdf->Close();
?>