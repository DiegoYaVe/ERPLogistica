<?php
class materiaprima{

function __construct(){
    $this->model = new modelmateriaprima($obj);
}
function index(){
    
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;

    $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus']);
    $this->view = new viewlmateriaprima($this->model);
    $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus']);
}

function insert(){
  //foreachdie();
  if(isset($_POST['estatus'])){
    $est = "A";
  } else {
    $est = "I";
  }
  
  $this->model->setdata('',$_POST['nmb'], $_POST['material'], $_POST['color'],$_POST['tipo'],$est);
  $this->model->insert();

  redirect('?modulo=materiaprima&accion=index');
}
function update(){
    
  if(isset($_POST['estatus'])){
    $est = "A";
  } else {
    $est = "I";
  }
  $this->model->setdata($_POST['id'], $_POST['nmb'], $_POST['material'], $_POST['color'],$_POST['tipo'],$est);
  $this->model->update();
  redirect('?modulo=materiaprima&accion=index');
}

  function borrar(){
    $sql = 'DELETE FROM pr_materiaprima
            WHERE pmp_id = "'.$_GET['id'].'"';
    setq($sql);

    redirect('?modulo=materiaprima&accion=index');
  }

}

class modelmateriaprima{
function result($nmb,$estatus){ //filtro
  $bloque = 50;
  $sql = 'SELECT * FROM pr_materiaprima  WHERE 1=1 ';
  if($nmb) $sql.= ' AND pmp_nmb LIKE "%'.trim($nmb).'%"';
  if($estatus) $sql.= ' AND pmp_estatus="'.$estatus.'"';
  $sql.=' ORDER BY pmp_id ASC ';
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM pmp_materiaprima WHERE pmp_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['pmp_id'];
    $this->nmb = $row['pmp_nmb'];
    $this->material = $row['pmp_material'];
    $this->color = $row['pmp_color'];
    $this->estatus = $row['pmp_estatus'];
}


function setdata($id,$nmb, $material, $color, $tipo, $estatus){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->material = clearvmayus($material);
    $this->color = $color;
    $this->tipo = $tipo;
    $this->estatus = $estatus;
}

function insert(){
    $sql = 'INSERT INTO pr_materiaprima SET
            pmp_nmb = "'.$this->nmb.'",
            pmp_material = "'.$this->material.'",
            pmp_tipo = "'.$this->tipo.'",
            pmp_estatus = "'.$this->estatus.'",
            pmp_color = "'.$this->color.'"';
    setq($sql);
}
function update(){
    $sql = 'UPDATE pr_materiaprima SET
            pmp_nmb = "'.$this->nmb.'",
            pmp_material = "'.$this->material.'",
            pmp_estatus = "'.$this->estatus.'",
            pmp_color = "'.$this->color.'",
            pmp_tipo = "'.$this->tipo.'"
            WHERE pmp_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlmateriaprima {
    var $model;

function __construct($model) {

    $this->model = $model;
    $this->model->aest = array("A" => "Activo","I" => "Inactivo");
    $this->model->ntipo = array("P" => "Pieza","A" => "Área","V" => "Volumen");
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
    
      $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setmateriaprima.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>';
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      
    $filtrar = '
              <form class="form-inline" role="form" method="post" action="?modulo=materiaprima&accion=index" id="filtro">
              
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
                <a href="?modulo=categorias&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';
        toolbar("Materia prima","", $filtrar, $nuevo);

      echo '<script>
            function checerase(iduni){
              var conf = confirm("¿Deseas eliminar la materia prima seleccionada?\nPresiona OK para borrarla");
              if(conf == true){
                window.location.href="?modulo=materiaprima&accion=borrar&id=" + iduni;
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
                    <th width="20%">Material</th>
                    <th width="20%">Tipo</th>
                    <th width="15%">Color</th>
                    <th width="15%">Estatus</th>
                    <th width="10%"></th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
      if($row['pmp_estatus'] == "A") {
        $bg = 'success';
        $txt = 'ACTIVO';
      }else {
        $bg = 'danger';
        $txt = 'INACTIVO';
      }

        echo '<tr>
        <td>'.$row['pmp_nmb'].'</td>
        <td>'.$row['pmp_material'].'</td>
        <td>'.$this->model->ntipo[$row['pmp_tipo']].'</td>
        <td style="background:'.$row['pmp_color'].'"></td>
        <td class="alert bg-'.$bg.'">'.$txt.'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar materia prima" data-fancybox data-type="ajax" data-src="popup/setmateriaprima.php?id='.$row['pmp_id'].'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';
          if(busca($row['pmp_id'],'articulos_composicion','ac_mp','COUNT(*)') == 0)
          echo '<button type="button" class="btn btn-danger" onclick="checerase('.$row['pmp_id'].')">
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
            pageLength: 50,
        });
      });
      </script>';


  
}


}
?>