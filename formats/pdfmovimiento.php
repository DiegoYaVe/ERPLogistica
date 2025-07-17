<?php
  include('../funciones.php');
  include('../lib/fpdf/fpdf.php');
  session_start();
  /* ini_set('display_errors', 1); */

  class PDF extends FPDF{
    var $widths;
    var $aligns;
    function Header(){
      $sql='SELECT * FROM movimientos WHERE m_id="'.$_GET['id'].'" AND m_tipo = "'.$_GET['tipo'].'"';
      $rs=setq($sql) or die($sql);
      $rw= $rs->fetch_array();
      //$empresa = busca($_GET['id'],'movimientos','m_id','m_empresa');
      $empresa = $_SESSION['emp'];

      include_once('../modulos/config.php');
      $emp = new modelconfig();
      $emp->select($empresa);

      if(file_exists('../'.$emp->logo) && $emp->logo != NULL) $logo = '../'.$emp->logo;
      else $logo = NULL;

      include_once('../modulos/movimientos.php');
      $movim = new modelmovimientos();
      $movim->select($_GET['id'],$_GET['tipo']);
      if($movim->estatus == "N"){
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
      // T�tulo
      $folio = busca($_GET['id'],'movimientos','m_empresa = "'.$_SESSION['emp'].'" AND m_tipo = "'.$_GET['tipo'].'" AND m_id','m_folio');
      $motivo = busca($_GET['id'],'movimientos','m_empresa = "'.$_SESSION['emp'].'" AND m_tipo = "'.$_GET['tipo'].'" AND m_id','m_motivo');

      $tipomov = array("E"=>"Entrada","S"=>"Salida","T"=>"Trasapaso");
      //imprimir el numero de la cotizacion
      $this->SetFont('kalingab','',18);
      $this->Cell(35);
      $this->Cell(110,32,utf8_decode($tipomov[$_GET['tipo']].' de Mercancía'),0,0,"C");
      $this->Ln();

      $this->SetFont('kalinga','',9);
      $this->Cell(4);
      $this->SetFillColor(91, 155, 213);
      $this->SetTextColor(255,255,255);
      $this->Cell(41,6,utf8_decode("No. de movimiento:"),1,0,'C',true);
      $this->SetTextColor(0,0,0);
      $this->Cell(51,6,utf8_decode($folio),1,0);
      $this->SetFillColor(91, 155, 213);
      $this->SetTextColor(255,255,255);
      $this->Cell(41,6,utf8_decode("Operador:"),1,0,'C',true);
      $this->SetTextColor(0,0,0);
      $this->Cell(51,6,utf8_decode($movim->usuario),1,0);
      $this->Ln();

      $this->Cell(4);
      $this->SetFillColor(91, 155, 213);
      $this->SetTextColor(255,255,255);
      $this->Cell(41,6,utf8_decode("Fecha de compra:"),1,0,'C',true);
      $this->SetTextColor(0,0,0);
      $this->Cell(51,6,utf8_decode(fecha_formato($movim->fecha,false,true)),1,0);
      $this->SetFillColor(91, 155, 213);
      $this->SetTextColor(255,255,255);
      $this->Cell(41,6,utf8_decode("Aplicación:"),1,0,'C',true);
      $this->SetTextColor(0,0,0);
      $this->Cell(51,6,utf8_decode(fecha_formato($movim->fechaap,true,true)),1,0);
      $this->Ln();

      $this->Ln();
      $this->Ln();
      $this->Cell(4);
      $this->SetFillColor(91, 155, 213);
      $this->SetTextColor(255,255,255);
      $this->Cell(184,4,utf8_decode("Motivo:"),1,0,'C',1);
      $this->Ln();
      $this->Cell(4);
      $this->SetTextColor(0,0,0);
      $this->Cell(184,8,utf8_decode($motivo),1,0);
      //$this->Ln();

      // Salto de l�nea
      $this->Ln();
    }

// Pie de p�gina
function Footer()
{
  $empresa = busca($_GET['id'],'movimientos','m_id','m_empresa');

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

  $empresa = busca($_GET['id'],'movimientos','m_id','m_empresa');
  include_once('../modulos/config.php');
  $emp= new modelconfig();
  $emp->select($empresa);

  include_once('../modulos/movimientos.php');
  $movim = new modelmovimientos();
  $movim->select($_GET['id'],$_GET['tipo']);

  $pdf->AddFont('kalinga','','kalinga.php');
  $pdf->AddFont('kalingab','','kalingab.php');
  $pdf->SetFont('kalinga','',10);
  $pdf->SetTextColor(0,0,0);

$pdf->Cell(0,8,utf8_decode($movim->destino),0,1,'L');

 //$pdf->Ln(3);
//tabla de los productos de la cotizacion
  $pdf->Cell(4);
  $sql='SELECT * FROM movimientosd WHERE md_movimiento="'.$movim->id.'" AND md_tipo = "'.$movim->tipo.'"';
  $resultado=setq($sql) or die($sql);
  $pdf->SetFillColor(91, 155, 213);//relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223);//colorea las lineas de las celdas de la tabla
  $pdf->SetFont('kalingab','',8);
  $pdf->SetTextColor(255, 255, 255);

//###definir el ancho de columna
  $col1=25; $col2=155;
  $pdf->Cell($col1,7,'Cantidad',1,0,'C',true);
  $pdf->Cell($col2,7,utf8_decode('Descripción'),1,0,'C',true);
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
$pdf->SetWidths(array($col1,$col2));
srand(microtime()*1000000);
$bandera=true;
$viva = busca($_SESSION['emp'],'configuracionesp','c_id','c_iva');
$poriva = $viva/100;
$ivat = 0;
$totalf = 0;
while($row=$resultado->fetch_array()){
  $iva=0;    //cuando se requiere desplegar el iva

    $pdf->Cell(4);
    $pdf->prod = $pdf->PageNo();
    $pdf->SetFillColor(214, 230, 244);//relleno alternado de la tabla
    //Para imprimir la tabla, envia los registros al metodo array del archivo mc_table.php
    $pdf->SetAligns(array('C','L'));
    $modelonmb = "";
    if(!empty($row['md_modelo'])){
      $modelonmb = busca($row['md_modelo'],'articulos_variantes','av_modelo','av_nmb');  
    }
    
    $pdf->RowMin(array($row['md_cantidad'],utf8_decode(busca($row['md_articulo'],'articulos','a_id','a_nmb')." ".$modelonmb)),false);
    $bandera=!$bandera;
}

$vertotal = 1;


/*
if($fila['co_ttdc'] == 1){
  $sqlt = 'SELECT * FROM tabuladormsi WHERE t_estatus = "A" ORDER BY t_nmb ASC';
  $resultt = setq($sqlt) or die($sqlt);
  if(mysql_num_rows($resultt) > 0){
    $pdf->Ln();
    //$pdf->Cell(124);
    $pdf->Cell(5);
    $pdf->Cell(60,6,utf8_decode('Pagos con tarjetas de d�bito o cr�dito'),1,0,'C',false);
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

$pdf->Ln(3);
/*
$pdf->SetFont('kalinga','',8);
$pdf->SetTextColor(0,0,0);
$pdf->MultiCell(177,5,utf8_decode('Esperando que el contenido de la presente cubra sus expectativas me pongo a sus �rdenes para cualquier duda o aclaraci�n.'),0,'J');
$pdf->Ln(1);
$pdf->MultiCell(177,4,utf8_decode('Los precios pueden variar sin previo aviso o hasta agotar existencias'),0,'J');
$pdf->MultiCell(177,4,utf8_decode('Se aplican restricciones'),0,'J');
$pdf->Ln(1);
$pdf->MultiCell(190,5,utf8_decode('Cotizaci�n v�lida hasta el '.date('d-m-Y',strtotime($movim->ffin))),0,'R');

*/
$pdf->Ln(10);

//$pdf->Output('cotizaciones/'.busca($_GET['idcotiza'],'cotizaciones','co_id','co_cotizacion').'.pdf','F');
$pdf->Output();
?>
