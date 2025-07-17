<?php
include('../funciones.php');
include('../lib/fpdf/fpdf.php');
session_start();
ini_set('display_errors', 1);

class PDF extends FPDF
{
  var $widths;
  var $aligns;
  var $alto;

  function Header()
  {
    //if(isset($_GET['uuid'])) $_GET['ordenp'] = busca($_GET['uuid'],'crm_cotizaciones','cc_uuid','cc_id');
    /* $sql='SELECT * FROM crm_cotizaciones WHERE cc_id="'.$_GET['ordenp'].'"';
    $rs=setq($sql) or die($sql);
    $rw= $rs->fetch_array();
    $empresa = busca($_GET['ordenp'],'crm_cotizaciones INNER JOIN crm_tableros ON ct_id = cc_tablero','cc_id','ct_empresa'); */

    include_once('../modulos/config.php');
    $emp = new modelconfig();
    $emp->select($empresa);

    $sql = 'SELECT * FROM almacenes WHERE a_id = "1"';
    $result = setq($sql);
    $rowalm = $result->fetch_array();

    $imglogo = busca('1', 'empresas', 'e_id', 'e_logo');

    if (!empty($imglogo))
      $logo = '../' . $imglogo;
    else
      $logo = NULL;



    //if(file_exists('../'.$emp->logo) && $emp->logo != NULL) $logo = '../'.$emp->logo;
    //else $logo = NULL;

    include_once('../modulos/cotizaciones.php');
    $cotiza = new modelcotizaciones();
    $cotiza->select($_GET['ordenp']);
    if ($cotiza->estatus != "A" && $cotiza->estatus != "V") {
      $this->Image('../assets/media/preview.png', 5, 30, 200);
    }

    $sqlop = 'SELECT * FROM pr_procesosop WHERE pp_ordenp = "' . $_GET['ordenp'] . '"';
    $result = setq($sqlop);


    // Logo
    if ($logo)
      $this->Image($logo, 163, 2, 30);
    //$this->Image($logo,163,2,35);
    // Arial bold 15
    $this->AddFont('kalinga', '', 'kalinga.php');
    $this->AddFont('kalingab', '', 'kalingab.php');
    $this->SetFont('kalinga', '', 8);
    // Movernos a la derecha
    $this->Cell(80);
    $this->SetDrawColor(100, 152, 201);
    $this->Line(160, 10, 160, 30);
    $this->SetTextColor(0, 0, 0);
    // Título

    // Establecer el tamaño de letra a un valor más grande (por ejemplo, 14)
    $this->SetFont('Arial', '', 14);
    // Agregar el texto centrado
    $this->Cell(-13, 20, utf8_decode('HOJA VIAJERA'), 0, 1, 'C');
    $this->SetFont('kalinga', '', 10);
    $this->SetTextColor(0, 0, 0);
  }

  // Pie de página
  function Footer()
  {
    // Posición: a 1,5 cm del final
    $this->SetY(-15);
    $this->SetFont('kalinga', '', 11);
    /*
      $this->Cell(0,5,utf8_decode('José Luis Reyes Benítez'),0,1,'C');
      $this->Ln(2);
      $this->Cell(0,5,'REBL8905188J8',0,1,'C');
      $this->Ln();
    */
    $this->SetFont('kalinga', '', 11);
    //  $this->Cell(0,5,'___________________________________',0,1,'C');
    //$this->Cell(0,5,utf8_decode($fila['c_id']),0,1,'C');
    // Arial italic 8
    $this->SetFont('kalinga', '', 8);
    // Número de página
    $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
  }
  function SetWidths($w)
  {
    //Ajustar la gama del ancho de columna
    $this->widths = $w;
  }

  function SetAligns($a)
  {
    //ajusta la alineacion en el arreglo
    $this->aligns = $a;
  }

  function Row($data, $bandera)
  {
    //Calcular la altura de la fila
    $nb = 0;
    for ($i = 0; $i < count($data); $i++)
      $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
    $h = 9 * $nb;
    //Emitir un salto de página primera, si es necesario
    $this->CheckPageBreak($h);
    if ($bandera == true)
      $rellenar = 'FD';
    if ($bandera == false)
      $rellenar = 'D';
    //Dibuja las celdas de la fila
    for ($i = 0; $i < count($data); $i++) {
      $w = $this->widths[$i];
      $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
      //Guardar la posición actual
      $x = $this->GetX();
      $y = $this->GetY();
      //dibuja la tabla
      $this->Rect($x, $y, $w, $h, $rellenar);
      //solo imprime el texto
      $this->MultiCell($w, 5, $data[$i], 0, $a);
      //Put the position to the right of the cell
      $this->SetXY($x + $w, $y);
    }
    //Ir a la siguiente línea
    $this->Ln($h);
    $bandera = !$bandera;
  }

