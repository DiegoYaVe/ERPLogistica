<?php
//ini_set('display_errors', 1);
class licencias{
  var $model;
  var $view;
function licencias(){
  $this->model = new modellicencias($obj);
}

function index(){
  if(!isset($_GET['cliente'])) $_GET['cliente'] = NULL;
  if(!isset($_GET['estatus'])) $_GET['estatus'] = "'A','N','P'";

  $this->model->result($_GET['cliente'],$_GET['estatus']);
  $this->view = new viewlicencias($this->model);
  $this->view->browse($_GET['cliente'],$_GET['estatus']);
}
function edit(){
  if(!isset($_GET['id'])) $_GET['id'] = NULL;

  if(!isset($_REQUEST['cliente'])){
    $_REQUEST['cliente'] = NULL;
    $idcliente = NULL;
  }
  else $idcliente = getIDcliente($_REQUEST['cliente']);

  $this->model->select($_GET['id']);
  $this->view = new viewlicencias($this->model);
  $this->view->edit($idcliente);
}

function show(){
  if(!isset($_GET['id'])) $_GET['id'] = NULL;
  $this->model->select($_GET['id']);
  $this->view = new viewlicencias($this->model);
  $this->view->show();
}
function insert(){

  $this->model->setdata(NULL, $_POST['empresa'], $_POST['cliente'], $_POST['nmbac'],$_POST['ffin'],$_POST['responsable'],$_POST['diasgracia'],$_POST['observaciones']);
  $this->model->insert();
  
  $ncesar = buscajdsuite('CESARP08','empresa_licencia','el_estatus = "A" AND el_asesor','COUNT(*)');
  $nray = buscajdsuite('RAYMUNDO','empresa_licencia','el_estatus = "A" AND el_asesor','COUNT(*)');

  if($ncesar == $nray) $asesor = 'CESARP08';
  elseif($ncesar > $nray) $asesor = 'RAYMUNDO';
  else $asesor = 'CESARP08';

  $sqlemplic = 'INSERT INTO empresa_licencia SET
                el_clientetye = "'.$_POST['cliente'].'",
                el_asesor = "'.$asesor.'",
                el_licencia = "'.$this->model->id.'"';
  setqjdsuite($sqlemplic);

  redirect('?modulo=licencias&accion=show&id='.$this->model->id);
}

function update(){
  $this->model->setdata($_GET['id'], $_POST['empresa'], $_POST['cliente'], $_POST['nmbac'],$_POST['ffin'],$_POST['responsable'],$_POST['diasgracia'],$_POST['observaciones']);
  $this->model->update();

  redirect('?modulo=licencias&accion=show&id='.$this->model->id);
}

function agregarlicencia(){
  
  $this->model->select($_GET['id']);

  $numlic = 0;
  if($_POST['cantidad'] > 0){

    include_once('remisiones.php');
    $remis = new modelremisiones();
    $acronimo = busca($_SESSION['emp'],'empresas','e_id','e_siglas');
    $acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas').'-RM';
    $folioint = getmax('r_folio','remisiones','r_empresa = "'.$_SESSION['emp'].'"');
    if($folioint == "1"){
      $folioint = $acrof.str_pad($folioint,6,"0",STR_PAD_LEFT);
    }
    $almacen = busca($_POST['cliente'],'crm_clientes','c_id','c_almacen');
    $remis->setdata(NULL,$this->model->tablero,$folioint,$this->model->cliente,$almacen,$this->model->descuento,$this->model->nmb,$this->model->maildestino,$_SESSION['uid'],$_POST['fpago'],"0");
    $remis->insert();

    include_once('cxcobrar.php');
    $cuentaxc = new modelcxcobrar();
    $idcxc = $cuentaxc->insertcxcobrar($_SESSION['emp'],$_POST['cliente'],$remis->id,'R',$_POST['importe'],$_POST['fini'],' ');
    $cuentaxc->addproyeccion($idcxc);
    // COMENTADO LICENCIA EMPRESA
    $linean = busca($_POST['articulo'.$_POST['modalidad']],'articulos','a_id','a_lineaneg');
    $nmb = busca($_POST['articulo'.$_POST['modalidad']],'articulos','a_id','a_nmb');
    for($i=0;$i<$_POST['cantidad'];$i++){
      $numlic++;
      $empresalic = busca($_POST['cliente'],'licencias','l_cliente','l_folder');
      //$sqlmod = 'SELECT lm_tipo, lm_periodo, lm_precio,lm_usuarios,lm_ventas,lm_articulos FROM licencias_modalidades WHERE lm_id = "'.$_POST['modalidad'].'"';
      $sqlmod = 'SELECT lm_usuarios, lm_ventas, lm_producto, lm_tipo, lm_periodo, lm_articulos FROM licencias_modalidades WHERE lm_id = "'.$_POST['modalidad'].'"';
      $resultmod = setq($sqlmod);
      //list($tipo,$periodo,$precio,$usu,$vent,$art) = $resultmod->fetch_array();
      list($usuarios,$ventas,$productoid,$tipo,$periodo,$articulos) = $resultmod->fetch_array();
      if($periodo == "M") $ffinlic = date('Y-m-d', strtotime($_POST['fini'].' +1 month'));
      elseif($periodo == "A") $ffinlic = date('Y-m-d', strtotime($_POST['fini'].' +1 year'));
      elseif($periodo == "T") $ffinlic = date('Y-m-d', strtotime($_POST['fini'].' +3 month'));


      if($tipo == "L" || $tipo == "U") $this->model->agregarlicencia($_GET['id'],$_POST['empresa'],$_POST['cliente'],$_POST['modalidad'],$_POST['fini'],$remis->id,$_POST['importe'],$periodo);

      /*
      if($tipo == "L"){

        $sqlcountces = 'SELECT COUNT(*) FROM empresa_licencia WHERE el_estatus = "A" AND el_asesor = "CESARP08"';
        $resultces = setq($sqlcountces);
        list($ncesar) = $resultces->fetch_array();
  
        $sqlcountjor = 'SELECT COUNT(*) FROM empresa_licencia WHERE el_estatus = "A" AND el_asesor = "JORGE1308"';
        $resultjor = setq($sqlcountjor);
        list($njorge) = $resultjor->fetch_array();
  
        if($ncesar == $njorge) $asesor = 'CESARP08';
        elseif($ncesar > $njorge) $asesor = 'JORGE1308';
        else $asesor = 'CESARP08';

      
        
        $sqlinsertdatoslic = 'UPDATE empresa_licencia SET
                              el_modalidad = "'.$_POST['modalidad'].'",
                              el_usuarios = "'.$usuarios.'",
                              el_ventas = "'.$ventas.'",
                              el_articulos = "'.$articulos.'",
                              el_fini = "'.$_POST['fini'].'",
                              el_ffin = "'.$ffinlic.'",
                              el_fpago = "'.$_POST['fpago'].'",
                              el_importe = "'.$_POST['precio'.$_POST['modalidad']].'",
                              el_asesor = "'.$asesor.'"
                              WHERE el_clientetye = "'.$_POST['cliente'].'"';
        setqjdsuite($sqlinsertdatoslic);

      }elseif($tipo == "U"){

        $sqlbuscarjdsuite = 'SELECT el_usuarios FROM empresa_licencia WHERE el_empresa = "'.$empresalic.'"';
        $resultbuscajdsuite = setqjdsuite($sqlbuscarjdsuite);
        list($usuariosexis) = $resultbuscajdsuite->fetch_array();
        $nuevousu = $usuariosexis+$usuarios;
        $updateusers = 'UPDATE empresa_licencia SET
                        el_usuarios = "'.$nuevousu.'"
                        WHERE el_empresa = "'.$empresalic.'"';
        setqjdsuite($updateusers);

      }else*/
      if($tipo == "P"){

        $sql = 'INSERT INTO licencias_adicionales SET
                la_paquete = "'.$productoid.'",
                la_fini = NULL,
                la_empresa = "'.$empresalic.'",
                la_nhoras = "'.$articulos.'",
                la_hrestante = "'.$articulos.'",
                la_estatus = "R",
                la_tipo = "P",
                la_cxcobrar = "'.$idcxc.'",
                la_ugenera = "JDCEO",
                la_falta = "'.date('Y-m-d H:i:s').'",
                la_adjunto = ""';
        setqjdsuite($sql);

      }elseif($tipo == "F"){

        $sql = 'INSERT INTO licencias_adicionales SET
                la_paquete = "'.$productoid.'",
                la_fini = NULL,
                la_empresa = "'.$empresalic.'",
                la_nhoras = "'.$articulos.'",
                la_hrestante = "'.$articulos.'",
                la_estatus = "R",
                la_tipo = "F",
                la_cxcobrar = "'.$idcxc.'",
                la_ugenera = "JDCEO",
                la_falta = "'.date('Y-m-d H:i:s').'",
                la_adjunto = ""';
        setqjdsuite($sql);

      }
      //demás tipos

      //empresa_licencia en jdsuite
      $idrec = getmax('rd_id','remisionesd','rd_remision');
      $remis->setDatad($remis->id, $idrec, 1, $_POST['articulo'.$_POST['modalidad']], $_POST['importe'],0,0,$linean,$nmb);
      $remis->insertdetalle();

      
      $sql3 = 'UPDATE remisiones SET
            r_estatus = "A",
            r_faplica = "'.date('Y-m-d H:i:s').'",
            r_observaciones = "'.formmayus($_POST['descripcion'],true).'",
            r_uaplica = "'.$_SESSION['uid'].'"
            WHERE r_id="'.$remis->id.'"';
      setq($sql3);

      $sql3 = 'UPDATE crm_tableros SET
            ct_estatus = "A"
            WHERE ct_id="' . $this->model->tablero. '"';
      setq($sql3);

    }
    $remis->calculaimporte($remis->id);
  }

  redirect('?modulo=remisiones&accion=show&id='.$remis->id);
}

}

