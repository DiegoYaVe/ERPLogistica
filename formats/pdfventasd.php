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
  $this->Cell(110,32,utf8_decode('REPORTE GENERAL DE VENTAS'),0,0,"C");
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

$col1=19; $col2=16; $col3=28; $col4=27; $col5=18; $col6=18; $col7=18; $col8=22; $col9=25;
$pdf->Cell($col1,7,'Folio',1,0,'C',true);
$pdf->Cell($col2,7,'Fecha',1,0,'C',true);
$pdf->Cell($col3,7,utf8_decode('Vendedor'),1,0,'C',true);
$pdf->Cell($col4,7,'Cliente',1,0,'C',true);
$pdf->Cell($col5,7,'Telefono',1,0,'C',true);
$pdf->Cell($col6,7,'Subtotal',1,0,'C',true);
$pdf->Cell($col7,7,'Descuento',1,0,'C',true);
$pdf->Cell($col8,7,utf8_decode('Total'),1,0,'C',true);
$pdf->Cell($col9,7,utf8_decode('Envío'),1,0,'C',true);
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
$pdf->SetWidths(array($col1,$col2,$col3,$col4,$col5, $col6, $col7, $col8, $col9));

$totalsubtotal = 0;
$totaldescuento = 0;
$totalfinal = 0;
$totalenvios = 0;
$numrems = 0;

$sql = 'SELECT * FROM remisiones INNER JOIN remisionesd ON rd_remision = r_id WHERE rd_articulo != "0" AND DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'" AND r_estatus != "N"';
if($_GET['cliente']) $sql.=' AND r_cliente IN (SELECT c_id FROM crm_clientes WHERE 
  (c_nmb LIKE "%'.$_GET['cliente'].'%" OR c_alias LIKE "%'.$_GET['cliente'].'%" OR c_apellidos LIKE "%'.$_GET['cliente'].'%") )';
if($_GET['vendedor']) $sql.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
if($_GET['estatus']){
  if($_GET['estatus'] == "V") $sql.=' AND r_estatus IN ("A","F","FE")';
  else if ($_GET['estatus'] == "F") $sql.=' AND r_estatus IN ("F","FE")';
  else if ($_GET['estatus'] == "P") $sql.=' AND r_estatus IN ("P","D")';
  else $sql.=' AND r_estatus = "'.$_GET['estatus'].'" ';
}
$sql.='GROUP BY r_id ORDER BY r_faplica DESC';
$resultrem = setq($sql);
while($row = $resultrem -> fetch_array()){
  $numrems++;
  $subtotal = ($row['r_total']-$row['r_iva']+$row['r_mdescuento']-$row['r_precioenvio']);
  $totalrem = ($row['r_total']-$row['r_iva']-$row['r_precioenvio']);

  $cliente = busca($row['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb').' '.busca($row['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
  $telefono = busca($row['r_cliente'], 'crm_clientes', 'c_id', 'c_telefono2');
  $pdf->RowMin(array(utf8_decode($row['r_folio']),date('d/m/Y',strtotime($row['r_faplica'])),utf8_decode(busca($row['r_encargado'],'usuarios','u_id','CONCAT(u_nmb, " ", u_apellidos)')),utf8_decode($cliente), number_format($elefono,2), number_format($subtotal,2), number_format($row['r_mdescuento'],2), number_format($totalrem,2), number_format($row['r_precioenvio'],2)),false);
  $totalsubtotal+=$subtotal;
  $totaldescuento+=$row['r_mdescuento'];
  $totalfinal+=$totalrem;
  $totalenvios+=$row['r_precioenvio'];
}
if($totalsubtotal > 0) {
  $pordesc = ($totaldescuento*100)/$totalsubtotal;
  $porfinal = ($totalfinal*100)/$totalsubtotal;
} else {
  $pordesc = 0;
  $porfinal = 0;
}


$pdf->RowMin(array('Numero de ventas:',$numrems,'','', 'Totales:', number_format($totalsubtotal, 2), number_format($totaldescuento, 2), number_format($totalfinal, 2), number_format($totalfinal, 2)),false);

$sqlnif = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON rc_remision = r_id INNER JOIN articulos ON rc_articulo = a_id WHERE r_estatus IN ("A","F","FE")
  AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") AND DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"';
if($_GET['vendedor']) $sqlnif.=' AND r_encargado = "'.$_GET['vendedor'].'"';
$sqlnif.= ' GROUP BY r_id';
$resultnif = setq($sqlnif);
$infvf = $resultnif -> num_rows;  

$pdf->RowMin(array('Ventas con inflable:',$infvf,'','', 'Totales porcentajes:', '100%', number_format($pordesc, 2).'%', number_format($porfinal, 2).'%', ''),false);

$sqlni = 'SELECT COUNT(*) FROM remisionesc INNER JOIN remisiones ON rc_remision = r_id INNER JOIN articulos ON rc_articulo = a_id WHERE r_estatus IN ("A","F","FE")
  AND a_categoria IN (SELECT cat_id FROM categorias WHERE cat_inflable = "1") AND DATE(r_faplica) BETWEEN "'.$_GET['fini'].'" AND "'.$_GET['ffin'].'"';
if($_GET['vendedor']) $sqlni.=' AND r_encargado = "'.$_GET['vendedor'].'" ';
$resultni = setq($sqlni);
list($infv) = $resultni -> fetch_array();  
$pdf->RowMin(array('Inflables vendidos:',$infv,'','', '', '', '', '', ''),false);

//$pdf->Output('cotizaciones/'.busca($_GET['idcotiza'],'cotizaciones','co_id','co_cotizacion').'.pdf','F');
$pdf->Output();
?>
