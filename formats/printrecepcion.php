<?php
include('../funciones.php');
include('../lib/fpdf/fpdf.php');
session_start();
//ini_set('display_errors', 1);

class PDF extends FPDF
{
var $widths;
var $aligns;

  function Header()
  {
    $sql='SELECT * FROM recepciones WHERE r_id="'.$_GET['id'].'"';
    $rs=setq($sql) or die($sql);
    $rw= $rs->fetch_array();
    $empresa = busca($_GET['id'],'recepciones','r_id','r_empresa');

    include_once('../modulos/config.php');
    $emp = new modelconfig();
    $emp->select($empresa);

    if(file_exists('../'.$emp->logo) && $emp->logo != NULL) $logo = '../'.$emp->logo;
    else $logo = NULL;

    include_once('../modulos/recepciones.php');
    $recep = new modelrecepciones();
    $recep->select($_GET['id']);
    if($recep->estatus == "N"){
      $this->Image('../images/preview.png',5,30,200);
    }

    // Logo
    if($logo)
      $this->Image($logo,163,8,35);
    // Arial bold 15
    $this->AddFont('kalinga','','kalinga.php');
    $this->AddFont('kalingab','','kalingab.php');
    $this->SetFont('kalinga','',8);
    // Movernos a la derecha
    $this->SetDrawColor(100, 152, 201);
    $this->Line(160,13,160,38);
    $this->SetTextColor(0,0,0);
    $this->SetDrawColor(0, 0, 0);
    // Título

  //imprimir el numero de la cotizacion
  $this->SetFont('kalingab','',18);
  $this->Cell(35);
  $this->Cell(110,32,utf8_decode('RECEPCIÓN DE MERCANCÍA'),0,0,"C");
  $this->Ln();
  //////////////////////////////////
  $this->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
  $this->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
  $this->SetTextColor(255, 255, 255);
  ///////////////////////////////
  $this->SetFont('kalinga','',9);
  $this->Cell(4);
  $this->SetFillColor(91, 155, 213);
  $this->Cell(41,6,utf8_decode("No. de recepción:"),1,0,'C',true);
  $this->SetTextColor(0, 0, 0);
  $this->Cell(51,6,utf8_decode(str_pad($recep->folio,6,"0",STR_PAD_LEFT)),1,0);
  $this->SetTextColor(255, 255, 255);
  $this->Cell(41,6,utf8_decode("Comprador:"),1,0,'C',true);
  $this->SetTextColor(0, 0, 0);
  $this->Cell(51,6,utf8_decode($recep->comprador),1,0);
  $this->Ln();

  $this->Cell(4);
  $this->SetTextColor(255, 255, 255);
  $this->Cell(41,6,utf8_decode("Fecha de compra:"),1,0,'C',true);
  $this->SetTextColor(0, 0, 0);
  $this->Cell(51,6,utf8_decode(fecha_formato($recep->fechacompra,false,true)),1,0);
  $this->SetTextColor(255, 255, 255);
  $this->Cell(41,6,utf8_decode("Aplicación:"),1,0,'C',true);
  $this->SetTextColor(0, 0, 0);
  $this->Cell(51,6,utf8_decode(fecha_formato($recep->fechaapli,true,true)),1,0);
  $this->Ln();

  $this->Cell(4);
  $this->SetTextColor(255, 255, 255);
  $this->Cell(41,5,utf8_decode("Proveedor:"),1,0,'C',true);
  $this->SetTextColor(0, 0, 0);
  $this->Cell(143,5,utf8_decode(busca($recep->proveedor,'proveedores','p_id','p_nmb')),1,0);
  $this->Ln();

  $this->Cell(4);
  $this->SetTextColor(255, 255, 255);
  $this->Cell(41,5,utf8_decode("Tipo Documento:"),1,0,'C',true);
  $this->SetTextColor(0, 0, 0);
  $this->Cell(51,5,utf8_decode($recep->foliodoc),1,0);
  $this->SetTextColor(255, 255, 255);
  $this->Cell(41,5,utf8_decode("Folio Documento:"),1,0,'C',true);
  $this->SetTextColor(0, 0, 0);
  $this->Cell(51,5,utf8_decode($recep->foliodoc),1,0);
  $this->Ln();

    // Salto de línea
    $this->Ln();
}

// Pie de página
function Footer()
{
  $empresa = busca($_GET['id'],'recepciones','r_id','r_empresa');

  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  // Posición: a 1,5 cm del final
  $this->SetY(-25);
  $this->SetFont('kalinga','',11);
/*
  $this->Cell(0,5,utf8_decode('José Luis Reyes Benítez'),0,1,'C');
  $this->Ln(2);
  $this->Cell(0,5,'REBL8905188J8',0,1,'C');
  $this->Ln();
*/
  $this->SetFont('kalinga','',11);
  $this->Cell(0,5,'___________________________________',0,1,'C');
  //$this->Cell(0,5,utf8_decode($fila['c_id']),0,1,'C');
  $this->Cell(0,5,utf8_decode($emp->web),0,1,'C');
  // Arial italic 8
  $this->SetFont('kalinga','',8);
  // Número de página
  $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'R');
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
    $this->SetFont('kalinga','',7);
    $this->MultiCell($w,4.5,$data[$i],0,$a);
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
    $s=$nmb.'';

