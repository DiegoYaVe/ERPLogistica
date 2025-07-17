<?php
ini_set('display_errors', 1);
class proveedores{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelproveedores(isset($obj));
  }

  function index(){
    if(!isset($_REQUEST['nmb'])){
      $_REQUEST['nmb'] = NULL;
      $estatus = "A";
    }
    else{
      if(isset($_REQUEST['estatus'])) $estatus = "A";
      else $estatus  = "I";
    }
    
    if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;
    $this->model->result(trim($_REQUEST['nmb']),$estatus,$_REQUEST['page']);
    $this->view = new viewproveedores($this->model);
    $this->view->browse($_REQUEST['nmb'],$estatus,$_REQUEST['page']);
  }
  function edit(){
    if(!isset($_GET['proveedor'])) $_GET['proveedor'] = NULL;

    $this->model->select($_GET['proveedor']);
    $this->view = new viewproveedores($this->model);
    $this->view->edit();
  }

  function setproveedor(){
    if(isset($_POST['id']))
      $id = $_POST['id'];
    else
      $id = getmax('p_id','proveedores');

    if($_POST['contacto'] == "OK"){
      $this->model->setdata($id,$_POST['nmb'],$_POST['alias'],$_POST['correo'],$_POST['telefono'],$_POST['extension'],$_POST['url'],"A");
      $this->model->setproveedores();
      
      $idcontacto = getmax('pc_id','proveedores_contactos');

      $this->model->setDataContacto($idcontacto,$id,$_POST['areac'],$_POST['nmbc'],$_POST['telefonoc'],$_POST['extensionc'],$_POST['whatsapp'],$_POST['correoc'],$_POST['observaciones']);
      $this->model->setcontacto();
      $this->model->id = $id;
    }else{
      $this->model->setdata($id,$_POST['nmb'],$_POST['alias'],$_POST['correo'],$_POST['telefono'],$_POST['extension'],$_POST['url'],"A");
      $this->model->setproveedores();
    }

    if($_POST['id']) $ok = ""; else $ok = "&ok=1";

    redirect('?modulo=proveedores&accion=edit&proveedor='.$this->model->id.$ok.'');
  }

  function contactos(){
    if(!isset($_GET['idc'])) $_GET['idc'] = NULL;

    $this->model->select($_GET['proveedor']);
    $this->model->resultcontactos($_GET['proveedor']);
    $this->view = new viewproveedores($this->model);
    $this->view->contactos();
  }

  function setcontacto(){
    if(isset($_POST['id'])) $idcontacto = $_POST['id'];
    else $idcontacto = getmax('pc_id','proveedores_contactos');

    $this->model->setDataContacto($idcontacto,$_POST['proveedor'],$_POST['areac'],$_POST['nmbc'],$_POST['telefonoc'],$_POST['extensionc'],$_POST['whatsapp'],$_POST['correoc'],$_POST['observaciones']);
    $this->model->setcontacto();

    redirect('?modulo=proveedores&accion=contactos&proveedor='.$this->model->proveedor);
  }

  function editcontacto(){
    $this->model->select($_GET['proveedor']);
    $this->model->selectcontactos($_GET['idc'],$_GET['proveedor']);
    $this->view = new viewproveedores($this->model);
    $this->view->editcontacto();
  }

  function direcciones(){
    if(!isset($_GET['proveedor'])) $_GET['proveedor'] = NULL;

    $this->model->select($_GET['proveedor']);
    $this->model->resultdir($_GET['proveedor']);
    $this->view = new viewproveedores($this->model);
    $this->view->direcciones();
  }

  function setdirproveedor(){

    if(isset($_POST['predeterminada'])) $this->model->predeterminado = "1"; else $this->model->predeterminado = 0;
    if(!isset($_GET['dir'])) $_GET['dir'] = getmax('ps_id','proveedores_sucursales');

    $this->model->setdataDir($_GET['dir'],$_GET['proveedor'],$_POST['nmbdir'],$_POST['calle'],$_POST['nume'],$_POST['numi'],$_POST['colonia'],$_POST['cp'],$_POST['municipio'],$_POST['estado'],$_POST['pais']);
    $this->model->setdirproveedor();

    redirect('?modulo=proveedores&accion=direcciones&proveedor='.$_GET['proveedor'].'&lastid='.$this->model->id);
  }

  function updateestatus(){
    $sql = 'UPDATE proveedores SET
            p_estatus = "'.$_GET['estatus'].'"
            WHERE p_id = "'.$_GET['id'].'" AND p_empresa = "'.$_SESSION['emp'].'"';
    setq($sql);

    redirect('?modulo=proveedores&accion=edit&proveedor='.$_GET['id'].'');
  }



}

class modelproveedores{
  function select($id){
    $sql = 'SELECT * FROM proveedores WHERe p_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['p_id'];
    $this->alias = $row['p_alias'];
    $this->nmb = $row['p_nmb'];
    $this->correo = $row['p_correo'];
    $this->telefono = $row['p_telefono'];
    $this->extension	 = $row['p_extension'];
    $this->url = $row['p_url'];
    $this->estatus = $row['p_estatus'];
  }

  function result($nmb,$estatus,$page){ //filtro
    $bloque = 10;
    $sql = 'SELECT * FROM proveedores WHERE p_empresa = "'.$_SESSION['emp'].'" AND p_estatus = "'.$estatus.'" ';
    if($nmb) $sql.= ' AND p_nmb LIKE "%'.$nmb.'%" ';
    $sql.=' ORDER BY p_id ASC ';
    $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
    $this->result = setq($sql);
    $this->resultt = setq($sql);
  }

  function setdata($id,$nmb,$alias,$correo,$telefono,$extension,$url,$estatus){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'");
    $cambio = "";

    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->alias = clearvmayus($alias);
    $this->correo = clearvminus($correo);
    $this->telefono = clearvmayus($telefono);
    $this->extension = clearvmayus($extension);
    $this->url = clearvminus($url);
    $this->estatus = clearvmayus($estatus);
  }

