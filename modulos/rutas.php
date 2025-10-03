<?php
ini_set('display_errors', 0);
class rutas{

function __construct(){
    $this->model = new modelrutas($obj);
}
function index(){
    

    $this->model->result();
    $this->view = new viewlrutas($this->model);
    $this->view->browse();
}

function puntos(){
  $this->view = new viewlrutas($this->model);
  $this->view->puntos();
}

function insertdestino(){
  $sqlins='INSERT INTO ruta_destino SET rd_nombre = "'.$_POST['nombre'].'", rd_descripcion = "'.$_POST['descripcion'].'", rd_estatus = "A"';
  setq($sqlins);
  redirect('?modulo=rutas&accion=puntos'); 
}

function insertorigen(){
  $sqlins='INSERT INTO ruta_origen SET ro_nombre = "'.$_POST['nombre'].'", ro_descripcion = "'.$_POST['descripcion'].'", ro_estatus = "A"';
  setq($sqlins);
  redirect('?modulo=rutas&accion=puntos'); 
}

function insert(){
  $sqlins = 'INSERT INTO ruta_tarifario SET 
    rt_origen = "' . $_POST['origen'] . '", 
    rt_destino = "' . $_POST['destino'] . '", 
    rt_km = "' . $_POST['kilometros'] . '", 
    rt_rendimiento = "' . $_POST['rendimiento'] . '", 
    rt_precio = "' . $_POST['precio'] . '", 
    rt_capacidad_tanque = "' . $_POST['capacidad'] . '", 
    rt_tanques = "' . $_POST['tanques'] . '", 
    rt_combustible = "' . $_POST['combustible'] . '", 
    rt_casetas = "' . $_POST['casetas'] . '", 
    rt_desgaste = "' . $_POST['desgaste'] . '", 
    rt_operador = "' . $_POST['operador'] . '", 
    rt_costodvl = "' . $_POST['costodvl'] . '", 
    rt_ventadvl = "' . $_POST['ventadvl'] . '", 
    rt_ventakraus = "' . $_POST['ventakraus'] . '"';
  setq($sqlins);
  redirect('?modulo=rutas&accion=index');
}
function update(){
  
  $sqlupd = 'UPDATE ruta_tarifario SET 
    rt_origen = "' . $_POST['origen'] . '", 
    rt_destino = "' . $_POST['destino'] . '", 
    rt_km = "' . $_POST['kilometros'] . '", 
    rt_rendimiento = "' . $_POST['rendimiento'] . '", 
    rt_precio = "' . $_POST['precio'] . '", 
    rt_capacidad_tanque = "' . $_POST['capacidad'] . '", 
    rt_tanques = "' . $_POST['tanques'] . '", 
    rt_combustible = "' . $_POST['combustible'] . '", 
    rt_casetas = "' . $_POST['casetas'] . '", 
    rt_desgaste = "' . $_POST['desgaste'] . '", 
    rt_operador = "' . $_POST['operador'] . '", 
    rt_costodvl = "' . $_POST['costodvl'] . '", 
    rt_ventadvl = "' . $_POST['ventadvl'] . '", 
    rt_ventakraus = "' . $_POST['ventakraus'] . '" WHERE rt_id = "'.$_POST['id'].'"';
  setq($sqlupd);

  redirect('?modulo=rutas&accion=index');
}

function borrar(){
  $sql = 'DELETE FROM ruta_tarifario
          WHERE rt_id = "'.$_GET['id'].'"';
  setq($sql);

  redirect("?modulo=rutas&accion=index");
}

function borrarorigen(){
  $sql = 'DELETE FROM ruta_origen
          WHERE ro_id = "'.$_GET['id'].'"';
  setq($sql);

  redirect("?modulo=rutas&accion=puntos");
}

function borrardestino(){
  $sql = 'DELETE FROM ruta_destino
          WHERE rd_id = "'.$_GET['id'].'"';
  setq($sql);

  redirect("?modulo=rutas&accion=puntos");
}
}

