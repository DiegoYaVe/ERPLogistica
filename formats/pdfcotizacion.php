<?php
include('../funciones.php');
include('../lib/fpdf/fpdf.php');
session_start();
ini_set('display_errors', 0);

class PDF extends FPDF
{
var $widths;
var $aligns;
var $alto;

  function Header(){
 
      $this->Image('../img/headerc.png', 0, 0, 220); // 210 mm es el ancho A4, ajusta si usas otra medida
      $this->Ln(50); // Ajusta este valor a la altura real del headerc.jpg si es necesario
  }

// Pie de página
 function Footer()
{
    // Posición a 260 mm del inicio (A4 es 297 mm de alto, así que 260 está cerca del fondo)
    $this->SetY(-40); // Mueve 40 mm hacia arriba desde el final de la página, ajusta según altura del footer

    // Inserta la imagen del footer
    $this->Image('../img/footerc.png', -5, 267, 220); 
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
    $this->SetFont('Montserrat-Light','',7);
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
    $this->SetFont('Montserrat-Light','',7);
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

function paquete($id,$nmb,$idcd, $remision)
{
    $s=$nmb.'';

    if($remision != 1){
      $sql='select * from crm_cotizacionesdd where cdd_idd="'.$idcd.'" ORDER BY cdd_id ASC';
      $res=setq($sql) or die($sql);
      while($rw=$res->fetch_array()){
        $s.='
  -'.trim(utf8_decode($rw['cdd_nmb']));
      }
    } else{
      $combo = busca($id, 'articulos', 'a_id', 'a_tipoprod');
      if($combo == "M"){
        $sql = 'SELECT COUNT(*) cantidad, a_nmb, rc_modelo, rc_articulo FROM remisionesc INNER JOIN articulos ON rc_articulo = a_id WHERE rc_remisiond = "'.$idcd.'" AND rc_ligado IS NULL GROUP BY rc_articulo, rc_modelo';
        $result = setq($sql);
        if($result ->num_rows > 0){
          $s.='
          -'. utf8_decode('INCLUYE:');
          while($row = $result -> fetch_array()){
            if($row['rc_modelo']) $mode = busca($row['rc_articulo'], 'articulos_variantes', 'av_modelo = "'.$row['rc_modelo'].'" AND av_articulo', 'av_nmb');
            else $mode ='';
            $s.='
            -'.$row['cantidad'].' '.trim(utf8_decode($row['a_nmb'])).' '.$mode;
          }
        }
      }
      $sql = 'SELECT COUNT(*) cantidad, a_nmb FROM remisionesc INNER JOIN articulos ON rc_articulo = a_id WHERE rc_remisiond = "'.$idcd.'" AND rc_ligado IS NOT NULL GROUP BY rc_articulo, rc_modelo';
      $result = setq($sql);
      if($result ->num_rows > 0){
        $s.='
        -'. utf8_decode('COMPLEMENTOS:');
        while($row = $result -> fetch_array()){
          $s.='
          -'.$row['cantidad'].' '.trim(utf8_decode($row['a_nmb']));
        }
      }

    }

    $sqlart='SELECT a_tipoprod, a_mdetalle, a_peso, a_largo, a_ancho, a_alto FROM articulos WHERE a_id = "'.$id.'"';
    $resart=setq($sqlart);
    list($tipoprod, $mdetalle, $peso, $largo, $ancho, $alto) = $resart -> fetch_array();

    if($tipoprod != "M" && $mdetalle == "1"){
      $s.="\n";
      $s.=''.trim(utf8_decode("DIMENSIONES:"))."\n";
      $s.=' -'.trim(utf8_decode("Peso:".number_format($peso, 0).' kilos'))."\n";
      $s.=' -'.trim(utf8_decode("Largo:".number_format($largo, 0).' metros'))."\n";
      $s.=' -'.trim(utf8_decode("Ancho:".number_format($ancho, 0).' metros'))."\n";
      $s.=' -'.trim(utf8_decode("Alto:".number_format($alto, 0).' metros'))."\n";
    }

    if($_SESSION['uid'] == "LOGISTICA"){
    $s = base64_encode($s) ;
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

    function addClient($client)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30, 10, 'CLIENTE', 0, 1);
        
        $this->SetTextColor(0, 102, 204); // Azul
        $this->SetFont('Arial', '', 12);
        $this->Cell(100, 6, $client, 0, 0);
        
        $this->SetTextColor(0); // Reset
        $this->Ln(3);
    }

    function addDescription($origen, $destino, $peso)
    {
        $this->SetFillColor(20, 40, 80);
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(190, 8, utf8_decode('DESCRIPCIÓN'), 0, 1, 'C', true);
        
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 11);
        $this->MultiCell(190, 6, "Origen: $origen", 0);
        $this->MultiCell(190, 6, "Destino: $destino", 0);
        $this->MultiCell(190, 6, "Peso y dimensiones:\n$peso", 0);
        $this->Ln(3);
    }

