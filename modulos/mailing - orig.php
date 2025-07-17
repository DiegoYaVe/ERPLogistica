<?php
//ini_set('display_errors',1);
class mailing{
  var $model;
  var $view;
  function __construct(){
    $this->model = new modelmailing(isset($obj));
  }
  function index(){
    
    if(!isset($_REQUEST['nombre'])) $_REQUEST['nombre'] = NULL;
    if(!isset($_REQUEST['responsable'])) $_REQUEST['responsable'] = NULL;
    if(!isset($_REQUEST['temporalidad'])) $_REQUEST['temporalidad'] = NULL;
    
    $this->model->resultcampana($_REQUEST['nombre'],$_REQUEST['responsable'],$_REQUEST['temporalidad']);
    $this->view = new viewmailing($this->model);
    $this->view->browse($_REQUEST['nombre'],$_REQUEST['responsable'],$_REQUEST['temporalidad']);
  }
  function edit(){
    if(!isset($_GET['id'])) $_GET['id'] = NULL;
    $this->model->selectcampana($_GET['id']);
    $this->view = new viewmailing($this->model);
    $this->view->edit();
  }
  function setcampana(){

    if(isset($_POST['campana'])) $idC = $_POST['campana']; else $idC = getmax("mc_id","mailing_campanas");
    if(isset($_POST['detalle'])) $idD = $_POST['detalle']; else $idD = getmax("md_id","mailing_campanasd");

    $this->model->setdata($nuser,$_POST['nmb'],$_POST['finicio'],$_POST['fsig'],$_POST['temporalidad'],$_POST['responsable'],
                            $idD,$_POST['asunto'],$_POST['cuerpo'],$_POST['flanzamiento'],$_POST['orden']);
    $this->model->setcampana();
    $this->model->setdetalle();
    redirect("?modulo=mailing&accion=index");
  }
  function showcampana(){
    if(!isset($_REQUEST['asunto'])) $_REQUEST['asunto'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;

    $this->model->resultcampanad($_GET['id'],$_REQUEST['asunto'],$_REQUEST['estatus']);
    $this->view = new viewmailing($this->model);
    $this->view->browsecampanad($_REQUEST['asunto'],$_REQUEST['estatus']);
  }
  function selectcampana(){
    $this->model->selectcampanad($_GET['idd'],$_GET['id']);
    $this->view = new viewmailing($this->model);
    $this->view->editD();
  }
  function insertcampana(){
    //foreach($_POST as $campo => $valor){	echo "POST->". $campo ."= ". $valor.'<br>'; }
    //foreach($_GET as $campo => $valor) {	echo "GET->". $campo ."= ". $valor.'<br>'; }
    //die(); 
    if(!isset($_REQUEST['id'])) $_REQUEST['id'] = NULL;
    if(!isset($_REQUEST['nombre'])) $_REQUEST['nombre'] = NULL;
    if(!isset($_REQUEST['fechai'])) $_REQUEST['fechai'] = NULL;
    if(!isset($_REQUEST['fechas'])) $_REQUEST['fechas'] = NULL;
    if(!isset($_REQUEST['temporalidad'])) $_REQUEST['temporalidad'] = NULL;
    if(!isset($_REQUEST['responsable'])) $_REQUEST['responsable'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
    if(!isset($_REQUEST['segmento'])) $_REQUEST['segmento'] = NULL;

    $this->model->insertcampana($_REQUEST['id'],$_REQUEST['nombre'],$_REQUEST['fechai'],$_REQUEST['fechas'],$_REQUEST['temporalidad'],$_REQUEST['responsable'],$_REQUEST['estatus'],$_REQUEST['segmento']);

    redirect('?modulo=mailing&accion=index');
  }
  function updatecampana(){

    if(!isset($_REQUEST['id'])) $_REQUEST['id'] = NULL;
    if(!isset($_REQUEST['nombre'])) $_REQUEST['nombre'] = NULL;
    if(!isset($_REQUEST['fechai'])) $_REQUEST['fechai'] = NULL;
    if(!isset($_REQUEST['fechas'])) $_REQUEST['fechas'] = NULL;
    if(!isset($_REQUEST['temporalidad'])) $_REQUEST['temporalidad'] = NULL;
    if(!isset($_REQUEST['responsable'])) $_REQUEST['responsable'] = NULL;
    if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
    if(!isset($_REQUEST['segmento'])) $_REQUEST['segmento'] = NULL;
    
    $this->model->updatecampana($_REQUEST['id'],$_REQUEST['nombre'],$_REQUEST['fechai'],$_REQUEST['fechas'],$_REQUEST['temporalidad'],$_REQUEST['responsable'],$_REQUEST['estatus'],$_REQUEST['segmento']);

    redirect('?modulo=mailing&accion=index');



  }


  function insertcampanad(){

    if(isset($_GET['idd'])) $idD = $_GET['idd']; else $idD = getmax("md_id","mailing_campanasd");
    $id = $_GET['id'];
    $this->model->setdata($_GET['id'],"","","","","",$idD,$_POST['asunto'],$_POST['cuerpoG'],$_POST['fsig'],$_POST['orden']);
    $this->model->insertcampanad();
    redirect("?modulo=mailing&accion=showcampana&id=$id");
  }
  function updatecampanad(){
    $id = $_GET['id'];
    $this->model->setdata($_GET['id'],"","","","","",$_POST['idD'],$_POST['asunto'],$_POST['cuerpoG'],"",$_POST['orden']);
    $this->model->updatecampanad();
    redirect("?modulo=mailing&accion=showcampana&id=$id");
  }
  function updateord(){
    $id = $_GET['id'];
    $this->model->updateord($_GET['id'],$_GET['idd'],$_POST['orden']);
    redirect("?modulo=mailing&accion=showcampana&id=$id");
  }
  function selectemail(){
    $id = $_GET['id'];
    $idd = $_GET['idd'];
    $this->model->selectemail($id,$idd);
    $this->view = new viewmailing($this->model);
    $this->view->browseemail($id,$idd);

  }
}

class modelmailing{
  function resultcampana($nombre,$responsable,$temporalidad){
    $sql = 'SELECT * FROM mailing_campanas WHERE 1 ';
    if($nombre) $sql.= ' AND mc_nmb LIKE "%'.$nombre.'%"';
    if($responsable) $sql.= ' AND mc_responsable = "'.$responsable.'"';
    if($temporalidad) $sql.= ' AND mc_temporalidad = "'.$temporalidad.'"';

    $sql.=' ORDER BY mc_id ASC';
    $this->result = setq($sql);

  }


  function selectcampana($id){
    $sql = 'SELECT * FROM mailing_campanas WHERE 1 AND mc_id = "'.$id.'"';
    //if($id) $sql.= ' AND mc_id = "'.$id.'"';
    $result = setq($sql);
    $row = $result->fetch_array();
    $this->id = $row['mc_id'];
    $this->nombre = $row['mc_nmb'];
    $this->fechainicio = $row['mc_finicio'];
    $this->fechasiguiente = $row['mc_fsiguiente'];
    $this->temporalidad = $row['mc_temporalidad'];
    $this->responsable = $row['mc_responsable'];
    $this->segmento = $row['mc_segmento'];
    $this->estatus = $row['mc_estatus'];
  }

  function selectcampanad($idD,$idC){
    //
    if(isset($idD)){
      $sql = ' SELECT * FROM mailing_campanas INNER JOIN mailing_campanasd ON mc_id = md_campana 
      WHERE mc_id = "'.$idC.'" AND md_id = "'.$idD.'"';
    }else{
      $sql = 'SELECT * FROM mailing_campanas WHERE mc_id = "'.$idC.'"';
    }
    //
    $result = setq($sql);
    $row = $result->fetch_array();

    $this->idC = $row['mc_id'];
    $this->nmb = $row['mc_nmb'];
    $this->finicio = $row['mc_finicio'];
    $this->fsiguiente = $row['mc_fsiguiente'];
    $this->temp = $row['mc_temporalidad'];
    $this->responsable = $row['mc_responsable'];
    $this->estatusC = $row['mc_estatus'];
    if(isset($idD)){
      $this->idD = $row['md_id'];
      $this->asunto = $row['md_asunto'];
      $this->cuerpo = $row['md_cuerpo'];
      $this->lanzamiento = $row['md_flanzamiento'];
      $this->orden = $row['md_orden'];
      $this->estatusD = $row['md_estatus'];
    }
  }
  function resultcampanad($idC,$asunto,$estatus){
    $sql = 'SELECT * FROM mailing_campanasd WHERE md_campana = "'.$idC.'"';
    if($asunto) $sql.= ' AND md_asunto LIKE "%'.$asunto.'%"';
    if($estatus != null) $sql.= ' AND md_estatus = "'.$estatus.'" ';
    $sql.=' ORDER BY md_orden, md_id ASC';
    $this->result = setq($sql);
  }
  function setdata($idC,$nmb,$finicio,$fsig,$temp,$responsable,$idD,$asunto,$cuerpo,$flanzamiento,$orden){
    $this->idC = $idC;
    $this->nmb = $nmb;
    $this->finicio = $finicio;
    $this->fsiguiente = $fsig;
    $this->temp = $temp;
    $this->responsable = $responsable;

    $this->idD = $idD;
    $this->asunto = $asunto;
    $this->cuerpo = $cuerpo;
    $this->lanzamiento = $flanzamiento;
    $this->orden = $orden;
  }
  function updatecampana($id,$nombre,$fechai,$fechas,$temporalidad,$responsable,$estatus,$segmento){
    if($estatus == "on"){
      $estatus = "A";
    }elseif($estatus == ""){
      $estatus = "I";
    }
    $sql = 'UPDATE mailing_campanas SET
            mc_nmb = "'.$nombre.'",
            mc_finicio = "'.$fechai.'",
            mc_fsiguiente = "'.$fechas.'",
            mc_temporalidad = "'.$temporalidad.'",
            mc_responsable = "'.$responsable.'",
            mc_segmento = "'.$segmento.'",
            mc_estatus = "'.$estatus.'" 
            WHERE mc_id = "'.$id.'"';
    setq($sql);

  }
  function insertcampana($id,$nombre,$fechai,$fechas,$temporalidad,$responsable,$estatus,$segmento){
    if($estatus == "on"){
      $estatus = "A";
    }elseif($estatus == ""){
      $estatus = "I";
    }

    $sql = 'INSERT INTO mailing_campanas SET
            mc_nmb = "'.$nombre.'",
            mc_finicio = "'.$fechai.'",
            mc_fsiguiente = "'.$fechas.'",
            mc_temporalidad = "'.$temporalidad.'",
            mc_responsable = "'.$responsable.'",
            mc_segmento = "'.$segmento.'",
            mc_estatus = "'.$estatus.'"';
    setq($sql);

  }
  function insertcampanad(){
    $sql = "INSERT INTO mailing_campanasd SET
            md_id = '".$this->idD."',
            md_campana = '".$this->idC."',
            md_asunto = '".$this->asunto."',
            md_cuerpo = '".$this->cuerpo."',
            md_flanzamiento = '".$this->lanzamiento."',
            md_orden = '".$this->orden."',
            md_estatus= 'N'";
    
    setq($sql);
  }
  function updatecampanad(){
    $sql = "UPDATE mailing_campanasd SET
            md_asunto = '".$this->asunto."',
            md_cuerpo = '".$this->cuerpo."',
            md_orden = '".$this->orden."'
            WHERE md_id = '".$this->idD."'";
    setq($sql);
  }
  function updateord($idC,$idD,$orden){
    $sql = 'UPDATE  mailing_campanasd SET md_orden = "'.$orden.'" WHERE md_id = "'.$idD.'"
            AND md_campana = "'.$idC.'"';
    setq($sql) or die($sql);
  }
  function selectemail($id,$idd){
    /*$sql = 'SELECT * FROM mailing_envios INNER JOIN mailing_campanas ON me_campana = mc_id 
    INNER JOIN mailing_campanasd ON md_id = me_correo
    WHERE me_campana = "'.$id.'" AND me_correo = "'.$idd.'"';*/
    $sql = 'SELECT * FROM mailing_envios INNER JOIN mailing_campanas ON me_campana = mc_id 
    INNER JOIN mailing_campanasd ON md_id = me_correo
    WHERE me_campana = "'.$id.'" AND me_correo = "'.$idd.'"';
    $this->result = setq($sql);
  }

}

class viewmailing{

  var $model;
  function __construct($model){
    $this->tipest = array("N"=>"Nuevo","F"=>"Finalizado");
    $this->model = $model;
  }

  function browse($nmb,$responsable,$temporalidad){

    if($temporalidad == " " || $temporalidad == NULL) $est = "selected";
    elseif($temporalidad = "A") $est0 = "selected";
    elseif($temporalidad = "U") $est1 = "selected";
    elseif($temporalidad = "M") $est2 = "selected";

    //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions"><div class="col-md-2">';
    echo '<a href="?modulo=mailing&accion=edit" accesskey="">
        <button type="button" class="btn btn-blue mr-2 ml-2"><i class="fa fa-plus"></i> Nuevo </button>
      </a></div>';

    //Sección: E2 Encabezado - Filtros
    ?>
    <div class="col-md-1">
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>
    <div id="filter-panel" class="col-md-9 collapse filter-panel">
            <div class="panel panel-default">
              <div class="panel-body">
                <form class="form-inline" role="form" method="post" action="?modulo=mailing&accion=index">
                  <div class="mb-5 mr-2">
                    <input type="text" class="form-control" id="pref-search" name="nombre" value="<?php echo $nmb ?>" placeholder="Buscar por nombre de campaña">
                  </div><!-- form group [search] -->
                  <div class="mb-5 mr-2">
                    <select id="pref-orderby" class="form-control" name="responsable" placeholder="Responsable">
                      <option value="">RESPONSABLE</option>
                      <?php
                        $sql = 'SELECT DISTINCT(mc_responsable), u_id FROM mailing_campanas INNER JOIN usuarios ON u_id = mc_responsable WHERE u_estatus = "A"';
                        $result = setq($sql);
                        while($row = $result->fetch_array()){
                          if($responsable == $row['mc_responsable']) $sel = "selected"; else $sel = "";
                      ?>
                          <option value="<?php echo $row['mc_responsable'] ?>" <?php echo $sel; ?>><?php echo $row['mc_responsable'] ?></option>
                      <?php
                        }
                      ?>
                    </select>
                  </div> <!-- form group [order by] -->
                  <div class="mb-5 mr-2">
                    <select id="pref-orderby" class="form-control" name="temporalidad">
                      <option value="" <?php echo $est?> >TEMPORALIDAD</option>
                      <option value="A" <?php echo $est0?> >SEMANAL</option>
                      <option value="U" <?php echo $est1 ?> >UNICA</option>
                      <option value="M" <?php echo $est2 ?> >MENSUAL</option>
                    </select>
                  </div> <!-- form group [order by] -->
                  <div class="mb-5">
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                  </button>
                  <a href="?modulo=mailing&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                  </button></a>
                  </div>
                </form>
              </div>
            </div>
          </div>
    <?php
    echo '</div></div>';

      echo '
      <div class="main-card mb-3 card">
      <div class="card-body row">';

      echo '<div class="col-12">
      <div class="table-responsive">
      <center><table width="100%" class="mb-0 table table-hover">
      <thead class="bg-light-blue bg-darken-2 text-white">
      <tr>
      <th><b>Campaña</b></th>
      <th><b>Siguiente envio</b></th>
      <th><b>Temporalidad</b></th>
      <th><b>Responsable</b></th>
      <th><b>Acciones</b></th>
      </tr></thead>';
      while($row = $this->model->result->fetch_array()){
        $sqlcount = 'SELECT COUNT(*) FROM mailing_envios WHERE me_campana = "'.$row['mc_id'].'"';
        $resultcount = setq($sqlcount);
        $rowcount = $resultcount->fetch_array();
        echo '<tr>
        <th>'.$row['mc_nmb'].'</th>
        <td>'.fecha_formato(date("d-m-Y", strtotime($row['mc_fsiguiente'])),false,false).'</td>';
        if($row['mc_temporalidad'] == "A"){
          echo '<td>Repetible Semanalmente</td>'; 
        }elseif($row['mc_temporalidad'] == "M"){
          echo '<td>Repetible Mensualmente</td>'; 
        }elseif ($row['mc_temporalidad'] == "U")
          echo '<td>Unica</td>';
        echo '<td>'.$row['mc_responsable'].'</td>
        <th>
        <a href="?modulo=mailing&accion=edit&id='.$row['mc_id'].'"><button type="button" class="btn btn-info" /><i class="icon-edit2"></i> Editar</button></a>
        <a href="?modulo=mailing&accion=showcampana&id='.$row['mc_id'].'"><button type="button" class="btn btn-secondary" /><i class="icon-envelope"></i> Ver Correos</button></a>
        <span class="tag tag-pill tag-danger tag-up">'.$rowcount['COUNT(*)'].'</span></a>
        </th>
        </tr>';
      }
      echo '</table>';

  }

  function browsecampanad($asunto,$estatus){
    //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions"><div class="col-md-4">';
    echo '<a href="?modulo=mailing&accion=index">
            <button class="ml-2 mr-2 btn btn-warning">
              <i class="fa fa-arrow-left"></i> Atrás
            </button>
          </a>
          <a href="?modulo=mailing&accion=selectcampana&id='.$_GET['id'].'" accesskey="">
            <button type="button" class="btn btn-blue mr-2 ml-2"><i class="fa fa-plus"></i> Nuevo </button>
          </a>
        </div>';

    $estn = "";
    $estf = "";
    $estt = "";
    if(!isset($estatus) || $estatus == null) $estt = "selected";
    elseif($estatus == "F") $estf = "selected";
    else $estn = "selected";
    //Sección: E2 Encabezado - Filtros
    ?>
    <div class="col-md-1">
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>
    <div id="filter-panel" class="col-md-7 collapse filter-panel">
            <div class="panel panel-default">
              <div class="panel-body">
                <?php
                echo '<form class="form-inline" role="form" method="post" action="?modulo=mailing&accion=showcampana&id='.$_GET['id'].'">';
                ?>
                  <div class="mb-5 mr-2">
                    <input type="text" class="form-control" id="pref-search" name="asunto" value="<?php echo $asunto ?>" placeholder="Buscar por asunto">
                  </div><!-- form group [search] -->
                  <div class="mb-5 mr-2">
                    <select id="pref-orderby" class="form-control" name="estatus">
                      <option value="N" <?php echo $estn ?> >Nuevos</option>
                      <option value="F" <?php echo $estf ?> >Finalizados</option>
                      <option value="" <?php echo $estt ?> >Todos</option>
                    </select>
                  </div> <!-- form group [order by] -->
                  <div class="mb-5">
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                  </button>
                  <?php
                  echo '<a href="?modulo=mailing&accion=showcampana&id='.$_GET['id'].'"><button type="button" class="btn btn-warning">';
                  ?>
                    <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                  </button></a>
                  </div>
                </form>
              </div>
            </div>
          </div>
    <?php
    echo '</div></div>';

    echo '
    <div class="main-card mb-3 card">
    <div class="card-body row">';

    echo '<div class="col-12">
    <div class="table-responsive">
    <center><table width="100%" class="mb-0 table table-hover">
    <thead class="bg-light-blue bg-darken-2 text-white">
    <tr>
    <th><b>Asunto</b></th>
    <!-- <th>Preview</th> -->
    <th width="7%"><b>Orden</b></th>
    <th><b>Ver</b></th>
    </tr></thead>';
    while($row = $this->model->result->fetch_array()){
      $sqlcount = 'SELECT COUNT(*) FROM mailing_envios WHERE me_correo = "'.$row['md_id'].'"';
      $resultcount = setq($sqlcount);
      $rowcount = $resultcount->fetch_array();
      echo '<tr>
      <th>'.$row['md_asunto'].'</th>';
      //<td>'.date("d-m-Y", strtotime($row['md_flanzamiento'])).'</td>';
      //echo '<td><div class="" style="overflow: scroll; max-width: 212px; max-height: 255px;">'.$row['md_cuerpo'].'</div></td>';
      echo '<form action="?modulo=mailing&accion=updateord&id='.$row['md_campana'].'&idd='.$row['md_id'].'" method="post" onsubmit="return checkSubmitenviar();">
                <td><input type="number" class="form-control" onchange="submit();" name="orden" value="'.$row['md_orden'].'" /></td>
           </form>
      <th>
      <a href="?modulo=mailing&accion=selectcampana&id='.$_GET['id'].'&idd='.$row['md_id'].'"><button type="button" class="btn btn-primary" /><i class="icon-edit2"></i> Editar</button></a>
      <a href="?modulo=mailing&accion=selectemail&id='.$_GET['id'].'&idd='.$row['md_id'].'"><button type="button" class="btn btn-success" /><i class="icon-envelope"></i> Enviados</button>
      <span class="tag tag-pill tag-danger tag-up">'.$rowcount['COUNT(*)'].'</span></a>
      </th>
      </tr>';
    }
    echo '</table>';
  }
  function edit(){
    if($this->model->id){
      $accion = 'updatecampana';
      $titulo = 'Actualizar Campaña';
      $readonly = 'readonly';
    }
    else{
      $accion = 'insertcampana';
      $titulo = 'Insertar Campaña';
      $readonly = '';
    }


    if(!isset($this->model->temporalidad) || $this->model->temporalidad == null) $est0 = " ";
    elseif($this->model->temporalidad == "A") $est0 = "SEMANAL";
    elseif($this->model->temporalidad == "M") $est0 = "MENSUAL";
    elseif($this->model->temporalidad == "U") $est0 = "UNICA";

    echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
    echo '<a href="?modulo=mailing&accion=index">
            <button class="mb-2 mr-2 btn btn-warning">
              <i class="fa fa-arrow-left"></i> Atrás
            </button>
          </a></div></div>';

        echo  '
    <div class="row">
    <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title" id="basic-layout-form">'.$titulo.'</h4>
        <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
        <div class="heading-elements">
          <ul class="list-inline mb-0">
            <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
            <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
            <li><a data-action="close"><i class="icon-cross2"></i></a></li>
          </ul>
        </div>-->
      </div>
    <div class="card-body collapse in">
    <form method="POST" action="?modulo=mailing&accion='.$accion.'">
      <div class="card-block">
          <div class="form-body">
            <div class="row">
              <div class="col-md-4" hidden>
                <div class="mb-5">
                  <label for="username">ID de Campaña</label>
                  <input type="text" id="id" value="'.$this->model->id.'" class="form-control" placeholder="ID de Campaña" name="id" onkeyup="compUsuario(event)">
                  <div id="DivDestino"></div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-5">
                  <label for="nombre">Nombre</label>
                  <input type="text" id="nombre" value="'.$this->model->nombre.'" class="form-control" placeholder="Nombre de Campaña" name="nombre" autofocus="autofocus" required="required">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-5">
                  <label for="fechai">Fecha de Inicio</label>
                  <input type="date" id="fechai" value="'.$this->model->fechainicio.'" class="form-control" placeholder="" name="fechai" required="required">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-5">
                  <label for="username">Segmento</label>
                  <select id="segmento" class="form-control" name="segmento" placeholder="Segmento">';
                    $es = array("1","2","3","4","5");
                    $es1 = array("Público en General","Distribuidores","Todos","Rechazados","En Carrito y Proceso de Pago");
                    for ($i = 0; $i < count($es); $i++){ 
                      if ($this->model->segmento == $es[$i]) {
                        $sel = 'selected';
                      }else
                        $sel = '';
                        echo '<option value="'.$es[$i].'" '.$sel.'>'.$es1[$i].'</option>';
                      }
                        echo '</select>
                </div>
              </div>
            </div>
            <div class="row mt-1">
              <div class="col-md-4">
                <div class="mb-5">
                  <label for="fechas">Fecha de Siguiente Aparición</label>
                  <input type="date" id="fechas" value="'.$this->model->fechasiguiente.'" class="form-control" placeholder="" name="fechas" required="required">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-5">
                  <label>Temporalidad de Campaña</label>
                  <select class="form-control" name="temporalidad" id="temporalidad" onchange="temporalidadf()" required="required">';
                  if(empty($this->model->temporalidad)) echo '<option value="">Seleccione una temporalidad</option>';
                  else echo '<option value="'.$this->model->temporalidad.'">'.$est0.'</option>';
                  echo '
                  <option value="A">SEMANAL</option>
                  <option value="U">UNICA</option>
                  <option value="M">MENSUAL</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-5">
                  <label>Responsable</label>
                  <select class="form-control" name="responsable" id="responsable" required="required">';
                  if(empty($this->model->responsable)) echo '<option value="">Seleccione un responsable</option>';
                  else echo '<option value="'.$this->model->responsable.'">'.$this->model->responsable.'</option>';
                  echo '
                  <option value="MAILING">MAILING</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
              <div class="mb-5">
                <label>Estatus</label>';
                if($this->model->estatus == "A"){
                echo '
                <input type="checkbox" name="estatus" checked data-toggle="toggle" data-on="ACTIVO" data-off="INACTIVO" data-onstyle="success" data-offstyle="danger" >';
                }else{
                  echo '
                  <input type="checkbox" name="estatus" data-toggle="toggle" data-on="ACTIVO" data-off="INACTIVO" data-onstyle="success" data-offstyle="danger" >';
                }      
                echo '
              </div>
            </div>
            </div>
            
            <div class="form-actions">
              <button type="button" class="btn btn-warning mr-1">
                <i class="icon-cross2"></i> Cancelar registro
              </button>
              <button type="submit" class="btn btn-primary" id="saveuser">
                <i class="fa fa-check2"></i> Guardar
              </button>
            </div>
          </div>
      </div>
    </form>
    </div>
    </div>
    </div>
    </div>';

    echo '



    ';
    //fechas.value = "'.die(date("Y-m-d", strtotime("22-08-1998"))).'";
        
  }
  function editD(){
    if($this->model->idD){
      $accion = 'updatecampanad';
      $titulo = 'Actualizar Mailing-Campaña';
    }
    else{
      $accion = 'insertcampanad';
      $titulo = 'Registro Nueva Mailing-Campaña';
    }

    echo '<div class="page-title-actions"><div class="d-inline-block dropdown">';
    echo '<a href="?modulo=mailing&accion=showcampana&id='.$_GET['id'].'">
            <button class="mb-2 mr-2 btn btn-warning">
              <i class="fa fa-arrow-left"></i> Atrás
            </button>
          </a>
          <a href="?modulo=mailing&accion=showcampana&id='.$_GET['id'].'">
            <button class="mb-2 mr-2 btn btn-primary">
              <i class="icon-cross2"></i> Cancelar
            </button>
          </a>
          <a >
            <button type="button" data-bs-toggle="modal" data-target="#exampleModal" class="mb-2 mr-2 btn bg-teal">
              <i class="fa fa-eye"></i> Vista Previa
            </button>
          </a></div></div>
          
          
          <!-- Modal -->
          <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
            <form action="query/visualizaremail.php?&idd='.$_GET['idd'].'&id='.$_GET['id'].'" method="POST">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Visualizar Correo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
              <div class="mb-5">
                <label>Ingresa una direccion de correo para mostrar una previsualización: </label>
                <input type="email" style="text-transform:lowercase;" class="form-control" name="correo" required />
              </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Enviar</button>
              </div>
            </form>
            </div>
          </div>
          </div>';

    ?>
    <script>
      tinymce.init({
        selector: '#cuerpo',
        plugins: [
          'a11ychecker','advlist','advcode','advtable','autolink','checklist','export',
          'lists','link','image','charmap','preview','anchor','searchreplace','visualblocks',
          'powerpaste','fullscreen','formatpainter','insertdatetime','media','table','help','wordcount','codesample','code'
        ],
        content_css: [
          //"css/bootstrap.css",
          //"css/bootstrap-extended.css",
          //"css/style.css",
          //"css/pace.css",
          "https://fonts.googleapis.com/css2?family=Josefin+Sans&display=swap",
        ],

        toolbar: 'undo redo | formatpainter casechange blocks | bold italic backcolor | ' +
          'alignleft aligncenter alignright alignjustify | ' +
          'bullist numlist checklist outdent indent | removeformat | a11ycheck code table help | codesample | code'
      });
    </script>
    <?php
    echo '
      <div class="row">
      <form enctype="multipart/form-data" name="campanad" id="campanad" autocomplete="off" class="form" method="post" action="?modulo=mailing&accion='.$accion.'&id='.$_GET['id'].'">';
        if($this->model->idD){
          echo '<input type="hidden" value="'.$this->model->idD.'" name="idD" >';
        }
        if($this->model->fsiguiente){
          echo '<input type="hidden" value="'.$this->model->fsiguiente.'" name="fsig" >';
        }
        ?>
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h4 class="card-title" id="basic-layout-form"><?php echo $titulo;?></h4>
                <!--<a class="heading-elements-toggle"><i class="icon-ellipsis font-medium-3"></i></a>
                <div class="heading-elements">
                  <ul class="list-inline mb-0">
                    <li><a data-action="collapse"><i class="icon-minus4"></i></a></li>
                    <li><a data-action="expand"><i class="icon-expand2"></i></a></li>
                    <li><a data-action="close"><i class="icon-cross2"></i></a></li>
                  </ul>
                </div>-->
              </div>
            <div class="card-body collapse in">
              <div class="card-block">
                  <div class="form-body">
                    <div class="row">
                      <div class="col-md-5">
                        <div class="mb-5">
                          <label for="username">Campaña</label>
                          <input type="text" id="campana" value="<?php echo $this->model->nmb ?>" class="form-control" placeholder="" name="campana" readonly>
                          <div id="DivDestino"></div>
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div class="mb-5">
                          <label for="orden">Orden</label>
                          <input type="text" id="orden" value="<?php echo nl2br($this->model->orden,true) ?>" class="form-control" placeholder="Orden" name="orden" required="required" >
                        </div>
                      </div>
                      <div class="col-md-5">
                        <div class="mb-5">
                          <label for="apellidos">Asunto</label>
                          <input type="text" id="asunto" value="<?php echo $this->model->asunto ?>" class="form-control" placeholder="Asunto del correo" name="asunto" required="required" >
                        </div>
                      </div>
                    </div>
                    <div class="row mt-1">
                      <div class="col-md-12">
                        <div class="mb-5">
                          <label for="cuerpo">Mensaje del correo</label>
                          <textarea id="cuerpo" class="form-control" placeholder="Texto del correo" name="cuerpo" rows="15" required="required" >
                            <?php echo $this->model->cuerpo ?>
                          </textarea>
                          <input type="hidden" id="cuerpoG" class="form-control" name="cuerpoG">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row border mt-2 mb-2 mb-5">
                    <div class="col-lg-3">
                        <h3 class="mt-1">Agregar Documentos</h3>
                        <input type="file" class="form-control p-2 mt-1" name="file1" id="file1" accept="application/pdf, .doc, .docx, .pptx, .xlsx" />
                        <a id="subirdoc" type="button"><button type="button" onclick="subir()" class="btn btn-info m-1" >Subir</button></a>
                    </div>
                    <div class="col-lg-9 p-3" id="aparecerdoc">
                    <?php 
                    $sqldocs = 'SELECT * FROM mailing_documentos WHERE md_campanad = "'.$_GET['idd'].'"';
                    $resultdocs = setq($sqldocs);
                    while($rowdocs = $resultdocs->fetch_array()){
                      echo '
                      <div class="col-lg-4 p-2 border" id="doc'.$rowdocs['md_id'].'"><i class="icon-file"></i><br><p> '.$rowdocs['md_nmb'].'</p><a class="btn btn-red btn-sm" type="button" onclick="borrardoc('.$rowdocs['md_id'].')">
                      <i class="fa fa-trash"></i></a></div>
                      ';
                    }
                    ?>
                    </div>
                  </div>
                  <div class="row border mt-2 mb-2 mb-5">
                    <div class="col-lg-3">
                        <h3>Agregar imagenes</h3>
                        <input type="file" class="form-control p-2 mt-1" name="img1" id="img1" accept="image/jpg, image/jpeg, image/png">
                        <div class="input-group m-2" bis_skin_checked="1">
												<label class="display-inline-block custom-control custom-radio ml-1">
													<input type="radio" name="tipo" value="I" class="custom-control-input" checked>
													<span class="custom-control-indicator"></span>
													<span class="custom-control-description ml-0">Incrustada</span>
												</label>
												<label class="display-inline-block custom-control custom-radio">
													<input type="radio" name="tipo" value="A" class="custom-control-input">
													<span class="custom-control-indicator"></span>
													<span class="custom-control-description ml-0">Adjunta</span>
												</label>
											</div>
                        <a id="subirimg" type="button"><button type="button" onclick="subirimg()" class="btn btn-info m-1" >Subir</button></a>
                    </div>
                    <div class="col-lg-9 p-3" id="aparecerimg">
                    <?php 
                    $sqldocs = 'SELECT * FROM mailing_imagenes WHERE mi_campanad = "'.$_GET['idd'].'"';
                    $resultdocs = setq($sqldocs);
                    while($rowdocs = $resultdocs->fetch_array()){
                      echo '
                      <div class="col-lg-4 p-2 border" id="img'.$rowdocs['mi_id'].'"><br><img src="'.$rowdocs['mi_ruta'].'" class="img-thumbnail"><a class="btn btn-red btn-sm" type="button" onclick="borrarimg('.$rowdocs['mi_id'].')">
                      <i class="fa fa-trash"></i></a></div>
                      ';
                    }
                    ?>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <button class="mb-2 mr-2 btn btn-success btn-block" type="button" onClick="modificarss()">
                      <i class="fa fa-save"></i> Guardar
                    </button>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <?php
    

    echo '</div>';

  }

  function browseemail($id,$idd){
    echo '
    <div class="row page-title-actions">
    <div class="col-md-4">';
    echo '
      <a class="" href="?modulo=mailing&accion=showcampana&id='.$_GET['id'].'">
        <button class="btn btn-warning">
          <i class="fa fa-arrow-left"></i> Atrás
        </button>
      </a>
      <a href="formats/xlsxenviosmail?idd='.$_GET['idd'].'" target="_blank">
        <button class="btn btn-green">
          <i class="icon-file-excel-o"></i> Reporte
        </button>
      </a>
    </div>
    </div>';
    $sqlcam = 'SELECT * FROM mailing_campanas INNER JOIN 	mailing_campanasd ON md_campana = mc_id WHERE mc_id = "'.$_GET['id'].'" AND md_id = "'.$_GET['idd'].'"';
    $resultcam = setq($sqlcam);
    $rowcamp  = $resultcam->fetch_array();
    echo '
    <div class="bg-white p-2">
    <h5 class="">Destinatarios de la campaña:  "'.$rowcamp['mc_nmb'].'" - "'.$rowcamp['md_asunto'].'" </h5>
    
    ';


    echo '
    <div class="table-responsive mt-2" bis_skin_checked="1">
      <table class="table table-bordered table-striped table-hover">
        <thead class="bg-light-blue bg-darken-2 text-white">
          <tr>  
              <th>No.</th>
              <th>Destinatario</th>
              <!--<th>Tipo de cliente</th>-->
              <th>Campaña</th>
              <th>Mail</th>
              <th>Fecha</th>
              <th>Correo</th>
              <th>Tipo de Registro</th>
          </tr>
        </thead>
        <tbody class="">';
        $i=1;
        while($row = $this->model->result->fetch_array()){
        if($row['me_origen'] == "CL"){
          $sql = 'SELECT * FROM clientes WHERE c_id = "'.$row['me_registro'].'"';
          $result = setq($sql);
          $rowcl = $result->fetch_array();

          $nmb = $rowcl['c_nmb'];
          $campana = $row['mc_nmb'];
          $asunto = $row['md_asunto'];
          $fecha =  fecha_formato($row['me_fecha'],false,false);
          $correoreg = $row['me_correoreg'];
          $origen = "CLIENTES";


        }elseif($row['me_origen'] == "RD"){
          $sql = 'SELECT * FROM registros_distribuidores WHERE rd_id = "'.$row['me_registro'].'"';
          $result = setq($sql);
          $rowcl = $result->fetch_array();

          $nmb = $rowcl['rd_nmb'];
          $campana = $row['mc_nmb'];
          $asunto = $row['md_asunto'];
          $fecha =  fecha_formato($row['me_fecha'],false,false);
          $correoreg = $row['me_correoreg'];
          $origen = "REGISTRO DISTRIBUIDOR";
        }  
        echo '
          <tr>
              <th>'.$i++.'</th>
              <td>'.$nmb.'</td>
              <td>'.$campana.'</td>
              <td>'.$asunto.'</td>
              <td>'.$fecha.'</td>
              <td>'.$correoreg.'</td>
              <td>'.$origen.'</td>
          </tr>';
        }
        echo '
        </tbody>
      </table>
    </div>';
//selectemail

    echo '
    </div>
    
    ';
  }

}
?>



<script src="js/jquery.min.js" type="text/javascript"></script>
<script>
  function modificarss(){
    var myContent = tinyMCE.get('cuerpo').getContent();
    console.log(myContent);
    document.getElementById('cuerpoG').value = myContent;
      document.getElementById("campanad").submit();
  }



  function temporalidadf() {
    var temporalidad = document.getElementById("temporalidad").value;
    var fechas = document.getElementById("fechas");
    var fechai = document.getElementById("fechai").value;
    $.ajax({
      url: "query/sumafecha.php",
      type: "POST",
      dataType: "html",
      data: {'temporalidad' : temporalidad,
             'fechai' : fechai},
    })
    .done(function(data){
      fechas.value = data;
    });
  
  }



  function subir(){
    var form = document.getElementById('campanad');
    var formData = new FormData(form);
    var file = document.getElementById('file1').value;
    $.ajax({
      url: "query/docmailing.php",
      type: "POST",
      dataType: "html",
      data: formData,
      processData: false,  // tell jQuery not to process the data
      contentType: false   // tell jQuery not to set contentType
 
    })
    .done(function(data){
      var aparecerdoc = document.getElementById('aparecerdoc');
      if(data != ""){
      aparecerdoc.innerHTML+= data;
      }else{

      }
    });

  } 

  function subirimg(){
    var form = document.getElementById('campanad');
    var formData = new FormData(form);
    var file = document.getElementById('img1').value;
    $.ajax({
      url: "query/imgmailing.php",
      type: "POST",
      dataType: "html",
      data: formData,
      processData: false,  // tell jQuery not to process the data
      contentType: false   // tell jQuery not to set contentType
 
    })
    .done(function(data){
      var aparecerdoc = document.getElementById('aparecerimg');
      if(data != ""){ 
      aparecerdoc.innerHTML+= data;
      }else{

      }
    });

  } 


  function copiar(ev) {
    // Obtener contenido del div oculto
    let contenido = document.getElementById(ev).value;
    console.log(contenido);
    // Crear input
    let input = document.createElement('input');
    // Asignar contenido
    input.value = contenido;

    console.log(input.value);
    // Agregar input a documento
    document.body.appendChild(input);
    // Seleccionar contenido
    input.select();
    // Copiar
    document.execCommand('copy');
    // Eliminar input
    input.remove();
  }

  function borrardoc(id){
    $.ajax({
      url: "query/borrardoc.php",
      type: "POST",
      dataType: "html",
      data: {'id' : id},
 
    })
    .done(function(data){
          var div = document.getElementById('doc'+data);
          div.remove();
    });

  }

  function borrarimg(id){
    $.ajax({
      url: "query/borrardoc.php",
      type: "POST",
      dataType: "html",
      data: {'id' : id,
            'tipo': "img"},
 
    })
    .done(function(data){
          var div = document.getElementById('img'+data);
          div.remove();
    });

  }



</script>