  function RowMin($data, $bandera)
  {
    // Calcular la altura de la fila
    $nb = 0;
    for ($i = 0; $i < count($data); $i++) {
      $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
    }
    $h = 4.5 * $nb;

    // Emitir un salto de página primera, si es necesario
    $this->CheckPageBreak($h);

    // Definir colores de fondo para celda
    if ($bandera) {
      $this->SetFillColor(0, 0, 0); // Negro
    } else {
      $this->SetFillColor(92, 156, 212); // Blanco (o el color que desees)
    }

    // Dibuja las celdas de la fila
    for ($i = 0; $i < count($data); $i++) {
      $w = $this->widths[$i];
      $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';

      // Guardar la posición actual
      $x = $this->GetX();
      $y = $this->GetY();

      // Dibujar la celda con color de fondo
      $this->Rect($x, $y, $w, $h, 'F');

      // Solo imprime el texto
      $this->SetTextColor(255, 255, 255); // Texto en blanco para contrastar con el fondo negro
      $this->SetFont('kalinga', '', 7);

      // Ajustar posición y altura solo para $nmbp y $estatus
      if ($i == 0 || $i == (count($data) - 1)) {
        $offsetY = ($h - $this->FontSize) / 2;
        $this->SetXY($x, $y + $offsetY);
        $this->MultiCell($w, 4.5, $data[$i], 0, $a);
      } else {
        $this->MultiCell($w, 4.5, $data[$i], 0, $a);
      }

      // Restaurar color de texto y posición XY
      $this->SetTextColor(0, 0, 0); // Restaurar color de texto a negro
      $this->SetXY($x + $w, $y);
    }

    // Ir a la siguiente línea
    $this->Ln($h);
    $bandera = !$bandera;
  }



  function RowMinImg($data, $bandera, $valida)
  {
    //Calcular la altura de la fila
    $nb = 0;
    for ($i = 0; $i < count($data); $i++)
      $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
    $h = 5 * $nb;
    //Emitir un salto de p�gina primera, si es necesario
    $this->CheckPageBreak($h);
    if ($bandera == true)
      $rellenar = 'FD';
    if ($bandera == false)
      $rellenar = 'D';
    //Dibuja las celdas de la fila  
    if (!$valida) {
      if ($h < 30) {
        $h = 30;
      }
    }
    for ($i = 0; $i < count($data); $i++) {
      $w = $this->widths[$i];
      $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
      //Guardar la posici�n actual
      $x = $this->GetX();
      $y = $this->GetY();
      //dibuja la tabla
      $this->Rect($x, $y, $w, $h, $rellenar);
      //solo imprime el texto
      $this->SetFont('kalinga', '', 7);
      $this->MultiCell($w, 4.5, $data[$i], 0, $a);
      //Put the position to the right of the cell
      $this->SetXY($x + $w, $y);
    }

    $enviar = ($h / 2) - 13;
    $this->setAltura($enviar);

    //Ir a la siguiente l�nea
    $this->Ln($h);
    $bandera = !$bandera;
  }

  function RowMin3($data, $bandera, $valida)
  {
      // Calcular la altura de la fila
      $nb = 0;
      for ($i = 0; $i < count($data); $i++) {
          $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
      }
      $h = 5 * $nb;
  
      // Emitir un salto de página primera, si es necesario
      $this->CheckPageBreak($h);
  
      if ($bandera == true)
          $rellenar = 'FD';
      if ($bandera == false)
          $rellenar = 'D';
  
      // Dibuja las celdas de la fila  
      if (!$valida) {
          if ($h < 30) {
              $h = 30;
          }
      }
  
      $xStart = $this->GetX(); // Guardar la posición X inicial
      for ($i = 0; $i < count($data); $i++) {
          $w = $this->widths[$i];
          $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';          
  
          // Dibuja la celda
          $this->Rect($this->GetX(), $this->GetY(), $w, $h, $rellenar);
  
          // Solo imprime el texto
          if($i == 0){
            $tamanio = 8;
            $letra = 'Arial';
            $estilo = 'B';
          } else{
            $tamanio = 7;
            $letra = 'kalinga';
            $estilo = '';
          }
          $this->SetFont($letra, $estilo, $tamanio);
          $this->SetXY($this->GetX(), $this->GetY());
          $this->Cell($w, $h, $data[$i], 0, 0, $a);
      }
  
      $this->SetX($xStart); // Restaurar la posición X inicial
      $this->Ln($h); // Ir a la siguiente línea
      $bandera = !$bandera;
  }
  


