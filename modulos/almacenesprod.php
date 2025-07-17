<?php
ini_set('display_errors',1);
class almacenesprod{

function __construct(){
    $this->model = new modelalmacenesprod(isset($obj));
}
function index(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";

    $this->model->result(trim($_REQUEST['nmb']),$_REQUEST['estatus']);
    $this->model->select($_GET['id']);
    $this->view = new viewlalmacenesprod($this->model);
    $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus']);
}

function insert(){
  if(isset($_POST['vendible'])) $vendible = "1"; else $vendible = "0";
  if(isset($_POST['talmacen'])) $talmacen = "I"; else $talmacen = "M";
  $estatus = "A";

  $this->model->setdata(getmax('pa_id','pr_almacenes'), $_POST['nmb'],$vendible,$estatus,$_SESSION['emp'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['ciudad'],$_POST['estado'],$_POST['pais'],$_POST['cp'],$_POST['telefono'],$_POST['correo'], $talmacen);
  $unieue = unique($_POST['nmb'],'pa_nmb','pr_almacenes');

  if($unieue == 1) {
    $this->model->insert();
    $error = "";
  }
  else $error = "&error=2XMM01";

  if($_FILES['img']['name']){
  $id = $this->model->id;
  //$id = getmax('p_id','publicaciones');
  //Recogemos el img enviado por el formulario
  $img = $_FILES['img']['name'];
  $extension = pathinfo($img, PATHINFO_EXTENSION);
  $nuevo_nombre = 'imgalmacen-'.$id.'.'.$extension;
  //Si el img contiene algo y es diferente de vacio
  if (isset($img) && $img != "") {
    //Obtenemos algunos datos necesarios sobre el img
    $tipo = $_FILES['img']['type'];
    $tamano = $_FILES['img']['size'];
    $temp = $_FILES['img']['tmp_name'];
    
    //list($imgw, $imgh, $tipox, $atributos) = getimagesize($_FILES['img']['tmp_name']); 2000000
    //Se comprueba si el img a cargar es correcto observando su extensión y tamaño
    if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "webp") || strpos($tipo, "jpg") || strpos($tipo, "png") || strpos($tipo, "mp4")) && ($tamano < (9000000) ))) {
        die('<div><b>Error. La extensión o el tamaño de los imgs no es correcta.<br/>
        - Se permiten .gif, .jpg, .png. y de 200 kb como máximo.</b></div>');
    } else {
      //Si la img es correcta en tamaño y tipo
      //Se intenta subir al servidor
      //if (move_uploaded_file($temp, '../jdshop.mx/images/tutorialesjdshop/'.$nuevo_nombre)) {
      if (move_uploaded_file($temp, 'img/almacenesprod/'.$nuevo_nombre)) {
        //Cambiamos los permisos del img a 777 para poder modificarlo posteriormente
        chmod('img/almacenesprod/'.$nuevo_nombre, 0777);
        //Mostramos el mensaje de que se ha subido co éxito
        //die ('<div><b>Se ha subido correctamente la img.</b></div>');
        //Mostramos la img subida
        //die ('<p><img src="img/imgs/'.$img.'"></p>');
        $ruta = 'img/almacenesprod/'.$nuevo_nombre.'';
        $sql = 'UPDATE pr_almacenes SET
                pa_img = "'.$ruta.'"
                WHERE pa_id = "'.$id.'"';
        setq($sql);
      }
      else {
        //Si no se ha podido subir la img, mostramos un mensaje de error
        die('<div><b>Ocurrió algún error al subir el fichero. No pudo guardarse.</b></div>');
      }
    }
  }
  }
  
  redirect('?modulo=almacenesprod&accion=index'.$error);
}
function update(){
   $estatus = "A";
   if(isset($_POST['vendible'])) $vendible = "1"; else $vendible = "0";
   if(isset($_POST['talmacen'])) $talmacen = "I"; else $talmacen = "M";

  $this->model->setdata($_POST['id'], $_POST['nmb'],$vendible,$estatus,$_SESSION['emp'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['ciudad'],$_POST['estado'],$_POST['pais'],$_POST['cp'],$_POST['telefono'],$_POST['correo'], $talmacen);
  if(unique($_POST['nmb'],'pa_nmb','pr_almacenes','pa_empresa = "'.$_SESSION['emp'].'" AND pa_id != "'.$this->model->id.'"') == 1) {
    $this->model->update();
    $error = "";
  }
  else $error = "&error=2XMM01";

  if($_FILES['img']['name']){
    $id = $this->model->id;
    //$id = getmax('p_id','publicaciones');
    //Recogemos el img enviado por el formulario
    $img = $_FILES['img']['name'];
    $extension = pathinfo($img, PATHINFO_EXTENSION);
    $nuevo_nombre = 'imgalmacen-'.$id.'.'.$extension;
    //Si el img contiene algo y es diferente de vacio
    if (isset($img) && $img != "") {
      //Obtenemos algunos datos necesarios sobre el img
      $tipo = $_FILES['img']['type'];
      $tamano = $_FILES['img']['size'];
      $temp = $_FILES['img']['tmp_name'];
      
      //list($imgw, $imgh, $tipox, $atributos) = getimagesize($_FILES['img']['tmp_name']); 2000000
      //Se comprueba si el img a cargar es correcto observando su extensión y tamaño
      if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "webp") || strpos($tipo, "jpg") || strpos($tipo, "png") || strpos($tipo, "mp4")) && ($tamano < (9000000) ))) {
        die('<div><b>Error. La extensión o el tamaño de los imgs no es correcta.<br/>
        - Se permiten .gif, .jpg, .png. y de 200 kb como máximo.</b></div>');
      }
      else {
        //Si la img es correcta en tamaño y tipo
        //Se intenta subir al servidor
        //if (move_uploaded_file($temp, '../jdshop.mx/images/tutorialesjdshop/'.$nuevo_nombre)) {
        if (move_uploaded_file($temp, 'img/almacenesprod/'.$nuevo_nombre)) {
          //Cambiamos los permisos del img a 777 para poder modificarlo posteriormente
          chmod('img/almacenesprod/'.$nuevo_nombre, 0777);
          //Mostramos el mensaje de que se ha subido co éxito
          //die ('<div><b>Se ha subido correctamente la img.</b></div>');
          //Mostramos la img subida
          //die ('<p><img src="img/imgs/'.$img.'"></p>');
          $ruta = 'img/almacenesprod/'.$nuevo_nombre.'';
          $sql = 'UPDATE pr_almacenes SET
                  pa_img = "'.$ruta.'"
                  WHERE pa_id = "'.$id.'"';
          setq($sql);
        }
        else {
          //Si no se ha podido subir la img, mostramos un mensaje de error
          die('<div><b>Ocurrió algún error al subir el fichero. No pudo guardarse.</b></div>');
        }
      }
    }
  }

  redirect('?modulo=almacenesprod&accion=index'.$error);
}

  function borrar(){
    $sql = 'DELETE FROM pr_almacenes
            WHERE pa_id = "'.$_GET['id'].'"
            AND pa_empresa = "'.$_SESSION['emp'].'"';
    setq($sql);

    redirect("?modulo=almacenesprod&accion=index");
  }


  function borrarimg(){
    $ruta = busca($_GET['id'],'pr_almacenes','pa_id','pa_img');
    $sql = 'UPDATE pr_almacenes SET
            pa_img = NULL
            WHERE pa_id = "'.$_GET['id'].'" ';

    if(setq($sql)){
      unlink($ruta);
      redirect('?modulo=almacenesprod&accion=index');
    }
  }

}

