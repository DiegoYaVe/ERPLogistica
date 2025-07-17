<?php
include('../funciones.php');
include('../lib/fpdf/pdf_js.php');
session_start();
ini_set('display_errors', 1);

class PDF extends PDF_JavaScript
{
var $widths;
var $aligns;
  function Header()
  {
    $sql='SELECT * FROM remisiones WHERE r_id="'.$_GET['id'].'"';
    $rs=setq($sql) or die($sql);
    $rw= $rs->fetch_array();
    $empresa = getmax('e_id', 'empresas', false, false);

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
    $fecha = new DateTime($remis->faplica);
    $meses = array("01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre");


    // Logo
    if($logo)
      $this->Image($logo,20,5,20);
    // Arial bold 15
    $this->AddFont('kalinga','','kalinga.php');
    $this->AddFont('kalingab','','kalingab.php');
    // Movernos a la derecha
    $this->SetTextColor(0,0,0);
    $this->SetDrawColor(0, 0, 0);
    // Título

  $this->SetFont('kalingab','',18);
  $this->Cell(4);
  $this->SetFillColor(91, 155, 213);
  $this->Cell(40);
  $this->Cell(120,6,utf8_decode($emp->nmb),0,0,"C");
  $logo2 = '../img/inflalandia-games.jpg';
//  $this->Image($logo2,180,8,30);

  $this->SetFont('kalingab','',9);
  $this->Ln(4);
  $this->Cell(4);
  $this->SetTextColor(0,0,0);
  $this->Cell(30);
  $this->Cell(120,6,utf8_decode($emp->calle.' '.$emp->nume.' '.$emp->numi.' '.$emp->colonia.' '.$emp->ciudad.' '.$emp->estado.' '.$emp->cp),0,0);
  $this->Ln(4);
  $this->Cell(30);
  $this->Cell(140,6,utf8_decode("WWW.FABRICAINFLABLE.COM"),0,0,"C");
  $this->Ln(8);

  $this->SetFont('kalingab','',15);
  $this->SetTextColor(255,0,0);
  $this->SetFont('kalingab','',14);
  $this->Cell(190,5,utf8_decode("PÓLIZA DE GARANTÍA"),0,0,"C");
  $this->Ln(8);

  $this->SetFont('kalingab','',9);
  $this->Cell(40,5,"NOMBRE DEL CLIENTE",0,0,"C");
  $this->SetFont('kalingab','',8);
  $this->SetTextColor(0,0,0);
  $this->Cell(65,5,utf8_decode(busca($remis->cliente,'crm_clientes','c_id','CONCAT(c_nmb," ",c_apellidos)')),1,0,"C");
  $this->Cell(2);
  $this->SetTextColor(255,0,0);
  $this->SetFont('kalingab','',9);
  $this->Cell(35,5,"FECHA DE COMPRA",0,0,"C");
  $this->SetTextColor(0,0,0);
  $this->SetFont('kalingab','',8);
  $this->Cell(45,5,utf8_decode($fecha->format('d')." de ".$meses[$fecha->format('m')]." de ".$fecha->format('Y')),1,0,"C");


  $this->Ln(8);
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
    $h=5*$nb;
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
  $articulo = busca($idcd, 'remisionesd', 'rd_id', 'rd_articulo');
  $s=$nmb."\n";
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
  $pdf->AliasNbPages();
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true,5);
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
  $pdf->SetFont('kalingab','',10);

//###definir el ancho de columna
 
//impresion de una tabla multicell utilzando un script mc_table del archivo mc_table.php
 //##################################################################################
 //#######################################################################################
 //#################################################################################3
//SetWidths(array(columna1,columna2,columna3,columna4,columna5,....,columnan))
//columna es el numero de ancho de cada columna
  $fecha = new DateTime($remis->faplica);

  $x=$pdf->GetX();
  $y=$pdf->GetY();

  $meses = array("01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril", "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre", "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre");

  $pdf->SetFont('Helvetica','',9);
  
