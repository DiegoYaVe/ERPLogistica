<?php
/* ini_set('display_errors', 1); */
  class usuarios{ 
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelusuarios(isset($obj));
    }
    
    function index(){
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = NULL;
      if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
      if(!isset($_REQUEST['grupo'])) $_REQUEST['grupo'] = NULL;

      /*if(!isset($_REQUEST['update'])){ $estatus = "A"; }
      else{
        if(isset($_REQUEST['estatus'])) $estatus = "A";
        else $estatus  = "I";
      }*/
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "A";

      $this->model->result($_REQUEST['page'],trim($_REQUEST['nmb']),$_REQUEST['grupo'],$_REQUEST['estatus']);
      $this->view = new viewusuarios($this->model);
      $this->view->browse($_REQUEST['page'],$_REQUEST['nmb'],$_REQUEST['grupo'],$_REQUEST['estatus']);
    }
    function edit(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;

      if($_SESSION['emp'] != 1) $licenciausu = busca($_SESSION['emp'],'empresa_licencia','el_empresa','el_usuarios');
      else $licenciausu = 1000;
      $sqltotusu = 'SELECT COUNT(*) FROM usuarios WHERE u_empresa = "'.$_SESSION['emp'].'"';
      $resultusu = setq($sqltotusu);
      list($usuarios) = $resultusu->fetch_array();
      if($usuarios >= $licenciausu && $_GET['id'] == NULL){
        echo '
        <script>
            alert("La cantidad de usuarios en tu licencia ha sido superado. No puedes agregar nuevos usuarios. Contacta a tu agente de JDCEO.");
            window.location.href = "?modulo=usuarios&accion=index";
        </script>
        ';
      }else {
        $this->model->select($_GET['id']);
        $this->view = new viewusuarios($this->model);
        $this->view->edit();
      }
    }
    function setusuario(){
      if(isset($_POST['notificaciones'])){
        $notificaciones = 1;
      } else {
        $notificaciones = 0;
      }
      $countusu = busca($_SESSION['emp'],'usuarios','u_estatus = "A" AND u_empresa','COUNT(*)');
      $usulicencia = busca($_SESSION['emp'],'empresa_licencia','el_empresa','el_usuarios');
      if($countusu == $usulicencia && isset($_POST['estatus'])) {
        echo '<script>
          alert("La cantidad de usuarios en tu licencia ha sido superado. No puedes activar el usuario actual. Contacta a tu agente de JD CEO");
          window.location.href = "?modulo=usuarios&accion=index";
        </script>';
        die();
      }

      if(isset($_POST['nuser'])) $nuser = $_POST['nuser']; else $nuser = getmax("u_nuser","usuarios");
      
      $this->model->setdata($nuser,$_POST['id'],$_POST['nmb'],$_POST['apellidos'],$_POST['correo'],$_POST['telefono'],$_POST['puesto'],$_POST['nacimiento'],$_POST['grupo'],$_POST['comment'],$_POST['estatus'],$_POST['mailcorp'],$_POST['passcorp'],$_POST['host'],$_POST['port'],$_POST['seguridad'],$_POST['correopas'],$_POST['remitente'],$_POST['color'], $notificaciones,$_POST['saludo']);
      $this->model->setusuario();
      $this->model->setusuariosorden();
      if(!isset($_GET['id'])) $this->model->usuarioemp();
      if($_POST['chpass'] == "1"){
        $okpass = $this->model->setpass($_POST['id'],$_POST['password'],$_POST['password2']);
      }else $okpass = 1;

      if (isset($_FILES['avatar']['name'])) {
        $nombre_archivo = $_FILES['avatar']['name'];
        $tipo_archivo = $_FILES['avatar']['type'];
        $tamano_archivo = $_FILES['avatar']['size'];
        if($tamano_archivo > 3000000) $error = "6XMUE2";
        else{
          if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg") || strpos($tipo_archivo, "jpg") || strpos($tipo_archivo, "png")))) {
            $error = "6XMUE3";
          }
          else{
            if (move_uploaded_file($_FILES['avatar']['tmp_name'],  'images/avatares/'.$nombre_archivo)){
              $sql = 'UPDATE usuarios SET u_avatar = "'.$nombre_archivo.'" WHERE u_id = "'.$_POST['id'].'"';
              setq($sql);
              $error="1";
            }
            else $error = "6XMUE4";
          }
        }
      }else{
        $error = "1";
      }

      redirect("?modulo=usuarios&accion=edit&id=".$this->model->id.'&error='.$okpass.'&avatar='.$error);
    }
    function delavatar(){
      unlink('images/avatares/'.busca($_GET['id'],'usuarios','u_id','u_avatar'));

      $sql = 'UPDATE usuarios SET u_avatar = NULL WHERE u_id = "'.$_GET['id'].'"';
      setq($sql);

      redirect("?modulo=usuarios&accion=edit&id=".$_GET['id']);
    }

    function setorden(){
      for($i = 0; $i < $_POST['tamano']; $i++){
        // Cadena original con comillas dobles
        $cadenaConComillas = $_POST['uid'.$i];

        // Utilizando la función str_replace() para quitar las comillas dobles
        $cadenaSinComillas = str_replace('"', '', $cadenaConComillas);
        $this->model->setdataorden($cadenaSinComillas, ($i+1));
        $this->model->setorden();
      }
      redirect("?modulo=usuarios&accion=show");
      
    }

    function show(){
      $this->view = new viewusuarios($this->model);
      $this->view->show();
    }
  }

  class modelusuarios{
    
    function select($id){
      $sql = "SELECT * FROM usuarios WHERe u_id = '".$id."' AND u_empresa = '".$_SESSION['emp']."'";
      $result = setqemojis($sql);
      $row = $result->fetch_array();
      $this->nuser = $row['u_nuser'];
      $this->id = $row['u_id'];
      $this->nmb = $row['u_nmb'];
      $this->apellidos = $row['u_apellidos'];
      $this->grupo = $row['u_grupo'];
      $this->correo= $row['u_correo'];
      $this->telefono= $row['u_telefono'];
      $this->avatar= $row['u_avatar'];
      $this->estatus = $row['u_estatus'];
      $this->nacimiento = $row['u_nacimiento'];
      $this->puesto = $row['u_puesto'];
      $this->mailcorp= $row['u_mailcorp'];
      $this->passcorp= $row['u_passcorp'];
      $this->aporte = $row['u_aporte'];
      $this->host = $row['u_host'];
      $this->puerto = $row['u_puerto'];
      $this->seguridad = $row['u_seguridad'];
      $this->contraseñacorp = $row['u_contraseñacorp'];
      $this->remitente = $row['u_remitente'];
      $this->color = $row['u_color'];
      $this->saludo = $row['u_saludo'];
      $this->notificaciones = $row['u_notificaciones'];
      
    }
    function result($page,$nmb,$grupo,$estatus){
      $pagenum = 50;
      $sql = 'SELECT * FROM usuarios WHERE ';
      if($estatus != "T") $sql .= 'u_estatus = "'.$estatus.'" ';
      else $sql .= 'u_estatus != "" ';
      if($nmb) $sql.= ' AND (u_nmb LIKE "%'.$nmb.'%" OR u_nuser LIKE "%'.$nmb.'%" OR u_apellidos  LIKE "%'.$nmb.'%" OR u_id LIKE "%'.$nmb.'%")';
      if($grupo) $sql.= ' AND u_grupo = "'.$grupo.'" ';
      $sql.=' AND u_empresa = "'.$_SESSION['emp'].'" ORDER BY u_id ASC';
      $sql.=' LIMIT '.($pagenum*$page).','.$pagenum;

      $this->result = setq($sql);
      $this->resultt = setq($sql);
    }
    function setdata($nuser,$id,$nmb,$apellidos,$correo,$telefono,$puesto,$nacimiento,$grupo,$comment,$estatus,$mailcorp,$passcorp,$host,$puerto,$seguridad,$correopass,$remitente,$color,$notificaciones,$saludo){
      $this->nuser = $nuser;
      $this->id = clearvmayus($id);
      $this->nmb = clearvmayus($nmb);
      $this->apellidos = clearvmayus($apellidos);
      $this->correo = clearvminus($correo);
      $this->telefono = clearvmayus($telefono);
      $this->puesto = clearvmayus($puesto);
      $this->nacimiento = clearvmayus($nacimiento);
      $this->grupo = clearvmayus($grupo);
      $this->aporte = clearvmayus($comment,false);
      $this->mailcorp = clearvminus($mailcorp);
      $this->passcorp = clearvmayus($passcorp,false);
      $this->host = clearvmayus($host,false);
      $this->puerto = clearvmayus($puerto,false);
      $this->seguridad = clearvmayus($seguridad,false);
      $this->correopass = clearvmayus($correopass,false);
      $this->remitente = clearvmayus($remitente,false);
      $this->color = clearvmayus($color);
      $this->saludo = $saludo;
      $this->notificaciones = $notificaciones;
      if($estatus) $this->estatus = "A"; else $this->estatus = "I";
    }
    function setusuario(){
      $sql = "INSERT INTO usuarios SET
              u_nuser = '".$this->nuser."',
              u_id = '".$this->id."',
              u_empresa = '".$_SESSION['emp']."',
              u_grupo = '".$this->grupo."',
              u_nmb = '".$this->nmb."',
              u_apellidos = '".$this->apellidos."',
              u_nacimiento = '".$this->nacimiento."',
              u_estatus= '".$this->estatus."',
              u_correo= '".$this->correo."',
              u_telefono= '".$this->telefono."',
              u_puesto= '".$this->puesto."',
              u_mailcorp= '".$this->mailcorp."',
              u_aporte= '".$this->aporte."',            
              u_host = '".$this->host."',
              u_puerto = '".$this->puerto."',
              u_seguridad = '".$this->seguridad."',
              u_contraseñacorp = '".base64_encode($this->correopass)."',
              u_remitente = '".$this->remitente."',
              u_color = '".$this->color."',
              u_saludo = '".$this->saludo."',
              u_notificaciones = '".$this->notificaciones."'
              ON DUPLICATE KEY UPDATE
              u_grupo = '".$this->grupo."',
              u_nmb = '".$this->nmb."',
              u_empresa = '".$_SESSION['emp']."',
              u_apellidos = '".$this->apellidos."',
              u_nacimiento = '".$this->nacimiento."',
              u_estatus= '".$this->estatus."',
              u_correo= '".$this->correo."',
              u_telefono= '".$this->telefono."',
              u_mailcorp= '".$this->mailcorp."',
              u_puesto= '".$this->puesto."',
              u_aporte= '".$this->aporte."',
              u_host = '".$this->host."',
              u_puerto = '".$this->puerto."',
              u_seguridad = '".$this->seguridad."',
              u_contraseñacorp = '".base64_encode($this->correopass)."',
              u_remitente = '".$this->remitente."',
              u_color = '".$this->color."',
              u_saludo = '".$this->saludo."',
              u_notificaciones = '".$this->notificaciones."'";
      setqemojis($sql);

      if(!empty($this->passcorp)){
        $sql = "UPDATE usuarios SET u_passcorp= '".$this->passcorp."' WHERE u_nuser = '".$this->nuser."'";
        setq($sql);
      }
    }

    function setusuariosorden(){
      $sqls = 'SELECT u_grupo, u_estatus FROM usuarios WHERE u_id = "'.$this->id.'"';
      $results = setq($sqls);
      list($grupo, $estatus) = $results->fetch_array();
      if($grupo == "VENTAS" && $estatus == "A"){
        $orden = getmax("uo_orden", "usuarios_orden", false, true);
        $sql = "INSERT IGNORE INTO usuarios_orden (uo_uid, uo_orden, uo_estatus)
          VALUES ('" . $this->id . "', '" . $orden . "', 'I')";
        setq($sql);
      } else if($estatus == "I" && $grupo == "VENTAS"){
        $orden = busca($this->id, "usuarios_orden", "uo_uid", "uo_orden");
        $registro = busca($this->id, "usuarios_orden", "uo_uid", "COUNT(*)");
        if($registro > 0){
          $sql = "DELETE FROM usuarios_orden WHERE uo_uid = '".$this->id."'";
          setq($sql);
          $sql1 = "SELECT uo_uid FROM usuarios_orden WHERE uo_orden > ".$orden;
          $result1 = setq($sql1);
          while($row = $result1->fetch_array()){
            $sqlo = "UPDATE usuarios_orden SET uo_orden= '".$orden."' WHERE uo_uid = '".$row['uo_uid']."'";
            setq($sqlo);
            $orden++;
          }


        }
      }

      
    }
    function setpass($id,$pass,$pass2){
      if(empty($pass) || empty($pass2)) $error = "6XMU01";
      elseif($pass != $pass2) $error = "6XMU01";
      else{
        $sql = "UPDATE usuarios SET u_password = PASSWORD('".strtoupper($pass)."') WHERE u_id = '".$id."'";
        $error = setq($sql);
      }

      return($error);
    }
    function usuarioemp(){
      $sql = "INSERT INTO usuario_empresa SET ue_usuario  = '".$this->id."',
              ue_empresa = '".$_SESSION['emp']."'";
      setq($sql);
    }

    function setorden (){
      $sql = "INSERT INTO usuarios_orden SET
              uo_uid = '".$this->uido."',
              uo_orden = '".$this->orden."'
              ON DUPLICATE KEY UPDATE
              uo_uid = '".$this->uido."',
              uo_orden = '".$this->orden."'";
      setq($sql);
    }

    function setdataorden($uido,$orden){
      $this->uido = $uido;
      $this->orden = $orden;
  }
}

