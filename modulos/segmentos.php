<?php
//ini_set('display_errors', 1);
class segmentos{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelsegmentos(isset($obj));
  }

  function index(){
    if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
    if(!isset($_REQUEST['update'])){
      $estatus = "A";
    }
    else{
      if(isset($_REQUEST['estatus'])) $estatus = "A";
      else $estatus  = "I";
    }

    $this->model->result(trim($_REQUEST['nmb']),$estatus);
    $this->view = new viewsegmentos($this->model);
    $this->view->browse($_REQUEST['nmb'],$estatus);
  }

  function edit(){
    if(!isset($_REQUEST['id'])) $_REQUEST['id'] = NULL;

    $this->model->select($_REQUEST['id']);
    $this->view = new viewsegmentos($this->model);
    $this->view->edit();
  }

  function insert(){

    $this->model->setdata($_POST['id'],$_POST['nmb'],$_POST['descripcion'],$_POST['requerido'],$_POST['estatus']);
    $this->model->insert();

    $sql = 'SELECT MAX(cs_id) FROM crm_segmentos ';
    $result = setq($sql);
    list($idcs) = $result->fetch_array();

    if($_POST['solog'] == "1") $location = "?modulo=segmentos&accion=index";
    else $location = "?modulo=segmentos&accion=show&id=".$idcs;

    redirect($location);
  }

  function update(){

    $this->model->setdata($_POST['id'],$_POST['nmb'],$_POST['descripcion'],$_POST['requerido'],$_POST['estatus']);
    $this->model->update();

    if($_POST['solog'] == "1") $location = "?modulo=segmentos&accion=index";
    else $location = "?modulo=segmentos&accion=show&id=".$this->model->id;

    redirect($location);
  }

  function show(){
    $this->model->select($_GET['id']);
    $this->view = new viewsegmentos($this->model);
    $this->view->show();
  }

  function setvalor(){
    if(!isset($_POST['editar']))
      $this->model->setvalor($_GET['id'],$_POST['valor'],$_POST['descripcion']);
    else
      $this->model->editvalor($_GET['id'],$_POST['valor'],$_POST['descripcion'],$_POST['idd']);

    redirect("?modulo=segmentos&accion=show&id=".$_GET['id'].'&avs=1');
  }

  function delvalor(){
    $this->model->delvalor($_GET['seg'],$_GET['idd']);

    redirect("?modulo=segmentos&accion=show&id=".$_GET['seg'].'&avs=2');
  }

  function active(){
    $this->model->active($_GET['id'],$_GET['estatus']);

    redirect("?modulo=segmentos&accion=index");
  }
}

class modelsegmentos{
  function result($nmb,$estatus){
    $sql = 'SELECT * FROM crm_segmentos WHERE cs_empresa = "'.$_SESSION['emp'].'"'; //cs_estatus = "'.$estatus.'" AND
    if($nmb) $sql.=' AND cs_nmb LIKE "%'.$nmb.'%" ';

    $this->result = setq($sql);
  }

  function select($id){
    $sql = 'SELECT * FROM crm_segmentos WHERE cs_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['cs_id'];
    $this->nmb = $row['cs_nmb'];
    $this->descripcion = $row['cs_descripcion'];
    $this->requerido = $row['cs_requerido'];
    $this->panel = $row['cs_panel'];
    $this->estatus = $row['cs_estatus'];
  }

  function setdata($id,$nmb,$descripcion,$requerido,$estatus){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";

    $this->id = $id;
    $this->nmb = str_replace($simbol,$cambio,mb_strtoupper(trim($nmb)));
    $this->descripcion = str_replace($simbol,$cambio,mb_strtoupper(trim($descripcion)));
    if($requerido) $this->requerido = "1"; else $this->requerido = 0;
    if($estatus) $this->estatus = "A"; else $this->estatus = "I";
  }

  function insert(){
    $sql = 'INSERT INTO crm_segmentos SET
            cs_nmb = "'.$this->nmb.'",
            cs_descripcion = "'.$this->descripcion.'",
            cs_requerido = "'.$this->requerido.'",
            cs_estatus = "'.$this->estatus.'",
            cs_empresa = "'.$_SESSION['emp'].'"';
    setq($sql);
  }

  function update(){
    $sql = 'UPDATE crm_segmentos SET
            cs_nmb = "'.$this->nmb.'",
            cs_descripcion = "'.$this->descripcion.'",
            cs_requerido = "'.$this->requerido.'",
            cs_estatus = "'.$this->estatus.'"
            WHERE cs_id = "'.$this->id.'"';
    setq($sql);
  }

  function active($id,$estatus){
    $sql = 'UPDATE crm_segmentos SET cs_estatus = "'.$estatus.'" WHERE cs_id = "'.$id.'"';
    setq($sql);
  }

