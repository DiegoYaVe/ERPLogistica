<?php
include('../funciones.php');
include('../lib/fpdf/fpdf.php');
session_start();
//ini_set('display_errors', 1);

class PDF extends FPDF
{
var $widths;
var $aligns;
var $alto;

  function Header()
  {
    if(isset($_GET['uuid'])) $_GET['idrem'] = busca($_GET['uuid'],'crm_cotizaciones','cc_uuid','cc_id');
    $sql='SELECT * FROM crm_cotizaciones WHERE cc_id="'.$_GET['idrem'].'"';
    $rs=setq($sql) or die($sql);
    $rw= $rs->fetch_array();
    $empresa = "1";

    include_once('../modulos/config.php');
    $emp = new modelconfig();
    $emp->select($empresa);

    $sql = 'SELECT * FROM almacenes WHERE a_id = "'.$rw['cc_almacen'].'"';
    $result = setq($sql);
    $rowalm = $result->fetch_array();
    
    $imglogo = busca('1', 'empresas', 'e_id', 'e_logo');
    
    if(!empty($imglogo)) $logo = '../'.$imglogo;
    else $logo = NULL;
    
    

    //if(file_exists('../'.$emp->logo) && $emp->logo != NULL) $logo = '../'.$emp->logo;
    //else $logo = NULL;

    include_once('../modulos/remisiones.php');
    $remi = new modelremisiones();
    $remi->select($_GET['idrem']);
    if($remi->estatus != "A" && $remi->estatus != "F" && $remi->estatus != "FE"){
      $this->Image('../assets/media/preview.png',5,30,200);
    }

    // Logo
    if($logo)
      $this->Image($logo,163,2,30);
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
  $empresa = "1";

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
  $h=9*$nb;
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

function RowMinImg($data,$bandera,$valida)
{
  //Calcular la altura de la fila
  $nb=0;
  for($i=0;$i<count($data);$i++)
    $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    $h=6*$nb;
    //Emitir un salto de p�gina primera, si es necesario
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
    //Guardar la posici�n actual
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

  //Ir a la siguiente l�nea
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
  if(($this->GetY()+$h)+10>$this->PageBreakTrigger)
    $this->AddPage($this->CurOrientation);
    if($this->prod != $this->PageNo()){
      $this->Ln(10);
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

function paquete($idrem, $guia)
{
  $s='';
    
  $sql='SELECT rc_articulo articulo, rc_modelo modelo, COUNT(*) cantidad FROM remisionesc WHERE rc_remision ="'.$idrem.'" AND rc_guia = "'.$guia.'"  GROUP BY rc_articulo, rc_modelo';
  $res =setq($sql) or die($sql);
  if($res -> num_rows < 1){
    $s .= utf8_decode('SIN ARTÍCULOS ASIGNADOS');
  } else {
    while($rw = $res->fetch_array()){
      $nmb = busca($rw['articulo'], 'articulos', 'a_id', 'a_nmb');
      if($rw['modelo'] != "") $nmb .= ' '.busca($rw['articulo'], 'articulos_variantes', 'av_modelo = "'.$rw['modelo'].'" AND av_articulo', 'av_nmb');
      $s.='
        - '.$rw['cantidad'].' '.trim(utf8_decode($nmb));
    }
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

  $empresa = "1";
  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  include_once('../modulos/remisiones.php');
  $remi= new modelremisiones();
  $remi->select($_GET['idrem']);

  $pdf->AddFont('kalinga','','kalinga.php');
  $pdf->AddFont('kalingab','','kalingab.php');
  $pdf->SetFont('kalinga','',10);
  $pdf->SetTextColor(0,0,0);

//imprimir el numero de la cotizacion
$pdf->Ln(10);
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$pdf->SetFont('kalingab','',17);
$pdf->Cell(190,6,utf8_decode('LISTADO DE GUÍAS'),0,1,'C');
$pdf->Ln(5);


 //$pdf->Ln(3);
//tabla de los productos de la cotizacion
$pdf->Cell(4);
$pdf->Ln(4);
$texto = busca('1', 'configuracionesp', 'c_id', 'c_textoguias');
$pdf->SetFont('kalingab','',11);
$pdf->MultiCell(190,7,utf8_decode($texto),0,'C');
$pdf->Ln(5);

$sql='SELECT * FROM `guias_articulos` WHERE `ga_cotizacion` = "'.$remi->id.'"';
$resultado=setq($sql) or die($sql);
$pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
$pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
$pdf->SetFont('kalingab','',8);
$pdf->SetTextColor(255, 255, 255);


//###definir el ancho de columna
$pdf->Cell(4);
$col1=42; $col2=97; $col3=21; $col4=21;
$pdf->Cell($col1,7,utf8_decode('Guía'),1,0,'C',true);
$pdf->Cell($col2,7,utf8_decode('Artículos que contiene'),1,0,'C',true);
$pdf->Cell($col3,7,utf8_decode('Paquetería'),1,0,'C',true);
$pdf->Cell($col4,7,'Sucursal',1,0,'C',true);
$pdf->Ln(7);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('kalinga','',8);
$bandera=true;
$pdf->SetFillColor(214, 230, 244);
//relleno alternado de la tabla

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
  // Agrega la imagen al PDF utilizando la función Image
  $imageX = 18  ; // Ajusta la posición X de la imagen
  $imageY = $pdf->GetY(); // Obtiene la posición Y actual del PDF
  $imageWidth = 25; // Ajusta el ancho de la imagen
  $imageHeight = 25; // El alto se ajustará automáticamente

  // Continúa con el resto de la fila
  $pdf->Cell(4);
  $pdf->prod = $pdf->PageNo();
  $pdf->SetFillColor(214, 230, 244); // Relleno alternado de la tabla
  $pdf->SetAligns(array('C', 'C', 'J', 'R', 'R'));
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
  $paqueteria = busca($row['ga_paqueteria'], 'paqueterias', 'p_id', 'p_nmb');
  $sucursal = busca($row['ga_sucursal'], 'paqueterias_sucursales', 'ps_id', 'ps_nmb');

  if(!$sucursal) $sucursal = "";
  
  /* $count = busca($row['ga_cotizacion'], 'crm_cotizacionesc', 'ccc_guia = "'.$row['ga_id'].'" AND ccc_cotizacion', 'COUNT(*)');
  if($count > 0) */
  $pdf->RowMinImg(array($row['ga_nmb'], $pdf->paquete($row['ga_cotizacion'], $row['ga_id']), $paqueteria, $sucursal), false, true);
  
  

  /* if (in_array($imageExtension, $allowedExtensions)) {
    // Agregar la imagen al PDF
    if(!empty($rutaimg)){
      $imageY += ($pdf->getAltura() + 2);
      $pdf->Image($rutaimg, $imageX, $imageY, $imageWidth, $imageHeight);
    }
  } */
  
  $bandera=!$bandera;
}

$pdf->Cell(10);
$pdf->Ln(4);
$texto = busca('1', 'configuracionesp', 'c_id', 'c_textoguias2');
$pdf->SetFont('kalingab','',11);
$pdf->MultiCell(190,7,utf8_decode($texto),0,'C');
$pdf->Ln(5);

$pdf->Ln(5);

$pdf->Output();
?>
