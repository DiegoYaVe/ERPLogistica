<?php
  session_start();
  ini_set('display_errors',1);

  class concesionarios {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelconcesionarios();
    }
    function index(){
      $this->model->result();
      $this->view = new viewconcesionarios($this->model);
      $this->view->browse();
    }
    function insert(){
      if(isset($_POST['estatus'])) $estatus = "A"; else $estatus = "I";
      $this->model->setdata($_POST['nombre'],$_POST['direccion'],$_POST['telefono'],$_POST['correo'],$estatus,0);  
      $this->model->insert();
      redirect('?modulo=concesionarios&accion=index');
    }
    function update(){
      if(isset($_POST['estatus'])) $estatus = "A"; else $estatus = "I";
      $this->model->setdata($_POST['nombre'],$_POST['direccion'],$_POST['telefono'],$_POST['correo'],$estatus,$_POST['id']);  
      $this->model->update();
      redirect('?modulo=concesionarios&accion=index');
    }
  }

  class modelconcesionarios {  
    function result(){
      $sql = 'SELECT * FROM concesionarios WHERE c_empresa = "'.$_SESSION['emp'].'"';
      $this->result = setq($sql);
    }
    function setdata($nombre,$direccion,$telefono,$correo,$estatus,$id){
      $this->nombre = clearvmayus($nombre);
      $this->direccion = clearvmayus($direccion);
      $this->telefono = clearvmayus($telefono);
      $this->correo = clearvminus($correo);
      $this->estatus = clearvmayus($estatus);
      $this->id = clearvmayus($id);
    }
    function insert(){
      $sql = 'INSERT INTO concesionarios SET
              c_nmb = "'.$this->nombre.'",
              c_direccion = "'.$this->direccion.'",
              c_telefono = "'.$this->telefono.'",
              c_email = "'.$this->correo.'",
              c_estatus = "'.$this->estatus.'",
              c_empresa = "'.$_SESSION['emp'].'"';
      setq($sql);
    }
    function update(){
      $sql = 'UPDATE concesionarios SET
              c_nmb = "'.$this->nombre.'",
              c_direccion = "'.$this->direccion.'",
              c_telefono = "'.$this->telefono.'",
              c_email = "'.$this->correo.'",
              c_estatus = "'.$this->estatus.'"
              WHERE c_empresa = "'.$_SESSION['emp'].'" AND c_id = "'.$this->id.'"';
      setq($sql);
    }
  }

  class viewconcesionarios{
    var $model;
    function __construct($model){  
      $this->model = $model;
      echo '
      <script language="JavaScript">
        function checksubmit(){
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
          return true;
        }
        function checksubmitaplicar(){
          document.getElementById("aplicar").value = "JD";
          document.getElementById("aplicar").disabled = true;
          return true;
        }
      </script>';
    }
    function browse(){
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
  if (e.altKey && e.which === 82) {
    window.location.reload();
  }
});
</script>
      <?php

      echo '<div class="form-inline mb-5">
        <a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setconcesionario.php" href="javascript:;" class="btn btn-primary">
          <i class="fa fa-plus"></i> Nuevo
        </a>
        <!-- <button class="btn btn-info"> Filtrar</button> -->
      </div>';

      echo '<div class="card">
        <div class="table-responsive">
          <table class="table table-hover table-striped table-bordered">            
            <thead class="bg-light-blue bg-darken-2 text-white">
              <tr class="bg-light-blue bg-darken-2 text-white">
                <th colspan="6">Concesionarios de la empresa:</th>
              </tr> 
              <tr> 
                <th>Nombre</th>
                <th>Direccion</th>
                <th>Telefono</th>
                <th>Email</th>
                <th>Estatus</th>
                <th>Editar</th>
              </tr>
            </thead>
            <tbody>';
              while($row = $this->model->result->fetch_array()){
                echo  '<tr>
                  <td>'.$row['c_nmb'].'</td>
                  <td>'.$row['c_direccion'].'</td>
                  <td>'.$row['c_telefono'].'</td>
                  <td>'.$row['c_email'].'</td>';
                  if($row['c_estatus'] == "A"){
                    $bck = "alert alert-success no-border ";
                    $estatus = "ACTIVO";
                  }elseif($row['c_estatus'] == "I"){
                    $bck = "alert alert-danger no-border";
                    $estatus = "INACTIVO";
                  }
                  echo '<td class="'.$bck.'">'.$estatus.'</td>
                  <td><a class="btn btn-info btn-sm" data-fancybox data-type="ajax" data-src="popup/setconcesionario.php?id='.$row['c_id'].'" href="javascript:;"><i class="icon-edit"></i></a></td>
                </tr>';
              }
            echo '</tbody>
          </table>
        </div>
      </div>';
    }
  }
?>