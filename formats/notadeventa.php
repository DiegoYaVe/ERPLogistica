<?php
include('../funciones.php');
include('../lib/fpdf/pdf_js.php');
session_start();
//ini_set('display_errors', 1);

class PDF extends PDF_JavaScript
{
var $widths;
var $aligns;
var $countrow;

  function Header()
  {
  }

// Pie de página
function Footer()
{
  $empresa = busca($_GET['id'],'remisiones','r_id','r_empresa');

  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  // Posición: a 1,5 cm del final
  $this->SetY(-10);
  $this->SetFont('kalinga','',8);

  $this->SetTextColor(0,0,0);
  // Número de página
  $this->Cell(0,6,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'R');
}
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

function Row($data,$bandera)
{
  //Calcular la altura de la fila
  $nb=0;
  for($i=0;$i<count($data);$i++)
    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
  $h=5*$nb;
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
    $this->MultiCell($w,5,$data[$i],0,$a);
    //Put the position to the right of the cell
    $this->SetXY($x+$w,$y);
  }
  //Ir a la siguiente línea
  $this->Ln($h);
    $bandera=!$bandera;
}

function RowMin($data,$bandera)
{
  //Calcular la altura de la fila
  $nb=0;
  for($i=0;$i<count($data);$i++)
    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    $h=4*$nb;
    //Emitir un salto de página primera, si es necesario
    $this->CheckPageBreak($h);
    $this->SetDrawColor(255,255,255);
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
    $this->SetFont('kalinga','',7);
    $this->MultiCell($w,4,$data[$i],0,$a);
    //Put the position to the right of the cell
    $this->SetXY($x+$w,$y);
  }
  //Ir a la siguiente línea
  $this->Ln($h);
    $bandera=!$bandera;
  //$this->countrow++;
}

function RowMinImg($data,$bandera,$valida)
{
  //Calcular la altura de la fila
  $nb=0;
  for($i=0;$i<count($data);$i++)
    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    $h=5*$nb;
    //Emitir un salto de página primera, si es necesario
    $this->CheckPageBreak($h);
    if($bandera==true) $rellenar ='FD';
    if($bandera==false) $rellenar ='D';
    //Dibuja las celdas de la fila
  if(!$valida){
    if($h < 30){
      $h = 30;
    }
  }

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
    $this->SetFont('kalinga','',7);
    $this->MultiCell($w,4.5,$data[$i],0,$a);
    //Put the position to the right of the cell
    $this->SetXY($x+$w,$y);
  }

  $enviar = ($h / 2) - 13;
  $this->setAltura($enviar);
  //Ir a la siguiente línea
  $this->Ln($h);
    $bandera=!$bandera;
}

function setAltura($altura){
  $this->altura = $altura;
}

function getAltura(){
  return $this->altura;
}