  function setproveedores(){
    $sql = 'INSERT INTO proveedores SET
            p_id = "'.$this->id.'",
            p_empresa= "'.$_SESSION['emp'].'",
            p_alias = "'.$this->alias.'",
            p_nmb= "'.$this->nmb.'",
            p_correo= "'.$this->correo.'",
            p_telefono= "'.$this->telefono.'",
            p_extension= "'.$this->extension.'",
            p_url= "'.$this->url.'",
            p_estatus= "'.$this->estatus.'"
            ON DUPLICATE KEY UPDATE
            p_alias = "'.$this->alias.'",
            p_nmb= "'.$this->nmb.'",
            p_correo= "'.$this->correo.'",
            p_telefono= "'.$this->telefono.'",
            p_extension= "'.$this->extension.'",
            p_url= "'.$this->url.'",
            p_estatus= "'.$this->estatus.'"';
    $ok = setq($sql);

    return($ok);
  }

  function resultcontactos($proveedor){
    $sql = 'SELECT * FROM proveedores_contactos WHERE pc_proveedor = "'.$proveedor.'" ORDER BY pc_area';
    $this->resultpc = setq($sql);
  }

  function selectcontactos($id){
    $sql = 'SELECT * FROM proveedores_contactos WHERE pc_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->idc = $row['pc_id'];
    $this->proveedorc = $row['pc_proveedor'];
    $this->areac= $row['pc_area'];
    $this->nmbc = $row['pc_nmb'];
    $this->correoc = $row['pc_correo'];
    $this->telefonoc = $row['pc_telefono'];
    $this->extensionc	 = $row['pc_extension'];
    $this->whatsappc = $row['pc_whatsapp'];
    $this->observacionesc = $row['pc_observaciones'];
  }

  function setDataContacto($id,$proveedor,$areac,$nmbc,$telefonoc,$extensionc,$whatsapp,$correoc,$observaciones){
    mb_internal_encoding("UTF-8");

    $this->id = clearvmayus($id);
    $this->proveedor = clearvmayus($proveedor);
    $this->areac = clearvmayus($areac);
    $this->nmbc = clearvmayus($nmbc);
    $this->telefonoc = clearvmayus($telefonoc);
    $this->extensionc = clearvmayus($extensionc);
    $this->whatsapp = clearvmayus($whatsapp);
    $this->correoc = clearvminus($correoc);
    $this->observaciones = clearvmayus($observaciones);
  }

  function setcontacto(){
    $sql = 'INSERT INTO proveedores_contactos SET
            pc_id = "'.$this->id.'",
            pc_proveedor = "'.$this->proveedor.'",
            pc_area = "'.$this->areac.'",
            pc_nmb = "'.$this->nmbc.'",
            pc_correo = "'.$this->correoc.'",
            pc_telefono = "'.$this->telefonoc.'",
            pc_extension = "'.$this->extensionc.'",
            pc_whatsapp = "'.$this->whatsapp.'",
            pc_observaciones = "'.$this->observaciones.'"
            ON DUPLICATE KEY UPDATE
            pc_area = "'.$this->areac.'",
            pc_nmb = "'.$this->nmbc.'",
            pc_correo = "'.$this->correoc.'",
            pc_telefono = "'.$this->telefonoc.'",
            pc_extension = "'.$this->extensionc.'",
            pc_whatsapp = "'.$this->whatsapp.'",
            pc_observaciones = "'.$this->observaciones.'"';
    setq($sql);
  }

  function resultdir($id){
    $sql = 'SELECT * FROM proveedores_sucursales WHERE ps_proveedor = "'.$id.'"
            ORDER BY ps_predeterminada DESC,ps_id ASC';
    $this->resultdir = setq($sql);
    $this->id = $id;
  }

  function setdataDir($dir,$proveedor,$nmbdir,$calle,$nume,$numi,$colonia,$cp,$municipio,$estado,$pais){
    $this->dir = clearvmayus($dir);
    $this->proveedor = clearvmayus($proveedor);
    $this->nmbdir = clearvmayus($nmbdir);
    $this->calle = clearvmayus($calle);
    $this->nume = clearvmayus($nume);
    $this->numi = clearvmayus($numi);
    $this->colonia = clearvmayus($colonia);
    $this->cp = clearvmayus($cp);
    $this->municipio = clearvmayus($municipio);
    $this->estado = clearvmayus($estado);
    $this->pais = clearvmayus($pais);
  }

  function setdirproveedor(){
    $sql = 'INSERT INTO proveedores_sucursales SET
            ps_id = "'.$this->dir.'",
            ps_nmb = "'.$this->nmbdir.'",
            ps_proveedor = "'.$this->proveedor.'",
            ps_calle = "'.$this->calle.'",
            ps_nume = "'.$this->nume.'",
            ps_numi = "'.$this->numi.'",
            ps_colonia = "'.$this->colonia.'",
            ps_municipio = "'.$this->municipio.'",
            ps_cp = "'.$this->cp.'",
            ps_estado = "'.$this->estado.'",
            ps_predeterminada = "0",
            ps_pais = "'.$this->pais.'"
            ON DUPLICATE KEY UPDATE
            ps_calle = "'.$this->calle.'",
            ps_nume = "'.$this->nume.'",
            ps_numi = "'.$this->numi.'",
            ps_colonia = "'.$this->colonia.'",
            ps_municipio = "'.$this->municipio.'",
            ps_cp = "'.$this->cp.'",
            ps_estado = "'.$this->estado.'",
            ps_pais = "'.$this->pais.'"';
    setq($sql);

    if(isset($_POST['nmbdir'])){
      $sqlnm = 'UPDATE proveedores_sucursales SET ps_nmb = "'.$this->nmbdir.'" WHERE ps_id = "'.$this->dir.'"';
      setq($sqlnm);
    }

    if($this->predeterminado == "1"){
      $sql0 = 'UPDATE proveedores_sucursales SET ps_predeterminada = "0" WHERE ps_proveedor = "'.$this->proveedor.'"';
      setq($sql0);

      $sql1 = 'UPDATE proveedores_sucursales SET ps_predeterminada = "1"
               WHERE ps_id = "'.$this->dir.'" AND ps_proveedor = "'.$this->proveedor.'"';
      setq($sql1);
    }
  }
}