  $textto = "La FÁBRICA DE INFLABLES INFLALANDIA garantiza por cinco años (1,825 días), a partir de la fecha de entrega de el producto, el correcto funcionamiento del mismo contra cualquier defecto en los materiales y mano de obra empleados para su fabricación. La cual no incluye desgaste natural o por uso general del producto, daños ocasionados por terceros, defectos o daños ocultos.
  Toda reclamación por daños o entregas incompletas debe notificarse dentro de los primeros 2 días hábiles siguientes a la fecha en que EL CLIENTE recibió SUS PRODUCTOS. Es necesario reportarlos directamente a su tienda de venta o al centro de atención a clientes TEL. +52 771 126 76 35; en caso contrario, se entenderá que el CLIENTE recibió LOS PRODUCTOS a su entera satisfacción.
  Cuando se trate de PRODUCTOS en liquidación y/o de oferta, LOS PRODUCTOS se entregarán en las condiciones físicas en que se encuentren al momento de su venta; esto quiere decir que, no se aceptará reclamación, reparación, devolución o cambio físico alguno, debiendo EL CLIENTE firmar de conformidad al momento de levantar el pedido y recibir los PRODUCTOS.
  LA FÁBRICA DE INFLABLES  INFLALANDIA no estará obligada a cumplir con lo que se menciona en esta cláusula de garantías, cuando el plazo de garantía haya prescrito o en su defecto, LOS PRODUCTOS hayan sido usados en condiciones diferentes a las normales, presenten reparaciones efectuadas por un tercero o hayan sido dañadas con dolo.
  En los casos de artículos como accesorios, pelotas, trampolines y futbolitos, la garantía aplica únicamente por faltantes de piezas, la cual tendrá una validez de 7 días a partir de la entrega o envió, presentando el reporte a nuestro Centro de Atención a Clientes, a fin de coordinar el servicio.
  En caso de motores y turbinas, La Fábrica de Inflables otorga la garantía de 6 meses a partir de la fecha de entrega del mismo. Para el mejor funcionamiento y cuidado de éstos, le recomendamos consultar el *manual de uso* para prevenir daños.
  Con la finalidad de brindar una respuesta oportuna en daños visibles EL/LA COMPRADOR (A) podrá reportarlo con fotografías evidentes y claras a los correos ventas@fabricainflable.com con copia a  produccion@fabricainflable.com colocando en el asunto el número de pedido, tomando en cuenta que las fotografías sean panorámicas (vista completa) de los daños, costado derecho e izquierdo, etiquetas, parte debajo o trasera del producto, componentes (cinta, bolsa de trasporte, motor, accesorios, etc.) Una vez enviada la información nuestro centro de atención a clientes contactara a la brevedad posible al COMPRADOR (A) para brindarle información y proceso correspondiente.
  La garantía se entrega en escrito al cliente ya sea en físico o bien puede enviarse vía digital en caso de no haberla recibido. La garantía es aplicable en el producto, en defecto de fabricación, la empresa no se hace cargo en ningún caso del traslado del producto, el cliente tendrá que enviarlo bajo sus propios medios o bien traerlo directamente a la planta. Las cuestiones eléctricas no entran en ningún caso en garantía. ";
  $pdf->Ln(2);
  $pdf->Multicell(189,5,utf8_decode($textto),0,"J");
  $pdf->Cell(20);
  $pdf->Ln(2);
  $pdf->SetFont('Helvetica','b',13);
  $pdf->Multicell(189,5,utf8_decode('PROCESO DE GARANTÍA'),0,"L");
  $pdf->Ln(2);

  $paso1='1. ENVIA EVIDENCIA FOTOGRÁFICA Y DE VIDEO A TU ASESOR PARA VALORAR SI EL PRODUCTO ENTRA O NO A GARANTÍA';
  $paso2='2. UNA VEZ APROBADA LA GARANTÍA, DEBES HACERNOS LLEGAR POR ESCRITO LA DESCRIPCIÓN CON LA EVIDENCIA DEL DAÑO EN EL PRODUCTO.';
  $paso3='3. AL MOMENTO DE REGRESAR TUS PRODUCTOS, ES IMPORTANTE ENVIAR TU GUÍA DE RASTREO PAGADA A TU ASESOR Y FOTOGRAFÍAS DEL PRODUCTO EMPAQUETADO.';
  $paso4='4. AL LLEGAR TU PRODUCTO A PLANTA, EL EQUIPO DE PRODUCCIÓN HACE LA REVISIÓN Y LO INGRESA A REPARACIÓN.';
  $paso5='5. EL PROCESO PUEDO DURAR DE 15 A 20 DÍAS. ';
  $paso6='6. EL ASESOR TE SOLICITARÁ LA GUIA PAGADA PARA REALIZAR EL ENVIO DE REGRESO.';
  $paso7='7. TE HARÉMOS LLEGAR LA EVIDENCIA FOTOGRAFICA DEL PRODUCTO REPARADO.';
  $paso8='8. UNA VEZ ENTREGADO EL PRODUCTO EN PAQUETERÍA, ÉSTE DEMORARÁ DE 3 A 5 DÍAS HABILES EN LLEGAR A TU DOMICILIO.';