  function setAltura($altura)
  {
    $this->altura = $altura;
  }

  function getAltura()
  {
    return $this->altura;
  }

  function CheckPageBreak($h)
  {
    //Si la altura h provocaría un desbordamiento, añadir una nueva página de inmediato
    if (($this->GetY() + $h) + 10 > $this->PageBreakTrigger)
      $this->AddPage($this->CurOrientation);
    if ($this->prod != $this->PageNo()) {
      $this->Ln(10);
      $this->Cell(4);
    }
  }

  function NbLines($w, $txt)
  {
    //Calcula el número de líneas de un MultiCell de anchura w tomará
    $cw =& $this->CurrentFont['cw'];
    if ($w == 0)
      $w = $this->w - $this->rMargin - $this->x;
    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
    $s = str_replace("\r", '', $txt);
    $nb = strlen($s);
    if ($nb > 0 and $s[$nb - 1] == "\n")
      $nb--;
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $nl = 1;
    while ($i < $nb) {
      $c = $s[$i];
      if ($c == "\n") {
        $i++;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
        continue;
      }
      if ($c == ' ')
        $sep = $i;
      $l += $cw[$c];
      if ($l > $wmax) {
        if ($sep == -1) {
          if ($i == $j)
            $i++;
        } else
          $i = $sep + 1;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
      } else
        $i++;
    }
    return $nl;
  }
  function GenerateWord()
  {
    //Get a random word
    $nb = rand(3, 10);
    $w = '';
    for ($i = 1; $i <= $nb; $i++)
      $w .= chr(rand(ord('a'), ord('z')));
    return $w;
  }

  function paquete($id, $nmb, $idcd)
  {
    $s = $nmb . '';

    $sql = 'select * from crm_cotizacionesdd where cdd_idd="' . $idcd . '" ORDER BY cdd_id ASC';
    $res = setq($sql) or die($sql);
    while ($rw = $res->fetch_array()) {
      $s .= '
-' . trim(utf8_decode($rw['cdd_nmb']));
    }

    $sqlart = 'SELECT a_tipoprod, a_mdetalle, a_peso, a_largo, a_ancho, a_alto FROM articulos WHERE a_id = "' . $id . '"';
    $resart = setq($sqlart);
    list($tipoprod, $mdetalle, $peso, $largo, $ancho, $alto) = $resart->fetch_array();

    if ($tipoprod != "M" && $mdetalle == "1") {
      $s .= "\n";
      $s .= '' . trim(utf8_decode("DIMENSIONES:")) . "\n";
      $s .= ' -' . trim(utf8_decode("Peso:" . number_format($peso, 0) . ' kilos')) . "\n";
      $s .= ' -' . trim(utf8_decode("Largo:" . number_format($largo, 0) . ' metros')) . "\n";
      $s .= ' -' . trim(utf8_decode("Ancho:" . number_format($ancho, 0) . ' metros')) . "\n";
      $s .= ' -' . trim(utf8_decode("Alto:" . number_format($alto, 0) . ' metros')) . "\n";
    }

    return $s;
  }

  function WriteHTML($html)
  {
    //HTML parser
    $html = str_replace("\n", ' ', $html);
    $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($a as $i => $e) {
      if ($i % 2 == 0) {
        //Text
        if ($this->HREF)
          $this->PutLink($this->HREF, $e);
        elseif ($this->ALIGN == 'center')
          $this->Cell(0, 5, $e, 0, 1, 'C');
        else
          $this->Write(5, $e);
      } else {
        //Tag
        if ($e[0] == '/')
          $this->CloseTag(strtoupper(substr($e, 1)));
        else {
          //Extract properties
          $a2 = explode(' ', $e);
          $tag = strtoupper(array_shift($a2));
          $prop = array();
          foreach ($a2 as $v) {
            if (preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
              $prop[strtoupper($a3[1])] = $a3[2];
          }
          $this->OpenTag($tag, $prop);
        }
      }
    }
  }

  function OpenTag($tag, $prop)
  {
    //Opening tag
    if ($tag == 'B' || $tag == 'I' || $tag == 'U')
      $this->SetStyle($tag, true);
    if ($tag == 'A')
      $this->HREF = $prop['HREF'];
    if ($tag == 'BR')
      $this->Ln(5);
    if ($tag == 'P')
      $this->ALIGN = $prop['ALIGN'];
    if ($tag == 'HR') {
      if (!empty($prop['WIDTH']))
        $Width = $prop['WIDTH'];
      else
        $Width = $this->w - $this->lMargin - $this->rMargin;
      $this->Ln(2);
      $x = $this->GetX();
      $y = $this->GetY();
      $this->SetLineWidth(0.4);
      $this->Line($x, $y, $x + $Width, $y);
      $this->SetLineWidth(0.2);
      $this->Ln(2);
    }
  }

  function CloseTag($tag)
  {
    //Closing tag
    if ($tag == 'B' || $tag == 'I' || $tag == 'U')
      $this->SetStyle($tag, false);
    if ($tag == 'A')
      $this->HREF = '';
    if ($tag == 'P')
      $this->ALIGN = '';
  }

  function SetStyle($tag, $enable)
  {
    //Modify style and select corresponding font
    $this->$tag += ($enable ? 1 : -1);
    $style = '';
    foreach (array('B', 'I', 'U') as $s)
      if ($this->$s > 0)
        $style .= $s;
    $this->SetFont('', $style);
  }

  function PutLink($URL, $txt)
  {
    //Put a hyperlink
    $this->SetTextColor(0, 0, 255);
    $this->SetStyle('U', true);
    $this->Write(5, $txt, $URL);
    $this->SetStyle('U', false);
    $this->SetTextColor(0);
  }
}

$pdf = new PDF("P", "mm", "Letter");
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);
$pdf->SetFont('Arial', '', 9);