class viewproveedores{
  var $model;
  function __construct($model){
    ?>
    <script>
    function checkguardar(){
      document.getElementById("sendform").innerHTML = "Guardando";
      document.getElementById("sendform").disabled = true;
      return true;
    }
    $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 78) {
          var btn = document.getElementById("nuevo");
          btn.click();
          e.preventDefault();
        }
      });
  
      $("body").on("keydown", function(e) { 
        if (e.altKey && e.which === 76) {
          $("#nmb").focus();
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
      $this->model = $model;
      $this->tipoc = array("C"=>"Cliente","P"=>"Prospécto");
      $this->arrtipodom = array("1"=>"Oficina","2"=>"Particular","3"=>"Entrega","4"=>"Adicional","5"=>"Punto de entrega");
  }

  function browse($nmb,$estatus,$page){
    /*$numres = 50;
    $nr = $this->model->resultt->num_rows;
    $np = $nr/$numres; */

    if($estatus == "A") $chest = "checked"; else $chest = "";

    $botones = '';
    $botones .= '
      <a id="nuevo" href="?modulo=proveedores&accion=edit" >
        <button type="button" class="btn btn-sm btn-primary mb-1"><i class="fa fa-plus"></i> Nuevo </button>
      </a>';

    $filtro = '';
    $filtro .= '
      <form autocomplete="off" action="?modulo=proveedores&accion=index" method="post" onsubmit="return checkSubmitenviar();" class="row form-inline mb-1" id="filtro">
      <input name="page" id="page" value="'.$page.'" hidden> 
      <div class="mb-5">
        <div class="mb-5">
          <label class="">Filtrar por nombre:</label>
          <input onkeyup="ceropage();" type="text" id="nmb" name="nmb" value="'.$nmb.'" size="30" placeholder="Nombre de la empresa o fragmento" onfocus="this.select();" autofocus class="form-control">
          <script>
            function ceropage(){
              document.getElementById("page").value = 0;
              var codigo = event.which || event.keyCode;
              if(codigo === 13) {
                mandar(0);
              }
            }
          </script>
        </div>
      </div>
      <div class="mb-5">
        <div class="mb-5">
          <label class="">Filtrar por estatus:</label><br>
          <input type="checkbox" name="estatus" '.$chest.' data-toggle="toggle" id="toogle-estatus" data-size="medium" data-onstyle="success" data-offstyle="danger" data-on="Activos" data-off="Inactivos" >
        </div>
      </div>
      <div class="mb-5">
        <label>Acciones: </label><br>
        <button type="button" onclick="mandar(0)" class="btn btn-info"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="?modulo=proveedores&accion=index" class="btn btn-warning"><i class="fa fa-times"></i> Limpiar</a>
      </div>
      </form>
    ';

    echo '';

    toolbar($_GET['modulo'], $botones,$filtro);
    echo '
    <div class="card mt-3">
    <div class="card-body" >';

    echo '<div class="col-md-12 col-12 col-sm-12">
    <div class="table-responsive">
    <center>
    <table class="mb-0 table table-hover table-striped" id="myTable">
    <thead class="bg-light-blue bg-darken-2 ">
    <tr>
    <th><b>Alias</b></th>
    <th><b>Nombre</b></th>
    <th><b>Telefono</b></th>
    <th><b>Correo</b></th>
    <th><b>Acciones</b></th>
    </tr></thead>';
    $i = 0;
    while($row = $this->model->result->fetch_array()){

      echo '<tr>
        <th>'.$row['p_alias'].'</th>
        <td>'.$row['p_nmb'].'</td>
        <td><a href="tel:'.$row['p_telefono'].'">'.$row['p_telefono'].'</a></td>
        <td><a href="mailto:'.$row['p_correo'].'">'.$row['p_correo'].'</a></td>
        <td>
          <button type="button" class="btn btn-primary"
            data-kt-menu-trigger="click"
            data-kt-menu-placement="bottom-start">
            <i class="fas fa-cogs"></i> Opciones
          </button>
          <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-200px py-4"
            data-kt-menu="true">
            <div class="menu-item px-3">
              <a href="?modulo=proveedores&accion=edit&proveedor='.$row['p_id'].'" class="menu-link px-3">Editar</a>
            </div>
            <div class="menu-item px-3">
              <a class="menu-link px-3" href="?modulo=proveedores&accion=direcciones&proveedor='.$row['p_id'].'">Direcciones</a>
            </div>
            <div class="menu-item px-3">
              <a class="menu-link px-3" href="?modulo=proveedores&accion=contactos&proveedor='.$row['p_id'].'">Contactos</a>
            </div>
            <div class="menu-item px-3">
              <a class="menu-link px-3" target="_BLANK" href="?modulo=recepciones&accion=index&proveedor='.$row['p_id'].'">Compras</a>
            </div>
          </div>
        </td>
      </tr>';
    }
    echo '
    ';
    echo '</table>
    </div>
    <script>
      $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
      });
    </script>
    ';

    //filtro

    /* if($_REQUEST['page'] == 0){
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
          
    $bloque = 10;
    $sqlpg = 'SELECT COUNT(*) FROM proveedores WHERE p_empresa = "'.$_SESSION['emp'].'" AND p_estatus = "'.$estatus.'" ';
    if($nmb) $sqlpg.= ' AND p_nmb LIKE "%'.$nmb.'%" ';
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
      </div>'; */  

  }

  function edit(){
      if($this->model->id) $titulo = 'Actualizar datos básicos de '.$this->model->alias;
      else $titulo = 'Registro básico de proveedor';
      ?>
      <script>
      window.onload=function(){
        const valores = window.location.search;
        const urlParams = new URLSearchParams(valores);
        var actividad = urlParams.get('ok');
        if(actividad !== "undefined" && actividad !== null){
          //$('#nuevoarticulo').show(5000);
          //setInterval($('#nuevoarticulo').hide(3000),3000);}
          $('#nuevoarticulo').delay(3000).slideUp(400);
          //$('#nuevoarticulo').fadeOut(3000);
          
          
        }
      }
      </script>
      <?php

      $botones = '';
      $botones .= '
        <a href="?modulo=proveedores&accion=index">
          <button class="btn btn-warning btn-sm"><i class="fa fa-arrow-left"></i> Atrás</button>
        </a>';
        if($this->model->estatus == "A") $botones .= '<button class="btn btn-sm btn-danger" onclick="editestatus(\'I\');"><i class="fa fa-times"></i> Desactivar Proveedor</button>';
        else $botones .= '<button class="btn btn-success btn-sm" onclick="editestatus(\'A\');"><i class="fa fa-check"></i> Activar Proveedor</button>';
        echo '
        <script>
          function editestatus(estatus){
            var c = confirm("¿Estas seguro de cambiar el estatus del proveedor?");
            if(c){
              window.location.href="?modulo=proveedores&accion=updateestatus&id='.$this->model->id.'&estatus="+estatus;
            }
          }
        </script>';
      toolbar($_GET['modulo'],$botones);
    echo '
    <div class="row mt-3">
      <div class="col-md-9">
        <div class="card card-body">
          <div class="card-header p-1">
            <h4 class="card-title" id="basic-layout-form">'.$titulo.'</h4>
            <p style="font-size: small;"><i> (*) Campos Obligatorios</i></p>
            <!--<a class="heading-elements-toggle"><i class="fas fa-ellipsis-h"></i></a>
            <div class="heading-elements">
              <ul class="list-inline mb-0">
                <li><a data-action="collapse"><i class="fas fa-minus"></i></a></li>
                <li><a data-action="expand"><i class="fas fa-expand-alt"></i></a></li>
                <li><a data-action="close"><i class="fas fa-times"></i></a></li>
              </ul>
            </div>-->
          </div>';

          echo '<form autocomplete="off" name="prvedit" id="prvedit" action="?modulo=proveedores&accion=setproveedor" method="post" onsubmit="checkguardar();" >';
            if($this->model->id) echo '<input type="hidden" name="id" value="'.$this->model->id.'" />';
            echo '<div class="card-body">
              <div class="card-block">
                <div class="form-body">
                  <div class="row p-1">';
                  if($_GET['ok']){
                    echo'<div class="col-md-12 alert alert-green h6" id="nuevoarticulo">
                      <center>
                        <b>Proveedor agregado correctamente</b>
                      </center>
                    </div>';
                  }
                  echo '
                    <div class="col-md-8 col-sm-12 mb-5">
                      <div class="input-group-desc">
                        <label class="name">Nombre empresa*</label>
                        <input class="form-control" autocomplete="off" type="text" value="'.$this->model->nmb.'" name="nmb" id="nmb" required="required" autofocus placeholder="Nombre de la empresa o persona proveedora">
                      </div>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-5">
                      <div class="input-group-desc">
                        <label class="label--desc">Nombre corto de tu proveedor*</label>
                        <input class="input--style-3 form-control" type="text" value="'.$this->model->alias.'" name="alias" id="alias" placeholder="Asigna un nombre corto a tu proveedor">
                      </div>
                    </div>
                    <div class="col-md-4 col-sm-8 mt-2 mb-5">
                      <div class="input-group-desc">
                        <label class="label--desc">Teléfono principal</label>
                        <input class="input--style-3 form-control" type="tel" value="'.$this->model->telefono.'" name="telefono" id="telefono" placeholder="Teléfono principal" data-toggle="tooltip" data-placement="top" title="Telefono para principal para contacto " >
                      </div>
                    </div>
                    <script>
                    $("#telefono").keyup(function(){              
                            var ta      =   $("#telefono");
                            letras      =   ta.val().replace(/ /g, "");
                            ta.val(letras)
                    }); 
                    </script>
                    <div class="col-md-2 col-sm-4 mt-2 mb-5">
                      <div class="input-group-desc">
                        <label class="label--desc">Extensión</label>
                        <input class="input--style-3 form-control" type="text" value="'.$this->model->extension.'" name="extension" id="extension" placeholder="extension" >
                      </div>
                    </div>
                    <div class="col-md-6 col-sm-12 mt-2 mb-5">
                      <div class="input-group-desc">
                        <label class="label--desc">Correo principal*</label>
                        <input class="input--style-3 form-control" type="email" style="text-transform:lowercase;" value="'.$this->model->correo.'" name="correo" id="correo" placeholder="Correo electrónico" data-toggle="tooltip" data-placement="top" title="Señala un correo electrónico por default para enviar información a tu proveedor" required="required">
                      </div>
                    </div>
                    <div class="col-md-12 col-sm-12 mt-2 mb-5">
                      <div class="input-group-desc">
                        <label class="label--desc">URL o dirección del proveedor</label>
                        <input class="input--style-3 form-control" type="text" value="'.$this->model->url.'" name="url" id="url" placeholder="Sitio Web para obtener información del proveedor">
                      </div>
                    </div>';
                  if(!$this->model->id){
                    echo '
                    
                    <div class="col-md-12 mt-2"><hr><h5>Registro de contacto</h5></div>
                    <input trype="text" name="contacto" value="OK" hidden>
                    <div class="col-md-5 col-sm-12 mb-5">
                      <div class="input-group-desc">
                        <label class="name">Area del contacto</label>
                        <input class="form-control" autocomplete="off" type="text" value="" name="areac" id="areac" required="required" autofocus placeholder="Ejem. Gerencia, ventas, Compras, Etc.">
                      </div>
                    </div>
                    <div class="col-md-7 col-sm-12 mb-5">
                      <div class="input-group-desc">
                        <label class="label--desc">Nombre del encargado</label>
                        <input class="input--style-3 form-control" type="text" value="" name="nmbc" id="nmbc" required="required" placeholder="Encargado del área">
                      </div>
                    </div>
                  </div>
                  <div class="col-md-5 col-sm-12 mb-5">
                    <div class="input-group-desc">
                      <label class="label--desc">Telefono principal</label>
                      <input class="input--style-3 form-control" type="tel" value="" name="telefonoc" id="telefonoc"  placeholder="Teléfono fijo de contacto">
                    </div>
                  </div>

                  <div class="col-md-2 col-sm-4 mb-5">
                    <div class="input-group-desc">
                      <label class="label--desc">Extensión</label>
                      <input class="input--style-3 form-control" type="text" value="" name="extensionc" id="extensionc" placeholder="Extensión" >
                    </div>
                  </div>

                  <div class="col-md-5 col-sm-12 mb-5">
                    <div class="input-group-desc">
                      <label class="label--desc">Whatsapp / Celular</label>
                      <input class="input--style-3 form-control" type="tel" value="" name="whatsapp" id="whatsapp" placeholder="Número celular" data-toggle="tooltip" data-placement="top" title="Celular del contacto" >
                    </div>
                  </div>
                </div>
                <div class="col-md-5 col-sm-8 mb-5">
                  <div class="input-group-desc">
                    <label class="label--desc">Correo electrónico</label>
                    <input class="input--style-3 form-control" type="email" style="text-transform:lowercase;" value="" name="correoc" id="correoc" placeholder="correo Electrónico de tu contacto">
                  </div>
                </div>

                <div class="col-md-7 col-sm-12 mb-5">
                  <div class="input-group-desc">
                    <label class="label--desc">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Si tienes alguna anotación sobre el contacto, ponla aqui"></textarea>
                  </div>
                </div>';
                  }
                  echo '
                  </div>
                  <div class="row  mt-1 mb-1">
                    <center><button type="submit" id="sendform" class="btn btn-success" ><i class="fa fa-save"></i> Guardar</button></center>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>';
    if($this->model->id){
      echo '<div class="col-md-3">
        <div class="card card-body">
          <div class="card-header p-1">
            <h4 class="card-title" id="basic-layout-form">Actividades</h4>
            <!--<a class="heading-elements-toggle"><i class="fas fa-ellipsis-h"></i>Regresar </a>
            <div class="heading-elements">
              <ul class="list-inline mb-0">
                <li><a data-action="collapse"><i class="fas fa-minus"></i></a></li>
              </ul>
            </div>-->
          </div>';
        $this->actividades();
    }
  }

