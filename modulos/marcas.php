<?php
class marcas{

function __construct(){
    $this->model = new modelmarcas($obj);
}
function index(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";
    if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

    $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus'],$_REQUEST['page']);
    $this->model->select($_GET['id']);
    $this->view = new viewlmarcas($this->model);
    $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus'],$_REQUEST['page']);
}

function insert(){

  $estatus = "A";

  $this->model->setdata(getmax('m_id','marcas'), $_POST['nmb'],$estatus,$_SESSION['emp']);
  $unieue = unique($_POST['nmb'],'m_nmb','marcas','m_empresa = "'.$_SESSION['emp'].'"');
  //die($unieue);
  if($unieue == 1) {
    $this->model->insert();
    $error = "";
  }
  else $error = "&error=2XMM01";


  redirect('?modulo=marcas&accion=index'.$error);
}
function update(){
   $estatus = "A";

  $this->model->setdata($_POST['id'], $_POST['nmb'],$estatus,$_SESSION['emp']);
  if(unique($_POST['nmb'],'m_nmb','marcas','m_empresa != "'.$_SESSION['emp'].'" AND m_id = "'.$this->model->id.'"') == 1) {
    $this->model->update();
    $error = "";
  }
  else $error = "&error=2XMM01";

  redirect('?modulo=marcas&accion=index'.$error);
}

  function borrar(){
    $sql = 'DELETE FROM marcas
            WHERE m_id = "'.$_GET['id'].'"
            AND m_empresa = "'.$_SESSION['emp'].'"';
    setq($sql);

    redirect("?modulo=marcas&accion=index");
  }

}

