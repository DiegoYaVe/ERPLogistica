<?php
  $GLOBALS['menu'] = "Productos";
class unidades {

  var $model;
  var $view;

  function __construct() {
      $this->model = new modelunidades(isset($obj));
  }

  function index() {
    
    if(!isset($_GET['id'])) $_GET['id'] = NULL;
    if(!isset($_POST['nmb'])) $_POST['nmb'] = NULL;
    if(!isset($_POST['estatus'])) $_POST['estatus'] = "A";
  
    $this->model->results(trim($_POST['nmb']),$_POST['estatus']);
    $this->model->select($_GET['id']);
    $this->view = new viewunidades($this->model);
    $this->view->show($_POST['nmb'],$_POST['estatus']);
  }

  function insert() {
      $this->model->setData(getmax('u_id','unidades'), $_POST['nmb'], $_POST['descripcion'], $_POST['clavesat'], "A");

      if(busca($this->model->nmb,'unidades','u_empresa = "'.$_SESSION['emp'].'" AND u_nmb','COUNT(*)') > 0)
        echo'<script type="text/javascript">alert("Error: El nombre de la unidad ya existe\nIntenta con otro diferente");history.go(-1);</script>';
      else{
        $this->model->insert();
        redirect('?modulo=unidades&accion=index');
      }
  }

  function disable() {
      $this->model->setData($_GET['id'], $_POST['nmb'],"","",$_POST['estatus']);
      $this->model->disable();
      redirect('?modulo=unidades&accion=index');
  }

  function enable() {
      $this->model->setData($_GET['id'], $_POST['nmb'],"","",$_POST['estatus']);
      $this->model->enable();
      redirect('?modulo=unidades&accion=index');
  }

  function update() {
      $this->model->setData($_POST['id'], $_POST['nmb'], $_POST['descripcion'], $_POST['clavesat'], "A");

      if(busca($this->model->nmb,'unidades','u_id != "'.$this->model->id.'" AND u_empresa = "'.$_SESSION['emp'].'" AND u_nmb','COUNT(*)') > 0)
        echo'<script type="text/javascript">alert("Error: El nombre de la unidad ya existe\nIntenta con otro diferente");history.go(-1);</script>';
      else{
        $this->model->update();
        redirect('?modulo=unidades&accion=index');
      }
  }

  function borrar(){
    $sql = 'DELETE FROM unidades
            WHERE u_id = "'.$_GET['id'].'"
            AND u_empresa = "'.$_SESSION['emp'].'"';
    setq($sql);

    redirect("?modulo=unidades&accion=index");
  }

}

/* -----------------------------------------------MODEL---------------------------------------------------------------------- */

class modelunidades {

  function setData($id, $nmb, $descripcion, $clavesat, $estatus) {
      $this->id = clearvmayus($id);
      $this->nmb = clearvmayus($nmb);
      $this->descripcion = clearvmayus($descripcion);
      $this->clavesat = clearvmayus($clavesat);
      $this->estatus = clearvmayus($estatus);
  }

  function select($id) {
      $sql = 'SELECT * FROM unidades WHERE u_id= "' . $_GET['id'] . '" AND u_empresa = "'.$_SESSION['emp'].'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['u_id'];
      $this->nmb = $row['u_nmb'];
      $this->descripcion = $row['u_descripcion'];
      $this->clavesat = $row['u_clavesat'];
      $this->estatus = $row['u_estatus'];
  }

  function insert() {
    $sql = 'INSERT INTO unidades SET
            u_id = "' . $this->id . '",
            u_nmb = "' . $this->nmb . '",
            u_descripcion = "' . $this->descripcion . '",
            u_clavesat = "' . $this->clavesat . '",
            u_empresa = "' . $_SESSION['emp'] . '",
            u_estatus = "' . $this->estatus.'"';    //   die($sql);
    setq($sql);
  }

    function result($nmb) {
        $sql = 'SELECT * FROM cfdi_unidades WHERE m_empresa = "'.$_SESSION['emp'].'" ';
        if($nmb) $sql.=' AND cu_nmb LIKE "%'.$nmb.'%" OR cu_clave LIKE "%'.$nmb.'%"';
        $this->resultalm = setq($sql);  
    }