$pdf->AddFont('kalinga', '', 'kalinga.php');
$pdf->AddFont('kalingab', '', 'kalingab.php');
$pdf->SetFont('kalinga', '', 10);
$pdf->SetTextColor(0, 0, 0);

//imprimir el numero de la cotizacion
$sqlop1 = 'SELECT * FROM pr_ordenprod WHERE po_id = "' . $_GET['ordenp'] . '"';
$resultp1 = setq($sqlop1) or die($sqlop1);
$rowp1 = $resultp1->fetch_array();
$pdf->Cell(38, 8, utf8_decode('Orden de producción:'), 0, 0);
$pdf->Cell(0, 8, utf8_decode($rowp1['po_folio']), 0, 1);
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$pdf->SetFont('kalingab', '', 10);
$pdf->Cell(180, 6, 'Pachuca de Soto, Hgo. ' . date('d') . ' de ' . $meses[date('n') - 1] . ' de ' . date('Y') . '', 0, 1, 'R');

if (isset($_GET['L'])) {
  $pdf->SetFont('kalingab', '', 10);
  $pdf->Cell(23, 8, utf8_decode('Dirección:'), 0, 0, 'L');
  $pdf->SetFont('kalinga', '', 10);
  $direnvio = busca($cotiza->direnvio, 'crm_direcciones INNER JOIN estado ON cd_estado = e_id', 'cd_id', 'CONCAT(cd_calle," ",cd_nume," ",cd_numi," ",cd_colonia," ",cd_municipio," ",e_nmb," ",cd_cp)');
  $pdf->Cell(0, 8, utf8_decode($direnvio), 0, 1, 'L');
} else {
  $pdf->Cell(4);
  $pdf->MultiCell(180, 5, $texto, 0, 'J');
  //$pdf->Ln(3);
//tabla de los productos de la cotizacion
  $pdf->Cell(4);
  $foliosol = busca($rowp1['po_solicitud'], 'pr_solicitudes', 'ps_id', 'ps_folio');
  if ($rowp1['po_estatus'] == "N") {
    $estatus = "NUEVA";
  } else if ($rowp1['po_estatus'] == "P") {
    $estatus = "EN EJECUCIÓN";
  } else if ($rowp1['po_estatus'] == "F") {
    $estatus = "FINALIZADA";
  } else {
    $estatus = "CANCELADA";
  }

  $fini = fecha_formato($rowp1['po_fgen'], true, false);
  if (empty($rowp1['po_ffin'])) {
    $ffin = "SIN FINALIZAR";
  } else {
    $ffin = fecha_formato($rowp1['po_ffin'], true, false);
  }
  $nprocesos = busca($_GET['ordenp'], 'pr_procesosop', 'pp_ordenp', 'COUNT(*)');

  $pdf->SetFillColor(91, 155, 213); //relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223); //colorea las lineas de las celdas de la tabla
  $pdf->SetFont('kalingab', '', 8);
  $pdf->SetTextColor(255, 255, 255);

  //###definir el ancho de columna
  $col1 = 44;
  $col2 = 22;
  $col3 = 42;
  $col4 = 41;
  $col5 = 41;
  $pdf->Cell($col1, 7, utf8_decode('Artículo'), 1, 0, 'C', true);
  $pdf->Cell($col2, 7, 'Cantidad', 1, 0, 'C', true);
  $pdf->Cell($col3, 7, utf8_decode('Solicitud de producción'), 1, 0, 'C', true);
  $pdf->Cell($col4, 7, utf8_decode('Generó'), 1, 0, 'C', true);
  $pdf->Cell($col5, 7, 'Encargado', 1, 0, 'C', true);
  $pdf->Ln(7);

  //INICIO FILA 1 DE DETALLES DE LA OP
  $array1 = array(
    utf8_decode($rowp1['po_nmbarticulo']),
    utf8_decode($rowp1['po_cantidad']),
    utf8_decode($foliosol),
    utf8_decode($rowp1['po_ugen']),
    utf8_decode($rowp1['po_encargado'])
  );
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFont('kalinga', '', 8);
  $pdf->SetFillColor(214, 230, 244); //relleno alternado de la tabla
  $pdf->SetWidths(array($col1, $col2, $col3, $col4, $col5));
  $pdf->Cell(4);
  $pdf->prod = $pdf->PageNo();
  $pdf->SetFillColor(214, 230, 244); // Relleno alternado de la tabla
  $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
  $pdf->RowMinImg($array1, false, true);
  //FIN FILA 1 DE DETALLES DE LA OP


  $pdf->SetFillColor(91, 155, 213); //relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223); //colorea las lineas de las celdas de la tabla
  $pdf->SetFont('kalingab', '', 8);
  $pdf->SetTextColor(255, 255, 255);
  $pdf->Cell(4);
  $pdf->MultiCell(180, 5, $texto, 0, 'J');
  $pdf->Cell(4);
  $col1 = 38;
  $col2 = 65;
  $col3 = 65;
  $col4 = 22;
  $pdf->Cell($col1, 7, 'Estatus', 1, 0, 'C', true);
  $pdf->Cell($col2, 7, 'Fecha inicio', 1, 0, 'C', true);
  $pdf->Cell($col3, 7, 'Fecha final', 1, 0, 'C', true);
  $pdf->Cell($col4, 7, 'No. procesos', 1, 0, 'C', true);
  $pdf->Ln(7);


  //INICIO FILA 1 DE DETALLES DE LA OP
  $array1 = array(
    utf8_decode($estatus),
    utf8_decode($fini),
    utf8_decode($ffin),
    utf8_decode($nprocesos)
  );
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFont('kalinga', '', 8);
  $pdf->SetFillColor(214, 230, 244); //relleno alternado de la tabla
  $pdf->SetWidths(array($col1, $col2, $col3, $col4, $col5));
  $pdf->Cell(4);
  $pdf->prod = $pdf->PageNo();
  $pdf->SetFillColor(214, 230, 244); // Relleno alternado de la tabla
  $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
  $pdf->RowMinImg($array1, false, true);
  //FIN FILA 1 DE DETALLES DE LA OP

  $pdf->Ln(14);

  // Establecer el tamaño de letra a un valor más grande (por ejemplo, 14)
  $pdf->SetFont('Arial', '', 14);

  // Agregar el texto centrado
  $pdf->Cell(0, 0, utf8_decode('Procesos de la orden de producción'), 0, 1, 'C');

  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFont('kalinga', '', 8);
  $bandera = true;
  $pdf->SetFillColor(214, 230, 244); //relleno alternado de la tabla
}
$pdf->Cell(4);
$pdf->MultiCell(180, 5, $texto, 0, 'J');
//$pdf->Ln(3);
//tabla de los productos de la cotizacion
$pdf->Cell(4);
$sql = 'SELECT * FROM pr_procesosop
       WHERE pp_ordenp = "' . $_GET['ordenp'] . '" ORDER BY pp_orden ASC';
