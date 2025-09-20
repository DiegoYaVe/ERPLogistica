<?php
ini_set('display_errors', 0);
session_start();
include_once('../funciones.php');
include_once('../modulos/prospectos.php');

$response = array();
$respuesta = 0;          // <<< evita undefined
$respuesta2 = null;
$respuesta3 = null;
$registros = 0;          // <<< evita undefined
$codes = "";             // <<< evita undefined
$nvendedore2 = 0;        // <<< tu variable al final estaba mal escrita, la mantengo por compatibilidad
$siguiente = 1;          // <<< valor por defecto

if (isset($_SESSION['uid'])) {

  $prospectos = new modelprospectos();

  $html = "";
  $telefonos = "";
  $usuariosventas = array();
  $ocupados = array();
  $ocupados2 = array();

  // --- INPUTS ---
  $id        = isset($_POST['id']) ? $_POST['id'] : '';
  $nmb       = isset($_POST['nmb']) ? $_POST['nmb'] : '';
  $cp        = isset($_POST['cp']) ? $_POST['cp'] : '';
  $correo    = isset($_POST['correo']) ? $_POST['correo'] : '';
  $phone     = isset($_POST['telefono']) ? $_POST['telefono'] : '';
  $code      = isset($_POST['code']) ? $_POST['code'] : '';
  $pais      = isset($_POST['pais']) ? $_POST['pais'] : '';
  $observacion = isset($_POST['observacion']) ? $_POST['observacion'] : '';
  $lead      = isset($_POST['lead']) ? $_POST['lead'] : '';

  $status    = '';
  $telefono  = preg_replace("/[^0-9]/", "", $phone); // Limpiamos el número de teléfono
  $enviar    = 0;
  $read      = "readonly";

  // consecutivo diario
  $ncaptura = getmax("cl_ncaptura", "crm_leads WHERE cl_fasigna = '" . date("Y-m-d") . "'", false, true);

  if (!empty($id)) {
    // --- EDICIÓN / REINSERCIÓN ---
    $sqldat = 'SELECT cl_ncaptura, cl_estatus, cl_vendedor FROM crm_leads WHERE cl_id = "' . $id . '"';
    $resultdat = setq($sqldat);
    list($ncaptura, $status, $vendedori) = $resultdat->fetch_array();

    // se elimina el registro anterior
    $sqldel = "DELETE FROM crm_leads WHERE cl_id = '" . $id . "'";
    setq($sqldel);
    $respuesta3 = 3; // bandera
  } else {
    // --- NUEVO LEAD ---

    // Teléfono ya existe en leads de la página
    $existe = busca($telefono, 'crm_leads', ' cl_lead = "' . $lead . '" AND cl_id != "' . $id . '" AND cl_code = "' . $code . '" AND cl_telefono', 'cl_vendedor');
    if (!empty($existe)) {
      $respuesta  = 41; // Tel ya existe
      $agente = busca($existe, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
      $response['agente'] = $existe . " " . $agente;
    }
    $status = 'P';

    // Teléfono ya existe en clientes
    $existe = busca($telefono, 'crm_clientes', ' c_telefono1 = "' . $telefono . '" OR c_telefono2', 'c_uregistro');
    if (!empty($existe)) {
      $respuesta  = 42; // Tel ya existe en clientes
      $agente = busca($existe, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
      $response['agente'] = $existe . " " . $agente;
    }
    $status = 'P';
  }

  // ----------------------------------------------------------------
  // SI EL TELÉFONO NO ESTÁ REPETIDO EN LEADS: CONTINÚA EL FLUJO
  // ----------------------------------------------------------------
  if ($respuesta != 41) {

    // --- (Dejo tu lógica de bloque/consecutivo por si la usas para otras cosas) ---
    $pvendedor = busca($telefono, "crm_leads", "cl_telefono", "cl_vendedor");
    $nvendedores = intval(busca("A", "usuarios_orden", 'uo_estatus', 'COUNT(*)'));

    $clid0 = busca($lead, "crm_leads", "cl_lead", "MAX(cl_id) AS cl_id");
    $consecutivo = intval(busca($clid0, "crm_leads", "cl_id", "cl_consecutivo"));

    if ($consecutivo >= $nvendedores) {
      $siguiente = 1;
    } else {
      $siguiente = $consecutivo + 1;
    }

    // (calcula inicial del bloque)
    $sqlcon = "SELECT cl_id FROM crm_leads WHERE cl_consecutivo = 1 ORDER BY cl_id DESC, cl_consecutivo DESC LIMIT 1";
    $resultcon = setq($sqlcon);
    $inicial = 0;
    if ($resultcon && $resultcon->num_rows > 0) {
      list($inicial) = $resultcon->fetch_array();
    }

    // (ocupar/rotación – la dejo pero NO la usamos para definir $vendedor)
    if ($inicial > 0) {
      $sqlvend = "SELECT cl_vendedor FROM crm_leads WHERE cl_id >= '" . $inicial . "' AND cl_lead = '" . $lead . "' ORDER BY cl_id ASC, cl_consecutivo ASC LIMIT " . $consecutivo;
      $resultvend = setq($sqlvend);
      $k = 0;
      while ($resultvend && ($rowvend = $resultvend->fetch_array())) {
        $ocupados[$k] = $rowvend['cl_vendedor'];
        $k++;
      }
    }

    $sqlu = "SELECT * FROM usuarios_orden WHERE uo_estatus = 'A' ORDER BY uo_orden";
    $resultu = setq($sqlu);
    while ($resultu && ($rowu = $resultu->fetch_array())) {
      array_push($usuariosventas, $rowu['uo_uid']);
    }

    // ----------------------------------------------------------------
    // >>>>>>>>> FORZAR VENDEDOR = USUARIO EN SESIÓN <<<<<<<<<
    // ----------------------------------------------------------------
    $vendedor = $_SESSION['uid']; // <<< AQUÍ ESTÁ LA CLAVE

    // Si quieres respetar el vendedor original SOLO en re-inserción/edición, descomenta:
    // if (isset($vendedori) && !empty($vendedori)) { $vendedor = $vendedori; } // opcional

    // O si quieres respetar un vendedor previo por teléfono (histórico), descomenta:
    // if (!empty($pvendedor)) { $vendedor = $pvendedor; } // opcional

    // --- Inserta / guarda el lead con el VENDEDOR DE LA SESIÓN ---
    $prospectos->setdatalead(
      $id,
      $lead,
      $nmb,
      $cp,
      $correo,
      $telefono,
      $code,
      $pais,
      $status,
      $observacion,
      $vendedor,           // <<< vendedor forzado a sesión
      $ncaptura,
      $_SESSION['uid'],    // usuario que realiza la acción
      "",
      date("Y-m-d"),
      $siguiente
    );
    $respuesta = $prospectos->insertlead();

    // --- Reconsultar para armar tabla ---
    $sqlcl = 'SELECT * FROM crm_leads WHERE cl_lead = "' . $lead . '" AND cl_fasigna = "' . date("Y-m-d") . '" ORDER BY cl_estatus = "P" DESC, cl_id DESC;';
    $resultcl = setq($sqlcl);
    $read = "readonly";

    // actualizar consecutivo y reglas de lectura
    $clid1 = busca($lead, "crm_leads", "cl_lead", "MAX(cl_id) AS cl_id");
    $consecutivo = intval(busca($clid1, "crm_leads", "cl_id", "cl_consecutivo"));

    if ($consecutivo >= $nvendedores) {
      $respuesta2 = 2;
      $enviar = 1;
    } else {
      $nvendedores2 = intval(busca("A", "usuarios_orden", 'uo_estatus', 'COUNT(*)'));
      $registros = intval(busca($lead, 'crm_leads', 'cl_estatus = "P" AND cl_lead', 'COUNT(*)'));

      if ($registros < $nvendedores2) {
        $read = "";
      } else {
        $read = "readonly";
      }
    }

    if ($respuesta == 1) {
      $i = 1;
      while ($resultcl && ($row = $resultcl->fetch_array())) {
        if ($row['cl_estatus'] == "A" || $row['cl_estatus'] == "F") {
          $backg = 'background: #deffde;';
        } else if ($row['cl_estatus'] == "X") {
          $backg = 'background: #f5000026;';
        } else {
          $backg = '';
        }

        $html .= '
        <tr class="">
          <th style="height: 44.84px;' . $backg . '">+' . $row['cl_code'] . " " . $row['cl_telefono'] . '</span></th>
          <th style="height: 44.84px;' . $backg . '"><span>' . $row['cl_nmb'] . '</span></th>
          <th style="height: 44.84px;' . $backg . '">' . $row['cl_cp'] . '</span></th>
          <th style="height: 44.84px;' . $backg . '">' . $row['cl_correo'] . '</span></th>
          <th style="height: 44.84px;' . $backg . '">' . $row['cl_observacion'] . '</span></th>
          <th style="height: 44.84px;' . $backg . '">';

        if ($row['cl_estatus'] == "P") {
          $html .= '<button type="button" onClick="editarLead(' . $row['cl_id'] . ');" class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i></button>  
                    <button type="button" onClick="borrarLead(' . $row['cl_id'] . ');" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>';
        } else {
          // selector de vendedor (se muestra pero recuerda: al guardar, forzamos el de sesión)
          $vend = '';
          $html .= '
            <div class="row">
              <div class="col-md-6">
                <select class="form-control" id="idvendedor' . $row['cl_id'] . '" name="idvendedor' . $row['cl_id'] . '" onchange="cambiarvendedor(' . $row['cl_id'] . ');">';

          $sqlv = 'SELECT * FROM usuarios_orden WHERE uo_estatus = "A"';
          $resultv = setq($sqlv);
          while ($resultv && ($rowv = $resultv->fetch_array())) {
            $nmbv = busca($rowv['uo_uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
            if ($row['cl_vendedor'] == $rowv['uo_uid']) {
              $sel = 'selected';
              $vend = $row['cl_vendedor'];
            } else {
              $sel = '';
            }
            $html .= '<option value="' . $rowv['uo_uid'] . '" ' . $sel . '>' . $nmbv . '</option>';
          }

          $html .= '</select>
              </div>
              <div class="col-md-2">
                <input type="hidden" value="' . $vend . '" id="venoriginal' . $row['cl_id'] . '">
                <button type="button" onClick="editarLead(' . $row['cl_id'] . ');" class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i></button>
              </div>
            </div>';
        }

        $html .= '</th></tr>';

        if ($i == 1) {
          $telefonos .= $row['cl_telefono'];
          $codes .= $row['cl_code'];
        } else {
          $telefonos .= "," . $row['cl_telefono'];
          $codes .= "," . $row['cl_code'];
        }
        $i++;
      }
    }

    if (isset($respuesta2)) {
      $respuesta = $respuesta2;
    }
    if (isset($respuesta3)) {
      $respuesta = $respuesta3;
    }

    $response['html'] = $html;
    $response['registros'] = $registros;
    $response['vendedores'] = $nvendedore2; // (ojo: tu variable original parece typo)
    $response['enviar'] = $enviar;
  } // fin if($respuesta != 41)

  $response['respuesta'] = $respuesta;

} else {
  $response['respuesta'] = 247; // La sesión caducó
}

echo json_encode($response);
?>
