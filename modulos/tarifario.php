<?php
ini_set('display_errors', 1);

/* ===========================
   CONTROLADOR
   =========================== */
class tarifario
{
  var $model;
  var $view;

  function __construct()
  {
    $this->model = new modeltarifario(isset($obj));
  }

  function index()
  {
    $this->view = new viewtarifario($this->model);
    $this->view->browse();
  }

  function show()
  {
    $this->view = new viewtarifario($this->model);
    $this->view->show();
  }

  function insert()
  {
    $this->model->setdata(
      '', 
      $_POST['precio'],
      $_POST['tipo_combustible'],
      $_POST['unidad'], 
      $_POST['km'], 
      $_POST['rendimiento'], 
      $_POST['capacidad_tanque'], 
      $_POST['casetas'], 
      $_POST['var_desgaste'], 
      $_SESSION['uid'], 
      date('Y-m-d H:i:s'), 
      'A'
    );
    $this->model->insert();
    redirect('?modulo=tarifario&accion=index');
  }

  function update()
  {
    $id = intval($_POST['id']);
    $this->model->setdata(
      $id, 
      $_POST['precio'],
      $_POST['tipo_combustible'],
      $_POST['unidad'], 
      $_POST['km'], 
      $_POST['rendimiento'], 
      $_POST['capacidad_tanque'], 
      $_POST['casetas'], 
      $_POST['var_desgaste'], 
      $_SESSION['uid'], 
      date('Y-m-d H:i:s'), 
      'A'
    );
    $this->model->update($id);
    redirect('?modulo=tarifario&accion=index');
  }

  function delete()
  {
    $id = intval($_GET['id']);
    $this->model->softdelete($id);
    redirect('?modulo=tarifario&accion=index');
  }

  // Actualizar precio global del combustible
  function setprecio()
  {
    $precio = floatval($_POST['precio']);
    $this->model->setActiveFuelPrice($precio, $_SESSION['uid']);
    redirect('?modulo=tarifario&accion=index');
  }
}


/* ===========================
   MODELO
   =========================== */
class modeltarifario
{
  // Entradas para INSERT/UPDATE
  function setdata($id, $precio, $tipocombustible, $unidad, $km, $rend, $cap, $casetas, $var_desgaste, $ugenera, $falta, $estatus)
  {
    $this->tc_id                 = (int)$id;
    $this->tc_preciocombustible  = (float)$precio;
    $this->tc_tipocombustible    = trim($tipocombustible);   // <— CONSISTENTE
    $this->tc_unidad             = trim($unidad);
    // Si decides fijar KM=1, déjalo así; si no, usa floatval($km)
    $this->tc_km                 = 1.0;
    $this->tc_rendimiento        = (float)$rend;
    $this->tc_capacidad_tanque   = (float)$cap;
    $this->tc_casetas            = (float)$casetas;
    $this->tc_var_desgaste       = (float)$var_desgaste;
    $this->tc_estatus            = $estatus;
    $this->tc_ugenera            = $ugenera;
    $this->tc_falta              = $falta;
  } // <— ESTE } FALTABA

  function insert()
  {
    $sql = 'INSERT INTO tarifario_costos SET
      tc_preciocombustible = "'.$this->tc_preciocombustible.'",
      tc_tipocombustible   = "'.addslashes($this->tc_tipocombustible).'",
      tc_unidad            = "'.addslashes($this->tc_unidad).'",
      tc_km                = "'.$this->tc_km.'",
      tc_rendimiento       = "'.$this->tc_rendimiento.'",
      tc_capacidad_tanque  = "'.$this->tc_capacidad_tanque.'",
      tc_casetas           = "'.$this->tc_casetas.'",
      tc_var_desgaste      = "'.$this->tc_var_desgaste.'",
      tc_estatus           = "'.$this->tc_estatus.'",
      tc_ugenera           = "'.addslashes($this->tc_ugenera).'",
      tc_falta             = "'.$this->tc_falta.'"';
    setq($sql);
  }