$resultado = setq($sql) or die($sql);
$z = 0;
$totalfilas = $resultado->num_rows;
while ($row = $resultado->fetch_array()) {
  $pdf->SetFillColor(91, 155, 213); //relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223); //colorea las lineas de las celdas de la tabla
  $pdf->SetFont('kalingab', '', 8);
  $pdf->SetTextColor(255, 255, 255);
  $pdf->Cell(4);
  $pdf->MultiCell(180, 5, $texto, 0, 'J');
  $pdf->Cell(4);
  $col1 = 60;
  $col2 = 25;
  $col3 = 50;
  $col4 = 25;
  $col5 = 30;
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->Cell($col1, 7, utf8_decode('Proceso'), 1, 0, 'C', true);
  $pdf->SetFont('kalingab', '', 8);
  $pdf->Cell($col2, 7, utf8_decode('Tipo'), 1, 0, 'C', true);
  $pdf->Cell($col3, 7, utf8_decode('Fase'), 1, 0, 'C', true);
  $pdf->Cell($col4, 7, utf8_decode('Planta'), 1, 0, 'C', true);
  $pdf->Cell($col5, 7, utf8_decode('Tipo de proceso'), 1, 0, 'C', true);
  $pdf->Ln(7);

  $nmbp = busca($row['pp_proceso'], 'pr_procesos', 'ppr_id', 'ppr_nmb');
  $tipo = busca($row['pp_proceso'], 'pr_procesos', 'ppr_id', 'ppr_tipo');

  if ($tipo == "O") {
    $tipop = "OTRO";
  } else if ($tipo == "C") {
    $tipop = "CORTE";
  } else {
    $tipop = "IMPRESIÓN";
  }

  $planta = busca($row['pp_proceso'], 'pr_procesos', 'ppr_id', 'ppr_planta');
  $fase = busca($row['pp_fase'], 'pr_fases', 'pf_id', 'pf_nmb');
  $planta = busca($planta, 'pr_plantas', 'pp_id', 'pp_nmb');

  if ($row['pp_tipoproceso'] == "C") {
    $tproceso = "CRONOMETRADO";
  } else {
    $tproceso = "MANUAL";
  }

  //INICIO FILA 1 DE DETALLES DE LA OP
  $array1 = array(
    utf8_decode($nmbp),
    utf8_decode($tipop),
    utf8_decode($fase),
    utf8_decode($planta),
    utf8_decode($tproceso)
  );
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFont('kalinga', '', 8);
  $pdf->SetFillColor(214, 230, 244); //relleno alternado de la tabla
  $pdf->SetWidths(array($col1, $col2, $col3, $col4, $col5));
  $pdf->Cell(4);
  $pdf->prod = $pdf->PageNo();
  $pdf->SetFillColor(214, 230, 244); // Relleno alternado de la tabla
  $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
  $pdf->RowMin3($array1, false, true);
  //FIN FILA 1 DE DETALLES DE LA OP

  $pdf->SetFillColor(91, 155, 213); //relleno del encabezado de una tabla
  $pdf->SetDrawColor(132, 179, 223); //colorea las lineas de las celdas de la tabla
  $pdf->SetFont('kalingab', '', 8);
  $pdf->SetTextColor(255, 255, 255);
  $pdf->Cell(4);
  $pdf->MultiCell(180, 0, $texto, 0, 'J');
  $pdf->Cell(4);
  $col1 = 38;
  $col2 = 38;
  $col3 = 38;
  $col4 = 38;
  $col5 = 38;
  $pdf->Cell($col1, 7, utf8_decode('Operadores'), 1, 0, 'C', true);
  $pdf->Cell($col2, 7, utf8_decode('Duración'), 1, 0, 'C', true);
  $pdf->Cell($col3, 7, utf8_decode('Fecha inicio'), 1, 0, 'C', true);
  $pdf->Cell($col4, 7, utf8_decode('Fecha final'), 1, 0, 'C', true);
  $pdf->Cell($col5, 7, utf8_decode('Estatus'), 1, 0, 'C', true);
  $pdf->Ln(7);


  if ($row['pp_estatus'] == "N") {
    $estatus = "SIN INICIAR";
  } else if ($row['pp_estatus'] == "P") {
    $estatus = "PENDIENTE DE OPERADOR";
  } else if ($row['pp_estatus'] == "A") {
    $estatus = "EN EJECUCIÓN";
  } else if ($row['pp_estatus'] == "F") {
    $estatus = "FINALIZADO";
  }

  if (empty($row['pp_fini'])) {
    $fini = "SIN INICIAR";
  } else {
    $fini = fecha_formato($row['pp_fini'], true, false);
  }

  if (empty($row['pp_ffin'])) {
    $ffin = "SIN FINALIZAR";
  } else {
    $ffin = fecha_formato($row['pp_ffin'], true, false);
  }

  if (empty($row['pp_fini'])) {
    $fini = "SIN INICIAR";
  } else {
    $fini = $row['pp_fini'];
  }

  if (empty($row['pp_ffin'])) {
    $ffin = "SIN FINALIZAR";
  } else {
    $ffin = $row['pp_ffin'];
  }

  $operadores = '';
  $sqlppx = 'SELECT ppx_operador FROM pr_procesoexe WHERE ppx_proceso = "' . $row['pp_id'] . '" AND ppx_ordenp = "' . $_GET['ordenp'] . '" AND ppx_operador != "" GROUP BY ppx_operador';
  $resultppx = setq($sqlppx);
  while ($rowppx = $resultppx->fetch_array()) {
    if ($rowppx['ppx_tuser'] == "O") {
      $operadores .= busca($rowppx['ppx_operador'], 'pr_empleados', 'pe_id', 'pe_nmb') . " - OPERADOR" . "\n";
    } else {
      $operadores .= busca($rowppx['ppx_operador'], 'usuarios', 'u_nuser', 'u_nmb') . " " . busca($rowppx['ppx_operador'], 'usuarios', 'u_nuser', 'u_apellidos') . " - ADMIN" . "\n";
    }
  }

  if (empty($operadores)) {
    $operadores = 'SIN OPERADORES';
  }

  $duracion = "00:20:12";
  $sqld = 'SELECT ppx_id, ppx_horas, ppx_minutos, ppx_segundos FROM pr_procesoexe WHERE ppx_proceso = "' . $row['pp_id'] . '" AND ppx_ordenp = "' . $_GET['ordenp'] . '" AND ppx_operador != "" ORDER BY ppx_id DESC LIMIT 1';
  $resultd = setq($sqld);
  $duracion = $resultd->fetch_array();

  $horas = $duracion['ppx_horas'];
  $minutos = $duracion['ppx_minutos'];
  $segundos = $duracion['ppx_segundos'];

  $horas = (strlen($horas) >= 2) ? $horas : ((!empty($horas) && strlen($horas) < 2) ? "0$horas" : "00");
  $minutos = (strlen($minutos) >= 2) ? $minutos : ((!empty($minutos) && strlen($minutos) < 2) ? "0$minutos" : "00");
  $segundos = (strlen($segundos) >= 2) ? $segundos : ((!empty($segundos) && strlen($segundos) < 2) ? "0$segundos" : "00");

  $tiempo = $horas . ":" . $minutos . ":" . $segundos; //Tiempo en el contador
  //INICIO FILA 1 DE DETALLES DE LA OP
  $array2 = array(
    utf8_decode($operadores),
    utf8_decode($tiempo),
    utf8_decode($fini),
    utf8_decode($ffin),
    utf8_decode($estatus)
  );
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFont('kalinga', '', 8);
  $pdf->SetFillColor(214, 230, 244); //relleno alternado de la tabla
  $pdf->SetWidths(array($col1, $col2, $col3, $col4, $col5));
  $pdf->Cell(4);
  $pdf->prod = $pdf->PageNo();
  $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C'));
  $pdf->RowMinImg($array2, false, true);
  //FIN FILA 1 DE DETALLES DE LA OP



  //INICIAN LAS INTERACCIONES CON EL PROCESO POR PARTE DE LOS OPERADORES
  $sqlu = 'SELECT * FROM pr_procesoexe WHERE ppx_proceso = "' . $row['pp_id'] . '" AND ppx_ordenp = "' . $_GET['ordenp'] . '" ORDER BY ppx_id ASC';
  $resultu = setq($sqlu);

  if ($resultu->num_rows == 0) {
    // Muestra el mensaje centrado
    $col1 = 190;
    $pdf->Cell(4);
    $pdf->MultiCell(180, 0, $texto, 0, 'J');
    $pdf->Cell(4);
    $pdf->SetWidths(array($col1));
    $pdf->RowMinImg(array("NO HAY ELEMENTOS PARA MOSTRAR"), false, true);
  } else {
    //###definir el ancho de columna
    $col1 = 33;
    $col2 = 20;
    $col3 = 15;
    $col4 = 14;
    $col5 = 23;
    $col6 = 15;
    $col7 = 70;

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('kalinga', '', 8);
    $bandera = true;
    $pdf->SetFillColor(214, 230, 244); //relleno alternado de la tabla



    $pdf->Cell(4);
    $pdf->MultiCell(180, 0, $texto, 0, 'J');
    $pdf->Cell(4);
    $pdf->Cell($col1, 7, utf8_decode('Operador'), 1, 0, 'C', true);
    $pdf->Cell($col2, 7, utf8_decode('Fecha'), 1, 0, 'C', true);
    $pdf->Cell($col3, 7, utf8_decode('Hora'), 1, 0, 'C', true);
    $pdf->Cell($col4, 7, utf8_decode('Tiempo'), 1, 0, 'C', true);
    $pdf->Cell($col5, 7, utf8_decode('Acción'), 1, 0, 'C', true);
    $pdf->Cell($col6, 7, utf8_decode('Cantidad'), 1, 0, 'C', true);
    $pdf->Cell($col7, 7, utf8_decode('Observacion'), 1, 0, 'C', true);
    $pdf->Ln(7);
    //columna es el numero de ancho de cada columna
    $pdf->SetWidths(array($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8));
    srand(microtime() * 1000000);
    while ($rowu = $resultu->fetch_array()) {
      // Continúa con el resto de la fila
      $pdf->Cell(4);
      $pdf->SetFillColor(214, 230, 244); // Relleno alternado de la tabla
      $pdf->prod = $pdf->PageNo();
      $pdf->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'L'));
      if ($rowu['ppx_operador'] == "U") {
        $operador = busca($rowu['ppx_operador'], 'usuarios', 'u_nuser', 'u_nmb') . " " . busca($rowu['ppx_operador'], 'usuarios', 'u_nuser', 'u_apellidos');
      } else {
        $operador = busca($rowu['ppx_operador'], 'pr_empleados', 'pe_id', 'pe_nmb');
      }

      $fecha = fecha_formato($rowu['ppx_fecha'], false, true);
      $hora = $rowu['ppx_hora'];
      if ($rowu['ppx_accion'] == "1") {
        $tiempo = "00:00:00"; //Tiempo en el contador
      } else {
        $horas = $rowu['ppx_horas'];
        $minutos = $rowu['ppx_minutos'];
        $segundos = $rowu['ppx_segundos'];

        $horas = (strlen($horas) >= 2) ? $horas : ((!empty($horas) && strlen($horas) < 2) ? "0$horas" : "00");
        $minutos = (strlen($minutos) >= 2) ? $minutos : ((!empty($minutos) && strlen($minutos) < 2) ? "0$minutos" : "00");
        $segundos = (strlen($segundos) >= 2) ? $segundos : ((!empty($segundos) && strlen($segundos) < 2) ? "0$segundos" : "00");

        $tiempo = $horas . ":" . $minutos . ":" . $segundos; //Tiempo en el contador
      }

      if ($rowu['ppx_accion'] == "1") {
        $accion = "INICIO";
      } else if ($rowu['ppx_accion'] == "2") {
        $accion = "PAUSA";
      } else if ($rowu['ppx_accion'] == "3") {
        $accion = "REANUDACIÓN";
      } else {
        $accion = "FINALIZACIÓN";
      }

      $cantidad = $rowu['ppx_cantidad'];
      $observaciones = $rowu['ppx_obs'];


      $array2 = array(
        utf8_decode($operador),
        utf8_decode($fecha),
        utf8_decode($hora),
        utf8_decode($tiempo),
        utf8_decode($accion),
        utf8_decode($cantidad),
        utf8_decode($observaciones),
      );

      $pdf->RowMinImg($array2, false, true);
    }
  }
  //FINALIZAN LAS INTERACCIONES CON EL PROCESO POR PARTE DE LOS OPERADORES
  $bandera = !$bandera;
  $pdf->Ln(7);

  // Obtener la altura total de la página actual
  $alturaPagina = $pdf->h;

  // Obtener la posición vertical actual del cursor
  $posicionActual = $pdf->GetY();

  // Obtener la altura de una línea (ajusta esto según tus necesidades)
  $alturaLinea = 5;

  // Calcular el espacio restante en términos de líneas
  $lineasRestantes = ($alturaPagina - $posicionActual) / $alturaLinea;
  if (floor($lineasRestantes) < 16 && $z < ($totalfilas - 1)) {
    $pdf->AddPage();
  }

  /* die("remaingningLines: ".floor($lineasRestantes)); */

  $z++;

}