class modellicencias{
  function result($cliente,$estatus){
    $sql = 'SELECT * FROM licencias WHERE l_estatus IN ('.$estatus.')';
    if($cliente) $sql.=' AND l_cliente IN (
              SELECT c_id FROM crm_clientes WHERE c_empresa = "'.$_SESSION['emp'].'" AND
              (c_nmb LIKE "%'.$cliente.'%" OR c_alias LIKE "%'.$cliente.'%")
              )';

    $sql.=' ORDER BY l_id ASC';
    $this->result = setq($sql);
  }

  function select($id){
    $sql = 'SELECT * FROM licencias WHERE l_id="'.$id.'"';
    $result = setq($sql) or die($sql);
    $row = $result->fetch_array();
    $this->id = $row['l_id'];
    $this->cliente = $row['l_cliente'];
    $this->folder = $row['l_folder'];
    $this->nmb = $row['l_nmb'];
    $this->empresa = $row['l_empresa'];
    $this->estatus = $row['l_estatus'];
    $this->fini = $row['l_fini'];
    $this->ualta = $row['l_ualta'];
    $this->observaciones = $row['l_observaciones'];
    $this->fiscales = $row['l_fiscales'];
    $this->tablero= $row['l_tablero'];
    $this->diasgracia = $row['l_diasgracia'];
    $this->ffin = $row['l_ffin'];
    $this->ufin = $row['l_ufin'];
  }

  function setdata($id, $empresa, $cliente, $nmbac,$fini,$responsable,$diasgracia,$observaciones,$estatus="N"){
    mb_internal_encoding("UTF-8");
    $this->id = $id;
    $this->nmb = clearvmayus($nmbac);
    $this->cliente = $cliente;
    $this->empresa = $empresa;
    $this->estatus = $estatus;
    $this->fini = $fini;
    $this->ualta = $responsable;
    $this->diasgracia = $diasgracia;
    $this->observaciones = $observaciones;
    $this->fiscales = $fiscales;
    $this->ffin = $ffin;
    $this->ufin = $ufin;
  }
  function insert(){
    include_once('tableros.php');
    $table = new modeltableros();
    $folio = getmax('ct_folio','crm_tableros');
    if($folio == $acronimo.'1') $folio = $acronimo.str_pad(1,6,"0",STR_PAD_LEFT);
    $fini = explode(" ",date('Y-m-d H:i:s'));
    
    $table->setdata(NULL,$this->nmb,$folio,$this->cliente,$_SESSION['uid'],$fini[0],$fini[1],1,$fini[0],1,"A");
    $table->insert();

    $sql = 'INSERT INTO licencias SET
            l_cliente = "'.$this->cliente.'",
            l_nmb = "'.$this->nmb.'",
            l_folder = "'.$this->folder.'",
            l_empresa= "'.$this->empresa.'",
            l_estatus = "'.$this->estatus.'",
            l_diasgracia = "'.$this->diasgracia.'",
            l_tablero = "'.$table->idt.'",
            l_fini = "'.$this->fini.'",
            l_ualta = "'.$this->ualta.'",
            l_observaciones = "'.$this->observaciones.'",
            l_fiscales = "'.$this->fiscales.'"';
    setq($sql);
    $this->id = getmax('l_id','licencias','l_empresa= "'.$this->empresa.'"',false);
  }
  function update(){
    $sql = 'UPDATE licencias SET
            l_cliente = "'.$this->cliente.'",
            l_nmb = "'.$this->nmb.'",
            l_folder = "'.$this->folder.'",
            l_empresa= "'.$this->empresa.'",
            l_diasgracia = "'.$this->diasgracia.'",
            l_fini = "'.$this->fini.'",
            l_ualta = "'.$this->ualta.'",
            l_observaciones = "'.$this->observaciones.'",
            l_fiscales = "'.$this->fiscales.'",
            l_ffin = "'.$this->ffin.'",
            l_ufin = "'.$this->ufin.'"
            WHERE l_id = "'.$this->id.'" ';
    setq($sql);
    //l_estatus = "'.$this->estatus.'",
  }
  function agregarlicencia($id,$empresa,$cliente,$modalidad,$fini,$remision,$preciou,$perido){
    $numl = busca($id,'licencias_renovaciones','lr_licencia','COUNT(*)');
    $diasg = busca($id,'licencias','l_id','l_diasgracia');

    if($perido == "M") $ffin = date('Y-m-d', strtotime($fini.' +1 month'));
    elseif($perido == "A") $ffin = date('Y-m-d', strtotime($fini.' +1 year'));
    elseif($perido == "T") $ffin = date('Y-m-d', strtotime($fini.' +3 month'));
    //$ffin = date('Y-m-d',strtotime($fini.'+1 month'));
    $flim = date('Y-m-d',strtotime($ffin.'+'.$diasg.' days'));

    $clave = str_pad($id,4,"0",STR_PAD_LEFT).str_pad(date('Y'),4,"0",STR_PAD_LEFT).str_pad(date('m'),4,"0",STR_PAD_LEFT).str_pad($numl,3,"0",STR_PAD_LEFT); //4num empresa-4 año-2mes-3numlic

    $sql = 'INSERT INTO licencias_renovaciones SET
            lr_licencia = "'.$id.'",
            lr_clave = "'.$clave.'",
            lr_importe = "'.$preciou.'",
            lr_fecha = "'.date('Y-m-d').'",
            lr_fini = "'.$fini.'",
            lr_ffin = "'.$ffin.'",
            lr_flimite = "'.$flim.'",
            lr_modalidad = "'.$modalidad.'",
            lr_estatus = "P",
            lr_remision = "'.$remision.'"';
    setq($sql);
  }
}