class modelmarcas{
function result($nmb,$estatus,$page){ //filtro
  $bloque = 50;
  $sql = 'SELECT * FROM marcas WHERE m_empresa = "'.$_SESSION['emp'].'" ';
  if($nmb) $sql.= ' AND m_nmb LIKE "%'.trim($nmb).'%"';
  $sql.=' ORDER BY m_id ASC';
  $result = setq($sql);
  if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
  $this->result = setq($sql);
  $this->resultt = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM marcas WHERE m_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['m_id'];
    $this->nmb = $row['m_nmb'];
    $this->estatus = $row['m_estatus'];
}

function setdata($id,$nmb,$estatus,$empresa){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","�","*","+","~","^","[","�","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->estatus = clearvmayus($estatus);
    $this->empresa = clearvmayus($empresa);
}
function insert(){
    $sql = 'INSERT INTO marcas SET
            m_id = "'.$this->id.'",
            m_nmb = "'.$this->nmb.'",
            m_empresa = "'.$this->empresa.'"';
    setq($sql);
}
function update(){
    $sql = 'UPDATE marcas SET
            m_nmb = "'.$this->nmb.'",
            m_empresa = "'.$this->empresa.'"
            WHERE m_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlmarcas {
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
   //Secci�n: E1 Encabezado - Botones de acci�n
    echo '<div class="row page-title-actions">';
    if($estatus == "A") $chest = "checked"; else $chest = "";

    $esta = "";
    $esti = "";
    $estt = "";
    if(!isset($estatus) || $estatus == "A") $esta = "selected";
    elseif($estatus == "I") $esti = "selected";
    else $estt = "selected";

    //Secci�n: E2 Encabezado - Filtros
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

    <div class="mb-5 form-inline col-md-12">
      <a id="nuevo" class="btn btn-primary" data-fancybox data-type="ajax" data-src="popup/setmarca.php" href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo 
      </a>
      <button id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
      <button class="btn btn-secondary" data-toggle="tooltip" data-placement="bottom" data-original-title="Las marcas te permitirán organizar de mejor manera tu almacen y determinar estadísticos." data-trigger="click" >
        <i class="icon-question-circle"></i>
      </button>
    </div>
    <div id="filter-panel" class="col-md-12 collapse filter-panel mb-1">
          <div class="panel panel-default">
            <div class="panel-body">
              <form class="form-inline" role="form" method="post" action="?modulo=marcas&accion=index" id="filtro">
              <input name="page" id="page" value="<?php echo $page; ?>" hidden> 
                <div class="mb-5">
                  <label for="">Filtrar por nombre:</label>
                  <input onkeyup="ceropage();" type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
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
                <button type="button" onclick="mandar(0);" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=marcas&accion=index"><button type="button" class="btn btn-warning">
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
              var conf = confirm("¿Deseas eliminar la marca seleccionada?\nPresiona Aceptar para borrarla");
              if(conf == true){
                window.location.href="?modulo=marcas&accion=borrar&id=" + iduni;
              }
            }
            function verlineasneg(iduni){
              window.open("?modulo=articulos&accion=index&marca=" + iduni);
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
                    <th></th>
                  </tr>
                </thead>';  /*
    if($this->model->id) $accion = 'update'; else $accion = 'insert';


    echo '<form method="post" action="?modulo=marcas&accion='.$accion.'">
          <input type="hidden" value="'.$this->model->id.'" name="id" />
            <tr>
              <td><input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre de la marca" name="nmb" value="'.$this->model->nmb.'" required="required" ></td>
              <td><button type="submit" class="btn btn-primary" ><i class="fa fa-save"></i></td>
          </tr></form>'; */
    while ($row = $this->model->result->fetch_array()) {
        echo '<tr>
        <td>'.$row['m_nmb'].'</td>
        <td>';
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar nombre de la marca"  data-fancybox data-type="ajax" data-src="popup/setmarca.php?id='.$row['m_id'].'" href="javascript:;">
                  <button type="button" class="btn btn-info">
                    <i class="icon-edit2"></i>
                  </button> 
                </a>' ;
        if((busca($row['m_id'],'articulos','a_estatus = "A" AND a_marca','COUNT(*)')) == 0)
          echo '<button type="button" class="btn btn-danger" onclick="checerase('.$row['m_id'].')">
                  <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar marca" class="icon-eraser"></i></button>
                ';
        else
          echo '<button type="button" class="btn bg-teal" onclick="verlineasneg('.$row['m_id'].')">
                  <i data-toggle="tooltip" data-placement="top" title data-original-title="La marca se encuentra en uso, Presiona aquí para ver los productos asignados a '.$row['m_nmb'].'" class="fa fa-eye"></i>
                </button> ';

        echo '</td>
        ';
    }
    echo '</table>';

    //filtro

    if($_REQUEST['page'] == 0){
      $hidden = 'style="pointer-events: none;
      background: #70707026;
      color: black;"';
    }else $hidden = "";

    echo '<div class="col-md-12 text-xs-center">
    <div class="mb-3">
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <li class="page-item">
            <a class="page-link" onclick="mandar('.($_REQUEST['page']-1).')" aria-label="Previous" '.$hidden.'>
              <span aria-hidden="true">&laquo; Ant</span>
              <span class="sr-only">Anterior</span>
            </a>
          </li>';

          echo '
          <script>
            function mandar(id){
              document.getElementById("page").value = id;
              document.getElementById("filtro").submit();
            }
          </script>';

          
    $bloque = 50;
    $sqlpg = 'SELECT COUNT(*) FROM marcas WHERE m_empresa = "'.$_SESSION['emp'].'" ';
    if($nmb) $sqlpg.= ' AND m_nmb LIKE "%'.trim($nmb).'%"';
    $resultpg = setq($sqlpg);
    list($numg) =  $resultpg->fetch_array();
    $pageres = ceil($numg/$bloque);
    //die($numg);

      if($pageres>=10){
        if($_REQUEST['page'] == 0) { $min = 1; $nombre = "Inicio";}
        else {$min = $_REQUEST['page']-1; $nombre = "Inicio...";}
        if($min <= 1) $min = 1;

        if($_REQUEST['page'] == ($pageres-1)) $max = ($pageres-1);
        else $max = $_REQUEST['page']+3;
        if($max >= ($pageres-1)) $max = ($pageres-1);

        if($_REQUEST['page'] == 0) $active = "active";
        else $active = "";
        echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar(0)">'.$nombre.'</a></li>';
      }elseif($pageres<=9){
        $max=$pageres;
        $min=0;
      }
      //for($i=0;$i<$pageres;$i++){
      for($i=$min;$i<$max;$i++){
        if($_REQUEST['page'] == $i) $active = "active";
        else $active = "";
        if($i == 0) $nombre = "Inicio";
        else $nombre = $i;
        echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.$i.')">'.$nombre.'</a></li>';
      }
      if($pageres<=9){
      }else{
        if($_REQUEST['page'] == ($pageres-5)) $nombre = ($pageres-2);
        else $nombre = '... '.($pageres-2);
        if($_REQUEST['page'] == $i) $active = "active";
        else $active = "";
        if($_REQUEST['page'] >= ($pageres-4)) echo '';
        else echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.($pageres-2).')">'.$nombre.'</a></li>';
        echo '<li class="page-item '.$active.'"><a class="page-link" onclick="mandar('.($pageres-1).')">'.($pageres-1).'</a></li>';
      }
      if($_REQUEST['page'] == ($pageres-1) || $pageres <= 1) $hidden2 = 'style="pointer-events: none;
      background: #70707026;
      color: black;"';
      else $hidden2 = '';
      echo '<li class="page-item">
        <a class="page-link" onclick="mandar('.($_REQUEST['page']+1).')" aria-label="Next" '.$hidden2.'>
          <span aria-hidden="true">Sig &raquo;</span>
          <span class="sr-only">Siguiente</span>
        </a>
      </li>
    </ul>
  </nav>
  </div>
  </div>'; 

    
            
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