function CheckPageBreak($h)
{
  //Si la altura h provocaría un desbordamiento, añadir una nueva página de inmediato
  if($this->GetY()+$h>$this->PageBreakTrigger)
    $this->AddPage($this->CurOrientation);
    if($this->prod != $this->PageNo()){
      $this->Cell(4);
    }
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
function GenerateWord()
{
  //Get a random word
  $nb=rand(3,10);
  $w='';
  for($i=1;$i<=$nb;$i++)
    $w.=chr(rand(ord('a'),ord('z')));
  return $w;
}

function paquete($id,$nmb,$idcd)
{
  $articulo = busca($idcd, 'remisionesd', 'rd_id', 'rd_articulo');
  $s=$nmb."\n";
  $this->countrow++;
  $combo = busca($articulo, 'articulos', 'a_id', 'a_tipoprod');
  if($combo == "M"){
    $sqldet = 'SELECT COUNT(*) AS cantidad, a_nmb, rc_modelo, rc_articulo FROM remisionesc INNER JOIN articulos ON rc_articulo = a_id WHERE rc_remisiond = "'.$idcd.'" GROUP BY rc_articulo, rc_modelo';
    $resultdet = setq($sqldet);
    if($resultdet -> num_rows > 0){
      $s.= ' INCLUYE: '."\n";
      while($rowdet = $resultdet -> fetch_array()){
        $nb = $rowdet['a_nmb'];
        if($rowdet['rc_modelo'])  $nb .= ' '.busca($rowdet['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowdet['rc_modelo'].'" AND av_articulo', 'av_nmb');
        $detalle = ' - '.$rowdet['cantidad'].' '.$nb."\n";
        $s.= utf8_decode($detalle);
        $this->countrow++;
      }
    }
  } else {
    $sqldet = 'SELECT COUNT(*) AS cantidad, a_nmb, rc_modelo, rc_articulo FROM remisionesc INNER JOIN articulos ON rc_articulo = a_id WHERE rc_ligado IS NOT NULL AND rc_remisiond = "'.$idcd.'" GROUP BY rc_articulo, rc_modelo';
    $resultdet = setq($sqldet);
    if($resultdet -> num_rows > 0){
      $s.= ' Complementos: '."\n";
      while($rowdet = $resultdet -> fetch_array()){
        $nb = $rowdet['a_nmb'];
        if($rowdet['rc_modelo'])  $nb .= ' '.busca($rowdet['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$rowdet['rc_modelo'].'" AND av_articulo', 'av_nmb');
        $detalle = ' - '.$rowdet['cantidad'].' '.$nb."\n";
        $s.= utf8_decode($detalle);
        $this->countrow++;
      }
    }
  }

  /* $sql='SELECT CONCAT(a_nmb,"  - ",a_uso," ",a_accesorio," ",a_tipoactivo," ",a_capacidad," ",a_presion," ",a_umedida) as Activo, aa_noserie
        FROM remision_activo INNER JOIN activo_almacen ON aa_id = ra_activo INNER JOIN activos ON a_id = aa_activo
        WHERE ra_remision="'.$id.'" AND ra_idr = "'.$idcd.'"';
  $res=setq($sql) or die($sql);
  while($rw=$res->fetch_array()){
    $s.='
      -'.utf8_decode(trim(ucfirst(strtoupper($rw['aa_noserie'].' - '.$rw['Activo']))));
  } */

  return $s;
}

function WriteHTML($html)
{
        //HTML parser
        $html=str_replace("\n",' ',$html);
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                //Text
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                elseif($this->ALIGN=='center')
                    $this->Cell(0,5,$e,0,1,'C');
                else
                    $this->Write(5,$e);
            }
            else
            {
                //Tag
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    //Extract properties
                    $a2=explode(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $prop=array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $prop[strtoupper($a3[1])]=$a3[2];
                    }
                    $this->OpenTag($tag,$prop);
                }
            }
        }
    }

    function OpenTag($tag,$prop)
    {
        //Opening tag
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,true);
        if($tag=='A')
            $this->HREF=$prop['HREF'];
        if($tag=='BR')
            $this->Ln(5);
        if($tag=='P')
            $this->ALIGN=$prop['ALIGN'];
        if($tag=='HR')
        {
            if( !empty($prop['WIDTH']) )
                $Width = $prop['WIDTH'];
            else
                $Width = $this->w - $this->lMargin-$this->rMargin;
            $this->Ln(2);
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetLineWidth(0.4);
            $this->Line($x,$y,$x+$Width,$y);
            $this->SetLineWidth(0.2);
            $this->Ln(2);
        }
    }

    function CloseTag($tag)
    {
        //Closing tag
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF='';
        if($tag=='P')
            $this->ALIGN='';
    }

    function SetStyle($tag,$enable)
    {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s)
            if($this->$s>0)
                $style.=$s;
        $this->SetFont('',$style);
    }

    function PutLink($URL,$txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }
    function AutoPrint($printer='')
    {
      // Open the print dialog
      if($printer)
      {
          $printer = str_replace('\\', '\\\\', $printer);
          $script = "var pp = getPrintParams();";
          $script .= "pp.interactive = pp.constants.interactionLevel.full;";
          $script .= "pp.printerName = '$printer'";
          $script .= "print(pp);";
      }
      else
        $script = 'print(true);';
        $this->IncludeJS($script);
    }
}

  
  $pdf = new PDF("P","mm", "Letter");
  $pdf -> countrow = 0;
  $pdf->AliasNbPages();
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true,15);
  $pdf->SetFont('Arial','',9);


  $empresa = busca($_GET['id'],'remisiones','r_id','r_empresa');
  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  include_once('../modulos/remisiones.php');
  $remis = new modelremisiones();
  $remis->select($_GET['id']);
  $sql='SELECT * FROM remisiones WHERE r_id="'.$_GET['id'].'"';
    $rs=setq($sql) or die($sql);
    $rw= $rs->fetch_array();
    $empresa = busca($_GET['id'],'remisiones','r_id','r_empresa');

    include_once('../modulos/config.php');
    $emp = new modelconfig();
    $emp->select($empresa);

    if(file_exists('../'.$emp->logo) && $emp->logo != NULL) $logo = '../'.$emp->logo;
    else $logo = NULL;

    include_once('../modulos/remisiones.php');
    $remis = new modelremisiones();
    $remis->select($_GET['id']);
    if($remis->estatus == "N"){
      $pdf->Image('../images/preview.png',5,30,200);
    }

    // Logo
    if($logo)
      $pdf->Image($logo,20,5,20);
    // Arial bold 15
    $pdf->AddFont('kalinga','','kalinga.php');
    $pdf->AddFont('kalingab','','kalingab.php');
    // Movernos a la derecha
    $pdf->SetTextColor(0,0,0);
    $pdf->SetDrawColor(0, 0, 0);
    // Título

  $pdf->SetFont('kalingab','',18);
  $pdf->Cell(4);
  $pdf->SetFillColor(91, 155, 213);
  $pdf->Cell(40);
  $pdf->Cell(120,6,utf8_decode($emp->nmb),0,0,"C");
  $logo2 = '../img/inflalandia-games.jpg';
