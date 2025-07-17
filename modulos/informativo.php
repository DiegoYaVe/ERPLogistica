<?php
$GLOBALS['menu']="E-Commerce";
class informativo{
  var $model;
  var $view;
function informativo(){
  include('dbw.php');
  $this->model = new modelinformativo(isset($obj));
}
function index(){
  $this->model->result();
  $this->view = new viewinformativo($this->model);
  $this->view->browse();
}
function indexti(){
  $this->model->resultti();
  $this->view = new viewinformativo($this->model);
  $this->view->browsetit();
}

function edit(){
  if(!isset($_GET['id']))
    $_GET['id'] = NULL;
  $this->model->select($_GET['id']);
  $this->view = new viewinformativo($this->model);
  $this->view->edit();
}
function insert(){
  if(busca(trim($_POST['nmb']),'cat_info','ci_nmb','COUNT(*)') > 0)
    die(alert_back('Ya existe una categoria con el mismo nombre',true));
  $this->model->setData($_POST['id'],$_POST['menu'],$_POST['nmb'],$_POST['estatus']);
  $this->model->insert();
  header('Location: ?modulo=informativo&accion=show&id='.$this->model->id);
}
function update(){
  if(busca(trim($_POST['nmb']),'cat_info','ci_id != "'.$_POST['id'].'" AND ci_nmb','COUNT(*)') > 0)
    die(alert_back('Ya existe una categoria con el mismo nombre',true));
  $this->model->setData($_POST['id'],$_POST['menu'],$_POST['nmb'],$_POST['estatus']);
  $this->model->update();
  header('Location: ?modulo=informativo&accion=show&id='.$this->model->id);
}
function show(){
  if(!isset($_GET['id']))
    $_GET['id'] = NULL;
  $this->model->select($_GET['id']);
  $this->model->resultinfo($_GET['id']);
  $this->view = new viewinformativo($this->model);
  $this->view->show();
}

function editinfo(){
  if(!isset($_GET['id']))
    $_GET['id'] = NULL;
  $this->model->selectinfo($_GET['id']);
  $this->view = new viewinformativo($this->model);
  $this->view->editinfo($_GET['cat']);
}
function insertinfo(){
  $this->model->setDatainfo($_POST['id'],$_POST['categoria'],$_POST['titulo'],$_POST['descripcion'],$_POST['estatus']);
  $this->model->insertinfo();
  header('Location: ?modulo=informativo&accion=show&id='.$this->model->categoria);
}
function updateinfo(){
  $this->model->setDatainfo($_POST['id'],$_POST['categoria'],$_POST['titulo'],$_POST['descripcion'],$_POST['estatus']);
  $this->model->updateinfo();
  header('Location: ?modulo=informativo&accion=show&id='.$this->model->categoria);
}
function activar(){
  $this->model->activar($_GET['id']);
  header('Location: ?modulo=informativo&accion=index');
}
function desactivar(){
  $this->model->desactivar($_GET['id']);
  header('Location: ?modulo=informativo&accion=index');
}
function activarinfo(){
  $this->model->activarinfo($_GET['id']);
  header('Location: ?modulo=informativo&accion=show&id='.$_GET['cat']);
}
function desactivarinfo(){
  $this->model->desactivarinfo($_GET['id']);
  header('Location: ?modulo=informativo&accion=show&id='.$_GET['cat']);
}
function editti(){
  echo ' <form action="?modulo=informativo&accion=updateti&id='.$_GET['id'].'" method="post">
  <table class="lista" widht="100%">
  <thead><th colspan="2">TITULO</th></thead>
  <tbody>
  <tr><th>Nombre</th><td><input type="text" name="nmbinfo" value="'.busca($_GET['id'],'titulos_info','ti_id','ti_nmb').'" required/></td></tr>
  <tr><td colspan="3"><input type="submit" value="" id="guardar" class="botont"/></td></tr>
  </tbody></table></form>
  ';
}

function updateti(){
  mb_internal_encoding("UTF-8");
  $simbol = array('"',"'","#","$","%", "&","/","=","*","+","~","^","[","°","|");
  $nmb = str_replace($simbol,'',trim($_POST['nmbinfo']));
  $sql = 'UPDATE titulos_info SET ti_nmb = "'.$nmb.'" WHERE ti_id = "'.$_GET['id'].'"';
  mysql_query($sql) or die($sql);
  header('Location: ?modulo=informativo&accion=indexti');
}

function slider(){
  $this->model->resultsl();
  $this->view = new viewinformativo($this->model);
  $this->view->showslider();
}
function insertimagen(){
  $this->model->insertimagen();
  header('Location: ?modulo=informativo&accion=slider');
}
function deleteimagen(){
  $this->model->deleteimagen($_GET['id']);
  header('Location: ?modulo=informativo&accion=slider');
}
function vieworder(){
?>
<script>
function checkorder(id,ordena){
  orden = $("#"+id).val();
  c = 0;
  for (i=0;i<document.ordersl.elements.length;i++){
    if(document.ordersl.elements[i].type == "number")
      if(document.ordersl.elements[i].value == orden)
        c++;
  }
  if(c > 1){
    alert("La Posicion "+orden+" del Slider ya esta Ocupada");
    $("#"+id).val(ordena);
  }
}
</script>
<?php
  $sql = 'SELECT * FROM imgslider ORDER BY i_orden';
  $result = mysql_query($sql) or die($sql);
  echo '<form name="ordersl" action="?modulo=informativo&accion=setorder" method="post">
        <table class="lista">';
  $i=0;
      echo '<tr>';
  while($row = mysql_fetch_array($result)){
    $i++;
    echo '  <td><table class="lista">
            <tr><td><img src="https://jdshop.mx/slider/'.$row['i_nmb'].'.'.$row['i_ext'].'" alt="" width="100pt" /></td>
            <th><input type="number" min="1" max="10" name="order'.$row['i_id'].'" id="order'.$row['i_id'].'" value="'.$row['i_orden'].'" onchange="checkorder(\'order'.$row['i_id'].'\','.$row['i_orden'].');"/></th></tr>
            <tr><td>Link</td><td><input type="text" name="link'.$row['i_id'].'" value="'.$row['i_link'].'" /></td></tr>
            </table></td>';

    if($i%3 == 0)
        echo '</tr><tr>';

  }
  echo '<tr><td colspan="6"><input type="submit" value="" id="actualizar" class="botont" /></td></tr>
        </table></form>';
}
function setorder(){
  $sql = 'SELECT * FROM imgslider ORDER BY i_orden';
  $result = mysql_query($sql) or die($sql);
  while($row = mysql_fetch_array($result)){
    $i++;
    $sqlo = 'UPDATE imgslider SET
    i_orden = "'.$_POST['order'.$row['i_id']].'",
    i_link = "'.$_POST['link'.$row['i_id']].'"
    WHERE i_id = "'.$row['i_id'].'"';
    mysql_query($sqlo) or die($sqlo);
  }

  header('Location: ?modulo=informativo&accion=slider');
}
function vozc(){
  $this->model->resultvoz();
  $this->view = new viewinformativo($this->model);
  $this->view->browsevoz();
}
function editvoz(){
  if(!isset($_GET['id']))
    $_GET['id'] = NULL;
  $this->model->selectvoz($_GET['id']);
  $this->view = new viewinformativo($this->model);
  $this->view->editvoz();
}
function insertvoz(){
  $this->model->setdatavoz($_POST['id'],$_POST['descripcion'],$_POST['nmb'],$_POST['mostrar'],$_POST['orden']);
  $this->model->insertvoz();
  header('Location: ?modulo=informativo&accion=vozc');
}
function updatevoz(){
  $this->model->setdatavoz($_POST['id'],$_POST['descripcion'],$_POST['nmb'],$_POST['mostrar'],$_POST['orden']);
  $this->model->updatevoz();
  header('Location: ?modulo=informativo&accion=vozc');
}
function editimgvoz(){
  echo '<form enctype="multipart/form-data" method="post" action="?modulo=informativo&accion=adjimgvoz">
  <table class="lista" width="100%">
  <tr>
  <th>Imagen</th>
  <td colspan="3"><input type="file" name="archivo" required /></td>
  </tr>
  <tr>
  <td colspan="4"><input type="submit" id="adjuntar" class="botont" value=" " /></td>
  </tr>
  </table>
  </form>
  ';
}
function adjimgvoz(){
  $ext = pathinfo($_FILES ["archivo"] ["name"], PATHINFO_EXTENSION);
  if($ext != "jpg" && $ext != "png" && $ext != "JPG" && $ext != "PNG")
    die(alert_back("Error: El formato de la imagen es invalido ".$ext,true));

  /*if (!is_dir('departamentol')) {
    @mkdir('departamentol', 0777);
  } */

  if(!move_uploaded_file($_FILES ["archivo"] ["tmp_name"],'images/voz.'.$ext)){

    die(alert_back("Error: El archivo no ha sido copiado",true));
  }

  $host = 'purpuraylinofino.mx';
  $user = 'purpuray_purpura';
  $db = 'purpuray_purpura';
  $pass = 'Wptyeall.0';
  $host = busca(1,'configuracion','c_consecutivo','c_urlr');
  $user = busca(1,'configuracion','c_consecutivo','c_userr');
  $db = busca(1,'configuracion','c_consecutivo','c_dbr');
  $pass = busca(1,'configuracion','c_consecutivo','c_passr');

  $mysqli = mysqli_init();
  $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
  $mysqli->real_connect($host,$user,$pass,$db);
  if ($mysqli->ping()){
      $ftp_server = $host;
      $ftp_user_name = 'purpuraftp@purpuraylinofino.mx';
      $ftp_user_pass = $pass;
      $remote_file = 'voz.'.$ext;
      $file = 'images/voz.'.$ext;
      // set up basic connection
      if(file_exists($file)){
        $cid = ftp_connect($ftp_server);
        // login with username and password
        $resultado = ftp_login($cid,$ftp_user_name, $ftp_user_pass);

        if ((!$cid) || (!$resultado)) {
          echo "Fallo en la conexión";
          die();
        }
        else {
          //echo "Conectado.";
        }

        ftp_pasv ($cid, true) ;
        //echo "<br> Cambio a modo pasivo<br />";
        ftp_chdir($cid, "images/");
        //echo "Cambiado al directorio necesario";
        // upload a file
        if (ftp_put($cid, $remote_file, $file, FTP_BINARY)) {
          //echo "successfully uploaded $file\n";
        }
        else {
          echo "There was a problem while uploading $file\n";
          die();
        }
        // close the connection
        ftp_close($cid);
      }
  }
  $mysqli->close();
  //include("db.php");

  header('Location: ?modulo=informativo&accion=vozc');
}

}

