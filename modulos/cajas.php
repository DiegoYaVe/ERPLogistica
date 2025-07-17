<?php
/* if($_SESSION['uid'] == "ADMIN")
  ini_set('display_errors', 1); */
class cajas{
  function __construct(){
      $this->model = new modelcajas($obj);
  }
  function index(){
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d', strtotime('-10 days'));
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d', strtotime('last day of this month'));
      if(!isset($_REQUEST['estatus']) || $_REQUEST['estatus'] == "") $_REQUEST['estatus'] = NULL;

      $this->model->result($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['estatus']);
      $this->view = new viewcajas($this->model);
      $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['estatus']);
  }

  function show(){
    if(!isset($_REQUEST['remision'])) $_REQUEST['remision'] = NULL;
    if(!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;
    if(!isset($_REQUEST['vendedor'])) $_REQUEST['vendedor'] = NULL;
    if(!isset($_REQUEST['estatus']) || $_REQUEST['estatus'] == "") $_REQUEST['estatus'] = NULL;
    $this->model->select($_GET['id']);
    $this->view = new viewcajas($this->model);
    $this->view->show($_REQUEST['remision'], $_REQUEST['cliente'], $_REQUEST['vendedor'], $_REQUEST['estatus']);
  }

  function abrircaja(){
    $check = busca('A', 'cajas', 'c_estatus', 'COUNT(*)');
    if($check > 0){
      echo '<script>
      alert("Ya existe una caja abierta")
      </script>';
    } else{
      $this->model->setdata(NULL, date('Y-m-d'), date('H:i:s'), $_SESSION['uid'], $_POST['efectivoap'], "A");
      $this->model->insert();
      $idcaja = getmax('c_id', 'cajas', false, false);
      redirect('?modulo=cajas&accion=show&id='.$idcaja);
    }
  }
  function ingreso(){
    //foreachdie();
    $fecha = str_replace("T"," ",$_POST['fecha']);
    $sqlins = 'INSERT INTO cajas_movimientos SET cm_caja = "'.$_GET['id'].'",
                                                cm_monto = "'.$_POST['monto'].'",
                                                cm_tipo = "I",
                                                cm_descripcion = "'.$_POST['observaciones'].'",
                                                cm_ugen = "'.$_SESSION['uid'].'",
                                                cm_fgen = "'.$fecha.'"';
    setq($sqlins);

    $mov = getmax('cm_id', 'cajas_movimientos', false, false);

    ?>
      <script>
        function imprimirticket(idcd){
          console.log('entro a la funcion');
          $.ajax({
            type: "POST",
            url: "query/ticketmovcaja.php",
            data: {"mov": idcd},
            success: function(data){
              console.log(data);
              var datax = JSON.parse(data);
              var ventimp = window.open('about:blank', 'ImprimirTicket');
              ventimp.document.open();
              ventimp.document.write('<html><head><title>' + document.title + '</title>');
              //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
              ventimp.document.write('</head><body>');
              ventimp.document.write('<div style="width:402px;height:200px">');
              ventimp.document.write('<center><img src="img/UNICO-LOGO-OFICIAL.png" height="200px" width="200px" alt="img/UNICO-LOGO-OFICIAL.png"></center>');
              ventimp.document.write('</div>');
              ventimp.document.write('<table width="402px"><tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Fecha:</td><td align="left" colspan="2" style="font-size:20px">'+datax['fecha']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Genera:</td><td align="left" colspan="2" style="font-size:20px">'+datax['ugen']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Monto:</td><td align="left" colspan="2" style="font-size:20px">$'+datax['monto']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Tipo:</td><td align="left" colspan="2" style="font-size:20px">'+datax['tipo']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Motivo:</td><td align="left" colspan="2" style="font-size:20px">'+datax['obs']+'</td></tr>');
              ventimp.document.write('<tr><td colspan="3" height="15px"></td></tr>');
              ventimp.document.write('</table></body></html>');
              ventimp.document.close();
              ventimp.focus();

              ventimp.addEventListener('load', function () {
                //ventimp.document.open(); 
                  window.close(); 
                  ventimp.print();
                  // Realiza las operaciones en el documento de la ventana emergente aquí
                  document.location.href = "?modulo=cajas&accion=show&id=<?php echo $_GET['id']; ?>";
              });
            }
          });
        }

        imprimirticket(<?php echo $mov; ?>);
        
      </script>
    <?php

    //redirect("?modulo=cajas&accion=show&id=".$_GET['id']);
  }

  function gasto(){
    $fecha = str_replace("T"," ",$_POST['fecha']);
    $sqlins = 'INSERT INTO cajas_movimientos SET cm_caja = "'.$_GET['id'].'",
                                                cm_monto = "'.$_POST['monto'].'",
                                                cm_tipo = "G",
                                                cm_descripcion = "'.$_POST['observaciones'].'",
                                                cm_ugen = "'.$_SESSION['uid'].'",
                                                cm_fgen = "'.$fecha.'"';
    setq($sqlins);
    $mov = getmax('cm_id', 'cajas_movimientos', false, false);

    ?>
      <script>
        function imprimirticket(idcd){
          console.log('entro a la funcion');
          $.ajax({
            type: "POST",
            url: "query/ticketmovcaja.php",
            data: {"mov": idcd},
            success: function(data){
              console.log(data);
              var datax = JSON.parse(data);
              var ventimp = window.open('about:blank', 'ImprimirTicket');
              ventimp.document.open();
              ventimp.document.write('<html><head><title>' + document.title + '</title>');
              //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
              ventimp.document.write('</head><body>');
              ventimp.document.write('<div style="width:402px;height:200px">');
              ventimp.document.write('<center><img src="img/UNICO-LOGO-OFICIAL.png" height="200px" width="200px" alt="img/UNICO-LOGO-OFICIAL.png"></center>');
              ventimp.document.write('</div>');
              ventimp.document.write('<table width="402px"><tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Fecha:</td><td align="left" colspan="2" style="font-size:20px">'+datax['fecha']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Genera:</td><td align="left" colspan="2" style="font-size:20px">'+datax['ugen']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Monto:</td><td align="left" colspan="2" style="font-size:20px">$'+datax['monto']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Tipo:</td><td align="left" colspan="2" style="font-size:20px">'+datax['tipo']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Motivo:</td><td align="left" colspan="2" style="font-size:20px">'+datax['obs']+'</td></tr>');
              ventimp.document.write('<tr><td colspan="3" height="15px"></td></tr>');
              ventimp.document.write('</table></body></html>');
              ventimp.document.close();
              ventimp.focus();

              ventimp.addEventListener('load', function () {
                //ventimp.document.open(); 
                  window.close(); 
                  ventimp.print();
                  // Realiza las operaciones en el documento de la ventana emergente aquí
                  document.location.href = "?modulo=cajas&accion=show&id=<?php echo $_GET['id']; ?>";
              });
            }
          });
        }

        imprimirticket(<?php echo $mov; ?>);
        
      </script>
    <?php
  }

  function confirmarabono(){
    $remision = busca($_GET['idd'], 'cajasd', 'cd_id','cd_remision');
    $sql = 'SELECT cx_id, cx_abonado FROM cxcobrar WHERE cx_referencia = "'.$remision.'"';
    $result = setq($sql);
    list($idcxc, $abonado) = $result -> fetch_array();
    $valida= busca($idcxc, 'ingresos INNER JOIN ingreso_cxcobrar ON i_id = ic_ingreso INNER JOIN cxcobrar ON cx_id = ic_cxcobrar', 'i_estatus = "F" AND cx_id', 'COUNT(*)');
    $valida2 = busca($remision, 'cajasd', 'cd_estatus = "A" AND cd_remision', 'COUNT(*)');
    if(intval($valida) == 0 && intval($valida2) == 0){
      include('remisiones.php');
      $rem = new modelremisiones();
      $rem -> aplicar($remision);
      $uaplica = busca($_GET['idd'], 'cajasd', 'cd_id', 'cd_ugen');
      $faplica = busca($_GET['idd'], 'cajasd', 'cd_id', 'cd_fgen');
      $sqlupd = 'UPDATE remisiones SET r_estatus = "A", r_uaplica="'.$uaplica.'", r_faplica="'.$faplica.'" WHERE r_id = "'.$remision.'"';
      setq($sqlupd);
    }

    $sqlupd = 'UPDATE cajasd SET cd_estatus = "A", cd_fconfirma = "'.date('Y-m-d H:i:s').'", cd_uconfirma = "'.$_SESSION['uid'].'" WHERE cd_id = "'.$_GET['idd'].'"';
    setq($sqlupd);

    $fecha=date('Y-m-d H:i:s');
    $importecxc = busca($_GET['idd'], 'cajasd', 'cd_id', 'cd_monto');
    $total=$abonado+$importecxc;
    $totalcxc = busca($idcxc,'cxcobrar','cx_id','cx_importe');
    if($total >= $totalcxc){
      $sqli = 'UPDATE cxcobrar SET cx_estatus = "F", cx_abonado="'.$total.'", cx_ffin ="'.$fecha.'"
              WHERE cx_id="'.$idcxc.'"';
      setq($sqli) or die($sqli);
      $tipoap = busca($idcxc,'cxcobrar','cx_id','cx_tipo');
      /* $sqlcx = 'UPDATE cortesd SET cd_tipom = "'.$tipoap.'" WHERE cd_tipo = "C" AND cd_idref = "'.$ingreso.'"';
      setq($sqlcx); */
 
      $remision = busca($idcxc,'cxcobrar','cx_id','cx_referencia');
      $envio = busca($remision, 'crm_cotizaciones', 'cc_remision', 'cc_envio');

      //if($envio == "P"){
        $sql = 'UPDATE remisiones SET r_estatus = "F" WHERE r_id = "'.$remision.'"';
        setq($sql);

        $sqlarticulos = 'UPDATE remisionesc SET rc_estatus = "P" WHERE rc_estatus = "N" AND rc_tipoenvio = "C" AND rc_remision = "'.$remision.'"';
        setq($sqlarticulos);

        $sqlarticulos2 = 'UPDATE remisionesc SET rc_estatus = "A" WHERE rc_estatus = "N" AND rc_tipoenvio IN ("D", "O") AND rc_remision = "'.$remision.'"';
        setq($sqlarticulos2);
      //}
      //$this->completaventa($idcxc);
    }else{
      $sqli = 'UPDATE cxcobrar SET cx_estatus = "A", cx_abonado="'.$total.'"
                WHERE cx_id="'.$idcxc.'"';
      setq($sqli) or die($sqli);
    }

    redirect("?modulo=cajas&accion=show&id=".$_GET['id']);
  }

  function cancelarabono(){
    $idd = $_GET['idd'];

    $sqlupd = 'UPDATE cajasd SET cd_estatus = "C" WHERE cd_id = "'.$idd.'"';
    setq($sqlupd);

    redirect("?modulo=cajas&accion=show&id=".$_GET['id']);
  }

  function entregarefectivo(){
    include_once('cuentas.php');
    $cuen = new modelcuentas(); 
    $cuenta = $_POST['cuenta'];
    $corteo = busca('A', 'cortes', 'c_cuenta = "1" AND c_estatus', 'c_id');
    $corted = busca($_POST['cuenta'], 'cortes', 'c_estatus = "A" AND c_cuenta', 'c_id');
    $cuen->inserttraspaso("ENTREGA DE EFECTIVO ".date('d-m-Y'),$_POST['monto'],$corteo,"1",$corted,$_POST['cuenta']);

    redirect("?modulo=cajas&accion=index");
  }

  function movimientos(){
    $this->model->select($_GET['id']);
    $this->view = new viewcajas($this->model);
    $this->view->movimientos();

  } 
}

