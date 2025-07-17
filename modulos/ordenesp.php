<?php
//ini_set('display_errors', 1);
  /*class ordenesp {
    var $model;
    var $view;

    function __construct() {
      $this->model = new modelordenesp(isset($obj));
    }
    function index() {
      if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-60 days'));
      if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d');
      if (!isset($_REQUEST['proveedor'])) $_REQUEST['proveedor'] = NULL;
      if (!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if (!isset($_REQUEST['folio'])) $_REQUEST['folio'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

      $this->model->result($_REQUEST['page'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['estatus'],$_REQUEST['proveedor']);
      $this->view = new viewordenesp($this->model);
      //function browse($fini,$ffin,$proveedor,$estatus,$page) {
      $this->view->browse($_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['proveedor'],$_REQUEST['estatus'],$_REQUEST['page']);
    }
    function show(){ 
      $this->model->select($_GET['id']);
      $this->model->resultd($_GET['id']);
      $this->view = new viewordenesp($this->model);
      $this->view->show();
    }
  }

  class modelordenesp {
    function result($page,$fini,$ffin,$estatus,$proveedor){
      $bloque = 50;
      $prov = clearvmayus($proveedor);

      $sql = 'SELECT * FROM ordenesp WHERE op_empresa = "'.$_SESSION['emp'].'"
              AND DATE(op_fechac) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
      //if($proveedor) $sql.=' AND o_proveedor IN (SELECT p_id FROM proveedores WHERE p_nmb LIKE "%'.$prov.'%" OR p_alias LIKE "%'.$prov.'%")';
      if($estatus) $sql.=' AND op_estatus = "'.$estatus.'"';
      $sql.='ORDER BY op_id DESC';
      $result = setq($sql);
      if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
      $this->resultrc = setq($sql);
      $this->resultt = setq($sql);
    }
    function select($id){
      $sql = 'SELECT * FROM ordenesp WHERE o_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['op_id'];
      $this->folio = $row['op_folio'];
      $this->tipo = $row['op_tipo'];
      $this->nmb = $row['op_nmb'];
      $this->proyecto = $row['op_proyecto'];
      $this->imp = $row['op_importe'];
      $this->estatus = $row['op_estatus'];
      $this->fechac = $row['op_fechac'];
      $this->userc = $row['op_userc'];
      $this->autorizo = $row['op_autorizo'];
      $this->fechaa = $row['op_fechaa'];
    }
    function setdata($id,$folio,$tipo,$nmb,$proyecto,$imp,$estatus,$fechac,$userc,$autorizo,$fechaa){
      $this->id = $id;
      $this->folio = $folio;
      $this->tipo = $tipo;
      $this->nmb = $nmb;
      $this->proyecto = $proyecto;
      $this->imp = $imp;
      $this->estatus = $estatus;
      $this->fechac = $fechac;
      $this->userc = $userc;
      $this->autorizo = $autorizo;
      $this->fechaa = $fechaa;
    }
    function insert(){
      $sql = 'INSERT INTO ordenesp SET
              op_folio = "'.$this->folio.'",
              op_tipo = "'.$this->tipo.'",
              op_nmb = "'.$this->nmb.'",
              op_proyecto = "'.$this->proyecto.'",
              op_importe = "'.$this->imp.'",
              op_estatus = "'.$this->estatus.'",
              op_fechac = "'.$this->fechac.'",
              op_userc = "'.$this->userc.'",
              op_autorizo = "'.$this->autorizo.'",
              op_fechaa = "'.$this->fechaa.'"';
      setq($sql);

      $this->id = getmax('o_id','ordenesc','o_empresa = "'.$_SESSION['emp'].'"',false);
      return($this->id);
    }
    function update(){
      $sql = 'UPDATE ordenesp SET
              op_nmb = "'.$this->nmb.'",
              op_importe = "'.$this->imp.'",
              op_estatus = "'.$this->estatus.'",
              WHERE o_id = "'.$this->id.'"';
      setq($sql);
    }
  }

  class viewordenesp{
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->tipodoc = array("F"=>"Fiscal","C"=>"Contable");
    }
    function browse($fini,$ffin,$proveedor,$estatus,$page) {
      ?>
      <script language="JavaScript">
        function checksubmit() {
          document.getElementById("guardar").value = "JD";
          document.getElementById("guardar").disabled = true;
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
          var btn = document.getElementById("filtrar");
          btn.click();
          $("#proveedor").focus();
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
      $this->model->aestatus = array("F" => "Recibido","A" => "Aplicado","P" => "En espera de autorización","N" => "En Captura","C" => "Cancelado");
      $this->model->nestatus = array("F" => "btn-primary","A" => "btn-success","P" => "bg-amber bg-darken-2","N"=>"bg-yellow bg-darken-3","C" => "btn-danger");
  
        $accion = 'insert';
        if (isset($this->model->id))$accion = 'update';
        //Sección: E1 Encabezado - Botones de acción
        echo '<div class="row page-title-actions">';
          //Sección: E2 Encabezado - Filtros
          $selt = '';
          $sela = '';
          $seln = '';
          $selp = '';
          $selc = '';
          if($estatus == "A") $sela = "selected";
          if($estatus == "N") $seln = "selected";
          if($estatus == "P") $selp = "selected";
          if($estatus == "C") $selc = "selected";
          else $selt = 'selected';
          ?>
          <div class="col-md-12 mb-1">
            <a id="nuevo" data-fancybox data-type="ajax" data-src="popup/setordenc.php" href="javascript:;">
              <button type="button" class="btn btn-primary">
                <span class="glyphicon glyphicon-cog"></span><i class="fa fa-plus"></i> Nuevo
              </button>
            </a>
            <button id="filtrar" type="button" class="btn btn-info"  data-toggle="collapse" data-target="#filter-panel">
              <span class="glyphicon glyphicon-cog"></span><i class="fa fa-search5"></i> Filtrar
            </button>
          </div>
          <div id="filter-panel" class="col-md-12 collapse filter-panel mb-1">
            <div class="panel panel-default">
              <div class="panel-body">
                <form class="form-inline" role="form" method="post" >
                  <div class="mb-5">
                    <label for="tipom">Desde:</label>
                    <input type="date" name="fini" id="fini" class="form-control" value="<?php echo $fini ?>" />
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Hasta:</label>
                    <input type="date" name="ffin" id="ffin" class="form-control" value="<?php echo $ffin?>" />
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Proveedor:</label>
                    <input type="text" class="form-control" id="proveedor" name="proveedor" placeholder="Nombre del proveedor que buscas" onfocus="this.select();" value="<?php echo $proveedor ?>" />
                  </div>
                  <div class="mb-5">
                    <label for="tipom">Estatus:</label>
                    <select class="form-control" name="estatus" id="estatus" >
                      <option value="" <?php echo $selt; ?> >Todos</option>
                      <option value="N" <?php echo $seln; ?> >En captura</option>
                      <option value="A" <?php echo $sela; ?> >Aplicados</option>
                      <option value="P" <?php echo $selp; ?> >Espera de Autorización</option>
                      <option value="C" <?php echo $selc; ?> >Cancelados</option>
                    </select>
                  </div><!-- form group [search] -->
                  <div class="mb-5">
                    <label for="">Acciones:</label> <br>
                    <button type="submit" class="btn btn-info">
                      <span class="glyphicon glyphicon-record"></span> <i class="fa fa-redo"></i> Actualizar
                    </button>
                    <!-- <a href="?modulo=almacenes&accion=index"><button type="button" class="btn btn-warning"> -->
                    <a href="?modulo=ordenesc&accion=index">
                      <button type="button" class="btn btn-warning">
                        <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
                      </button>
                    </a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="main-card mb-3 card">
        <div class="card-body row">
          <?php
          echo '<div class="table table-responsive">
            <table class="table table-hover table-striped">
              <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
                <tr>
                  <th>Folio</th>
                  <th>Proveedor</th>
                  <th>Último Movimiento</th>
                  <th>Fecha de compra</th>
                  <th>Importe</th>
                  <th>Observaciones</th>
                  <th>Estatus</th>
                  <th></th>
                </tr>
              </thead>';
              while($row = $this->model->resultrc->fetch_array()){
                if($row['o_estatus'] == "P" || $row['o_estatus'] == "A") $dir = "showoc"; else $dir = "show";
                if($row['o_estatus'] == "A") $lastm = "Aplicación: <b>".$row['o_uapli'].'</b><br>'.date('d-m-Y',strtotime($row['o_fapli']));
                elseif($row['o_estatus'] == "C") $lastm = "Cancelación: <b>".$row['o_ucan'].'</b><br>'.date('d-m-Y',strtotime($row['o_fcan']));
                else $lastm = "Registro: <b>".$row['o_ugen'].'</b><br>'.date('d-m-Y',strtotime($row['o_fgen']));
                
                if($row['o_tipooc'] == "A") $tipor = "Fiscal";
                else $tipor = "Contable";

                echo '<tr>
                  <th>'.$row['o_folio'].'</th>
                  <td>'.busca($row['o_proveedor'],'proveedores','p_id','p_alias').'</td>
                  <td>'.$lastm.'</td>
                  <td>'.fecha_formato($row['o_fechacom'],false,false).'</td>
                  <td class="number-align">$'.number_format($row['o_total'],2).'</td>
                  <td>'.$row['o_observaciones'].'</td>
                  <td class="'.$this->model->nestatus[$row['o_estatus']].'">'.$this->model->aestatus[$row['o_estatus']].'</td>
                  <td>
                    <a href="?modulo=ordenesc&accion='.$dir.'&id='.$row['o_id'].'">
                      <button type="button" class="btn btn-info"><i class="fa fa-eye"></i> Detalles</button>
                    </a>';
                    if($row['o_estatus'] == "A"){
                      echo '<a target="_BLANK" href="formats/pdfordenc.php?id='.$row['o_id'].'">
                        <button type="button" class="btn btn-secondary"><i class="icon-print"></i> Imprimir</button>
                      </a>';
                      echo '<a data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?ordenc='.$row['o_id'].'" href="javascript:;">
                        <button type="button" class="btn btn-primary">
                          <span class="glyphicon glyphicon-cog"></span><i class="icon-cart-arrow-down"></i> Recibir
                        </button>
                      </a>';
                    }elseif($row['o_estatus'] == "F"){
                      echo '<a target="_BLANK" href="formats/pdfordenc.php?id='.$row['o_id'].'">
                        <button type="button" class="btn btn-secondary"><i class="icon-print"></i> Imprimir</button>
                      </a>';
                    }
                  echo '</td>
                </tr>';
              }
            echo '</table>
          </div>';
          if($_REQUEST['proveedor']){
          }elseif($_REQUEST['fini'] == date('Y-m-d',strtotime('-60 days')) && $_REQUEST['ffin'] == date('Y-m-d')){
            echo '<div class="col-md-12 text-xs-center">
              <div class="mb-3">
                <nav aria-label="Page navigation">
                  <ul class="pagination">
                    <li class="page-item">
                      <a class="page-link" href="?modulo=ordenesc&accion=index&page='.($_GET['page']-1).'" aria-label="Previous">
                        <span aria-hidden="true">&laquo; Ant</span>
                        <span class="sr-only">Anterior</span>
                      </a>
                    </li>';
                    $numres = 50;
                    $nr = $this->model->resultt->num_rows;
                    $np = $nr/$numres;
                    $paginaa = $page-1;
                    $pagina = $page+1;
                    $sqlpg = 'SELECT COUNT(*) FROM ordenesc WHERE o_empresa = "'.$_SESSION['emp'].'"
                              AND DATE(o_fgen) BETWEEN "'.$fini.'" AND "'.$ffin.'"';
                    $resultpg = setq($sqlpg);
                    list($numg) =  $resultpg->fetch_array();
                    $pageres = ceil($numg/$numres);
                    //die($numg);
                    if($pageres>10){
                      if($_GET['page'] == 0) { $min = 1; $nombre = "Inicio";}
                      else {$min = $_GET['page']-1; $nombre = "Inicio...";}
                      if($min <= 1) $min = 1;

                      if($_GET['page'] == ($pageres-1)) $max = ($pageres-1);
                      else $max = $_GET['page']+3;
                      if($max >= ($pageres-1)) $max = ($pageres-1);

                      if($_GET['page'] == 0) $active = "active";
                      else $active = "";
                      echo '<li class="page-item '.$active.'">
                        <a class="page-link" href="?modulo=ordenesc&accion=index&page=0">'.$nombre.'</a>
                      </li>';
                    }elseif($pageres<6){
                      $max=$pageres;
                      $min=0;
                    }
                    //for($i=0;$i<$pageres;$i++){
                    for($i=$min;$i<$max;$i++){
                      if($_GET['page'] == $i) $active = "active";
                      else $active = "";
                      if($i == 0) $nombre = "Inicio";
                      else $nombre = $i;
                      echo '<li class="page-item '.$active.'">
                        <a class="page-link" href="?modulo=ordenesc&accion=index&page='.$i.'">'.$nombre.'</a>
                      </li>';
                    }
                    if($pageres<9){
                    }else{
                      if($_GET['page'] == ($pageres-5)) $nombre = ($pageres-2);
                      else $nombre = '... '.($pageres-2);
                      if($_GET['page'] == $i) $active = "active";
                      else $active = "";
                      if($_GET['page'] >= ($pageres-4)) echo '';
                      else 
                        echo '<li class="page-item '.$active.'">
                          <a class="page-link" href="?modulo=ordenesc&accion=index&page='.($pageres-2).'">'.$nombre.'</a>
                        </li>';
                      echo '<li class="page-item '.$active.'">
                        <a class="page-link" href="?modulo=ordenesc&accion=index&page='.($pageres-1).'">'.($pageres-1).'</a>
                      </li>';
                    }
                    echo '<li class="page-item">
                      <a class="page-link" href="?modulo=ordenesc&accion=index&page='.($_GET['page']+1).'" aria-label="Next">
                        <span aria-hidden="true">Sig &raquo;</span>
                        <span class="sr-only">Siguiente</span>
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>'; 
          }else{
          }
        echo'</div>
      </div>';
    }
    function show(){
      ?>
      <script>
        $(document).ready(function() {
          $('#producto').on('keyup', function() {
            var key = $(this).val();
            //var empresa = $('#empresa').val();
            var dataString = 'producto='+key;
            $.ajax({
              type: "POST",
              url: "query/suggestproducts.php",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  var cb = $(this).attr('value');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  $.ajax({
                    type: "POST",
                    url: "query/setprecio.php",
                    data: {'cb': cb, 'origen':'1'},
                    success: function(dataPRC) {
                      var costo = document.getElementById('costo');
                      if(dataPRC !== "" && dataPRC !== null){
                        costo.value = dataPRC;
                      }else{
                        costo.value = "";
                      }
                    }
                  });
                  $.ajax({
                    type: "POST",
                    url: "query/setlineasn.php",
                    data: {'cb': cb},
                    success: function(dataRTN) {
                      var ln = document.getElementById('linean');
                      if(dataRTN !== "" && dataRTN !== null){
                        ln.value = dataRTN;
                        $("#linean").prop("disabled","true");
                        ln.removeAttribute("style");
                        $("#toolln").prop("hidden","true");
                      }else{
                        ln.removeAttribute("disabled");
                        ln.value = "";
                        $("#linean").prop("style","border: solid 3px red");
                        document.getElementById("toolln").removeAttribute("hidden");
                      }
                    }
                  });
                  $("#cantidad").focus();
                  return false;
                });
              }
            });
          });
        });
      </script>
      <?php
      if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
      if ($_GET['alerta'] == 1) echo alert("El articulo no Existe", true);

      echo '<script>
        function delprod(idprod){
          var conf = confirm("¿Deseas borrar el producto de la orden de compra?");
          if(conf == true){
            window.location.href="?modulo=ordenesc&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
          }
        }
        function aplicamov(){
          var conf = confirm("Deseas aplicar esta orden de compra\n¿Deseas continuar?");
          if(conf == true){
            window.location.href="?modulo=ordenesc&accion=aplicar&id='.$this->model->id.'";
          }
        }
        function changeln(){
          var ln = document.getElementById("linean");
          ln.removeAttribute("style");
          $("#toolln").prop("hidden","true");
        }
      </script>';

          echo '<div class="row page-title-actions"><div class="col-12 col-md-12">';
            if($this->model->tablero){
              echo '<a href="?modulo=tableros&accion=show&id='.$this->model->tablero.'" acceskey="">
                <button type="button" class="btn btn-warning mb-1"><i class="fa fa-arrow-left"></i> Tablero </button>
              </a>
              <a href="?modulo=ordenesc&accion=index" accesskey="">
                <button type="button" class="btn bg-orange bg-darken-1 text-white mb-1"><i class="icon-mail-reply"></i> Listado Ordenes </button>
              </a>';
            }else{
              echo '<a href="?modulo=ordenesc&accion=index" accesskey="">
                <button type="button" class="btn btn-warning mb-1"><i class="fa fa-arrow-left"></i> Atrás </button>
              </a>';
            }
            if($this->model->estatus == "A")
              echo '<a target="_BLANK" href="formats/pdfordenc.php?id='.$this->model->id.'">
                <button type="button" class="btn btn-secondary mb-1"><i class="icon-print"></i> Imprimir</button>
              </a>
              <a data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-info mb-1">
                  <span class="glyphicon glyphicon-cog"></span><i class="icon-envelope-o"></i> Reenviar correo
                </button>
              </a>
              <a data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?ordenc='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-primary mb-1">
                  <span class="glyphicon glyphicon-cog"></span><i class="icon-cart-arrow-down"></i> Recibir
                </button>
              </a>';
            elseif($this->model->estatus == "N" || $this->model->estatus == "P"){
              echo '<a data-fancybox data-type="ajax" data-src="popup/setordenc.php?id='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-primary mb-1">
                  <span class="glyphicon glyphicon-cog"></span><i class="fa fa-pen"></i> Editar
                </button>
              </a>';
              echo '
              <script>
              function autorizar(){
                var a = confirm("¿Estás seguro de aplicar la orden de compra: '.$this->model->folio.'? Se creará una cuenta por pagar.");
                if(a){
                  window.location.href="?modulo=ordenesc&accion=aplicar&id='.$this->model->id.'"
                }
              }
              </script>
              ';
              if(busca($this->model->id,'ordenescd','od_ordenc','COUNT(*)') > 0){
              if($this->model->estatus == "N"){
                $direccion = 'data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;"';
              }elseif($this->model->estatus == "P"){
                $direccion = 'onclick="autorizar();"';
              }

              echo '<a '.$direccion.'>
                <button type="button" class="btn btn-success mb-1">
                  <span class="glyphicon glyphicon-cog"></span><i class="fa fa-check"></i> Aplicar
                </button>
              </a>';
              }
              if(busca($this->model->tablero,'remisiones','r_estatus = "A" AND r_tablero','COUNT(*)') > 0){
                echo '<a href="popup/importarprodsordenc.php?ordenc='.$this->model->id.'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                  <button type="button" class="btn btn-secondary mb-1"><i class="icon-download4"></i>Importar producto</button>
                </a>';
              }
              if($this->model->estatus == "P"){
                echo '
                <script>
                function denegar(){
                var a = confirm("¿Seguro que quieres denegar la orden de compra: '.$this->model->folio.'?");
                  if(a){
                    window.location.href="?modulo=ordenesc&accion=updatee&id='.$this->model->id.'";
                  }
                }
                </script>';
                echo '<button type="button" onclick="denegar();" class="btn btn-danger text-white"><i class="fa fa-times"></i> Denegar</button>';
              }
            }
          echo '</div>';

          if($this->model->estatus == "A"){
            //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
          }

          if($this->model->estatus == "N") $leyenda = 'Añadir productos a la Requisición'; //$leyenda = 'Añadir productos a la Orden de compra ';
          else  $leyenda = 'Lista de productos en la Orden de compra ';

          if($this->model->estatus == "N"){
            $hidden = "hidden";
            $col1 = "col-md-6 col-lg-5";
            $col2 = "col-md-6 col-lg-3";
            $col3 = "col-md-12 col-lg-2 text-xs-center";
            //$col = ""; 
          }else{
            $hidden = "";
            $col1 = "col-md-2 col-lg-3";
            $col2 = "col-md-2 col-lg-1";
            $col3 = "col-md-2 text-xs-center";
          }

        echo '</div>
      </div>';
      $readon = "";
      $exisantm = "";
      $miva = "";

      if($this->model->estatus == "N" || $this->model->estatus == "P"){
        echo '<div class="row">
          <form method="post" autocomplete="off" action="?modulo=ordenesc&accion=insertdetalle&id='.$this->model->id.'" name="ordencompra" onsubmit="checksubmit();">
            <input type="hidden" id="lineann" name="lineann" />
            <input type="hidden" id="importe" name="importe" />
            <div class="mt-1 col-12 col-md-12 alert alert-primary" data-toggle="tooltip" data-placement="top" title="'.$this->model->observaciones.'">
              '.$leyenda.'
                - 
              '.$this->model->folio.'
            </div>
            <div class="'.$col1.'">
              <div class="mb-5">
                <label for="agregar">Producto</label><br>
                <div class="input-group">
                  <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus >
                  <a class="input-group-addon" href="popup/productos-buscar.php?id='.$this->model->id.'&from=ordenesc" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                    <button accesskey="B" type="button" class="btn btn-primary btn-sm"><i class="fa fa-search5"></i></button>
                  </a>
                </div>
                <div id="suggestions"></div>
              </div>
            </div>
            <div class="'.$col2.'">
              <div class="mb-5">
                <label for="cant">Cantidad</label><br>
                <input type="number" min="0" value="1" onfocus="this.select()" max="9999999" step="0.01" name="cantidad" id="cantidad" placeholder="Cantidad" class="form-control" required="required" />
              </div>
            </div>
            <div class="col-md-4 col-lg-3" '.$hidden.'>
              <div class="mb-5">
                <label for="cant">Costo compra (Antes de IVA)</label><br>
                <input type="number" min="0" max="9999999" step="0.01" id="costo" name="importe" placeholder="Importe del concepto" class="form-control" required="required" />
              </div>
            </div>
            <div class="col-md-1 col-lg-1" '.$hidden.'>
              <div class="mb-5">
                <center>
                  <label for="cant">IVA</label><br>
                  <input type="checkbox" name="iva" '.$miva.' />
                <center>
              </div>
            </div>
            <div class="col-md-3 col-lg-2" '.$hidden.'>
              <div class="mb-5">
                <label for="linean" class">Linea de negocio: </label>
                <span id="toolln" hidden title="El articulo no se encuentra en tu inventario, por favor selecciona una linea de negocio para el articulo"><i class="icon-question-circle"></i></span><br>
                <select id="linean" name="ln" class="form-control" onchange="changeln();" required>
                  <option value="" disabled selected>Selecciona Linea de Negocios</option>';
                  $sql = 'SELECT * FROM lineas_negocio WHERE ln_estatus = "A" AND ln_empresa = "'.$_SESSION['emp'].'"';
                  $result = setq($sql);
                  while($row = $result->fetch_array()){
                    echo '<option value="'.$row['ln_id'].'">'.$row['ln_alias'].' - '.$row['ln_nmb'].'</option>';
                  }
                echo '</select>
              </div>
            </div>
            <div class="'.$col3.'">
              <div class="mb-5">
                <label></label><br>
                <button type="submit" class="btn btn-success"><i class="icon-android-send"></i> Agregar producto</button>
              </div>
            </div>
          </form>
        </div>';
      }else{
        echo '<div class="mt-1 col-8 col-md-8 alert alert-primary">
          '.$leyenda.' - '.$this->model->folio.'
        </div>';
        $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
      }

      echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
          <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
            <tr>
              <th width="30%">Artículo</th>
              <th>Piezas Solicitadas</th>
              <th '.$hidden.'>Precio unitario</th>
              <th '.$hidden.'>IVA</th>
              <th '.$hidden.'>Importe total</th>
              <th>Acciones</th>
            </tr>
          </thead>';
          $subtotal = 0;
          $totiva = 0;
          $poriva = busca(1,'configuracionesp','c_id','c_iva')/100;
          if($this->model->estatus == "A") $readonly = "disabled";
          while($row = $this->model->resultr->fetch_array()){
            $preciodb = $row['od_precio'];
            if($row['od_iva'] == "1"){
              $chiva = 'checked';
              //$precio = $row['od_precio']*(1+$poriva);
              /////////$precio = $row['od_precio'] / (1+$poriva);
              $precio = $row['od_precio'];
              $importe = $precio*$row['od_cantidad'];
              $iva = $importe*($poriva);
            }else{
              $chiva = "";
              $precio = $row['od_precio'];
              $importe = $precio*$row['od_cantidad'];
            }
            echo '<tr>
              <form method="post" action="?modulo=ordenesc&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['od_id'].'">
                <td>'.$row['od_nmbarticulo'].'</td>';
                echo '<td>
                  <input type="number" value="'.number_format($row['od_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>';
                if($this->model->estatus != "N")
                echo '
                <td class="number-align" >
                  <input type="hidden" name="preciodb" value="'.$preciodb.'"/>
                  <input type="hidden" name="precioshow" value="'.$importe.'" hidden>
                  <input type="number" value="'.number_format($importe,2,'.','').'" min="0" max="9999999" step="0.01" name="precio" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>
                <td ><input type="checkbox" name="iva" '.$chiva.' '.$readon.' onchange="submit();" /></td>
                <td >
                  <input type="number" value="'.number_format($importe,2,'.','').'" min="0" max="9999999" step="0.01" name="importe" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" readonly="readonly"  />
                </td>';
                if($this->model->estatus == "N" || $this->model->estatus == "P")
                  echo '<td>
                    <button type="submit" class="btn-sm btn-primary"><i class="fa fa-redo"></i> Actualizar</button>
                    <button type="button" class="btn-sm btn-danger" onclick="delprod('.$row['od_id'].')"><i class="fa fa-trash"></i> Borrar</button>';
                  echo '</td>
              </form>
            </tr>';
          }
          echo '<tfoot>';
            if($this->model->iva > 0 && $this->model->estatus == "P"){
              echo '<tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="2" class="number-align alert alert-primary h4">Subtotal</td>
                <td class="number-align alert alert-primary h4">'.number_format($this->model->subtotal,2).'</td>
              </tr>
              <tr>
                <td colspan="2" >&nbsp;</td>
                <td colspan="2"  class="number-align alert alert-info h4">IVA</td>
                <td  class="number-align alert alert-info h4">'.number_format($this->model->iva,2).'</td>
              </tr>';
            }
            if($this->model->estatus != "N")
            echo '<tr >
              <td colspan="2">&nbsp;</td>
              <td colspan="2"  class="number-align alert alert-primary h4">Total</td>
              <td  class="number-align alert alert-primary h4">'.number_format($this->model->total,2).'</td>
            </tr>';
          echo '</tfoot>';
        echo '</table>
      </div>';
    }

    function showoc(){
      ?>
      <script>
        $(document).ready(function() {
          $('#producto').on('keyup', function() {
            var key = $(this).val();
            //var empresa = $('#empresa').val();
            var dataString = 'producto='+key;
            $.ajax({
              type: "POST",
              url: "query/suggestproducts.php",
              data: dataString,
              success: function(data) {
                //Escribimos las sugerencias que nos manda la consulta
                $('#suggestions').fadeIn(1000).html(data);
                //Al hacer click en alguna de las sugerencias
                $('.suggest-element').on('click', function(){
                  //Obtenemos la id unica de la sugerencia pulsada
                  var id = $(this).attr('id');
                  var cb = $(this).attr('value');
                  //Editamos el valor del input con data de la sugerencia pulsada
                  $('#producto').val($('#'+id).attr('data'));
                  //Hacemos desaparecer el resto de sugerencias
                  $('#suggestions').fadeOut(1000);
                  $.ajax({
                    type: "POST",
                    url: "query/setprecio.php",
                    data: {'cb': cb, 'origen':'1'},
                    success: function(dataPRC) {
                      var costo = document.getElementById('costo');
                      if(dataPRC !== "" && dataPRC !== null){
                        costo.value = dataPRC;
                      }else{
                        costo.value = "";
                      }
                    }
                  });
                  $.ajax({
                    type: "POST",
                    url: "query/setlineasn.php",
                    data: {'cb': cb},
                    success: function(dataRTN) {
                      var ln = document.getElementById('linean');
                      if(dataRTN !== "" && dataRTN !== null){
                        ln.value = dataRTN;
                        $("#linean").prop("disabled","true");
                        ln.removeAttribute("style");
                        $("#toolln").prop("hidden","true");
                      }else{
                        ln.removeAttribute("disabled");
                        ln.value = "";
                        $("#linean").prop("style","border: solid 3px red");
                        document.getElementById("toolln").removeAttribute("hidden");
                      }
                    }
                  });
                  $("#cantidad").focus();
                  return false;
                });
              }
            });
          });
        });
      </script>
      <?php
      if (!isset($_GET['alerta'])) $_GET['alerta'] = NULL;
      if ($_GET['alerta'] == 1) echo alert("El articulo no Existe", true);

      echo '<script>
        function delprod(idprod){
          var conf = confirm("¿Deseas borrar el producto de la orden de compra?");
          if(conf == true){
            window.location.href="?modulo=ordenesc&accion=delprod&id='.$this->model->id.'&idprod=" + idprod;
          }
        }
        function aplicamov(){
          var conf = confirm("Deseas aplicar esta orden de compra\n¿Deseas continuar?");
          if(conf == true){
            window.location.href="?modulo=ordenesc&accion=aplicar&id='.$this->model->id.'";
          }
        }
        function changeln(){
          var ln = document.getElementById("linean");
          ln.removeAttribute("style");
          $("#toolln").prop("hidden","true");
        }
      </script>';

          echo '<div class="row page-title-actions"><div class="col-12 col-md-12">';
            if($this->model->tablero){
              echo '<a href="?modulo=tableros&accion=show&id='.$this->model->tablero.'" acceskey="">
                <button type="button" class="btn btn-warning ml-1"><i class="fa fa-arrow-left"></i> Tablero </button>
              </a>
              <a href="?modulo=ordenesc&accion=index" accesskey="">
                <button type="button" class="btn bg-orange bg-darken-1 text-white"><i class="icon-mail-reply"></i> Listado Ordenes </button>
              </a>';
            }else{
              echo '<a href="?modulo=ordenesc&accion=index" accesskey="">
                <button type="button" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Atrás </button>
              </a>';
            }
            if($this->model->estatus == "A")
              echo '<a target="_BLANK" href="formats/pdfordenc.php?id='.$this->model->id.'">
                <button type="button" class="btn btn-secondary"><i class="icon-print"></i> Imprimir</button>
              </a>
              <a data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-info">
                  <span class="glyphicon glyphicon-cog"></span><i class="icon-envelope-o"></i> Reenviar correo
                </button>
              </a>
              <a data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?ordenc='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-primary">
                  <span class="glyphicon glyphicon-cog"></span><i class="icon-cart-arrow-down"></i> Recibir
                </button>
              </a>';
            elseif($this->model->estatus == "N" || $this->model->estatus == "P"){
              echo '<a data-fancybox data-type="ajax" data-src="popup/setordenc.php?id='.$this->model->id.'" href="javascript:;">
                <button type="button" class="btn btn-primary">
                  <span class="glyphicon glyphicon-cog"></span><i class="fa fa-pen"></i> Editar
                </button>
              </a>';
              echo '
              <script>
              function autorizar(){
                var a = confirm("¿Estás seguro de aplicar la orden de compra: '.$this->model->folio.'? Se creará una cuenta por pagar.");
                if(a){
                  window.location.href="?modulo=ordenesc&accion=aplicar&id='.$this->model->id.'"
                }
              }
              </script>
              ';
              if(busca($this->model->id,'ordenescd','od_ordenc','COUNT(*)') > 0){
              if($this->model->estatus == "N"){
                $direccion = 'data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;"';
              }elseif($this->model->estatus == "P"){
                $direccion = 'onclick="autorizar();"';
              }
              $direccion = 'data-fancybox data-type="ajax" data-src="popup/aplicaordenc.php?id='.$this->model->id.'" href="javascript:;"';
              echo '<a '.$direccion.'>
                <button type="button" class="btn btn-success">
                  <span class="glyphicon glyphicon-cog"></span><i class="fa fa-check"></i> Aplicar
                </button>
              </a>';
              }
              if(busca($this->model->tablero,'remisiones','r_estatus = "A" AND r_tablero','COUNT(*)') > 0){
                echo '<a href="popup/importarprodsordenc.php?ordenc='.$this->model->id.'" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                  <button type="button" class="btn btn-secondary"><i class="icon-download4"></i>Importar producto</button>
                </a>';
              }
              if($this->model->estatus == "P"){
                echo '
                <script>
                function denegar(){
                var a = confirm("¿Seguro que quieres denegar la orden de compra: '.$this->model->folio.'?");
                  if(a){
                    window.location.href="?modulo=ordenesc&accion=updatee&id='.$this->model->id.'";
                  }
                }
                </script>';
                echo '<button type="button" onclick="denegar();" class="btn btn-danger text-white"><i class="fa fa-times"></i> Denegar</button>';
              }
            }
          echo '</div>';

          if($this->model->estatus == "A"){
            //echo '<a href="imprimirmovimiento.php?movimiento='.$_GET['id'].'&tipo='.$_GET['tipo'].'" target="_BLANK" title="Reimprimir pedido" ><input type="button" value=" " id="imprimir" class="botonb"></a>';
          }

          if($this->model->estatus == "N") $leyenda = 'Añadir productos a la Requisición'; //$leyenda = 'Añadir productos a la Orden de compra ';
          else  $leyenda = 'Lista de productos en la Orden de compra ';

          if($this->model->estatus == "N"){
            $hidden = "hidden";
            $col1 = "col-md-6 col-lg-5";
            $col2 = "col-md-6 col-lg-3";
            $col3 = "col-md-12 col-lg-2 text-xs-center";
            //$col = ""; 
          }else{
            $hidden = "";
            $col1 = "col-md-2 col-lg-3";
            $col2 = "col-md-2 col-lg-1";
            $col3 = "col-md-2 text-xs-center";
          }

        echo '</div>
      </div>';
      $readon = "";
      $exisantm = "";
      $miva = "";

      if($this->model->estatus == "N" || $this->model->estatus == "P"){
        echo '<div class="row">
          <form method="post" autocomplete="off" action="?modulo=ordenesc&accion=insertdetalle&id='.$this->model->id.'" name="ordencompra" onsubmit="checksubmit();">
            <input type="hidden" id="lineann" name="lineann" />
            <input type="hidden" id="importe" name="importe" />
            <div class="mt-1 col-12 col-md-12 alert alert-primary" data-toggle="tooltip" data-placement="top" title="'.$this->model->observaciones.'">
              '.$leyenda.'
                - 
              '.$this->model->folio.'
            </div>
            <div class="'.$col1.'">
              <div class="mb-5">
                <label for="agregar">Producto</label><br>
                <div class="input-group">
                  <input type="text" name="producto" id="producto" placeholder="Escribe un fragmento de tu producto" class="search_query form-control" required="required"  autofocus >
                  <a class="input-group-addon" href="popup/productos-buscar.php?id='.$this->model->id.'&from=ordenesc" onclick="window.open(this.href,\'window\',\'width=870, height=650\');return false">
                    <button accesskey="B" type="button" class="btn btn-primary btn-sm"><i class="fa fa-search5"></i></button>
                  </a>
                </div>
                <div id="suggestions"></div>
              </div>
            </div>
            <div class="'.$col2.'">
              <div class="mb-5">
                <label for="cant">Cantidad</label><br>
                <input type="number" min="0" value="1" onfocus="this.select()" max="9999999" step="0.01" name="cantidad" id="cantidad" placeholder="Cantidad" class="form-control" required="required" />
              </div>
            </div>
            <div class="col-md-4 col-lg-3" '.$hidden.'>
              <div class="mb-5">
                <label for="cant">Costo compra (Antes de IVA)</label><br>
                <input type="number" min="0" max="9999999" step="0.01" id="costo" name="importe" placeholder="Importe del concepto" class="form-control" required="required" />
              </div>
            </div>
            <div class="col-md-1 col-lg-1" '.$hidden.'>
              <div class="mb-5">
                <center>
                  <label for="cant">IVA</label><br>
                  <input type="checkbox" name="iva" '.$miva.' />
                <center>
              </div>
            </div>
            <div class="col-md-3 col-lg-2" '.$hidden.'>
              <div class="mb-5">
                <label for="linean" class">Linea de negocio: </label>
                <span id="toolln" hidden title="El articulo no se encuentra en tu inventario, por favor selecciona una linea de negocio para el articulo"><i class="icon-question-circle"></i></span><br>
                <select id="linean" name="ln" class="form-control" onchange="changeln();" required>
                  <option value="" disabled selected>Selecciona Linea de Negocios</option>';
                  $sql = 'SELECT * FROM lineas_negocio WHERE ln_estatus = "A" AND ln_empresa = "'.$_SESSION['emp'].'"';
                  $result = setq($sql);
                  while($row = $result->fetch_array()){
                    echo '<option value="'.$row['ln_id'].'">'.$row['ln_alias'].' - '.$row['ln_nmb'].'</option>';
                  }
                echo '</select>
              </div>
            </div>
            <div class="'.$col3.'">
              <div class="mb-5">
                <label></label><br>
                <button type="submit" class="btn btn-success"><i class="icon-android-send"></i> Agregar producto</button>
              </div>
            </div>
          </form>
        </div>';
      }else{
        echo '<div class="mt-1 col-8 col-md-8 alert alert-primary">
          '.$leyenda.' - '.$this->model->folio.'
        </div>';
        $readon = 'readonly="readonly"  onclick="javascript: return false;" ';
      }

      echo '<div class="table table-responsive table-hover">
        <table class="table table-hover table-striped">
          <thead class="thead bg-blue bg-darken-3 text-white pt-1 pb-1">
            <tr>
              <th width="30%">Artículo</th>
              <th>Piezas Solicitadas</th>
              <th '.$hidden.'>Precio unitario</th>
              <th '.$hidden.'>+IVA</th>
              <th '.$hidden.'>Importe total</th>
              <th>Acciones</th>
            </tr>
          </thead>';
          $subtotal = 0;
          $totiva = 0;
          $iva = 0;
          $poriva = busca(1,'configuracionesp','c_id','c_iva')/100;
          if($this->model->estatus == "A") $readonly = "disabled";
          while($row = $this->model->resultr->fetch_array()){
            $preciodb = $row['od_precio'];
            if($row['od_iva'] == "1"){
              $chiva = 'checked';
              //$precio = $row['od_precio']*(1+$poriva);
              /////////$precio = $row['od_precio'] / (1+$poriva);
              $precio = $row['od_precio'];
              $importe = $precio*$row['od_cantidad'];
              $iva += $importe*($poriva);
            }else{
              $chiva = "";
              $precio = $row['od_precio'];
              $importe = $precio*$row['od_cantidad'];
            }
            
            echo '<tr>
              <form method="post" action="?modulo=ordenesc&accion=updatedetalle&id='.$this->model->id.'&idprod='.$row['od_id'].'">
                <td>'.$row['od_nmbarticulo'].'</td>';
                echo '<td>
                  <input type="number" value="'.number_format($row['od_cantidad'],2,'.','').'" min="0" max="9999999" step="0.01" name="cantidad" placeholder="Cantidad" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>';
                if($this->model->estatus != "N")
                echo '
                <td class="number-align" >
                  <input type="hidden" name="preciodb" value="'.$preciodb.'"/>
                  <input type="hidden" name="precioshow" value="'.$importe.'" hidden>
                  <input type="number" value="'.number_format($precio,2,'.','').'" min="0" max="9999999" step="0.01" name="precio" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" '.$readon.' />
                </td>
                <td ><input type="checkbox" name="iva" '.$chiva.' '.$readon.' onchange="submit();" /></td>
                <td >
                  <input type="number" value="'.number_format($importe,2,'.','').'" min="0" max="9999999" step="0.01" name="importe" placeholder="Importe total" class="form-control number-align" required="required" onfocus="this.select();" readonly="readonly"  />
                </td>';
                if($this->model->estatus == "N" || $this->model->estatus == "P")
                  echo '<td>
                    <button type="submit" class="btn-sm btn-primary"><i class="fa fa-redo"></i> Actualizar</button>
                    <button type="button" class="btn-sm btn-danger" onclick="delprod('.$row['od_id'].')"><i class="fa fa-trash"></i> Borrar</button>';
                  echo '</td>
              </form>
            </tr>';
          }
          echo '<tfoot>';
            if($this->model->diva == "1"){
              echo '<tr>
                <td colspan="2">&nbsp;</td>
                <td colspan="2" class="number-align alert alert-primary h4">Subtotal</td>
                <td class="number-align alert alert-primary h4">$'.number_format($this->model->subtotal,2).'</td>
              </tr>
              <tr>
                <td colspan="2" >&nbsp;</td>
                <td colspan="2"  class="number-align alert alert-info h4">IVA</td>
                <td  class="number-align alert alert-info h4">$'.number_format($this->model->iva,2).'</td>
              </tr>';
            }
            if($this->model->estatus != "N")
            echo '<tr >
              <td colspan="2">&nbsp;</td>
              <td colspan="2"  class="number-align alert alert-primary h4">Total</td>
              <td  class="number-align alert alert-primary h4">$'.number_format($this->model->total,2).'</td>
            </tr>';
          echo '</tfoot>';
        echo '</table>
      </div>';
    }
  }*/
?>