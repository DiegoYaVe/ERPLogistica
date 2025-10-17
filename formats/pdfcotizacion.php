<?php
define('FPDF_FONTPATH', realpath(__DIR__.'/../lib/fpdf/font').DIRECTORY_SEPARATOR);

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
      // Header imagen ancho carta (Letter)
      $this->Image('../img/headerc.png', 0, 0, 220);
      $this->Ln(50);
  }

  // Pie de página
  function Footer()
  {
      // Altura cercana al fondo (Letter 279mm aprox.)
      $this->SetY(-40);
      $this->Image('../img/footerc.png', -5, 250, 220);
  }

  /* =================== Helpers de tablas =================== */
  function SetWidths($w){ $this->widths=$w; }
  function SetAligns($a){ $this->aligns=$a; }

  function Row($data,$bandera)
  {
    $nb=0;
    for($i=0;$i<count($data);$i++)
      $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    $h=9*$nb;
    $this->CheckPageBreak($h);
    $rellenar = $bandera ? 'FD' : 'D';

    for($i=0;$i<count($data);$i++){
      $w=$this->widths[$i];
      $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
      $x=$this->GetX();
      $y=$this->GetY();
      $this->Rect($x,$y,$w,$h,$rellenar);
      // Cuerpo siempre en Abadi
      $this->SetFont('Abadi','',9);
      $this->MultiCell($w,5,$data[$i],0,$a);
      $this->SetXY($x+$w,$y);
    }
    $this->Ln($h);
  }

  function RowMin($data,$bandera)
  {
    $nb=0;
    for($i=0;$i<count($data);$i++)
      $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    $h=4.5*$nb;
    $this->CheckPageBreak($h);
    $rellenar = $bandera ? 'FD' : 'D';

    for($i=0;$i<count($data);$i++){
      $w=$this->widths[$i];
      $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
      $x=$this->GetX();
      $y=$this->GetY();
      $this->Rect($x,$y,$w,$h,$rellenar);
      $this->SetFont('Abadi','',7);
      $this->MultiCell($w,4.5,$data[$i],0,$a);
      $this->SetXY($x+$w,$y);
    }
    $this->Ln($h);
  }

  function RowMinImg($data,$bandera,$valida)
  {
    $nb=0;
    for($i=0;$i<count($data);$i++)
      $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    $h=5*$nb;
    $this->CheckPageBreak($h);
    $rellenar = $bandera ? 'FD' : 'D';
    if(!$valida && $h<30){ $h=30; }

    for($i=0;$i<count($data);$i++){
      $w=$this->widths[$i];
      $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
      $x=$this->GetX();
      $y=$this->GetY();
      $this->Rect($x,$y,$w,$h,$rellenar);
      $this->SetFont('Abadi','',7);
      $this->MultiCell($w,4.5,$data[$i],0,$a);
      $this->SetXY($x+$w,$y);
    }

    $enviar = ($h / 2) - 13;
    $this->setAltura($enviar);
    $this->Ln($h);
  }

  function setAltura($altura){ $this->altura = $altura; }
  function getAltura(){ return $this->altura; }

  function CheckPageBreak($h)
  {
    if(($this->GetY()+$h)+10>$this->PageBreakTrigger)
      $this->AddPage($this->CurOrientation);
  }

  function NbLines($w,$txt)
  {
    $cw=&$this->CurrentFont['cw'];
    if($w==0) $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n") $nb--;
    $sep=-1; $i=0; $j=0; $l=0; $nl=1;
    while($i<$nb){
      $c=$s[$i];
      if($c=="\n"){ $i++; $sep=-1; $j=$i; $l=0; $nl++; continue; }
      if($c==' ') $sep=$i;
      $l+=$cw[$c];
      if($l>$wmax){
        if($sep==-1){ if($i==$j) $i++; }
        else $i=$sep+1;
        $sep=-1; $j=$i; $l=0; $nl++;
      } else $i++;
    }
    return $nl;
  }

  /* =================== HTML mínimo (enlaces) =================== */
  function WriteHTML($html)
  {
      $html=str_replace("\n",' ',$html);
      $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
      foreach($a as $i=>$e){
          if($i%2==0){
              if($this->HREF) $this->PutLink($this->HREF,$e);
              elseif($this->ALIGN=='center') $this->Cell(0,5,$e,0,1,'C');
              else $this->Write(5,$e);
          }else{
              if($e[0]=='/') $this->CloseTag(strtoupper(substr($e,1)));
              else{
                  $a2=explode(' ',$e);
                  $tag=strtoupper(array_shift($a2));
                  $prop=array();
                  foreach($a2 as $v){
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
      if($tag=='B' || $tag=='I' || $tag=='U') $this->SetStyle($tag,true);
      if($tag=='A') $this->HREF=$prop['HREF'];
      if($tag=='BR') $this->Ln(5);
      if($tag=='P')  $this->ALIGN=$prop['ALIGN'];
      if($tag=='HR'){
          $Width = !empty($prop['WIDTH']) ? $prop['WIDTH'] : $this->w - $this->lMargin-$this->rMargin;
          $this->Ln(2);
          $x = $this->GetX(); $y = $this->GetY();
          $this->SetLineWidth(0.4);
          $this->Line($x,$y,$x+$Width,$y);
          $this->SetLineWidth(0.2);
          $this->Ln(2);
      }
  }
  function CloseTag($tag)
  {
      if($tag=='B' || $tag=='I' || $tag=='U') $this->SetStyle($tag,false);
      if($tag=='A') $this->HREF='';
      if($tag=='P') $this->ALIGN='';
  }
  function SetStyle($tag,$enable)
  {
      $this->$tag+=($enable ? 1 : -1);
      $style='';
      foreach(array('B','I','U') as $s)
          if($this->$s>0) $style.=$s;
      // Mantener familia actual (no forzar Arial)
      $this->SetFont($this->FontFamily,$style);
  }
  function PutLink($URL,$txt)
  {
      $this->SetTextColor(0,0,255);
      $this->SetStyle('U',true);
      $this->Write(5,$txt,$URL);
      $this->SetStyle('U',false);
      $this->SetTextColor(0);
  }

  /* =================== Bloques del layout =================== */

  // Bloque "CLIENTE" (etiqueta CenturyGothic, valores Abadi)
  function addClient($client)
  {
      // Etiqueta "CLIENTE" con Century Gothic
      $this->SetFont('CenturyGothic','',12);
      $this->Cell(30, 10, utf8_decode('CLIENTE'), 0, 1);

      // Valor en Abadi
      $this->SetTextColor(29,125,200);
      $this->SetFont('Abadi','',12);
      $this->Cell(100, 6, $client, 0, 0);

      $this->SetTextColor(0);
      $this->Ln(3);
  }

  // Bloque DESCRIPCION (barra azul como en el diseño, título con Century Gothic)
function addDescription($origen, $destino, $peso)
{
    // Barra
    $this->SetFillColor(26, 42, 76);
    $this->SetTextColor(255);
    $this->SetFont('CenturyGothic','',10.8);
    $this->Cell(192, 8, utf8_decode('DESCRIPCION'), 0, 1, 'C', true);

    // Contenido (sin bordes, interlineado parejo)
    $this->SetTextColor(0);
    $this->SetFont('Abadi','',11);
    $this->SetX(12);
    $this->MultiCell(192, 6, utf8_decode("Origen: ").$origen, 0, 'L');
    $this->SetX(12);
    $this->MultiCell(192, 6, utf8_decode("Destino: ").$destino, 0, 'L');
    $this->SetX(12);
    $this->MultiCell(192, 6, utf8_decode("Peso y dimensiones:")."\n".$peso, 0, 'L');
    $this->Ln(2);
}


  // Tabla CONCEPTO / PRECIO (encabezados Century Gothic, cuerpo Abadi)
function addConceptTable($concepts, $totalConceptos = 0.0)
{
    $cotizacion = $_GET['idcotiza'];

    // Cols y estilo
    $wConcepto = 142;   // un poco más ancho para calzar el diseño
    $wPrecio   = 50;
    $lineH     = 6;
    $padX      = 3.2;

    $headBg    = [26, 42, 76];
    $headTxt   = [255, 255, 255];
    $z1        = [247, 249, 252]; // zebra muy tenue
    $z2        = [239, 242, 245];

    // Header
    $this->SetFillColor($headBg[0], $headBg[1], $headBg[2]);
    $this->SetTextColor($headTxt[0], $headTxt[1], $headTxt[2]);
    $this->SetFont('CenturyGothic','',10.8);
    $this->Cell($wConcepto, 8, utf8_decode('CONCEPTO'), 0, 0, 'L', true);
    $this->Cell($wPrecio,   8, utf8_decode('PRECIO'),   0, 1, 'R', true);

    // Cuerpo (sin bordes)
    $this->SetTextColor(0);
    $this->SetFont('Abadi','',10.6);

    $rowIndex = 0;
    foreach ($concepts as $row) {
        $concepto = (string)$row[0];
        $precio   = (string)$row[1];

        // Alto dinámico por wrap
        $nbConcept = $this->NbLines($wConcepto - $padX*2, $concepto);
        $nbPrecio  = $this->NbLines($wPrecio   - $padX*2, $precio);
        $nb        = max($nbConcept, $nbPrecio);
        $h         = $lineH * $nb;

        $this->CheckPageBreak($h);

        // Zebra sólo de fondo (sin marco)
        $bg = ($rowIndex % 2 === 0) ? $z1 : $z2;
        $this->SetFillColor($bg[0], $bg[1], $bg[2]);

        $x = $this->GetX(); $y = $this->GetY();
        $this->Rect($x, $y, $wConcepto + $wPrecio, $h, 'F');

        // Concepto
        $this->SetXY($x + $padX, $y + 1.2);
        $this->MultiCell($wConcepto - $padX*2, $lineH, $concepto, 0, 'L');

        // Precio
        $this->SetXY($x + $wConcepto + $padX, $y + 1.2);
        $this->MultiCell($wPrecio - $padX*2, $lineH, $precio, 0, 'R');

        $this->SetY($y + $h);
        $rowIndex++;
    }

    // Totales (idéntico estilo del diseño)
    $subtotal   = (float)$totalConceptos;
    $diva       = busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_diva') ? 1 : 0;
    $dretencion = busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_dretencion') ? 1 : 0;

    $iva         = $diva ? ($subtotal * 0.16) : 0;
    $retencion   = $dretencion ? ($subtotal * 0.04) : 0;
    $total       = ($subtotal + $iva) - $retencion;

    // Línea suave separadora
    $this->Ln(1);
    $this->SetDrawColor(205, 209, 213);
    $this->SetLineWidth(0.2);
    $this->Cell($wConcepto + $wPrecio, 0, '', 'T', 1);
    $this->Ln(2);

    // Filas de totales
    $this->SetFont('Abadi','',11);

    if ($diva) {
        $this->Cell($wConcepto, 7, utf8_decode('IVA 16%'), 0, 0, 'L');
        $this->SetFont('CenturyGothic','',11);
        $this->Cell($wPrecio,   7, '$ ' . number_format($iva, 2), 0, 1, 'R');
        $this->SetFont('Abadi','',11);
    }

    $this->Cell($wConcepto, 7, utf8_decode('SUBTOTAL'), 0, 0, 'L');
    $this->SetFont('CenturyGothic','',11);
    $this->Cell($wPrecio,   7, '$ ' . number_format($subtotal + $iva, 2), 0, 1, 'R');
    $this->SetFont('Abadi','',11);

    if ($dretencion) {
        $this->Cell($wConcepto, 7, utf8_decode('RETENCION 4%'), 0, 0, 'L');
        $this->SetFont('CenturyGothic','',11);
        $this->Cell($wPrecio,   7, '- $ ' . number_format($retencion, 2), 0, 1, 'R');
        $this->SetFont('Abadi','',11);
    }

    // Separador antes de TOTAL
    $this->Ln(1);
    $this->SetDrawColor(165, 170, 175);
    $this->Cell($wConcepto + $wPrecio, 0, '', 'T', 1);
    $this->Ln(1.5);

    // TOTAL en azul
    $this->SetFont('CenturyGothic','',13.2);
    $this->SetTextColor(26, 42, 76);
    $this->Cell($wConcepto, 8.5, utf8_decode('TOTAL'), 0, 0, 'L');
    $this->SetFont('CenturyGothic','',14);
    $this->Cell($wPrecio,   8.5, '$ ' . number_format($total, 2), 0, 1, 'R');
    $this->SetTextColor(0,0,0);

    $this->Ln(2);
}


  // Bloque TÉRMINOS Y CONDICIONES (título Century Gothic, lista Abadi)
// Bloque TÉRMINOS Y CONDICIONES (con ajuste de líneas y salto de página)
function addTerms($terms)
{
    // Parámetros de estilo
    $left   = 12;        // mismo margen que usas
    $width  = 192;       // 210 - (12+12); para Letter con tus márgenes
    $lineH  = 5.6;       // alto de línea del texto

    // Función interna: pinta la barra de título
    $drawHeader = function($suffix = ''){
        $this->SetFillColor(26, 42, 76);
        $this->SetTextColor(255);
        $this->SetFont('CenturyGothic','',10.8);
        $this->SetX($left);
        $this->Cell($width, 8, utf8_decode('TERMINOS Y CONDICIONES'.$suffix), 0, 1, 'L', true);
    };

    // Título en la página actual
    $drawHeader('');

    // Texto
    $this->SetTextColor(0);
    $this->SetFont('Abadi','',10.6);

    foreach ($terms as $term) {
        $text = '- ' . $term;

        // Si ya no cabe al menos una línea + un pequeño margen, saltamos de página y
        // reimprimimos la barra del bloque para continuidad.
        if ($this->GetY() + $lineH + 8 > $this->PageBreakTrigger) {
            $this->AddPage();
            $drawHeader(' (CONT.)');
            $this->SetTextColor(0);
            $this->SetFont('Abadi','',10.6);
        }

        // Imprime el item con MultiCell para envolver texto largas
        $this->SetX($left);
        $this->MultiCell($width, $lineH, utf8_decode($text), 0, 'L');
    }
}



  function addFooterInfo($name, $position, $email)
  {
      $this->SetFont('Abadi','',10);
      $this->Ln(1);
      $this->SetFont('CenturyGothic','',10);
      $this->SetTextColor(29,125,200);
      $this->Cell(190, 6, utf8_decode($name), 0, 1, 'R');

      $this->SetFont('Abadi','',10);
      $this->SetTextColor(0);
      $this->Cell(190, 6, utf8_decode($position), 0, 1, 'R');
      $this->Cell(190, 6, utf8_decode($email), 0, 1, 'R');
  }
}

/* =================== Generación del PDF =================== */

// Carta (Letter) en mm
$pdf = new PDF("P","mm","Letter");
$pdf->SetMargins(12, 36, 12);
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddPage();

// === Registrar fuentes ===
// (asegúrate de tener los .php/.z en ../lib/fpdf/font/)

$pdf->AddFont('CenturyGothic','', 'GOTHIC.php');   // regular
$pdf->AddFont('CenturyGothic','B','GOTHICB.php');  // bold
$pdf->AddFont('Abadi','',  'AbadiMTStd.php');         // regular
$pdf->AddFont('Abadi','B', 'AbadiMTStd-Bold.php');    // bold

// (si quieres seguir teniendo Kalinga para subtítulos azules, puedes mantenerla)
$pdf->AddFont('kalinga','','kalinga.php');
$pdf->AddFont('kalingab','','kalingab.php');

$pdf->SetY(35);

/* ========== Datos ========= */
include_once('../modulos/cotizaciones.php');
$cotiza = new modelcotizaciones();
$cotizacion = $_GET['idcotiza'];
$cotiza->select($_GET['idcotiza']);

$cliente   = busca($cotiza->tablero, 'crm_tableros', 'ct_id', 'ct_cliente');
$client    = busca($cliente, 'crm_clientes', 'c_id', 'c_nmb').' '.busca($cliente, 'crm_clientes', 'c_id', 'c_apellidos');
$vendedor  = busca($cotiza->responsable, 'usuarios', 'u_id' ,'CONCAT(u_nmb, " ", u_apellidos)');
$puestov   = busca($cotiza->responsable, 'usuarios', 'u_id' ,'u_puesto');
$correov   = busca($cotiza->responsable, 'usuarios', 'u_id' ,'u_correo');
$origen    = busca($cotiza->motivo, 'ruta_origen', 'ro_id', 'ro_nombre');
$destino   = busca($cotiza->dirdestino, 'ruta_destino', 'rd_id', 'rd_nombre');
if(!$destino)
  $destino = busca($cotiza->direnvio, 'crm_direcciones', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi," ",cd_colonia," ",cd_municipio," ",cd_cp)');

$peso = $cotiza->pesomercancia." Kg aprox\n".$cotiza->largomercancia." Largo X ".$cotiza->anchomercancia." Ancho X ".$cotiza->altomercancia." Alto. Unidad de medida: ".$cotiza->unidadmedida;

$concepts = [];
$totalConceptos = 0.0;

if($cotiza->tipokm != "proveedor"){
  $sql = 'SELECT * FROM crm_cotizacionesd WHERE cdm_cotizacion = "'.$cotizacion.'"';
  $result = setq($sql);
  while($row = $result -> fetch_array()){
    $descripcion = utf8_decode($row['cdm_nmbarticulo']);

    $especial = (float)$cotiza->especial;
    if($especial != 0){
      $precio = $especial;
    } else {
      $precio = (float)$cotiza->mtotal;
    }

    $totalConceptos += $precio;

    // precio con moneda en la celda (para verse como $2,450.00 USD)
    $monedaTmp = busca($cotizacion,'crm_cotizaciones','cc_id','cc_moneda');
    $precio_formateado = "$ " . number_format($precio, 2) . ' ' . $monedaTmp;

    $concepts[] = [$descripcion, $precio_formateado];
  }
} else {
  $sql = 'SELECT * FROM crm_cotizaciones_proveedor INNER JOIN proveedores ON p_id = cp_proveedor WHERE cp_cotizacion = "'.$cotiza->id.'"';
  $result = setq($sql);
  while($row = $result -> fetch_array()){
    $descripcion = utf8_decode($row['cp_concepto']);
    $precio = (float)$row['cp_costo'] + (float)$row['cp_extra'];

    $totalConceptos += $precio;

    $monedaTmp = busca($cotizacion,'crm_cotizaciones','cc_id','cc_moneda');
    $precio_formateado = "$ " . number_format($precio, 2) . ' ' . $monedaTmp;

    $concepts[] = [$descripcion, $precio_formateado];
  }
}

$terms = array();
$sqlcon = 'SELECT * FROM crm_cotizaciones_condiciones WHERE cf_cotizacion = "'.$cotizacion.'"';
$resultcon = setq($sqlcon);
while($rowcon = $resultcon -> fetch_array()){
  $terms[] = $rowcon['cf_descripcion'];
}

$moneda        = busca($cotizacion,'crm_cotizaciones','cc_id','cc_moneda');
$preciodolares = busca($cotizacion,'crm_cotizaciones','cc_id','cc_preciodolares');

/* ===== ENCABEZADO tipo del diseño ===== */
$pdf->SetY(35);

$leftX  = 12;   // margen izq definido
$rightX = 132;  // columna derecha
$topY   = 40;

// Folio (6 dígitos)
$folioSolo  = substr((string)$cotiza->folio, -6);
$numeroCotizacion = str_pad($folioSolo, 6, '0', STR_PAD_LEFT);
$folioFmt   = str_pad($cotiza->cliente.$numeroCotizacion, 3, '0', STR_PAD_LEFT);

// Fecha en ES y mayúsculas
$fechaUpper = mb_strtoupper(fechaEnEspanol(date('Y-m-d')), 'UTF-8');

// Folio (6 dígitos)
$folioSolo  = substr((string)$cotiza->folio, -6);
$numeroCotizacion = str_pad($folioSolo, 6, '0', STR_PAD_LEFT);
$folioFmt   = str_pad($cotiza->cliente.$numeroCotizacion, 3, '0', STR_PAD_LEFT);

// Fecha en ES y mayúsculas
$fechaUpper = mb_strtoupper(fechaEnEspanol(date('Y-m-d')), 'UTF-8');

// Título COTIZACIÓN (Century Gothic + altura/kerning visual similar)
$pdf->SetXY($leftX, $topY);
$pdf->SetTextColor(0);
$pdf->SetFont('CenturyGothic','',36);
$pdf->Cell(110, 14, utf8_decode('COTIZACIÓN'), 0, 1, 'L');

// Columna derecha (alineación y jerarquía)
$pdf->SetXY($rightX, $topY - 2);
$pdf->SetFont('Abadi','',10);
$pdf->SetTextColor(75,75,75);
$pdf->Cell(66, 6, utf8_decode('N°. ').$folioFmt, 0, 1, 'R');

$pdf->SetX($rightX);
$pdf->Cell(66, 6, utf8_decode($fechaUpper), 0, 1, 'R');

$pdf->SetX($rightX);
$pdf->SetFont('CenturyGothic','',12);
$pdf->SetTextColor(26,42,76);
$pdf->Cell(66, 6, utf8_decode($vendedor), 0, 1, 'R');

$pdf->SetX($rightX);
$pdf->SetFont('Abadi','',10);
$pdf->SetTextColor(0);
$pdf->Cell(66, 5.8, utf8_decode($correov), 0, 1, 'R');

// >>>>>>>>>>>>>>>> AQUI VA EL BLOQUE CLIENTE <<<<<<<<<<<<<<<<<
$yCliente = $topY + 20;
$pdf->SetXY($leftX, $yCliente);

// Etiqueta gris (Century Gothic)
$pdf->SetFont('CenturyGothic','B',12.2);
$pdf->SetTextColor(120,120,120);
$pdf->Cell(0, 5, utf8_decode('CLIENTE'), 0, 1, 'L');

// Alias empresa (Abadi)
$pdf->SetX($leftX);
$pdf->SetFont('Abadi','',11.2);
$pdf->SetTextColor(0);
$empresaAlias = busca($cotiza->cliente, 'crm_clientes', 'c_id', 'c_empresa');
$pdf->Cell(0, 6, utf8_decode("".$empresaAlias), 0, 1, 'L');

// Contacto subrayado azul (Abadi U)
$contactoCliente = busca($cotiza->cliente, 'crm_clientes', 'c_id', 'c_nmb');
$pdf->SetX($leftX);
$pdf->SetFont('Abadi','U',11.2);
$pdf->SetTextColor(26,42,76);
$pdf->Cell(0, 6, utf8_decode($contactoCliente), 0, 1, 'L');

// Reset
$pdf->SetFont('Abadi','',11);
$pdf->SetTextColor(0);
// >>>>>>>>>>>>>>>> FIN BLOQUE CLIENTE <<<<<<<<<<<<<<<<<<<<<<<

// Moneda / tipo de cambio con el mismo interlineado azul del diseño
$pdf->SetX($rightX);
$pdf->SetFont('CenturyGothic','',9.8);
$pdf->SetTextColor(26,42,76);
$pdf->Cell(66, 5.8, utf8_decode('MONEDA: '.$moneda), 0, 1, 'R');

if ($moneda != 'USD') {
    $pdf->SetX($rightX);
    $pdf->SetFont('CenturyGothic','',9.8);
    $pdf->Cell(66, 5.8, utf8_decode('TIPO CAMBIO: $'.number_format((float)$preciodolares,2)), 0, 1, 'R');
}

// DESCRIPCION
$pdf->addDescription($origen, $destino, $peso);

// CONCEPTO / PRECIO
$pdf->addConceptTable($concepts, $totalConceptos);

// TÉRMINOS Y CONDICIONES
$pdf->addTerms($terms);

// Firma / datos (si quieres mostrarlos)
$pdf->addFooterInfo("", "", "");

/* ===== Salida ===== */
if(isset($_GET['descarga'])){
  $pdf->Output('cotizacion - '.$cotiza->folio.'.pdf', 'D');
} else {
  $pdf->Output(busca($_GET['idcotiza'],'crm_cotizaciones','cc_id','cc_nmb').'.pdf','I');
}

/* ===== Utilidad: fecha en español ===== */
function fechaEnEspanol($fechaISO) {
    $meses = [
        '01' => 'enero','02' => 'febrero','03' => 'marzo','04' => 'abril',
        '05' => 'mayo','06' => 'junio','07' => 'julio','08' => 'agosto',
        '09' => 'septiembre','10' => 'octubre','11' => 'noviembre','12' => 'diciembre'
    ];
    $fecha = new DateTime($fechaISO);
    $dia  = $fecha->format('d');
    $mes  = $fecha->format('m');
    $anio = $fecha->format('Y');
    return "$dia de " . $meses[$mes] . " de $anio";
}
?>