class modelcajas{
  function result($fini,$ffin,$estatus){ //filtro
    $bloque = 50;
    $sql = 'SELECT * FROM cajas WHERE c_fini >= "'.$fini.'" AND c_fini <= "'.$ffin.'"';
    if($estatus) $sql.= ' AND c_estatus = "'.$estatus.'"';
    $sql.=' ORDER BY c_fini DESC, c_hini DESC';
    $this->result = setq($sql);
    $this->resultt = setq($sql);
  }
  function select($id){
      $sql = 'SELECT * FROM cajas WHERE c_id="'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['c_id'];
      $this->fini = $row['c_fini'];
      $this->hini = $row['c_hini'];
      $this->ffin = $row['c_ffin'];
      $this->hfin = $row['c_hfin'];
      $this->estatus = $row['c_estatus'];
      $this->cajero = $row['c_cajero'];
  }

  function setdata($id,$fini,$hini, $cajero, $fondo, $estatus){
      mb_internal_encoding("UTF-8");
      $simbol = array('"',"'","#","$","%", "&","/","(",")","=","?","¡","*","+","~","^","[","°","|","{","}","[","]");
      $cambio = "";
      $this->id = $id;
      $this->fini = $fini;
      $this->hini = $hini;
      $this->cajero = $cajero;
      $this->fondo = $fondo;
      $this->estatus = $estatus;
  }
  function insert(){
      $sql = 'INSERT INTO cajas SET
              c_fini = "'.$this->fini.'",
              c_hini = "'.$this->hini.'",
              c_cajero = "'.$this->cajero.'",
              c_fondo = "'.$this->fondo.'",
              c_estatus = "'.$this->estatus.'"';
      setq($sql);
  }
  
}

