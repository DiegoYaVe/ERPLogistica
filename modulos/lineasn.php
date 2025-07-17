<?php
class lineasn{

function __construct(){
    $this->model = new modellineasn($obj);
}
function index(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";

    $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus']);
    $this->model->select($_GET['id']);
    $this->view = new viewlineasn($this->model);
    $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus']);
}
function edit(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;

    $this->model->select($_GET['id']);
    $this->view = new viewlineasn($this->model);
    $this->view->edit();
}
function insert(){

  $estatus = "A";

  $this->model->setdata(getmax('ln_id','lineas_negocio'), $_POST['nmb'], $_POST['utilidad'], $_POST['abrevia'], $estatus,$_POST['color'],$_SESSION['emp']);
  $unieue = unique($_POST['abrevia'],'ln_alias','lineas_negocio','ln_empresa = "'.$_SESSION['emp'].'"');

  if($unieue == 1) {
    $this->model->insert();
    $error = "";
  }
  else $error = "&error=2XML01";


  redirect('?modulo=lineasn&accion=index'.$error);
}
function update(){
   $estatus = "A";

  $this->model->setdata($_POST['id'], $_POST['nmb'], $_POST['utilidad'], $_POST['abrevia'], $estatus,$_POST['color'],$_SESSION['emp']);
  if(unique($_POST['abrevia'],'ln_alias','lineas_negocio','ln_empresa != "'.$_SESSION['emp'].'" AND ln_id = "'.$this->model->id.'"') == 1) {
    $this->model->update();
    $error = "";
  }
  else $error = "&error=2XML01";

  redirect('?modulo=lineasn&accion=index'.$error);
}

  function borrar(){
    $sql = 'DELETE FROM lineas_negocio
            WHERE ln_id = "'.$_GET['id'].'"
            AND ln_empresa = "'.$_SESSION['emp'].'"';
    setq($sql);

    redirect("?modulo=lineasn&accion=index");
  }

}