if ($cotiza->msi == "1") {
  $sqlt = 'SELECT * FROM tabuladormsi WHERE t_estatus = "A" ORDER BY t_nmb ASC';
  $resultt = setq($sqlt) or die($sqlt);
  if ($resultt->num_rows > 0) {
    //$pdf->Cell(124);
    $pdf->Cell(5);
    $pdf->Cell(60, 6, utf8_decode('Pagos con tarjetas de débito o crédito'), 1, 0, 'C', true);
    $pdf->Ln(6);
    while ($rwt = $resultt->fetch_array()) {
      $ttotal = $cotiza->importe * (1 + ($rwt['t_porcentaje'] / 100));
      $pagot = $ttotal / $rwt['t_nmb'];
      $pdf->Cell(5);
      $pdf->Cell(30, 6, $rwt['t_nmb'] . ' Pago(s) de', 1, 0, 'L', false);
      $pdf->Cell(30, 6, '$ ' . number_format($pagot, 2), 1, 0, 'R', false);
      $pdf->Ln(6);
    }
    $pdf->Cell(5);
    $pdf->Cell(60, 6, utf8_decode('Tarjetas Participantes '), 0, 0, 'L', false);
    $pdf->Ln(6);
    $pdf->Cell(5);
    $pdf->Cell(60, 6, $pdf->Image('../images/ttdc.jpg', $pdf->GetX(), $pdf->GetY(), 30, 'JPG'), 0, 0, 'L', false);
    $pdf->Ln(6);
  }
}

/* if($cotiza->estatus == "A" || $cotiza->estatus == "E")
  $pdf->Output(busca($_GET['ordenp'],'crm_cotizaciones','cc_id','cc_nmb').'.pdf','I');
else */
$pdf->Output();
?>