class viewusuarios{
  var $model;
  function __construct($model){
    //    include('header.php');
    ?>
    <script>
      function checkguardar(){
        document.getElementById("saveuser").innerHTML = "Guardando";
        document.getElementById("saveuser").disabled = true;
        return true;
      }
    </script>
    <?php
    $this->model = $model;
    $this->tipoc = array("C"=>"Cliente","P"=>"Prospécto");
  }
  function browse($page,$nmb,$grupo,$estatus){
    if($estatus == "A") $chest = "checked"; else $chest = "";
    //Sección: E1 Encabezado - Botones de acción
  
    $nuevo = '<a id="nuevo" href="?modulo=usuarios&accion=edit" accesskey="">
            <button type="button" class="btn-sm btn btn-primary"><i class="fa fa-plus"></i> Nuevo </button>
          </a>';

    $orden = '
      <a href="?modulo=usuarios&accion=show">
        <button class="btn btn-sm btn-info">
          <i class="fas fa-users-cog"></i> Ordenar vendedores
        </button>
      </a>';  
    $filtro = 'HOLA';


      
      $esta = "";
      $esti = "";
      $estt = "";
      if(!isset($estatus) || $estatus == "A") $esta = "selected";
      elseif($estatus == "I") $esti = "selected";
      else $estt = "selected";
      //Sección: E2 Encabezado - Filtros
      ?>
      <script>
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
            $("#pref-search").focus();
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

    toolbar($_GET['modulo'],$nuevo,'',$orden);

    echo '<div class="card mt-3">
      <div class="card-body">';
        echo '<div class="">
          <div class="table-responsive">
            <center>
              <table class="mb-0 table table-hover" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th><b>Usuario</b></th>
                    <th><b>Nombre</b></th>
                    <th><b>Grupo</b></th>
                    <th><b>Correo</b></th>
                    <th><b>Telefono</b></th>
                    <th><b>Ver</b></th>
                  </tr>
                </thead>';
                while($row = $this->model->result->fetch_array()){
                  echo '<tr>
                    <th>'.$row['u_id'].'</th>
                    <td>'.$row['u_nmb'].'</td>
                    <td>'.$row['u_grupo'].'</td>
                    <td>'.$row['u_correo'].'</td>
                    <td>'.$row['u_telefono'].'</td>
                    <th><a href="?modulo=usuarios&accion=edit&id='.$row['u_id'].'"><button type="button" class="btn btn-sm btn-secondary" /><i class="fas fa-edit"></i> Editar</button></a></th>
                  </tr>';
                }
              echo '</table>';
              /*
                echo '<div class="col-md-12">
                  <div class="mb-3">
                    <nav aria-label="Page navigation">
                      <ul class="pagination">
                        <li class="page-item">
                          <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo; Ant</span>
                            <span class="sr-only">Anterior</span>
                          </a>
                        </li>';
                        $numres = 20;
                        $nr = $this->model->resultt->num_rows;
                        $np = $nr/$numres;
                        $paginaa = $page-1;
                        $pagina = $page+1;
                        $sqlpg = 'SELECT COUNT(*) FROM usuarios ';
                        $resultpg = setq($sqlpg);
                        list($numg) =  $resultpg->fetch_array();
                        $pageres = ceil($numg/$numres);
                        for($i=1;$i<=$pageres;$i++){
                          echo '<li class="page-item active"><a class="page-link" href="#">'.$i.'</a></li>';
                        }
                        echo '<li class="page-item">
                          <a class="page-link" href="#" aria-label="Next">
                            <span aria-hidden="true">Sig &raquo;</span>
                            <span class="sr-only">Siguiente</span>
                          </a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                </div>';
              */
            echo '</center>
          </div>
        </div>
      </div>
    </div>';

    echo '
    
    <script>
      $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          pageLength: 50,
      });
    </script>
    ';


  }
  function edit(){
    echo '
    <style>
    .funkyradio div {
      clear: both;
      overflow: hidden;
    }
    
    .funkyradio label {
      width: 100%;
      border-radius: 3px;
      border: 1px solid #D1D3D4;
      font-weight: normal;
    }
    
    .funkyradio input[type="radio"]:empty,
    .funkyradio input[type="checkbox"]:empty {
      display: none;
    }
    
    .funkyradio input[type="radio"]:empty ~ label,
    .funkyradio input[type="checkbox"]:empty ~ label {
      position: relative;
      line-height: 2.5em;
      text-indent: 3.25em;
      margin-top: 0.2em;
      cursor: pointer;
      -webkit-user-select: none;
         -moz-user-select: none;
          -ms-user-select: none;
              user-select: none;
    }
    
    .funkyradio input[type="radio"]:empty ~ label:before,
    .funkyradio input[type="checkbox"]:empty ~ label:before {
      position: absolute;
      display: block;
      top: 0;
      bottom: 0;
      left: 0;
      content: "";
      width: 2.5em;
      background: #bf434e;
      border-radius: 3px 0 0 3px;
    }
    
    .funkyradio input[type="radio"]:hover:not(:checked) ~ label,
    .funkyradio input[type="checkbox"]:hover:not(:checked) ~ label {
      color: #888;
    }
    
    .funkyradio input[type="radio"]:hover:not(:checked) ~ label:before,
    .funkyradio input[type="checkbox"]:hover:not(:checked) ~ label:before {
      content: "\2714";
      text-indent: .9em;
      color: #C2C2C2;
    }
    
    .funkyradio input[type="radio"]:checked ~ label,
    .funkyradio input[type="checkbox"]:checked ~ label {
      color: #777;
    }
    
    .funkyradio input[type="radio"]:checked ~ label:before,
    .funkyradio input[type="checkbox"]:checked ~ label:before {
      content: "\2714";
      text-indent: .9em;
      color: #333;
      background-color: #ccc;
    }
    
    .funkyradio input[type="radio"]:focus ~ label:before,
    .funkyradio input[type="checkbox"]:focus ~ label:before {
      box-shadow: 0 0 0 3px #999;
    }
    
    .funkyradio-default input[type="radio"]:checked ~ label:before,
    .funkyradio-default input[type="checkbox"]:checked ~ label:before {
      color: #333;
      background-color: #ccc;
    }
    
    .funkyradio-primary input[type="radio"]:checked ~ label:before,
    .funkyradio-primary input[type="checkbox"]:checked ~ label:before {
      color: #fff;
      background-color: #337ab7;
    }
    
    .funkyradio-success input[type="radio"]:checked ~ label:before,
    .funkyradio-success input[type="checkbox"]:checked ~ label:before {
      color: #fff;
      background-color: #5cb85c;
    }
    
    .funkyradio-danger input[type="radio"]:checked ~ label:before,
    .funkyradio-danger input[type="checkbox"]:checked ~ label:before {
      color: #fff;
      background-color: #d9534f;
    }
    
    .funkyradio-warning input[type="radio"]:checked ~ label:before,
    .funkyradio-warning input[type="checkbox"]:checked ~ label:before {
      color: #fff;
      background-color: #f0ad4e;
    }
    
    .funkyradio-info input[type="radio"]:checked ~ label:before,
    .funkyradio-info input[type="checkbox"]:checked ~ label:before {
      color: #fff;
      background-color: #5bc0de;
    }

    .switch {
      display: inline-block;
      height: 25px;
      position: relative;
      width: 45px;
    }
    
    .switch input {
      display:none;
    }
    
    .slider {
      background-color: #ccc;
      bottom: 0;
      cursor: pointer;
      left: 0;
      position: absolute;
      right: 0;
      top: 0;
      transition: .4s;
    }
    
    .slider:before {
      background-color: #fff;
      bottom: 4px;
      content: "";
      height: 18px;
      left: 4px;
      position: absolute;
      transition: .4s;
      width: 18px;
    }
    
    input:checked + .slider {
      background-color: #66bb6a;
    }
    
    input:checked + .slider:before {
      transform: translateX(18px);
    }
    
    .slider.round {
      border-radius: 34px;
    }
    
    .slider.round:before {
      border-radius: 50%;
    }
    
    body {
      background-color: #f1f2f3;
    }
    </style>';
    if($_GET['avatar'] == "6XMUE2" || $_GET['avatar'] == "6XMUE4"){
      echo '<div class="alert alert-danger col-md-12 text-xs-center">Ha sucedido un error al actualizar tu imagen de perfil. Intenta con otra imagen o formato.</div>';
    }
    if($this->model->id){
      $accion = 'update';
      $titulo = 'Actualizar Datos';
    }
    else{
      $accion = 'insert';
      $titulo = 'Registro básico de cliente';
    }

    $atras = '
    <a href="?modulo=usuarios&accion=index">
      <button class="btn btn-sm btn-warning">
        <i class="fa fa-arrow-left"></i> Atrás
      </button>
    </a>
    ';
    toolbar($_GET['modulo'],$atras);
    ?>
    <script>
      function sendval(){
        document.getElementById("sologas").value = "1";
      }
      function viewpass(){
        document.getElementById("pass").style.display = "";
        document.getElementById("cpass").style.display = "";
        document.getElementById("changepass").style.display = "none";
        document.getElementById("chpass").value = 1;

        document.getElementById("password").setAttribute("required", true);
        document.getElementById("password2").setAttribute("required", true);
      }
    </script>
    <div class="row mt-3">
      <form name="setuser" id="setuser" autocomplete="off" class="form row" method="post" action="?modulo=usuarios&accion=setusuario" enctype="multipart/form-data" onsubmit="checkguardar();">
        <?php
        if($this->model->nuser){
          echo '<input type="hidden" value="'.$this->model->nuser.'" name="nuser" >';
          $readonly = ' readonly="readonly" ';
          $readpass = ' ';
          echo '<input type="hidden" value="0" name="chpass" id="chpass" />';
        }
        else{
          $readonly = ' ';
          $readpass = ' style="display:none" ';
          echo '<input type="hidden" value="1" name="chpass" id="chpass" />';
        }
        ?>
        <div class="col-xl-7 col-md-12">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title" id="basic-layout-form">Información General </h4>
            </div>
            <div class="card-body ">
              <div class="card-block">
                <div class="form-body">
                  <div class="row">
                    <div class="col-md-4">
                      <div class="mb-5">
                        <label class="required" for="username">Usuario </label>
                        <input type="text" id="username" value="<?php echo $this->model->id ?>" class="form-control" placeholder="Nombre de usuario (sin espacios)" name="id" required="required" onkeyup = "compUsuario(event)"  autofocus="autofocus" <?php echo $readonly; ?> >
                        <div id="DivDestino"></div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="mb-5">
                        <label class="required" for="nmb">Nombre </label>
                        <input type="text" id="nmb" value="<?php echo $this->model->nmb ?>" class="form-control" placeholder="Nombre (s)" name="nmb" required="required" >
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="mb-5">
                        <label class="required" for="apellidos">Apellidos </label>
                        <input type="text" id="apellidos" value="<?php echo $this->model->apellidos ?>" class="form-control" placeholder="Apellidos" name="apellidos" required="required" >
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="mb-5">
                        <label class="required" for="correo">E-mail personal </label>
                        <input type="email" style="text-transform:lowercase;" id="correo" value="<?php echo $this->model->correo ?>" class="form-control" placeholder="E-mail" name="correo" required="required" >
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="mb-5">
                        <label class="required" for="telefono">Celular </label>
                        <input type="tel" id="telefono" value="<?php echo $this->model->telefono ?>" class="form-control" placeholder="Celular" name="telefono" required="required" onchange="quitarespacios();" >
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="mb-5">
                        <label class="required" for="puesto">Puesto </label>
                        <input type="text" id="puesto" value="<?php echo $this->model->puesto ?>" class="form-control" placeholder="Puesto" name="puesto" required="required" >
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4">
                      <div class="mb-5">
                        <label class="required" for="nacimiento">Fecha Nacimiento </label>
                        <?php
                          $maxfn = date('Y-m-d',strtotime('-15 years'));
                          $minfn = date('Y-m-d',strtotime('-60 years'));
                        ?>
                        <input type="date" id="nacimiento" max="<?php echo $maxfn ?>" min="<?php echo $minfn ?>" value="<?php echo $this->model->nacimiento ?>" class="form-control" placeholder="Fecha de nacimiento" name="nacimiento" required="required" >
                      </div>
                    </div>
                    <?php
                      if($this->model->id){
                        $pass = 'none ';
                        $req = "";
                      } else {
                        $pass = '';
                        $req = "required";
                      }
                    ?>
                    <div class="col-md-4" id="pass" style="display:<?php echo $pass; ?>" >
                      <div class="mb-5">
                        <label class="required" for="password">Contraseña </label>
                        <input type="password" id="password" class="form-control" placeholder="Escribe una contraseña válida" name="password" data-toggle="tooltip" data-placement="top" title="Incluir Mayusculas, minusculas, números y al menos 1 signo (.#$%&/=?+-_.:,;)" <?php echo $req; ?>>
                      </div>
                    </div>
                    <div class="col-md-4" id="cpass" style="display:<?php echo $pass; ?>" >
                      <div class="mb-5">
                        <label class="required" for="password2">Confirmar Contraseña </label>
                        <input type="password" id="password2" class="form-control" placeholder="Escribe una contraseña válida" name="password2" data-toggle="tooltip" data-placement="top" title="Escriba nuevamente la contraseña anterior" <?php echo $req; ?>>
                      </div>
                    </div>
                  </div>
                  <p style="font-size: x-small;" for=""><i>(*) Requeridos</i></p>
                  <div class="form-actions">
                    <a href="?modulo=usuarios&accion=index">
                      <button type="button" class="btn btn-warning mr-1">
                        <i class="fas fa-times-circle"></i> Cancelar registro
                      </button>
                    </a>
                    <button type="button" class="btn btn-info mr-1" id="changepass" onclick="viewpass();" <?php echo $readpass ?> >
                      <i class="fas fa-check"></i> Cambiar contraseña
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveuser">
                      <i class="fas fa-save"></i> Guardar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-5 col-md-12">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title" id="basic-layout-form">Rol en la empresa</h4>
          </div>
          <div class="card-body ">
            <div class="card-block">
              <div class="form-body">
                <div class="row">
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="mb-5">
                      <label class="required">Rol del empleado </label>
                      <select class="mb-5 form-select form-select-solid" onchange="setNotify();" name="grupo" id="grupo" required="required" >
                        <option value="">Seleccione un grupo</option>
                        <?php
                          $sql = 'SELECT * FROM grupos WHERE g_estatus = "A" AND g_id != "SUPER"';
                          $result = setq($sql);
                          while($row = $result->fetch_array()){
                            if($row['g_id'] == $this->model->grupo) $sel = 'selected'; else $sel = '';
                            ?>
                              <option value="<?php echo $row['g_id'] ?>" <?php echo $sel ?> ><?php echo $row['g_nmb'] ?></option>
                            <?php
                          }
                        ?>
                      </select>
                    </div>
                  </div>
                  <?php
                    if($this->model->estatus == "A" || !$this->model->estatus) $ch = 'checked';
                    if($this->model->notificaciones == 1){
                      $notify = 'checked';  
                    }else{
                      $notify = '';
                    }

                    if($this->model->grupo == "VENTAS" || $this->model->grupo == "LOGISTIC" || 
                    $this->model->grupo == "FINANZAS" || $this->model->grupo == "ADMIN" || $this->model->grupo == "GERENCIA"){
                      $visible = '';  
                    }else{
                      $visible = 'hidden';
                    }


                    if($this->model->grupo == "VENTAS") $change = "desactivarVendedor()";
                    else $change= '';
                  ?>

                <div class="col-md-3 col-sm-3 col-xs-6">
                <div class="mb-5">
                <div>
                  <label>Estatus</label>
                </div>
                <div>
                  <label class="switch" for="estatus">
                  <input type="checkbox" name="estatus" id="estatus" <?php echo $ch; ?> data-toggle="toggle" onchange="<?php echo $change; ?>" data-on="Activo" data-off="Inactivo" data-onstyle="success" data-offstyle="danger"/>
                    <div class="slider round"></div>
                  </label>
                </div>
                </div>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-6" id="divnotify" <?php echo $visible; ?>>
                <div class="mb-5">
                <div>
                  <label>Notificaciones</label>
                </div>
                <div>
                  <label class="switch" for="notificaciones">
                  <input type="checkbox" name="notificaciones" id="notificaciones" <?php echo $notify; ?> data-toggle="toggle" data-on="Activo" data-off="Inactivo" data-onstyle="success" data-offstyle="danger"/>
                    <div class="slider round"></div>
                  </label>
                    </div>
                  </div>
                </div>

                
                

                </div>

                <?php
                if($this->model->grupo == "VENTAS" || $this->model->grupo == "ADMIN" || $this->model->grupo == "GERENCIA"){
                ?>
                <div class="row">
                  <div class="col-md-12">
                    <div class="mb-5">
                      <textarea name="saludo" id="saludo" rows="5" placeholder="Inserte un saludo para los clientes" class="form-control" id="saludo"><?php echo $this->model->saludo; ?></textarea>
                    </div>
                  </div>
                </div>
                <?php
                }
                ?>

                <script src="assets/js/tinymce/tinymce.min.js"></script>

                <script>

                tinymce.init({
                    selector: '#saludo',
                    plugins: [
                        'a11ychecker', 'advlist', 'advcode', 'advtable', 'autolink', 'checklist', 'export',
                        'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks',
                        'powerpaste', 'fullscreen', 'formatpainter', 'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample', 'code'
                    ],
                    content_css: [
                        "https://fonts.googleapis.com/css2?family=Josefin+Sans&display=swap",
                    ],
                    toolbar: 'undo redo | formatpainter casechange blocks | bold italic backcolor | ' +
                        'alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist checklist outdent indent | removeformat | a11ycheck code table help | codesample | code',
                    setup: function (editor) {
                    }
                });

                </script>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-5">
                      <label for="tipo">Color de calendario <i class="icon-exclamation-circle" data-toggle="tooltip" data-placement="top" title="Color para identificar tu actividades en el calendario"></i></label>
                      <input type="color" name="color" class="form-control" id="color" value="<?php echo $this->model->color; ?>" required>
                    </div>
                  </div>
                </div>
                <div class="row mt-1">
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="mb-5">
                      <label for="projectinput8">Correo Empresarial</label>
                      <input type="email" style="text-transform:lowercase;" id="mailcorp" class="form-control" placeholder="Tu correo corporativo para envio de correos" name="mailcorp" value="<?php echo $this->model->mailcorp; ?>" >
                    </div>
                    <!-- CORREO CONFIG -->
                    <?php
                    if($this->model->host == "tls://smtp.gmail.com:587"){
                      $gmailcorp = "checked";
                      $display = "block";
                      $readonly = "readonly";
                      $disabled = "disabled";
                      if($this->model->seguridad == "tls") $tls = "selected";
                      elseif($this->model->seguridad == "ssl") $ssl = "selected";
                      else $ninguna = "selected";
                    }elseif($this->model->host == "tls://smtp.office365.com:587"){
                      $outlookcorp = "checked";
                      $display = "block";
                      $readonly = "readonly";
                      $disabled = "disabled";
                      if($this->model->seguridad == "tls") $tls = "selected";
                      elseif($this->model->seguridad == "ssl") $ssl = "selected";
                      else $ninguna = "selected";
                    }elseif($this->model->host == ""){
                      $display = "none";
                    }else{
                      $servidorcorp = "checked";
                      $display = "block";
                      $readonly = "";
                      $disabled = "";
                      if($this->model->seguridad == "tls") $tls = "selected";
                      elseif($this->model->seguridad == "ssl") $ssl = "selected";
                      else $ninguna = "selected";
                    }
                    ?>
                    <!-- MODAL AYUDACORP-->
                    <div class="modal fade" id="exampleModalAYUDA" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Configuracion Correo Empresarial</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <div class="mb-1" style="font-size: small;">
                                El correo empresarial funciona para realizar envios por correo electronico de manera automatica en los modulos de cotizaciones, remisiones y ordenes de compra del sistema JD CEO.
                                Por ello es importante tener configurado este apartado. 
                                Cada usuario tendrá un correo configurado para realizar los envios.
                                Se cuenta con 3 tipos de configuraciones:
                            </div>
                            <button class="btn btn-primary btn-sm mb-1" type="button"  data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                              Información Para Gmail
                            </button>
                            <button class="btn btn-primary btn-sm mb-1" type="button"  data-target="#collapseExample2" aria-expanded="false" aria-controls="collapseExample">
                              Información Para Outlook
                            </button>
                            <button class="btn btn-primary btn-sm mb-1" type="button"  data-target="#collapseExample3" aria-expanded="false" aria-controls="collapseExample">
                              Información Para Servidor
                            </button>
                            <div id="collapseExample">
                              <div class="p-1 card-body" style="font-size: small;">
                              <h4>Gmail</h4>
                              <ul>
                                <li>Activa la verificación en dos pasos para tu cuenta de gmail desde <a target="_blank" href="https://myaccount.google.com/u/2/signinoptions/two-step-verification/enroll-welcome?hl=es">aquí</a>.</li>
                                <li>Una vez configurada la verificación en dos pasos, genera una contraseña para aplicación desde <a target="_blank" href="https://myaccount.google.com/apppasswords?rapt=AEjHL4MYMudlCbNT3QS73VGi3I5Eg-Dd0eNl_oGn7uDFWidx7h7IISO3XhYLx_KTqNr1mWxRxJ0dNiCZMF4cl8QC1-4AhBgeTw">aquí</a>.</li>
                                <li>Seleccionar "Correo electrónico" y en dispositivo "Otra" para personalizar el nombre a guardar.</li>
                                <li>Al pulsar en "Generar" se crea una contraseña de 16 digitos la cual se utilizará en el campo de "Contraseña" de la configuración del correo empresarial.</li>
                                <li>Pulsar en "Comprobar" para verificar el correcto funcionamiento del correo</li>
                              </ul>
                            </div>
                          </div>
                          <div id="collapseExample2">
                            <div class="p-1  card-body" style="font-size: small;">
                            <h4>Outlook</h4>
                            <ul>
                              <li>Completa los campos de correo empresarial con tu direccion de correo electronico de de outlook(live, hotmail).</li>
                              <li>Una vez guardados presiona el boton "Comprobar"</li>
                              <li>Dirigete al apartado de seguridad de outlook desde <a target="_blank" href="https://account.live.com/Activity?mkt=es-MX&refd=account.microsoft.com&refp=security">aqui</a>.</li>
                              <li>En actividad inusal se mostrará una advertencia de sincronización automatica desde la ip "162.241.92.192". Autorizar uso pulsando en "Fui yo"</li>
                              <li>Pulsar nuevamente el boton "Comprobar" de JD CEO</li>
                            </ul>
                          </div>
                        </div>
                        <div id="collapseExample3">
                          <div class="p-1 card-body" style="font-size: small;">
                          <h4>Servidor</h4>
                          <ul>
                            <li>Si cuentas con un servicio de correo electronico proveído por un hostin, debes llenar los datos con la información proporcionada desde tu servidor.</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" class="btn btn-primary">Save changes</button> -->
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mb-5">
                        <label>Proveedor de Correo <a data-bs-toggle="modal" data-bs-target="#exampleModalAYUDA"><i class="icon-question"></i></a></label>
                        <div class="funkyradio">
                <div class="funkyradio-success">
                    <input type="checkbox" name="provco" id="provco1" <?php echo $gmailcorp; ?> onchange="gmail(this);"/>
                    <label for="provco1">Gmail</label>
                </div>
                <div class="funkyradio-success">
                    <input type="checkbox" name="provco" id="provco2" onchange="outlook(this);" <?php echo $outlookcorp; ?>/>
                    <label for="provco2">Outlook</label>
                </div>
                <div class="funkyradio-success">
                    <input type="checkbox" name="provco" id="provco3" onchange="servidor(this);" <?php echo $servidorcorp; ?>/>
                    <label for="provco3">Servidor de E-mail</label>
                </div>
            </div>
              </div>
            </div>
            <div class="col-md-6 mb-5" id="condicionescorreo" style="display: <?php echo $display; ?>;">
            <?php
            if($outlookcorp == "checked" || $gmailcorp == "checked" || $servidorcorp == "checked"){
            ?>
              <div class="mb-5">
                <label for="">Host: *</label>
                <input class="form-control" name="host" value="<?php echo $this->model->host; ?>" type="text" <?php echo $readonly; ?> >
                <label for="">Puerto: *</label>
                <input type="number" name="port" class="form-control" value="<?php echo $this->model->puerto; ?>" <?php echo $readonly; ?> >
                <label for="">Tipo de Seguridad: *</label> 
                <select name="seguridad" class="form-control" <?php echo $readonly.' '.$disabled; ?> > 
                  <option value="0" <?php echo $ninguna; ?> >Ninguna</option> 
                  <option value="tls" <?php echo $tls; ?> >tls</option> 
                  <option value="ssl" <?php echo $ssl; ?> >ssl</option>
                </select>
                <label for="">Contraseña: *</label>
                <input type="password" id="correopas" class="form-control" name="correopas" data-toggle="tooltip" data-placement="top" title="Sensible a Mayusculas, Minusculas, números y signos" value="<?php echo base64_decode($this->model->contraseñacorp); ?>" required>
                <button id="show_password" class="btn btn-primary" type="button" onclick="mostrarPasswordCorp2()"> <span class="fa fa-eye"></span> </button> <br>
                <label for="">Remitente: * </label><i class="fa fa-info" data-toggle="tooltip" data-placement="top" title="Nombre que aparcerá en el correo como remitente."></i>
                <input type="text" data-toggle="tooltip" data-placement="top" title="Nombre que aparcerá en el correo como remitente." name="remitente" class="form-control" value="<?php echo $this->model->remitente; ?>" required>
                <button type="button" data-toggle="tooltip" data-placement="top" title="Asegurate que la información haya sido guardada antes de comprobar los datos" onclick="comprobar();" class="btn btn-success mt-1"><i class="fa fa-check"></i> Comprobar</button>
              </div>
              <?php              
              }
            ?>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12" hidden>
              <div class="mb-5">
                <label for="projectinput8">Pasword correo corporativo </label>
                <div class="input-group">
                  <input type="password" id="passcorp" class="form-control" placeholder="Dejar vacio si no ha cambiado" name="passcorp" data-toggle="tooltip" data-placement="top" title="Sensible a Mayusculas, Minusculas, números y signos" >
                  <button id="show_password" class="btn btn-primary" type="button" onclick="mostrarPasswordCorp()"> <span class="fa fa-eye"></span> </button>
                </div>
              </div>
            </div>
          </div>
          <div class="row mt-1">
            <div class="col-md-6 col-sm-6 col-xs-12">
              <div class="mb-5">
                <?php if(file_exists('images/avatares/'.$this->model->avatar) && $this->model->avatar){ ?>
                  <a href="?modulo=usuarios&accion=delavatar&id=<?php echo $this->model->id ?>">
                    <button type="button" class="btn btn-danger mr-1"><i class="icon-android-delete"></i>Borrar Avatar</button>
                  </a>
                  <?php 
                }else{ ?>
                  <label>Actualizar Avatar</label>
                  <label id="avatarid" class="file center-block">
                    <input type="file" id="avatar" name="avatar"  accept="image/*">
                    <span class="file-custom"></span>
                  </label>
                  <?php 
                } ?>
              </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <div class="mb-5">
                <?php if(file_exists('images/avatares/'.$this->model->avatar) && $this->model->avatar){ ?>
                  <label>Avatar</label>
                  <img src="images/avatares/<?php echo $this->model->avatar ?>" class="img-thumbnail" alt="" />
                  <?php 
                }
                else{ ?>
                  <label id="nameav" class="file center-block">Sin avatar seleccionado</label>
                  <?php 
                } ?>
              </div>
            </div>
          </div>
          <div class="row mt-1" hidden>
            <div class="col-md-12">
              <div class="mb-5">
                <label for="projectinput8">Aporte esperado</label>
                <textarea id="projectinput8" rows="5" class="form-control" name="comment" placeholder="About Project"><?php echo $this->model->aporte ?></textarea>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <script>

      function desactivarVendedor(){
        var estatus = document.getElementById("estatus");
        var vendedor = "<?php echo $_GET['id']; ?>";

        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })

        if(!estatus.checked){
          Swal.fire({
            title: 'Atención',
            text: "¿Estás seguro de marcar como inactivo a este vendedor?. Todos sus leads serán reasignados a otros vendedores.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Estoy seguro',
            cancelButtonText: 'Cancelar'
          }).then((result) => {
            if (result.isConfirmed) {
              $.ajax({
                url: "query/reasignarleads.php",
                method: "POST",
                dataType: 'json',
                data: {
                  "vendedor": vendedor
                },
              }).done(function(data){
                if(data.respuesta == 0){
                  
                  Toast.fire({
                  icon: 'success',
                  title: data.txtrespuesta
                })
                } else{
                  Toast.fire({
                    icon: 'error',
                    title: data.txtrespuesta
                  })
                  estatus.checked = true;
                }
              });    
            } else{
              estatus.checked = true;
                  Toast.fire({
                  icon: 'success',
                  title: "Acción cancelada"
                })
                  estatus.checked = true;
            }
          })
        }
      }

      function mostrarPasswordCorp2(){
        var elementoContraseña = document.getElementById("correopas");

        if (elementoContraseña.type === "password") {
          // Cambia el tipo de entrada de contraseña a texto
          elementoContraseña.type = "text";
        } else {
          elementoContraseña.type = "password";
        }

      }
      
      function quitarespacios(){
        original = document.getElementById("telefono").value;
        var x;
        var sCadenaSinBlancos = "";
        for (x=0; x < original.length; x++) {
          if (original.charAt(x) != " ") sCadenaSinBlancos+=original.charAt(x);
        }
        document.getElementById("telefono").value = sCadenaSinBlancos;
      }

      function setNotify(){
        var grupo = document.getElementById("grupo");
        var notificaciones = document.getElementById("notificaciones");
        var divnotify = document.getElementById("divnotify")
        var saludo = document.getElementById("saludo");
        if(grupo.value == "LOGISTIC" || grupo.value == "FINANZAS" 
          || grupo.value == "VENTAS" || grupo.value == "ADMIN" || grupo.value == "GERENCIA"){
          divnotify.removeAttribute("hidden");
          notificaciones.checked = true;
        } else{
          divnotify.setAttribute("hidden", true);
          notificaciones.checked = false;
        }

        if(grupo.value == "VENTAS" || grupo.value == "ADMIN" || grupo.value == "GERENCIA"){
          saludo.setAttribute("required", true);
          saludo.removeAttribute("hidden");
        } else{
          saludo.removeAttribute("required");
          saludo.setAttribute("hidden", true);
        }
      }

      function checkChecked() {
        var checkboxes = document.getElementsByName("provco");
        var checked = false;

        checkboxes.forEach(function(cb) {
            if (cb.checked) {
                checked = true;
                return;
            }
        });

        if (checked) {
          return true;
        } else {
          return false;
        }
    }
    </script>
    <?php
    //echo '</div></form>'; 
  }

  function show(){
    echo '
        <!-- Incluye la biblioteca Sortable -->
        <script src="assets/js/Sortable.min.js"></script>
        <!-- Incluye los scripts de Materialize -->
        <script src="assets/js/materialize.min.js"></script>
        ';
      echo '
      
      <style>
        /* Añade estilos personalizados para la tabla arrastrable */
        .sortable-table {
          width: 100%;
        }
        .sortable-item {
          background-color: #f5f5f5;
          border: 1px solid #e0e0e0;
          padding: 10px;
          cursor: grab;
        }
      </style>';

      $atras = '
      <a href="?modulo=usuarios&accion=index">
        <button class="btn btn-sm btn-warning">
          <i class="fa fa-arrow-left"></i> Atrás
        </button>
      </a>';   
      toolbar($_GET['modulo'],$atras,'','');
        ?>
        <!-- Incluye la biblioteca Sortable -->
        <?php
        //Modal - Nuevo deal - TABLERO
        echo '
        <div class="modal fade text-xs-left" id="newtablero" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33">Nueva hoja de prospectos</h4>
              </div>
              <form method="post" action="?modulo=prospectos&accion=insert" autocomplete="off" >
                <div class="modal-body row">
                  <div class="col-md-6 col-xs-12">
                    <label>Fecha inicio: </label>
                    <div class="mb-5">
                      <div class="position-relative has-icon-left">
                        <input type="datetime-local" id="fini" name="fini" class="form-control" value="'.date('Y-m-d').'T'.date('H:i:s').'" step="any" required readonly>
                        <div class="form-control-position">
                          <i class="icon-calendar5"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6 col-xs-12">
                    <label>Usuario: </label>
                    <div class="mb-5">
                    <input type="text" id="ugen" name="ugen" class="form-control" value="'.$_SESSION['uid'].'" required readonly>
                    </div>
                  </div>
                  <div class="col-md-12 col-xs-12">
                    <label>Observaciones: </label>
                    <div class="mb-5">
                      <textarea id="observacion" class="form-control" rows="4" name="observacion" placeholder="Escribe una obervación"></textarea>
                    </div>
                  </div>
                </div>
                <div class="modal-footer p-2">
                  <center><button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Registrar hoja nueva</button></center>
                </div>
              </form>
            </div>
          </div>
        </div>';
        //filtro
        echo '
        
        <style>
          .container { 
          display: flex;
          width: 100%;
          height: 100%;
          padding: 20px;
          justify-content: center;

          .listWrap {
          height: 800px;
          width: 1000px;
          }

          .list {
          list-style: none;
          margin: 0;
          padding: 0;
          display: table;
          white-space: nowrap;
          width: 100%;
          }

          .list tr {
          background-color: #f0f0f0;
          display: table-row;
          color: #5c5c5c;
          }

          .list tr:nth-child(odd) {
          background-color: #f2f2f2;
          display: table-row;
          font-size: 9pt;
          color: #5c5c5c;
          }

          .list td:nth-child(even) {
          background-color: #e8e8e8;
          display: table-row;
          font-size: 9pt;
          color: #5c5c5c;
          }

          .list tr:nth-child(even):hover {
          background-color: #dadada;
          }

          .list tr:nth-child(1) th:first-child {
          border-top-left-radius: 6px;
          }

          .list tr:nth-child(1) th:last-child {
          border-top-right-radius: 6px;
          }

          /*Inicio Estas 2*/

          .list thead tr:nth-child(1) {
          background-color: #201c2b;
          text-transform: uppercase;
          font-size: 8pt;
          font-weight: bold;
          color: #b8b5c0;
          }

          .list thead tr:nth-child(1) th {
          border-bottom: 2px solid #7d5bbe;
          padding: 14px;
          }

          /*Fin Estas 2*/

          .list th {
          text-align: left;
          display: table-cell;
          padding: 6px;
          vertical-align: middle;
          }

        </style>
        ';

        echo '
        <script>
        $( document ).ready(function() {
          initEvents();
      });
      
      function initEvents() {
          $(".list").hover(function(){
          $(".list li:first span").stop().animate({borderWidth: "5", backgroundColor: "#3f3659", color: "#e5e3e8"},{duration: 170, complete: function() {}} );     
        }, function () {        
          $(".list li:first span").stop().animate({borderWidth: "2", backgroundColor: "#201c2b", color: "#b8b5c0"},{duration: 170, complete: function() {}} ); 
        });
      }
      </script>
        ';

        echo '
        <div class="container">
          <div class="listWrap">
            <h1>Orden de los vendedores</h1>
              <table id="mi-tabla" class="list">
                  <thead class="">
                    <tr>
                      <th>Usuario</th>
                      <th>Nombre completo</th>
                      <th>Correo</th>
                      <th>Telefono</th>
                    </tr>
                  </thead>
                  <tbody>';

                  $sqluo = "SELECT * FROM usuarios_orden INNER JOIN usuarios ON uo_uid = u_id WHERE u_estatus= 'A' AND u_grupo = 'VENTAS' ORDER BY uo_orden ASC";
                  $result = setq($sqluo);
                  /* if($resultuo->num_rows > 0){
                    $result = $resultuo;
                  } else{
                    $sqlu = "SELECT * FROM usuarios WHERE u_estatus= 'A' AND u_grupo = 'VENTAS' ORDER BY u_id ASC";
                    $resultu = setq($sqlu);
                    $result = $resultu;
                  }                 */
                  
                  while($row = $result->fetch_array()){
                  echo'
                    <tr class="sortable-item" style="height: 50px;">
                      <th><span>'.$row['u_id'].'</span></th>
                      <th><span>'.$row['u_nmb']." ".$row['u_apellidos'].'</span></th>
                      <th><span>'.$row['u_correo'].'</span></th>
                      <th><span>'.$row['u_telefono'].'</span></th>
                    </tr>';
                  }
                  echo '
                  </tbody>
              </table>
              <div style="justify-content: center; display: flex;" hidden>
              <!-- Botón para guardar el orden -->
                <button type="button" id="guardarOrden" class="btn btn-primary mt-3">Guardar</button>    
              </div>
          </div>
      </div>
        ';
        
        ?>
        <script>
        // Inicializar la tabla arrastrable
        const miTabla = document.getElementById("mi-tabla").getElementsByTagName("tbody")[0];
        const guardarOrdenBtn = document.getElementById("guardarOrden");
    
        const sortable = new Sortable(miTabla, {
          animation: 150, // Duración de la animación en milisegundos (opcional)
          group: "mi-tabla", // Grupo para habilitar el arrastre entre diferentes tablas (opcional)
          onEnd: function(evt) {
            guardarOrdenBtn.click();
            guardarOrdenBtn.disabled = false; // Habilitar el botón para guardar el orden
          }
        });
    
        guardarOrdenBtn.addEventListener("click", function() {
        // Crear el formulario
        var orden = new Array();
        const myForm = document.createElement("form");
    
        // Asignar un ID al formulario
        myForm.id = "orden-prospectos";
    
        // Asignar la URL de acción y el método
        myForm.action = "?modulo=usuarios&accion=setorden";
        /* myForm.action = "modulos/prueba2.php"; */
        myForm.method = "POST";
    
        // Obtener las celdas con los datos que deseas enviar
        const celdasSpan = document.querySelectorAll("#mi-tabla tbody th span");
    
        var j = 0;
        // Bucle que recorre los spans y guarda los valores e ids en la matriz "orden"
        for (var i = 0; i < celdasSpan.length; i++) {
            // Crear un input oculto para enviar el orden por POST
            let nombre = document.createElement("input");
            nombre.type = "hidden";
            nombre.name = "uid" + j;
            nombre.value = JSON.stringify(celdasSpan[i].innerText);
            myForm.appendChild(nombre);  

            i = i+3;
            j++;
        }
        let tamano = document.createElement("input");
        tamano.type = "hidden";
        tamano.name = "tamano";
        tamano.value = JSON.stringify((celdasSpan.length/4));
        myForm.appendChild(tamano);
    
        // Agregar el formulario al cuerpo del documento
        document.body.appendChild(myForm);
    
        // Enviar el formulario
        myForm.submit();
    });
      </script>
        <?php

        echo '
        <script>
  function alertSweet(icono, titulo, mensaje) {
      Swal.fire({
          icon: icono,
          title: titulo,
          text: mensaje
      }).then((result) => {
          if (result.isConfirmed || result.isDenied) {
              Swal.close();
          }
      });
  }

/*   // Obtener el elemento del botón con el ID "nuevo"
  const botonNuevo = document.getElementById("nuevo");
  // Agregar un evento de clic al botón nuevo
  botonNuevo.addEventListener("click", function(event) {
    // Prevenir el comportamiento predeterminado del evento
    event.preventDefault();
    // Si el resultado es "0", realizar las siguientes acciones
    // Obtener el atributo "data-bs-target" del botón
    const modalTarget0 = botonNuevo.getAttribute("data-bs-target");
    // Comprobar si el atributo "data-bs-target" ya existe 
    if (!modalTarget0) {
      // Si el atributo "data-bs-target" no existe, configurar los atributos "data-bs-target" y "data-bs-toggle" del botón
      const modalTarget = botonNuevo.setAttribute("data-bs-target", "#newtablero");
      const modalToggle = botonNuevo.setAttribute("data-bs-toggle", "modal");
      // Obtener el elemento modal con el ID "newtablero"
      const modal = document.querySelector(modalTarget);
      // Comprobar si el elemento modal existe
      if (modal) {
        // Si existe, crear un objeto Modal de Bootstrap y mostrar el modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
      }
      // Obtener nuevamente el elemento del botón
      const modalT = document.querySelector(modalToggle);
      // Comprobar si el elemento del botón existe
      if (modalT) {
        // Si existe, crear un objeto Modal de Bootstrap y mostrar el modal nuevamente
        const bsModalT = new bootstrap.Modal(modalT);
        bsModalT.show();
      }
      // Simular un clic en el botón "nuevo" para que se abra el modal recién configurado
      botonNuevo.click();
    }
  }); */


  // Agregar un evento que se ejecutará cuando el DOM esté listo
  document.addEventListener("DOMContentLoaded", function() {
  // Obtener el elemento del modal con el ID "newtablero"
  const modalNewTablero = document.getElementById("newtablero");
  // Comprobar si el elemento del modal existe en el DOM
  if (modalNewTablero) {
    // Si el modal existe, agregar un evento que se ejecutará cuando se oculte el modal
    modalNewTablero.addEventListener("hidden.bs.modal", function() {
      // Obtener el botón con el ID "nuevo"
      const botonNuevo = document.getElementById("nuevo");
      // Remover los atributos "data-bs-target" y "data-bs-toggle" del botón "nuevo"
      botonNuevo.removeAttribute("data-bs-target");
      botonNuevo.removeAttribute("data-bs-toggle");
    });
  }
  });

  function finalizar(id) {
    Swal.fire({
      icon: "question",
      title: "Atención",
      html: "¿Está seguro de finalizar la hoja del día?<br>Esta opción es irreversible.",
      showCloseButton: true, // Muestra el botón de cerrar (x) en el SweetAlert
      showCancelButton: true, // Muestra el botón de cancelar
      focusConfirm: false, // Evita que el botón "Confirmar" obtenga el foco
      confirmButtonText: "Confirmar", // Texto para el botón "Confirmar"
      confirmButtonColor: "#3085d6", // Color del botón "Confirmar"
      cancelButtonText: "Cancelar", // Texto para el botón "Cancelar"
      cancelButtonColor: "#d33", // Color del botón "Cancelar"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = "?modulo=prospectos&accion=finalizar&id=" + id;
      }
      Swal.close();
    });
  }

  
  </script>
        ';
  }
}
?>
<script>
  function selectOne(checkbox) {
      var checkboxes = document.getElementsByName("provco");
      checkboxes.forEach(function(cb) {
          if (cb !== checkbox) {
              cb.checked = false;
          }
      });
  }

  function checkChecked() {
    var checkboxes = document.getElementsByName("provco");
    var checked = false;

    checkboxes.forEach(function(cb) {
        if (cb.checked) {
            checked = true;
            return;
        }
    });

    if (checked) {
      return true;
    } else {
      return false;
    }
}
  function gmail(checkbox){
    selectOne(checkbox);
    var div = document.getElementById('condicionescorreo');
    if(!checkChecked()){
      div.innerHTML = '';
      document.getElementById("mailcorp").removeAttribute("required");
      document.getElementById("mailcorp").value = "";
    }else{
    div.innerHTML = '\
      <div class="mb-5 "> \
        <label for="">Host: *</label> \
        <input class="form-control" name="host" value="tls://smtp.gmail.com:587" type="text" readonly> \
        <label for="">Puerto: *</label> \
        <input type="number" name="port" class="form-control" value="587" readonly> \
        <label for="">Tipo de Seguridad: *</label> \
        <input type="text" name="seguridad" class="form-control" value="tls" readonly> \
        <label for="">Contraseña: *</label> \
        <input type="password" id="correopas" class="form-control" name="correopas" data-toggle="tooltip" data-placement="top" required value=""> \
        <label for="">Remitente: * </label><i class="fa fa-info" data-toggle="tooltip" data-placement="top" title="Nombre que aparcerá en el correo como remitente."></i> \
        <input type="text" data-toggle="tooltip" data-placement="top" title="Nombre que aparcerá en el correo como remitente." placeholder="Nombre que aparcerá en el correo como remitente" name="remitente" class="form-control" required> \
      </div>';
    div.style = "display: block;";
    document.getElementById("mailcorp").setAttribute("required", true);
    }
  }
  function outlook(checkbox){
    selectOne(checkbox);
    var div = document.getElementById('condicionescorreo');
    if(!checkChecked()){
      div.innerHTML = '';
      document.getElementById("mailcorp").removeAttribute("required");
      document.getElementById("mailcorp").value = "";
    }else{
    div.innerHTML = '\
      <div class="mb-5">\
        <label for="">Host: *</label>\
        <input class="form-control" name="host" value="tls://smtp.office365.com:587" type="text" readonly>\
        <label for="">Puerto: *</label>\
        <input type="number" name="port" class="form-control" value="587" readonly>\
        <label for="">Tipo de Seguridad: *</label> \
        <input type="text" name="seguridad" class="form-control" value="tls" readonly> \
        <label for="">Contraseña: *</label>\
        <input type="password" id="correopas" class="form-control" name="correopas" data-toggle="tooltip" data-placement="top" title="Sensible a Mayusculas, Minusculas, números y signos" required>\
        <label for="">Remitente: * </label> <i class="fa fa-info" data-toggle="tooltip" data-placement="top" title="Nombre que aparcerá en el correo como remitente."></i>\
        <input type="text" data-toggle="tooltip" data-placement="top" title="Nombre que aparcerá en el correo como remitente." placeholder="Nombre que aparcerá en el correo como remitente" name="remitente" class="form-control" required>\
      </div>';
    div.style = "display: block;";
    document.getElementById("mailcorp").setAttribute("required", true);
    }
  }
  function servidor(checkbox){
    selectOne(checkbox);
    var div = document.getElementById('condicionescorreo');
    if(!checkChecked()){
      div.innerHTML = '';
      document.getElementById("mailcorp").removeAttribute("required");
      document.getElementById("mailcorp").value = "";
    }else{
    div.innerHTML = '\
      <div class="mb-5">\
        <label for="">Host: *</label>\
        <input class="form-control" name="host" value="" type="text">\
        <label for="">Puerto: *</label>\
        <input type="number" name="port" class="form-control" value="">\
        <label for="">Tipo de Seguridad: *</label> \
        <select name="seguridad" class="form-control"> \
          <option value="">Ninguna</option> \
          <option value="tls">tls</option> \
          <option value="ssl">ssl</option>\
        </select>\
        <label for="">Contraseña: *</label>\
        <input type="password" id="correopas" class="form-control" name="correopas" data-toggle="tooltip" data-placement="top" title="Sensible a Mayusculas, Minusculas, números y signos" required>\
        <label for="">Remitente: * </label> <i class="fa fa-info" data-toggle="tooltip" data-placement="top" title="Nombre que aparcerá en el correo como remitente."></i>\
        <input type="text" data-toggle="tooltip" data-placement="top" title="Nombre que aparcerá en el correo como remitente." placeholder="Nombre que aparcerá en el correo como remitente." name="remitente" class="form-control" required>\
      </div>';
    div.style = "display: block;";
    document.getElementById("mailcorp").setAttribute("required", true);
    }
  }
  function comprobar(){
    $.ajax({
      url: "pruebaemailcorp.php?id=<?php echo $_SESSION['uid']; ?>",
      method: "GET",
    })
    .done(function(data){
      alert(data)
    })
  }
  $(function () {
    $('[data-toggle="tooltip"]').tooltip()  
  }); 
</script>