    function results($nmb,$estatus){
      $sql = 'SELECT * FROM unidades INNER JOIN cfdi_unidades ON u_clavesat = cu_clave
              WHERE 1 ';
      if($nmb) $sql.=' AND u_nmb LIKE "%'.$nmb.'%" OR cu_nmb LIKE "%'.$nmb.'%" ';
      if($estatus != "T") $sql.=' AND u_estatus = "'.$estatus.'" ';
      $sql .= ' HAVING u_empresa = "'.$_SESSION['emp'].'" ';
      $this->resultalm = setq($sql);
    }

    function disable() {
        $sql = 'UPDATE unidades SET u_estatus = "I" WHERE u_id="' . $_GET['id'] . '" AND u_empresa = "'.$_SESSION['emp'].'"';
        setq($sql);
    }

    function enable() {
        $sql = 'UPDATE unidades SET u_estatus = "A WHERE u_id="' . $_GET['id'] . '" AND u_empresa = "'.$_SESSION['emp'].'"';
        // echo $sql;
        setq($sql);
    }
    function update() {
        $sql = 'UPDATE unidades SET
            u_nmb = "' . $this->nmb . '",
            u_descripcion = "' . $this->descripcion . '",
            u_clavesat = "' . $this->clavesat . '",
            u_estatus = "' . $this->estatus.'"
            WHERE   u_id = "' . $this->id . '" AND u_empresa = "'.$_SESSION['emp'].'"';    //   die($sql);
       setq($sql);

    }

}

/* -----------------------------------------------VIEW---------------------------------------------------------------------- */

class viewunidades {

  var $model;
  function __construct($model) {
    $this->model = $model;
  }