//  $pdf->Image($logo2,180,8,30);

  $pdf->SetFont('kalingab','',9);
  $pdf->Ln(4);
  $pdf->Cell(4);
  $pdf->SetTextColor(0,0,0);
  $pdf->Cell(30);
  $pdf->Cell(120,6,utf8_decode($emp->calle.' '.$emp->nume.' '.$emp->numi.' '.$emp->colonia.' '.$emp->ciudad.' '.$emp->estado.' '.$emp->cp),0,0);
  $pdf->Ln(4);
  $pdf->Cell(30);
  $pdf->Cell(140,6,utf8_decode("WWW.FABRICAINFLABLE.COM"),0,0,"C");
  $pdf->Ln(8);

  $pdf->SetFont('kalingab','',15);
  $pdf->SetTextColor(255,0,0);
  $pdf->Cell(20,5,"Fecha",0,0,"C");
  $pdf->SetFont('kalingab','',13);
  $pdf->SetTextColor(0,0,0);

  if($remis->estatus == "N") $fechar = $remis->falta; else $fechar = $remis->faplica;
  $fechastr = strtotime($fechar);
  $pdf->Cell(5,5,substr(date('d',$fechastr),0,1),1,0,"C");
  $pdf->Cell(1);
  $pdf->Cell(5,5,substr(date('d',$fechastr),1,1),1,0,"C");
  $pdf->Cell(1);
  $pdf->Cell(5,5,substr(date('m',$fechastr),0,1),1,0,"C");
  $pdf->Cell(1);
  $pdf->Cell(5,5,substr(date('m',$fechastr),1,1),1,0,"C");
  $pdf->Cell(1);
  $pdf->Cell(5,5,substr(date('y',$fechastr),0,1),1,0,"C");
  $pdf->Cell(1);
  $pdf->Cell(5,5,substr(date('y',$fechastr),1,1),1,0,"C");
  $pdf->Cell(5);

  $pdf->SetTextColor(255,0,0);
  $pdf->SetFont('kalingab','',14);
  $pdf->Cell(80,5,"ORDEN DE VENTA Y/O PEDIDO",0,0,"C");

  $pdf->SetFont('kalingab','',12);
  $pdf->Cell(20,5,"FOLIO",0,0,"C");
  $pdf->SetTextColor(0,0,0);
  $pdf->Cell(30,6,utf8_decode(substr($remis->folio,6,10)),1,0);
  $pdf->Ln(8);

  $pdf->SetFont('kalingab','',9);
  $pdf->Cell(38,5,"NOMBRE DEL CLIENTE",0,0,"C");
  $pdf->SetFont('kalingab','',8);
  $pdf->Cell(100,5,utf8_decode(busca($remis->cliente,'crm_clientes','c_id','c_nmb')),1,0,"C");
  $pdf->Cell(2);
  $pdf->SetFont('kalingab','',9);
  $pdf->Cell(20,5,"TELEFONO",0,0,"C");
  $pdf->SetFont('kalingab','',8);
  $pdf->Cell(37,5,utf8_decode(busca($remis->cliente,'crm_clientes','c_id','c_telefono2')),1,0,"C");


  $pdf->Ln(8);

  $pdf->SetTitle(utf8_decode('Remisión '.$remis->folio));
  $pdf->AddFont('kalinga','','kalinga.php');
  $pdf->AddFont('kalingab','','kalingab.php');
  $pdf->SetFont('kalinga','',10);
  $pdf->SetTextColor(0,0,0);

//  $pdf->Cell(0,8,utf8_decode($remis->destino),0,1,'L');

 //$pdf->Ln(3);
//tabla de los productos de la cotizacion
  $pdf->Cell(4);
  $sql='SELECT * FROM remisionesd WHERE rd_remision="'.$remis->id.'"';
  $resultado=setq($sql) or die($sql);
  $pdf->SetFont('kalingab','',10);

//###definir el ancho de columna
  $col1=18; $col2=25; $col3=89; $col4=28; $col5=30;
  $pdf->Cell($col1,7,'Cantidad',1,0,'C');
  $pdf->Cell($col2,7,'SKU','RT',0,'C');
  $pdf->Cell($col3,7,utf8_decode('Producto'),1,0,'C');
  $pdf->Cell($col4,7,'Precio unitario',1,0,'C');
  $pdf->Cell($col5,7,'Importe',1,0,'C');
  $pdf->Ln(7);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFont('kalinga','',8);
  $bandera=true;

