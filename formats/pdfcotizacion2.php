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
    if(isset($_GET['uuid'])) $_GET['idcotiza'] = busca($_GET['uuid'],'crm_cotizaciones','cc_uuid','cc_id');
    $sql='SELECT * FROM crm_cotizaciones WHERE cc_id="'.$_GET['idcotiza'].'"';
    $rs=setq($sql) or die($sql);
    $rw= $rs->fetch_array();
    $empresa = busca($_GET['idcotiza'],'crm_cotizaciones INNER JOIN crm_tableros ON ct_id = cc_tablero','cc_id','ct_empresa');

    include_once('../modulos/config.php');
    $emp = new modelconfig();
    $emp->select($empresa);

    $sql = 'SELECT * FROM almacenes WHERE a_id = "'.$rw['cc_almacen'].'"';
    $result = setq($sql);
    $rowalm = $result->fetch_array();
    
    if($rowalm['a_img']) $logo = '../'.$rowalm['a_img'];
    else $logo = NULL;
    
    

    //if(file_exists('../'.$emp->logo) && $emp->logo != NULL) $logo = '../'.$emp->logo;
    //else $logo = NULL;

    include_once('../modulos/cotizaciones.php');
    $cotiza = new modelcotizaciones();
    $cotiza->select($_GET['idcotiza']);
    if($cotiza->estatus != "A" || $cotiza->estatus != "V"){
      $this->Image('../assets/media/preview.png',5,30,200);
    }

    // Logo
    if($logo)
      $this->Image($logo,163,2,35);
    //$this->Image($logo,163,2,35);
    // Arial bold 15
    $this->AddFont('kalinga','','kalinga.php');
    $this->AddFont('kalingab','','kalingab.php');
    $this->SetFont('kalinga','',8);
    // Movernos a la derecha
    $this->Cell(80);
    $this->SetDrawColor(100, 152, 201);
    $this->Line(160,10,160,30);
    $this->SetTextColor(0,0,0);
    // Título
    $this->SetTextColor(0,0,0);
    /* $this->Cell(70,4,utf8_decode(ucwords(strtolower(''.$emp->tel))),0,2,'R');
    $this->Cell(70,4,utf8_decode(ucwords(strtolower(''.$emp->correo))),0,2,'R');
    $this->Cell(70,4,utf8_decode(ucwords(strtolower(''.$emp->calle.' '.$emp->nume.' '.$emp->numi))),0,2,'R');
    $this->Cell(70,4,utf8_decode(ucwords(strtolower($emp->colonia.' '.$emp->cp.' '.$emp->ciudad.' '.$emp->estado))),0,2,'R'); */
    $this->Cell(70,4,utf8_decode(ucwords(strtolower(''.$rowalm['a_telefono']))),0,2,'R');
    $this->Cell(70,4,utf8_decode(ucwords(strtolower(''.$rowalm['a_correo']))),0,2,'R');
    $this->Cell(70,4,ucwords(strtolower(utf8_decode(''.$rowalm['a_calle'].' '.$rowalm['a_nume'].' '.$rowalm['a_numi']))),0,2,'R');
    $this->Cell(70,4,ucwords(strtolower(utf8_decode($rowalm['a_colonia'].' '.$rowalm['a_cp'].' '.$rowalm['a_ciudad'].' '.$rowalm['a_estado']))),0,2,'R');
    //$this->Cell(70,4,ucwords(strtolower(utf8_decode(''.$rowalm['a_direccion']))),0,2,'R');
    //$this->Cell(70,4,utf8_decode(ucwords(strtolower($emp->colonia.' '.$emp->cp.' '.$emp->ciudad.' '.$emp->estado))),0,2,'R');
    // Salto de línea
    $this->Ln();
}