  function setvalor($id,$valor,$descripcion){
    $sql = 'INSERT INTO crm_segmentosd SET
            cd_segmento = "'.$id.'",
            cd_id = "'.getmax('cd_id','crm_segmentosd').'",
            cd_valor = "'.clearvmayus($valor).'",
            cd_descripcion = "'.clearvmayus($descripcion).'",
            cd_estatus = "A",
            cd_falta = "'.date('Y-m-d').'"';
    setq($sql);
  }

  function editvalor($id,$valor,$descripcion,$idd){
    $sql = 'UPDATE crm_segmentosd SET
            cd_valor = "'.clearvmayus($valor).'",
            cd_descripcion = "'.clearvmayus($descripcion).'",
            cd_estatus = "A"
            WHERE cd_segmento = "'.$id.'" AND cd_id = "'.$idd.'"';
    setq($sql);
  }

  function delvalor($seg,$idd){
    $sql = 'DELETE FROM crm_segmentosd WHERE cd_segmento = "'.$seg.'" AND cd_id = "'.$idd.'"';
    setq($sql);
  }
}

class viewsegmentos{
  var $model;
  function __construct($model){

  ?>
  <script>
  function checkguardar(){
    document.getElementById("guardar").innerHTML = "Guardar";
    document.getElementById("guardar").disabled = true;
    document.getElementById("guardar1").innerHTML = "Guardar y agregar valores";
    document.getElementById("guardar1").disabled = true;
    return true;
  }
  function checkguardarvalor(){
    document.getElementById("guardar").innerHTML = "Agregando";
    document.getElementById("guardar").disabled = true;
    return true;
  }
  function checkguardaru(){
    pass = $('#pass').val();
    cpass = $('#cpass').val();
    if(pass === ''){
      document.getElementById("guardar").innerHTML = "JD";
      document.getElementById("guardar").disabled = true;
      return true;
    }
    else if(pass == cpass){
      document.getElementById("guardar").innerHTML = "JD";
      document.getElementById("guardar").disabled = true;
      return true;
    }
    else{
      alert("Las contraseñas no coinciden");
      return false;
    }
  }
  </script>
  <?php
    $this->model = $model;
    $this->tipoc = array("C"=>"Cliente","P"=>"Prospécto");

    $this->estatusm = array("A"=>"Asignable","I"=>"No Asignable");
    $this->estatusr = array("A"=>"Ruta activa","I"=>"Ruta Inactiva");

    $this->cestatusr = array("A"=>"#00CC33","I"=>"#CC0000");
  }

  function browse($nmb,$estatus){
    echo '<div class="page-title-actions col-md-12">';
    echo '
    <a href="?modulo=segmentos&accion=edit" id="nuevo"><button class="mb-1 btn btn-primary"><i class="fa fa-plus"></i> Nuevo</button></a>
    <button accesskey="L" id="filtrar" type="button" class="btn btn-info mb-1"  data-toggle="collapse" data-target="#filter-panel">
      <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
    </button>';
    echo '';

    if($estatus == "A") $chest = "checked"; else $chest = "";
  ?>
  <script>
    $(function() {
      $('#toogle-estatus').bootstrapToggle({
        on: 'Activos',
        off: 'Inactivos'
      });
    })

    $('body').on("keydown", function(e) { 
      if (e.altKey && e.which === 78) {
        var btn = document.getElementById('nuevo');
        btn.click();
        e.preventDefault();
      }
    });

    $('body').on("keydown", function(e) { 
      if (e.altKey && e.which === 76) {
        var btn = document.getElementById('filtrar');
        btn.click();
        e.preventDefault();
      }
    });

    $('body').on("keydown", function(e) { 
      if (e.altKey && e.which === 82) {
        window.location.reload();
      }
    });
  </script>
  <?php
    echo '
    <form autocomplete="off" action="?modulo=segmentos&accion=index&update=1" method="post" onsubmit="return checkSubmitenviar();">
    <div id="filter-panel" class="collapse filter-panel col-md-12 mb-2 form-inline">

      <div class="mb-5">
        <label>Filtrar por nombre:</label>
        <input type="text" name="nmb" value="'.$nmb.'" size="30" placeholder="Nombre o fragmento del usuario" onfocus="this.select();" autofocus class="form-control">
      </div>
      <div class="mb-5">
        <label>Filtrar:</label><br>
        <button  id="filtro" type="button" class="btn btn-info"> <i class="fa fa-search5"></i> Buscar </button>
      </div>
      <!-- 
      <div class="mb-5">
        <label>Filtrar por estatus:</label><br>
        <input type="checkbox" name="estatus" '.$chest.' data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" data-on="Activos" data-off="Inactivos" onchange="submit();" >
      </div> -->
    </div>
    </form>';


    echo '<div class="">
    <div class="">';

    echo '<div class="col-12">
    <div class="table-responsive">
    <center><table width="100%" class="mb-0 table table-hover table-bordered table-striped">
    <thead class="bg-light-blue bg-darken-2 text-white">
    <tr>
    <th width="15%"><b>Nombre</b></th>
    <th width="50%"><b>Descripcion</b></th>
    <th width="25%"><b>Acciones</b></th>
    <th width="10%"><b>Estatus</b></th>
    </tr></thead>';
    while($row = $this->model->result->fetch_array()){
      echo '<tr>
      <td>'.$row['cs_nmb'].'</td>
      <td>'.substr($row['cs_descripcion'],0,100).'</td>';

      $sql = 'SELECT COUNT(*) FROM crm_segmentosd WHERE cd_segmento = "'.$row['cs_id'].'"';
      $result = setq($sql);
      list($vals) = $result->fetch_array();

      if($row['cs_estatus'] == "A"){
        echo '<td>
                <a href="?modulo=segmentos&accion=edit&id='.$row['cs_id'].'">
                  <button class="btn btn-primary m-1"><i class="fa fa-pen"></i> Editar
                  </button>
                </a>
                <a href="?modulo=segmentos&accion=show&id='.$row['cs_id'].'">
                  <button class="btn btn-info m-1">
                  <i class="fa fa-plus"></i> Agregar <span class="tag tag-pill tag-secondary">'.$vals.'</span>
                  </button>
                </a>
              </td>';
        echo '<td><div class="alert alert-success text-center">Activo</div></td>';
      }
      else{
        echo '<td>
                <a href="?modulo=segmentos&accion=active&id='.$row['cs_id'].'&estatus=A">
                  <input type="button" value="Activar" class="mb-2 mr-2 btn btn-success"/>
                </a>
              </td>';
        echo '<td><div class="alert alert-success text-danger">Inactivo</div></td>';
      }
      echo '</tr>';
    }
    echo '</table>';
  }