//impresion de una tabla multicell utilzando un script mc_table del archivo mc_table.php
 //##################################################################################
 //#######################################################################################
 //#################################################################################3
//SetWidths(array(columna1,columna2,columna3,columna4,columna5,....,columnan))
//columna es el numero de ancho de cada columna
$pdf->SetWidths(array($col1,$col2,$col3,$col4,$col5));
$pdf->SetAligns(array("L","C","L","R","R"));
srand(microtime()*1000000);
$yres = $pdf->GetY();
$pdf->ln(2);
$yres2 = $pdf->GetY();
while($row=$resultado->fetch_array()){
  $total = $row['rd_precio']*$row['rd_cantidad'];
  if($row['rd_modelo']) $preciox = busca($row['rd_articulo'], 'articulos_precios', 'ap_activo = "1" AND ap_modelo = "'.$row['rd_modelo'].'" AND ap_articulo', 'ap_preciol');
  else $preciox = $row['rd_precio'];
  // else $preciox = busca($row['rd_articulo'], 'articulos_precios', 'ap_activo = "1" AND ap_modelo IS NULL AND ap_articulo', 'ap_preciol');

  $pdf->RowMin(array($row['rd_cantidad'],$row['rd_modelo'],$pdf->paquete($row['rd_remision'],utf8_decode($row['rd_nmbarticulo']),$row['rd_id']),'$'.number_format($preciox,2),'$'.number_format($total,2)),false);
  $bandera=!$bandera;
  //$pdf->countrow++;
} 

  //echo $pdf->countrow;
  $val = $pdf->countrow * 4 + 65;
  if($val < 150){
     $val = 150;
  }
  $next = $val+2;

  $pdf->SetDrawColor(0,0,0);
  $ymov = $yres-$val;
  $pdf->Rect(14,$val,$col1,$ymov);

  $ymov = $yres-$val;
  $xrect = $col1+14;
  $pdf->Rect(32,$val,$col2,$ymov);

  $ymov = $yres-$val;
  $xrect+=$col2;
  $pdf->Rect($xrect,$val,$col3,$ymov);

  $ymov = $yres-$val;
  $xrect+=$col3;
  $pdf->Rect($xrect,$val,$col4,$ymov);

  $ymov = $yres-$val;
  $xrect+=$col4;
  $pdf->Rect($xrect,$val,$col5,$ymov);


  $pdf->SetY($next);

  $pdf->SetFont('kalingab','',11);
  $pdf->Cell(10);
  $pdf->Cell(40,6,utf8_decode("CUPÓN"),1,0,0);
  $pdf->SetFont('kalingab','',12);
  $pdf->Cell(40,6,utf8_decode(number_format($remis->mdescuento,2)),1,0,0);
  $pdf->Cell(10);

  $pdf->SetFont('kalingab','',11);
  $pdf->Cell(40,5,utf8_decode("COSTO DE ENVÍO"),1,0,0);
  $pdf->SetFont('kalingab','',12);
  $pdf->Cell(40,5,utf8_decode(number_format($remis->precioenvio,2)),1,0,0);
  $pdf->Cell(2);
  $pdf->Ln(7);


  $pdf->SetFont('kalingab','',10);
  $pdf->Cell(28 ,6,utf8_decode("TOTAL"),1,0,0);
  $pdf->Cell(30,6,utf8_decode(number_format($remis->total,2)),1,0,0);
  $pdf->Cell(5);

  $sqling = 'SELECT * FROM ingreso_cxcobrar INNER JOIN cxcobrar ON cx_id = ic_cxcobrar
             INNER JOIN ingresos ON i_id = ic_ingreso
             WHERE cx_tipo = "R" AND cx_referencia = "'.$remis->id.'" AND i_estatus IN ("A","F")';
  $resulting = setq($sqling);
  $anticipos = "";
  $numing = 0;
  $abonado = 0;
  while($rowi = $resulting->fetch_array()){
    $numing++;
    $anticipos.=date('d-m-Y',strtotime($rowi['i_fecha'])).' - '.number_format($rowi['ic_monto']);
    if($numing > 1) $anticipos.="\n";
    $abonado+=$rowi['ic_monto'];
  }
  $abonado += busca($remis->id, 'cajasd', 'cd_remision' ,'SUM(cd_total)');
  

  $fecha = new DateTime($remis->faplica);

  $pdf->Cell(28,6,utf8_decode("ABONO(S)"),1,0,0);
  $pdf->SetFont('kalingab','',10);
  /* if($_SESSION['uid'] == "ADMIN"){
    $pdf->MultiCell(40,3,$anticipos,1,0,0,"R");
  } else { */
    $pdf->Cell(46,6,utf8_decode(number_format($abonado, 2)),1,0,0);
  //}
  $pdf->Cell(5);

  $pdf->SetFont('kalingab','',10);
  $saldo = $remis->total-$abonado;
  if($saldo < 0) $saldo = 0;
  $pdf->Cell(28,5,utf8_decode("RESTANTE"),1,0,0);
  $pdf->Cell(30,5,utf8_decode(number_format($saldo,2)),1,0,0);
  $pdf->Ln(2);

  $x=$pdf->GetX();
  $y=$pdf->GetY()+5;

  $meses = array("01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre");

  $pdf->Rect(10,$y,200,56);
  $pdf->SetFont('kalingab','',7);
  $pdf->Ln(6);
  $textto = "Debe(mos) y pagaré(mos) en forma incondicional a la orden de LA FABRICA DE INFLABLES INFLALANDIA SA DE CV, en la ciudad de Pachuca Hgo. el total de esta orden de compra recibida a nuestra entera satisfacción, el día ".$fecha->format('d')." de ".$meses[$fecha->format('m')]." de ".$fecha->format('Y').", en causa de demora en el pago, causará un interes del cinco por ciento mensual. Para la validación de este pedido y los anticipos aquí descritos, así como alguna GARANTÍA o aclaración este formato debe acompañarse de cotizacion impresa. Doy por enterado que la CANCELACIÓN de cualquier PEDIDO y/o APARTADO genera una penalización del 10% del (los) anticipo(s) realizado(s) al pedido total, teniendo un plazo máximo de 24 horas posteriores a la fecha de la presente, para realizar cualquier cambio o cancelación, Toda PETICIÓN especial a pedido deberá estar acentada en este formato. El tiempo de crédito una vez generada la nota de remisión es de 7(siete) y de no liquidarse la(s) mercancia(s) en este lapso causará una PENALIZACIÓN del 20% (VEINTE POR CIENTO) sobre el total de la compra. APARTADOS contarán con 7 días naturales para que el precio y los anticipos sean respetados, despues de los 7 días LA EMPRESA Y/O SUS REPRESENTANTES estarán liberados de cualquier responsabilidad de entrega o respetar dichos apartados. (para más POLITICAS DE VENTA ver hoja 2)";
  $pdf->Multicell(200,4,utf8_decode($textto),0,"J");
  $pdf->Cell(20);
  $pdf->SetFont('kalingab','',6);
  $pdf->Cell(50,5,"HE LEIDO Y ENTENDIDO TODOS LOS DATOS ACENTADOS EN ESTE FORMATO");
  $pdf->Cell(40);
  $pdf->MultiCell(80,5,"FLETE Y ENVIO A CUENTA Y CARGO DEL CLIENTE UNA VEZ SALIDA LA MERCANCIA NO SE ACEPTAN DEVOLUCIONES");
  $pdf->Ln(5);
  $pdf->Cell(35);
  $pdf->SetFont('kalingab','',7);
  $pdf->Cell(50,5,"NOMBRE Y FIRMA DE CONFORMIDAD","T");
  $pdf->Ln();

  $y=$pdf->GetY();

  $pdf->Rect(10,$y,200,40);
  $pdf->SetFont('kalingab','',11);
  $pdf->Cell(42,5,utf8_decode("TALÓN DE ENTREGA"),0,0,0);
  $pdf->Cell(5);
  $pdf->SetFont('kalingab','',9);
  $pdf->Cell(30,5,utf8_decode("TIPO DE ENTREGA"),0,0,0);
  $pdf->Cell(5,5,"",1);
  $pdf->Cell(15,5,utf8_decode("TOTAL"),0,0,0);
  $pdf->Cell(15,5,utf8_decode("FECHA"),0,0,0);
  $pdf->Cell(23,5,utf8_decode(""),"B",0,0);
  $pdf->Cell(5,3,"");
  $pdf->Cell(5,5,"",1);
  $pdf->Cell(15,5,utf8_decode("PARCIAL"),0,0,0);
  $pdf->Cell(15,5,utf8_decode("FECHA"),0,0,0);
  $pdf->Cell(23,5,utf8_decode(""),"B",0,0);
  $pdf->Ln(10);
  $pdf->Cell(5,3,"");
  $pdf->Cell(190,5,"","B",0,"C");
  $pdf->Ln(7);
  $pdf->Cell(5,3,"");
  $pdf->Cell(190,5,"","B",0,"C");
  $pdf->Ln(10);

  $pdf->SetFont('kalingab','',10);
  $pdf->Cell(50,5,utf8_decode("RECIBÍ DE CONFORMIDAD"),0,0,0);
  $pdf->Cell(55);
  $pdf->Cell(45,5,utf8_decode("RECIBÍ DE CONFORMIDAD"),0,0,0);
  $pdf->Ln(3);
  $pdf->SetFont('kalingab','',6);
  $pdf->Cell(50,5,utf8_decode("CONFIRMO QUE RECIBI PEDIDO COMPLETO"),0,0,0);
  $pdf->SetFont('kalingab','',10);
  $pdf->Cell(45,5,utf8_decode("NOMBRE Y FIRMA"),"T",0,"C");
  $pdf->Cell(5);
  $pdf->SetFont('kalingab','',6);
  $pdf->Cell(50,5,utf8_decode("CONFIRMO QUE RECIBI PEDIDO COMPLETO"),0,0,0);
  $pdf->SetFont('kalingab','',10);
  $pdf->Cell(45,5,utf8_decode("NOMBRE Y FIRMA"),"T",0,"C");
  $texto2 = '

