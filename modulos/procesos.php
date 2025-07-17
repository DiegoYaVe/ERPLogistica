<?php
class procesos{

function __construct(){
    $this->model = new modelprocesos($obj);
}
function index(){    
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;

    $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus']);
    $this->view = new viewlprocesos($this->model);
    $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus']);
}

function insert(){
  //foreachdie();
  if(isset($_POST['estatus'])){
    $est = "A";
  } else {
    $est = "I";
  }

  if($_POST['tipo'] != "O"){
    $_POST['texto'] = "";
  }

  $veri = busca($_POST['nmb'], 'pr_procesos', 'ppr_planta = "'.$_POST['planta'].'" AND ppr_fase = "'.$_POST['fase'].'" AND ppr_nmb', 'COUNT(*)');
  if($veri > 0){
    echo '<script>alert("El nombre de la proceso ya está ligado a la planta y fase en el sistema, verifique su información.")</script>';
  }else {
    $this->model->setdata('',$_POST['nmb'], $_POST['descripcion'],$est,$_POST['tipo'], $_POST['texto'],$_POST['planta'],$_POST['fase']);
    $this->model->insert();
  }

  redirect('?modulo=procesos&accion=index');
}
function update(){
    
  if(isset($_POST['estatus'])){
    $est = "A";
  } else {
    $est = "I";
  }
  if($_POST['tipo'] != "O"){
    $_POST['texto'] = "";
  }
  $veri = busca($_POST['nmb'], 'pr_procesos', 'ppr_planta = "'.$_POST['planta'].'" AND ppr_fase = "'.$_POST['fase'].'" AND ppr_id != "'.$_POST['id'].'" AND ppr_nmb', 'COUNT(*)');
  if($veri > 0){
    echo '<script>alert("El nombre de la proceso ya se encuentra en el sistema, verifique su información.")</script>';
  } else {
    $this->model->setdata($_POST['id'], $_POST['nmb'], $_POST['descripcion'],$est, $_POST['tipo'], $_POST['texto'],$_POST['planta'],$_POST['fase']);
    $this->model->update();
  }
  
  redirect('?modulo=procesos&accion=index');
}

  function borrar(){
    $sql = 'DELETE FROM pr_procesos
            WHERE ppr_id = "'.$_GET['id'].'"';
    setq($sql);

    redirect('?modulo=procesos&accion=index');
  }

}

class modelprocesos{
function result($nmb,$estatus){ //filtro
  $sql = 'SELECT * FROM pr_procesos WHERE 1=1 ';
  if($nmb) $sql.= ' AND ppr_nmb LIKE "%'.trim($nmb).'%"';
  if($estatus) $sql.= ' AND ppr_estatus="'.$estatus.'"';
  $sql.=' ORDER BY ppr_id ASC ';
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM pr_procesos WHERE ppr_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['ppr_id'];
    $this->nmb = $row['ppr_nmb'];
    $this->descripcion = $row['ppr_descripcion'];
    $this->estatus = $row['ppr_estatus'];
}


function setdata($id,$nmb, $descripcion, $estatus, $tipo, $obstipo,$planta,$fase){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->descripcion = clearvmayus($descripcion);
    $this->estatus = $estatus;
    $this->tipo = $tipo;
    $this->obstipo = $obstipo;
    $this->planta = $planta;
    $this->fase = $fase;
}

function insert(){
    $sql = 'INSERT INTO pr_procesos SET
            ppr_nmb = "'.$this->nmb.'",
            ppr_descripcion = "'.$this->descripcion.'",
            ppr_estatus = "'.$this->estatus.'",
            ppr_tipo = "'.$this->tipo.'",
            ppr_obstipo = "'.$this->obstipo.'",
            ppr_planta = "'.$this->planta.'",
            ppr_fase = "'.$this->fase.'"
            ';
    setq($sql);
}
function update(){
    $sql = 'UPDATE pr_procesos SET
            ppr_nmb = "'.$this->nmb.'",
            ppr_descripcion = "'.$this->descripcion.'",
            ppr_estatus = "'.$this->estatus.'",
            ppr_tipo = "'.$this->tipo.'",
            ppr_obstipo = "'.$this->obstipo.'",
            ppr_planta = "'.$this->planta.'",
            ppr_fase = "'.$this->fase.'"
            WHERE ppr_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlprocesos {
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
    
      $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setproceso.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>';
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      
    $filtrar = ' <form class="form-inline" role="form" method="post" action="?modulo=procesos&accion=index" id="filtro">
              
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
                <a href="?modulo=procesos&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

             
        toolbar($_GET['modulo'],"", $filtrar, $nuevo);

      echo '<script>
            function checerase(iduni){
              var conf = confirm("¿Deseas eliminar La proceso seleccionada?\nPresiona OK para borrarlo");
              if(conf == true){
                window.location.href="?modulo=procesos&accion=borrar&id=" + iduni;
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
                    <th width="20%">Nombre</th>
                    <th width="30%">Descripción</th>
                    <th width="10%">Planta</th>
                    <th width="10%">Fase</th>
                    <th width="20%">Estatus</th>
                    <th width="10%"></th>
                  </tr>
                </thead>'; 
      while ($row = $this->model->result->fetch_array()) {
        if($row['ppr_estatus'] == "A") {
          $bg = 'success';
          $txt = 'ACTIVO';
        }else {
          $bg = 'danger';
          $txt = 'INACTIVO';
        }

        echo '<tr>
        <td>'.$row['ppr_nmb'].'</td>
        <td>'.$row['ppr_descripcion'].'</td>
        <td>'.busca($row['ppr_planta'],'pr_plantas','pp_id','pp_nmb').'</td>
        <td>'.busca($row['ppr_fase'],'pr_fases','pf_id','pf_nmb').'</td>
        <td class="alert bg-'.$bg.'">'.$txt.'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar la proceso" data-fancybox data-type="ajax" data-src="popup/setproceso.php?id='.$row['ppr_id'].'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';

          //if(busca($row['pmp_id'],'articulos_composicion','ac_molde','COUNT(*)') == 0)
          /* echo '<button type="button" class="btn btn-danger" onclick="checerase('.$row['ppr_id'].')">
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
            pageLength: 100,
        });
      });
      </script>';


  
}


}
?>