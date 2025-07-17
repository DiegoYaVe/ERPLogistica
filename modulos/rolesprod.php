<?php
/* ini_set('display_errors', 1); */
  class rolesprod{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelrolesprod(isset($obj));
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
      $this->view = new viewrolesprod($this->model);
      $this->view->browse();
    }

    function setrol(){
        if(isset($_POST['estatus'])){
            $estatus = "A";
        } else{
            $estatus = "I";
        }

      $this->model->setdata($_POST['id'],$_POST['nmb'],$_POST['grupo'],$estatus,$_POST['descripcion']);

      $this->model->setrol();
      if(!empty($_POST['id'])){
        $idrol = $_POST['id'];
      } else{
        $idrol = getmax('rp_id', 'rolesprod', false, false);
      }
      $this->model->setprocesorol($idrol);

      redirect("?modulo=rolesprod&accion=index");
    }

    function show(){
      $this->view = new viewrolesprod($this->model);
      $this->view->show();
    }
    function empleados(){
      if(!isset($_POST['nmb'])) $_POST['nmb'] = NULL;
      if(!isset($_POST['estatus'])) $_POST['estatus'] = "A";
      if(!isset($_POST['rol'])) $_POST['rol'] = NULL;

      $this->model->resultempleados($_POST['nmb'],$_POST['rol'],$_POST['estatus']);
      $this->view = new viewrolesprod($this->model);
      $this->view->empleados($_POST['nmb'],$_POST['rol'],$_POST['estatus']);
    }
    function insertempleado(){
      if(isset($_POST['estatus'])) $estatus = "A";
      else $estatus = "I";

      $val = 0;

      while($val == 0){
        $contraseña = rand(1000, 9999);
        $check = busca($contraseña, 'pr_empleados', 'pe_passwordse', 'COUNT(*)');
        if(intval($check) <= 0){
          $val = 1;
        }
      }

      $this->model->setdataempleado(NULL,$_POST['nmb'],$_POST['rol'],$contraseña,$estatus);
      $this->model->insertempleado();

      redirect("?modulo=rolesprod&accion=empleados");
    }
    function updateempleado(){
      if(isset($_POST['estatus'])) $estatus = "A";
      else $estatus = "I";

      $this->model->setdataempleado($_GET['id'],$_POST['nmb'],$_POST['rol'],'',$estatus);
      $this->model->updateempleado();

      redirect("?modulo=rolesprod&accion=empleados");
    }
  }

  class modelrolesprod{
    
    function select($id){
      $sql = "SELECT * FROM rolesprod WHERe rp_id = '".$id."'";
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['rp_id'];
      $this->nmb = $row['rp_nmb'];
      $this->grupo = $row['rp_grupo'];
      $this->estatus = $row['rp_estatus'];
      $this->descripcion = $row['rp_descripcion'];
      
    }
    function result($page,$nmb,$grupo,$estatus){
      $pagenum = 50;
      $sql = 'SELECT * FROM rolesprod ORDER BY rp_id ASC';
      $sql.=' LIMIT '.($pagenum*$page).','.$pagenum;

      $this->result = setq($sql);
      $this->resultt = setq($sql);
    }
    function setdata($id,$nmb,$grupo,$estatus,$descripcion){
      $this->id = $id;
      $this->nmb = clearvmayus($nmb);
      $this->grupo = clearvmayus($grupo);
      $this->estatus = clearvmayus($estatus);
      $this->descripcion = clearvmayus($descripcion);
    }
    function setrol(){
      $sql = "INSERT INTO rolesprod SET
              rp_id = '".$this->id."',
              rp_grupo = '".$this->grupo."',
              rp_nmb = '".$this->nmb."',
              rp_estatus= '".$this->estatus."',
              rp_descripcion = '".$this->descripcion."'
              ON DUPLICATE KEY UPDATE
              rp_grupo = '".$this->grupo."',
              rp_nmb = '".$this->nmb."',
              rp_estatus= '".$this->estatus."',
              rp_descripcion = '".$this->descripcion."'";
      setq($sql);
    }

    function setprocesorol($idrol){
      $sqlr = 'SELECT ppr_id FROM pr_procesos WHERE ppr_estatus = "A"';
      $resultr = setq($sqlr);
      while($rowp = $resultr->fetch_array()){
        if(isset($_POST['rol'.$rowp['ppr_id']])){
          $sql2 = 'DELETE FROM roles_permisos WHERE rp_idrol = "'.$idrol.'" AND rp_proceso = "'.$rowp['ppr_id'].'"';
          setq($sql2);

          $sql = "INSERT INTO roles_permisos SET
              rp_idrol = '".$idrol."',
              rp_proceso = '".$rowp['ppr_id']."'
              ";
          setq($sql);
        } else{
          $sql = 'DELETE FROM roles_permisos WHERE rp_idrol = "'.$idrol.'" AND rp_proceso = "'.$rowp['ppr_id'].'"';
          setq($sql);
        }
      }
    }
    function selectempleado($id){
      $sql = 'SELECT * FROM pr_empleados WHERE pe_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['pe_id'];
      $this->nmb = $row['pe_nmb'];
      $this->rol = $row['pe_rol'];
      $this->password = $row['pe_password'];
      $this->passwordse = $row['pe_passwordse'];
      $this->estatus = $row['pe_estatus'];
    }

    function setdataempleado($id,$nmb,$rol,$password,$estatus){
      $this->id = clearvmayus($id);
      $this->nmb = clearvmayus($nmb);
      $this->rol = clearvmayus($rol);
      $this->password = $password;
      $this->estatus = clearvmayus($estatus);
    }

    function resultempleados($nmb,$rol,$estatus){
      $sql = 'SELECT * FROM pr_empleados WHERE pe_estatus = "'.$estatus.'" ';
      if($nmb) $sql.=' AND pe_nmb LIKE "%'.$nmb.'%" ';
      if($rol) $sql.=' AND pe_rol = "'.$rol.'" ';
      $sql.=' ORDER BY pe_nmb ASC';
      $this->resulte = setq($sql);
    }
    function insertempleado(){
      $sql = 'INSERT INTO pr_empleados SET
              pe_nmb = "'.$this->nmb.'",
              pe_rol = "'.$this->rol.'",
              pe_password = PASSWORD("'.$this->password.'"),
              pe_passwordse = "'.$this->password.'",
              pe_estatus = "'.$this->estatus.'"';
      setq($sql);
      $this->id = getmax('pe_id', 'pr_empleados', false, false);
    }
    function updateempleado(){
      $sql = 'UPDATE pr_empleados SET
              pe_nmb = "'.$this->nmb.'",
              pe_rol = "'.$this->rol.'",
              pe_estatus = "'.$this->estatus.'"
              WHERE pe_id = "'.$this->id.'"'; //pe_password = PASSWORD("'.$this->password.'"),
      setq($sql);
    }
}

