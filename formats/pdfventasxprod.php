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
    if(!empty($_GET['categoria'])) $categoria = $_GET['categoria']; else $categoria = NULL;
    if(!empty($_GET['almacen'])) $almacenes = $_GET['almacen']; else $almacenes = NULL;
    $empresa = $_SESSION['emp'];

    include_once('../modulos/config.php');
    $emp = new modelconfig();
    $emp->select($_SESSION['emp']);

    if(file_exists('../'.$emp->logo) && $emp->logo != NULL) $logo = '../'.$emp->logo;
    else $logo = NULL;

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
    // T�tulo

  //imprimir el numero de la cotizacion
  $this->SetFont('kalingab','',12);
  $this->Cell(35);
  $this->Cell(110,32,utf8_decode('REPORTE DE VENTAS POR PRODUCTO'),0,0,"C");
  $this->Ln();

  $this->SetFont('kalinga','',9);
  $this->Cell(4);
  $this->SetFillColor(91, 155, 213);
  $this->SetTextColor(255,255,255);
  $this->Cell(25,6,utf8_decode("DESDE:"),1,0,'C',true);
  $this->SetTextColor(0,0,0);
  $this->Cell(70,6,fecha_formato(utf8_decode($_GET['fini']), true, true),1,0);
  $this->SetFillColor(91, 155, 213);
  $this->SetTextColor(255,255,255);
  $this->Cell(25,6,utf8_decode("HASTA:"),1,0,'C',true);
  $this->SetTextColor(0,0,0);
  $this->Cell(66,6,fecha_formato(utf8_decode($_GET['ffin']), true, true),1,0);
  $this->Ln();
  $this->Ln();

  // Salto de l�nea
  $this->Ln();
}

// Pie de p�gina
function Footer()
{
  // Posici�n: a 1,5 cm del final
  $this->SetY(-15);
  $this->SetFont('kalinga','',8);
  // N�mero de p�gina
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
  //Emitir un salto de p�gina primera, si es necesario
  $this->CheckPageBreak($h);
    if($bandera==true) $rellenar ='FD';
    if($bandera==false) $rellenar ='D';
  //Dibuja las celdas de la fila
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
    $this->MultiCell($w,5,$data[$i],0,$a);
    //Put the position to the right of the cell
    $this->SetXY($x+$w,$y);
  }
  //Ir a la siguiente l�nea
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
    //Emitir un salto de p�gina primera, si es necesario
    $this->CheckPageBreak($h);
    if($bandera==true) $rellenar ='FD';
    if($bandera==false) $rellenar ='D';
    //Dibuja las celdas de la fila
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
    $this->MultiCell($w,4,$data[$i],0,$a);
    //Put the position to the right of the cell
    $this->SetXY($x+$w,$y);
  }
  //Ir a la siguiente l�nea
  $this->Ln($h);
    $bandera=!$bandera;
}

function CheckPageBreak($h)
{
  //Si la altura h provocar�a un desbordamiento, a�adir una nueva p�gina de inmediato
  if($this->GetY()+$h>$this->PageBreakTrigger)
    $this->AddPage($this->CurOrientation);
    if($this->prod != $this->PageNo()){
      $this->Cell(4);
    }
}

