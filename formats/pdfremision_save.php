<?php
include('../funciones.php');
include('../lib/fpdf/pdf_js.php');
session_start();
//ini_set('display_errors', 1);

class PDF extends PDF_JavaScript
{
var $widths;
var $aligns;

  function Header()
  {
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
  $this->Cell(110,32,utf8_decode('NOTA DE REMISIÓN'),0,0,"C");
  $this->Ln();

  $this->SetFont('kalingab','',11);
  $this->Cell(4);
  $this->SetFillColor(91, 155, 213);
  $this->SetTextColor(255,255,255);
  $this->Cell(184,6,utf8_decode($emp->nmb),1,0,"C",1);
  $this->SetFont('kalinga','',9);
  $this->Ln();
  $this->Cell(4);
  $this->SetTextColor(0,0,0);
  $this->Cell(184,6,utf8_decode($emp->calle.' '.$emp->nume.' '.$emp->numi.' '.$emp->colonia.' '.$emp->ciudad.' '.$emp->estado.' '.$emp->cp),1,0);
  $this->Ln();

  if($remis->estatus == "N") $fechar = $remis->falta; else $fechar = $remis->faplica;

  $this->Cell(4);
  $this->SetFillColor(91, 155, 213);
  $this->SetTextColor(255,255,255);
  $this->Cell(41,6,utf8_decode("No. de remisión:"),1,0,'C',true);
  $this->SetTextColor(0,0,0);
  $this->Cell(51,6,utf8_decode(str_pad($remis->folio,6,"0",STR_PAD_LEFT)),1,0);
  $this->SetFillColor(91, 155, 213);
  $this->SetTextColor(255,255,255);
  $this->Cell(41,6,utf8_decode("Fecha de venta:"),1,0,'C',true);
  $this->SetTextColor(0,0,0);
  $this->Cell(51,6,utf8_decode(fecha_formato(date('Y-m-d',strtotime($fechar)),false,true)),1,0,'R');
  $this->Ln();

  $this->Cell(4);
  $this->SetFillColor(91, 155, 213);
  $this->SetTextColor(255,255,255);
  $this->Cell(41,6,utf8_decode("Cliente:"),1,0,'C',true);
  $this->SetTextColor(0,0,0);
  $this->Cell(143,6,utf8_decode(busca($remis->cliente,'crm_clientes','c_id','CONCAT(c_nmb," ",c_apellidos)')),1,0);
  $this->Ln();
  $this->Ln();
  // Salto de línea
  $this->Ln();
}

// Pie de página
function Footer()
{
  $empresa = busca($_GET['id'],'remisiones','r_id','r_empresa');

  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  // Posición: a 1,5 cm del final
  $this->SetY(-27);
  $this->SetFont('kalinga','',11);
/*
  $this->Cell(0,5,utf8_decode('José Luis Reyes Benítez'),0,1,'C');
  $this->Ln(2);
  $this->Cell(0,5,'REBL8905188J8',0,1,'C');
  $this->Ln();
*/
  $this->SetFont('kalinga','',11);
  $this->Cell(0,3,'___________________________________',0,1,'C');
  //$this->Cell(0,5,utf8_decode($fila['c_id']),0,1,'C');
  $this->Cell(0,5,utf8_decode($emp->web),0,1,'C');
  // Arial italic 8
  $this->SetFont('kalinga','',8);

  $this->SetTextColor(204,0,0);
  $this->Cell(0,5,'Este no es un comprobante fiscal',0,1,'C');

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
    $s=$nmb.'';

    $sql='SELECT CONCAT(a_nmb,"  - ",a_uso," ",a_accesorio," ",a_tipoactivo," ",a_capacidad," ",a_presion," ",a_umedida) as Activo, aa_noserie
          FROM remision_activo INNER JOIN activo_almacen ON aa_id = ra_activo INNER JOIN activos ON a_id = aa_activo
          WHERE ra_remision="'.$id.'" AND ra_idr = "'.$idcd.'"';
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
  $pdf->AliasNbPages();
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true,35);
  $pdf->SetFont('Arial','',9);