    $sql='SELECT CONCAT(a_nmb,"  - ",a_uso," ",a_accesorio," ",a_tipoactivo," ",a_capacidad," ",a_presion," ",a_umedida) as Activo, aa_noserie
          FROM recepcion_activo INNER JOIN activo_almacen ON aa_id = ra_activo INNER JOIN activos ON a_id = aa_activo
          WHERE ra_recepcion="'.$id.'" AND ra_idr = "'.$idcd.'"';
    $res=setq($sql) or die($sql);
    while($rw=$res->fetch_array()){
      $s.='
-'.utf8_decode(trim(ucfirst(strtoupper($rw['aa_noserie'].' - '.$rw['Activo']))));
    }

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
}

  $pdf = new PDF("P","mm", "Letter");
  $pdf->AliasNbPages();
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true,35);
  $pdf->SetFont('Arial','',9);

  $empresa = busca($_GET['id'],'recepciones','r_id','r_empresa');
  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  include_once('../modulos/recepciones.php');
  $recep = new modelrecepciones();
  $recep->select($_GET['id']);

  $pdf->AddFont('kalinga','','kalinga.php');
  $pdf->AddFont('kalingab','','kalingab.php');
  $pdf->SetFont('kalinga','',10);
  $pdf->SetTextColor(0,0,0);

/* $pdf->Cell(0,8,utf8_decode($recep->destino),0,1,'L'); */

 //$pdf->Ln(3);
//tabla de los productos de la cotizacion
  $pdf->Cell(4);
  $sql='SELECT * FROM recepcionesd WHERE rd_recepcion='.$recep->id;
  $resultado=setq($sql) or die($sql);
  $pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
  $pdf->SetFont('kalingab','',8);
  $pdf->SetTextColor(255, 255, 255);

//###definir el ancho de columna
  $col1=17; $col2=120; $col3=22; $col4=25; $col5=24;
  $pdf->Cell($col1,7,'Cantidad',1,0,'C',true);
  $pdf->Cell($col2,7,utf8_decode('Descripción'),1,0,'C',true);
  $pdf->Cell($col3,7,'Unitario',1,0,'C',true);
  $pdf->Cell($col4,7,'Total',1,0,'C',true);
  $pdf->Ln(7);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFont('kalinga','',8);
  $bandera=true;
  $pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla

//impresion de una tabla multicell utilzando un script mc_table del archivo mc_table.php
 //##################################################################################
 //#######################################################################################
 //#################################################################################3
