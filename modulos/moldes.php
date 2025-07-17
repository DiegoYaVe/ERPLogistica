<?php
class moldes{

function __construct(){
    $this->model = new modelmoldes($obj);
}
function index(){    
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;

    $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus']);
    $this->view = new viewlmoldes($this->model);
    $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus']);
}

function insert(){
  //foreachdie();
  if(isset($_POST['estatus'])){
    $est = "A";
  } else {
    $est = "I";
  }
  $this->model->setdata('',$_POST['nmb'], $_POST['x'], $_POST['y'], $_POST['z'],$est);
  $this->model->insert();

  redirect('?modulo=moldes&accion=index');
}
function update(){
    
  if(isset($_POST['estatus'])){
    $est = "A";
  } else {
    $est = "I";
  }

  $this->model->setdata($_POST['id'], $_POST['nmb'], $_POST['x'], $_POST['y'], $_POST['z'],$est);
  $this->model->update();
  redirect('?modulo=moldes&accion=index');
}

  function borrar(){
    $sql = 'DELETE FROM pr_moldes
            WHERE pm_id = "'.$_GET['id'].'"';
    setq($sql);

    redirect('?modulo=moldes&accion=index');
  }

}

class modelmoldes{
function result($nmb,$estatus){ //filtro
  $sql = 'SELECT * FROM pr_moldes WHERE 1=1 ';
  if($nmb) $sql.= ' AND pm_nmb LIKE "%'.trim($nmb).'%"';
  if($estatus) $sql.= ' AND pm_estatus="'.$estatus.'"';
  $sql.=' ORDER BY pm_id ASC ';
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM pr_moldes WHERE pm_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['pm_id'];
    $this->nmb = $row['pm_nmb'];
    $this->x = $row['pm_x'];
    $this->y = $row['pm_y'];
    $this->z = $row['pm_z'];
    $this->estatus = $row['pm_estatus'];
}


function setdata($id,$nmb, $x, $y, $z, $estatus){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->x = $x;
    $this->y = $y;
    $this->z = $z;
    $this->estatus = $estatus;
}

function insert(){
    $sql = 'INSERT INTO pr_moldes SET
            pm_nmb = "'.$this->nmb.'",
            pm_x = "'.$this->x.'",
            pm_y = "'.$this->y.'",
            pm_z = "'.$this->z.'",
            pm_estatus = "'.$this->estatus.'"
            ';
    setq($sql);
}
function update(){
    $sql = 'UPDATE pr_moldes SET
            pm_nmb = "'.$this->nmb.'",
            pm_x = "'.$this->x.'",
            pm_y = "'.$this->y.'",
            pm_z = "'.$this->z.'",
            pm_estatus = "'.$this->estatus.'"
            WHERE pm_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlmoldes {
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
    
      $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setmolde.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>';
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      
    $filtrar = ' <form class="form-inline" role="form" method="post" action="?modulo=moldes&accion=index" id="filtro">
              
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
                <a href="?modulo=moldes&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

             
        toolbar($_GET['modulo'],"", $filtrar, $nuevo);

      echo '<script>
            function checerase(iduni){
              var conf = confirm("¿Deseas eliminar el molde seleccionada?\nPresiona OK para borrarlo");
              if(conf == true){
                window.location.href="?modulo=moldes&accion=borrar&id=" + iduni;
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
                    <th width="30%">Medidas</th>
                    <th width="20%">Estatus</th>
                    <th width="10%"></th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
      if($row['pm_estatus'] == "A") {
        $bg = 'success';
        $txt = 'ACTIVO';
      }else {
        $bg = 'danger';
        $txt = 'INACTIVO';
      }

        echo '<tr>
        <td>'.$row['pm_nmb'].'</td>
        <td>'.number_format($row['pm_x'], 2).'x'.number_format($row['pm_y'], 2).'x'.number_format($row['pm_z'],2).'</td>
        <td class="alert bg-'.$bg.'">'.$txt.'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar el molde" data-fancybox data-type="ajax" data-src="popup/setmolde.php?id='.$row['pm_id'].'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';

          if(busca($row['pm_id'],'articulos_composicion','ac_molde','COUNT(*)') == 0)
          echo '<button type="button" class="btn btn-danger" onclick="checerase('.$row['pm_id'].')">
                  <i data-toggle="tooltip" data-placement="top" class="fas fa-trash"></i></button>
                ';
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