class modelrutas{
function result(){ //filtro
  
  $sql = 'SELECT * FROM ruta_tarifario INNER JOIN ruta_origen ON ro_id = rt_origen INNER JOIN ruta_destino ON rd_id = rt_destino  WHERE 1=1 ';
  $sql.=' ORDER BY rt_id ASC ';
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM ruta_tarifario WHERE rt_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['rt_id'];
    $this->nmb = $row['rt_nmb'];
    $this->siglas = $row['rt_siglas'];
    $this->inflable = $row['rt_inflable'];
}

function setdata($id,$nmb,$siglas, $inflable){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->siglas = clearvmayus($siglas);
    $this->inflable = clearvmayus($inflable);
}
function insert(){
    $sql = 'INSERT INTO rutas SET
            rt_id = "'.$this->id.'",
            rt_nmb = "'.$this->nmb.'",
            rt_siglas = "'.$this->siglas.'",
            rt_inflable = "'.$this->inflable.'"';
    setq($sql);
}
function update(){
    $sql = 'UPDATE rutas SET
            rt_nmb = "'.$this->nmb.'",
            rt_siglas = "'.$this->siglas.'",
            rt_inflable = "'.$this->inflable.'"
            WHERE rt_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlrutas {
    var $model;

function __construct($model) {

    $this->model = $model;
    $this->model->aest = array("A" => "Activo","I" => "Inactivo");
?>
<script language="JavaScript">
function checkSubmitedit() {
    document.getElementById("guardar").value = "JD";
    document.getElementById("guardar").disabled = true;
    return true;
}
</script>
<?php
}
function browse() {
   //Sección: E1 Encabezado - Botones de acción
    
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
    
      $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setruta.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>';
      $boton = '<a href="?modulo=rutas&accion=puntos">
                <button type="button" class="btn btn-info btn-sm"><i class="fa fa-plus"></i> Origen/Destino </button>
              </a>';
      
      
    /* $filtrar = '
              <form class="form-inline" role="form" method="post" action="?modulo=rutas&accion=index" id="filtro">
              <input name="page" id="page" value="'.$page.'" hidden> 
                <div class="mb-5">
                  <label for="">Filtrar por nombre:</label>
                  <input onkeyup="ceropage();" type="text" class="form-control" id="pref-search" name="nmb" value=" '.$nmb.'" placeholder="Buscar por nombre">
                  <script>
                    function ceropage(){
                      document.getElementById("page").value = 0;
                      var codigo = event.which || event.keyCode;
                      if(codigo === 13) {
                        mandar(0);
                      }
                    }
                  </script>
                </div><!-- form group [search] -->

                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                <button type="submit" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=rutas&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>'; */

        toolbar($_GET['modulo'],"", '', $nuevo, $boton);

      echo '<script>
            function checerase(iduni){
              var conf = confirm("¿Deseas eliminar la ruta seleccionada?\nPresiona Aceptar para borrarla");
              if(conf == true){
                window.location.href="?modulo=rutas&accion=borrar&id=" + iduni;
              }
            }
            function verlineasneg(iduni){
              window.open("?modulo=articulos&accion=index&ruta=" + iduni);
            }
          </script>

        ';




  echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table table-striped" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th width="">Origen</th>
                    <th width="">Destino</th>
                    <th width="">Kilometros</th>
                    <th width="">Total costo</th>
                    <th width="">Total venta</th>
                    <th width=""></th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
        
        echo '<tr>
        <td>'.$row['ro_nombre'].'</td>
        <td>'.$row['rd_nombre'].'</td>
        <td>'.number_format($row['rt_km'], 0).'</td>
        <td>$'.number_format($row['rt_costodvl'],2).'</td>
        <td>$'.number_format($row['rt_ventadvl'], 2).'</td>
        <td>';
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar ruta" data-fancybox data-type="ajax" data-src="popup/setruta.php?id='.$row['rt_id'].'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';
        echo '</td>
        ';
    }
    echo '</table>
    
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

function puntos() {
   
      $izquierda = '
      <a href="?modulo=rutas&accion=index">
      <button type="button" class=" btn btn-warning"><i class="fa fa-arrow-left"></i>Atrás</button>
      </a>';  

        toolbar('Origen/Destino',$izquierda, '');

      echo '<script>
            function borrarorigen(iduni){
              var conf = confirm("¿Deseas eliminar el origen seleccionado?\nPresiona Aceptar para borrarla");
              if(conf == true){
                window.location.href="?modulo=rutas&accion=borrarorigen&id=" + iduni;
              }
            }
            function borrardestino(iduni){
              var conf = confirm("¿Deseas eliminar el destino seleccionado?\nPresiona Aceptar para borrarla");
              if(conf == true){
                window.location.href="?modulo=rutas&accion=borrardestino&id=" + iduni;
              }
            }
          </script>
        ';




  echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-6 col-6 col-sm-6 mb-4">
          <form method="post" action="?modulo=rutas&accion=insertorigen" class="row">
            <div class="col-5 col-md-5">
              <label for="agregar" class">Nombre</label>
              <input type="text" name="nombre" id="nombre" placeholder="Nombre" class="form-control" required />
            </div>
            <div class="col-5 col-md-5">
              <label for="agregar" class">Descripción</label>
              <input type="text" name="descripcion" id="descripcion" placeholder="Descripcion" class="form-control" required />
            </div>
            <div class="col-2 col-md-2 mt-6">
              <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i>Añadir</button>
            </div>
          </form>
          <div class="table-responsive text-medium mt-4" >
            <center>
              <h3>Origen</h3>
              <table class="table table-striped" id="myTable">
                <thead class="bg-primary text-white">
                  <tr>
                    <th width="60%">Nombre</th>
                    <th width="20%">Descripción</th>
                    <th width="20%"></th>
                  </tr>
                </thead>'; 
    $setqo = 'SELECT * FROM ruta_origen';
    $resulto = setq($setqo);
    while ($row = $resulto->fetch_array()) {
        echo '<tr>
        <td>'.$row['ro_nombre'].'</td>
        <td>'.$row['ro_descripcion'].'</td>
        <td>';
          echo '<button type="button" class="btn btn-sm btn-danger" onclick="borrarorigen('.$row['ro_id'].')">
                  <i data-toggle="tooltip" data-placement="top" class="fas fa-trash"></i></button>
                ';
        echo '</td>
        ';
    }
    echo '</table>
    </div>
    </div>';


    echo '<div class="col-md-6 col-6 col-sm-6">
          <form method="post" action="?modulo=rutas&accion=insertdestino" class="row">
            <div class="col-5 col-md-5">
              <label for="agregar" class">Nombre</label>
              <input type="text" name="nombre" id="nombre" placeholder="Nombre" class="form-control" required />
            </div>
            <div class="col-5 col-md-5">
              <label for="agregar" class">Descripción</label>
              <input type="text" name="descripcion" id="descripcion" placeholder="Descripcion" class="form-control" required />
            </div>
            <div class="col-2 col-md-2 mt-6">
              <button class="btn btn-sm btn-primary"><i class="fas fa-plus"></i>Añadir</button>
            </div>
          </form>
          <div class="table-responsive text-medium mt-4" >
            <center>
              <h3>Destino</h3>
              <table class="table table-striped" id="myTable">
                <thead class="bg-primary text-white">
                  <tr>
                    <th width="60%">Nombre</th>
                    <th width="20%">Descripción</th>
                    <th width="20%"></th>
                  </tr>
                </thead>'; 
    $setqo = 'SELECT * FROM ruta_destino';
    $resulto = setq($setqo);
    while ($row = $resulto->fetch_array()) {
        echo '<tr>
        <td>'.$row['rd_nombre'].'</td>
        <td>'.$row['rd_descripcion'].'</td>
        <td>';
          echo '<button type="button" class="btn btn-sm btn-danger" onclick="borrardestino('.$row['rd_id'].')">
                  <i data-toggle="tooltip" data-placement="top" class="fas fa-trash"></i></button>
                ';
        echo '</td>
        ';
    }
    echo '</table>
    </div>
    </div>';


  
}
}
?>