  function update($id)
  {
    $id = (int)$id;
    $sql = 'UPDATE tarifario_costos SET
      tc_preciocombustible = "'.$this->tc_preciocombustible.'",
      tc_tipocombustible   = "'.addslashes($this->tc_tipocombustible).'",
      tc_unidad            = "'.addslashes($this->tc_unidad).'",
      tc_km                = "'.$this->tc_km.'",
      tc_rendimiento       = "'.$this->tc_rendimiento.'",
      tc_capacidad_tanque  = "'.$this->tc_capacidad_tanque.'",
      tc_casetas           = "'.$this->tc_casetas.'",
      tc_var_desgaste      = "'.$this->tc_var_desgaste.'",
      tc_umod              = "'.addslashes($_SESSION['uid']).'",
      tc_fmod              = "'.date('Y-m-d H:i:s').'"
      WHERE tc_id = "'.$id.'"';
    setq($sql);
  }

  function softdelete($id)
  {
    $id = (int)$id;
    $sql = 'UPDATE tarifario_costos
            SET tc_estatus="B", tc_umod="'.addslashes($_SESSION['uid']).'", tc_fmod="'.date('Y-m-d H:i:s').'"
            WHERE tc_id="'.$id.'"';
    setq($sql);
  }

  function getActiveFuelPrice()
  {
    $sql = 'SELECT cc_precio FROM config_combustible WHERE cc_activo="S"
            ORDER BY cc_vigente_desde DESC, cc_id DESC LIMIT 1';
    $r = setq($sql);
    if ($r && $row = $r->fetch_array()) return (float)$row[0];
    return 0.0;
  }

  function setActiveFuelPrice($precio, $usuario)
  {
    setq('UPDATE config_combustible SET cc_activo="N" WHERE cc_activo="S"');
    $sql = 'INSERT INTO config_combustible SET
      cc_precio = "'.(float)$precio.'",
      cc_vigente_desde = "'.date('Y-m-d').'",
      cc_activo = "S",
      cc_ugenera = "'.addslashes($usuario).'",
      cc_falta = "'.date('Y-m-d H:i:s').'"';
    setq($sql);
  }

  function getAllRows()
  {
    $sql = 'SELECT * FROM v_tarifario_costos WHERE tc_estatus="A" ORDER BY tc_id DESC';
    return setq($sql);
  }

  function getOne($id)
  {
    $id = (int)$id;
    $sql = 'SELECT * FROM tarifario_costos WHERE tc_id = "'.$id.'"';
    $r = setq($sql);
    return $r ? $r->fetch_assoc() : null;
  }
}



/* ===========================
   VISTA
   =========================== */
class viewtarifario
{
  var $model;
  function __construct($model)
  {
    $this->model = $model;
    ?>
    <script>
      function checkguardar(btnId="sendform"){
        var b = document.getElementById(btnId);
        if(b){ b.innerHTML = "Guardando..."; b.disabled = true; }
        return true;
      }
    </script>
    <?php
  }