function contactos(){
  $titulo = 'Contactos de '.$this->model->nmb;
  echo '<div class="form-inline mb-5 col-md-12">';
  $atras = '<a href="?modulo=proveedores&accion=index">
          <button class="btn btn-warning"><i class="fa fa-arrow-left"></i>
            Atrás
          </button>
        </a>';
  $botones = '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adddireccion"><i class="fas fa-user-plus"></i>
            Agregar contacto
          </button>';
  echo '<!-- 
        <a href="?modulo=proveedores&accion=edit&proveedor='.$this->model->id.'">
          <button class="btn btn-info"><i class="fas fa-truck"></i>
            Regresar a '.$this->model->nmb.'
          </button>
        </a> -->';
  echo '</div>';

  toolbar("PROVEEDORES", $atras, "", $botones);

  /************Modal*/
  echo '<div class="modal fade" id="adddireccion" tabindex="-1" role="dialog" aria-labelledby="ModalFiscales" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header mb-2">
                <button type="button" class="close" data-bs-dismiss="modal" aria-bs-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <label class="modal-title text-text-bold-600" id="ModalFiscales">Añadir Nuevo contacto</label>
                <p style="font-size: small;"><i> (*) Campos Obligatorios</i></p>
              </div>
        <form autocomplete="off" name="contprovee" id="contprovee" action="?modulo=proveedores&accion=setcontacto" method="post" >
        <input type="hidden" name="proveedor" value="'.$this->model->id.'" />';

  echo '<div class="card-body">
          <div class="card-block">
            <div class="form-body">
              <div class="row">
                <div class="col-md-5 col-sm-12">
                  <div class="input-group-desc">
                    <label class="name">Area del contacto*</label>
                    <input class="form-control" autocomplete="off" type="text" value="" name="areac" id="areac" required="required" autofocus placeholder="Ejem. Gerencia, ventas, Compras, Etc.">
                  </div>
                </div>

                <div class="col-md-7 col-sm-12">
                  <div class="input-group-desc">
                    <label class="label--desc">Nombre del encargado*</label>
                    <input class="input--style-3 form-control" type="text" value="" name="nmbc" id="nmbc" required="required" placeholder="Encargado del área">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-5 col-sm-12 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Telefono principal</label>
                    <input class="input--style-3 form-control" type="tel" value="" name="telefonoc" id="telefonoc"  placeholder="Teléfono fijo de contacto">
                  </div>
                </div>

                <div class="col-md-2 col-sm-4 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Extensión</label>
                    <input class="input--style-3 form-control" type="text" value="" name="extensionc" id="extensionc" placeholder="Extensión" >
                  </div>
                </div>

                <div class="col-md-5 col-sm-12 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Whatsapp / Celular</label>
                    <input class="input--style-3 form-control" type="tel" value="" name="whatsapp" id="whatsapp" placeholder="Número celular" data-toggle="tooltip" data-placement="top" title="Celular del contacto" >
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-5 col-sm-8 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Correo electrónico</label>
                    <input class="input--style-3 form-control" type="email" style="text-transform:lowercase;" value="" name="correoc" id="correoc" placeholder="correo Electrónico de tu contacto">
                  </div>
                </div>

                <div class="col-md-7 col-sm-12 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Si tienes alguna anotación sobre el contacto, ponla aqui"></textarea>
                  </div>
                </div>
            </div>
          <div class="row  mt-1">
            <center><button type="submit" id="sendform" class="btn btn-success" ><i class="fa fa-save"></i> Guardar</button></center>
          </div>
        </div>
      </div>
    </div>
  </form></div>
          </div>
        </div>';
  /*/**************Fin modal*/
 echo '
  <div class="row">
    <div class="col-md-9">
      <div class="card">
        <div class="card-header p-1">
          <h4 class="card-title" id="basic-layout-form">'.$titulo.'</h4>
          <!--<a class="heading-elements-toggle"><i class="fas fa-ellipsis-h"></i></a>
          <div class="heading-elements">
          </div>-->
        </div>';
  echo '<div class="table-responsive">
    <table class="table  table-hover table-striped">
    <thead class="bg-light-blue bg-darken-2 text-white">
    <tr>
    <th><b>Área</b></th>
    <th><b>Nombre</b></th>
    <th><b>Telefono (s)</b></th>
    <th><b>Correo</b></th>
    <th><b></b></th>
    <th><b></b></th>
    </tr></thead>';
  while($row = $this->model->resultpc->fetch_array()){
    if(!empty($row['pc_extension'])) $ext = 'Ext. '.$row['pc_extension']; else $ext = "";
    if(empty($row['pc_observaciones'])) $row['pc_observaciones'] = "Sin observaciones";

    if($row['pc_whatsapp']) $icon = '<i class="fas fa-mobile"></i> '; else $icon = '';

    echo '<tr>
            <th>'.$row['pc_area'].'</th>
            <th>'.$row['pc_nmb'].'</th>
            <th class="">
              <br>
              <a href="tel:'.$row['pc_telefono'].'"><i class="fas fa-mobile"></i> '.$row['pc_telefono'].' '.$ext.'</a>
              <br><br>
              <a href="tel:'.$row['pc_whatsapp'].'">'.$icon.$row['pc_whatsapp'].'</a>
            </th>
            <th><a href="mailto:'.$row['pc_correo'].'">'.$row['pc_correo'].'</a></th>
            <th>
              <button class="btn btn-secondary" data-toggle="tooltip" data-bs-placement="top" title="'.$row['pc_observaciones'].'" >
                <i class="fas fa-book"></i>
              </button>
            </th>
            <th>
              <a href="?modulo=proveedores&accion=editcontacto&proveedor='.$row['pc_proveedor'].'&idc='.$row['pc_id'].'">
                <button class="btn btn-sm btn-primary"><i class="fa fa-pen"></i>Editar</button>
              </a>
            </th>
          </tr>';
  }

  echo '</table>';
  echo '</div></div></div>';

  echo '<div class="col-md-3">
          <div class="card">
            <div class="card-header p-1">
              <h4 class="card-title" id="basic-layout-form">Actividades</h4>
              <!--<a class="heading-elements-toggle"><i class="fas fa-ellipsis-h"></i>Regresar </a>
              <div class="heading-elements">
                <ul class="list-inline mb-0">
                  <li><a data-action="collapse"><i class="fas fa-minus"></i></a></li>
                </ul>
              </div>-->
            </div>';
  $this->actividades();
}

