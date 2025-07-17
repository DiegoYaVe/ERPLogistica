<?php
ini_set('display_errors',0);
include_once('../funciones.php');
session_start();

$bandera = $_POST['bandera'];
if ($bandera == 1) {
  $chec = ' disabled';
} else {
  $chec = '';
}
$direnvio = $_POST['direnvio'];
$idcotiza = $_POST['idcotiza'];
$idremision = $_POST['idremision'];
$guia = $_POST['guia'];
$tablaAsignados = '';
$tablaNoAsignados = '';
$response = array();


function imprimircheckboxes($k, $articulotipo, $background, $idremision, $producto, $peso, $chec)
{  

  $sqlc = 'SELECT rc_paqueteria, rc_observacion, rc_fenvio, rc_guia FROM remisionesc WHERE rc_id = "' . $k . '"';
  $resultc = setq($sqlc);
  list($pqt, $obs, $fecha, $guia) = $resultc->fetch_array();

  if ($pqt == 0) {
    if ($articulotipo == "C") {
      $paqueteria = 'RECOGE EN SUCURSAL';
    } else {
      $paqueteria = 'OCURRE';
    }
  } else {
    $paqueteria = busca($pqt, 'paqueterias', 'p_id', 'p_nmb');
  }

  $guiaestatus = busca($guia, 'guias_articulos', 'ga_id', 'ga_estatus');
  if($guiaestatus == "F"){
    $chec = " disabled";
  }

  $rtn .= '
        <td class="tamanio-letra">
        <center>
          <input class="form-check-input all-check" type="checkbox" name="select' . $k . '" id="select' . $k . '" ' . $chec . '>
        </center>
        </td>';
  $rtn .= $producto;
  $rtn .= '<td class="tamanio-letra">
        <center>
            <span>' . $paqueteria . '</span>
        </center>
        </td>
        <td class="tamanio-letra">
        <center>
            <span>' . $fecha . '</span>
        </center>
        </td>
        <td class="tamanio-letra">
        <center>
            <span>' . $peso . ' KG</span>
        </center>
        </td>
        ';
  if (!isset($_POST['asignadospreviamente'])) {
    $rtn .= '
        <td class="tamanio-letra">
        <textarea class="form-control" id="obs' . $k . '" name="obs' . $k . '" style="font-size: 11px;" readonly>' . $obs . '</textarea>
        </td>';
  }
  return $rtn;
}