class modelalmacenesprod{
function result($nmb){
  $sql = 'SELECT * FROM pr_almacenes';
  if($nmb) $sql.= ' AND pa_nmb LIKE "%'.trim($nmb).'%"';

  $sql.=' ORDER BY pa_id ASC';
  $this->result = setq($sql);
}
function select($id){
    $sql = 'SELECT * FROM pr_almacenes WHERE pa_id="'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['pa_id'];
    $this->nmb = $row['pa_nmb'];
    $this->estatus = $row['pa_estatus'];
}

function setdata($id,$nmb,$vendible,$estatus,$empresa,$calle,$nume,$numi,$colonia,$ciudad,$estado,$pais,$cp,$telefono,$correo,$talmacen){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->estatus = clearvmayus($estatus);
    $this->vendible = clearvmayus($vendible);
    $this->empresa = clearvmayus($empresa);
    //$this->direccion = clearvmayus($direccion);
    $this->calle = clearvmayus($calle);
    $this->nume = clearvmayus($nume);
    $this->numi = clearvmayus($numi);
    $this->colonia = clearvmayus($colonia);
    $this->ciudad = clearvmayus($ciudad);
    $this->estado = clearvmayus($estado);
    $this->pais = clearvmayus($pais);
    $this->cp = clearvmayus($cp);
    $this->telefono = $telefono;
    $this->correo = $correo;
    $this->talmacen = $talmacen;
}
function insert(){
    $sql = 'INSERT INTO pr_almacenes SET
            pa_id = "'.$this->id.'",
            pa_nmb = "'.$this->nmb.'",
            pa_vendible = "'.$this->vendible.'",
            pa_empresa = "'.$this->empresa.'",
            pa_calle = "'.$this->calle.'",
            pa_nume = "'.$this->nume.'",
            pa_numi = "'.$this->numi.'",
            pa_colonia = "'.$this->colonia.'",
            pa_ciudad = "'.$this->ciudad.'",
            pa_estado = "'.$this->estado.'",
            pa_pais = "'.$this->pais.'",
            pa_cp = "'.$this->cp.'",
            pa_telefono = "'.$this->telefono.'",
            pa_correo = "'.$this->correo.'",
            pa_tipoa = "'.$this->talmacen.'"
            ';
    setq($sql);
}
function update(){
    $sql = 'UPDATE pr_almacenes SET
            pa_nmb = "'.$this->nmb.'",
            pa_vendible = "'.$this->vendible.'",
            pa_empresa = "'.$this->empresa.'",
            pa_calle = "'.$this->calle.'",
            pa_nume = "'.$this->nume.'",
            pa_numi = "'.$this->numi.'",
            pa_colonia = "'.$this->colonia.'",
            pa_ciudad = "'.$this->ciudad.'",
            pa_estado = "'.$this->estado.'",
            pa_pais = "'.$this->pais.'",
            pa_cp = "'.$this->cp.'",
            pa_telefono = "'.$this->telefono.'",
            pa_correo = "'.$this->correo.'",
            pa_tipoa = "'.$this->talmacen.'"
            WHERE pa_id = "'.$this->id.'"';
    setq($sql);
}

}

