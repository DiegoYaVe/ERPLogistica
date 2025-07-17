<?php
class precios{

  function __construct(){
      $this->model = new modelprecios($obj);
  }
  function index(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;
      if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";

      $this->model->result($_REQUEST['nmb'],$_REQUEST['estatus']);
      $this->model->select($_GET['id']);
      $this->view = new viewlprecios($this->model);
      $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus']);
  }
  function insert(){

    if(isset($_POST['miva'])) $miva = "1"; else $miva = 0;
    $estatus = "A";
    if($_POST['pv'] == "on") $pv = "1";
    else $pv = "0";
    $this->model->setdata(getmax('ep_id','esquema_precio'), $_POST['nmb'],$_POST['base'],$_POST['precio'],$_POST['masmenos'],$_POST['sumarp'],$miva,$_POST['sumarf'],$estatus,$_SESSION['emp'],$pv);
    $unieue = unique($_POST['nmb'],'ep_nmb','esquema_precio','ep_empresa = "'.$_SESSION['emp'].'"');

    if($unieue == 1) {
      $this->model->insert();
      $error = "";
    }
    else $error = "&error=2XME01";


    redirect('?modulo=precios&accion=index'.$error);
  }
  function update(){

    $estatus = "A";
    if(isset($_POST['miva'])) $miva = "1"; else $miva = 0;
    if($_POST['pv'] == "on") $pv = "1";
    else $pv = "0";
    $this->model->setdata($_POST['id'], $_POST['nmb'],$_POST['base'],$_POST['precio'],$_POST['masmenos'],$_POST['sumarp'],$miva,$_POST['sumarf'],$estatus,$_SESSION['emp'],$pv);
    if(unique($_POST['nmb'],'ep_nmb','esquema_precio','ep_empresa != "'.$_SESSION['emp'].'" AND ep_id = "'.$this->model->id.'"') == 1) {
      $this->model->update();
      $error = "";
    }else $error = "&error=2XME01";

    redirect('?modulo=precios&accion=index'.$error);
  }
  function borrar(){
    $sql = 'DELETE FROM esquema_precio
            WHERE ep_id = "'.$_GET['id'].'"
            AND ep_empresa = "'.$_SESSION['emp'].'"';
    setq($sql);

    redirect("?modulo=precios&accion=index");
  }
  function setprecioalmacen(){
    $sql = 'SELECT * FROM almacenes WHERE a_empresa = "'.$_SESSION['emp'].'" AND a_estatus = "A"';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      if(isset($_POST['precioal'.$row['a_id']])) $estatusal = "1"; else $estatusal = "0";

        $this->model->esquemaalmacen($_GET['precio'],$row['a_id'],$estatusal);
    }

    redirect("?modulo=precios&accion=index");
  }
}