class viewlicencias{
    var $model;

  function viewlicencias($model) {
    $this->model = $model;
    $this->model->aw = array("1"=>"JD SHOP","2"=>"JD SUITE");
  }

  function browse($cliente){
   //Sección: E1 Encabezado - Botones de acción
    echo '<div class="row page-title-actions">';
    //Sección: E2 Encabezado - Filtros
  ?>

    <div class="mb-5">
      <a href="?modulo=licencias&accion=edit">
        <button type="button" class="btn btn-primary">
          <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
        </button>
      </a>
      <button type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
        <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
      </button>
    </div>

    <div id="filter-panel" class="col-md-12 collapse filter-panel mb-2">
      <div class="panel panel-default">
        <div class="panel-body">
          <form class="form-inline" role="form" method="post" action="?modulo=licencias&accion=index">
            <div class="mb-5 mr-1">
              <label for="tipom">Cliente</label>
              <input type="text" class="form-control" name="cliente" placeholder="Nombre del cliente que buscas" onfocus="this.select();" value="<?php echo $proveedor ?>" />
            </div>
            <div class="mb-5 mr-1">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus">
                <option value="" <?php echo $selt; ?> >Todos</option>
                <option value="A" <?php echo $sela; ?> >Activos</option>
                <option value="P" <?php echo $selp; ?> >En pausa</option>
                <option value="C" <?php echo $selc; ?> >Cancelados</option>
              </select>
            </div><
            <div class="mb-5">
              <label for="">Aciones</label> <br>
              <button type="submit" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i>
              </button>
              <a href="?modulo=licencias&accion=index"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i>
              </button></a>
            </div>
          </form>
        </div>
      </div>
    </div>
    </div>
    <div class="main-card mb-3 card">
    <div class="card-body row">

  <?php

  echo '
    <div class="table-responsive">
      <table class="table table-hover table-striped bg-white">
        <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Inicio de licencia</th>
            <th>Observaciones</th>
            <th>Ultima renovación</th>
            <th>Plan actual</th>
            <th>Estatus</th>
            <th></th>
          </tr>
        </thead>
        <tbody>';
          while ($row = $this->model->result->fetch_array()){
            $lastr = busca($row['l_id'],'licencias_renovaciones','lr_estatus = "A" AND lr_licencia','MAX(lr_fini)');
            if(!$lastr) $ultiimaren = "-";
            else $ultiimaren = fecha_formato($lastr,false,true);
            
            if($row['l_estatus'] == "A"){ $estatus = "ACTIVA"; $colest = "bg-success"; }
            elseif($row['l_estatus'] == "P"){ $estatus = "EN PAUSA"; $colest = "bg-warning"; }
            else{ $estatus = "INACTIVA"; $colest = "bg-danger"; }

            echo '<tr>
                    <th>'.$row['l_id'].'</th>
                    <th>'.busca($row['l_cliente'],'crm_clientes','c_id','c_alias').'</th>
                    <th>'.fecha_formato($row['l_fini'],false,true).'</th>
                    <th>'.$row['l_observaciones'].'</th>
                    <th>'.$ultiimaren.'</th>
                    <th>'.busca($row['l_id'],'licencias_renovaciones','lr_estatus IN ("A","X") AND lr_licencia','COUNT(*)').'</th>
                    <th class="'.$colest.'">'.$estatus.'</th>
                    <th>
                      <a href="?modulo=licencias&accion=show&id='.$row['l_id'].'">
                        <button class="btn btn-primary"><i class="icon-chevron-right2"></i></button>
                      </a>
                    </th>
                  </tr>';
          }

  echo '</tbody>
      </table>
    </div>';

  echo '</div>';

  }