POLÍTICAS DE VENTA
TIPO DE VENTA
1.- Contado: El cliente liquida totalmente el importe de la operacion en el momento de adquirir el producto.
2.-Apartado: E| CLIENTE podra reservar LOS PRODUCTOS DE ENTREGA INMEDIATA entregando un anticipo minimo del 10% y debiendo liquidar el saldo en un plazo máximo
de: INFLABLES: 8 dias naturales. TRAMPOLINES, FUTBOLITOS Y ACCESORIOS: 30 dias naturales.
3.-Pedido: En esta opcion EL CLIENTE tiene la oportunidad de reservar LOS PRODUCTOS, con un anticipo minimo del 50% para que su pedido entre a producción o sea requerido a CEDIS, debiendo cubrir el saldo total a la fecha de entrega o bien antes de envio en dado sea el caso.
En todos los casos, la fecha de entrega del pedido se acordará con el  CLIENTE y estará sujeta a la llegada de los productos al almacén requerido o bien al término de su produccción.

METODOS DE PAGO
1. Efectivo: Podrá realizar su pago directamente en cajas de cobro dentro de nuestras sucursales, o bien en la MATRIZ ubicada en Carretera Pachuca-Actopan km 7.1, Col Loma Bonita, C.P. 42088 Pachuca, Hidalgo.
2. Transferencia Bancaria y/o Depósito Bancario: Al realizar su compra seleccionando la forma de pago Transferencia Bancaria le proporcionaremos los datos de la
cuenta donde debera realizar el depósito bancario. Usted cuenta con 2 dias naturales a partir de la generacion de su pedido para realizar el pago correspondiente. Una vez realizado su pago se debera enviar al correo contabilidad@fabricainflable.com una copia del comprobante de pago e identificacion del titulas.Le agradeceremos que en el asunto del correo coloque el número de pedido que la tienda le asigno.
3.Tarjeta bancaria. Su pago podrá ser acreditado en nuestra platafomra de pagos con tarjetas de c´réditoVISA y MASTERCARD, generando un 2.71% do comisión adicional al costo del producto en pago de una sola exhibición y de un 3.1% pagando con AMERICAN EXPRESS.
4. Pago en línea. Su pago puede realizarse a través de nuestro sitio web WWW.FABRICAINFLABLE.COM  con tarjetas de crédito ó débito. Visa o MAsterCard. Una vez efectuado su pago, puede enviar un correo a contabilidad@fabricainflable.com y nuestra área de call center se conmunicará con usted para confirmar los datos y fecha de entrega de su compra. En caso de que el banco lo requiera, le pediremos hacernos llegar copia de una identificación oficiak del titular de la tarjeta al correo contabilidad@fabricainflable.com para autenticar su compra.
En todos los casos LOS PRODUCTOS serán entregados hasta que el pedido esté totalmente pagado y acreditado en nuestras cuentas bancarias.