class modelprecios{
  function result($nmb){
    $sql = 'SELECT * FROM esquema_precio WHERE ep_empresa = "'.$_SESSION['emp'].'" ';
    if($nmb) $sql.= ' AND ep_nmb LIKE "%'.trim($nmb).'%"';

    $sql.=' ORDER BY ep_id ASC';
    $this->result = setq($sql);
  }
  function select($id){
      $sql = 'SELECT * FROM esquema_precio WHERE ep_id="'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['ep_id'];
      $this->nmb = $row['ep_nmb'];
      $this->base = $row['ep_base'];
      $this->precio = $row['ep_precio'];
      $this->masmenos = $row['ep_masmenos'];
      $this->sumarp = $row['ep_sumarp'];
      $this->miva = $row['ep_miva'];
      $this->sumarf = $row['ep_sumarf'];
      $this->estatus = $row['ep_estatus'];
      $this->pv = $row['ep_puntodv'];
  }
  function setdata($id,$nmb,$base,$precio,$masmenos,$sumarp,$miva,$sumarf,$estatus,$empresa,$pv){
      mb_internal_encoding("UTF-8");
      $this->id = $id;
      $this->nmb = clearvmayus($nmb);
      $this->base = clearvmayus($base);
      $this->precio = clearvmayus($precio);
      $this->masmenos = clearvmayus($masmenos);
      $this->sumarp = clearvmayus($sumarp);
      $this->miva = clearvmayus($miva);
      $this->sumarf = clearvmayus($sumarf);
      $this->estatus = clearvmayus($estatus);
      $this->empresa = clearvmayus($empresa);
      $this->pv = clearvmayus($pv);
  }
  function insert(){
      if($this->pv == "1"){
        $sql = 'UPDATE esquema_precio SET
                ep_puntodv = "0" 
                WHERE ep_empresa = "'.$_SESSION['emp'].'"';
        setq($sql);
      }

      $sql = 'INSERT INTO esquema_precio SET
              ep_id = "'.$this->id.'",
              ep_nmb = "'.$this->nmb.'",
              ep_base = "'.$this->base.'",
              ep_precio = "'.$this->precio.'",
              ep_masmenos = "'.$this->masmenos.'",
              ep_sumarp = "'.$this->sumarp.'",
              ep_sumarf = "'.$this->sumarf.'",
              ep_miva = "'.$this->miva.'",
              ep_empresa = "'.$this->empresa.'",
              ep_puntodv = "'.$this->pv.'"';
      setq($sql);
  }
  function update(){
      if($this->pv == "1"){
        $sql = 'UPDATE esquema_precio SET
                ep_puntodv = "0" 
                WHERE ep_empresa = "'.$_SESSION['emp'].'"';
        setq($sql);
      }
      $sql = 'UPDATE esquema_precio SET
              ep_nmb = "'.$this->nmb.'",
              ep_base = "'.$this->base.'",
              ep_precio = "'.$this->precio.'",
              ep_masmenos = "'.$this->masmenos.'",
              ep_sumarp = "'.$this->sumarp.'",
              ep_sumarf = "'.$this->sumarf.'",
              ep_miva = "'.$this->miva.'",
              ep_puntodv = "'.$this->pv.'"
              WHERE ep_id = "'.$this->id.'"';
      setq($sql);

      $this->setprecio();
  }
  function esquemaalmacen($precio,$almacen,$estatusal){
    if($estatusal == "1")
      $sql = 'INSERT INTO esquema_almacen SET
              ea_almacen = "'.$almacen.'",
              ea_esquema = "'.$precio.'",
              ea_faplica = "'.date('Y-m-d').'",
              es_uaplica = "'.$_SESSION['emp'].'"
              ON DUPLICATE KEY UPDATE
              ea_faplica = "'.date('Y-m-d').'",
              es_uaplica = "'.$_SESSION['emp'].'"';
    else
      $sql = 'DELETE FROM esquema_almacen WHERE ea_almacen = "'.$almacen.'" AND ea_esquema = "'.$precio.'"';

      setq($sql);
  }
  function setprecio(){
    $sql = 'SELECT * FROM articulos_precios WHERE ap_esquema = "'.$this->id.'" AND ap_activo = "1"';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $costo = $row['ap_costo'];
      if($costo != NULL && $costo != 0){
        if($this->base == "C") $base = $costo;
        else $base = busca($this->precio,'articulos_precios','ap_activo = "1" AND ap_articulo = "'.$row['ap_articulo'].'" AND ap_esquema','ap_precio');

        $diferencia = $base*($this->sumarp/100);

        if($this->masmenos == "+") $precio = ($base+$diferencia)+$this->sumarf;
        else $precio = ($base-$diferencia)+$this->sumarf;

        if($this->miva == 1) $precio = $precio * 1.16;

        $sqldpr = 'UPDATE articulos_precios SET ap_activo = "0"
                  WHERE ap_articulo = "'.$row['ap_articulo'].'" AND ap_esquema = "'.$this->id.'"';
        setq($sqldpr);
        
        $sqlipr = 'INSERT INTO articulos_precios SET
                  ap_articulo = "'.$row['ap_articulo'].'",
                  ap_esquema = "'.$this->id.'",
                  ap_precio = "'.$precio.'",
                  ap_costo = "'.$row['ap_costo'].'",
                  ap_activo = "1",
                  ap_fecha = "'.date('Y-m-d H:i:s').'",
                  ap_user = "'.$_SESSION['uid'].'"'; 
        setq($sqlipr);
      }
    }
    