function editcontacto(){
  $titulo = 'Editar contacto '.$this->model->areac.' de '.$this->model->alias;
  echo '<div class="mb-5 col-md-12 form-inline">';
  $atras =  '<a href="?modulo=proveedores&accion=contactos&proveedor='.$_GET['proveedor'].'">
          <button class="btn btn-warning"><i class="fa fa-arrow-left"></i>
            Atrás
          </button>
        </a>';
  echo '</div>';

  toolbar("PROVEEDORES", $atras);

 echo '
  <div class="row">
    <div class="col-md-9">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title" id="basic-layout-form">'.$titulo.'</h4>
          <p style="font-size: small;"><i> (*) Campos Obligatorios</i></p>
          <!--<a class="heading-elements-toggle"><i class="fas fa-ellipsis-h"></i></a>
          <div class="heading-elements">
            <ul class="list-inline mb-0">
              <li><a data-action="collapse"><i class="fas fa-minus"></i></a></li>
              <li><a data-action="expand"><i class="fas fa-expand-alt"></i></a></li>
              <li><a data-action="close"><i class="fas fa-times"></i></a></li>
            </ul>
          </div>-->
        </div>';
  echo '
        <form autocomplete="off" name="contprovee" id="contprovee" action="?modulo=proveedores&accion=setcontacto" method="post" >
        <input type="hidden" name="proveedor" value="'.$this->model->id.'" />';

  if($this->model->idc)
    echo '<input type="hidden" name="id" value="'.$this->model->idc.'" />';

  echo '<div class="card-body">
          <div class="card-block">
            <div class="form-body">
              <div class="row">
                <div class="col-md-5 col-sm-12">
                  <div class="input-group-desc">
                    <label class="name">Area del contacto*</label>
                    <input class="form-control" autocomplete="off" type="text" value="'.$this->model->areac.'" name="areac" id="areac" required="required" autofocus placeholder="Ejem. Gerencia, ventas, Compras, Etc.">
                  </div>
                </div>

                <div class="col-md-7 col-sm-12">
                  <div class="input-group-desc">
                    <label class="label--desc">Nombre del encargado*</label>
                    <input class="input--style-3 form-control" type="text" value="'.$this->model->nmbc.'" name="nmbc" id="nmbc" required="required" placeholder="Encargado del área">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-5 col-sm-12 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Telefono principal</label>
                    <input class="input--style-3 form-control" type="tel" value="'.$this->model->telefonoc.'" name="telefonoc" id="telefonoc"  placeholder="Teléfono fijo de contacto">
                  </div>
                </div>

                <div class="col-md-2 col-sm-4 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Extensión</label>
                    <input class="input--style-3 form-control" type="text" value="'.$this->model->extensionc.'" name="extensionc" id="extensionc" placeholder="Extensión" >
                  </div>
                </div>

                <div class="col-md-5 col-sm-12 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Whatsapp / Celular</label>
                    <input class="input--style-3 form-control" type="tel" value="'.$this->model->whatsappc.'" name="whatsapp" id="whatsapp" placeholder="Número celular" data-toggle="tooltip" data-placement="top" title="Celular del contacto" >
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-5 col-sm-8 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Correo electrónico</label>
                    <input class="input--style-3 form-control" type="email" style="text-transform:lowercase;" value="'.$this->model->correoc.'" name="correoc" id="correoc" placeholder="correo Electrónico de tu contacto">
                  </div>
                </div>

                <div class="col-md-7 col-sm-12 mt-2">
                  <div class="input-group-desc">
                    <label class="label--desc">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Si tienes alguna anotación sobre el contacto, ponla aqui">'.$this->model->observacionesc.'</textarea>
                  </div>
                </div>
            </div>
          <div class="row  mt-1">
            <center><button type="submit" id="sendform" class="btn btn-success" ><i class="fa fa-save"></i> Guardar</button></center>
          </div>
        </div>
      </div>
    </div>
  </form>';

  echo '</div></div>';
  echo '<div class="col-md-3">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title" id="basic-layout-form">Actividades</h4>
              <!--<a class="heading-elements-toggle"><i class="fas fa-ellipsis-h"></i>Regresar </a>
              <div class="heading-elements">
                <ul class="list-inline mb-0">
                  <li><a data-action="collapse"><i class="fas fa-minus"></i></a></li>
                </ul>
              </div>-->
            </div>';

 $this->actividades();
}

  function direcciones(){
    echo '
        <div class="modal fade" id="adddireccion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header mb-2">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <label class="modal-title text-text-bold-600" id="myModalLabel33">Añadir dirección</label>
                <p style="font-size: small;"><i> (*) Campos Obligatorios</i></p>
              </div>
              <form method="post" action="?modulo=proveedores&accion=setdirproveedor&proveedor='.$this->model->id.'" class="container" name="senddir" onsubmit="checkguardar();">
                <div class="card-body row">
                  <div class="mb-5 col-md-12 align-middle">
                    <label for="calle">Alias del domicilio*</label>
                    <input type="text" name="nmbdir" id="nmbdir" class="form-control" placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" required="required" autofocus />
                  </div>
                  <div class="mb-5 col-12 col-md-6">
                    <label for="calle">Calle*</label>
                    <input type="text" name="calle" id="calle" class="form-control" placeholder="La calle de tu domicilio" title="Se requiere un valor"  required="required" />
                  </div>
                  <div class="mb-5 col-6 col-md-3">
                    <label for="nume">Número Exterior*</label>
                    <input type="text" name="nume" id="nume" class="form-control" placeholder="Número exterior" title="Se requiere un valor"  required="required" />
                  </div>
                  <div class="mb-5 col-6 col-md-3">
                    <label for="numi">Número interior</label>
                    <input type="text" name="numi" id="numi" class="form-control" placeholder="La calle de tu domicilio" title="Se requiere un valor" />
                  </div>
                  <div class="mb-5 col-12 col-md-5">
                    <label for="colonia">Colonia*</label>
                    <input type="text" name="colonia" id="colonia" class="form-control" placeholder="La colonia de tu domicilio" title="Se requiere un valor"  required="required" />
                  </div>
                  <div class="mb-5 col-12 col-md-2">
                    <label for="cp">Código postal*</label>
                    <input type="text" name="cp" id="cp" class="form-control" placeholder="El CP de tu domicilio" title="Se requiere un valor"  required="required" />
                  </div>
                  <div class="mb-5 col-12 col-md-5">
                    <label for="municipio">Ciudad*</label>
                    <input type="text" name="municipio" id="municipio" class="form-control" placeholder="La Ciudad de tu domicilio" title="Se requiere un valor"  required="required" />
                  </div>
                  <div class="mb-5 col-12 col-md-5">
                    <label for="estado">Estado*</label>
                    '.menu_select_db('estado','e_id','e_nmb','','estado','XXX',false, false, false, true,false).'
                  </div>
                  <div class="mb-5 col-12 col-md-3">
                    <label for="pais">Pais*</label>
                    <input type="text" name="pais" id="pais" class="form-control" placeholder="El pais de tu domicilio" title="Se requiere un valor"  required="required" />
                  </div>';
                echo '
                  <div class="mb-5 col-12 col-md-12">
                    <label for="pais">.</label>
                    <button type="submit" class="btn btn-success" id="sendform"><i class="fa fa-save"></i> Guardar</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>';
      
        
      $botones = '';

      $botones .= '
      <a href="?modulo=proveedores&accion=index" >
        <button type="button" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i> Atrás</button>
      </a>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#adddireccion"><i class="fa fa-plus"></i> Agregar dirección </button>
      ';

      toolbar($_GET['modulo'],$botones);

    echo ' 
          <div class="">
            <div class="row" >';
  ?>
  <script>
    function sendval(){
      document.getElementById("sologas").value = "1";
    }
    function iconcollapse(idcol){
      var numdir = document.getElementById("numdirs").value;

      for(var i=1; i <= numdir;i++){
        var nmbdir = document.getElementById("nmbdir" + i).value;
        if(i == idcol){
          document.getElementById("nmbdir" + i).disabled = false;
          document.getElementById("update" + i).style.display = "";
        }
        else{
          document.getElementById("nmbdir" + i).disabled = true;
          document.getElementById("update" + i).style.display = "none";
        }
      }
      var id = document.getElementById("heading"+idcol);
      var id2 = document.getElementById("btn"+idcol);
      var demas = document.getElementsByClassName("activado");
      //var demas2 = document.getElementsByClassName("blanquito"); 
      $(demas).removeClass("activado");
      //$(demas2).removeClass("blanquito");
      id.classList.add("activado");
      //id2.classList.add("blanquito");

      if($("#accordion"+idcol).attr("aria-expanded") == "true"){
        console.log("HOLAA");
        var icon = document.getElementById("drop"+idcol);
        $(icon).removeClass("fas fa-minus");
        icon.classList.add("fas fa-plus");
      }else{
        /*        
        var demasicon = document.getElementsByClassName("fas fa-minus");
        $(demasicon).removeClass("icon-minus");
        $(demasicon).removeClass("fa fa-plus");
        demasicon.classList.add("fa fa-plus"); */

        var icon = document.getElementById("drop"+idcol);
        $(icon).removeClass("fas fa-plus");
        icon.classList.add("fas fa-minus");
      }
    }
    function updatedir(numdir){
      document.getElementsByName("senddir" + numdir)[0].submit();
    }
    function newname(idcol){
      document.getElementById("divname"+idcol).removeAttribute("hidden");
      $("#nmbdir"+idcol).focus();
    }
  </script>
 <?php
 echo '<div id="accordionWrapa1" role="tablist" aria-multiselectable="true" class="col-md-9 mt-3">
        <div class="card card-body">
          <div class="card-header">
            <h4 class="card-title" id="basic-layout-form">Direcciones de '.$this->model->alias.'</h4>
          </div>        ';

 $idcol = 0;
 while($rowd = $this->model->resultdir->fetch_array()){
   if($rowd['ps_predeterminada']) $pred = "checked"; else $pred = "";
   $idcol++;
   if($idcol == 1){ $ariae = ' in '; $icondef = "fas fa-minus"; $showup = ' '; } else{ $ariae = ""; $icondef = "fa fa-plus"; $showup = ' style="display:none;" '; }
   if($pred == "checked"){
      $bck = "activado";
      $blanquito = "blanquito";
      $span = '<span class="tag tag-warning"><i class="fas fa-star"></i></span>';
      $icon = "fas fa-minus";
    }else{ 
      $bck = "";
      $span = "";
      $blanquito = "";
      $icon = "fa fa-plus";
    }
  echo '<div id="heading'.$idcol.'" class="card-header pt-1 rounded desactivado2 '.$bck.'">
          <div class="mb-5 col-md-2 col-xs-4">
              <button type="button" class="btn btn-secondary" onclick="newname('.$idcol.');" data-toggle="tooltip" data-placement="top" data-original-title="Editar nombre de dirección"><i class="fas fa-pen-square"></i></button>
            </div>
            <div class="mb-5 col-md-6 col-xs-8">
              <a id="btn'.$idcol.'" data-toggle="collapse" data-parent="#accordionWrapa1" href="#accordion'.$idcol.'" aria-expanded="true" aria-controls="accordion1" class="card-title lead blanquito '.$blanquito.'" onclick="iconcollapse('.$idcol.')">
                <h6>'.$rowd['ps_nmb'].' <i id="drop'.$idcol.'" class="'.$icon.'"></i></h6>
              </a>
            </div>
            <div class="mb-5 col-md-4 col-xs-12">
              <button type="button" onclick="updatedir('.$idcol.');" class="btn btn-success" id="update'.$idcol.'" '.$showup.'><i class="fa fa-redo"></i> Actualizar</button>
              '.$span.'
            </div>
          </div>
          <form name="senddir'.$idcol.'" method="post" action="?modulo=proveedores&accion=setdirproveedor&proveedor='.$this->model->id.'&dir='.$rowd['ps_id'].'" id="accordion'.$idcol.'" role="tabpanel" aria-labelledby="heading'.$idcol.'" class="card-collapse '.$ariae.'" aria-expanded="true">
            <div class="card-body row p-2">
              <div class="mb-5 col-12 col-md-12 dirname'.$idcol.'" id="divname'.$idcol.'" hidden>
                <label for="calle">Nombre del domicilio</label>
                <input type="text" name="nmbdir" id="nmbdir'.$idcol.'" class="form-control" data-parent="#acordendirecciones" h placeholder="Con que nombre identificamos esta dirección" title="Se requiere un nombre corto" value="'.$rowd['ps_nmb'].'" required="required" />
              </div>
              <div class="mb-5 col-12 col-md-7">
                <label for="calle">Calle</label>
                <input type="text" name="calle" id="calle'.$idcol.'" class="form-control" placeholder="La calle de tu domicilio" title="Se requiere un valor"  required="required" step="2" value="'.$rowd['ps_calle'].'" />
              </div>';
          echo '
              <div class="mb-5 col-6 col-md-2">
                <label for="nume">Número Exterior</label>
                <input type="text" name="nume" id="nume'.$idcol.'" class="form-control" placeholder="Número exterior" title="Se requiere un valor"  required="required" step="4" value="'.$rowd['ps_nume'].'" />
              </div>
              <div class="mb-5 col-6 col-md-3">
                <label for="numi">Número interior</label>
                <input type="text" name="numi" id="numi'.$idcol.'" class="form-control" placeholder="La calle de tu domicilio" title="Se requiere un valor" step="5" value="'.$rowd['ps_numi'].'" />
              </div>
              <div class="mb-5 col-12 col-md-5">
                <label for="colonia">Colonia</label>
                <input type="text" name="colonia" id="colonia'.$idcol.'" class="form-control" placeholder="La colonia de tu domicilio" title="Se requiere un valor"  required="required" step="6" value="'.$rowd['ps_colonia'].'" />
              </div>
              <div class="mb-5 col-12 col-md-2">
                <label for="cp">Código postal</label>
                <input type="text" name="cp" id="cp'.$idcol.'" class="form-control" placeholder="El CP de tu domicilio" title="Se requiere un valor"  required="required" step="7" value="'.$rowd['ps_cp'].'" />
              </div>
              <div class="mb-5 col-12 col-md-5">
                <label for="municipio">Ciudad</label>
                <input type="text" name="municipio" id="municipio'.$idcol.'" class="form-control" placeholder="La Ciudad de tu domicilio" title="Se requiere un valor"  required="required" step="8" value="'.$rowd['ps_municipio'].'" />
              </div>
              <div class="mb-5 col-12 col-md-4">
                <label for="estado">Estado</label>
                '.menu_select_db('estado','e_id','e_nmb',$rowd['ps_estado'],'estado','XXX',false, false, false, true,false).'
              </div>
              <div class="mb-5 col-12 col-md-3">
                <label for="pais">Pais</label>
                <input type="text" name="pais" id="pais'.$idcol.'" class="form-control" placeholder="El pais de tu domicilio" title="Se requiere un valor"  required="required" step="10" value="'.$rowd['ps_pais'].'" />
              </div>
              <div class="mb-5 col-12 col-md-2">
                <center>
                  <label for="predeterminada">Predeterminada</label><br>
                  <input type="checkbox" name="predeterminada" id="predeterminada'.$idcol.'" data-toggle="toggle" data-on="Si" data-off="No" data-onstyle="success" data-offstyle="danger" '.$pred.'>
                </center>
              </div>
            </div>
          </form>
        ';
  }
  echo '</div>
      </div>';
 echo '<input type="hidden" id="numdirs" value="'.$idcol.'">';

  echo '<div class="col-md-3 mt-3">
          <div class="card card-body">
            <div class="card-header p-1">
              <h4 class="card-title" id="basic-layout-form">Actividades</h4>
              <!--<a class="heading-elements-toggle"><i class="fas fa-ellipsis-h"></i>Regresar </a>
              <div class="heading-elements">
                <ul class="list-inline mb-0">
                  <li><a data-action="collapse"><i class="fas fa-minus"></i></a></li>
                </ul>
              </div>-->
            </div>';

 echo $this->actividades();
 }



