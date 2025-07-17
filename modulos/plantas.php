<?php
class plantas{

function __construct(){
    $this->model = new modelplantas($obj);
}
function index(){    
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;

    $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus']);
    $this->view = new viewlplantas($this->model);
    $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus']);
}

function insert(){
  //foreachdie();
  if(isset($_POST['estatus'])){
    $est = "A";
  } else {
    $est = "I";
  }
  $veri = busca($_POST['nmb'], 'pr_plantas', 'pp_nmb', 'COUNT(*)');
  if($veri > 0){
    echo '<script>alert("El nombre de la planta ya se encuentra en el sistema, verifique su información.")</script>';
  }else {
    $this->model->setdata('',$_POST['nmb'], $_POST['descripcion'],$est);
    $this->model->insert();
  }

  redirect('?modulo=plantas&accion=index');
}
function update(){
    
  if(isset($_POST['estatus'])){
    $est = "A";
  } else {
    $est = "I";
  }
  $veri = busca($_POST['nmb'], 'pr_plantas', 'pp_id != "'.$_POST['id'].'" AND pp_nmb', 'COUNT(*)');
  if($veri > 0){
    echo '<script>alert("El nombre de la planta ya se encuentra en el sistema, verifique su información.")</script>';
  } else {
    $this->model->setdata($_POST['id'], $_POST['nmb'], $_POST['descripcion'],$est);
    $this->model->update();
  }
  
  redirect('?modulo=plantas&accion=index');
}

  function borrar(){
    $sql = 'DELETE FROM pr_plantas
            WHERE pp_id = "'.$_GET['id'].'"';
    setq($sql);

    redirect('?modulo=plantas&accion=index');
  }

}

class modelplantas{
function result($nmb,$estatus){ //filtro
  $sql = 'SELECT * FROM pr_plantas WHERE 1=1 ';
  if($nmb) $sql.= ' AND pp_nmb LIKE "%'.trim($nmb).'%"';
  if($estatus) $sql.= ' AND pp_estatus="'.$estatus.'"';
  $sql.=' ORDER BY pp_id ASC ';
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM pr_plantas WHERE pp_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['pp_id'];
    $this->nmb = $row['pp_nmb'];
    $this->descripcion = $row['pp_descripcion'];
    $this->estatus = $row['pp_estatus'];
}


function setdata($id,$nmb, $descripcion, $estatus){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->descripcion = clearvmayus($descripcion);
    $this->estatus = $estatus;
}

function insert(){
    $sql = 'INSERT INTO pr_plantas SET
            pp_nmb = "'.$this->nmb.'",
            pp_descripcion = "'.$this->descripcion.'",
            pp_estatus = "'.$this->estatus.'"
            ';
    setq($sql);
}
function update(){
    $sql = 'UPDATE pr_plantas SET
            pp_nmb = "'.$this->nmb.'",
            pp_descripcion = "'.$this->descripcion.'",
            pp_estatus = "'.$this->estatus.'"
            WHERE pp_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlplantas {
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
function browse($nmb,$estatus) {
   //Sección: E1 Encabezado - Botones de acción

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
    
      $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setplanta.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>';
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      
    $filtrar = ' <form class="form-inline" role="form" method="post" action="?modulo=plantas&accion=index" id="filtro">
              
                <div class="mb-5">
                  <label for="">Filtrar por nombre:</label>
                  <input type="text" class="form-control" id="pref-search" name="nmb" value=" '.$nmb.'" placeholder="Buscar por nombre">
                </div>
                <div class="mb-5">
                  <label for="">Filtrar por estatus:</label>
                  <select id="estatus" name="estatus" class="form-control">
                    <option value="">TODOS</option>
                    <option value="A">ACTIVO</option>
                    <option value="I">INACTIVO</option>
                  </select>
                </div><!-- form group [search] -->
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                <button type="submit" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=plantas&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

             
        toolbar($_GET['modulo'],"", $filtrar, $nuevo);

      echo '<script>
            function checerase(iduni){
              var conf = confirm("¿Deseas eliminar La planta seleccionada?\nPresiona OK para borrarlo");
              if(conf == true){
                window.location.href="?modulo=plantas&accion=borrar&id=" + iduni;
              }
            }
          </script>

        ';




  echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th width="40%">Nombre</th>
                    <th width="30%">Descripción</th>
                    <th width="20%">Estatus</th>
                    <th width="10%"></th>
                  </tr>
                </thead>'; 
      while ($row = $this->model->result->fetch_array()) {
        if($row['pp_estatus'] == "A") {
          $bg = 'success';
          $txt = 'ACTIVO';
        }else {
          $bg = 'danger';
          $txt = 'INACTIVO';
        }

        echo '<tr>
        <td>'.$row['pp_nmb'].'</td>
        <td>'.$row['pp_descripcion'].'</td>
        <td class="alert bg-'.$bg.'">'.$txt.'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar la planta" data-fancybox data-type="ajax" data-src="popup/setplanta.php?id='.$row['pp_id'].'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';

          //if(busca($row['pmp_id'],'articulos_composicion','ac_molde','COUNT(*)') == 0)
          /* echo '<button type="button" class="btn btn-danger" onclick="checerase('.$row['pp_id'].')">
                  <i data-toggle="tooltip" data-placement="top" class="fas fa-trash"></i></button>
                '; */
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


}
?>