//SetWidths(array(columna1,columna2,columna3,columna4,columna5,....,columnan))
//columna es el numero de ancho de cada columna
$pdf->SetWidths(array($col1,$col2,$col3,$col4));
srand(microtime()*1000000);
$bandera=true;
$viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
$poriva = $viva/100;
$ivat = 0;
$totalf = 0;
//echo $recep->iva;
$recep->diva = 1;
while($row=$resultado->fetch_array()){
  $iva=0;
  /*switch ($recep->diva) {
  //switch ($row['rd_iva']) {
    //cuando se requiere desplegar el iva
    case "1":
      if($row['rd_iva']=="1"){
        $iva=0;
        $iva=($row['rd_cantidad']*$row['rd_costo'])*$poriva;
        $precioProducto=$row['rd_costo'];
      }
      else{
        $iva=0;
        //if($recep->mtotal == 1){
        if($recep->diva == 1){
          $importeind = $row['rd_cantidad']*$row['rd_costo'];
          $iva=$importeind-(($row['rd_cantidad']*$row['rd_costo']/($viva + 100))*100);
          $precioProducto=$row['rd_costo']/(1+$poriva);
          //echo '<br><br>'.$row['cd_precio'].'->'.$iva.'<<<'.$row['cd_nmbarticulo'];

        }
        else{
          $iva=($row['rd_cantidad']*$row['rd_costo'])*$poriva;
          $precioProducto=$row['rd_costo'];
        }
      }
    break;

        //cuando no se requiere desplegar el iva
    case "0":
      if($row['rd_iva'] == "1"){
        $iva=0;
        $iva=($row['rd_cantidad']*$row['rd_costo'])*$poriva;
        $precioProducto=$row['rd_costo']*(1+$poriva);
      }
      else{
        $iva=0;
        $precioProducto=$row['rd_costo'];
      }
    break;

    case "N":
      $precioProducto=$row['rd_costo'];
    break;

    default:
        //echo "Your favorite color is neither red, blue, nor green!";
    }*/
    $precioProducto = $row['rd_costo'];
    if($row['rd_iva'] == "1"){
      $iva = ($precioProducto * $row['rd_cantidad']) * $poriva;
    }
    $ivat+=$iva;

    $total=$precioProducto*$row['rd_cantidad'];
    //echo $total.'--'.$totalf.'<br>';
    $totalf+=$total;
    //if($pdf->PageNo() == 1)
    $pdf->Cell(4);
    $pdf->prod = $pdf->PageNo();
    $pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla
    //Para imprimir la tabla, envia los registros al metodo array del archivo mc_table.php
    $pdf->SetAligns(array('C','J','R','R'));
    $pdf->RowMin(array($row['rd_cantidad'],$pdf->paquete($row['rd_recepcion'],utf8_decode(busca($row['rd_articulo'],'articulos','a_id','a_nmb')),$row['rd_id']),'$'.number_format($precioProducto,2),'$'.number_format($total,2)),false);
    $bandera=!$bandera;
}