function NbLines($w,$txt)
{
  //Calcula el n�mero de l�neas de un MultiCell de anchura w tomar�
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

$pdf->AddFont('kalinga','','kalinga.php');
$pdf->AddFont('kalingab','','kalingab.php');
$pdf->SetFont('kalinga','',8);
$pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
$pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
$pdf->SetFont('kalingab','',8);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(4);

$col1=36; $col2=40; $col3=38; $col4=38; $col5=38;
$pdf->Cell($col1,7,'Modelo',1,0,'C',true);
$pdf->Cell($col2,7,'Producto',1,0,'C',true);
$pdf->Cell($col3,7,utf8_decode('Cantidad'),1,0,'C',true);
$pdf->Cell($col4,7,'Importe',1,0,'C',true);
$pdf->Cell($col5,7,'% del total',1,0,'C',true);
$pdf->Ln(7);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('kalinga','',8);
$bandera=true;
$pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla

// Movernos a la derecha
$pdf->SetDrawColor(100, 152, 201);
$pdf->SetFillColor(100, 152, 201);
$pdf->Line(160,10,160,30);
$pdf->SetTextColor(0,0,0);
$pdf->SetWidths(array($col1,$col2,$col3,$col4,$col5));

$sql = 'SELECT r_folio,rd_articulo,rd_modelo,rd_cantidad,remisiones.r_descuento,rd_precio, a_categoria, r_encargado
          FROM remisiones INNER JOIN remisionesd ON r_id = rd_remision INNER JOIN articulos ON rd_articulo = a_id 
          WHERE DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus IN ("A","F","FE")';

  if($_GET['vendedor']) $sql.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
  if($_GET['categoria']) {
    if($_GET['categoria'] == "T") $sql .= ' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
    else $sql.=' AND a_categoria = "'.$_GET['categoria'].'"';
  }
  $sql.=' ORDER BY r_faplica DESC';

  $resultremd = setq($sql);

  $sqls = 'SELECT SUM(r_total-r_iva-r_precioenvio) FROM remisiones
           WHERE DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus IN ("A","F","FE")';
  if($_GET['vendedor']) $sqls.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
  $results = setq($sqls);
  list($sumaprod) = $results->fetch_array();
  $totalventa = $sumaprod;

  $sql2 = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, r_encargado, a_nmb FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision 
              INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE") AND rc_ligado IS NULL';
  if($_GET['categoria']) {
      if($_GET['categoria'] == "T") $sql2 .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
      else $sql2.=' AND a_categoria = "'.$_GET['categoria'].'"';
  }            
  if($_GET['asesor']) $sql2.=' AND r_encargado = "'.$_GET['asesor'].'"';
  $sql2.=' GROUP BY rc_articulo, rc_modelo, r_encargado ORDER BY a_nmb ASC';
  
  $result2 = setq($sql);
  while($row2 = $result2->fetch_array()){
    $cant[$row2['rd_articulo']][$row2['rd_modelo']] = 0;
    $import[$row2['rd_articulo']][$row2['rd_modelo']] = 0;
    $vcat[$row2['r_encargado']][$row2['a_categoria']] = 0;
  }

  $result = setq($sql2);
  while($row = $result -> fetch_array()){
    $cant[$row['articulo']][$row['rc_modelo']]+= $row['cantidad'];
    $vcat[$row['r_encargado']][$row['a_categoria']]+= $row['cantidad'];
  }

  $resultremd = setq($sql);
  while($row = $resultremd->fetch_array()){
    $aplicadesc = busca($row['rd_articulo'], 'articulos', 'a_id', 'a_descuento');
    if($row['r_descuento'] == 0)
      $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*(($row['rd_precio']));
    else{
      if($aplicadesc == "1") $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*($row['rd_precio']-($row['rd_precio']*($row['r_descuento']/100)));
      else $import[$row['rd_articulo']][$row['rd_modelo']]+=$row['rd_cantidad']*(($row['rd_precio']));
    }
  }
  $numrems++;
  
  $result = setq($sql);
  $sumacant = 0;
  $sumatot = 0;
  $sumaporc = 0;
  $sql3 = 'SELECT  COUNT(*) cantidad, rc_articulo articulo, rc_modelo, a_tipoprod, a_categoria, r_encargado, a_nmb FROM remisionesc INNER JOIN remisiones ON r_id = rc_remision 
          INNER JOIN articulos ON a_id = rc_articulo WHERE  DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE") AND rc_ligado IS NULL';
if($_GET['categoria']) {
  if($_GET['categoria'] == "T") $sql3 .=' AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1")';
  else $sql3.=' AND a_categoria = "'.$_GET['categoria'].'"';
}            
if($_GET['asesor']) $sql3.=' AND r_encargado = "'.$_GET['asesor'].'"';
$sql3.=' GROUP BY rc_articulo, rc_modelo ORDER BY a_nmb ASC';
$result2 = setq($sql3);
while($row = $result2->fetch_array()){
  $i++;
  $porc = ($import[$row['articulo']][$row['rc_modelo']]/$totalventa)*100;
  $nmb = $row['a_nmb'];
  if($row['rc_modelo']){
    $nmb.= ' '.busca($row['rc_modelo'], 'articulos_variantes', 'av_articulo = "'.$row['articulo'].'" AND av_modelo' ,'av_nmb');
  }
  $pdf->RowMin(array(utf8_decode($row['rc_modelo']),utf8_decode($nmb),$cant[$row['articulo']][$row['rc_modelo']],'$'.number_format($import[$row['articulo']][$row['rc_modelo']], 2),number_format($porc,2).'%'),false);

  $sumacant+=$cant[$row['articulo']][$row['rc_modelo']];
  $sumatot+=$import[$row['articulo']][$row['rc_modelo']];
  $sumaporc+=$porc;
}
$pdf->AddFont('kalinga','','kalinga.php');
$pdf->AddFont('kalingab','','kalingab.php');
$pdf->SetFont('kalinga','',8);
$pdf->SetFillColor(91, 155, 213); //relleno del encabezado de una tabla
$pdf->SetDrawColor(132, 179, 223); //colorea las lineas de las celdas de la tabla
$pdf->SetFont('kalingab','',8);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(4);
$pdf->Cell($col1,7,'',1,0,'C',true);
$pdf->Cell($col2,7,'Totales',1,0,'C',true);
$pdf->Cell($col3,7,$sumacant,1,0,'C',true);
$pdf->Cell($col4,7,$sumatot,1,0,'C',true);
$pdf->Cell($col5,7,number_format($sumaporc,2).'%',1,0,'C',true);

$pdf->AddPage();
$pdf->SetAutoPageBreak(true,35);
$pdf->SetFont('Arial','',9);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('kalingab','',12);
$pdf->Cell(190,7,utf8_decode('Paquetes vendidos'),0,0,"C");
$pdf->Ln(10);

$pdf->AddFont('kalinga','','kalinga.php');
$pdf->AddFont('kalingab','','kalingab.php');
$pdf->SetFont('kalinga','',8);
$pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
$pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
$pdf->SetFont('kalingab','',8);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(4);

$col1=47; $col2=47; $col3=47; $col4=47; 
$pdf->Cell($col1,7,'Paquete',1,0,'C',true);
$pdf->Cell($col2,7,'Cantidad',1,0,'C',true);
$pdf->Cell($col3,7,utf8_decode('Importe'),1,0,'C',true);
$pdf->Cell($col4,7,'% al total',1,0,'C',true);
$pdf->Ln(7);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('kalinga','',8);
$bandera=true;
$pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla

// Movernos a la derecha
$pdf->SetDrawColor(100, 152, 201);
$pdf->SetFillColor(100, 152, 201);
$pdf->Line(160,10,160,30);
$pdf->SetTextColor(0,0,0);
$pdf->SetWidths(array($col1,$col2,$col3,$col4,$col5));

$sumpack = 0;
$summon = 0;
$sumport = 0;
$sql = 'SELECT rd_nmbarticulo, SUM(rd_cantidad) cantidad, SUM(rd_cantidad*rd_precio) precio FROM remisionesd INNER JOIN remisiones ON r_id = rd_remision INNER JOIN articulos ON a_id = rd_articulo
    WHERE a_tipoprod = "M" AND DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE")';
if($_GET['asesor']) $sql.=' AND r_encargado = "'.$_GET['asesor'].'"';
$sql .= ' GROUP BY rd_articulo, rd_modelo';
$result3 = setq($sql);
while($row = $result3 -> fetch_array()){
    $porc =($row['precio']/$totalventa)*100;
    $pdf->RowMin(array(utf8_decode($row['rd_nmbarticulo']),number_format($row['cantidad'],0),'$'.number_format($row['precio'], 2),number_format($porc,2).'%'),false);
   
    $sumpack += $row['cantidad'];
    $summon += $row['precio'];
    $sumport += $porc;
}

$pdf->AddFont('kalinga','','kalinga.php');
$pdf->AddFont('kalingab','','kalingab.php');
$pdf->SetFont('kalinga','',8);
$pdf->SetFillColor(91, 155, 213); //relleno del encabezado de una tabla
$pdf->SetDrawColor(132, 179, 223); //colorea las lineas de las celdas de la tabla
$pdf->SetFont('kalingab','',8);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(4);
$pdf->Cell($col1,7,'Totales',1,0,'C',true);
$pdf->Cell($col2,7,$sumpack,1,0,'C',true);
$pdf->Cell($col3,7,'$'.number_format($summon, 2),1,0,'C',true);
$pdf->Cell($col4,7,number_format($sumport, 2).'%',1,0,'C',true);
$pdf->Ln(10);

$sqlcat = 'SELECT * FROM categorias';
if($_GET['categoria']) {
  if($_GET['categoria'] == "T") $sqlcat .= ' WHERE cat_inflable = "1"';
  else $sqlcat .= ' WHERE cat_id = "'.$_GET['categoria'].'"';
}
$resultcat = setq($sqlcat);
$ancho = 190/($resultcat->num_rows+1);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('kalingab','',12);
$pdf->Cell(190,7,utf8_decode('Ventas por categoría'),0,0,"C");
$pdf->Ln(10);

$pdf->AddFont('kalinga','','kalinga.php');
$pdf->AddFont('kalingab','','kalingab.php');
$pdf->SetFont('kalinga','',8);
$pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
$pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
$pdf->SetFont('kalingab','',8);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(4);
$anchos = array(); 
$i = 1;
$anchos[0]= $ancho;
$pdf->Cell($ancho,7,'Asesor',1,0,'C',true);
$resultcat = setq($sqlcat);
while($row = $resultcat->fetch_array()){
  $anchos[$i] = $ancho;
  $i++;
  $pdf->Cell($ancho,7,utf8_decode($row['cat_nmb']),1,0,'C',true);
}

$pdf->Ln(7);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('kalinga','',8);
$bandera=true;
$pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla

// Movernos a la derecha
$pdf->SetDrawColor(100, 152, 201);
$pdf->SetFillColor(100, 152, 201);
$pdf->Line(160,10,160,30);
$pdf->SetTextColor(0,0,0);
$pdf->SetWidths($anchos);


$i = 0;
$sumpack = 0;
$summon = 0;
$sumport = 0;
$sumcat = array();
$datoscat = array();
$datoscat = array();
$sqlusu = 'SELECT DISTINCT(r_encargado), u_id, u_nmb, u_apellidos FROM remisiones INNER JOIN usuarios ON r_encargado = u_id WHERE DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"  AND r_estatus IN ("A","F","FE")';
if($_GET['asesor']) $sqlusu.=' AND r_encargado = "'.$_GET['asesor'].'"';
$resultu = setq($sqlusu);
while($rowu = $resultu -> fetch_array()){
  $j=0;
  $datoscat[$i][$j] = $rowu['u_nmb'].' '.$rowu['u_apellidos'];
  $resultcat = setq($sqlcat);
  while($rowcat = $resultcat -> fetch_array()){
    $j++;
    if(!$vcat[$rowu['r_encargado']][$rowcat['cat_id']]) $vcat[$rowu['r_encargado']][$rowcat['cat_id']] = 0;
    $datoscat[$i][$j]= $vcat[$rowu['r_encargado']][$rowcat['cat_id']];
    if(!$sumcat[$rowcat['cat_id']]) $sumcat[$rowcat['cat_id']] = 0;
    $sumcat[$rowcat['cat_id']]+= $vcat[$rowu['r_encargado']][$rowcat['cat_id']];
  }
  $i++;
}

for($i = 0; $i < COUNT($datoscat); $i++){
  $pdf->RowMin($datoscat[$i],false);
} 



$pdf->AddFont('kalinga','','kalinga.php');
$pdf->AddFont('kalingab','','kalingab.php');
$pdf->SetFont('kalinga','',8);
$pdf->SetFillColor(91, 155, 213); //relleno del encabezado de una tabla
$pdf->SetDrawColor(132, 179, 223); //colorea las lineas de las celdas de la tabla
$pdf->SetFont('kalingab','',8);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(4);
$pdf->Cell($ancho,7,'Totales',1,0,'C',true);
$resultcat = setq($sqlcat);
while($rowcat = $resultcat -> fetch_array()){
  $pdf->Cell($ancho,7,$sumcat[$rowcat['cat_id']],1,0,'C',true);
}


//$pdf->Output('cotizaciones/'.busca($_GET['idcotiza'],'cotizaciones','co_id','co_cotizacion').'.pdf','F');
$pdf->Output();
?>