// Pie de página
function Footer()
{
  $empresa = busca($_GET['idcotiza'],'crm_cotizaciones INNER JOIN crm_tableros ON ct_id = cc_tablero','cc_id','ct_empresa');

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
//  $this->Cell(0,5,'___________________________________',0,1,'C');
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
    $h=4.5*$nb;
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

    $sql='select * from crm_cotizacionesdd where cdd_idd="'.$idcd.'" ORDER BY cdd_id ASC';
    $res=setq($sql) or die($sql);
    while($rw=$res->fetch_array()){
      $s.='
-'.trim(utf8_decode(mb_strtolower($rw['cdd_nmb'])));
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
  $pdf->SetAutoPageBreak(true,5);
  $pdf->SetFont('Arial','',9);

  $empresa = busca($_GET['idcotiza'],'crm_cotizaciones INNER JOIN crm_tableros ON ct_id = cc_tablero','cc_id','ct_empresa');
  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  include_once('../modulos/cotizaciones.php');
  $cotiza= new modelcotizaciones();
  $cotiza->select($_GET['idcotiza']);

  $pdf->AddFont('kalinga','','kalinga.php');
  $pdf->AddFont('kalingab','','kalingab.php');
  $pdf->SetFont('kalinga','',10);
  $pdf->SetTextColor(0,0,0);

//imprimir el numero de la cotizacion
$pdf->Cell(23,8,utf8_decode('Cotización:'),0,0);
$pdf->Cell(0,8,utf8_decode($cotiza->folio),0,1);
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$pdf->SetFont('kalingab','',10);
$pdf->Cell(180,6,'Pachuca de Soto, Hgo. '.date('d').' de '.$meses[date('n')-1].' de '.date('Y').'',0,1,'R');

$pdf->Cell(22,8,utf8_decode('Atención:'),0,0,'L');
$pdf->SetFont('kalinga','',10);

$pdf->Cell(0,8,utf8_decode($cotiza->destino),0,1,'L');

$texto=utf8_decode('En atención a su solicitud, me permito enviarle la siguiente propuesta correspondiente a los productos o servicios de su interés.');
$pdf->Cell(4);
$pdf->MultiCell(180,5,$texto,0,'J');
 //$pdf->Ln(3);
//tabla de los productos de la cotizacion
  $pdf->Cell(4);
  $sql='SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion='.$cotiza->id.' ORDER BY cdm_id ASC';
  $resultado=setq($sql) or die($sql);
  $pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
  $pdf->SetFont('kalingab','',8);
  $pdf->SetTextColor(255, 255, 255);

//###definir el ancho de columna
  $col1=15; $col2=130; $col3=22; $col4=23; $col5=24;
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
$viva = busca(1,'configuracionesp','c_id','c_iva');
$poriva = $viva/100;
$ivat = 0;
$totalf = 0;
while($row=$resultado->fetch_array()){
  $iva=0;
  switch ($cotiza->diva) {
    //cuando se requiere desplegar el iva
    case "1":
      if($row['cdm_iva']=="1"){
        $iva=0;
        $iva=($row['cdm_cantidad']*$row['cdm_precio'])*$poriva;
        $precioProducto=$row['cdm_precio'];
      }
      else{
        $iva=0;
        if($cotiza->mtotal == 1){
          $importeind = $row['cdm_cantidad']*$row['cdm_precio'];
          $iva=$importeind-(($row['cdm_cantidad']*$row['cdm_precio']/($viva + 100))*100);
          $precioProducto=$row['cdm_precio']/(1+$poriva);
          //echo '<br><br>'.$row['cd_precio'].'->'.$iva.'<<<'.$row['cd_nmbarticulo'];
        }
        else{
          $iva=($row['cdm_cantidad']*$row['cdm_precio'])*$poriva;
          $precioProducto=$row['cdm_precio'];
        }
      }
      break;
    //cuando no se requiere desplegar el iva
    case "0":
      /* if($row['cd_iva'] == "1"){
        $iva=0;
        $iva=($row['cdm_cantidad']*$row['cdm_precio'])*$poriva;
        $precioProducto=$row['cdm_precio']*(1+$poriva);
      }
      else{ */
        $iva=0;
        $precioProducto=$row['cdm_precio'];
      /* } */
    break;
    case "N":
      $precioProducto=$row['cdm_precio'];
    break;
    default:
    //echo "Your favorite color is neither red, blue, nor green!";
  }
  $ivat+=$iva;
  $total=$precioProducto*$row['cdm_cantidad'];
  $totalf+=$total;
  //Trae la ruta de la imagen de cada articulo
 /*  if($row['cdm_modelo'] == "") {
    $cb = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_cb');
    $ruta = 'productos/'.busca($cb, 'imagenes', 'i_idproducto','CONCAT(i_nmb,".",i_ext)');
  } else {
    $ruta = busca($row['cdm_articulo'], 'articulos_descargas', 'ad_modelo = "'.$row['cdm_modelo'].'" AND ad_articulo', 'ad_ruta');
  }
  $destination_path = getcwd().DIRECTORY_SEPARATOR; */

  //echo $total.'--'.$totalf.'<br>';
  //if($pdf->PageNo() == 1)
  $pdf->Cell(4);
  $pdf->prod = $pdf->PageNo();
  $pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla
  //Para imprimir la tabla, envia los registros al metodo array del archivo mc_table.php
  $pdf->SetAligns(array('C','J','R','R'));
  $pdf->RowMin(array($row['cdm_cantidad'],$pdf->paquete($row['cdm_articulo'],utf8_decode($row['cdm_nmbarticulo']),$row['cdm_id']),'$'.number_format($precioProducto,2),'$'.number_format($total,2)),false);
  $bandera=!$bandera;
}

$vertotal = busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_mtotal');
if($vertotal == 1){
  //consulta base de datos
     $pdf->Cell(4);
      if($cotiza->diva == '0' && $cotiza->descuento == 0 && $cotiza->precioenvio == 0){
        $pdf->Cell(17,6,' ','T',0,'C');
        $pdf->Cell(128,6,' ','T',0,'L');
        $pdf->Cell(22,6,'Total a Pagar',1,0,'L',$bandera);
        $pdf->Cell(23,6,'$'.str_pad(number_format($cotiza->importe,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
        $pdf->Ln(6);
      }
      else{
        $pdf->Cell(17,6,' ','T',0,'C');
        $pdf->Cell(128,6,' ','T',0,'L');
        $pdf->Cell(22,6,'Subtotal',1,0,'L',$bandera);
        $pdf->Cell(23,6,'$'.str_pad(number_format($totalf,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
        $pdf->Ln(6);
      }

  if($cotiza->descuento>0){
    $impdesc = ($totalf*($cotiza->descuento/100));
    $pdf->Cell(149);
    $bandera = !$bandera;
    $pdf->Cell(22,6,'Descuento '.number_format($cotiza->descuento,0).'%',1,0,'L',$bandera);
    $pdf->Cell(23,6,'$'.str_pad(number_format($impdesc,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
    $pdf->Ln(6);
  }
  if($cotiza->diva == '1'){
    $pdf->Cell(149);  $bandera = !$bandera;
    $pdf->Cell(22,6,'IVA',1,0,'L',$bandera);
    $pdf->Cell(23,6,'$'.str_pad(number_format($cotiza->iva,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
    $pdf->Ln(6);
  }
  if($cotiza->precioenvio != 0){
    $pdf->Cell(149);  $bandera = !$bandera;
    $pdf->Cell(22,6,'Costo de envio',1,0,'L',$bandera);
    $pdf->Cell(23,6,'$'.str_pad(number_format($cotiza->precioenvio,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
    $pdf->Ln(6);
  }
  if($cotiza->descuento > 0 || $cotiza->diva == '1' || $cotiza->precioenvio > 0){
      $totalf = $totalf + $cotiza->precioenvio - $impdesc;
      $pdf->Cell(149);
      $bandera = !$bandera;
      $pdf->Cell(22,6,'Total a Pagar',1,0,'L',$bandera);
      $pdf->Cell(23,6,'$'.str_pad(number_format($totalf,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
  }
  //cierrra la tabla
}

 //condiciones de servicio
$pdf->SetFont('kalinga','',10);
$pdf->Ln(5);
$sql='SELECT * FROM crm_cotizaciones_condiciones
      WHERE cf_estatus="A" AND cf_cotizacion = "'.$_GET['idcotiza'].'" ORDER BY cf_orden';
$resultado=setq($sql) or die($sql);
$resultadon=setq($sql) or die($sql);
if($resultadon->num_rows > 0) $condiciones='Condiciones Comerciales'; else $condiciones = "";

$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0,5,$condiciones,0,1,'L');
$pdf->SetFont('kalinga','',6);


While($row=$resultado->fetch_array()){
  $pdf->cell(10);
     //convertir en minuscula el nombre
    //$condicion = strtolower($row['cf_descripcion']);
    //convertir en mayuscula la primera letra de cada palabra
    //$condicion = ucwords($condicion);
    $pdf->MultiCell(177,3,utf8_decode($row['cf_descripcion']),0,'J');
}

if($cotiza->msi == "1"){
  $sqlt = 'SELECT * FROM tabuladormsi WHERE t_estatus = "A" ORDER BY t_nmb ASC';
  $resultt = setq($sqlt) or die($sqlt);
  if($resultt->num_rows > 0){
    //$pdf->Cell(124);
    $pdf->Cell(5);
    $pdf->Cell(60,6,utf8_decode('Pagos con tarjetas de débito o crédito'),1,0,'C',true);
    $pdf->Ln(6);
    while($rwt = $resultt->fetch_array()){
      $ttotal = $cotiza->importe * (1+($rwt['t_porcentaje']/100));
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

$pdf->SetFont('kalinga','',7);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(177,4,utf8_decode('Esperando que el contenido de la presente cubra sus expectativas me pongo a sus órdenes para cualquier duda o aclaración.'),0,'J');
$pdf->Ln(1);
$pdf->MultiCell(177,4,utf8_decode('Los precios pueden variar sin previo aviso o hasta agotar existencias'),0,'J');
$pdf->MultiCell(177,4,utf8_decode('Se aplican restricciones'),0,'J');
$pdf->Ln(1);
$pdf->MultiCell(190,5,utf8_decode('Cotización válida hasta el '.date('d-m-Y',strtotime($cotiza->ffin))),0,'R');

//impresion de los datos del usuario
$sql='SELECT * FROM usuarios WHERE u_id="'.$cotiza->responsable.'"';
$result=setq($sql) or die($sql);
$fila=$result->fetch_array();
$pdf->SetFont('kalinga','',9);
$pdf->cell(160);
//convertir en minuscula el nombre
$nombre = mb_strtolower($fila['u_nmb'].' '.$fila['u_apellidos']);
//convertir en mayuscula la primera letra de cada palabra
$nombre = ucwords($nombre);
$pdf->Cell(30,5,utf8_decode($nombre),0,1,'R');
$pdf->cell(160);
$pdf->Cell(30,5,$fila['u_mailcorp'],0,1,'R');
$pdf->cell(160);
$pdf->Cell(30,5,'Cel. '.$fila['u_telefono'],0,1,'R');

$mayork = $pdf->GetY();
//echo $x;
if( $mayork > 245){
  $pdf->addpage();
}


if(busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_cuentas') == "1"){
  $sql = 'SELECT * FROM cuentas WHERE cu_enviacotiza = "1"
          ORDER BY cu_orden ASC';
  $result = setq($sql);
  $line = 0;

  $pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
  $pdf->SetTextColor(255, 255, 255);
  $pdf->SetFont('kalingab','',9);

  $pdf->Cell(195,5,'NUESTRAS CUENTAS BANCARIAS',0,0,'C',true);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla

  $pdf->Ln();
  $yor = $pdf->GetY();
  //echo $pdf->GetY();
  $x = 10;
  $y = $yor;
  
  while($row = $result->fetch_array()){
    $line++;

    $pdf->Rect($x,$y,65,20);
    $pdf->SetXY($x,$y);
    $pdf->SetFont('kalingab','',8);
    $pdf->Cell(65,4,utf8_decode($row['cu_propietario']),1,1,'C',true);
    $y+=4;
    $pdf->SetXY($x,$y);

    $pdf->Cell(15,4,'BANCO',0,0,'L');
    $pdf->SetFont('kalinga','',8);
    $pdf->Cell(50,4,utf8_decode($row['cu_banco']),0,1,'L');
    $y+=4;
    $pdf->SetXY($x,$y);

    $pdf->SetFont('kalingab','',8);
    $pdf->Cell(15,4,'CUENTA',0,0,'L');
    $pdf->SetFont('kalinga','',8);
    $pdf->Cell(50,4,$row['cu_cuenta'],0,1,'L');
    $y+=4;
    $pdf->SetXY($x,$y);

    $pdf->SetFont('kalingab','',8);
    $pdf->Cell(15,4,'CLABE',0,0,'L');
    $pdf->SetFont('kalinga','',8);
    $pdf->Cell(50,4,$row['cu_clabe'],0,1,'L');
    $y+=4;
    $pdf->SetXY($x,$y);

    if($row['cu_numtarjeta'] && $row['cu_numtarjeta'] != "0"){
      $pdf->SetFont('kalingab','',8);
      $pdf->Cell(15,4,'TARJETA',0,0,'L');
      $pdf->SetFont('kalinga','',8);
      $pdf->Cell(50,4,$row['cu_numtarjeta'],0,1,'L');
    }
    $pdf->Ln(4);

    $x=($line*65)+10;
    if($line%3 == 0){
      $line = 0;
      $x = 5;
    }
    else{
      $y=$yor;
    }
    $pdf->SetXY($x,$y);
  }

}

if($cotiza->estatus == "A" || $cotiza->estatus == "E")
  $pdf->Output(busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_nmb').'.pdf','I');
else
$pdf->Output();
?>