$vertotal = 1;
if($vertotal == 1){
  //consulta base de datos
     $pdf->Cell(4);
      if($recep->iva == 0 && $recep->descuento == 0){
        $pdf->Cell(17,6,' ','T',0,'C');
        $pdf->Cell(120,6,' ','T',0,'L');
        $pdf->Cell(22,6,'Total a Pagar',1,0,'L',$bandera);
        $pdf->Cell(25,6,'$'.str_pad(number_format($recep->total,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
        $pdf->Ln(6);
      }
      else{
        $pdf->Cell(17,6,' ','T',0,'C');
        $pdf->Cell(120,6,' ','T',0,'L');
        $pdf->Cell(22,6,'Subtotal',1,0,'L',$bandera);
        $pdf->Cell(25,6,'$'.str_pad(number_format($recep->subtotal,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
        $pdf->Ln(6);
      }


  if($recep->iva > 0){
    $pdf->Cell(141);  $bandera = !$bandera;
    $pdf->Cell(22,6,'IVA',1,0,'L',$bandera);
    $pdf->Cell(25,6,'$'.str_pad(number_format($recep->iva,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
    $pdf->Ln(6);
  }
  if($recep->descuento>0){
    //$impdesc = $totalf-$recep->importe;
    $impdesc = $totalf-$recep->total;
    $descuento = $recep->subtotal * ($recep->descuento/100);
    $pdf->Cell(141);
    $bandera = !$bandera;
    $pdf->Cell(22,6,'Descuento '.number_format($recep->descuento,0).'%',1,0,'L',$bandera);
    $pdf->Cell(25,6,'$'.str_pad(number_format($descuento,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
    $pdf->Ln(6);
  }
  if($recep->descuento > 0 || $recep->iva > 0){
      $pdf->Cell(141);
      $bandera = !$bandera;
      $pdf->Cell(22,6,'Total a Pagar',1,0,'L',$bandera);
      //$pdf->Cell(25,6,'$'.str_pad(number_format($recep->importe,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
      $pdf->Cell(25,6,'$'.str_pad(number_format($recep->total,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
  }
  //cierrra la tabla
}

/*
if($fila['co_ttdc'] == 1){
  $sqlt = 'SELECT * FROM tabuladormsi WHERE t_estatus = "A" ORDER BY t_nmb ASC';
  $resultt = setq($sqlt) or die($sqlt);
  if(mysql_num_rows($resultt) > 0){
    $pdf->Ln();
    //$pdf->Cell(124);
    $pdf->Cell(5);
    $pdf->Cell(60,6,utf8_decode('Pagos con tarjetas de débito o crédito'),1,0,'C',false);
    $pdf->Ln(6);
    while($rwt = mysql_fetch_array($resultt)){
      $ttotal = $fila['co_total'] * (1+($rwt['t_porcentaje']/100));
      $pagot = $ttotal/$rwt['t_nmb'];
      $pdf->Cell(5);
      $pdf->Cell(30,6,$rwt['t_nmb'].' Pago(s) de',1,0,'L',false);
      $pdf->Cell(30,6,'$ '.number_format($pagot,2),1,0,'R',false);
      $pdf->Ln(6);
    }
    $pdf->Cell(5);
    $pdf->Cell(60,6,utf8_decode('Tarjetas Participantes '),0,0,'L',false);
    $pdf->Ln(6);
    $pdf->Cell(5);
    $pdf->Cell(60,6,$pdf->Image('../images/ttdc.jpg', $pdf->GetX(), $pdf->GetY(),30,'JPG'),0,0,'L',false);
    $pdf->Ln(6);
  }
}
*/

 //condiciones de servicio
/* $pdf->SetFont('kalinga','',10);
$pdf->Ln(5);
$sql='SELECT * FROM crm_cotizacionescond
      WHERE ccn_estatus="A" AND ccn_cotizacion = "'.$_GET['idcotiza'].'" ORDER BY ccn_orden';
$resultado=setq($sql) or die($sql);
$resultadon=setq($sql) or die($sql);
if($resultadon->num_rows > 0) $condiciones='Condiciones Comerciales'; else $condiciones = "";

$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0,5,$condiciones,0,1,'L');
$pdf->SetFont('kalinga','',6);
$pdf->Ln(3);

While($row=$resultado->fetch_array()){
  $pdf->cell(10);
     //convertir en minuscula el nombre
    //$condicion = strtolower($row['cf_descripcion']);
    //convertir en mayuscula la primera letra de cada palabra
    //$condicion = ucwords($condicion);
    $pdf->MultiCell(177,3,utf8_decode($row['ccn_descripcion']),0,'J');
}
$pdf->Ln(3); */
/*
$pdf->SetFont('kalinga','',8);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(177,5,utf8_decode('Esperando que el contenido de la presente cubra sus expectativas me pongo a sus órdenes para cualquier duda o aclaración.'),0,'J');
$pdf->Ln(1);
$pdf->MultiCell(177,4,utf8_decode('Los precios pueden variar sin previo aviso o hasta agotar existencias'),0,'J');
$pdf->MultiCell(177,4,utf8_decode('Se aplican restricciones'),0,'J');
$pdf->Ln(1);
$pdf->MultiCell(190,5,utf8_decode('Cotización válida hasta el '.date('d-m-Y',strtotime($recep->ffin))),0,'R');

*/
$pdf->Ln(10);

//$pdf->Output('cotizaciones/'.busca($_GET['idcotiza'],'cotizaciones','co_id','co_cotizacion').'.pdf','F');
$pdf->Output();
?>