ENTREGA Y RECEPCIÓN DE LOS PRODUCTOS
1. ENTREGA FISICA RECEPCIÓN AL TITULAR DE LA COMPRA: EL CLIENTE recoge su mercancía en tienda o almacén generla, dentro de los horarios establecidos y con previa cita, deberá presentar una identificación oficial del titular de la compra, revisar que este completo y firmar de conformidad al recibirlos.
2. ENTREGA FISICA A UN TERCERO: Si EL CLIENTE, envia a una persona distinta al titular de la compra, a recoger su pedido, ésta deberá llevar una carta poder simple, odnde el CLIENTE de autorización por escrito de la recolección del producto, así como una identificación oficial vigente.
3. ENVIOS: Si EL CLIENTE, solicita que LOS PRODUCTOS sean enviados, la transportacion tendrá un cargo de envio independiente a LOS PRODUCTOS y deberá coordinar
su envio, asi como realizar llenado del formato correspondiente directamente con su asesor, FLETE Y ENVIO ES A CUENTA CARGO Y RIESGO DEL CLIENTE.
El horario de entrega de LOS PRODUCTOS a paqueterías serán de Lunes a Viernes de 15:00 a 17:00 horas en la fecha programada. Las solicitudes de reprogramacions y/o cambios de domicilio, deberán reportarse a LA FABRICA DE INFLABLES INFLALANDIA con un mínimo de dos días (24 horas) previo a la fecha programada para su envío; en primera instancia a la sucursal y/o al asesor o al Centro de Atención al Cliente, al número telefónico: +52 771 126 7635.
LA FABRICA DE INFLABLES no se hace responsable de ningún daño o perdida parcial o total que sufran LOS PRODUCTOS al ser transportados por las paqueteria, la mercancia sale con embalaje y es fotografiada antes y durante el proceso de paquetería. EL CLIENTE podrá contratar un seguro de envío adicional, directamente con la paquetería o por medio de su Asesor de Ventas.