  function show($nmb,$estatus) {
   //Sección: E1 Encabezado - Botones de acción
    if($estatus == "A") $chest = "checked"; else $chest = "";

    $esta = "";
    $esti = "";
    $estt = "";
    if(!isset($estatus) || $estatus == "A") $esta = "selected";
    elseif($estatus == "I") $esti = "selected";
    else $estt = "selected";
    //Sección: E2 Encabezado - Filtros
    ?>
    <script>
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 78) {
          var btn = document.getElementById("nuevo");
          btn.click();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 76) {
          var btn = document.getElementById("filtrar");
          btn.click();
          $("#pref-search").focus();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 82) {
          window.location.reload();
        }
      });
    </script>
    <?php
    $nuevo ='<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setunidad.php" href="javascrip:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>';
      
    
    $filtro = '<form class="form-inline" role="form" method="post" action="?modulo=unidades&accion=index">
                <div class="mb-5">
                  <label for="">Filtrar por nombre:</label>
                  <input type="text" class="form-control" id="pref-search" name="nmb" value="'.$nmb.'" placeholder="Buscar por nombre">
                </div><!-- form group [search] -->

                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                <button type="submit" class="btn btn-sm btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=unidades&accion=index"><button type="button" class="btn btn-sm btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

        toolbar($_GET['modulo'], "", $filtro, $nuevo);
    
    if($this->model->id) $accion = "update"; else $accion = "insert";

    echo '<div class="card mt-3">
    <div class="card-body row" >';
      echo '<div class="col-md-12 col-12 col-sm-12">
        <div class="table-responsive text-medium" >
          <center>
      <table border="0" class="table table-bordered table-striped" id="myTable">
        <thead class="bg-light-blue bg-darken-2"><tr>
          <th width="20%">Unidad</th>
          <th width="45%">Descripción</th>
          <th width="18%">Clave SAT</th>
          <th width="15%">Acción</th>
        </tr></thead>'; /*
        echo '
        <form method="post" action="?modulo=unidades&accion='.$accion.'" name="newunidad" id="newunidad">
        <input type="hidden" value="'.$this->model->id.'" name="id" />
        <tr>
          <th>
            <input type="text" id="nmb" value="'.$this->model->nmb.'" class="form-control" placeholder="Nombre de la unidad" name="nmb" required="required" >
          </th>
          <th>
            <input type="text" id="descripcion" value="'.$this->model->descripcion.'" class="form-control" placeholder="Descripcion" name="descripcion" required="required" >
          </th>
          <th>
            <div class="input-group">
              <input type="text" style="font-size:1rem" id="clavesat" value="'.$this->model->clavesat.'" class="form-control" placeholder="Clave SAT" name="clavesat" required="required" readonly="readonly" >
              <span class="input-group-addon">
                <a href="popup/setcondiciones?modulo='.$_GET['modulo'].'&accion=insertd" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                  <button class="btn-sm btn-info"><i class="fa fa-search5"></i></button>
                </a>
              </span>
            </div>
          </th>
          <th>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i></button>
          </th>
        </tr></form>'; */

    echo '<script>
            function checerase(iduni){
              var conf = confirm("¿Deseas eliminar la unidad seleccionada?\nPresiona Aceptar para borrarla");
              if(conf == true){
                window.location.href="?modulo=unidades&accion=borrar&id=" + iduni;
              }
            }
            function verunidad(iduni){
              window.open("?modulo=articulos&accion=index&unidad=" + iduni);
            }
          </script>';
    while ($row = $this->model->resultalm->fetch_array()) {
     echo '<tr>';
        echo '<td>'.$row['u_nmb'].'</td>';
        echo '<td>'.$row['u_descripcion'].'</td>';
        echo '<td>'.$row['u_clavesat'].'</td>
              <td>'; 
              /*
        echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar unidad" href="?modulo=unidades&accion=index&id='.$row['u_id'].'">
                <button type="button" class="btn btn-info">
                  <i class="icon-edit2"></i>
                </button> 
              </a>' ; */

        echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar unidad" data-fancybox data-type="ajax" data-src="popup/setunidad.php?id='.$row['u_id'].'" href="javascript:;">
                <button type="button" class="btn btn-sm btn-info">
                  <i class="fas fa-pen"></i>
                </button> 
              </a>' ;
        if(busca($row['u_id'],'articulos','a_estatus = "A" AND a_unidad','COUNT(*)') == 0)
          echo '<button class="btn btn-danger btn-sm"   onclick="checerase('.$row['u_id'].')">
                    <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar la unidad de medida" class="fas fa-trash"></i></button>
                ';
        else
          echo '<button class="btn bg-teal btn-sm" style="background: teal;" onclick="verunidad('.$row['u_id'].')">
                    <i data-toggle="tooltip" data-placement="top" title data-original-title="La unidad se encuentra en uso, Presiona aquí para ver los productos asignados a la '.$row['u_nmb'].'" class="fa fa-eye" style="color: #ffffff;"></i></button>
                ';
      echo '</td></tr>';
    }
    echo '</table></center></div></div></div></div>
    
    <script>
    $(document).ready(function () {
      var windowHeight = $(window).height();
        
        $("#myTable").DataTable( {
            paging: true,
            scrollY: windowHeight * 0.5,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            
        });
      });
      </script>';
  }

  function showsel($nmb) {
    echo '<div class="div2" id="bboton02">
    <form method="post">
        Buscar por nombre <input type="text" name="nmb" value="'.$nmb.'" onfocus="this.select();" autofocus />
        <input type="submit" class="botont" id="actualizar" value="" />
    </form>
    </div></div>
    <form action="?modulo=unidades&accion=setunid" method="post">
      <table border="0" width="100%" class="lista">
        <thead class="bg-light-blue bg-darken-2 text-white"><tr>
          <th><center>Seleccionar</center></th>
          <th><center>Clave</center></th>
          <th><center>Unidad</center></th>
          <th><center>Descripción <input type="submit" class="botont" id="guardar" value="" /></center></th>
        </tr></thead>
        <form method="post" action="?modulo=unidades&accion=setunidades">';
    while ($row = $this->model->resultalm->fetch_array()) {
      if(empty($row['cu_descripcion'])) $row['cu_descripcion'] = "Sin Descripción disponible";

      if(busca($row['cu_id'],'unidades','u_empresa = "'.$_SESSION['emp'].'" AND u_id','COUNT(*)') > 0) $checkp = "checked";
      else $checkp = "";
      echo '<tr>';
        echo '<th><input type="checkbox" name="u'.$row['cu_clave'].'" '.$checkp.' /></th>';
        echo '<td>'.$row['cu_clave'].'</td>';
        echo '<td>'.$row['cu_nmb'].'</td>';
        echo '<td>'.$row['cu_descripcion'].'</td>';
      echo '</tr>';
    }
    echo '<tr><th colspan="4"><input type="submit" class="botont" id="guardar" value="" /></th></tr>';
    echo '</form>';
  }
}
?>