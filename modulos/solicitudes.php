<?php
ini_set('display_errors',0);
class solicitudes{
  var $model;
  var $view;
  function __construct(){
    
    $this->model = new modelsolicitud();

  }

  function index(){

    $this->model->result();
    $this->view = new viewsolicitud($this->model);
    $this->view->browse();

  }

  function insert(){
    $_POST['estatus'] = "N";
    
    $folioop = getmax('ps_folio','pr_solicitudes');
    $acrof = busca("1",'empresas','e_id','e_siglas').'-SOLI';
    if($folioop == "1"){
      $folioop = $acrof.str_pad($folioop,6,"0",STR_PAD_LEFT);
    }  

    $this->model->setdata(NULL,$_POST['proyecto'],$folioop,$_POST['descripcion'],$_POST['fechasoli'],$_POST['otro'],$_POST['estatus'],$_POST['tipo']);
    $this->model->insert();

    redirect('?modulo=solicitudes&accion=index');
  }

  

}

class modelsolicitud{
  
  function result(){

    $sql = 'SELECT * FROM pr_solicitudes ';
    $this->result = setq($sql);

  }
  function insert(){
    $sql = 'INSERT INTO pr_solicitudes SET
            ps_folio = "'.$this->folio.'",
            ps_nmb = "'.$this->nmb.'",
            ps_descripcion = "'.$this->descripcion.'",
            ps_fechasoli = "'.date('Y-m-d H:i:s').'",
            ps_otro = "'.$this->otro.'",
            ps_estatus = "'.$this->estatus.'",
            ps_tiposoli = "'.$this->tiposoli.'" ';

    setq($sql);
  }

  function setdata($id,$nmb,$folio,$descripcion,$fechasoli,$otro,$estatus,$tiposoli){

    $this->id = $id;
    $this->nmb = $nmb;
    $this->folio = $folio;
    $this->descripcion = $descripcion;
    $this->fechasoli = $fechasoli;
    $this->otro = $otro;
    $this->estatus = $estatus;
    $this->tiposoli = $tiposoli;

  }

  function select($id){
    $sql = 'SELECT * FROM pr_solicitudes WHERE ps_id = "'.$id.'" ';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['ps_id'];
    $this->nmb = $row['ps_nmb'];
    $this->folio = $row['ps_folio'];
    $this->descripcion = $row['ps_descripcion'];
    $this->fechasoli = $row['ps_fechasoli'];
    $this->otro = $row['ps_otro'];
    $this->estatus = $row['ps_estatus'];
    $this->tiposoli = $row['ps_tiposoli'];
  }


}

class viewsolicitud{

  function __construct($model){
    
    $this->model = $model;

  }

  function browse(){

    $botones = '
    <a href="javascript:;" class="btn btn-primary btn-sm" data-fancybox data-type="ajax" data-src="popup/setsolicitud.php">
      <i class="fa fa-plus"></i> Nueva
    </a>
    ';
    toolbar($_GET['modulo'],$botones);

    echo '
    <div class="card mt-3">
      <div class="card-body">
        <div class="table-responsive text-medium">
        <table class="table table-hover" id="myTable">
          <thead class="">
            <tr class="">
              <th class="">Folio</th>
              <th class="">Proyecto</th>
              <th class="">Fecha</th>
              <th class="">Tipo</th>
              <th class="">Descripcion</th>
              <th class="">Acciones</th>
            </tr>
          </thead>
          <tbody class="">';
          while($row = $this->model->result->fetch_array()){

            echo '
            <tr class="">
              <td class="">'.$row['ps_folio'].'</td>
              <td class="">'.$row['ps_nmb'].'</td>
              <td class="">'.$row['ps_fechasoli'].'</td>
              <td class="">'.busca($row['ps_tiposoli'],'pr_tiposoli','pts_siglas','pts_nmb').'</td>
              <td class="">'.$row['ps_descripcion'].'</td>
              
              <td class="">
                <a href="javascript:;" class="btn btn-primary" data-fancybox data-type="ajax" data-src="popup/setsolicitud.php?id='.$row['ps_id'].'">
                  <i class="fa fa-eye"></i> Detalles
                </a>
              </td>
            </tr>
            ';

          }
          echo '
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <script>
    $(document).ready(function () {
      var windowHeight = $(window).height();
    
      $("#myTable").DataTable( {
          paging: true,
          scrollY: windowHeight * 0.5,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          responsivePriority: 1,
          pageLength: 50,
      });
    });
    </script>

    ';
  }

}

?>