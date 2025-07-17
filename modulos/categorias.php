<?php
class categorias{

function __construct(){
    $this->model = new modelcategorias($obj);
}
function index(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";
    if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

    $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus'],$_REQUEST['page']);
    $this->model->select($_GET['id']);
    $this->view = new viewlcategorias($this->model);
    $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus'],$_REQUEST['page']);
}

function insert(){
  $estatus = "A";
  if(isset($_POST['inflable'])){
    /* $sqlupd = 'UPDATE categorias SET cat_inflable = "0"';
    setq($sqlupd); */
    $inf = "1";
  } else {
    $inf = "0";
  }
  $this->model->setdata(getmax('cat_id','categorias'), $_POST['nmb'], $_POST['siglas'],$inf);
  $unieue = unique($_POST['siglas'],'cat_siglas','categorias');

  if($unieue == 1) {
    $this->model->insert();
    $error = "";
  }
  else $error = "&error=2XMC01";


  redirect('?modulo=categorias&accion=index'.$error);
}
function update(){
  $estatus = "A";
  if(isset($_POST['inflable'])){
    /* $sqlupd = 'UPDATE categorias SET cat_inflable = "0"';
    setq($sqlupd); */
    $inf = "1";
  } else {
    $inf = "0";
  }

  $this->model->setdata($_POST['id'], $_POST['nmb'], $_POST['siglas'],$inf);
  if(unique($_POST['siglas'],'cat_siglas','categorias','cat_id != "'.$this->model->id.'"') == 1) {
    $this->model->update();
    $error = "";
  }
  else $error = "&error=2XMC01";

  redirect('?modulo=categorias&accion=index'.$error);
}

  function borrar(){
    $sql = 'DELETE FROM categorias
            WHERE cat_id = "'.$_GET['id'].'"';
    setq($sql);

    redirect("?modulo=categorias&accion=index");
  }

}

class modelcategorias{
function result($nmb,$estatus,$page){ //filtro
  $bloque = 50;
  $sql = 'SELECT * FROM categorias  WHERE 1=1 ';
  if($nmb) $sql.= ' AND cat_nmb LIKE "%'.trim($nmb).'%"';
  $sql.=' ORDER BY cat_id ASC ';
  $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM categorias WHERE cat_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['cat_id'];
    $this->nmb = $row['cat_nmb'];
    $this->siglas = $row['cat_siglas'];
    $this->inflable = $row['cat_inflable'];
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
    $sql = 'INSERT INTO categorias SET
            cat_id = "'.$this->id.'",
            cat_nmb = "'.$this->nmb.'",
            cat_siglas = "'.$this->siglas.'",
            cat_inflable = "'.$this->inflable.'"';
    setq($sql);
}
function update(){
    $sql = 'UPDATE categorias SET
            cat_nmb = "'.$this->nmb.'",
            cat_siglas = "'.$this->siglas.'",
            cat_inflable = "'.$this->inflable.'"
            WHERE cat_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlcategorias {
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
function browse($nmb,$estatus,$page) {
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
    
      $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setcategoria.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
      </a>';
      /* $boton = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/guiasimagenes.php?guia=1&tipo=E" href="javascript:;">
        <i class="fa fa-plus"></i> Guias
      </a>'; */
      $boton = '<button class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Las categorias o familias de produtos, permiten realizar una división en el tratamiento y estadístico de los productos" data-trigger="click" >
        <i class="fas fa-question-circle"></i>
      </button>';
      
    $filtrar = '
              <form class="form-inline" role="form" method="post" action="?modulo=categorias&accion=index" id="filtro">
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
                <a href="?modulo=categorias&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

        toolbar($_GET['modulo'],"", $filtrar, $nuevo, $boton);

      echo '<script>
            function checerase(iduni){
              var conf = confirm("¿Deseas eliminar la categoria seleccionada?\nPresiona Aceptar para borrarla");
              if(conf == true){
                window.location.href="?modulo=categorias&accion=borrar&id=" + iduni;
              }
            }
            function verlineasneg(iduni){
              window.open("?modulo=articulos&accion=index&categoria=" + iduni);
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
                    <th width="60%">Nombre</th>
                    <th width="20%">Siglas identificadoras</th>
                    <th width="20%"></th>
                  </tr>
                </thead>'; 
    while ($row = $this->model->result->fetch_array()) {
        echo '<tr>
        <td>'.$row['cat_nmb'].'</td>
        <td>'.$row['cat_siglas'].'</td>
        <td>';
        /*
          echo '<button type="button" class="btn btn-info">
                  <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                </button> ' ; */
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" data-fancybox data-type="ajax" data-src="popup/setcategoria.php?id='.$row['cat_id'].'" href="javascript:;">
            <button type="button" class="btn btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';

        if(busca($row['cat_id'],'articulos','a_estatus = "A" AND a_categoria','COUNT(*)') == 0)
          echo '<button type="button" class="btn btn-danger" onclick="checerase('.$row['cat_id'].')">
                  <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar la linea de negocio" class="fas fa-trash"></i></button>
                ';
        else
          echo '<button type="button" class="btn bg-teal" style="background: teal" onclick="verlineasneg('.$row['cat_id'].')">
                  <i data-toggle="tooltip" data-placement="top" title data-original-title="La linea de negocio se encuentra en uso, Presiona aquí para ver los productos asignados a la '.$row['cat_nmb'].'" class="fa fa-eye" style="color: #ffffff;"></i>
                </button> ';

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