class modelinformativo{
function result(){
  $sql='SELECT * FROM cat_info';
  $this->result = mysql_query($sql) or die($sql);
}
function resultti(){
  $sql='SELECT * FROM titulos_info';
  $this->result = mysql_query($sql) or die($sql);
}
function resultsl(){
  $sql='SELECT * FROM imgslider';
  $this->result = mysql_query($sql) or die($sql);
}
function select($id){
  $sql = 'SELECT * FROM cat_info WHERE ci_id = "'.$id.'"';
  $rs = mysql_query($sql) or die($sql);
  $rw = mysql_fetch_array($rs);
  $this->id = $rw['ci_id'];
  $this->menu = $rw['ci_menu'];
  $this->nmb = $rw['ci_nmb'];
  $this->estatus = $rw['ci_estatus'];
}
function setData($id,$menu,$nmb,$estatus){
  mb_internal_encoding("UTF-8");
  $simbol = array('"',"'");
  $this->id = $id;
  $this->menu = $menu;
  $this->nmb = str_replace($simbol,'', trim($nmb));
  $this->estatus = "I";
  if($estatus)
    $this->estatus = "A";
}
function insert(){
  $sql = 'SELECT MAX(ci_id) FROM cat_info';
  $rs = mysql_query($sql) or die($sql);
  list($this->id) = mysql_fetch_array($rs);
  $this->id++;
  $sql='INSERT INTO cat_info SET
  ci_id = "'.$this->id.'",
  ci_menu = "'.$this->menu.'",
  ci_nmb = "'.$this->nmb.'",
  ci_estatus = "'.$this->estatus.'"
  ';
  mysql_query($sql) or die($sql);
}
function update(){
  $sql = 'UPDATE cat_info SET
  ci_menu = "'.$this->menu.'",
  ci_nmb = "'.$this->nmb.'",
  ci_estatus = "'.$this->estatus.'",
  ci_updated = "0"
  WHERE ci_id = "'.$this->id.'"
  ';
   mysql_query($sql) or die($sql);
}
function resultinfo($id){
  $sql = 'SELECT * FROM txt_info WHERE ti_categoria = "'.$id.'"';
  $this->result = mysql_query($sql) or die($sql);
}
function selectinfo($id){
  $sql='SELECT * FROM txt_info WHERE ti_id = "'.$id.'"';
  $rs = mysql_query($sql) or die($sql);
  $rw = mysql_fetch_array($rs);
  $this->id = $rw['ti_id'];
  $this->categoria = $rw['ti_categoria'];
  $this->titulo = $rw['ti_titulo'];
  $this->descripcion = $rw['ti_descripcion'];
  $this->estatus = $rw['ti_estatus'];
}
function setDatainfo($id,$categoria,$titulo,$descripcion,$estatus){
  mb_internal_encoding("UTF-8");
  $simbol = array('"',"'");
  $this->id = $id;
  $this->categoria = $categoria;
//  $this->titulo = str_replace($simbol,'', mb_strtoupper(trim($titulo)));
//  $this->descripcion = str_replace($simbol,'', mb_strtoupper(trim($descripcion)));
  $this->titulo = str_replace($simbol,'', trim($titulo));
  $this->descripcion = str_replace($simbol,'', trim($descripcion));
  $this->estatus = "I";
  if($estatus)
    $this->estatus = "A";
}
function insertinfo(){
  $sql = 'SELECT MAX(ti_id) FROM txt_info';
  $rs = mysql_query($sql) or die($sql);
  list($this->id) = mysql_fetch_array($rs);
  $this->id++;
  $sql = 'INSERT INTO txt_info SET
  ti_id = "'.$this->id.'",
  ti_categoria = "'.$this->categoria.'",
  ti_titulo = "'.$this->titulo.'",
  ti_descripcion = "'.$this->descripcion.'",
  ti_estatus = "'.$this->estatus.'"
  ';
  mysql_query($sql) or die($sql);
}
function updateinfo($id,$estatus){
  $sql = 'UPDATE txt_info SET
  ti_titulo = "'.$this->titulo.'",
  ti_descripcion = "'.$this->descripcion.'",
  ti_estatus = "'.$this->estatus.'",
  ti_updated = "0"
  WHERE ti_id = "'.$this->id.'"
  ';
   mysql_query($sql) or die($sql);
}
function activar($id){
  $sql = 'UPDATE cat_info SET ci_estatus = "A",ci_updated = "0" WHERE ci_id = "'.$id.'"';
  mysql_query($sql) or die($sql);
}
function desactivar($id){
  $sql = 'UPDATE cat_info SET ci_estatus = "I",ci_updated = "0" WHERE ci_id = "'.$id.'"';
  mysql_query($sql) or die($sql);
}
function activarinfo($id){
  $sql = 'UPDATE txt_info SET ti_estatus = "A",ti_updated = "0" WHERE ti_id = "'.$id.'"';
  mysql_query($sql) or die($sql);
}
function desactivarinfo($id){
  $sql = 'UPDATE txt_info SET ti_estatus = "I",ti_updated = "0" WHERE ti_id = "'.$id.'"';
  mysql_query($sql) or die($sql);
}

function insertimagen(){
  $carpetaDestino = '../jdshop.mx/slider/';
    # si hay algun archivo que subir
    if($_FILES["archivo"]["name"][0]){
        # recorremos todos los arhivos que se han subido
        for($i=0;$i<count($_FILES["archivo"]["name"]);$i++){
          if( $_FILES["archivo"]["size"][$i] <= 2367575 ) {
            $imagen = getimagesize($_FILES["archivo"]["tmp_name"][$i]);
            $ancho = $imagen[0];
            $alto = $imagen[1];
            if($ancho > $alto AND $ancho <= 1200){
            if($_FILES["archivo"]["type"][$i]=="image/jpeg" || $_FILES["archivo"]["type"][$i]=="image/jpg" || $_FILES["archivo"]["type"][$i]=="image/gif" || $_FILES["archivo"]["type"][$i]=="image/png"){
                # si exsite la carpeta o se ha creado
                if(file_exists($carpetaDestino) || @mkdir($carpetaDestino)){
                  $ext = pathinfo($_FILES ["archivo"] ["name"][$i], PATHINFO_EXTENSION);
                    $j=0;
                    $origen = $_FILES["archivo"]["tmp_name"][$i];
                    $sql= 'SELECT MAX(i_id) FROM imgslider';
                    $result = mysql_query($sql) or die($sql);;
                  list($j) = mysql_fetch_array($result);
                    $j = intval($j);
                  $j++;
                    $destino = $carpetaDestino.$j.'.'.$ext;
                    $msje = $j.'.'.$ext;
                    $nmb = $j;
                    # movemos el archivo
                    if(@move_uploaded_file($origen, $destino)){
                        //echo "<br>".$_FILES["archivo"]["name"][$i]." movido correctamente<br>";
                        $sql= 'SELECT MAX(i_id) FROM imgslider';
                        $result = mysql_query($sql) or die($sql);;
                      list($idi) = mysql_fetch_array($result);
                      $idi++;

                        $sql='
                        INSERT INTO imgslider SET
                        i_id = "'.$idi.'",
                        i_nmb = "'.$nmb.'",
                        i_ext = "'.$ext.'",
                        i_orden = "'.$idi.'"';
                        mysql_query($sql) or die($sql);
                    }
                    else{
                        echo "<br>No se ha podido mover el archivo: ".$_FILES["archivo"]["name"][$i];
                    }
                }
                else{
                    echo "<br>No se ha podido crear la carpeta: up/".$user;
                }
            }
            else{
                echo "<br>".$_FILES["archivo"]["name"][$i]." - NO es imagen jpg";
            }
          }
          else{
             die(alert_back('El Ancho de la imagen debe ser menor a 1200px y el alto no puede superar el ancho',true));
          }
        }
        else {
          die(alert_back('Subir una imagen menor a 2MB',true));
          echo 'el archivo es demasiado grande';
        }
        }
    }else{
        echo "<br>No se ha subido ninguna imagen";
    }
}

function deleteimagen($id){
  $dirjd = '../jdshop.mx/';
  $sql = 'SELECT COUNT(*) FROM imgslider';
  $result = mysql_query($sql) or die($sql);
  list($nimg) = mysql_fetch_array($result);
  if($nimg == 1)
    die(alert_back("Error al elminiar imagen el slider debe tener al menos una imagen",true));

  $sql = 'SELECT i_nmb, i_ext FROM imgslider WHERE i_id = "'.$id.'"';
  $rs = mysql_query($sql) or die($sql);
  list($nombre, $ext) = mysql_fetch_array($rs);

  $sql= 'DELETE FROM imgslider WHERE i_id = "'.$id.'"';
  if(unlink($dirjd.'slider/'.$nombre.'.'.$ext)){
    mysql_query($sql) or die(mysql_error() .'<br>');
  }
  elseif(!file_exists($dirjd.'slider/'.$nombre.'.'.$ext)){
    mysql_query($sql) or die(mysql_error() .'<br>');
  }
  else
    die('No se puede eliminar');
}

function resultvoz(){
  $sql='SELECT * FROM voz_clientes ORDER BY vc_id ASC';
  $this->result = mysql_query($sql) or die($sql);
}
function selectvoz($id){
  $sql = 'SELECT * FROM voz_clientes WHERE vc_id = "'.$id.'"';
  $rs = mysql_query($sql) or die($sql);
  $rw = mysql_fetch_array($rs);
  $this->id = $rw['vc_id'];
  $this->descripcion = $rw['vc_descripcion'];
  $this->nmb = $rw['vc_nmb'];
  $this->mostrar = $rw['vc_mostrar'];
  $this->orden = $rw['vc_orden'];
  $this->cliente = $rw['vc_cliente'];
}
function setdatavoz($id,$descripcion,$nmb,$mostrar,$orden){
  mb_internal_encoding("UTF-8");
  $simbol = array("<p>","</p>");
  $this->id = $id;
  $this->descripcion = str_replace($simbol,'', trim($descripcion));
  $this->nmb = str_replace($simbol,'',trim($nmb));
  $this->mostrar = "0";
  if($mostrar)
    $this->mostrar = "1";
  $this->orden = $orden;

  $this->cliente = busca($this->nmb,'clientes','CONCAT(c_nmb," ",c_apellidos)','c_id');

}
function insertvoz(){
  $sql = 'SELECT MAX(vc_id) FROM voz_clientes';
  $rs = mysql_query($sql) or die($sql);
  list($this->id) = mysql_fetch_array($rs);
  $this->id++;
  $sql='INSERT INTO voz_clientes SET
  vc_id = "'.$this->id.'",
  vc_descripcion = "'.$this->descripcion.'",
  vc_nmb = "'.$this->nmb.'",
  vc_mostrar = "'.$this->mostrar.'",
  vc_cliente = "'.$this->cliente.'",
  vc_orden = "'.$this->orden.'"
  ';
  mysql_query($sql) or die($sql);
}
function updatevoz(){
  $sql = 'UPDATE voz_clientes SET
  vc_descripcion = "'.$this->descripcion.'",
  vc_nmb = "'.$this->nmb.'",
  vc_mostrar = "'.$this->mostrar.'",
  vc_cliente = "'.$this->cliente.'",
  vc_orden = "'.$this->orden.'"
  WHERE vc_id = "'.$this->id.'"
  ';
   mysql_query($sql) or die($sql);
}

}

