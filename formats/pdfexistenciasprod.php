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
    if(!empty($_GET['almacen'])) $almacenes = $_GET['almacen']; else $almacenes = NULL;
    $empresa = $_SESSION['emp'];

    include_once('../modulos/config.php');
    $emp = new modelconfig();
    $emp->select(1);

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
  $this->Cell(80,32,utf8_decode('REPORTE DE EXISTENCIAS DE ALMACEN DE PRODUCCIÓN'),0,0,"C");
  $this->Ln();

  if($almacenes) $nmbalmacen = busca($almacenes,'almacenes','a_id','a_nmb'); else $nmbalmacen = "TODOS";

  $this->SetFont('kalinga','',9);
  $this->Cell(4);
  $this->SetFillColor(91, 155, 213);
  $this->SetTextColor(255,255,255);
  $this->Cell(25,6,utf8_decode("Almacen:"),1,0,'C',true);
  $this->SetTextColor(0,0,0);
  $this->Cell(70,6,utf8_decode($nmbalmacen),1,0);
  $this->SetFillColor(91, 155, 213);
  $this->SetTextColor(255,255,255);
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
    $this->SetFont('kalinga','',7);
    $this->MultiCell($w,4.5,$data[$i],0,$a);
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
  // Movernos a la derecha
  $pdf->SetDrawColor(100, 152, 201);
  $pdf->SetFillColor(100, 152, 201);
  $pdf->Line(160,10,160,30);
  $pdf->SetTextColor(0,0,0);

  
  
  if(!empty($_GET['almacen'])) $almacenes = $_GET['almacen']; else $almacenes = NULL;

$sql2 = '';

$sqlal = 'SELECT * FROM pr_almacenes where pa_estatus="A"';
if($almacenes) $sqlal.=' AND pa_id = "'.$almacenes.'" ';
$resultal = setq($sqlal) or die($sqlal);
while ($rowal = $resultal->fetch_array()) {
  $tiposm = array('A'=>'Área', 'P'=>'Pieza', 'V'=>'Volumen');
  $tiposu = array('A'=>'mts cuadrados', 'P'=>'unidades', 'V'=>'mts cúbicos');


  /* $sqlex = 'SELECT pmp_id id, pmp_nmb nmb, pmp_tipo tipo FROM pr_materiaprima WHERE pmp_estatus = "A"';
  if($_REQUEST['articulo']) $sqlex.=' AND (pmp_nmb LIKE "%'.$_REQUEST['articulo'].'%")';
  $sqlex .= 'ORDER BY pmp_nmb ASC'; */
  if($rowal['pa_tipoa'] == "M"){
    $sqlex = 'SELECT pmp_id id, pmp_nmb nmb, pmp_tipo tipo FROM pr_materiaprima WHERE pmp_estatus = "A"';
    if($_REQUEST['articulo']) $sqlex.=' AND (pmp_nmb LIKE "%'.$_REQUEST['articulo'].'%")';
    $sqlex .= 'ORDER BY pmp_nmb ASC';
  } else{
  $k = 0;
  $sqlcat = 'SELECT cat_id FROM categorias WHERE cat_estatus = "A" AND cat_inflable = "1"';
  $resultcat = setq($sqlcat);
  while($rowcat = $resultcat->fetch_array()){
    if($k == 0){
      $categorias .= $rowcat['cat_id'];
    } else{
      $categorias .= ",".$rowcat['cat_id'];
    }
    $k++;
  }
  $sqlex = 'SELECT a_id id, a_nmb nmb, cat_nmb categoria FROM articulos INNER JOIN categorias ON a_categoria = cat_id WHERE a_tipoprod != "M" AND a_inventariado = "1" ';
  $sqlex.=' AND cat_id IN ('.$categorias.')';
  $sqlex .= 'ORDER BY a_nmb ASC';
  }

  $resultex = setq($sqlex) or die($sqlex);

  $pdf->SetFont('kalinga','',7); 
  $pdf->SetWidths(array(62,62,62));
  $pdf->SetAligns(array("L","L","L"));
  srand(microtime()*1000000);
  $bandera=true;


  if($rowal['pa_tipoa'] == "M"){
    $pdf->RowMin(array(utf8_decode("ARTÍCULO"),"TIPO", "EXISTENCIA"),true);
    while ($row = $resultex->fetch_array()) {
      $articulo = $row['nmb'];
      $existencia = existenciamp($row['id'], $rowal['pa_id']);
      $pdf->RowMin(array(utf8_decode($articulo), utf8_decode($tiposm[$row['tipo']]) ,number_format($existencia, 0) . ' '.$tiposu[$row['tipo']]),false);
    }
  } else{
    $pdf->RowMin(array(utf8_decode("ARTÍCULO"),"TIPO", "MODELO", "EXISTENCIA"),true);
    while ($row = $resultex->fetch_array()) {
      $dif = 0;
      $var = busca($row['id'], 'articulos_variantes', 'av_articulo', 'COUNT(*)');
      if($var){ 
        $sqlvar = 'SELECT * FROM articulos_variantes WHERE av_articulo = "'.$row['id'].'"';
        $resultvar = setq($sqlvar);
        while($rowvar = $resultvar -> fetch_array()){
          $articulo = $row['nmb'].' '.$rowvar['av_nmb'];
          $modelo = $rowvar['av_modelo'];                    
          $existencia = existenciaModeloProduccion($row['id'], $rowal['pa_id'], $rowvar['av_modelo']);

          $pdf->RowMin(array(utf8_decode($articulo), utf8_decode($tiposm[$row['tipo']]) , utf8_decode($modelo) ,number_format($existencia, 0) . ' '.$tiposu[$row['tipo']]),false);
        }
      } else {
        $articulo = $row['nmb'];
        $modelo = "";                  
        $existencia = existenciaProduccion($row['id'], $rowal['pa_id']);
        
        $pdf->RowMin(array(utf8_decode($articulo), utf8_decode($tiposm[$row['tipo']]) , utf8_decode($modelo) ,number_format($existencia, 0) . ' '.$tiposu[$row['tipo']]),false);
      }
    }
  }
  
  

  $pdf->Ln(10);
}

//$pdf->Output('cotizaciones/'.busca($_GET['idcotiza'],'cotizaciones','co_id','co_cotizacion').'.pdf','F');
$pdf->Output();
?>