//----------------------------- INICIO DE LA TABLA DE LOS ASIGNADOS PREVIAMENTE -------------------------
if (isset($_POST['asignadospreviamente'])) {
  $k = 0;
  $cob = busca(busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_cp'), 'paqueterias_cobertura', 'pc_paqueteria = "1" AND pc_cp', 'pc_tipo');
  if ($cob == "0") {
    $selo = "checked";
    $selesp = "";
  } else {
    $selo = "";
    $selesp = "checked";
  }
  $sql = 'SELECT * FROM remisionesd INNER JOIN articulos ON rd_articulo = a_id WHERE rd_remision = "' . $idremision . '"';
  $result = setq($sql);

  $query0 = 'SELECT DISTINCT rc_remisiond AS rc_remisiond FROM remisionesc
              WHERE rc_remision = "' . $idremision . '"
              ORDER BY rc_remisiond ASC;';
  $result0 = setq($query0);

  $k = 0;
  $numarticulos = 0;
  $resp = 0;
  $tablaAsignados .= '<input type="hidden" value="' . $idcotiza . '" name="idcotiza">';
  $tablaAsignados .= '<input type="hidden" value="' . $idremision . '" name="idremision">';
  while ($row0 = $result0->fetch_array()) {
    //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
    $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, rd_cantidad, rd_modelo, rd_id, rd_articulo FROM remisionesd INNER JOIN articulos ON a_id = rd_articulo WHERE rd_id = "' . $row0['rc_remisiond'] . '"';
    $resultcmb = setq($sqlcmb);
    list($aid, $anmb, $tipoprod, $cantidad, $modelo, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();
    if ($tipoprod == "M") {
      for ($j = 1; $j <= intval($cantidad); $j++) {
        $nguiag = 0;
        $queryd = 'SELECT * FROM remisionesc WHERE rc_tipoenvio IN ("O","D")
                AND rc_remision = "' . $idremision . '" AND rc_remisiond = "' . $row0['rc_remisiond'] . '"
                AND rc_combo = "' . $j . '" ORDER BY rc_paqueteria, rc_id';
        
        $resultd = setq($queryd);

        if ($resultd->num_rows > 0) {
          $tablaAsignadosG .= '<tr class="tamanio-letra sin-hover2" style="background-color: #FFEE85 !important;"><td class="sin-hover tamanio-letra" colspan="6">&nbsp;&nbsp;PAQUETE - ' . $anmb . ' ' . $j . '</td></tr>';
        }
        $res = 0;
        while ($rowcb = $resultd->fetch_array()) {
          $artname = busca($rowcb['rc_articulo'], "articulos", "a_id", "a_nmb");
          $var = busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['rc_modelo'] . '" AND av_articulo', 'COUNT(*)');
          if ($var > 0) {
            $artname .= " ".busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['rc_modelo'] . '" AND av_articulo', 'av_nmb');
          }
          $artname .= " " . $rowcb['rc_numero'];
          $p1 = $rowcb['rc_id'];
          $tieneguia = $rowcb['rc_guia'];
          if (!empty($tieneguia)) {
            $nguia = intval(busca($guia, "remisionesc", "rc_id = '" . $p1 . "' AND rc_guia", "COUNT(*)"));
            if ($nguia > 0) {
              $nguiag += $nguia;
              $p2 = $rowcb['rc_tipoenvio'];
              $status = $rowcb['rc_estatus'];
              $embarcado = $rowcb['rc_embarque'];
              $esligado = $rowcb['rc_ligado'];
              if (!empty($esligado)) {
                $cadena = "**";
                $articulopadre = busca($rowcb['rc_ligado'], "remisionesc", "rc_id", "rc_articulo");
                $modelopadre = busca($rowcb['rc_ligado'], "remisionesc", "rc_id", "rc_modelo");
                $numeropadre = busca($rowcb['rc_ligado'], "remisionesc", "rc_id", "rc_numero");
                $artnameligadodos = busca($articulopadre, "articulos", "a_id", "a_nmb");
                $var = intval(busca($articulopadre, 'articulos_variantes', 'av_modelo = "' . $modelopadre . '" AND av_articulo', 'COUNT(*)'));
                if ($var > 0) {
                  $artnameligadodos .= " " . busca($articulopadre, 'articulos_variantes', 'av_modelo = "' . $modelopadre . '" AND av_articulo', 'av_nmb');
                }
                $artnameg = $artname . " DEL " . $artnameligadodos . " " . $numeropadre;
              } else {
                $cadena = "*";
                $artnameg = $artname;
              }
              $producto = '<td class="tamanio-letra">&nbsp;&nbsp;&nbsp;&nbsp; ' . $cadena . ' ' . $artnameg . '</td>';
              $peso = busca($rowcb['rc_articulo'], 'articulos', 'a_id', 'a_peso');
              $tablaAsignadosH .= '<tr class="tamanio-letra" id="tr' . $p1 . '">' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $chec) . '</tr>';
              $numarticulos++;
            }
          }
        }
        if ($nguiag > 0) {
          $tablaAsignados .= $tablaAsignadosG . $tablaAsignadosH;
          $tablaAsignados .= '<tr class="tamanio-letra sin-hover2" style="background: #FFEE85" ><td class="sin-hover tamanio-letra" colspan="6"></td></tr>';
        }
      }
    } else {
      for ($i = 0; $i < intval($cantidad); $i++) {
        $nmba = $anmb;
        $p1 = busca($cdmarticulo, "remisionesc", "rc_remision = '" . $idremision . "' AND rc_remisiond = '" . $cdmid . "' AND rc_modelo = '".$modelo."' AND rc_numero = '" . ($i + 1) . "' AND rc_articulo", "rc_id");
        $tipoenvio = busca($p1, 'remisionesc', 'rc_id', 'rc_tipoenvio');
        if (!($tipoenvio == "C" || $tipoenvio == "P")) {
          $tieneguia = busca($p1, "remisionesc", "rc_id", "rc_guia");
          $p2 = busca($cdmarticulo, "remisionesc", "rc_remision = '" . $idremision . "' AND rc_remisiond = '" . $cdmid . "' AND rc_modelo = '".$modelo."' AND rc_numero = '" . ($i + 1) . "' AND rc_articulo", "rc_tipoenvio");
          $status = busca($p1, 'remisionesc', 'rc_id', 'rc_estatus');
          $embarcado = busca($p1, 'remisionesc', 'rc_id', 'rc_embarque');
          $peso = busca($cdmarticulo, 'articulos', 'a_id', 'a_peso');
          if (!empty($tieneguia)) {
            $var = intval(busca($cdmarticulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'COUNT(*)'));
            if ($var > 0) {
              $nmba .= " " . busca($cdmarticulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'av_nmb');
            }
            $producto = '<td class="tamanio-letra">' . $nmba . ' ' . ($i + 1) . '</td>';
            $nguia = intval(busca($guia, "remisionesc", "rc_id = '" . $p1 . "' AND rc_guia", "COUNT(*)"));
            if ($nguia > 0) {
              $tablaAsignados .= '<tr class="tamanio-letra" id="tr' . $p1 . '">' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $chec) . '</tr>';
              $numarticulos++;
              $k++;
            }
          }
        }

        $sqligados = 'SELECT rc_id, rc_tipoenvio, rc_articulo, rc_modelo, rc_numero FROM remisionesc WHERE rc_ligado = "' . $p1 . '"';
        $resultligado = setq($sqligados);
        while ($rowligado = $resultligado->fetch_array()) {
          $p1 = $rowligado['rc_id'];
          $tipoenvio = busca($p1, 'remisionesc', 'rc_id', 'rc_tipoenvio');
          if (!($tipoenvio == "C" || $tipoenvio == "P")) {
            $tieneguia = busca($p1, "remisionesc", "rc_id", "rc_guia");
            if (!empty($tieneguia)) {
              $nguia = intval(busca($guia, "remisionesc", "rc_id = '" . $p1 . "' AND rc_guia", "COUNT(*)"));
              if ($nguia > 0) {
                $p2 = $rowligado['rc_tipoenvio'];
                $artnameligado = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
                $var = busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'COUNT(*)');
                if ($var > 0) {
                  $artnameligado .= " " . busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'av_nmb');
                }
                $artnameg = $artnameligado . " " . $rowligado['rc_numero'] . " DEL " . $nmba;
                $producto = '<td class="tamanio-letra">&nbsp;&nbsp;&nbsp;&nbsp; ** ' . $artnameg . ' ' . ($i + 1) . ' </td>';
                $peso = busca($rowligado['rc_articulo'], 'articulos', 'a_id', 'a_peso');
                $tablaAsignados .= '<tr class="tamanio-letra" id="tr' . $p1 . '">' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $chec) . '</tr>';
                $numarticulos++;
              }
            }
          }
        }


      }
    }
  }

  if ($numarticulos == 0) {
    $tablaAsignados = '<tr style="background: #ededed" class="tamanio-letra sin-hover2"><td class="tamanio-letra" colspan="6"><center>No hay elementos para mostrar</center></td></tr>';
  }
  $response['tablaAsignados'] = $tablaAsignados;
}

