<?php
ini_set('display_errors', 0);

/* ===========================
   CONTROLADOR
   =========================== */
class odkm
{
  var $model;
  var $view;

  function __construct()
  {
    $this->model = new modelodkm(isset($obj));
  }

  function index()
  {
    $this->view = new viewodkm($this->model);
    $this->view->browse();
  }

  function show()
  {
    $this->view = new viewodkm($this->model);
    $this->view->show();
  }

  function insert()
  {
    // Sanitizado/validación mínima
    $origen  = isset($_POST['origen'])  ? trim($_POST['origen'])  : '';
    $destino = isset($_POST['destino']) ? trim($_POST['destino']) : '';
    $km      = isset($_POST['km'])      ? floatval($_POST['km'])  : 0;

    if($origen === '' || $destino === '' || $km <= 0){
      $_SESSION['msg_error'] = 'Verifica los datos: Origen/Destino requeridos y KM > 0.';
      redirect('?modulo=odkm&accion=show');
      return;
    }

    $this->model->setdata(
      '', $origen, $destino, $km,
      $_SESSION['uid'], date('Y-m-d H:i:s'), 'A'
    );
    $ok = $this->model->insert();
    if(!$ok){
      $_SESSION['msg_error'] = 'No se pudo guardar. Quizá ya existe la combinación Origen/Destino.';
    }
    redirect('?modulo=odkm&accion=index');
  }

  function update()
  {
    $id      = intval($_POST['id']);
    $origen  = isset($_POST['origen'])  ? trim($_POST['origen'])  : '';
    $destino = isset($_POST['destino']) ? trim($_POST['destino']) : '';
    $km      = isset($_POST['km'])      ? floatval($_POST['km'])  : 0;

    if($id<=0 || $origen === '' || $destino === '' || $km <= 0){
      $_SESSION['msg_error'] = 'Verifica los datos: Origen/Destino requeridos y KM > 0.';
      redirect('?modulo=odkm&accion=show&id='.$id);
      return;
    }

    $this->model->setdata(
      $id, $origen, $destino, $km,
      $_SESSION['uid'], date('Y-m-d H:i:s'), 'A'
    );
    $ok = $this->model->update($id);
    if(!$ok){
      $_SESSION['msg_error'] = 'No se pudo actualizar. Revisa duplicados Origen/Destino.';
    }
    redirect('?modulo=odkm&accion=index');
  }

  function delete()
  {
    $id = intval($_GET['id']);
    $this->model->softdelete($id);
    redirect('?modulo=odkm&accion=index');
  }
}


/* ===========================
   MODELO
   =========================== */
class modelodkm
{
  // Set de datos para INSERT/UPDATE
  function setdata($id, $origen, $destino, $km, $ugenera, $falta, $estatus)
  {
    // Normaliza (mayúsculas, espacios)
    $norm = function($s){
      $s = trim(preg_replace('/\s+/', ' ', $s));
      return mb_strtoupper($s, 'UTF-8');
    };
    $this->od_id      = $id;
    $this->od_origen  = $norm($origen);
    $this->od_destino = $norm($destino);
    $this->od_km      = floatval($km);
    $this->od_estatus = $estatus;
    $this->od_ugenera = $ugenera;
    $this->od_falta   = $falta;
  }

  function insert()
  {
    // Previene duplicado por lógica además del UNIQUE
    $s = 'SELECT od_id FROM od_kilometros 
          WHERE od_estatus="A" 
            AND od_origen="'.addslashes($this->od_origen).'"
            AND od_destino="'.addslashes($this->od_destino).'"
          LIMIT 1';
    $r = setq($s);
    if($r && $r->num_rows>0) return false;

    $sql = 'INSERT INTO od_kilometros SET
      od_origen = "'.addslashes($this->od_origen).'",
      od_destino = "'.addslashes($this->od_destino).'",
      od_km = "'.$this->od_km.'",
      od_estatus = "'.$this->od_estatus.'",
      od_ugenera = "'.addslashes($this->od_ugenera).'",
      od_falta = "'.$this->od_falta.'"';
    return (bool)setq($sql);
  }

  function update($id)
  {
    // Chequeo de duplicado para otro ID
    $s = 'SELECT od_id FROM od_kilometros 
          WHERE od_estatus="A" 
            AND od_origen="'.addslashes($this->od_origen).'"
            AND od_destino="'.addslashes($this->od_destino).'"
            AND od_id <> "'.intval($id).'"
          LIMIT 1';
    $r = setq($s);
    if($r && $r->num_rows>0) return false;

    $sql = 'UPDATE od_kilometros SET
      od_origen = "'.addslashes($this->od_origen).'",
      od_destino = "'.addslashes($this->od_destino).'",
      od_km = "'.$this->od_km.'",
      od_umod = "'.addslashes($_SESSION['uid']).'",
      od_fmod = "'.date('Y-m-d H:i:s').'"
      WHERE od_id = "'.intval($id).'"';
    return (bool)setq($sql);
  }