class viewrolesprod{
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
  function browse(){
    //Sección: E1 Encabezado - Botones de acción

    $nuevo = '<a data-fancybox data-type="ajax" data-src="popup/setrol.php" href="javascript:;">
                <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Crear un nuevo rol"><i class="fa fa-plus"></i> Nuevo</button>
            </a>';
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

    toolbar("ROLES DE PRODUCCIÓN",$nuevo);

    echo '<div class="card mt-3">
      <div class="card-body">';
        echo '<div class="">
          <div class="table-responsive">
            <center>
              <table class="mb-0 table table-hover" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th><b>Nombre</b></th>
                    <th><b>Descripción</b></th>
                    <th><b>Estatus</b></th>
                    <th><b>Ver</b></th>
                  </tr>
                </thead>';
                while($row = $this->model->result->fetch_array()){
                    if($row['rp_estatus'] == "A"){
                        $estatus = "Activo";
                    } else{
                        $estatus = "Inactivo";
                    }
                  echo '<tr>
                    <td>'.$row['rp_nmb'].'</td>
                    <td>'.$row['rp_descripcion'].'</td>                    
                    <td>'.$estatus.'</td>
                    <th>
                        <a data-fancybox data-type="ajax" data-src="popup/setrol.php?id='.$row['rp_id'].'" href="javascript:;">
                            <button type="button" class="btn btn-sm btn-secondary" /><i class="fas fa-edit"></i> Editar
                            </button>
                        </a>
                    </th>
                  </tr>';
                }
              echo '</table>';
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
          }
      });
    </script>
    ';


  }
  function empleados($nmb, $rol, $estatus){
    //Sección: E1 Encabezado - Botones de acción

    $nuevo = '<a data-fancybox data-type="ajax" data-src="popup/setempleadoprod.php" href="javascript:;">
                <button type="button" class="btn btn-sm btn-primary mb-1 mr-1" data-toggle="tooltip" data-placement="top" title="Crear un nuevo rol"><i class="fa fa-plus"></i> Nuevo</button>
            </a>';


    toolbar("EMPLEADOS DE PRODUCCION",$nuevo);

    echo '<div class="card mt-3">
      <div class="card-body">';
        echo '<div class="">
          <div class="table-responsive">
            <center>
              <table class="mb-0 table table-hover" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th><b>Nombre</b></th>
                    <th><b>Rol</b></th>
                    <th><b>Estatus</b></th>
                    <th><b>Ver</b></th>
                  </tr>
                </thead>';
                while($row = $this->model->resulte->fetch_array()){
                    if($row['pe_estatus'] == "A"){
                        $estatus = "Activo";
                    } else{
                        $estatus = "Inactivo";
                    }
                  echo '<tr>
                    <td>'.$row['pe_nmb'].'</td>
                    <td>'.busca($row['pe_rol'],'rolesprod','rp_id','rp_nmb').'</td>
                    <td>'.$estatus.'</td>
                    <th>
                        <a data-fancybox data-type="ajax" data-src="popup/setempleadoprod.php?id='.$row['pe_id'].'" href="javascript:;">
                            <button type="button" class="btn btn-sm btn-secondary" /><i class="fas fa-edit"></i> Editar
                            </button>
                        </a>
                    </th>
                  </tr>';
                }
              echo '</table>';
            echo '</center>
          </div>
        </div>
      </div>
    </div>';

    echo '
    <script>
      $(document).ready(function () {
        var windowHeight = $(window).height();
        $("#myTable").DataTable( {
            paging: true,
            //"order": [[3, "desc"]],
            ordering: false,
            scrollY: windowHeight * 0.5,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength:50,
        });
      });
      </script>
    ';

  }

}
?>