class modellineasn{
function result($nmb,$estatus){
    if($estatus != "T")
      $sql = 'SELECT * FROM lineas_negocio  WHERE ln_empresa = "'.$_SESSION['emp'].'" AND ln_estatus = "'.$estatus.'" ';
    else
      $sql = 'SELECT * FROM lineas_negocio  WHERE ln_empresa = "'.$_SESSION['emp'].'" ';

    if($nmb) $sql.= ' AND ln_nmb LIKE "%'.trim($nmb).'%"';

    $sql.=' ORDER BY ln_id ASC';
    $this->result = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM lineas_negocio WHERE ln_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['ln_id'];
    $this->nmb = $row['ln_nmb'];
    $this->utilidad = $row['ln_utilidad'];
    $this->alias = $row['ln_alias'];
    $this->estatus = $row['ln_estatus'];
    $this->color = $row['ln_color'];
}
function setdata($id,$nmb,$utilidad,$alias,$estatus,$color,$empresa){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = str_replace($simbol,$cambio, mb_strtoupper(trim($nmb)));
    $this->utilidad = $utilidad;
    $this->alias = str_replace($simbol,$cambio, mb_strtoupper(trim($alias)));
    $this->estatus = $estatus;
    $this->color = mb_strtoupper($color);
    $this->empresa = mb_strtoupper($empresa);
}
function insert(){
    $sql = 'INSERT INTO lineas_negocio SET
            ln_id = "'.$this->id.'",
            ln_nmb = "'.$this->nmb.'",
            ln_utilidad = "'.$this->utilidad.'",
            ln_alias = "'.$this->alias.'",
            ln_estatus = "'.$this->estatus.'",
            ln_empresa = "'.$this->empresa.'",
            ln_color = "'.$this->color.'"';
    setq($sql);
}
function update(){
    $sql = 'UPDATE lineas_negocio SET
            ln_nmb = "'.$this->nmb.'",
            ln_utilidad = "'.$this->utilidad.'",
            ln_alias = "'.$this->alias.'",
            ln_estatus = "'.$this->estatus.'",
            ln_empresa = "'.$this->empresa.'",
            ln_color = "'.$this->color.'"
            WHERE ln_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlineasn {
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
    echo '<div class="row page-title-actions">';
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

    <div class="mb-5 col-md-12">
      <a id="nuevo" class="btn btn-primary" data-fancybox data-type="ajax" data-src="popup/setlinean.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>
      <button id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
      <button class="btn btn-secondary" data-toggle="tooltip" data-placement="bottom" data-original-title="Una linea de negocio es un organizador estadístico de tus productos, esto te permitirá analizar el comportamiento y crear planes a futuro" data-trigger="click" >
        <i class="icon-question-circle"></i>
      </button>
    </div>
    <div id="filter-panel" class="col-md-12 collapse filter-panel mb-1">
          <div class="panel panel-default">
            <div class="panel-body">
              <form class="form-inline" role="form" method="post" action="?modulo=lineasn&accion=index">
                <div class="mb-5">
                  <label for="">Filtrar por nombre:</label>
                  <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
                </div><!-- form group [search] -->

                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                <button type="submit" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=lineasn&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                </button></a>
                </div>
              </form>
            </div>
          </div>
        </div>

      </div></div>

      <script>
            function checerase(iduni){
              var conf = confirm("¿Deseas eliminar la Linea de negocio seleccionada?\nPresiona Aceptar para borrarla");
              if(conf == true){
                window.location.href="?modulo=lineasn&accion=borrar&id=" + iduni;
              }
            }
            function verlineasneg(iduni){
              window.open("?modulo=articulos&accion=index&linean=" + iduni);
            }
          </script>

        <div class="main-card mb-3 card">
          <div class="">

<?php


  echo '<div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead class="bg-light-blue bg-darken-2 text-white">
                  <tr>
                    <th>Nombre</th>
                    <th>Porcentaje de Utilidad (Promedio)</th>
                    <th>Abreviatura</th>
                    <th>Color identificador</th>
                    <th colspan="2"></th>
                  </tr>
                </thead>'; 
                /*
                  if($this->model->id) $accion = 'update'; else $accion = 'insert';

                  if($this->model->estatus == "A" || $accion == "insert") $check = 'checked="checked"'; else $check = "";
                  echo '<form method="post" action="?modulo=lineasn&accion='.$accion.'">
                        <input type="hidden" value="'.$this->model->id.'" name="id" />
                          <tr>
                            <td><input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la linea" name="nmb" value="'.$this->model->nmb.'" required="required" ></td>
                            <td><input type="number" name="utilidad" data-toggle="tooltip" data-placement="top" data-original-title="Determina el porcentaje aproximado de utilidad por cada venta de ese producto"  min="0" step="1" max="9999" class="form-control" placeholder="% Utilidad" required="required" value="'.$this->model->utilidad.'"  /></td>
                            <td><input type="text" id="abrevia" class="form-control mayus" data-toggle="tooltip" data-placement="top" data-original-title="Determina un nombre corto de tu Linea de negocio, 3 Carácteres" placeholder="Siglas" name="abrevia" minlength="3" maxlength="3" value="'.$this->model->alias.'" required="required"  ></td>
                            <td><input type="color" id="color" name="color" required="required"  data-toggle="tooltip" data-placement="top" data-original-title="Elije un color para que se muestre en tus reportes gráficos" value="'.$this->model->color.'"  ></td>
                            <td><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i></td>
                        </tr></form>'; 
                */
          
    while ($row = $this->model->result->fetch_array()) {
        echo '<tr>
        <td>'.$row['ln_nmb'].'</td>
        <td>'.number_format($row['ln_utilidad'],0).' %</td>
        <td>'.$row['ln_alias'].'</td>
        <td style="background:'.$row['ln_color'].'">&nbsp;</td>
        <td>'.$this->model->aest[$row['ln_estatus']].'</td>
        <td>';/*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar linea de negocio" href="?modulo=lineasn&accion=index&id='.$row['ln_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ;*/        
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar linea de negocio"  data-fancybox data-type="ajax" data-src="popup/setlinean.php?id='.$row['ln_id'].'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="icon-edit2"></i>
            </button> 
          </a>';

        if(busca($row['ln_id'],'articulos','a_estatus = "A" AND a_lineaneg','COUNT(*)') == 0)
          echo '<button type="button" class="btn btn-danger" onclick="checerase('.$row['ln_id'].')">
                  <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar la linea de negocio" class="icon-eraser"></i></button>
                ';
        else
          echo '<button type="button" class="btn bg-teal" onclick="verlineasneg('.$row['ln_id'].')">
                  <i data-toggle="tooltip" data-placement="top" title data-original-title="La linea de negocio se encuentra en uso, Presiona aquí para ver los productos asignados a la '.$row['ln_nmb'].'" class="fa fa-eye"></i>
                </button> ';

        echo '</td>
        ';
    }
    echo '</table>';
}
function edit(){
    $accion = 'insert';
    $ckbarra = "checked";
    $ckinstala = "";
    $ckproyecto = "";
    if($this->model->id){
        $accion = 'update';
        $ckbarra = '';
        $ckproyecto = '';
        if($this->model->estatus == 'A')
            $ckbarra = "checked";
        if($this->model->proyecto == "1")
            $ckproyecto = "checked";
        if($this->model->instalacion == "1")
            $ckinstala = "checked";
    }

    echo '<div class="div2" id="bboton01">
    <input type=button value=" "   onclick="javascript:history.go(-1);" accesskey="z" id="atras" class="botonb">
    </div>
    </div>';
    echo '<center>
    <form method="post" action="?modulo=lineasn&accion='.$accion.'" onsubmit="return checkSubmitedit();">';
    if($this->model->id)
        echo '<input type="hidden" name="id" value="'.$this->model->id.'" />';
    echo '<table border="0" width="50%" class="lista">
    <thead><tr><th colspan="2">Linea de Negocio</th></tr><thead>';
    echo '<tr><td>Nombre</td><td>
    <input type="text" name="nmb" size="50" value="'.$this->model->nmb.'" autofocus></td></tr>';
    echo '<tr><td>Porcentaje de Utilidad</td><td>
    <input type="number" name="utilidad" min="0" step="any" step="1" max="100" value="'.$this->model->utilidad.'" maxlength="50"/> %</td></tr>
    <tr><td>Monto Meta</td><td>
    <input type="number" name="monto" min="0" step="any" step="1" max="9999999" value="'.$this->model->monto.'" maxlength="50"/></td></tr>';
    echo '<tr><td>Abreviatura</td><td>
    <input type="text" name="alias" maxlength="3" value="'.$this->model->alias.'" autofocus></td></tr>';
    echo '<tr><td>Estatus</td><td>
    <input type="checkbox" name="estatus" '.$ckbarra.'/></td></tr>';
    echo '<tr><td>Requiere instalacion</td><td>
    <input type="checkbox" name="instalacion" '.$ckinstala.'/></td></tr>';
    echo '<tr><td>Tipo proyecto</td><td>
    <input type="checkbox" name="instalacion" '.$ckproyecto.'/></td></tr>';
    echo '<tr><td>Color para reporte</td><td>
    <input type="color" name="color" value="'.$this->model->color.'" /></td></tr>';

    echo '<tr><td colspan="2"><input type="submit" name="save" value=" " id="guardar" class="botont"></td></tr>
    </table></form></div>';
}

}
?>