  function softdelete($id)
  {
    $sql = 'UPDATE od_kilometros 
            SET od_estatus="B", od_umod="'.addslashes($_SESSION['uid']).'", od_fmod="'.date('Y-m-d H:i:s').'"
            WHERE od_id="'.intval($id).'"';
    setq($sql);
  }

  function getAll() {
    $sql = 'SELECT * FROM od_kilometros WHERE od_estatus="A" ORDER BY od_id DESC';
    return setq($sql);
  }

  function getOne($id) {
    $sql = 'SELECT * FROM od_kilometros WHERE od_id = "'.intval($id).'"';
    $r = setq($sql);
    return $r ? $r->fetch_assoc() : null;
  }
}


/* ===========================
   VISTA
   =========================== */
class viewodkm
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
    $nuevo = '<a href="?modulo=odkm&accion=show">
                <button type="button" class="btn btn-sm btn-primary">
                  <i class="fas fa-plus"></i> Nuevo
                </button>
              </a>';
    toolbar('Origen-Destino y KM', '', '', $nuevo, '');

    // Mensajes
    if(!empty($_SESSION['msg_error'])){
      echo '<div class="alert alert-danger mt-2">'.htmlspecialchars($_SESSION['msg_error']).'</div>';
      unset($_SESSION['msg_error']);
    }

    echo '<div class="card mt-2"><div class="card-body">';
    echo '<div class="table-responsive">';
    echo '<table id="odkmDT" class="table table-hover table-striped" style="width:100%">';
    echo '<thead class="bg-primary text-white">
            <tr>
              <th>ORIGEN</th>
              <th>DESTINO</th>
              <th>KM</th>
              <th></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>';
    echo '</div></div></div>';
    ?>
    <script>
    (function(){
      const fmtNum2 = new Intl.NumberFormat('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2});
      $('#odkmDT').DataTable({
        processing: true,
        serverSide: true,
        ordering: true,
        searching: true,
        paging: true,
        pageLength: 50,
        ajax: {
          url: 'query/datatableodkm.php',
          type: 'POST',
          dataSrc: 'data'
        },
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        columns: [
          { data: 'origen'  },
          { data: 'destino' },
          { data: 'km', render: d => fmtNum2.format(d) },
          { data: 'acciones', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        responsive: true
      });
    })();
    </script>
    <?php
  }

  function show()
  {
    $isEdit = isset($_GET['id']) && intval($_GET['id']) > 0;
    $data   = $isEdit ? $this->model->getOne(intval($_GET['id'])) : null;

    $origen  = $data ? $data['od_origen']  : '';
    $destino = $data ? $data['od_destino'] : '';
    $km      = $data ? $data['od_km']      : '0';

    $action = $isEdit ? '?modulo=odkm&accion=update' : '?modulo=odkm&accion=insert';

    toolbar('Origen-Destino y KM',
      '<a href="?modulo=odkm&accion=index"><button class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i> Atrás</button></a>',
      '', '', ''
    );

    // Mensajes
    if(!empty($_SESSION['msg_error'])){
      echo '<div class="alert alert-danger mt-2">'.htmlspecialchars($_SESSION['msg_error']).'</div>';
      unset($_SESSION['msg_error']);
    }

    echo '<div class="card mt-2"><div class="card-body">';
    echo '<form method="post" action="'.$action.'" onsubmit="return checkguardar()">';
    if($isEdit) echo '<input type="hidden" name="id" value="'.intval($_GET['id']).'">';

    echo '
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">ORIGEN</label>
        <input type="text" name="origen" class="form-control" value="'.htmlspecialchars($origen).'" maxlength="150" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">DESTINO</label>
        <input type="text" name="destino" class="form-control" value="'.htmlspecialchars($destino).'" maxlength="150" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">KILÓMETROS</label>
        <input type="number" step="0.01" min="0" name="km" class="form-control" value="'.htmlspecialchars($km).'" required>
      </div>
    </div>

    <div class="mt-4 text-center">
      <button type="submit" id="sendform" class="btn btn-primary">
        <i class="fas fa-save"></i> '.($isEdit?'Actualizar':'Guardar').'
      </button>
    </div>';

    echo '</form></div></div>';
  }
}
?>