function actividades(){
    if($_GET['accion'] == "edit") $alerta = 'class="alert alert-info"'; else $alerta = "";
    if($_GET['accion'] == "direcciones") $alertsuc = 'class="alert alert-info"'; else $alertsuc = "";
    if($_GET['accion'] == "contactos") $alertcont = 'class="alert alert-info"'; else $alertcont = "";

  echo '<div class="card-body ">
          <div class="card-block">
            <div class="form-body">
              <div class="row">
                <div class="col-md-12 col-sm-12">
                  <table class="table table-striped table-bordered table-hover">
                    <thead class="bg-light-blue bg-darken-2 ">
                      <tr><th>Elije una actividad</th></tr>
                    <thead>
                    <tbody>
                      <tr>
                        <th '.$alerta.'><a href="?modulo=proveedores&accion=edit&proveedor='.$this->model->id.'">
                          <i class="fas fa-street-view warning"></i> Datos del Proveedor </a>
                        </th>
                      </tr>
                      <tr>
                        <th '.$alertsuc.'><a href="?modulo=proveedores&accion=direcciones&proveedor='.$this->model->id.'">
                          <i class="fas fa-home warning"></i> Direcciones </a>
                        </th>
                      </tr>
                      <tr>
                        <th '.$alertcont.'><a href="?modulo=proveedores&accion=contactos&proveedor='.$this->model->id.'">
                          <i class="fas fa-male warning"></i> Contactos </a>
                        </th>
                      </tr>
                      <!-- <tr>
                        <th><i class="far fa-clock warning"></i> Historial</th>
                      </tr> -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>';
  echo '</div>';
}

}
?>