    function addConceptTable($concepts)
    {
        $this->SetFillColor(20, 40, 80);
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(130, 8, 'CONCEPTO', 0, 0, 'L', true);
        $this->Cell(60, 8, 'PRECIO', 0, 1, 'L', true);

        $this->SetTextColor(0);
        $this->SetFont('kalingab', '', 13);
        foreach ($concepts as $row) {
            $this->Cell(130, 8, $row[0], 0);
            $this->Cell(60, 8, $row[1], 0, 1);
        }
        $this->Ln(3);
    }

    function addTerms($terms)
    {
        $this->SetFillColor(20, 40, 80);
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(190, 8, utf8_decode('TÉRMINOS Y CONDICIONES'), 0, 1, 'L', true);

        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 10);
        foreach ($terms as $term) {
            $this->Cell(5);
            $this->Cell(185, 6, utf8_decode("- $term"), 0, 1);
        }
    }

    function addFooterInfo($name, $position, $email)
    {
        $this->SetFont('Arial', '', 10);
        $this->ln();
        $this->SetFont('kalingab', '', 10);
        $this->SetTextColor(29,125,200); 
        $this->Cell(190, 6, utf8_decode($name), 0, 1, 'R');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0); 
        $this->Cell(190, 6, utf8_decode($position), 0, 1, 'R');
        $this->Cell(190, 6, utf8_decode($email), 0, 1, 'R');
    }
}

  $pdf = new PDF("P","mm", "Letter");
  $pdf = new PDF();
  $pdf->AddPage();
   $pdf->AddFont('kalinga','','kalinga.php');
  $pdf->AddFont('kalingab','','kalingab.php');
  
  $pdf->SetY(35);

  // Datos
  
  include_once('../modulos/cotizaciones.php');
  $cotiza = new modelcotizaciones();
  $cotizacion = $_GET['idcotiza'];
  $cotiza->select($_GET['idcotiza']);
  $cliente = busca($cotiza->tablero, 'crm_tableros', 'ct_id', 'ct_cliente');
  $client = busca($cliente, 'crm_clientes', 'c_id', 'c_nmb').' '.busca($cliente, 'crm_clientes', 'c_id', 'c_apellidos');
  $vendedor = busca($cotiza->responsable, 'usuarios', 'u_id' ,'CONCAT(u_nmb, " ", u_apellidos)');
  $puestov = busca($cotiza->responsable, 'usuarios', 'u_id' ,'u_puesto');
  $correov = busca($cotiza->responsable, 'usuarios', 'u_id' ,'u_correo');
  $origen = busca($cotiza->motivo, 'ruta_origen', 'ro_id', 'ro_descripcion');
  $destino = busca($cotiza->dirdestino, 'ruta_destino', 'rd_id', 'rd_descripcion');
  if(!$destino) $destino = busca($cotiza->direnvio, 'crm_direcciones', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi," ",cd_colonia," ",cd_municipio," ",cd_cp)');
  $peso = $cotiza->pesomercancia." Kg aprox\n$cotiza->largomercancia\" Largo X $cotiza->anchomercancia\" Ancho X $cotiza->altomercancia\" Alto.";
  if($cotiza->tipokm != "proveedor"){
    $sql = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$cotizacion.'"';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $descripcion = utf8_decode($row['cdm_nmbarticulo']);
      $precio = $cotiza->mtotal; // Asegúrate que este campo exista

      // Si necesitas formatear el precio
      $precio_formateado = "$ " . number_format($precio, 2); // ej: "$ 850.00"

      // Agregar al arreglo
      $concepts[] = [$descripcion, $precio_formateado];
    }
  } else {
    $sql = 'SELECT * FROM crm_cotizaciones_proveedor WHERE cp_cotizacion = "'.$cotiza->id.'"';
    $result = setq($sql);
    while($row = $result -> fetch_array()){
      $descripcion = utf8_decode($row['cp_concepto']);
      $precio = floatval($row['cp_monto'])+floatval($row['cp_extra']); // Asegúrate que este campo exista

      // Si necesitas formatear el precio
      $precio_formateado = "$ " . number_format($precio, 2); // ej: "$ 850.00"

      // Agregar al arreglo
      $concepts[] = [$descripcion, $precio_formateado];
    }
  }

 /*  $concepts = [
      ["FLETE AEREO INTERNACIONAL", "$ 850.00"],
      ["DESCONSOLADACION AREA", "$ 3,500 MXP + IVA"],
      ["FLETE NACIONAL", "$ 3,800 MXP + IVA - RET"]
  ]; */

  $terms = array();
  /* $terms = [
      "La mercancía viaja sin seguro.",
      "Libre de maniobras.",
      "Carga general.",
      "Cotización expresada en MXP.",
      "Sujeta a disponibilidad.",
      "2 horas de carga y descarga.",
      "Cotización expresada en USD y MXN.",
      "Cotización basada en pesos y dimensiones proporcionados.",
      "Cotización puerta a puerta.",
      "Incluye impuestos de aduana.",
      "Servicio consolidado."
  ]; */
  $sqlcon = 'SELECT * FROM crm_cotizaciones_condiciones WHERE cf_cotizacion = "'.$cotizacion.'"';
  $resultcon = setq($sqlcon);
  while($rowcon = $resultcon -> fetch_array()){
    $terms[] = $rowcon['cf_descripcion'];
  }
  $pdf->SetTextColor(0);
  $pdf->SetFont('Arial', '', 35);
  $pdf->Cell(140, 8, utf8_decode("COTIZACIÓN"), 0);
  $pdf->SetFont('Arial', '', 11);
  $pdf->Cell(50, 8, "N. ". substr($cotiza->folio, -4), 0, 1, 'R');
  $pdf->SetFont('kalingab', '', 15);
  $pdf->Cell(140, 8, utf8_decode("CLIENTE"), 0);
  $pdf->SetFont('Arial', '', 11);
  $pdf->Cell(50, 8, fechaEnEspanol(date('Y-m-d')), 0, 1, 'R');
  $pdf->SetFont('kalingab', '', 11);
  $pdf->SetTextColor(29,125,200); 
  $pdf->Cell(50, 8, utf8_decode($client), 0);
  $pdf->SetTextColor(0);
  $pdf->SetFont('Arial', '', 11);
  $pdf->addFooterInfo($vendedor, $puestov,$correov);
  $pdf->addDescription($origen, $destino, $peso);
  $pdf->addConceptTable($concepts);
  $pdf->addTerms($terms);
  

  if(isset($_GET['descarga'])){
    $pdf->Output('cotizacion - '.$cotiza->folio.'.pdf', 'D');   
  } else //if($cotiza->estatus == "A" || $cotiza->estatus == "E")
    $pdf->Output(busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_nmb').'.pdf','I');

function fechaEnEspanol($fechaISO) {
    $meses = [
        '01' => 'enero',
        '02' => 'febrero',
        '03' => 'marzo',
        '04' => 'abril',
        '05' => 'mayo',
        '06' => 'junio',
        '07' => 'julio',
        '08' => 'agosto',
        '09' => 'septiembre',
        '10' => 'octubre',
        '11' => 'noviembre',
        '12' => 'diciembre'
    ];

    $fecha = new DateTime($fechaISO);
    $dia = $fecha->format('d');
    $mes = $fecha->format('m');
    $anio = $fecha->format('Y');

    return "$dia de " . $meses[$mes] . " de $anio";
}
?>