    $result1 = setq($sql);
    while($row1 = $result1->fetch_array()){
      $combo = busca($row1['ap_articulo'],'articulos_combos','ac_articulo','COUNT(*)');
      if($combo > 0){
        $sqlcombo = 'SELECT ac_ahijo FROM articulos_combos WHERE ac_articulo = "'.$row1['ap_articulo'].'"';
        $resultcombo = setq($sqlcombo);
        $preciocmb = 0;
        while($rowcombo = $resultcombo->fetch_array()){
          $preciocmb += busca($rowcombo['ac_ahijo'],'articulos_precios','ap_activo = "1" AND ap_esquema = "'.$this->id.'" AND ap_articulo','ap_precio');
        }

        $sqldpr = 'UPDATE articulos_precios SET ap_activo = "0"
                  WHERE ap_articulo = "'.$row1['ap_articulo'].'" AND ap_esquema = "'.$this->id.'"';
        setq($sqldpr);
        
        $sqlipr = 'INSERT INTO articulos_precios SET
                  ap_articulo = "'.$row1['ap_articulo'].'",
                  ap_esquema = "'.$this->id.'",
                  ap_precio = "'.$preciocmb.'",
                  ap_costo = "'.$row1['ap_costo'].'",
                  ap_activo = "1",
                  ap_fecha = "'.date('Y-m-d H:i:s').'",
                  ap_user = "'.$_SESSION['uid'].'"'; 
        setq($sqlipr);
      }
    }
  }
}

class viewlprecios {
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

    <div class="col-md-12 form-inline mb-5">
        <a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setesquemaprecio.php" class="btn btn-primary"
        href="javascript:;">
        <i class="fa fa-plus"></i> Nuevo
        </a>
        <button id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
        </button>
        <button class="btn btn-secondary" data-toggle="tooltip" data-placement="bottom" data-original-title="Determinar listas de precios te permite gestionar varias precios para un mismo producto." data-trigger="click" >
          <i class="icon-question-circle"></i>
        </button>
    </div>

    <!--------  
    <div class="mb-5 col-md-2">
      <a data-fancybox data-type="ajax" data-src="popup/setesquemaprecio.php" class="btn btn-primary"
      href="javascript:;">
      <i></i> Nuevo
      </a>
    </div>
    <div class="mb-5 col-md-2">
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>
    <div class="mb-5 col-md-2">
      <button class="btn btn-secondary" data-toggle="tooltip" data-placement="bottom" data-original-title="Determinar listas de precios te permite gestionar varias precios para un mismo producto." data-trigger="click" >
        <i class="icon-question-circle"></i>
      </button>
    </div>
    -->
    <div id="filter-panel" class="col-md-12 collapse filter-panel">
          <div class="panel panel-default">
            <div class="panel-body mb-1">
              <form class="form-inline" role="form" method="post" action="?modulo=precios&accion=index">
                <div class="mb-5">
                  <label for="">Filtrar por nombre:</label>
                  <input type="text" class="form-control" id="pref-search" name="nmb" value="<?php echo $nmb ?>" placeholder="Buscar por nombre">
                </div><!-- form group [search] -->

                <div class="mb-5">
                  <label for="">Acciones:</label> <br>
                <button type="submit" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=precios&accion=index"><button type="button" class="btn btn-warning">
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
                window.location.href="?modulo=precios&accion=borrar&id=" + iduni;
              }
            }
            function verlineasneg(iduni){
              window.open("?modulo=articulos&accion=index&marca=" + iduni);
            }
          </script>

        <div class="">
          <div class="">