  $empresa = busca($_GET['id'],'remisiones','r_id','r_empresa');
  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  include_once('../modulos/remisiones.php');
  $remis = new modelremisiones();
  $remis->select($_GET['id']);

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
  $pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
  $pdf->SetFont('kalingab','',8);
  $pdf->SetTextColor(255, 255, 255);

//###definir el ancho de columna
  $col1=33; $col2=15; $col3=97; $col4=22; $col5=23; $col6=24;
  $pdf->Cell($col1,7,'Imagen',1,0,'C',true);
  $pdf->Cell($col2,7,'Cantidad',1,0,'C',true);
  $pdf->Cell($col3,7,utf8_decode('Descripción'),1,0,'C',true);
  $pdf->Cell($col4,7,'Unitario',1,0,'C',true);
  $pdf->Cell($col5,7,'Total',1,0,'C',true);
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
$pdf->SetWidths(array($col1,$col2,$col3,$col4,$col5));
srand(microtime()*1000000);
$bandera=true;
$viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
$poriva = $viva/100;
$ivat = 0;
$totalf = 0;
while($row=$resultado->fetch_array()){
  $iva=0;
  switch($remis->diva){
    case "1":
      if($row['rd_iva']=="1"){
        $iva=0;
        $iva=($row['rd_cantidad']*$row['rd_precio'])*$poriva;
        $precioProducto=$row['rd_precio'];
      }else{
        $precioProducto=($row['rd_precio']/(1+($poriva)));
      }
      break;
    case "0":
      if($row['rd_iva'] == "1"){
        $iva = 0;
        $iva = ($row['rd_cantidad'] * $row['rd_precio']) * $poriva;
        $precioProducto = $row['rd_precio'] * (1+$poriva);
      }else{
        $iva = 0;
        $precioProducto = $row['rd_precio'];
      }
      break;
  }
  //cuando se requiere desplegar el iva
  $pordes = busca($row['rd_remision'],'remisiones','r_id','r_descuento');
  $total=$precioProducto*$row['cdm_cantidad'];
  $desc = busca($row['cdm_articulo'], 'articulos', 'a_id', 'a_descuento');
  if($desc == "1"){
    $descuni = $total*($pordes/100);
    $impmd = $total-$descuni;
    $subtotal=$impmd;
    $impdesc+=$descuni;
  } else {
    $subtotal=$total;
  }
  $ivat+=$iva;
  $total=$precioProducto*$row['rd_cantidad'];
  //echo $total.'--'.$totalf.'<br>';
  $totalf+=$total;

  if(empty($row['cdm_modelo'])){
    $query = 'SELECT CONCAT("../img/productos/",i_nmb, ".", i_ext) AS nombre_completo FROM imagenes WHERE i_idp = "'.$row['rd_articulo'].'" ORDER BY i_idimg DESC LIMIT 1';
  } else{
    $query = 'SELECT CONCAT("../img/",ad_ruta) AS nombre_completo FROM articulos_descargas WHERE ad_articulo = "'.$row['rd_articulo'].'" AND ad_modelo = "'.$row['rd_modelo'].'" ORDER BY ad_id DESC LIMIT 1';
  }
  $resultq = setq($query);
  list($rutaimg) = $resultq->fetch_array();


  // Agrega la imagen al PDF utilizando la función Image
  $imageX = 18  ; // Ajusta la posición X de la imagen
  $imageY = $pdf->GetY(); // Obtiene la posición Y actual del PDF
  $imageWidth = 25; // Ajusta el ancho de la imagen
  $imageHeight = 25; // El alto se ajustará automáticamente

  //if($pdf->PageNo() == 1)
  $pdf->Cell(4);
  $pdf->prod = $pdf->PageNo();
  $pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla
  //Para imprimir la tabla, envia los registros al metodo array del archivo mc_table.php
  $pdf->SetAligns(array('C','C','J','R','R'));
  // Validar la extensión del archivo de imagen
  $allowedExtensions = ['jpg', 'jpeg', 'png']; // Lista de extensiones permitidas
  $imageExtension = strtolower(pathinfo($rutaimg, PATHINFO_EXTENSION));
  $valida = false;
  if (!in_array($imageExtension, $allowedExtensions)) {
    // Agregar la imagen al PDF
    $valida = true;
  }

  if(empty($rutaimg)){
    $valida = true;
  }
  
  $pdf->RowMinImg(array("", $row['rd_cantidad'],$pdf->paquete($row['rd_remision'],utf8_decode($row['rd_nmbarticulo']),$row['rd_id']),'$'.number_format($precioProducto,2),'$'.number_format($total,2)),false,$valida);

  if (in_array($imageExtension, $allowedExtensions)) {
    // Agregar la imagen al PDF
    if(!empty($rutaimg)){
      $imageY += ($pdf->getAltura() + 2);
      $pdf->Image($rutaimg, $imageX, $imageY, $imageWidth, $imageHeight);
    }
  }
  
  $bandera=!$bandera;
}

$vertotal = 1;
if($vertotal == 1){
  //consulta base de datos
     $pdf->Cell(4);
      if($remis->diva == '0' && $remis->descuento == 0 && $remis->precioenvio == 0){
        $pdf->Cell(16,6,' ','T',0,'C');
        $pdf->Cell(15,6,' ','T',0,'C');
        $pdf->Cell(114,6,' ','T',0,'L');
        $pdf->Cell(22,6,'Total a Pagar',1,0,'L',$bandera);
        $pdf->Cell(23,6,'$'.str_pad(number_format($totalf,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
        $pdf->Ln(6);
      }else{
        $pdf->Cell(16,6,' ','T',0,'C');
        $pdf->Cell(15,6,' ','T',0,'C');
        $pdf->Cell(114,6,' ','T',0,'L');
        $pdf->Cell(22,6,'Subtotal',1,0,'L',$bandera);
        $pdf->Cell(23,6,'$'.str_pad(number_format($totalf,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
        $pdf->Ln(6);
      }
      if($remis->descuento > 0){
        $impdesc = $remis->mdescuento;
        $pdf->Cell(149);
        $bandera = !$bandera;
        $pdf->Cell(22,6,'Descuento '.number_format($remis->descuento,0).'%',1,0,'L',$bandera);
        $pdf->Cell(23,6,'$'.str_pad(number_format($impdesc,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
        $pdf->Ln(6);
      }
  if($remis->diva == '1'){
    $pdf->Cell(149);  $bandera = !$bandera;
    $pdf->Cell(22,6,'IVA',1,0,'L',$bandera);
    $pdf->Cell(23,6,'$'.str_pad(number_format($remis->iva,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
    $pdf->Ln(6);
  }
  if($remis->precioenvio > 0){
    $pdf->Cell(149);  $bandera = !$bandera;
    $pdf->Cell(22,6,utf8_decode('Costo de envío'),1,0,'L',$bandera);
    $pdf->Cell(23,6,'$'.str_pad(number_format($remis->precioenvio,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
    $pdf->Ln(6);
  }
  if($remis->descuento > 0 || $remis->diva == '1' || $remis->precioenvio > 0){
      $pdf->Cell(149);
      $bandera = !$bandera;
      $pdf->Cell(22,6,'Total a Pagar',1,0,'L',$bandera);
      $pdf->Cell(23,6,'$'.str_pad(number_format($remis->total+$remis->precioenvio,2),"0"," ",STR_PAD_RIGHT),1,0,'R',$bandera);
  }
  //cierrra la tabla
}


$pdf->SetY(15);
$pdf->Output('docs/remisiones/'.busca($_GET['id'],'remisiones','r_id','r_folio').'.pdf','F');
?>