  $y=$pdf->GetY();
  $pdf->SetFont('Helvetica','',6);
  $pdf->SetDrawColor(0, 10, 255);
  $pdf->SetLineWidth(0.7);
  $pdf->Rect(10,$y,45,20);
  $pdf->SetY($y+5);
  $pdf->Multicell(45,3,utf8_decode($paso1),0,"C");
  $pdf->Rect(60,$y,45,20); 
  $pdf->SetY($y+3);
  $pdf->Cell(50);
  $pdf->Multicell(45,3,utf8_decode($paso2),0,"C");
  $pdf->Rect(110,$y,45,20);
  $pdf->SetY($y+2);
  $pdf->Cell(100);
  $pdf->Multicell(45,3,utf8_decode($paso3),0,"C");
  $pdf->Rect(160,$y,45,20);
  $pdf->SetY($y+5);
  $pdf->Cell(150);
  $pdf->Multicell(45,3,utf8_decode($paso4),0,"C");
  $pdf->ln(10);
  $y=$pdf->GetY();
  $pdf->Rect(10,$y,45,20);
  $pdf->SetY($y+6);
  $pdf->Multicell(45,3,utf8_decode($paso5),0,"C");
  $pdf->Rect(60,$y,45,20); 
  $pdf->SetY($y+5);
  $pdf->Cell(50);
  $pdf->Multicell(45,3,utf8_decode($paso6),0,"C");
  $pdf->Rect(110,$y,45,20);
  $pdf->SetY($y+5);
  $pdf->Cell(100);
  $pdf->Multicell(45,3,utf8_decode($paso7),0,"C");
  $pdf->Rect(160,$y,45,20);
  $pdf->SetY($y+4);
  $pdf->Cell(150);
  $pdf->Multicell(45,3,utf8_decode($paso8),0,"C");
  
  $empresa = getmax('e_id', 'empresas', false, false);
  include_once('../modulos/config.php');
  $emp = new modelconfig();
  $emp->select($empresa);

  $y=$pdf->GetY();
  $pdf->ln(10);
  $pdf->SetY($y+10);
  $pdf->SetFont('Helvetica','b',9);
  $pdf->Multicell(100,3,utf8_decode('DATOS FISCALES'),0,"L");
  $pdf->ln(2);
  $pdf->SetFont('Helvetica','',8);
  $pdf->Multicell(125,3,utf8_decode('RAZÓN SOCIAL: '.$emp->razons),0,"L");
  $pdf->ln(2);
  $pdf->Multicell(125,3,utf8_decode('RFC: '.$emp->rfc),0,"L");
  $pdf->ln(2);
  $pdf->Multicell(125,3,utf8_decode('DOMICILIO: '.$emp->calle.' '.$emp->nume.' '.$emp->numi.' '.$emp->colonia.' '.$emp->ciudad.' '.$emp->estado.' '.$emp->cp),0,"L");
  $pdf->Rect(135,$y+10,45,30);
  $pdf->SetY($y+12);
  $pdf->Cell(125);
  $pdf->SetFont('Helvetica','b',7);
  $pdf->Multicell(45,3,utf8_decode('SELLO Y FIRMA DE LA FÁBRICA'),0,"C");
  $pdf->Image('../img/marcadeagua.png',145,246,23, 23);
  $pdf->SetFont('Helvetica','b',10);
  $pdf->setY($y+36);
  $pdf->setTextColor('159', '159', '159');
  $pdf->Multicell(152,3,date('Y'),0,"R");
  
//$pdf->Output('cotizaciones/'.busca($_GET['idcotiza'],'cotizaciones','co_id','co_cotizacion').'.pdf','F');
//$pdf->Output('../docs/'.utf8_decode('Rem'.$remis->id).'.pdf',"F");
$pdf->Output();
$pdf->Close();
?>