class viewlalmacenesprod {
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
    /* echo '<div class="row page-title-actions">'; */
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
    $filtro = '
    <form class="form-inline" role="form" method="post" action="?modulo=almacenesprod&accion=index" id="filtro">
    <div class="mb-5">
      <label for="">Filtrar por nombre:</label>
      <input type="text" class="form-control" id="pref-search" name="nmb" value="'.$nmb.'" placeholder="Buscar por nombre">
    </div><!-- form group [search] -->

    <div class="mb-5">
      <label for="">Acciones:</label><br>
      <button type="button" onclick="mandar(0)" class="btn btn-info">
      <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
    </button>
    <!-- <a href="?modulo=almacenesprod&accion=index"><button type="button" class="btn btn-sm btn-warning"> -->
    <button type="button" onclick="mandar(1)" class="btn btn-warning">
      <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
    </button><!-- </a> -->
    </div>
    </form>';

    $nuevo = '
    <a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setalmacenprod.php" href="javascript:;">
      <button type="button" class="btn btn-sm btn-primary">
        <i class="fa fa-plus"></i> Nuevo
      </button>
    </a>';
      toolbar($_GET['modulo'], "",$filtro, $nuevo);
      ?>

      <!-- </div></div> -->

    <script>
    function checerase(iduni){
      Swal.fire({
        title: 'Atención',
        html: "¿Deseas eliminar la marca seleccionada?<br>Presiona Aceptar para borrarla",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Estoy seguro'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href="?modulo=almacenesprod&accion=borrar&id=" + iduni;
        }
      })
    }

    function verlineasneg(iduni){
      window.open("?modulo=reportes&accion=existencias&almacen=" + iduni);
    }
    </script>
    <?php


    echo '
    <div class="card mt-3">
    <div class="card-body">
    <div class=" col-12 table-responsive text-medium">
    <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
    <thead class="bg-light-blue bg-darken-2 ">
      <tr>
        <th style="width: 70px;">Nombre</th>
        <th>Vendible</th>
        <th>Dirección</th>
        <th>Telefono</th>
        <th>Correo</th>
        <th>Imagen</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
    </tbody>';
      echo '
    </table>
    </div></div>';

    echo '
    <script>
    function mandar(id){
      var search = document.getElementById("pref-search").value;
      if(id == 1){
        search = "";
        document.getElementById("pref-search").value = "";
      }

      //var table = $("#myTable").DataTable();
      $("#myTable").DataTable().clear().draw();
      $("#myTable").DataTable().destroy();

      
      var windowHeight = $(window).height();

      $("#myTable").DataTable( {
        paging: true,
        scrollY: windowHeight * 0.5,
        processing: true,
        serverside: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatablealmacenesprod.php",
          type: "POST",
          datatype: "json",
          data:{ "search": search
          },
        },
        pageLength: "50",
        responsivePriority: 1,
      });
    }';

      echo '
      $(document).ready(function () {
        var windowHeight = $(window).height();
        scrollY: windowHeight * 0.5,
        $("#myTable").DataTable( {
          paging: true,
          scrollY: windowHeight * 0.5,
          processing: true,
          serverside: true,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatablealmacenesprod.php",
            type: "POST",
            datatype: "json",
          },
          pageLength: "50",
          responsivePriority: 1,
        });
      });
    </script>
      ';
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