  function edit($cliente){
  ?>
    <script>
    $(document).ready(function() {
        $('#cliente').on('keyup', function() {
          var key = $(this).val();
//          var empresa = $('#empresa').val();
          var dataString = 'cliente='+key;
        $.ajax({
          type: "POST",
          url: "query/suggestclientes.php",
          data: dataString,
          success: function(data) {
            //Escribimos las sugerencias que nos manda la consulta
            $('#suggestions-block').fadeIn(1000).html(data);
            //Al hacer click en alguna de las sugerencias
            $('.suggest-element').on('click', function(){
              //Obtenemos la id unica de la sugerencia pulsada
              var id = $(this).attr('id');
              //Editamos el valor del input con data de la sugerencia pulsada
              $('#cliente').val($('#'+id).attr('data'));
              //Hacemos desaparecer el resto de sugerencias
              $('#suggestions-block').fadeOut(1000);
              setname();
//              alert('Has seleccionado el '+id+' '+$('#'+id).attr('data'));
              return false;
            });
          }
        });
      });
    });
    </script>
  <?php
    echo '<div class="page-title-actions">
            <div class="row">
              <form action="?modulo=licencias&accion=edit" method="post" autocomplete="off" name="actualizaclie">
                <div class="col-sm-12 col-md-1">';
    echo '        <a href="?modulo=licencias&accion=index">
                    <button type="button" class="mb-2 mr-2 btn btn-warning">
                      <i class="fa fa-arrow-left"></i>Regresar
                    </button>
                  </a>
                </div>';
    echo '      <div class="col-12 col-sm-12 col-md-6">';
    echo '        <div class="mb-5">
                    <input type="text" name="cliente" id="cliente" placeholder="Nombre o alias del cliente" class="search_query form-control" value="'.$_REQUEST['cliente'].'" required autofocus >
                    <label for="nmb" class="mt-1">Nombre o alias del cliente</label>
                  </div>
                  <div id="suggestions-block"></div>';
      echo '    </div>
                <div class="col-12 col-sm-12 col-md-4 text-sm-center">
                  <button type="submit" class="btn btn-primary"><i class="icon-user-check"></i> Actualizar</button>
                  <a accesskey="B" href="popup/buscarclienterem?from=licencias" onclick="window.open(this.href,\'window\',\'width=980, height=650\');return false">
                    <button type="button" class="btn btn-info" ><i class="fa fa-search4"></i> Buscar</button>
                  </a>
                  <a href="?modulo=clientes&accion=edit" target="_BLANK">
                    <button type="button" class="btn bg-indigo bg-darken-1 text-white" ><i class="icon-user-plus2"></i> Nuevo</button>
                  </a>
                </div>
              </form>
            </div>';
    echo '</div>';

    if($cliente){
      include('modulos/clientes.php');
      $client = new modelclientes();
      $client->select($cliente);

      echo '<div class="row">
              <div class="col-7 col-md-7 card p-1 border-blue-grey">
                Crear Remisión para '.$client->nmb.' '.$client->apellidos.'
              </div>
              <div class="col-2 col-md-2">
                <a target="_BLANK" href="?modulo=clientes&accion=edit&id='.$client->id.'">
                  <button class="btn btn-secondary"><i class="icon-ios-person"></i> Ver Cliente</button>
                </a>
              </div>
              <div class="col-2 col-md-2">
                <a data-fancybox data-type="ajax" data-src="popup/setlicencias.php?cliente='.$client->id.'&rand='.rand().'" href="javascript:;" >
                  <button class="btn bg-grey bg-darken-2 text-white"><i class="icon-cash"></i> Crear contrato de JD CEO</button>
                </a>
              </div>';

        echo '<div class="col-12 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <div class="card-block">
                      <div class="media">
                        <div class="media-body text-xs-left">
                          <h3 class="teal bg-darken-3">Tableros abiertos</h3>
                        </div>
                        <div class="p-2 text-xs-center bg-teal bg-darken-3 media-right media-middle">
                            <i class="icon-calculator4 font-medium-2 white"></i>
                        </div>
                      </div>
                    </div>
                  </div>';
        $sql = 'SELECT * FROM crm_tableros WHERE ct_cliente = "'.$client->id.'" AND ct_estatus  IN ("N","P","G","L")
                ORDER BY ct_fcierre DESC,ct_folio DESC';
        $result = setq($sql);
        if($result->num_rows > 0){
          echo '<div class="table-responsive">
                <table class="table table-hover table-striped">
                <thead class="thead-inverse">
                  <tr>
                    <th>Tablero</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Ver</th>
                  </tr>
                <thead>';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            echo '<tr>
                    <th>'.$row['ct_folio'].'</th>
                    <th>'.$row['ct_nmb'].'</th>
                    <th>'.date('d-m-Y',strtotime($row['ct_fcierre'])).'</th>
                    <th>
                      <a target="_BLANK" href="?modulo=tableros&accion=show&id='.$row['ct_id'].'">
                        <button class="btn btn-info"><i class="icon-mail-forward"></i></button>
                      </a>
                    </th>
                  </tr>';
          }
          echo '</table></div></div>';
        }
        else
          echo '<div class="alert alert-danger text-xs-center text-md-center"><h3>Sin Tableros abiertos</h3></div>
          </div>';

        echo '</div>';

        echo '<div class="col-12 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <div class="card-block">
                      <div class="media">
                        <div class="media-body text-xs-left">
                          <h3 class="indigo bg-darken-4">Últimas 5 ventas</h3>
                        </div>
                        <div class="p-2 text-xs-center bg-indigo bg-darken-4 media-right media-middle">
                            <i class="icon-coin-dollar font-medium-2 white"></i>
                        </div>
                      </div>
                    </div>
                  </div>';
        $sql = 'SELECT * FROM remisiones WHERE r_cliente = "'.$client->id.'"
                ORDER BY r_falta DESC,r_id DESC';
        $result = setq($sql);
        if($result->num_rows > 0){
          echo '<div class="table-responsive">
                <table class="table table-hover table-striped">
                <thead class="thead-inverse">
                  <tr>
                    <th>Folio</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Importe</th>
                    <th>Ver</th>
                  </tr>
                <thead>';
          $result = setq($sql);
          while($row = $result->fetch_array()){
            echo '<tr>
                    <th>'.$row['r_folio'].'</th>
                    <th>'.$row['r_nmb'].'</th>
                    <th>'.date('d-m-Y',strtotime($row['r_falta'])).'</th>
                    <th>'.$row['r_total'].'</th>
                    <th>
                      <a target="_BLANK" href="?modulo=remisiones&accion=show&id='.$row['r_id'].'">
                        <button class="btn btn-info"><i class="icon-mail-forward"></i></button>
                      </a>
                    </th>
                  </tr>';
          }
          echo '</table></div></div>';
        }
        else
          echo '<div class="alert alert-danger text-xs-center text-md-center"><h3>Sin Ventas previas</h3></div>';

        echo '  </div>
              </div>
            </div>';
    }
    else{
      if(isset($_REQUEST['cliente'])){
        echo '<div class="row">
                <div class="col-12 col-md-12 alert alert-danger">
                  No existe un cliente que coincida con <span class="font-medium-2 text-bold-800 text-uppercase">'.$_POST['cliente'].'<span>
                </div>
              </div>  ';
      }
    }
  }