class viewinformativo{
  var $model;
function viewinformativo($model){
  include('db.php');
  include ('header.php');
  include('dbw.php');
  echo '<div class="content-wrapper">
  <section class="content">
  <div class="div" id="titulo01">
  <div class="div2" id="name">'.strtoupper(busca($_GET['modulo'],'modulosd','md_accion="'.$_GET['accion'].'" AND md_modulo', 'md_descripcion')).'</div>
  ';
  $this->model=$model;
}
function browsetit(){
  echo '
  <div class="div2" id="bboton01">
  </div></div>';

  echo '
  <table align="center" class="lista" width=50%>
  <thead>
  <tr>
  <th colspan="4"><b>Titulos Informativos</b></th>
  </tr>
  </thead>
  <tbody>
  <tr>
  <th>Id</th>
  <th>Nombre</th>
  <th></th>
  </tr>';
  while($rw = mysql_fetch_array($this->model->result)){
    echo '<tr>';
    echo '<th>'.$rw['ti_id'].'</th>';
    echo '<td>'.$rw['ti_nmb'].'</td>';
    echo '<td width="9%"><center><b><a id="popup" href="?modulo=informativo&accion=editti&id='.$rw['ti_id'].'"><input type="button" class="botonb" id="editar" /></a>';
    echo '</tr>';
  }
  echo '</tbody>
  </table>
  ';
}
function browse(){
  $amenu = array("1" => "Información","2" => "Conócenos","3" => "Sólo en Púrpura");
  echo '
  <div class="div2" id="bboton01">
  <a href="?modulo=informativo&accion=edit"><input type="button" value=" " class="botonb" id="nuevo" accesskey="N"></a>
  </div></div>';

  echo '
  <table align="center" class="lista" width=50%>
  <thead>
  <tr>
  <th colspan="4"><b>Textos Informativos</b></th>
  </tr>
  </thead>
  <tbody>
  <tr>
  <th>Id</th>
  <th>Menu</th>
  <th>Nombre</th>
  <th width="15%">Estatus</th>
  </tr>';
  while($rw = mysql_fetch_array($this->model->result)){
    echo '<tr>';
    echo '<th><a href="?modulo=informativo&accion=show&id='.$rw['ci_id'].'">'.$rw['ci_id'].'</a></th>';
    echo '<td>'.busca($rw['ci_menu'],'titulos_info','ti_id','ti_nmb').'</td>';
    echo '<td>'.$rw['ci_nmb'].'</td>';
    if($rw['ci_estatus'] == "A")
      echo '<td width="9%" style= "background:#45C431"><center><b><a href="?modulo=informativo&accion=desactivar&id='.$rw['ci_id'].'"><input type="button" class="botonb" id="desactivar" /></a>';
    else
      echo '<td width="9%" style= "background:#CC0000"><center><b><a href="?modulo=informativo&accion=activar&id='.$rw['ci_id'].'"><input type="button" class="botonb" id="activar" /></a>';
    echo '</tr>';
  }
  echo '</tbody>
  </table>
  ';
}
function edit(){
?>
<script>
var x;
$(document).ready(inicio);
function inicio(){
  //$(window).bind('beforeunload', nosalir);
}
function nosalir(){
  return 'Guarde los datos antes de continuar, de lo contrario perderán los cambios';
}
function salir(){
  $(window).unbind('beforeunload');
}
</script>
<?php
  echo '
  <div class="div2" id="bboton01">
  <input type="button" value=" " class="botonb" id="cancelar" accesskey="X" onclick="Javascript:history.back();">
  </div></div>';
  $amenu = array("1" => "Información","2" => "Conócenos","3" => "Sólo en Púrpura");
  $accion = 'insert';
  $titulo = 'Agregar Categoria';
  if($this->model->id){
    $accion = 'update';
    $titulo = 'Editar Categoria';
  }
  $estatus="checked";
  if($this->model->estatus == "I")
    $estatus = '';
  echo '<form action="?modulo=informativo&accion='.$accion.'" method="post" onsubmit="return checkSubmitdetalle();">
  <table align="center" class="lista" width="50%">
  <thead>
  <th colspan="2"><b>'.$titulo.'</b></th>
  </thead>
  <tbody>';
  if($this->model->id)
    echo '<input type="hidden" name="id" value="'.$this->model->id.'"/>';
  echo '<tr>
  <th>Nombre</th>
  <td><input type="text" name="nmb" value="'.$this->model->nmb.'" maxlength="200" autofocus></td>
  </tr>
  <tr>
  <th>Menu</th>
  <td>'.menu_select_db('titulos_info', 'ti_id', 'ti_nmb', $this->model->menu, 'menu', 'ti_sel = "A"',false,false,false,true).'</td></tr>
  <tr>
  <th><b>Estatus</b></th>
  <td><input type="checkbox" name="estatus" '.$estatus.'/></td>
  </tr>
  <tr>
  <td colspan="2"><input  type="submit" id="guardar" value=" " class="botont"></td>
  </tr>
  </tbody>
  </table>
  </form>';
}

function show(){
  $amenu = array("1" => "Información","2" => "Conócenos","3" => "Sólo en Púrpura");
  $aest = array("A" => "Activo","I" => "Inactivo");
  echo '
  <div class="div2" id="bboton01">
  <a href="?modulo=informativo&accion=index"><input type="button" value=" " class="botonb" id="atras" accesskey="B"></a>
  <a href="?modulo=informativo&accion=edit&id='.$this->model->id.'"><input type="button" value=" " class="botonb" id="editar" accesskey="M"></a>
  </div></div>';

  echo '<center>
  <table align="center" class="lista" width="50%">
  <tr><th>Menu</th><td>'.$amenu[$this->model->menu].'</td></tr>
  <tr><th>Categoria</th><td>'.$this->model->nmb.'</td></tr>
  <tr><th>Estatus</th><td>'.$aest[$this->model->estatus].'</td></tr>
  </table>
  ';

  echo '<a href="?modulo=informativo&accion=editinfo&cat='.$this->model->id.'"><input type="button" value=" " class="botont" id="nuevo" accesskey="N"></a>
  <table align="center" class="lista" width=90%>
  <thead>
  <tr><th colspan="5"><b>Informacion</b></th></tr>
  </thead>
  <tbody><tr>
  <th>Id</th>
  <th>Titulo</th>
  <th width="50%">Descripcion</th>
  <th width="8%">Editar</th>
  <th width="8%">Estatus</th>
  </tr>';
  while($rw = mysql_fetch_array($this->model->result)){
    echo '<tr>';
    echo '<th>'.$rw['ti_id'].'</a></th>';
    echo '<td>'.$rw['ti_titulo'].'</td>';
    echo '<td>'.$rw['ti_descripcion'].'</td>';
    echo '<td><a href="?modulo=informativo&accion=editinfo&id='.$rw['ti_id'].'&cat='.$this->model->id.'"><input type="button" class="botont" value=" " id="editar"></a></td>';

    if($rw['ti_estatus'] == "A")
      echo '<td width="9%" style= "background:#45C431"><center><b><a href="?modulo=informativo&accion=desactivarinfo&id='.$rw['ti_id'].'&cat='.$this->model->id.'"><input type="button" class="botonb" id="desactivar" /></a>';
    else
      echo '<td width="9%" style= "background:#CC0000"><center><b><a href="?modulo=informativo&accion=activarinfo&id='.$rw['ti_id'].'&cat='.$this->model->id.'"><input type="button" class="botonb" id="activar" /></a>';
    echo '</tr>';
  }
  echo '</tbody>
  </table>
  ';
}

function editinfo($cat){
?>
<script>
var x;
$(document).ready(inicio);
function inicio(){
//  $(window).bind('beforeunload', nosalir);
}
function nosalir(){
  return 'Guarde los datos antes de continuar, de lo contrario perderán los cambios';
}
function salir(){
  $(window).unbind('beforeunload');
}
</script>
<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script>tinymce.init({
  selector:'textarea',
  auto_focus: 'element1',
  theme: 'modern',
  plugins: [
    'advlist autolink lists link image charmap print preview hr anchor pagebreak',
    'searchreplace wordcount visualblocks visualchars code fullscreen',
    'insertdatetime media nonbreaking save table contextmenu directionality',
    'emoticons template paste textcolor colorpicker textpattern imagetools'
  ],
  toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
  toolbar2: 'print preview | forecolor backcolor emoticons'
});
</script>

<?php

  echo '
  <div class="div2" id="bboton01">
  <input type="button" value=" " class="botonb" id="cancelar" accesskey="X" onclick="Javascript:history.back();">
  </div></div>';
  $accion = 'insertinfo';
  $titulo = 'Agregar Informacion';
  if($this->model->id){
    $titulo = 'Editar Informacion';
    $accion = 'updateinfo';
  }
  $estatus = 'checked';
  if($this->model->estatus == "I")
    $estatus = '';
  echo '
  <form enctype="multipart/form-data" action="?modulo=informativo&accion='.$accion.'" method="post" onsubmit="return checkSubmitdetalle();">
  <table align="center" class="lista" width="80%">
  <thead><th colspan="2"><b>'.$titulo.'</b></th></thead><tbody>
  <tr>';
  if($this->model->id){
    echo ' <input type="hidden" name="id" value="'.$this->model->id.'" /> ';
    echo ' <input type="hidden" name="categoria" value="'.$this->model->categoria.'" /> ';
  }
  else{
    echo ' <input type="hidden" name="categoria" value="'.$cat.'" /> ';
  }
  echo '<th>Titulo</th>
  <td><input type="text" name="titulo" value="'.$this->model->titulo.'" autofocus></td>
  </tr><tr>
  <th>Descripción</th>
  <td><textarea name="descripcion" rows="7" cols="60">'.$this->model->descripcion.'</textarea></td>
  </tr><tr>
  <th><b>Estatus</b></th>
  <td><input type="checkbox" name="estatus" '.$estatus.' /></td>
  </tr>
  <tr>
  <td colspan="2"><input  type="submit" id="guardar" value=" " class="botont"></td>
  </tr>
  </tbody>
  </table>
  </form>';
}

function showslider(){
  echo '<div class="div2" id="bboton01">
  </div>
  </div>';
  echo '
  <form action="?modulo=informativo&accion=insertimagen&id='.$_GET['id'].'" method="post" enctype="multipart/form-data" onsubmit="return checkSubmitguardar();">
    <table align="center" class="lista" width="60%" >
    <thead>
        <th colspan="2">Subir imagen</th>
    </thead>
    <tbody>
        <tr>
            <th>Seleccionar una imagen</th>
            <td><input type="file" name="archivo[]" multiple="multiple" required></td>
        </tr>
        <tr>
            <td colspan="2"><input type="submit" value=" " id="guardar" class="botont"></td>
        </tr>
    </tbody>
    </table>
    </form>  ';
    $i=0;
   echo '<table width="60%" align="center" class="lista">
   <thead><tr><th colspan="4">
    <a id="popup" href="?modulo=informativo&accion=vieworder">ORDENAR IMAGENES</a></th></tr></thead><tr>';
    while($rw=mysql_fetch_array($this->model->result)){
      echo '<td class="productima">
      <a target="_blank" href="../jdshop.mx/slider/'.$rw['i_nmb'].'.'.$rw['i_ext'].'">
      <img width="198px" height="131px" src="https://jdshop.mx/slider/'.$rw['i_nmb'].'.'.$rw['i_ext'].'"></a>
      <div id="divborra"><input type="button" class="botont" id="borrar" value=" " onclick="confdel('.$rw['i_id'].')"></div>
      </td>';
       $i++;
      $j=$i%4;
      if($j=='0') echo '</tr><tr>';
    }
    echo '</table>';
    ?>
<script>
function checkSubmitguardar() {
  document.getElementById("guardar").value = "JD";
  document.getElementById("guardar").disabled = true;
  return true;
}
function confdel(img){
  var conf = confirm("Desea eliminar la imagen del slider?");
  if(conf){
    document.location.href = "?modulo=informativo&accion=deleteimagen&id="+img;
  }
}
</script>
  <?php
}

function browsevoz(){
  echo '
  <div class="div2" id="bboton01">
  <a href="?modulo=informativo&accion=editvoz"><input type="button" value=" " class="botonb" id="nuevo" accesskey="N"></a>
  <a id="popup" href="?modulo=informativo&accion=editimgvoz"><input type="button" value=" " class="botonb" id="adjuntar" accesskey="N"></a>
  </div></div>';

  echo '
  <table align="center" class="lista" width="90%">
  <thead>
  <tr>
  <th colspan="6"><b>La Voz de Nuestros Clientes</b></th>
  </tr>
  <tr><th colspan="6">Imagen</th></tr><tr><td colspan="6">';
  if(file_exists('images/voz.png'))
    echo '<img width="150px" height="150px" src="images/voz.png" alt=""/>';
  elseif(file_exists('images/voz.jpg'))
    echo '<img width="150px" height="150px" src="images/voz.jpg" alt=""/>';
  echo '</td></tr>
  </thead>
  <tbody>
  <tr>
  <th>Id</th>
  <th>Descripcion</th>
  <th>Nombre</th>
  <th>Mostrar</th>
  <th>Orden</th>
  <th></th>
  </tr>';
  while($rw = mysql_fetch_array($this->model->result)){
    echo '<tr>';
    echo '<th>'.$rw['vc_id'].'</th>';
    echo '<td>'.$rw['vc_descripcion'].'</td>';
    echo '<td>'.$rw['vc_nmb'].'</td>';
    if($rw['vc_mostrar'] == "1")
      echo '<td width="9%" style= "background:#45C431"><center><b>SI</b></td>';
    else
      echo '<td width="9%" style= "background:#CC0000"><center><b>NO</b></td>';
    echo '<td>'.$rw['vc_orden'].'</td>';
    echo '<td><a href="?modulo=informativo&accion=editvoz&id='.$rw['vc_id'].'"><input type="button" value="" id="editar" class="botont"/></a></td>';
    echo '</tr>';
  }
  echo '</tbody>
  </table>
  ';
}
function editvoz(){
  $sqlc = 'SELECT CONCAT(c_nmb," ",c_apellidos) nmb FROM clientes WHERE c_estatus = "A"';
  $resultc = mysql_query($sqlc) or die($sqlc);
  while ($row = mysql_fetch_array($resultc)) {
    $elementos[] = '"'.$row['nmb'].'"';
  }
  $arreglo = implode(", ", $elementos);
?>
<script>
var x;
$(document).ready(inicio);
function inicio(){
  //$(window).bind('beforeunload', nosalir);
}
function nosalir(){
  return 'Guarde los datos antes de continuar, de lo contrario perderán los cambios';
}
function salir(){
  $(window).unbind('beforeunload');
}
$(function() {
  var availableTags = new Array(<?php echo $arreglo; ?>);
  $("#tags").autocomplete({
    source: availableTags
  });
});
</script>
<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script>tinymce.init({
  selector:'textarea',
  auto_focus: 'element1',
  theme: 'modern',
  plugins: [
    'advlist autolink lists link image charmap print preview hr anchor pagebreak',
    'searchreplace wordcount visualblocks visualchars code fullscreen',
    'insertdatetime media nonbreaking save table contextmenu directionality',
    'emoticons template paste textcolor colorpicker textpattern imagetools'
  ],
  toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
  toolbar2: 'print preview | forecolor backcolor emoticons'
});
</script>
<?php
  echo '
  <div class="div2" id="bboton01">
  <input type="button" value=" " class="botonb" id="cancelar" accesskey="X" onclick="Javascript:history.back();">
  </div></div>';
  $accion = 'insertvoz';
  $titulo = 'Agregar';
  if($this->model->id){
    $accion = 'updatevoz';
    $titulo = 'Editar';
  }
  $mostrar = "checked";
  if($this->model->mostrar == "0")
    $mostrar = '';
  echo '<form action="?modulo=informativo&accion='.$accion.'" method="post" onsubmit="return checkSubmitdetalle();">
  <table align="center" class="lista" width="80%">
  <thead>
  <th colspan="2"><b>'.$titulo.'</b></th>
  </thead>
  <tbody>';
  if($this->model->id)
    echo '<input type="hidden" name="id" value="'.$this->model->id.'"/>';
  echo '
  <tr>
  <th>Descripción</th>
  <td><textarea name="descripcion" rows="7" cols="60">'.$this->model->descripcion.'</textarea></td></tr>
  <tr>
  <tr>
  <th>Nombre</th>
  <td><input type="text" name="nmb" id="tags" value="'.$this->model->nmb.'" maxlength="200" autofocus></td>
  </tr>
  <th><b>Mostrar</b></th>
  <td><input type="checkbox" name="mostrar" '.$mostrar.'/></td>
  </tr>
  <tr>
  <th>Orden</th>
  <td><input type="number" name="orden" value="'.$this->model->orden.'" min="0" step="1"></td>
  </tr>
  <tr>
  <td colspan="2"><input  type="submit" id="guardar" value=" " class="botont"></td>
  </tr>
  </tbody>
  </table>
  </form>';
}

}
?>