  function browse()
{
  $precio = $this->model->getActiveFuelPrice();

  // Toolbar
  $nuevo = '<a href="?modulo=tarifario&accion=show"><button type="button" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nuevo</button></a>';
  $accionPrecio = '
    <form class="form-inline" method="post" action="?modulo=tarifario&accion=setprecio" onsubmit="return checkguardar(\'btnPrecio\')">
      <div class="input-group input-group-sm">
        <span class="input-group-text">$ Combustible</span>
        <input type="number" step="0.0001" min="0" class="form-control" name="precio" value="'.number_format($precio,4,'.','').'" required>
        <button id="btnPrecio" class="btn btn-sm btn-secondary" type="submit"><i class="fas fa-gas-pump"></i> Actualizar</button>
      </div>
    </form>';
  toolbar('tarifario', '', $accionPrecio, $nuevo, '');

  // Tabla (DataTable AJAX) —> sustituye tu tabla anterior por esto
// Tabla (DataTable AJAX)
echo '<div class="card mt-2"><div class="card-body">';
echo '<div class="table-responsive">';
echo '<table id="tarifarioDT" class="table table-hover table-striped" style="width:100%">';
echo '<thead class="bg-primary text-white">
        <tr>
          <th>NOMBRE DE LA UNIDAD</th>
          <th>REND. (km/l)</th>
          <th>CAP. TANQUES (l)</th>
          <th>VAR. DESGASTE</th>
          <th>TIPO COMBUSTIBLE</th>
          <th>COSTO COMBUSTIBLE</th>
          <th></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>';
echo '</div></div></div>';
?>
<script>
(function(){
  const fmtNum4 = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
  const fmtNum6 = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 6, maximumFractionDigits: 6 });
  const fmtCur  = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 2 });

  $('#tarifarioDT').DataTable({
    processing: true,
    serverSide: true,
    ordering: true,
    searching: true,
    paging: true,
    pageLength: 50,
    ajax: {
      url: 'query/datatabletarifario.php',
      type: 'POST',
      dataSrc: 'data'
    },
    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
    columns: [
      { data: 'nombre' },                                               // NOMBRE
      { data: 'rendimiento',      render: d => fmtNum4.format(d) },     // RENDIMIENTO
      { data: 'capacidad_tanque', render: d => fmtNum4.format(d) },     // CAP. TANQUES
      { data: 'var_desgaste',     render: d => fmtNum6.format(d) },     // VARIABLE DE DESGASTE
      { data: 'tipo_combustible' },                                     // TIPO COMBUSTIBLE
      { data: 'costo_combustible',render: d => fmtCur.format(d) },      // COSTO COMBUSTIBLE
      { data: 'acciones',         orderable: false, searchable: false } // Acciones
    ],
    order: [[0, 'asc']], // Orden por NOMBRE
    responsive: true
  });
})();
</script>
<?php

}

  function show()
  {
    //$precio = $this->model->getActiveFuelPrice();
    $precio = busca($_GET['id'], 'tarifario_costos', 'tc_id', 'tc_preciocombustible');
    $isEdit = isset($_GET['id']) && intval($_GET['id']) > 0;

    $unidad  = busca($_GET['id'], 'tarifario_costos', 'tc_id', 'tc_unidad');
    $km      = busca($_GET['id'], 'tarifario_costos', 'tc_id', 'tc_km');
    $rend    = busca($_GET['id'], 'tarifario_costos', 'tc_id', 'tc_rendimiento');
    $cap     = busca($_GET['id'], 'tarifario_costos', 'tc_id', 'tc_capacidad_tanque');
    $casetas = busca($_GET['id'], 'tarifario_costos', 'tc_id', 'tc_casetas');
    $varDes  = busca($_GET['id'], 'tarifario_costos', 'tc_id', 'tc_var_desgaste');

    $action = $isEdit ? '?modulo=tarifario&accion=update' : '?modulo=tarifario&accion=insert';

    toolbar('tarifario', '<a href="?modulo=tarifario&accion=index"><button class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i> Atrás</button></a>', '', '', '');

    echo '<div class="card mt-2"><div class="card-body">';
    echo '<form method="post" action="'.$action.'" onsubmit="return checkguardar()">';
    if($isEdit) echo '<input type="hidden" name="id" value="'.intval($_GET['id']).'">';

    echo '
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">NOMBRE DE LA UNIDAD</label>
        <input type="text" name="unidad" class="form-control" value="'.htmlspecialchars($unidad).'" required>
      </div>

      <div class="col-md-3" hidden>
        <label class="form-label">KM</label>
        <input type="number" step="0.01" min="1" name="km" id="km" class="form-control" disabled value="1" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">RENDIMIENTO</label>
        <input type="number" step="0.0001" min="0" name="rendimiento" id="rend" class="form-control" value="'.htmlspecialchars($rend).'" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">CAPACIDAD TANQUE</label>
        <input type="number" step="0.001" min="0" name="capacidad_tanque" id="cap" class="form-control" value="'.htmlspecialchars($cap).'" required>
      </div>

      <div class="col-md-3" hidden>
        <label class="form-label">CASETAS ($)</label>
        <input type="number" step="0.01" min="0" name="casetas" id="casetas" class="form-control" value="'.htmlspecialchars($casetas).'" required>
      </div>

      <div class="col-md-3">
      <label class="form-label">TIPO DE COMBUSTIBLE</label>
      <select id="tipo_combustible" name="tipo_combustible" class="form-control">';
          $tipoActual = busca($_GET['id'], 'tarifario_costos', 'tc_id', 'tc_tipocombustible');
          $opciones = ['DIESEL', 'GASOLINA'];

          foreach ($opciones as $opcion) {
            $selected = ($tipoActual == $opcion) ? 'selected' : '';
            echo '<option value="'.$opcion.'" '.$selected.'>'.$opcion.'</option>';
          }
echo '
    </select>
    </div>
      <div class="col-md-3">
        <label class="form-label">PRECIO COMBUSTIBLE</label>
        <input type="number" step="0.0001" min="0" id="precio" name="precio" class="form-control" value="'.number_format($precio,4,'.','').'">
      </div>

      <div class="col-md-3">
        <label class="form-label">VARIABLE DE DESGASTE</label>
        <input type="number" step="0.000001" min="0" name="var_desgaste" id="var" class="form-control" value="'.htmlspecialchars($varDes).'" required>
      </div>
    </div>

    <hr/>

    <div class="row g-3" hidden>
      <div class="col-md-2">
        <label class="form-label">TANQUES</label>
        <input type="text" id="tanques" class="form-control" readonly>
      </div>
      <div class="col-md-2">
        <label class="form-label">COMBUSTIBLE ($)</label>
        <input type="text" id="combustible" class="form-control" readonly>
      </div>
      <div class="col-md-2">
        <label class="form-label">DESGASTE ($)</label>
        <input type="text" id="desgaste" class="form-control" readonly>
      </div>
      <div class="col-md-2">
        <label class="form-label">OPERADOR ($)</label>
        <input type="text" id="operador" class="form-control" readonly>
      </div>
      <div class="col-md-2">
        <label class="form-label">TOTAL COSTO DVL ($)</label>
        <input type="text" id="total" class="form-control" readonly>
      </div>
      <div class="col-md-2">
        <label class="form-label">VENTA DVL ($)</label>
        <input type="text" id="venta" class="form-control" readonly>
      </div>
    </div>

    <div class="mt-4 text-center">
      <button type="submit" id="sendform" class="btn btn-primary"><i class="fas fa-save"></i> '.($isEdit?'Actualizar':'Guardar').'</button>
    </div>

    </form></div></div>';

    // JS: cálculo en vivo (coincide con la vista SQL)
    ?>
    <script>
      function toNum(v){ var n = parseFloat(v); return isNaN(n)?0:n; }

      function recalc(){
        var km    = toNum(document.getElementById('km').value);
        var rend  = toNum(document.getElementById('rend').value);
        var cap   = toNum(document.getElementById('cap').value);
        var cas   = toNum(document.getElementById('casetas').value);
        var vdes  = toNum(document.getElementById('var').value);
        var prec  = toNum(document.getElementById('precio').value);

        var tanq  = (rend > 0 ? km / rend : 0);
        var comb  = prec * cap * tanq;
        var desg  = km * (vdes*10);     // según tu definición
        var oper  = km * (vdes*10);     // mismo cálculo que indicaste
        var total = comb + cas + desg + oper;
        var venta = total * 1.5;

        document.getElementById('tanques').value     = tanq.toFixed(4);
        document.getElementById('combustible').value = comb.toFixed(2);
        document.getElementById('desgaste').value    = desg.toFixed(2);
        document.getElementById('operador').value    = oper.toFixed(2);
        document.getElementById('total').value       = total.toFixed(2);
        document.getElementById('venta').value       = venta.toFixed(2);
      }

      ['km','rend','cap','casetas','var'].forEach(id=>{
        var el = document.getElementById(id);
        if(el){ el.addEventListener('input', recalc); }
      });
      recalc();
    </script>
    <?php
  }
}
?>