function show(){
  echo '<div class="row page-title-actions">';

  if(isset($_GET['message']) && $_GET['message'] != "OK"){
    echo '<div class="col-12 alert alert-danger">Error de existencias '.busca($_GET['message'],'articulos','a_id','a_nmb').'</div>';
  }

  echo '<div class="col-12 col-md-6">';
  echo '  <a href="?modulo=licencias&accion=index" accesskey="">
            <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atras </button>
          </a>';

    echo '<a data-fancybox data-type="ajax" data-src="popup/setlicencias.php?idlic='.$this->model->id.'&rand='.rand(1,100).'" href="javascript:;">
            <button type="button" class="btn btn-primary " data-toggle="tooltip" data-placement="top" title="Modificar información de la remisión"><i class="fa fa-pen"></i> Modificar</button>
          </a>';


  $remisionlc = busca($this->model->id,'licencias_renovaciones','lr_licencia','lr_remision');
  $cxclic = busca($remisionlc,'cxcobrar','cx_referencia','cx_estatus');

  if($cxclic == "F" && $this->model->estatus == "N"){

    echo '           
    <a data-fancybox data-type="ajax" data-src="popup/setempresa.php?idlic='.$this->model->id.'&rand='.rand(1,100).'" href="javascript:;">
      <button type="button" class="btn btn-purple " data-toggle="tooltip" data-placement="top" title="Agregar Empresa"><i class="fa fa-plus-circle"></i> Agregar Empresa</button>
    </a>';

  }

    echo '
    <a data-fancybox data-type="ajax" data-src="popup/setlicenciasuso.php?idlic='.$this->model->id.'&rand='.rand(1,100).'" href="javascript:;">
      <button type="button" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Añadir licencia para la empresa actual"><i class="fa fa-plus-circle"></i> Añadir licencia</button>
    </a>';

    echo '
    <div class="btn-group">
      <button class="btn btn-green dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Licencias Adicionales
      <span class="tag tag-pill tag-default tag-danger tag-default tag-up">'.$totalp.'</span>
      </button>
      <div class="dropdown-menu" style="font-size: small; min-width: 290px;"> <!-- style="max-height: 400px; overflow: scroll;" -->
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0" style="font-size: x-small;" >
            <thead class="thead bg-green bg-darken-3 text-white pt-1">
              <tr>
                <th>Licencia</th>
                <th>Estatus</th>
              </tr>
            </thead>
            <tbody>'; 
            $sql = 'SELECT * FROM licencias_adicionales INNER JOIN empresa_licencia ON el_empresa = la_empresa WHERE la_estatus = "A" AND el_licencia = "'.$this->model->id.'"';
            $result = setqjdsuite($sql);
            while($row = $result->fetch_array()){
              if($row['la_estatus'] == "A") $estatustabla = "CORRIENDO";
              else $estatustabla = "";
              echo '<tr>
                      <td>'.busca($row['la_paquete'],'licencias_modalidades','lm_producto','lm_nmb').'</td>
                      <td class="bg-success text-white" >'.$estatustabla.'</td>
                    </tr>';
            }
            echo '
            </tbody>
          </table
        </div>
      </div>
    </div>
    
    
    ';

  

  echo '</div></div>';

  echo '<div class="table-responsive">
          <table class="table table-stripped table-hover">
          <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
            <tr><th colspan="7">Licencias Contratadas</th></tr>
            <tr>
              <th>Clave licencia</th>
              <th>Modalidad</th>
              <th>Fecha renovación</th>
              <th>Fecha vencimiento</th>
              <th>Fecha Limite de pago</th>
              <th>Importe</th>
              <th>Estatus</th>
            </tr>
          </thead>';
  $sql = 'SELECT * FROM licencias_renovaciones WHERE lr_licencia = "'.$this->model->id.'"
          ORDER BY DATE(lr_ffin) DESC';
  $result = setq($sql);
  $total = 0;
  while($row = $result->fetch_array()){
    if($row['lr_estatus'] == "P"){ $estatus = "PAGO PENDIENTE"; $colest = "bg-orange"; } //if($row['lr_estatus'] == "P" && $cxclic != "F")
    elseif($row['lr_estatus'] == "A"){ $estatus = "ACTIVA"; $colest = "bg-success"; }
    elseif($row['lr_estatus'] == "C"){ $estatus = "CANCELADA"; $colest = "bg-info"; }
    elseif($row['lr_estatus'] == "X"){ $estatus = "VENCIDA"; $colest = "bg-danger"; }
    echo '<tr>
            <td>'.$row['lr_clave'].'</td>
            <td>'.busca($row['lr_modalidad'],'licencias_modalidades','lm_id','lm_nmb').'</td>
            <td>'.$row['lr_fini'].'</td>
            <td>'.$row['lr_ffin'].'</td>
            <td>'.$row['lr_flimite'].'</td>
            <td class="number-align">$'.number_format($row['lr_importe'],2).'</td>
            <td class="text-white '.$colest.'">'.$estatus.'</td>
          </tr>'; 
          $total+=$row['lr_importe'];
  }
    echo '<tr>
            <td colspan="4"></td>
            <td>Total</td>
            <td class="number-align">$'.number_format($total,2).'</td>
          </tr>';

  echo '</table></div>';

  echo '
  <div class="table-responsive">
    <table class="table table-striped table-hover mb-0">
      <thead class="thead bg-green bg-darken-3 text-white pt-1">
        <tr>
          <th>Licencia</th>
          <th>Estatus</th>
        </tr>
      </thead>
      <tbody>'; 
      $sql = 'SELECT * FROM licencias_adicionales INNER JOIN empresa_licencia ON el_empresa = la_empresa WHERE la_estatus = "A" AND el_licencia = "'.$this->model->id.'" AND la_tipo IN ("P","F")';
      $result = setqjdsuite($sql);
      while($row = $result->fetch_array()){
        if($row['la_estatus'] == "A") $estatustabla = "CORRIENDO";
        else $estatustabla = "";
        echo '<tr>
                <td>'.busca($row['la_paquete'],'licencias_modalidades','lm_producto','lm_nmb').'</td>
                <td class="bg-success text-white" >'.$estatustabla.'</td>
              </tr>';
              $total+=$row['lr_importe'];
      }
      echo '
      </tbody>
    </table
  </div>';
}
}
?>