//----------------------------- FIN DE LA TABLA DE LOS ASIGNADOS PREVIAMENTE -------------------------


//----------------------------- INICIO DE LA TABLA DE LOS NO ASIGNADOS AÚN -------------------------
if (isset($_POST['noasignados'])) {

  $k = 0;
  $cob = busca(busca($direnvio, 'crm_direcciones', 'cd_id', 'cd_cp'), 'paqueterias_cobertura', 'pc_paqueteria = "1" AND pc_cp', 'pc_tipo');
  if ($cob == "0") {
    $selo = "checked";
    $selesp = "";
  } else {
    $selo = "";
    $selesp = "checked";
  }
  $sql = 'SELECT * FROM remisionesd INNER JOIN articulos ON rd_articulo = a_id WHERE rd_remision = "' . $idremision . '"';
  $result = setq($sql);

  $query0 = 'SELECT DISTINCT rc_remisiond AS rc_remisiond FROM remisionesc 
            WHERE rc_remision = "' . $idremision . '" 
            ORDER BY rc_remisiond ASC;';
  $result0 = setq($query0);
  $k = 0;
  $numarticulos = 0;
  $resp = 0;
  $tablaNoAsignados .= '<input type="hidden" value="' . $idcotiza . '" name="idcotiza">';
  $tablaNoAsignados .= '<input type="hidden" value="' . $idremision . '" name="idremision">';
  while ($row0 = $result0->fetch_array()) {
    unset($tablaNoAsignadosM);
    //Buscamos el aticulo dentro los detalles de la cotización para determinar posteriormente su tipo (Combo o no)
    $sqlcmb = 'SELECT a_id, a_nmb, a_tipoprod, rd_cantidad, rd_modelo, rd_id, rd_articulo
               FROM remisionesd INNER JOIN articulos ON a_id = rd_articulo
               WHERE rd_id = "' . $row0['rc_remisiond'] . '"';
                  
    $resultcmb = setq($sqlcmb);
    //echo $sqlcmb.'<br>';
    list($aid, $anmb, $tipoprod, $cantidad, $modelo, $cdmid, $cdmarticulo) = $resultcmb->fetch_array();

    /* echo $sqlcmb;
    echo '<br><br>'; */
    if ($tipoprod == "M") {
      for ($k = 1; $k <= intval($cantidad); $k++) {
        $queryd = 'SELECT * FROM remisionesc WHERE rc_tipoenvio IN ("O","D")
                  AND rc_remision = "' . $idremision . '" AND rc_remisiond = "' . $row0['rc_remisiond'] . '"
                  AND rc_combo = "' . $k . '" AND rc_guia IS NULL ORDER BY rc_paqueteria, rc_id';
        /* if($_SESSION['uid'] == "ADMIN") setq($queryd, true); */
        $resultddet = setq($queryd);

        $tablaNATitulo = '<tr class="tamanio-letra sin-hover2" style="background: #FFEE85 !important;"><td class="tamanio-letra sin-hover" colspan="6">&nbsp;&nbsp;PAQUETE - ' . $anmb . ' (' . $k . ') </td></tr>';
        $numarticulosM = $numarticulos;
        $res = 0;
        while ($rowcb = $resultddet->fetch_array()) {
          $artname = busca($rowcb['rc_articulo'], "articulos", "a_id", "a_nmb");
          $var = busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['rc_modelo'] . '" AND av_articulo', 'COUNT(*)');
          if ($var > 0) {
            $artname .= " " . busca($rowcb['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowcb['rc_modelo'] . '" AND av_articulo', 'av_nmb');
          }

          $artname .= " " . $rowcb['rc_numero'];
          $p1 = $rowcb['rc_id'];
          $tieneguia = $rowcb['rc_guia'];
          $sucursal = $rowcb['rc_sucursal'];
          if (empty($tieneguia) || $tieneguia == 0) {
            $p2 = $rowcb['rc_tipoenvio'];
            $status = $rowcb['rc_estatus'];
            $esligado = $rowcb['rc_ligado'];
            if (!empty($esligado)) {
              $cadena = "**";
              $articulopadre = busca($rowcb['rc_ligado'], "remisionesc", "rc_id", "rc_articulo");
              $modelopadre = busca($rowcb['rc_ligado'], "remisionesc", "rc_id", "rc_modelo");
              $numeropadre = busca($rowcb['rc_ligado'], "remisionesc", "rc_id", "rc_numero");
              $artnameligadodos = busca($articulopadre, "articulos", "a_id", "a_nmb");
              $var = intval(busca($articulopadre, 'articulos_variantes', 'av_modelo = "' . $modelopadre . '" AND av_articulo', 'COUNT(*)'));
              if ($var > 0) {
                $artnameligadodos .= " " . busca($articulopadre, 'articulos_variantes', 'av_modelo = "' . $modelopadre . '" AND av_articulo', 'av_nmb');
              }
              $artnameg = $artname . " DEL " . $artnameligadodos . " " . $numeropadre;
            } else {
              $cadena = "*";
              $artnameg = $artname;
            }
            $producto = '<td class="tamanio-letra">&nbsp;&nbsp;&nbsp;&nbsp; ' . $cadena . ' ' . $artnameg . ' </td>';
            $peso = busca($rowcb['rc_articulo'], 'articulos', 'a_id', 'a_peso');
            $bandera = true;
            if (isset($_POST['paqueteria'])) {
              $mismapaqueteria = busca($_POST['paqueteria'], 'remisionesc', 'rc_id = "' . $p1 . '" AND rc_paqueteria', 'COUNT(*)');
              if ($mismapaqueteria > 0) {
                $bandera = true;
              } else {
                $bandera = false;
              }
            }
            if ($bandera == true) {
              $tablaNoAsignadosM .= '<tr class="tamanio-letra" id="tr' . $p1 . '"' . $back . '>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $chec) . '</tr>';
              $numarticulos++;
            }
          }
        }
        if ($numarticulos != $numarticulosM) {
          $tablaNoAsignadosM .= '<tr class="tamanio-letra sin-hover2" style="background: #FFEE85" ><td class="tamanio-letra sin-hover" colspan="6"></td></tr>';
          $tablaNATitulo .= $tablaNoAsignadosM;
          $tablaNoAsignados .= $tablaNATitulo;
        }
      }
    } else {
      //echo 'no es combo'."<br>";
      //echo 'cantidad: '.$cantidad."<br>";
      for ($i = 0; $i < intval($cantidad); $i++) {
        $nmba = $anmb;
        /* $p12 = busca2($cdmarticulo, "remisionesc", "rc_remision = '" . $idremision . "' AND rc_remisiond = '" . $cdmid . "' AND rc_modelo = '".$modelo."' AND rc_numero = '" . ($i + 1) . "' AND rc_articulo", "rc_id"); */
        $p1 = busca($cdmarticulo, "remisionesc", "rc_remision = '" . $idremision . "' AND rc_remisiond = '" . $cdmid . "' AND rc_modelo = '".$modelo."' AND rc_numero = '" . ($i + 1) . "' AND rc_articulo", "rc_id");
        /* echo 'p12: '.$p12;
        echo '<br>'; */

        $estatusc = busca($p1, 'remisionesc', 'rc_id', 'rc_estatus');
        //echo busca2($p1, 'remisionesc', 'rc_id', 'rc_estatus');
        if ($estatusc == "A") {
          //echo 'esattusA'."<br>";
          $var = intval(busca($cdmarticulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'COUNT(*)'));
          if ($var > 0) {
            $nmba .= " " . busca($cdmarticulo, 'articulos_variantes', 'av_modelo = "' . $modelo . '" AND av_articulo', 'av_nmb');
          }
          $tipoenvio = busca($p1, 'remisionesc', 'rc_id', 'rc_tipoenvio');
          if (!($tipoenvio == "C" || $tipoenvio == "P")) {
            //echo 'tipo de envio D o O'."<br>";
            $tieneguia = busca($p1, "remisionesc", "rc_id", "rc_guia");
            $p2 = busca($cdmarticulo, "remisionesc", "rc_remision = '" . $idremision . "' AND rc_remisiond = '" . $cdmid . "' AND rc_modelo = '".$modelo."' AND rc_numero = '" . ($i + 1) . "' AND rc_articulo", "rc_tipoenvio");
            $status = busca($p1, 'remisionesc', 'rc_id', 'rc_estatus');
            $producto = '<td class="tamanio-letra">' . $nmba . ' ' . ($i + 1) . '</td>';
            $peso = busca($cdmarticulo, 'articulos', 'a_id', 'a_peso');
            $sucursal = busca($p1, "remisionesc", "rc_id", "rc_sucursal");
            if (empty($tieneguia)) {
              //echo 'no tiene guia'."<br>";
              $bandera = true;
              if (isset($_POST['paqueteria'])) {
                $mismapaqueteria = busca($_POST['paqueteria'], 'remisionesc', 'rc_id = "' . $p1 . '" AND rc_paqueteria', 'COUNT(*)');
                if ($mismapaqueteria > 0) {
                  $bandera = true;
                  //echo 'bandera true'."<br>";
                } else {
                  $bandera = false;
                  //echo 'bandera false '."<br>";
                }
              }
              if ($bandera == true) {
                $tablaNoAsignados .= '<tr class="tamanio-letra" id="tr' . $p1 . '"' . $back . '>' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $chec) . '</tr>';
                $numarticulos++;
                $k++;
              }

            }
          }
        }

          $sqligados = 'SELECT rc_id, rc_tipoenvio, rc_articulo, rc_modelo, rc_numero FROM remisionesc WHERE rc_ligado = "' . $p1 . '"';
          $resultligado = setq($sqligados);
          while ($rowligado = $resultligado->fetch_array()) {
            $p1 = $rowligado['rc_id'];
            $estatusc = busca($p1, 'remisionesc', 'rc_id', 'rc_estatus');
            if ($estatusc == "A") {
            $tipoenvio = busca($p1, 'remisionesc', 'rc_id', 'rc_tipoenvio');
            if (!($tipoenvio == "C" || $tipoenvio == "P")) {
              $tieneguia = busca($p1, "remisionesc", "rc_id", "rc_guia");
              $sucursal = busca($p1, "remisionesc", "rc_id", "rc_sucursal");
              if (empty($tieneguia)) {
                $p2 = $rowligado['rc_tipoenvio'];
                $artnameligado = busca($rowligado['rc_articulo'], "articulos", "a_id", "a_nmb");
                $var = busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'COUNT(*)');
                if ($var > 0) {
                  $artnameligado .= " " . busca($rowligado['rc_articulo'], 'articulos_variantes', 'av_modelo = "' . $rowligado['rc_modelo'] . '" AND av_articulo', 'av_nmb');
                }
                $artnameg = $artnameligado . " " . $rowligado['rc_numero'] . " DEL " . $nmba;
                $producto = '<td class="tamanio-letra">&nbsp;&nbsp;&nbsp;&nbsp; ** ' . $artnameg . ' ' . ($i + 1) . ' </td>';
                $peso = busca($rowligado['rc_articulo'], 'articulos', 'a_id', 'a_peso');

                $bandera = true;
                if (isset($_POST['paqueteria'])) {
                  $mismapaqueteria = busca($_POST['paqueteria'], 'remisionesc', 'rc_id = "' . $p1 . '" AND rc_paqueteria', 'COUNT(*)');
                  if ($mismapaqueteria > 0) {
                    $bandera = true;
                  } else {
                    $bandera = false;
                  }
                }
                if ($bandera == true) {
                  $tablaNoAsignados .= '<tr class="tamanio-letra" id="tr' . $p1 . '">' . imprimircheckboxes($p1, $p2, $back, $idremision, $producto, $peso, $chec) . '</tr>';
                  $numarticulos++;
                }

              }
            }
            }
          }
        /* } */
      }
    }
  }
  if ($numarticulos == 0) {
    $tablaNoAsignados = '<tr style="background: #ededed" class="tamanio-letra sin-hover2"><td class="tamanio-letra" colspan="6"><center>No hay elementos para mostrar</center></td></tr>';
  }
  $response['tablaNoAsignados'] = $tablaNoAsignados;
}
//----------------------------- FIN DE LA TABLA DE LOS NO ASIGNADOS AUN -------------------------

echo json_encode($response);
?>