  function edit(){
    echo '
        <div class="d-inline-block dropdown">';
    echo '<a href="?modulo=segmentos&accion=index">
          <button class="mb-2 mr-2 btn btn-warning">
            <i class="fa fa-arrow-left"></i> Atras
          </button>
        </a>';
        //fas fa-fast-backward
    echo '</div>
    <div class="main-card mb-3 card">
    <div class="card-body row">';

    if($this->model->id){
      $accion = 'update';
      $titulo = 'Actualizar Datos';
    }
    else{
      $accion = 'insert';
      $titulo = 'Alta de información';
    }
    if(!$this->model->id){
      $req = "checked";
      $est = "checked";
    }
    else{
      if($this->model->requerido == "1") $req = "checked"; else $req = "";
      if($this->model->estatus == "A") $est = "checked"; else $est = "";
    }

    ?>
    <script>
      function sendval(){
        document.getElementById("sologas").value = "1";
        var nmbSegmento = document.getElementById("nmb").value;
        
        if(nmbSegmento !== null && nmbSegmento !== "")
          document.fprov.submit();
      }
    </script>
    <?php
    echo '<div class="col-md-12 center">
    <div class="alert alert-primary"><h5 class="card-title">'.$titulo.'</h5></div>
    <form autocomplete="off" name="fprov" id="fprov" action="?modulo=segmentos&accion='.$accion.'" method="post" onsubmit="return checkguardar();">
    <input type="hidden" name="solog" value="0" id="sologas" />
    <input type="hidden" name="id" value="'.$this->model->id.'" />
    <div class="form-row text-center">
      <div class="col-md-6">
        <div class="position-relative mb-5">
        <b><label for="nmb">Nombre del segmento</label></b>
        <input type="text" name="nmb" autofocus onfocus="this.select();" id="nmb" value="'.$this->model->nmb.'" placeholder="Nombre" class="form-control" required tabindex="1">
        </div>
      </div>
      <div class="col-md-3">
        <div class="position-relative mb-5">
        <b><label for="requerido" class="text-center">Hacer obligatorio su uso</label></b>
        <br>
        <label class="switch">
          <input type="checkbox" name="requerido" id="requerido" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="danger" '.$req.'>
          <span class="slider round"></span>
        </label>
        </div>
      </div>
      <div class="col-md-3">
        <div class="position-relative mb-5">
        <b><label for="estatus" class="text-center">Estatus</label></b>
        <br>
          <label class="switch">
            <input type="checkbox" name="estatus" '.$est.' class="flipswitch" />
            <span class="slider round"></span>
          </label>
        </div>
      </div>
      <div class="col-md-12">
        <div class="position-relative mb-5">
        <b><label for="descripcion" class="">Describe el uso de tu segmento</label></b>
          <textarea name="descripcion" cols="25" rows="3"  tabindex="2" class="form-control" placeholder="Descripcion del segmento">'.$this->model->descripcion.'</textarea>
        </div>
      </div>';
    echo '</div>
    <center><button id="guardar" role="submit" class="mt-1 btn btn-success" onclick="sendval();"><i class="fas fa-save"></i> Guardar</button>
    <button id="guardar" role="button" class="mt-1 btn btn-primary"> <i class="fas fa-save"></i> <i class="fas fa-plus"></i> Guardar y agregar valores</button></center>
    </form>';
  }