class viewcajas {
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
  function browse($fini,$ffin,$estatus) {
    //Sección: E1 Encabezado - Botones de acción
      if(!isset($estatus)) $estt = "selected";
      elseif($estatus == "C") $estc = "selected";
      elseif($estatus == "A") $esta = "selected";

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
      <?php
        $check = busca("A", 'cajas', 'c_estatus', 'COUNT(*)');
        if($check > 0){
          $nuevo = '<button class="btn btn-primary btn-sm" onclick="cajaabierta();">
          <i class="fa fa-plus"></i>
          Abrir caja</button>';
        } else {
          $nuevo = '<a id="nuevo" class="btn btn-sm btn-primary" data-fancybox data-type="ajax" data-src="popup/nuevacaja.php?rand='.rand().'" href="javascript:;">
          <i class="fa fa-plus"></i> Abrir caja
          </a>';
        }

        $otros = '<a id="entrega" class="btn btn-sm text-white" style="background:teal;" data-fancybox data-type="ajax" data-src="popup/entregarefe.php?rand='.rand().'" href="javascript:;">
          <i class="fas fa-exchange-alt" style="color: #ffffff"></i> Entregar efectivo
        </a>'; 

        $otros .= '<button class="btn btn-secondary btn-sm ms-2" onclick="abrircajon();">
        <i class="fas fa-cash-register"></i>
        Abrir cajón</button>';

        ?>
        <script>
          function abrircajon(){
            console.log('entro a la funcion');
            var ventimp = window.open('about:blank', 'ImprimirTicket');
                ventimp.document.open();
                ventimp.document.write('<html>');
                ventimp.document.write('<body> .');
                ventimp.document.write('</body></html>');
                ventimp.document.close();
                ventimp.focus();
                var checkVentimp = setInterval(function() {
                    if (ventimp.document.readyState === 'complete') {
                        clearInterval(checkVentimp); // Detenemos el temporizador
                        console.log('Ventana emergente cargada');
                        ventimp.print();
                        ventimp.close();
                    }
                }, 100);
            }
        </script>
        
        <?php
        
      $filtrar = '
                <form class="form-inline" role="form" method="post" action="?modulo=cajas&accion=index" id="filtro">
                
                  <div class="mb-5">
                    <label for="">Desde:</label>
                    <input type="date" class="form-control" id="pref-search" name="fini" value="'.$fini.'" placeholder="Buscar por fecha inicial">
                  </div>
                  <div class="mb-5">
                    <label for="">Hasta:</label>
                    <input type="date" class="form-control" id="pref-search" name="ffin" value="'.$ffin.'" placeholder="Buscar por fecha final">
                  </div>
                  <div class="mb-5">
                    <label for="">Buscar por estatus:</label>
                    <select class="form-control" name="estatus">
                    <option value="A" '.$esta.'>Abiertas</option>
                    <option value="C" '.$estc.'>Cerradas</option>
                    <option value="" '.$estt.'>Todas</option>
                    </select>
                  </div>
                  <div class="mb-5">
                    <label for="">Acciones:</label><br>
                  <button type="submit" class="btn btn-info">
                    <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                  </button>
                  <a href="?modulo=categorias&accion=index"><button type="button" class="btn btn-warning">
                    <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                  </button></a>
                  </div>
                </form>';

          toolbar($_GET['modulo'],"", $filtrar, $nuevo, $otros);

         
    echo '
        <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table class="table" id="myTable">
                  <thead class="bg-light-blue bg-darken-2 ">
                    <tr>
                      <th width="20%">Fecha inicio</th>
                      <th width="20%">Fecha fin</th>
                      <th width="20%">Cajero apertura</th>
                      <th width="20%">Estatus</th>
                      <th width="20%">Acciones</th>
                    </tr>
                  </thead>'; 
      while ($row = $this->model->result->fetch_array()) {
          if($row['c_estatus'] == "C") {
            $estatus = "CERRADA";
            $fondoe = "class='alert bg-danger text-white'";
            $ffin = fecha_formato($row['c_ffin'],false,false).' - '.$row['c_hfin'];
          }
          else {
            $ffin = "Caja abierta";
            $estatus = "ABIERTA";
            $fondoe = "class='alert bg-success text-white'";
          }
          echo '<tr>
          <td>'.fecha_formato($row['c_fini'], false, false).' - '.$row['c_hini'].'</td>
          <td>'.$ffin.'</td>
          <td>'.busca($row['c_cajero'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
          <td '.$fondoe.'>'.$estatus.'</td>
          <td>';
          /*
            echo '<button type="button" class="btn btn-info">
                    <a data-toggle="tooltip" data-placement="top" title data-original-title="Editar Categoria" href="?modulo=categorias&accion=index&id='.$row['cat_id'].'"><i class="icon-edit2"></i></a>
                  </button> ' ; */
            

            if($row['c_estatus'] != "C"){
              echo '<a href="?modulo=cajas&accion=show&id='.$row['c_id'].'" href="javascript:;">
                <button type="button" class="btn btn-info">
                  <i class="fas fa-eye" style="color: #ffffff;"></i>
                </button>
              </a>';
              echo '<button type="button" class="btn btn-danger" onclick="cerrarcaja('.$row['c_id'].')">
                    <i data-toggle="tooltip" data-placement="top" title data-original-title="Cerrar caja" class="fas fa-window-close style="color: #ffffff;"></i>
                  </button> ';
            } else {
              echo '<a href="?modulo=cajas&accion=movimientos&id='.$row['c_id'].'" href="javascript:;">
                <button type="button" class="btn btn-info">
                  <i class="fas fa-eye" style="color: #ffffff;"></i>
                </button>
              </a>';
              echo '
                <button type="button" class="btn btn-secondary" onclick="imprimircorte('.$row['c_id'].')">
                  <i class="fas fa-print"></i>
                </button>';
              
            }

          echo '</td>
          ';

      }
      echo '</table>
      
      <script>
      
        </script>';
        ?>
          <script>
            $(document).ready(function () {
              var windowHeight = $(window).height();
            
              $("#myTable").DataTable( {
                  paging: true,
                  scrollY: windowHeight * 0.5,
                  ordering: false,
                  language: {
                      url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
                  },
                  responsivePriority: 1,
              });
            });
            function cerrarcaja(id){
              var win = window.open('popup/cerrarcajapv.php?idcaja='+id,'cobrar','width=800, height=800');  
            }
            
            function cajaabierta(){
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ya existe una caja abierta"
                }).then((result) => {
                    if (result.isConfirmed || result.isDenied) {
                        Swal.close();
                    }
              });
            } 

            function imprimircorte(idcaja){
              //console.log('entro a la funcion');
              $.ajax({
                type: "POST",
                url: "query/ticketcortecaja.php",
                data: {"idcaja": idcaja},
                success: function(data){
                  //console.log(data);
                  var datax = JSON.parse(data);
                  var ventimp = window.open('about:blank', 'ImprimirTicket');
                  ventimp.document.open();
                  ventimp.document.write('<html><head><title>' + document.title + '</title>');
                  //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
                  ventimp.document.write('</head><body>');
                  ventimp.document.write('<div style="width:402px;height:200px">');
                  ventimp.document.write('<center><img src="img/UNICO-LOGO-OFICIAL.png" height="200" width="250px" alt="'+datax['logo']+'"></center>');
                  ventimp.document.write('</div>');
                  ventimp.document.write('<table width="402px"><tr>');
                  ventimp.document.write('<td width="50%" colspan="2" align="center" style="font-size:20px">FECHA DEL CORTE</td></tr>');
                  ventimp.document.write('<tr><td width="50%" colspan="2" align="center" style="font-size:18px">'+datax['fecha']+'</td></tr>');
                  ventimp.document.write('<tr><td colspan="2" height="10px"></td></tr>');
                  ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Apertura:</td><td align="left" colspan="2" style="font-size:20px">'+datax['uapertura']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Cierre:</td><td align="left" colspan="2" style="font-size:20px">'+datax['ucierre']+'</td></tr>');
                  ventimp.document.write('<tr><td colspan="2" height="15px"></td></tr>');
                  ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
                  ventimp.document.write('<tr><td width="100%" colspan="2" align="center" style="font-size:20px">TABULADOR</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">VALOR</td><td align="center" width="20%" style="font-size:20px">CANTIDAD</td></tr>');
                  ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">1000</td><td align="center" width="50%" style="font-size:20px">'+datax['1000']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">500</td><td align="center" width="50%" style="font-size:20px">'+datax['500']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">200</td><td align="center" width="50%" style="font-size:20px">'+datax['200']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">100</td><td align="center" width="50%" style="font-size:20px">'+datax['100']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">50</td><td align="center" width="50%" style="font-size:20px">'+datax['50']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">20</td><td align="center" width="50%" style="font-size:20px">'+datax['20']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">10</td><td align="center" width="50%" style="font-size:20px">'+datax['10']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">5</td><td align="center" width="50%" style="font-size:20px">'+datax['5']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">2</td><td align="center" width="50%" style="font-size:20px">'+datax['2']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">1</td><td align="center" width="50%" style="font-size:20px">'+datax['1']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">50c</td><td align="center" width="50%" style="font-size:20px">'+datax['50c']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Total: </td><td align="center" width="50%" style="font-size:20px">$'+datax['sumtab']+'</td></tr>');
                  ventimp.document.write('<tr><td colspan="2" height="5px"></td></tr>');
                  ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
                  ventimp.document.write('<tr><td width="100%" colspan="2" align="center" style="font-size:20px">EFECTIVO</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Calculado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['efesis']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Contado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['efecap']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Retirado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['eferetiro']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Diferencia:</td><td align="left" width="50%" style="font-size:20px">$'+datax['efedif']+'</td></tr>');
                  ventimp.document.write('<tr><td colspan="2" height="5px"></td></tr>');
                  ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
                  ventimp.document.write('<tr><td width="100%" colspan="2" align="center" style="font-size:20px">TARJETA</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Calculado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['tarsis']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Contado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['tarcap']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Retirado:</td><td align="left" width="50%" style="font-size:20px">$0.00</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Diferencia:</td><td align="left" width="50%" style="font-size:20px">$'+datax['tardif']+'</td></tr>');
                  ventimp.document.write('<tr><td colspan="2" height="5px"></td></tr>');
                  ventimp.document.write('<tr><td colspan="2">-------------------------------------------------------</td></tr>');
                  ventimp.document.write('<tr><td width="100%" colspan="2" align="center" style="font-size:20px">TOTAL</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Calculado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['totalsis']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Contado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['totalcap']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Retirado:</td><td align="left" width="50%" style="font-size:20px">$'+datax['eferetiro']+'</td></tr>');
                  ventimp.document.write('<tr><td width="50%" style="font-size:20px">Diferencia:</td><td align="left" width="50%" style="font-size:20px">$'+datax['totaldif']+'</td></tr>');
                  ventimp.document.write('</table></body></html>');
                  ventimp.document.close();
                  ventimp.focus();

                  ventimp.addEventListener('load', function () {
                    //ventimp.document.open(); 
                      window.close(); 
                      ventimp.print();
                      // Realiza las operaciones en el documento de la ventana emergente aquí
                      ventimp.document.close();
                  });
                }
              });
            }
          </script>
        <?php
  }
  function show($remision, $cliente, $vendedor, $estatus){
    //Sección: E1 Encabezado - Botones de acción
    if(!isset($estatus) || $estatus == "P") $estp = "selected";
    elseif($estatus == "C") $estc = "selected";
    elseif($estatus == "A") $esta = "selected";
    else {
      $estt = "selected";
      $estatus = "";
    }

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
    <?php
      
    $botones = '<a id="ingreso" class="btn btn-sm text-white" style="background:orange" data-fancybox data-type="ajax" data-src="popup/movimientocaja.php?id='.$this->model->id.'&tipo=I&rand='.rand().'" href="javascript:;">
    <i class="fa fa-plus" style="color: #fff"></i> Ingreso
    </a>
    <a>
    <a id="ingreso" class="btn btn-sm btn-info" data-fancybox data-type="ajax" data-src="popup/movimientocaja.php?id='.$this->model->id.'&tipo=G&rand='.rand().'" href="javascript:;">
    <i class="fa fa-minus"></i> Retiro
    </a>';
    $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id' ,'u_grupo'); 
    if($grupo == "ADMIN" || $grupo == "GERENCIA")
    $botones .= '<a href="?modulo=cajas&accion=movimientos&id='.$this->model->id.'"><button class="btn btn-sm btn-secondary"><i class="fas fa-chart-line"></i>Movimientos</button></a>
    ';
    $atras='<a href="?modulo=cajas&accion=index"><button class="btn btn-sm btn-warning"><i class="fas fa-arrow-left"></i>Atrás</button></a>';
      
      
    $filtrar = '
              <form class="form-inline" role="form" method="post" action="?modulo=cajas&accion=index" id="filtro">
              
                <div class="mb-5">
                  <label for="">Remisión:</label>
                  <input type="date" class="form-control" id="pref-search" name="remision" value=" '.$remision.'" placeholder="Buscar por folio de la remision">
                </div>
                <div class="mb-5">
                  <label for="">Cliente:</label>
                  <input type="date" class="form-control" id="pref-search" name="cliente" value=" '.$cliente.'" placeholder="Buscar por nombre del cliente">
                </div>
                <div class="mb-5">
                  <label for="">Vendedor:</label>
                  <input type="date" class="form-control" id="pref-search" name="vendedor" value=" '.$vendedor.'" placeholder="Buscar por vendedor">
                </div>
                <div class="mb-5">
                  <label for="">Buscar por estatus:</label>
                  <select class="form-control" name="estatus">
                  <option value="P" '.$estp.'>Por confirmar</option>
                  <option value="C" '.$estc.'>Cerradas</option>
                  <option value="A" '.$esta.'>Todas</option>
                  <option value="T" '.$estt.'>Todas</option>
                  </select>
                </div>
                <div class="mb-5">
                  <label for="">Acciones:</label><br>
                <button type="submit" class="btn btn-info">
                  <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                </button>
                <a href="?modulo=categorias&accion=index"><button type="button" class="btn btn-warning">
                  <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
                </button></a>
                </div>
              </form>';

        toolbar("MOVIMIENTOS EN CAJA",$atras, '', $botones);
  
  $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
  if($grupo != "ADMIN" && $grupo != "GERENCIA") $sqladd = ' AND r_encargado = "'.$_SESSION['uid'].'"';
  else $sqladd = "";
  $sql = 'SELECT * FROM remisiones INNER JOIN cxcobrar ON cx_referencia = r_id WHERE r_estatus NOT IN ("F", "N", "FE", "C") '.$sqladd.' ORDER BY cx_estatus DESC';
  $result = setq($sql);     
  echo '
      <div class="card mt-3">
      <div class="card-body row" >';
        echo '<div class="col-md-12 col-12 col-sm-12">
          <div class="table-responsive text-medium" >
            <center>
              <table class="table" id="myTable">
                <thead class="bg-light-blue bg-darken-2 ">
                  <tr>
                    <th width="16%">Cliente</th>
                    <th width="16%">Remision</th>
                    <th width="16%">Vendedor</th>
                    <th width="12%">Total de la cuenta</th>
                    <th width="10%">Abonado</th>
                    <th width="10%">Saldo</th>
                    <th width="10%">Estatus</th>
                    <th width="10%">Acciones</th>
                  </tr>
                </thead>'; 
    while ($row = $result->fetch_array()){
        $cliente = busca(busca($row['r_tablero'], 'crm_tableros', 'ct_id', 'ct_cliente'), 'crm_clientes', 'c_id', 'CONCAT(c_nmb, " ", c_apellidos)');
        $saldo = $row['cx_importe'] - $row['cx_abonado'];
        $vendedor = busca($row['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
        echo '<tr>
        <td>'.trim($cliente).'</td>
        <td>'.$row['r_folio'].'</td>
        <td>'.$vendedor.'</td>
        <td>$ '.number_format($row['cx_importe'], 2).'</td>
        <td>$ '.number_format($row['cx_abonado'], 2).'</td>
        <td>$ '.number_format($saldo, 2).'</td>';
        if($row['cx_estatus'] == "N"){
          echo '<td class="alert text-white" style="background:#ffc107;color:#F5B041;">SIN ABONAR</a></td>';
        }else if($row['cx_estatus'] == "A"){
          echo '<td class="alert text-white" style="background:#58D68D ;">ABONADA</a></td>';
        }else if($row['cx_estatus'] == "F"){
          echo '<td class="alert text-white" style="background:#2153F0;">LIQUIDADA</a></td>';
        }
        if($row['cx_estatus'] != "F"){
          echo '<td>';
            echo '
            <button type="button" class="btn btn-sm" style="background: teal" onclick="cobrar('.$this->model->id.','.$row['r_id'].')">
              <i class="far fa-credit-card" style="color:#ffffff"></i>
            </button>
            ';
          echo '</td>
          '; 
        } else {
          echo '<td>';
            echo '
              <button type="button" class="btn btn-secondary" >
                <i class="fas fa-print"></i> 
              </button>
            ';
          echo '</td>
          '; 
        }
    }
    echo '</table>
    
    <script>
    
      </script>';
      ?>
        <script>
          $(document).ready(function () {
            var windowHeight = $(window).height();
          
            $("#myTable").DataTable( {
                paging: true,
                scrollY: windowHeight * 0.5,
                ordering: false,
                language: {
                    url: "//cdn.datatbles.net/plug-ins/1.13.6/i18n/es-ES.json",
                },
                responsivePriority: 1,
                pageLength: 50,
            });
          });
          function cerrarcaja(id){
            var cerrar = confirm("¿Deseas cerrar la caja?");
            if(cerrar) window.location.href="?modulo=cajas&accion=cerrarcaja&id=" + id;
          }
          
          function cajaabierta(){
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Ya existe una caja abierta"
              }).then((result) => {
                  if (result.isConfirmed || result.isDenied) {
                      Swal.close();
                  }
            });
          } 
          function confirmar(id,idd){
            Swal.fire({
              icon: "question",
              text: "Qué acción deseas realizar?",
              showDenyButton: true,
              showCancelButton: true,
              confirmButtonColor: "#20c997",
              confirmButtonText: '<i class="fas fa-check" style="color:#ffffff"></i> Confirmar',
              denyButtonText: `<i class="fas fa-times" style="color:#ffffff"></i> Cancelar pago`,
              cancelButtonText: 'Salir'
              }).then((result) => {
                  if (result.isConfirmed) {
                    window.location.href = '?modulo=cajas&accion=confirmarabono&idd='+idd+'&id='+id;
                  } else if (result.isDenied){
                    window.location.href = '?modulo=cajas&accion=cancelarabono&idd='+idd+'&id='+id;
                    /* console.log('cancelado'); */
                  } else {
                    Swal.close();
                  }
            });
          }

          function cobrar(caja, remision){
            var win = window.open('popup/cobrarpv.php?idcaja='+caja+'&remision='+remision,'cobrar','width=800, height=800');  
          }
        </script>
      <?php
  }


  function movimientos(){
    //Sección: E1 Encabezado - Botones de acción
    if(!isset($estatus) || $estatus == "P") $estp = "selected";
    elseif($estatus == "C") $estc = "selected";
    elseif($estatus == "A") $esta = "selected";
    else {
      $estt = "selected";
      $estatus = "";
    }

    //Sección: E2 Encabezado - Filtros
    
    if($this->model->estatus == "C"){
      $atras='<a href="?modulo=cajas&accion=index"><button class="btn btn-sm btn-warning"><i class="fas fa-arrow-left"></i>Atrás</button></a>';
    } else {
      $atras='<a href="?modulo=cajas&accion=show&id='.$this->model->id.'"><button class="btn btn-sm btn-warning"><i class="fas fa-arrow-left"></i>Atrás</button></a>';
    }

    toolbar("CAJAS",$atras);

    $fondo = busca($_GET['id'],'cajas','c_id','c_fondo');

    echo '<div class="card mt-3">
    <div class="card-body row" >';
      echo '<div class="col-md-12 col-12 col-sm-12">
    <div class="col-md-12 mt-4">
    <div class="alert alert-success text-xs-center">Ingresos a caja</div>';
    echo '<div class="table-responsive">
      <table width="100%" class="table table-striped " style="font-size: small;">
        <thead class="bg-primary bg-darken-2 text-white">
          <tr>
            <th>Remisión</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Efectivo</th>
            <th>Tarjeta</th>
            <th>Generó</th>
            <th>Estatus</th>
            <th>Acciones</th>
          </tr>
        </thead><tbody>';
    $sumat = 0;
    $sumae = 0;
    $sumad = 0;
    $sumar = 0;
    $sumao = 0;
    $sumacam = 0;
    $sumamd = 0;
    $sumatd = 0;
    $sql = 'SELECT * FROM cajasd WHERE cd_caja = "'.$this->model->id.'" ORDER BY cd_id DESC';
    $result = setq($sql);
    while($row = $result->fetch_array()){
      $sumatodo = $row['cd_total'];
      $folio = busca($row['cd_remision'], 'remisiones', 'r_id', 'r_folio');
      $efe = $row['cd_efectivo'];
      $tarjeta = $row['cd_tarjeta'];
      $trans = $row['cd_transferencia'];
      echo '<tr>
              <td>'.$folio.'</td>
              <td>'.cambiar_fecha($row['cd_fgen'],true,true).'</td>
              <td class="number-align">$'.number_format($sumatodo,2).'</td>
              <td class="number-align">$'.number_format($efe,2).'</td>
              <td class="number-align">$'.number_format($tarjeta,2).'</td>';
              echo '
              <td class="">'.busca($row['cd_ugen'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>';
              if($row['cd_estatus'] == "C"){
                echo '<td class="alert text-white" style="background:#F03D21;color:#FFFFFF;">CANCELADO</a></td>';
              }else if($row['cd_estatus'] == "A"){
                echo '<td class="alert text-white" style="background:#007bff;">APLICADO</a></td>';
              }else if($row['cd_estatus'] == "P"){
                echo '<td class="alert" style="background:#FFB162;">POR CONFIRMAR</a></td>';
              }
              echo '<td nowrap>
                <button class="btn btn-secondary btn-sm" type="button" onclick="imprimirticket('.$row['cd_id'].');" ><i class="fas fa-print"></i> Imprimir</button>
                ';
                /* $idfac = busca($row['pt_id'],'facturas','f_ticket','f_id');
                if($row['pt_estatus'] == "A"){
                  echo '
                    
                  ';    
                }
                if($row['pt_estatus'] == "A" && !isset($idfac)){
                  echo '
                    <button type="button" class="btn btn-success btn-sm" id="facturar'.$row['pt_id'].'" onclick="facturar('.$row['pt_id'].');"><i class="icon-code2"></i> Facturar</button>
                  ';   
                }elseif(isset($idfac)){
                  echo '
                    <a class="btn btn-warning btn-sm" target="_blank" href="?modulo=facturas&accion=showfactura&factura='.$idfac.'"><i class="icon-eye"></i> Ver Factura</a>
                  ';   
                }
                if($row['pt_estatus'] == "R"){
                  $idcx = busca($row['pt_fiesta'],'cxcobrar','cx_tipo = "R" AND cx_referencia','cx_id');
                  $idcxestatus = busca($row['pt_fiesta'],'cxcobrar','cx_tipo = "R" AND cx_referencia','cx_estatus');
                  if((($idcxestatus =='F') || ($idcxestatus == 'C')) || $sumatodo > 0) echo '<a class="btn btn-primary btn-sm" target="_blank" href="?modulo=cxcobrar&accion=show&idcxc='.$idcx.'"><i class="icon-eye"></i> Ver Cuenta</a>';   
                  else echo '<a data-fancybox data-type="ajax" data-src="popup/setingreso.php?idcxc='.$idcx.'&rand='.rand(1,100).'&tck='.$row['pt_id'].'" href="javascript:;" class="btn btn-info btn-sm"><i class="icon-dollar"></i> Pagar</a>';
                }
                if($row['pt_estatus'] == "A" && $this->model->estatus == "N"){
                  echo '
                  <button type="button" class="btn btn-sm btn-danger" onclick="cancelartck('.$row['pt_id'].','.$_GET['id'].')"><i class="icon-close"></i> Cancelar</button>
                  ';
                } */
              echo '
              </td>
            </tr>';      

      if($row['cd_estatus'] == "A"){
        $sumat+=$row['cd_total'];
        $sumae+= $efe;
        $sumad+=$tarjeta;
        $sumar+=$trans;
      }
    }
    echo '</tbody></tfoot>';
      echo '<tr>
              <td>'.$row['pt_folio'].'</td>
              <th>Totales</th>
              <th class="number-align">$'.number_format($sumat,2).'</th>
              <th class="number-align">$'.number_format($sumae,2).'</th>
              <th class="number-align">$'.number_format($sumad,2).'</th>
            </tr>';      

    echo '</tfoot></table></div>';
    echo '</div>';
    echo '
        <script class="">
        function facturar(id){
          $("#facturar"+id).prop("disabled",true);
          window.location.href="?modulo=cajas&accion=facturartck&id="+id
        }
        
       </script>';

       ?>
       <script>
        function imprimirticket(idcd){
          //console.log('entro a la funcion');
          $.ajax({
            type: "POST",
            url: "query/ticketventa.php",
            data: {"idcd": idcd},
            success: function(data){
              console.log(data);
              var datax = JSON.parse(data);
              var nmbart = datax['articulos'].split('/');
              var cantart = datax['cantidad'].split('/');
              var preart = datax['precio'].split('/');
              var ventimp = window.open('about:blank', 'ImprimirTicket');
              ventimp.document.open();
              ventimp.document.write('<html><head><title>' + document.title + '</title>');
              //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
              ventimp.document.write('</head><body>');
              ventimp.document.write('<div style="width:402px;height:200px">');
              ventimp.document.write('<center><img src="img/UNICO-LOGO-OFICIAL.png" height="200" width="200px" alt="img/UNICO-LOGO-OFICIAL.png"');
              ventimp.document.write('</div>');
              ventimp.document.write('<div width="100%" style="font-size:14px">'+datax['direccion']+'</div>');
              ventimp.document.write('<div width="100%" style="font-size:14px">'+datax['telefono']+'</div></center>');
              ventimp.document.write('<table width="402px"><tr>');
              ventimp.document.write('<td width="50%" colspan="3" align="center" style="font-size:20px">FECHA</td></tr>');
              ventimp.document.write('<tr><td width="50%" colspan="3" align="center" style="font-size:20px">'+datax['fecha']+'</td></tr>');
              ventimp.document.write('<tr><td colspan="3" height="10px"></td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Folio:</td><td align="left" colspan="2" style="font-size:20px">'+datax['folio']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Genera:</td><td align="left" colspan="2" style="font-size:20px">'+datax['genera']+'</td></tr>');
              ventimp.document.write('<tr><td colspan="3" height="15px"></td></tr>');
              ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
              ventimp.document.write('<tr><td width="50%" style="font-size:20px">PRODUCTO</td><td align="center" width="20%" style="font-size:20px">CANTIDAD</td><td align="center" width="30%" style="font-size:20px">PRECIO</td></tr>');
              ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
              for(var i=0; i<nmbart.length;i++){
                ventimp.document.write('<tr><td width="50%" style="font-size:20px">'+nmbart[i]+'</td><td align="center" width="20%" style="font-size:20px">'+cantart[i]+'</td><td align="center" width="30%" style="font-size:20px">$'+preart[i]+'</td></tr>');
                ventimp.document.write('<tr><td colspan="3" height="5px"></td></tr>');
              }
              ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Efectivo recibido:</td><td align="right" style="font-size:14px">$'+datax['efectivorec']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Efectivo cobrado:</td><td align="right" style="font-size:14px">$'+datax['efectivo']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Cambio:</td><td align="right" style="font-size:14px">$'+datax['cambio']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Tarjeta:</td><td align="right" style="font-size:14px">$'+datax['tarjeta']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:14px">Total cobrado:</td><td align="right" style="font-size:14px">$'+datax['totalcd']+'</tr>');
              ventimp.document.write('<tr><td colspan="3">-------------------------------------------------------</td></tr>');
              ventimp.document.write('<tr><td width="100%" colspan="3" align="center" style="font-size:20px">CUENTA</td>');
              if(datax['descuentop'] != "0.00"){
                ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">% Descuento:</td><td align="right" style="font-size:20px">'+datax['descuentop']+'</td></tr>');
                ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Subtotal:</td><td align="right" style="font-size:20px">$'+datax['subtotal']+'</td></tr>');
                ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Descuento:</td><td align="right" style="font-size:20px">$'+datax['descuento']+'</td></tr>');
              }
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Total:</td><td align="right" style="font-size:20px">$'+datax['totalrem']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Abonado:</td><td align="right" style="font-size:20px">$'+datax['abonado']+'</tr>');
              ventimp.document.write('<tr><td width="50%" colspan="2" align="right" style="font-size:20px">Saldo:</td><td align="right" style="font-size:20px">$'+datax['saldo']+'</tr>');
              
              ventimp.document.write('<tr><td colspan="3" height="20px"></td></tr>');
              ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:18px">Gracias por su compra</td></tr>');
              ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:18px">Este recibo no es un comprobante fiscal</td></tr>');
              ventimp.document.write('<tr><td colspan="3" align="center" style="font-size:20px">'+datax['texto']+'</td></tr>');
              ventimp.document.write('</table></body></html>');
              ventimp.document.close();
              ventimp.focus();

              ventimp.addEventListener('load', function () {
                //ventimp.document.open(); 
                  window.close(); 
                  ventimp.print();
                  // Realiza las operaciones en el documento de la ventana emergente aquí
                  ventimp.document.close();
              });
            }
          });
        }
       </script>
       <?php

    ///////////////////////////////////////////Tabla de pv_ingresos////////////////////////////////////////////

echo '
<div class="col-md-12">
<div class="alert alert-success text-xs-center">Otros ingresos a caja</div>';
echo '<div class="table-responsive">
  <table width="100%" class="table table-striped " style="font-size: small;">
    <thead class="bg-primary bg-darken-2 text-white">
      <tr>
        <th>Registró</th>
        <th>Fecha</th>
        <th>Importe</th>
        <th>Descripción</th>
        <th></th>
      </tr>
    </thead><tbody>';

$sqli = 'SELECT * FROM cajas_movimientos WHERE cm_caja = "'.$this->model->id.'" AND cm_tipo = "I" ORDER BY cm_id DESC';
$resulti = setq($sqli);
$totali = 0;
while($rowi = $resulti->fetch_array()){
  echo '<tr>
          <td width="20%">'.busca($rowi['cm_ugen'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
          <td width="20%">'.$rowi['cm_fgen'].'</td>
          <td width="20%" class="number-align">$'.number_format($rowi['cm_monto'],2).'</td>
          <td width="20%">'.$rowi['cm_descripcion'].'</td>';
          echo '
          <td width="20%">
            <button class="btn btn-secondary btn-sm" type="button" onclick="imprimirticketmov('.$rowi['cm_id'].');" ><i class="fas fa-print"></i> Imprimir</button>
          ';
          /* $facturaing = busca($rowi['pi_id'],'facturas','f_ingreso','f_id');
            if($rowi['pi_referencia'] && !isset($facturaing)){
              echo '
              <button id="facturaring'.$rowi['pi_id'].'" onclick="facturaring('.$rowi['pi_id'].');" type="button" class="btn btn-sm btn-success"><i class="icon-code"></i> Facturar</button>
              ';
            }
            if($facturaing){
              echo '
              <a href="?modulo=facturas&accion=showfactura&factura='.$facturaing.'" class="btn btn-orange btn-sm" target="_blank"><i class="icon-eye"></i> Ver Factura</a>
              ';
            } */
          echo '
            </td>
          </tr>';
  $totali+=$rowi['cm_monto'];
            }
  echo '<tr>
          <td></td><td>Totales</td>
          <td width="20%" class="number-align">$'.number_format($totali,2).'</td>
        </tr>';
  echo '</table></div>';
echo '</div>
<script class="">
  function facturaring(id){
    var c = confirm("¿Deseas facturar este ingreso?");
    if(c){
      $("#facturaring"+id).prop("disabled",true);
      window.location.href="?modulo=cajas&accion=facturaring&id="+id
    }
  }
</script>
';

?>
  <script>
    function imprimirticketmov(idcd){
          console.log('entro a la funcion');
          $.ajax({
            type: "POST",
            url: "query/ticketmovcaja.php",
            data: {"mov": idcd},
            success: function(data){
              console.log(data);
              var datax = JSON.parse(data);
              var ventimp = window.open('about:blank', 'ImprimirTicket');
              ventimp.document.open();
              ventimp.document.write('<html><head><title>' + document.title + '</title>');
              ventimp.document.write('</head><body>');
              ventimp.document.write('<div style="width:402px;height:200px">');
              ventimp.document.write('<center><img src="img/UNICO-LOGO-OFICIAL.png" height="200px" width="200px" alt="img/UNICO-LOGO-OFICIAL.png"></center>');
              ventimp.document.write('</div>');
              //ventimp.document.write('<style type="text/css" media="print">@page{margin-right: 10px;}</style>');
              ventimp.document.write('</head><body>');
              ventimp.document.write('<table width="402px"><tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Fecha:</td><td align="left" colspan="2" style="font-size:20px">'+datax['fecha']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Genera:</td><td align="left" colspan="2" style="font-size:20px">'+datax['ugen']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Monto:</td><td align="left" colspan="2" style="font-size:20px">$'+datax['monto']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Tipo:</td><td align="left" colspan="2" style="font-size:20px">'+datax['tipo']+'</td></tr>');
              ventimp.document.write('<tr><td width="50%" align="center" style="font-size:20px">Motivo:</td><td align="left" colspan="2" style="font-size:20px">'+datax['obs']+'</td></tr>');
              ventimp.document.write('<tr><td colspan="3" height="15px"></td></tr>');
              ventimp.document.write('</table></body></html>');
              ventimp.document.close();
              ventimp.focus();

              var checkVentimp = setInterval(function() {
                  if (ventimp.document.readyState === 'complete') {
                      clearInterval(checkVentimp); // Detenemos el temporizador
                      console.log('Ventana emergente cargada');
                      ventimp.print();
                      ventimp.close();
                  }
              }, 100);
            }
          });
        }
  </script>

<?php

/////////////////////////////////////////Fin tabla de pv_ingresos//////////////////////////////////////////


///////////////////////////////////////////Tabla de pv_retiros////////////////////////////////////////////

echo '
<div class="col-md-12 " hidden  >
<div class="alert alert-success text-xs-center">Retiros realizados de caja</div>';
echo '<div class="table-responsive">
  <table width="100%" class="table table-striped " style="font-size: small;">
    <thead class="bg-primary bg-darken-2 text-white">
      <tr>
        <th>Cajero</th>
        <th>Fecha</th>
        <th>Total</th>            
        <th>Descripción</th>
        <th>Autoriza</th>
        <th></th>
      </tr>
    </thead><tbody>';

$sqli = 'SELECT * FROM cajas_movimientos WHERE cm_caja = "'.$this->model->id.'" AND cm_tipo = "G" ORDER BY cm_id DESC';
$resultr = setq($sqli);
while($rowr = $resultr->fetch_array()){
  
  echo '<tr>
          <td width="20%">'.$rowi['cm_ugen'].'</td>
          <td width="20%">'.$rowi['cm_fgen'].'</td>
          <td width="20%" class="number-align">$'.number_format($rowi['cm_monto'],2).'</td>
          <td width="20%">'.busca($rowi['cm_fpago'],'cfdi_fpago','cf_id','cf_descripcion').'</td>
          <td width="20%">'.$rowi['cm_descripcion'].'</td>';
          echo '                
          <td width="15%">
          <td><button class="btn btn-secondary btn-sm" type="button" onclick="imprimirticketmov('.$row['cm_id'].');" ><i class="fas fa-print"></i> Imprimir</button></td>
              ';
            }
          echo '
          </td>
        </tr>
        </table></div>';
echo '</div>';


/////////////////////////////////////////Fin tabla de pv_retiros//////////////////////////////////////////

    echo '<div class="col-md-12">';
    echo '<div class="alert alert-danger text-xs-center">Salidas de caja</div>';

    echo '<div class="table-responsive">
      <table width="100%" class="table table-striped" style="font-size: small;">
        <thead class="bg-primary bg-darken-2 text-white">
          <tr>
            <th>Registró</th>
            <th>Fecha</th>
            <th>Importe</th>
            <th class="">Descripción</th>
            <th class=""></th>
            <th></th>
          </tr>
        </thead>';

    $sumas = 0;
    $sqli = 'SELECT * FROM cajas_movimientos WHERE cm_caja = "'.$this->model->id.'" AND cm_tipo = "G" ORDER BY cm_id DESC';
    $resultr = setq($sqli);
    while($row = $resultr->fetch_array()){
      echo '
          <tr>
            <td width="20%">'.busca($row['cm_ugen'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)').'</td>
            <td width="20%">'.$row['cm_fgen'].'</td>
            <td width="20%" class="number-align">$'.number_format($row['cm_monto'],2).'</td>
            <td width="20%">'.$row['cm_descripcion'].'</td>
            <td><button class="btn btn-secondary btn-sm" type="button" onclick="imprimirticketmov('.$row['cm_id'].');" ><i class="fas fa-print"></i> Imprimir</button></td>
          </tr>
          <tr>
          </tr>';
      $sumas+=$row['cm_monto'];
    }
    echo '</tbody></tfoot>';
      echo '<tr>
              <th colspan=""></th>
              <th>Totales</th>
              <th class="number-align">$'.number_format($sumas,2).'</th>
            </tr>';      

    echo '</tfoot></table></div>';
    echo '';


//    if($this->model->estatus == "F"){
    $sumasinefe = busca($_GET['id'],'cajas_movimientos','cm_tipo = "I" AND cm_fpago = 1 AND cm_caja','SUM(cm_monto)');
    $sumasinotr = busca($_GET['id'],'cajas_movimientos','cm_tipo = "I" AND cm_fpago != 1 AND cm_caja','SUM(cm_monto)');

    echo '
    <div class="table-resposive" id="tablapruebaaa">
      <table class="table table-striped">
        <thead class="text-white">
          <tr>
            <th class="text-xs-center bg-warning bg-darken-3 ">Corte</th>
            <th class="text-xs-center bg-success bg-darken-3" colspan="7">Resumen de ingresos</th>
          </tr>
          <tr class="">
            <td class="text-xs-center bg-warning bg-darken-3 ">Corte de caja</td>
            <td class="text-xs-center bg-success bg-darken-3">Total ventas Efectivo</td>
            <td class="text-xs-center bg-success bg-darken-3">Total ventas TDC</td>
            <td class="text-xs-center bg-success bg-darken-3">Fondo </td>
            <td class="text-xs-center bg-success bg-darken-3">Ingresos Efectivo</td>
            <td class="text-xs-center bg-success bg-darken-3">Retiros caja</td>
            <!-- <td class="text-xs-center">Utilidad final en caja</td> -->
          </tr>
        </thead>
        <tbody class="tbody">
          <tr class="">
            <th class="text-xs-center">$'.number_format($sumat,2).'</th>
            <th class="text-xs-center">$'.number_format($sumae,2).'</th>
            <th class="text-xs-center">$'.number_format($sumad,2).'</th>
            <th class="text-xs-center">$'.number_format($fondo,2).'</th>
            <th class="text-xs-center">$'.number_format($sumasinefe,2).'</th>
            <th class="text-xs-center">- $'.number_format($sumas,2).'</th>
            <!-- <th class="text-xs-center">$'.number_format($sumat-$sumas,2).'</th> -->
          </tr>';
          $totreg = busca($this->model->id, 'cajas', 'c_id', 'c_total');
          //echo 'totreg: '.$totreg ;
          $differencia = ($totreg)-($sumat+$fondo-$sumas+$sumasinefe+$sumasinotr);
          if($differencia < 0) $bg = 'bg-danger';
          else $bg = 'bg-success';

          $efecaja = $fondo+$sumae-$sumas+$sumasinefe;
          echo '

          <tr>
            <td class="text-xs-center bg-warning text-white bg-darken-3" >$'.number_format($sumat,2).'</td>
            <td class="text-xs-center bg-info text-white bg-darken-3">Efectivo en caja</td>
            <td class="text-xs-center bg-info text-white bg-darken-3"><b>'.number_format($efecaja+$sumasin,2).'</b></td>';
            if(isset($_POST['arq'])){
              echo '<td class="text-xs-center bg-info text-white bg-darken-3">Arqueo de caja</td>
            <td class="text-xs-center bg-info text-white bg-darken-3"><b>'.number_format(floatval($_POST['arq']),2).'</b></td>';
            echo '<td class="text-xs-center bg-info text-white bg-darken-3">Diferencia</td>
            <td class="text-xs-center bg-info text-white bg-darken-3"><b>'.number_format((floatval($_POST['arq'])) - ($efecaja+$sumasin),2).'</b></td>';
            }
          echo '</tr>';
      if($this->model->estatus == "C")
          echo  '<tr>
            <th class="text-xs-center '.$bg.'  bg-darken-3"></th>
            <th class="text-xs-center '.$bg.' ">DIFERENCIA: </th>
            <th class="text-xs-center '.$bg.' " >$'.number_format($differencia,2).'</th>
            <!-- <td class="text-xs-center bg-lime text-white" colspan="2">Total: '.number_format($sumat-$sumas+$fondo,2).'</td> -->
          </tr>';
      echo '</tbody>
      </table>
    </div>
    </div></div>
    ';
  }
}
?>