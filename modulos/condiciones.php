<?php
/* ini_set('display_errors',1); */

class condiciones{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelcondiciones(isset($obj));
  }
  function index(){
    $this->view = new viewcondiciones($this->model);
    $this->view->browse();
  }
  function insert(){
    //foreachdie();
    if(isset($_POST['obliga'])) $obliga = "1";
    else $obliga = "0";
    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";
    $this->model->setdata(NULL, $_POST['nmb'], $estatus, $obliga);
    $this->model->insert();
    redirect('?modulo=condiciones&accion=index');
  }

  function update(){
    //foreachdie();
    if(isset($_POST['obliga'])) $obliga = "1";
    else $obliga = "0";
    if(isset($_POST['estatus'])) $estatus = "A";
    else $estatus = "I";
    $this->model->setdata($_REQUEST['id'], $_POST['nmb'], $estatus, $obliga);
    $this->model->update();
    redirect('?modulo=condiciones&accion=index');
  }
  function delete(){
    $this->model->delete($_REQUEST['id']);
    redirect('?modulo=condiciones&accion=index');
  }

}

class modelcondiciones{
  function setdata($id, $nmb, $estatus, $obligatorio){
    mb_internal_encoding("UTF-8");
    $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
    $cambio = "";
    $this->id = $id;
    $this->nmb = clearvmayus($nmb);
    $this->estatus = $estatus;
    $this->obligatorio = $obligatorio;
  }
  function insert(){
    $sql = 'INSERT INTO condicionesc SET c_nmb = "'.$this->nmb.'",
                                        c_estatus = "'.$this->estatus.'",
                                        c_obligatorio = "'.$this->obligatorio.'"';
    setq($sql);                                    
  }

  function update(){
    $sql = 'UPDATE condicionesc SET c_nmb = "'.$this->nmb.'",
                c_estatus = "'.$this->estatus.'",
                c_obligatorio = "'.$this->obligatorio.'" WHERE c_id = "'.$this->id.'"';
    setq($sql);     
  } 
  function delete($id){
    $sql = 'DELETE FROM condicionesc WHERE c_id = "'.$id.'"';
    setq($sql);  
  }
}

class viewcondiciones{
  function __construct($model){
    ?>
    <script>
    function checkguardar(){
      document.getElementById("sendform").innerHTML = "Guardando";
      document.getElementById("sendform").disabled = true;
      return true;
    }
    </script>
    <?php
  }

  function browse(){

    $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/setcondicion.php" href="javascript:;">
    <i class="fa fa-plus"></i> Nuevo
  </a>';

  toolbar("CONDICIONES COMERCIALES","","", $nuevo);

    echo '
    <div class="card-body">
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table table-striped" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th><b>Descripción</b></th>
                    <th><b>Estatus</b></th>
                    <th><b>Obligatoria</b></th>
                    <th><b>Acciones</b></th>
                  </tr>
                </thead>'; 
    $sql = 'SELECT * FROM condicionesc';
    $result = setq($sql);                
    while ($row = $result->fetch_array()) {
        if($row['c_estatus'] == "I") $estatus = "INACTIVA";
        else $estatus = "ACTIVA";
        if($row['c_obligatorio'] == "1") $obli = "SI";
        else $obli = "NO";
        echo '<tr>
        <td>'.$row['c_nmb'].'</td>
        <td>'.$estatus.'</td>
        <td>'.$obli.'</td>
        <td>';
          echo '<a data-toggle="tooltip" data-placement="top" title data-original-title="Editar condicion" data-fancybox data-type="ajax" data-src="popup/setcondicion.php?id='.$row['c_id'].'" href="javascript:;">
            <button type="button" class="btn-sm btn-info">
              <i class="fas fa-pen" style="color: #ffffff;"></i>
            </button>
          </a>';

      
          echo '<button type="button" class="btn btn-sm btn-danger" onclick="checerase('.$row['c_id'].')">
          <i data-toggle="tooltip" data-placement="top" title data-original-title="Eliminar la linea de negocio" class="fas fa-trash"></i></button>
        ';

        echo '</td>
        ';
    }
    echo '</table>
    
    <script>
        function checerase(iduni){
          var conf = confirm("¿Deseas eliminar la condicion seleccionada?\nPresiona Aceptar para borrarla");
          if(conf == true){
            window.location.href="?modulo=condiciones&accion=delete&id=" + iduni;
          }
        }
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            
        });
      </script>';
  }
}
?>