  function show(){
    echo '
        <div class="d-inline-block dropdown">';
    echo '
        <a href="?modulo=segmentos&accion=index"><button class="mb-2 mr-2 btn btn-warning"><i class="fa fa-arrow-left"> Atrás</i></button>
        </a>

        <a href="?modulo=segmentos&accion=index">
          <button class="mb-2 mr-2 btn btn-primary">
            <i class="fa fa-pen"></i> Cambiar información
          </button>
        </a>';

    echo '</div>';

    if($this->model->requerido == "1") $req = "SI"; else $req = "NO";
    if($this->model->estatus == "A") $est = "Activo"; else $est = "Inactivo";

    echo '
    <div class="table-responsive">
    <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th><div class=" alert alert-primary" style="font-size: smaller;">Nombre del segmento</div></th>
      <th><div class="alert alert-primary" style="font-size: smaller;">Descripción</div></th>
      <th><div class="alert alert-primary" style="font-size: smaller;">Obligatorio</div></th>
      <th><div class="alert alert-primary" style="font-size: smaller;">Estatus</div></th>
    </tr>
    </thead>
    <tbody>
    <tr>
      <td><div class="">'.$this->model->nmb.'</div></td>
      <td><div class="">'.$this->model->descripcion.'</div></td>
      <td><div class="">'.$req.'</div></td>
      <td><div class="">'.$est.'</div></td>
    <tr>
    </tbody>
    </table>
    </div>';

    echo '<div class="col-md-12 center"><h5 class="text-primary text-center alert alert-primary" style="font-size: smaller;">Agregar valores al segmento</h5></div>';
    echo '<form method="post" action="?modulo=segmentos&accion=setvalor&id='.$this->model->id.'" autocomplete="off" name="valorseg" onsubmit="checkguardarvalor();">';

    if(isset($_GET['idd'])){
      echo '<input type="hidden" name="editar" value="1">';
      echo '<input type="hidden" name="idd" value="'.$_GET['idd'].'">';
      $valor = busca($_GET['idd'],'crm_segmentosd','cd_segmento = "'.$this->model->id.'" AND cd_id','cd_valor');
      $descripcion = busca($_GET['idd'],'crm_segmentosd','cd_segmento = "'.$this->model->id.'" AND cd_id','cd_descripcion');
    }
    else{
      $valor = NULL;
      $descripcion = NULL;
    }


  ?>
    <script>
      function remove(idd){
        var conf = confirm("¿Deseas eliminar el elemento seleccionado?");
        if(conf == true){
          document.location.href="?modulo=segmentos&accion=delvalor&seg=" + <?php echo $this->model->id ?> + "&idd=" + idd;
        }
      }
      function edit(idd){
        document.location.href="?modulo=segmentos&accion=show&id=" + <?php echo $this->model->id ?> + "&idd=" + idd;
      }
    </script>
  <?php
      echo '<div class="form-row text-center">
              <div class="col-md-2 alert alert-primary" style="font-size: smaller;">Agregar un valor</div>
              <div class="mb-5 col-md-3"><input type="text" onfocus="this.select();" name="valor" value="'.$valor.'" class="form-control" placeholder="Nuevo Valor" required autofocus /></div>
              <div class="mb-5 col-md-5"><input type="text" onfocus="this.select();" name="descripcion" value="'.$descripcion.'" class="form-control" placeholder="¿Que pasa si este número de contactos es mayor?"  /></div>
              <div class="col-md-1 text-xs-center">
                <input type="submit" class="btn btn-success" value="Agregar" id="guardar" />
              </div>
            </div>
            <div class="row"></div>
            <div class="btn-group" role="group" >';
    $sql = 'SELECT * FROM crm_segmentosd WHERE cd_segmento = "'.$this->model->id.'" ORDER BY cd_valor';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      echo '
        <button type="button" class="btn btn-default border rounded-pill m-1"
          data-toggle="tooltip" data-placement="top" title data-original-title="'.$row['cd_descripcion'].'" >
          '.$row['cd_valor'].'
          <i class="actionicon icon-edit2 ml-2" onclick="edit('.$row['cd_id'].');"></i>
          <i class="actionicon fa fa-trash ml-2" onclick="remove('.$row['cd_id'].');"></i>

        </button>';
    }
    echo '</div></form>';


    echo '<div>';
  }
}
?>