GARANTÍAS
LA FABRICA DE INFLABLES INFLALANDIA otorga una garantia de 1825 días en INFLABLES a partir de la entrega de LOS PRODUCTOS, esto por defecto de fabricacion, la cual no incluye desgaste natural o por uso general del producto, daños ocasionados por terceros, defectos o daños ocultos, observando lo siguiente:
- Toda reclamación por daños o entregas incompletas debe notificarse dentro de los primeros 2 dias hábiles siguientes a la fecha en que EL CLIENTE recibió LOS PRODUCTOS, debiendo reportarse a su tienda de venta o al centro de atención a clientes TEL. +52 771 126 7635; en caso contrario se entenderá que el CLIENTE recibio LOS PRODUCTOS a su entrera satisfacción.
- Cuando estos se trate de PRODUCTOS en liquidación y/o de oferta, LOS PRODUCTOS se entregaran en las condiciones físicas en que se encuentren al momento de su venta.
Sobre estos PRODUCTOS no se aceptará reclamación, reparación, devolución o cambio físico alguno, debiendo EL CLIENTE firmar de conformidad al momento de levantar el pedido y recibir los PRODUCTOS.
LA FABRICA DE INFLABLES INFLALANDIA no estará obligada a cumplir con lo que se menciona en esta cláusula de garantias, cuando el plazo de garantia haya prescrito o en su defecto, LOS PRODUCTOS hayan sido usados en condiciones diferentes a las normales, presenten reparaciones efectuadas por un tercero o hayan sido dañadas con dolo.
En los casos de articulos como accesorios, pelotas,trampolines y futbolitos la garantía que se otorga es únicamente por faltantes de piezas, la cual tendrá una validez de 7 días a partir de la entrega o envió, directamente presentado el reporte a nuestro Centro de Atención a Clientes, a fin de coordinar el servicio. En caso de 
a fin de coordinar el servicio. En caso de motores y turbinas para inflables estas tendrán garantía de 6 meses a partir de la fecha de entrega del mismo (CONSULTE EL MANUAL DE USO PARA PREVENIR DAÑOS).
Con la finalidad de brindar una respuesta oportuna en daños visibles EL COMPRADOR(A) podrá reportarlo con fotografias evidentes y claras a los correos de ventas@fabricainflable.com con copia a produccion@fabricainflable.com, colocando en asunto número de pedido, tomando en cuenta que las fotografias sean panoramicas (vista completa) de los daños, costado derecho e izquierdo, etiquetas, parte debajo o trasera del producto, componentes (cinta, bolsa de transporte, motor, accesorios.etc). Una vez enviada la información nuestro centro de atención al cliente contactará a la brevedad  COMPRADOR(A) para brindarle información en cuanto a su garantía.