<?php



  $basep = "";
  $basec = "";
  $basef = "";
  $chiva = "";
  if($this->model->id){
    $accion = 'update';
    if($this->model->base == "C") $basec = "selected";
    elseif($this->model->base == "P") $basep = "selected";
    else $basef = "selected";

    if($this->model->miva) $chiva = "checked";
  }else{
    $accion = 'insert';
    $basec = "selected";
  }
  if($this->model->pv == "1") $checked = "checked";
  else $checked = "";
  ?>
    <script>
      function showbase(){
        if(document.getElementById("base").value == "P"){
          document.getElementById("preciob").style.display = "";
          document.getElementById("masomenos").style.display = "";
          document.getElementById("masiva").style.display = "none";
          document.getElementById("agregarm").style.display = "none";
          document.getElementById("precio").required = true;
          document.getElementById("miva").required = false;
          document.getElementById("sumarf").required = false;
        }
        else{
          document.getElementById("preciob").style.display = "none";
          document.getElementById("masomenos").style.display = "none";
          document.getElementById("precio").required = false;
          document.getElementById("masiva").style.display = "";
          document.getElementById("agregarm").style.display = "";
          document.getElementById("miva").required = false;
          document.getElementById("sumarf").required = true;
        }
      }
    </script>
  <?php

  echo '<div class="row">';
  /*
  echo '
        <form method="post" action="?modulo=precios&accion='.$accion.'" name="addprecio" onsubmit="checkSubmitedit();">
        <input type="hidden" value="'.$this->model->id.'" name="id" />';

  echo '<div class="offset-md-1 col-md-10 col-12 alert alert-primary text-xs-center">
            Agregar un esquema de precios
        </div>
        <div class="col-md-12">
        <div class="col-md-6 col-lg-3">
          <div class="mb-5">
            <label>Nombre del esquema</label>
            <input type="text" id="nmb" class="form-control focus mayus" placeholder="Nombre del esquema" name="nmb" value="'.$this->model->nmb.'" required="required" >
          </div>
        </div>
        <div class="col-md-6 col-lg-2">
          <div class="mb-5">
            <label>Monto de referencia</label>
            <select name="base" id="base" required="required" class="form-control" onchange="showbase()">
              <option value="C" '.$basec.' >Sacar precio a partir del costo</option>
              <option value="P" '.$basep.'>Determinar el precio desde un precio existente</option>
              <option value="F" '.$basef.'>Fijar el costo manualmente</option>
            </select>
          </div>
        </div>

        <div class="col-sm-8 col-md-2" id="preciob" style="display:none">
          <div class="mb-5">
            <label>Precio Referencia</label>
            <select name="precio" id="precio" required="required" class="form-control">';
        $sqlp = 'SELECT * FROM esquema_precio WHERE ep_empresa = "'.$_SESSION['emp'].'" AND ep_estatus = "A"';
        $resultp = setq($sqlp);
        while($row = $resultp->fetch_array()){
          echo '<option value="'.$row['ep_id'].'" >'.$row['ep_nmb'].'</option>';
        }
        echo '</select>
          </div>
        </div>
        <div class="col-md-6 col-md-2" id="masomenos" style="display:none">
          <div class="mb-5">
            <label>Comportamiento de la formula</label>
            <select name="masmenos" id="masmenos" required="required" class="form-control" onchange="showbase()">
              <option value="+">Sumar un porcentaje al precio</option>
              <option value="-">Restar un porcentaje al precio</option>
            </select>
          </div>
        </div>
        <div class="col-md-6 col-lg-2">
          <div class="mb-5">
            <label>% de utilidad</label>
            <input type="number" name="sumarp" id="sumarp" class="form-control" required="required" min="0" max="9999" step="0.01" value="'.$this->model->sumarp.'" placeholder="% de utilidad sobre la referencia" />
          </div>
        </div>
        <div class="col-md-6 col-lg-2" id="agregarm">
          <div class="mb-5">
            <label>Agregar</label>
            <input type="number" name="sumarf" id="sumarf" value="'.$this->model->sumarf.'" class="form-control" required="required" min="0" max="9999" step="0.01" placeholder="Sumar adicional" />
          </div>
        </div>
        <div class="col-md-6 col-lg-1" id="masiva">
          <div class="mb-5">
            <center>
              <label>Sumar IVA</label><br>
              <input type="checkbox" name="miva" id="miva" '.$chiva.' data-toggle="tooltip" data-placement="top" data-original-title="Referencia X Utilidad + IVA" />
            </center>
          </div>
        </div>
        <div class="col-md-6 col-lg-2">
          <div class="mb-5">
          <center>
            <label>Punto de Venta</label> <br>
            <input type="checkbox" name="pv" id="pv" '.$checked.'  data-toggle="tooltip" data-placement="top" data-original-title="Predeterminado para precios del Punto de Venta" >
          </center>
          </div>
        </div> 
        </div>
        <div class="col-sm-12 col-md-12 text-xs-center mb-1">
          <center><button type="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
        </div>';
  echo '</form>';
*/
  echo '<div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead class="bg-light-blue bg-darken-2 text-white">
                  <tr>
                    <th>Nombre</th>
                    <th>Cálculo</th>
                    <th></th>
                  </tr>
                </thead>';
    while ($row = $this->model->result->fetch_array()) {
      if($row['ep_base'] == "C"){
        $baser = number_format($row['ep_sumarp'],0). "% Sobre el costo";
        if($row['ep_miva'] == "1") $baser.= " Más IVA ";
        if($row['ep_sumarf'] > 0) $baser.= ', Más $'.$row['ep_sumarf'];
      }
      elseif($row['ep_base'] == "P"){
        if($row['ep_masmenos'] == "+") $masmenos = "más"; else $masmenos = "menos";
        $baser =  number_format($row['ep_sumarp'],0).' % '.$masmenos.' que '.busca($row['ep_precio'],'esquema_precio','ep_id','ep_nmb');
        if($row['ep_miva'] == "1") $baser.= " Más IVA ";
        if($row['ep_sumarf'] > 0) $baser.= ', Más $'.$row['ep_sumarf'];
      }
      else $baser = "PRECIO FIJADO MANUALMENTE";

        echo '<tr>
        <td>'.$row['ep_nmb'].'</td>
        <td>'.$baser.'</td>
        <td>';
          /*echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar el esquema de precios" href="?modulo=precios&accion=index&id='.$row['ep_id'].'">
                <button type="button" class="btn btn-info ml-1">
                  <i class="icon-edit2"></i> Editar
                </button></a>' ; */
        
          echo '<div style="display: flex;
          flex-flow: row wrap;
          align-items: center;">';
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar el esquema de precios" href="javascript:;" data-fancybox data-type="ajax" data-src="popup/setesquemaprecio.php?id='.$row['ep_id'].'">
          <button type="button" class="btn btn-info ml-1">
            <i class="icon-edit2"></i> Editar
          </button></a>' ;
        
        if(busca($row['ep_id'],'articulos INNER JOIN articulos_precios ON ap_articulo = a_id','a_estatus = "A" AND a_empresa = "'.$_SESSION['emp'].'" AND ap_esquema','COUNT(*)') == 0)
          echo '<button type="button" class="btn btn-danger ml-1" onclick="checerase('.$row['ep_id'].')" data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar el esquema de precios">
                  <i class="icon-eraser"></i> Editar
                </button>
                ';
        else
          echo '<a href="?modulo=articulos&accion=index&esquema='.$row["ep_id"].'" type="button" class="btn bg-teal ml-1 text-white" data-toggle="tooltip" data-placement="top" title data-original-title="El esquema de precios se encuentra en uso, Presiona aquí para ver los productos asignados a '.$row['ep_nmb'].'">
                  <i class="fa fa-eye"></i> Detalles
                </a> ';
        $numtag = busca($row['ep_id'],'esquema_almacen','ea_esquema','COUNT(*)');
          /*
          echo '<a data-fancybox data-type="ajax" data-src="popup/precioesquema.php?precio='.$row['ep_id'].'" href="javascript:;">
                <button type="button" class="btn bg-amber bg-darken-3 ml-1">
                  <i class="icon-th"></i> Aplicar a almacenes
                  <div class="tag tag-pill tag-danger">'.$numtag.'</div>
                </button></a>';  */

        echo'
          <form action="?modulo=precios&accion=setprecioalmacen&precio='.$row['ep_id'].'" method="post" enctype =  "multipart/form-data" onsubmit="checksubmit();">
              <div class="dropdown">
                <button class="btn btn bg-amber ml-1 bg-darken-3 dropdown-toggle"  type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-th"></i>  Aplicar a almacenes <div class="tag tag-pill tag-danger">'.$numtag.'</div>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style=" height: 380px; overflow: scroll;">';
                $sql = 'SELECT * FROM almacenes WHERE a_empresa = "'.$_SESSION['emp'].'" AND a_estatus = "A" AND a_vendible = "1"';
                $result = setq($sql);
                while($row2 = $result->fetch_array()){
                  if(busca($row2['a_id'],'esquema_almacen','ea_esquema = "'.$row['ep_id'].'" AND ea_almacen','COUNT(*)') > 0) $sel = "checked";
                  else $sel = "";
                  echo '
                    <li class="dropdown-item">
                        <input type="checkbox" name="precioal'.$row2['a_id'].'" '.$sel.'  class="flipswitchsn"/> '.$row2['a_nmb'].'
                    </li>
                  ';
                }

                echo '
                  <div class="mt-1 mb-1 col-md-12">
                  <center><button role="submit" class="btn btn-primary" id="guardar"><i class="fa fa-save"></i> Guardar</button></center>
                  </div>
                </div>';
                echo '<input type="hidden" value="'.$valac.'" name="actividad" id="actividad">
                </div>';
                echo '
          </form>';


        if($row['ep_puntodv'] == 1) echo '<span class="ml-1 tag tag-success">Punto de Venta</span>';
        echo '</div></td>';
        
    }
    echo '<tr style="height: 400px;"><td></td><td></td><td></td></tr>';
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