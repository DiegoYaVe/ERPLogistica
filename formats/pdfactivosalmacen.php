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
    $empresa = $_SESSION['emp'];

    include_once('../modulos/config.php');
    $emp = new modelconfig();
    $emp->select($empresa);

    if(file_exists('../'.$emp->logo) && $emp->logo != NULL) $logo = '../'.$emp->logo;
    else $logo = NULL;

    // Logo
    if($logo)
      $this->Image($logo,163,2,35);
    // Arial bold 15
    $this->AddFont('kalinga','','kalinga.php');
    $this->AddFont('kalingab','','kalingab.php');
    $this->SetFont('kalinga','',8);
    // Movernos a la derecha
    $this->Cell(80);
    $this->SetDrawColor(100, 152, 201);
    $this->Line(160,10,160,30);
    $this->SetTextColor(0,0,0);
    // T�tulo
    $this->SetTextColor(0,0,0);
    $this->Cell(70,4,utf8_decode(ucwords(strtolower(''.$emp->tel))),0,2,'R');
    $this->Cell(70,4,utf8_decode(ucwords(strtolower(''.$emp->correo))),0,2,'R');
    $this->Cell(70,4,utf8_decode(ucwords(strtolower(''.$emp->calle.' '.$emp->nume.' '.$emp->numi))),0,2,'R');
    $this->Cell(70,4,utf8_decode(ucwords(strtolower($emp->colonia.' '.$emp->cp.' '.$emp->ciudad.' '.$emp->estado))),0,2,'R');
    // Salto de l�nea
    $this->Ln();
}

// Pie de p�gina
function Footer()
{
  $empresa = $_SESSION['emp'];

  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  // Posici�n: a 1,5 cm del final
  $this->SetY(-25);
  $this->SetFont('kalinga','',11);
/*
  $this->Cell(0,5,utf8_decode('Jos� Luis Reyes Ben�tez'),0,1,'C');
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

    $sql='select * from crm_cotizacionesdd where cdd_idd="'.$idcd.'"';
    $res=setq($sql) or die($sql);
    while($rw=$res->fetch_array()){
      $s.='
-'.trim(ucfirst(strtolower(utf8_decode($rw['cdd_nmb']))));
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
  $pdf->SetAutoPageBreak(true,20);
  $pdf->SetFont('Arial','',9);

  $empresa = $_SESSION['emp'];
  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  if($_GET['almacen'] == "T") $almacen = NULL; else $almacen = $_GET['almacen'];
  include_once('../modulos/activos.php');
  $cotiza= new modelactivos();
  $cotiza->result(NULL,NULL,$almacen);

  $pdf->AddFont('kalinga','','kalinga.php');
  $pdf->AddFont('kalingab','','kalingab.php');
  $pdf->SetFont('kalinga','',14);
  $pdf->SetTextColor(0,0,0);

//imprimir el numero de la cotizacion
$pdf->Cell(50,10,utf8_decode('Inventario de activos'),0,0);
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$pdf->SetFont('kalingab','',10);
$pdf->Cell(100,6,''.date('d').' de '.$meses[date('n')-1].' de '.date('Y').'',0,1,'R');
$pdf->Ln();

  $sqla = 'SELECT * FROM almacenes ';
  if($_GET['almacen'] != "T") $sqla.= ' WHERE a_id = "'.$_GET['almacen'].'"';
  $resulta = setq($sqla);
  while($rowa = $resulta->fetch_array()){

    $sql='SELECT COUNT(*) cantidad,a_nmb FROM activo_almacen INNER JOIN activos ON a_id = aa_activo
          WHERE aa_almacen = "'.$rowa['a_id'].'"
          GROUP BY aa_almacen,aa_activo
          ORDER BY a_nmb '  ;
    $resultado=setq($sql) or die($sql);
    if($resultado->num_rows > 0){

      $resultado=setq($sql) or die($sql);
      $pdf->Cell(4);
      $pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
      $pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
      $pdf->SetFont('kalingab','',8);
      $pdf->SetTextColor(255, 255, 255);

      //###definir el ancho de columna
      $col1=150; $col2=40;
      $pdf->Cell(190,5,'Almacen '.$rowa['a_nmb'],1,0,'C',true);
      $pdf->Ln();
      $pdf->Cell(4);
      $pdf->Cell($col1,5,utf8_decode('Activo'),1,0,'C',true);
      $pdf->Cell($col2,5,'Cantidad',1,0,'C',true);
      $pdf->Ln();
      $pdf->SetTextColor(0, 0, 0);
      $pdf->SetFont('kalinga','',8);
      $bandera=true;
      $pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla

      $pdf->SetWidths(array($col1,$col2));
      srand(microtime()*1000000);
      $bandera=true;

      $sql='SELECT COUNT(*) cantidad,a_nmb FROM activo_almacen INNER JOIN activos ON a_id = aa_activo
            WHERE aa_almacen = "'.$rowa['a_id'].'"
            GROUP BY aa_almacen,aa_activo
            ORDER BY a_nmb '  ;
      $resultado=setq($sql) or die($sql);
      $totalamacen = 0;
      while($row=$resultado->fetch_array()){
          $pdf->SetAligns(array('J','R'));
          $pdf->RowMin(array($row['a_nmb'],$row['cantidad']),false);
          $bandera=!$bandera;

          $totalamacen+=$row['cantidad'];
      }
      $pdf->Cell(4);
      $pdf->Cell($col1,5,utf8_decode('Total del almacen '.$rowa['a_nmb']),1,0,'R',true);
      $pdf->Cell($col2,5,$totalamacen,1,0,'R',true);
      $pdf->Ln(10);

    }
  }

$pdf->Output();
?>