RESCISIÓN o CANCELACIÓN DEL PEDIDO
Para solicitar cualquier rescisión o cancelación, EL CLIENTE deberá presentar el pedido original en la sucursal en la que se efectuo la compra , bajo las siguientes consideraciones:
- Una vez recibidos LOS PRODUCTOS y firmados de conformidad, no se aceptan cancelaciones por cambio de modelo, tamaño, color, y/o cancelaciones definitivas por causas imputables al cliente.
En LOS PRODUCTOS que ya hayan sido FABRICADOS A PETICION DEL CLIENTE, no se aceptara cancelación ni cambio alguno, por cual el CLIENTE deberá asegurarse que el pedido es correcto al momento de realizarlo, ya que el producto una vez fabricado no podrá cambiarse nu desarmarse.
- Cuando EL CLIENTE no tenga PRODUCTOS en su poder, la cancelacion del pedido y la devolución de su dinero se efectuara en la sucursal donde se realizó la operación de compra presentando el documento y recibos de pago originales, considerando los siguientes tiempos
a) Cancelación dentro de las primeras 24 horas después de realizado su pedido se hará la devolución de su dinero o bien el cambio por algun otro producto, descontando una
penalización del 10% del anticipo recibido.
b) Cancelaciones de los 8 a los 15 dias después de realizado el pedido, se hará la devolución del dinero o bien el cambio por alguna mercancia, descontando una penalización del 20% del anticipo recibido
c) Cancelaciones de los 16 dias a la fecha de entrega, no serán admitidas, (en caso fortuito con previa autorización se realizara la devolución con una penalización del 20% del valor total del pedido)
- La DEVOLUCION del dinero al EL CLIENTE, se efectuará vía transferencia bancaria una vez llenado y firmado el formato de DEVOLUCIONES en la sucursal donde se realizó la operación de compra presentando el documento y recibos de pago originales, cinco dias hábiles después de que LA FABRICA DE INFLABLES INFLALANDIA, tenga los formatos requisitados en su poder.
- Para todos los casos de cancelación con devolución de dinero para EL CLIENTE, éste se efectuara de acuerdo a lo siguiente:
a) Cuando el pago haya sido efectuado en efectivo, transferencia, se reembolsará con transferencia electrónica a nombre del CLIENTE que aparece como titular en el
pedido. 
- Tratándose de ventas de "Apartado", y si EL CLIENTE no liquidará el saldo total del pedido, en el plazo máximo de 7 y 30 dias posteriores a la fecha de entrega, LA FABRICA DE INFLABLES dará por cancelado el pedido y pondrá a la venta los productos, dando AL CLIENTE la opcion de meter a producción su pedido, generando una nueva fecha de entrega o bien la opcion de la devolución del dinero a EL CLIENTE, con las penalizaciones por cancelación descritas arriba, la cual se efectuará presentando la solicitud en la sucursal donde se realizó la operación de compra, presentando el documento y recibos de pago originales.
- El plazo para obtener el reembolso de las cantidades pagadas a que tenga derecho EL CLIENTE, prescribirá al término de DIEZ MESES, contado a partir de la fecha de realización del pedido.';
$pdf->SetFont('kalingab','',6);
$pdf->Ln(10);
$pdf->Multicell(200,3,utf8_decode($texto2),0,"J");

$texto3 ='AVISO DE PRIVACIDAD 
LA FABRICA DE INFLABLES INFLALANDIA, en cumplimiento con la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP), es responsable del tratamiento de sus datos personales. 

1. Datos personales recabados 
Recabamos los siguientes datos personales: 
- Nombre completo 
- Dirección de correo electrónico 
- Número de teléfono 
2. Finalidades del tratamiento de los datos personales 
Sus datos personales serán utilizados para las siguientes finalidades: - 
Primarias: 
Contactarlo para enviarle información sobre nuestros productos, servicios y promociones. 
Gestionar campañas publicitarias personalizadas. - 
Secundarias: 
Realizar encuestas de satisfacción y estudios de mercado. 
En caso de que no desee que sus datos sean utilizados para las finalidades secundarias, puede manifestarlo conforme al procedimiento descrito en la sección 5 de este aviso. 
3. Transferencia de datos personales 
Sus datos personales no serán compartidos con terceros sin su consentimiento, excepto en los casos previstos por la Ley. 
4. Derechos ARCO (Acceso, Rectificación, Cancelación y Oposición) 
Usted tiene derecho a acceder, rectificar, cancelar u oponerse al uso de sus datos personales. Para ejercer estos derechos, envíe una solicitud al correo electrónico ventas@fabricainflable.com o comuníquese al teléfono +52 771 126 7635. 
La solicitud debe contener: 
- Nombre completo del titular. 
- Copia de una identificación oficial. 
- Descripción clara y precisa de los datos personales sobre los cuales busca ejercer sus derechos. 
- Cualquier otro elemento o documento que facilite la localización de los datos. 
5. Revocación del consentimiento 
En cualquier momento, puede revocar el consentimiento que nos ha otorgado para el tratamiento de sus datos personales. Para ello, envíe su solicitud al correo ventas@fabricainflable.com siguiendo los requisitos mencionados en la sección anterior. 
6. Contacto 
Si tiene alguna duda o comentario sobre este Aviso de Privacidad, puede contactarnos a través de: 
Correo electrónico: ventas@fabricainflable.com 
Teléfono: +52 771 126 7635 
Fecha de última actualización: 16 de enero del 2025.
';
$pdf->addPage();
$pdf->SetFont('kalingab','',6);
$pdf->Ln(10);
$pdf->Multicell(200,3,utf8_decode($texto3),0,"J");

//$pdf->Output('cotizaciones/'.busca($_GET['idcotiza'],'cotizaciones','co_id','co_cotizacion').'.pdf','F');
//$pdf->Output('../docs/'.utf8_decode('Rem'.$remis->id).'.pdf',"F");
$pdf->Output();
$pdf->Close();
?>