<?php
  /* ini_set('display_errors', 1); */
  class prospectos{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelprospectos(isset($obj));
    }
    function index(){      
      if(!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;
      if(!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-20 days'));
      if(!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d',strtotime('last day of this month'));
      if(!isset($_REQUEST['hini'])) $_REQUEST['hini'] = NULL;
      if(!isset($_REQUEST['hfin'])) $_REQUEST['hfin'] = NULL;
      if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;   
      
      $this->model->result(trim($_REQUEST['nmb']),$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['hini'],$_REQUEST['hfin'],$_REQUEST['page']);
      $this->view = new viewprospectos($this->model);
      $this->view->browse($_REQUEST['nmb'],$_REQUEST['estatus'], $_REQUEST['fini'], $_REQUEST['ffin'],$_REQUEST['hini'],$_REQUEST['hfin'],$_REQUEST['page']);
    }
    function insert(){
      $fini = explode("T", $_POST['fini']);
      $estado = $_POST['estado']; // recuperar el estado del formulario

      $this->model->setdata(
        "NULL",
        $_POST['ugen'],
        $fini[0],
        $fini[1],
        "NULL",
        "A",
        $_POST['observacion'],
        $estado // pasar estado
      );

      $this->model->insert();
      $this->insertorden();

      redirect('?modulo=prospectos&accion=show&id='.$this->model->idt);
    }

    function show(){
      $this->model->resultlead($_GET['id']);
      $this->view = new viewprospectos($this->model);
      $this->view->show();
      /* $this->view->show($_REQUEST['nmb'], $_REQUEST['hini'], $_REQUEST['hfin'], $_REQUEST['page']); */
    }
    function insertlead(){
      $ncaptura = getmax("cl_ncaptura", "crm_leads WHERE cl_fasigna = '".date("Y-m-d")."'", false, true); //Buscamos el número consecutivo diario de registros
      $pvendedor = busca($_POST['telefono'], "crm_leads", "cl_telefono", "cl_vendedor"); //Buscamos si hay un registro previo del prospecto
      $nvendedores = getmax("uo_orden", "usuarios_orden", false, false); //Consultamos el total de usuarios asignados para ventas
      $usuariosventas = array();
      $ocupados = array();
      $sqlu = "SELECT * FROM usuarios_orden WHERE uo_estatus = 'A' ORDER BY uo_orden";
      if($_SESSION['uid'] == "ADMIN") setq($sqlu,true);
      $resultu = setq($sqlu);
      $activos = $resultu->num_rows;
      if($activos == 0){
        echo '
        <script>
          alert("No hay vendedores activos. No fue posible asignar los prospectos.");
        </script>
        ';
      } else{
      while ($rowu = $resultu->fetch_array()){
        array_push($usuariosventas, $rowu['uo_orden']);
      }

      $sqluo = "SELECT uo_orden FROM usuarios_orden INNER JOIN crm_leads  ON cl_vendedor = uo_uid WHERE cl_estatus = 'P' AND cl_ucaptura = '".$_SESSION['uid']."' AND uo_estatus = 'A'";
      $resultuo = setq($sqluo);
      $totalu = $resultuo->num_rows;
      while ($rowuo = $resultuo->fetch_array()){
        array_push($ocupados, $rowuo['uo_orden']);
      }

      $disponibles = array_merge(array_diff($usuariosventas, $ocupados), array_diff($usuariosventas, $ocupados));
      $ultimovendedor = busca($disponibles[0], "usuarios_orden", "uo_estatus = 'A' AND uo_orden", "uo_uid"); //Consultamos el orden del ultimo registro de asignación

      //Comrobamos que el número de vendedores no sea mayor al orden
      if($totalu == $nvendedores){
        //Si el número de asignación llegó al límite de vendedores asignados volvemos a comenzar desde el primer vendedor activo

        $vendedor = busca('A', 'usuarios_orden', 'uo_estatus', 'MIN(uo_orden) AS uo_orden');
        /* $vendedor = busca("1", "usuarios_orden", "uo_orden", "uo_uid"); */
        
      } else{
        //Si el número de asignación no ha llegado al límite de vendedores asignados
        $vendedor = $ultimovendedor;
      }    

      //Si el prospecto ya tuvo un registro previo se le asigna el asesor que lo atendió en esa ocasión
      if (!empty($pvendedor)) {
        $vendedor = $pvendedor;
      }
    
                                                                                                                                                    
      $this->model->setdatalead("",$_POST['lead'],$_POST['nmb'],$_POST['cp'],$_POST['correo'],$_POST['telefono'],$_POST['code'],$_POST['pais'],"P",$_POST['observacion'],$vendedor,$ncaptura,$_SESSION['uid'],"NULL",date("Y-m-d"));
      $this->model->insertlead();
    }
      $link = '?modulo=prospectos&accion=show&id='.$_POST['lead'];
      redirect($link);
    }
    function finalizar(){
      $this->model->finalizarhoja($_GET['id']);

      redirect('?modulo=prospectos&accion=index');
    }

    function finalizaryasignar(){
      $this->model->finalizaryasignar($_GET['id']);

      redirect('?modulo=prospectos&accion=index');
    }

    /* function fcaptura(){
      $ncaptutados = $this->model->finalizarcaptura($_GET['id']);
      if($ncaptutados == 1){
        $titulo = 'NUEVO LEAD CAPTURADO';
      } else{
        $titulo = 'NUEVOS LEADS CAPTURADOS';
      } 
      $grupos = '"ADMIN","GERENCIA"';
      echo "
      <script>
      $.ajax({
        url: 'task/notificacionpushgrupos.php',
        method: 'POST',
        data: {
                'grupos': '".$grupos."',
                'ncapturados': '".$ncaptutados."',
                'tipo': 'FCAPTURA',
                'titulo': '".$titulo."'},
      }).done(function(data){
        window.location.href = '?modulo=prospectos&accion=show&id=".$_GET['id']."';
      });  
      </script>";
      / redirect("?modulo=prospectos&accion=show&id=".$_GET['id']);
    } */

    function fcaptura(){
      $ncapturados = $this->model->finalizarcaptura($_GET['id']);
      if(count($ncapturados) == 1){
        $titulo = 'NUEVO LEAD CAPTURADO';
      } else{
        $titulo = 'NUEVOS LEADS CAPTURADOS';
      } 
      /* $grupos = '"ADMIN","GERENCIA"'; */
      $grupos = '"GERENCIA"';
      echo "
      <script>
      $.ajax({
        url: 'task/notificacionpushgrupos.php',
        method: 'POST',
        data: {
                'grupos': '".$grupos."',
                'ncapturados': '".count($ncapturados)."',
                'tipo': 'FCAPTURA',
                'titulo': '".$titulo."'},
      }).done(function(data){
        //window.location.href = '?modulo=prospectos&accion=show&id=".$_GET['id']."';
      });  
      </script>";

      // Inicializamos un arreglo para contar las repeticiones
      $conteo = array();

      // Recorremos el arreglo y contamos las repeticiones
      foreach ($ncapturados as $vendedor) {
          if (isset($conteo[$vendedor])) {
              $conteo[$vendedor]++;
          } else {
              $conteo[$vendedor] = 1;
          }
      }

      // Enviamos todas las notificaciones PUSH
      foreach ($conteo as $vendedor => $cantidad) {
        $grupos = '"ADMIN","GERENCIA","VENTAS"';
        if($cantidad == 1){
          $titulo = $cantidad.' lead capturado';
          $cuerpo = 'Se te ha asignado '.$cantidad.' nuevo lead';
        } else{
          $titulo = $cantidad.' leads capturados';
          $cuerpo = 'Se te han asignado '.$cantidad.' nuevos leads';
        }

        echo "
        <script>
        $.ajax({
          url: 'task/notificacionpushleads.php',
          method: 'POST',
          data: {
                  'grupos': '".$grupos."',
                  'vendedor': '".$vendedor."',
                  'nleads': '".$cantidad."',
                  'titulo': '".$titulo."',
                  'cuerpo': '".$cuerpo."'},
        }).done(function(data){
          //window.location.href = '?modulo=prospectos&accion=show&id=".$_GET['id']."';
        });  
        </script>";
      }
      echo "
        <script>
          window.location.href = '?modulo=prospectos&accion=show&id=".$_GET['id']."';
        </script>";
    }

    function insertorden(){
      $sqlu = 'SELECT * FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A"';
      $resultu = setq($sqlu);
      /* $cantidad = $_POST['cantidad']; */
      while($row = $resultu->fetch_array()){
        if(isset($_POST['check'.$row['u_nuser']])){
          $this->model->insertorden($_POST['check'.$row['u_nuser']], 'A');
        } else{
          $this->model->insertorden($row['u_id'], 'I');
        }
      }

      if(!isset($_POST['fini'])){
        redirect('?modulo=prospectos&accion=show&id='.$_POST['id']);
      }
    }
  }

  class modelprospectos{
    function select($id){
      $sql = 'SELECT * FROM crm_capturaleads WHERE cc_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['cc_id'];
      $this->ugen = $row['cc_ugen'];
      $this->fini = $row['cc_fini'];
      $this->hini = $row['cc_hini'];
      $this->hfin = $row['cc_hfin'];      
      $this->estatus = $row['cc_estatus'];
      $this->observacion = $row['cc_observacion'];
    }
    function result($ugen,$estatus,$fini,$ffin,$hini,$hfin,$page){ //filtro 
      $bloque = 50;
      $sql = 'SELECT * FROM crm_capturaleads WHERE cc_fini BETWEEN "'.$fini.'" AND "'.$ffin.'"';      
      if($hini) $sql.=' AND cc_hini >= "'.$hini.'"';

      if($hfin) $sql.=' AND cc_hfin <= "'.$hfin.'"';

      if($estatus) $sql.=' AND cc_estatus = "'.$estatus.'"';

      if($ugen) $sql.= ' AND (cc_ugen LIKE "%'.$ugen.'%")';
      $sql.=' ORDER BY cc_id DESC LIMIT '.($bloque*$page).','.$bloque;
      //$result = setq($sql);
      //if($result->num_rows > $bloque) $sql.= ' LIMIT '.($bloque*$page).','.$bloque;
      $this->result = setq($sql);
      $this->resultt = setq($sql);
    }
    function setdata($id, $ugen, $fini, $hini, $hfin, $estatus, $observacion, $estado){
      mb_internal_encoding("UTF-8");
      $this->id = $id;
      $this->ugen = clearvmayus($ugen);
      $this->fini = $fini;
      $this->hini = $hini;
      $this->hfin = $hfin;
      $this->estatus = clearvmayus($estatus);
      $this->observacion = clearvmayus($observacion);
      $this->estado = clearvmayus($estado); // nuevo atributo
    }

    function insert(){
      $sql = 'INSERT INTO crm_capturaleads SET
              cc_fini = "'.$this->fini.'",
              cc_ugen = "'.$this->ugen.'",
              cc_hini = "'.$this->hini.'",
              cc_hfin = '.$this->hfin.',
              cc_estatus = "'.$this->estatus.'",
              cc_observacion = "'.$this->observacion.'",
              cc_estado = "'.$this->estado.'"'; // nuevo campo
      setq($sql);

      $this->idt = getmax('cc_id','crm_capturaleads',false,false);
    }

    function setdatalead($id,$lead,$nmb,$cp,$correo,$telefono,$code,$pais,$estatus,$observacion,$vendedor,$ncaptura,$ucaptura,$hasigna,$fechaasigna,$consecutivo){
      $this->id = $id;
      $this->lead = $lead;
      $this->nmb = clearvmayus($nmb);
      $this->cp = $cp;
      $this->correo = $correo;
      $this->telefono = $telefono;
      $this->code = $code;
      $this->pais = $pais;
      $this->estatus = clearvmayus($estatus);
      $this->observacion = clearvmayus($observacion);
      $this->vendedor = $vendedor;
      $this->ncaptura = $ncaptura;
      $this->ucaptura = clearvmayus($ucaptura);
      $this->hasigna = $hasigna;
      $this->fasigna = date("Y-m-d");
      $this->consecutivo = $consecutivo;
    }
    function insertlead(){
      $sqlf = '';
      if(!empty($this->id)){
        $sqlf = ' cl_id = "'.$this->id.'", ';
      }
      $sql = 'INSERT INTO crm_leads SET'.$sqlf.'
              cl_lead = "'.$this->lead.'",
              cl_nmb = "'.$this->nmb.'",
              cl_cp = "'.$this->cp.'",
              cl_correo = "'.$this->correo.'",
              cl_telefono = "'.$this->telefono.'" ,
              cl_code = "'.$this->code.'" ,
              cl_pais = "'.$this->pais.'" ,
              cl_estatus = "'.$this->estatus.'",
              cl_observacion = "'.$this->observacion.'",
              cl_vendedor = "'.$this->vendedor.'",
              cl_ncaptura = "'.$this->ncaptura.'",
              cl_ucaptura = "'.$this->ucaptura.'",
              cl_hasigna = NULL,
              cl_consecutivo = "'.$this->consecutivo.'",
              cl_fasigna = "'.$this->fasigna.'"';
      $result = setq($sql);
      if ($result !== false) {
        $respuesta = 1; //La consulta fue exitosa
      } else{
        $respuesta = 0; //La consulta NO fue exitosa
      }
    
      return($respuesta);
    }
    
    function updatelead(){
      $sql = 'UPDATE crm_leads SET
              cl_lead = "'.$this->lead.'",         
              cl_nmb = "'.$this->nmb.'",
              cl_telefono = "'.$this->telefono.'" ,
              cl_code = "'.$this->code.'" ,
              cl_pais = "'.$this->pais.'" ,
              cl_correo = "'.$this->correo.'",
              cl_estatus = "'.$this->estatus.'",
              cl_observacion = "'.$this->observacion.'",
              cl_vendedor = "'.$this->vendedor.'",
              cl_ncaptura = "'.$this->ncaptura.'",
              cl_ucaptura = "'.$this->ucaptura.'",
              cl_hasigna = "'.$this->hasigna.'",
              cl_fasigna = "'.$this->fasigna.'"
              WHERE cl_id = "'.$this->id.'"';
      setq($sql);
    }

    function resultlead($id){
      /* $sql = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_fasigna = "'.date("Y-m-d").'" ORDER BY cl_estatus = "P" DESC, cl_id DESC'; */
      $sql = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_estatus != "R" ORDER BY cl_estatus = "P" DESC, cl_id DESC';

      $this->result = setq($sql);

      /* $sql = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_fasigna = "'.date("Y-m-d").'" AND cl_estatus = "P" ORDER BY cl_id DESC'; */
      $sql0 = 'SELECT * FROM crm_leads WHERE cl_estatus = "P" AND cl_fasigna = "'.date("Y-m-d").'" AND cl_lead = "'.$id.'" ORDER BY cl_id DESC';
      $this->resultt = setq($sql0);
    }

    function finalizarhoja($id){
      $sql = 'UPDATE crm_capturaleads SET cc_estatus= "F", cc_hfin = "'.date("H:i:s").'", cc_ucancela = "'.$_SESSION['uid'].'" WHERE cc_id = "'.$id.'"';
      setq($sql);

      /* $sql = 'UPDATE crm_capturaleads SET cc_estatus= "F", cc_hfin = "'.date("H:i:s").'", cc_ucancela = "'.$_SESSION['uid'].'" WHERE cc_id = "'.$id.'"'; */
      $sql2 = 'DELETE FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_estatus = "P"';
      setq($sql2);
      /* setq($sql); */
    }

    function finalizaryasignar($id){
      $sql = 'UPDATE crm_capturaleads SET cc_estatus= "F", cc_hfin = "'.date("H:i:s").'", cc_ucancela = "'.$_SESSION['uid'].'" WHERE cc_id = "'.$id.'"';
      setq($sql);

      $sql2 = 'SELECT cl_id FROM crm_leads WHERE cl_estatus = "P" AND cl_lead = "'.$id.'" AND cl_fasigna = "'.date("Y-m-d").'"';
      $result = setq($sql2);
      while($row = $result->fetch_array()){
        $sql3 = 'UPDATE crm_leads SET cl_estatus = "A", cl_hasigna = "'.date("H:i:s").'" WHERE cl_lead = "'.$id.'" AND cl_id = "'.$row['cl_id'].'"';
        setq($sql3);
      }

      
    }

  function finalizarcaptura($id){
    $sql0 = 'SELECT cl_id, cl_vendedor, cl_code, cl_pais, cl_telefono FROM crm_leads WHERE cl_estatus = "P"';
    $result0 = setq($sql0);
    $cantvend = array();
    $i = 0;
    $hora = date("H:i:s");
    $fecha = date("Y-m-d");
    while($row0 = $result0->fetch_array()){
      $sql1 = 'SELECT cl_id FROM crm_leads WHERE cl_estatus IN ("P", "A", "X") AND cl_code = "'.$row0['cl_code'].'" AND cl_pais = "'.$row0['cl_pais'].'" AND cl_telefono = "'.$row0['cl_telefono'].'"';
      $result1 = setq($sql1);
      while($row1 = $result1->fetch_array()){
        $sqlh = 'UPDATE historial_leadsasignacion SET
                hl_lead = "'.$row1['cl_id'].'" WHERE hl_lead = "'.$row1['cl_id'].'"';
        setq($sqlh);
      }

      $sqldel = "DELETE FROM crm_leads WHERE cl_code = '".$row0['cl_code']."' AND cl_pais = '".$row0['cl_pais']."' AND cl_telefono = '".$row0['cl_telefono']."' AND cl_estatus IN ('A', 'X', 'R')";
      setq($sqldel);

      $sql = 'UPDATE crm_leads SET cl_estatus= "A", cl_hasigna = "'.$hora.'" WHERE cl_id = "'.$row0['cl_id'].'"';
      $cantvend[$i] = $row0['cl_vendedor'];
      setq($sql);
      $id = $row0['cl_id'];
      $vendedor = $row0['cl_vendedor'];
      inserthistorial($id, $hora, $fecha, $vendedor);
      $i++;
    }
    return $cantvend;
  }

  function insertorden($id, $estatus){
    $sql = 'UPDATE usuarios_orden
    SET uo_estatus = "'.$estatus.'"
    WHERE uo_uid = "'.$id.'"
    ';
    setq($sql);
  }

  }

  class viewprospectos{
    var $model;
    function __construct($model){
      ?>
<script>
function checkguardar() {
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
        $this->estatust = array("N"=>"Abierto","P"=>"Construcción","G"=>"Negociación","L"=>"Aplazado","A"=>"Vendido","F"=>"Finalizado","C"=>"Cancelado","O"=>"En Linea","X"=>"Perdido","W"=>"Ganados");
        $this->colestatust = array("N"=>"bg-yellow bg-accent-1","P"=>"bg-orange bg-accent-2","G"=>"bg-indigo bg-accent-3","L"=>"bg-amber bg-darken-4","A"=>"bg-success bg-accent-3","C"=>"bg-red bg-accent-4","F"=>"bg-blue bg-darken-2","X"=>"bg-red bg-darken-2");
    }
    function browse($ugen,$estatus,$fini,$ffin,$hini,$hfin,$page){
        $nuevo = '
        <button id="nuevo" type="button" class="btn btn-sm btn-primary">
          <i class="fa fa-plus"></i> Nueva hoja
        </button>';

        $hoja = busca(date("Y-m-d"), "crm_capturaleads", "cc_estatus ='A' AND cc_fini", "cc_id");

        if($hoja != ""){
          $hojaactual = "";
        } else{
          $hojaactual = "";
        }

        $filtro = '
        <form autocomplete="off" action="?modulo=prospectos&accion=index" method="post" id="filtro" class="">
          <div class="mb-5">
            <label class="mr-sm-2">Registra:</label>
            <select name="nmb" id="nmb" class="form-control">
              <option value="">TODOS</option>';
              //menu_select_db('usuarios','u_id','u_id',$agente,'agente','u_empresa = "'.$_SESSION['emp'].'" AND u_estatus = "A"',false,false,true,false,"Todos")
              $sql = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_empresa = "'.$_SESSION['emp'].'"';
              $result = setq($sql);
              while($row = $result->fetch_array()){
                if($row['u_id'] == $ugen) $agenteselect = 'selected';
                else $agenteselect = '';

                $filtro.= '<option value="'.$row['u_id'].'" '.$agenteselect.'>'.$row['u_id'].'</option>';
                
              }

              $filtro.= '
            </select>
          </div>
          <div class="mb-5" hidden>
            <label class="mr-sm-2">Page:</label>
            <div class="position-relative has-icon-left">
              <input type="text" id="page" name="page" class="form-control" value="" required>
            </div>
          </div>
          <div class="mb-5">
            <label class="mr-sm-2">Fecha desde:</label>
            <div class="position-relative has-icon-left">
              <input type="date" id="fini" name="fini" class="form-control" value="'.$fini.'" required>
              <input hidden type="date" id="fini2" class="form-control" value="'.date('Y-m-d',strtotime('-20 days')).'">
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-5">
            <label class="mr-sm-2">Fecha hasta:</label>
            <div class="position-relative has-icon-left">
              <input type="date" id="ffin" name="ffin" class="form-control" value="'.$ffin.'" required>
              <input hidden type="date" id="ffin2" class="form-control" value="'.date('Y-m-d',strtotime('last day of this month')).'">
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-2">
            <label class="mr-sm-2">Hora desde:</label>
            <div class="position-relative has-icon-left">
              <input type="time" id="hini" name="hini" class="form-control" value="'.$hini.'" required>
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="mr-sm-2">Hora hasta:</label>
            <div class="position-relative has-icon-left">
              <input type="time" id="hfin" name="hfin" class="form-control" value="'.$hfin.'" required>
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
         <div class="mb-5">
        <label class="mr-sm-2">Estatus:</label>
        <select name="estatus" id="estatus" class="form-control" >
          <option value="">Todos</option>
          <option value="A" ' . ($estatus == "A" ? "selected" : "") . '>Activo</option>
          <option value="F" ' . ($estatus == "F" ? "selected" : "") . '>Finalizado</option>
        </select>
        </div>
          <div class="mb-5">
            <label>Acciones:</label><br>
            <button type="button" onclick="mandar(0)" class="btn btn-success"><i class="icon-funnel"></i> Filtrar </button>
            <!-- <a href="?modulo=prospectos&accion=index"><button type="button" class="btn btn-warning"> -->
            <button type="button" onclick="mandar(1)" class="btn btn-warning">
            <span class="glyphicon glyphicon-record"></span> <i class="icon-android-cancel"></i> Limpiar
            </button><!-- </a> -->
          </div>
        </form>
        ';

        toolbar($_GET['modulo'],$nuevo,$filtro,$hojaactual);
          ?>
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

// Obtener el elemento del botón con el ID "nuevo"
const botonNuevo = document.getElementById('nuevo');
// Agregar un evento de clic al botón nuevo
botonNuevo.addEventListener('click', function(event) {
    // Prevenir el comportamiento predeterminado del evento
    event.preventDefault();
    $.ajax({
        type: "POST",
        url: "query/consultahoja.php",
        success: function(data) {
            // Comprobar si el resultado de la solicitud AJAX es diferente de "0" (Que haya una hoja abierta)
            if (data == 2) {
                // Si el resultado no es "0", mostrar una alerta
                alertSweet("error", "Atención", "No puede crear más de una hoja por día");
                //} else if (data == 3) {
                // Hay una hoja abierta de un día diferente a hoy
                //alertSweet("error", "Atención", "Existe una hoja sin finalizar de algún día previo");
            } else {
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
            }
        }
    });
});


// Agregar un evento que se ejecutará cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el elemento del modal con el ID "newtablero"
    const modalNewTablero = document.getElementById('newtablero');
    // Comprobar si el elemento del modal existe en el DOM
    if (modalNewTablero) {
        // Si el modal existe, agregar un evento que se ejecutará cuando se oculte el modal
        modalNewTablero.addEventListener('hidden.bs.modal', function() {
            // Obtener el botón con el ID "nuevo"
            const botonNuevo = document.getElementById('nuevo');
            // Remover los atributos "data-bs-target" y "data-bs-toggle" del botón "nuevo"
            botonNuevo.removeAttribute("data-bs-target");
            botonNuevo.removeAttribute("data-bs-toggle");
        });
    }
});

/* function mandar(id){
  document.getElementById("page").value = id;
  document.getElementById("filtro").submit();
} */

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
<?php
          //Modal - Nuevo deal - TABLERO
          echo '
          <div class="modal fade text-xs-left" id="newtablero" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title" id="myModalLabel33">Nueva hoja de prospectos</h4>
                </div>
                <form method="post" action="?modulo=prospectos&accion=insert" autocomplete="off" id="formNuevaHoja">
                  <div class="modal-body row">
                  <div class="col-md-12 col-xs-12">
                    <label>Nombre de la hoja:</label>
                    <div class="mb-5">
                      <div class="position-relative has-icon-left">
                        <input type="text" name="estado" id="estado" class="form-control text-uppercase" oninput="this.value = this.value.toUpperCase();" required placeholder="EJEMPLO: JALISCO">
                        <div class="form-control-position">
                          <i class="icon-map"></i>
                        </div>
                      </div>
                    </div>
                  </div>

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

                    <div class="col-12">
                    <table width="100%" class="mb-0 table table-hover table-striped" id="myTable2">
                    <thead class="bg-light-blue bg-darken-2 ">
                      <tr>
                        <th><b>Vendedor</b></th>
                        <th><b>Estatus</b></th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    </table>
                    </div>

                  </div>
                  <div class="modal-footer p-2">
                    <center>
                      <button type="button" id="btnRegistrarHoja" class="btn btn-primary">
                        <i class="fa fa-check"></i> Registrar hoja nueva
                      </button>
                    </center>
                  </div>
                </form>
              </div>
            </div>
          </div>';
          //filtro
        echo '
      <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table width="90%" class="mb-0 table-hover table-striped" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">
                    <tr>
                      <th width="8%"><b>No. Hoja</b></th>
                      <th width="10%"><b>Estado</b></th>
                      <th width="10%"><b>Usuario</b></th>
                      <th width="10%"><b>No. Prospectos</b></th>
                      <th width="10%"><b>Fecha</b></th>
                      <th width="10%"><b>Hora inicio</b></th>
                      <th width="10%"><b>Hora final</b></th>
                      <th width="17%"><b>Observaciones</b></th>
                      <th width="8%"><b>Estatus</b></th>
                      <th width="10%"></th>
                    </tr>
                  </thead>';
                echo '</table>
              </center>
            </div>
          </div>
        </div>
      </div>';

      echo '<script>
      function mandar(id){
        var fini = document.getElementById("fini");
        var ffin = document.getElementById("ffin");
        var hini = document.getElementById("hini");
        var hfin = document.getElementById("hfin");
        var ugen = document.getElementById("nmb");
        var estatus = document.getElementById("estatus");

        if(id == 1){
          search = "";
          fini.value = document.getElementById("fini2").value;
          ffin.value = document.getElementById("ffin2").value;
          hini.value = "";
          hfin.value = "";
          ugen.value = "";
          estatus.value = "";
        }

        $("#myTable").DataTable().clear().draw();
        $("#myTable").DataTable().destroy();

        $("#myTable").DataTable( {
          paging: true,
          scrollY: 400,
          processing: true,
          serverside: true,
          ordering: false,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatableprospectos.php",
            type: "POST",
            datatype: "json",
            data:{ 
              "fini": fini.value,
              "ffin": ffin.value,
              "hini": hini.value,
              "hfin": hfin.value,
              "ugen" : ugen.value,
              "estatus" : estatus.value
            }
          },
          pageLength: "50",
          responsivePriority: 1,
          columnDefs: [
            { type: "num", targets: 0 } // Indicar que la columna 0 debe tratarse como numérica
        ],
        order: [[0, "desc"]]
        });
        $("#myTable").on("draw.dt", function () {
          aplicarEstiloColumna(8);
        });
      }

      mandar(1);

      function aplicarEstiloColumna(col) {
        var columnaNumero = col;
        $("#myTable tbody tr").each(function() {
          var celdas = $(this).find("td");
          if (celdas.length > columnaNumero) {
            celdas.eq(columnaNumero).css("width", "70px");
            celdas.eq(columnaNumero).css("height", "34px");
            celdas.eq(columnaNumero).css("display", "table-cell");
            celdas.eq(columnaNumero).addClass("btn");
            celdas.eq(columnaNumero).addClass("no-border");
            celdas.eq(columnaNumero).addClass("text-middle");
            celdas.eq(columnaNumero).addClass("btn-sm"); 

            if(celdas.eq(columnaNumero).html() == "Finalizado"){
              celdas.eq(columnaNumero).addClass("btn-success");
            } else{
              celdas.eq(columnaNumero).addClass("btn-warning");
            }
          }
        });
      }
      

      //var table = $("#myTable").DataTable();
      $("#myTable2").DataTable().clear().draw();
      $("#myTable2").DataTable().destroy();
    
    
      $("#myTable2").DataTable( {
        paging: true,
        
        processing: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
        },
        ajax: {
          url: "query/datatableordenvendedores.php",
          type: "POST",
          datatype: "json",
          data: {tipo: 1}
        },
        pageLength: "50",
        responsivePriority: 1
      });
    
      document.getElementById("btnRegistrarHoja").addEventListener("click", function (e) {
      e.preventDefault();
      
      let estadoInput = document.getElementById("estado");
      let form = document.getElementById("formNuevaHoja");

      if (!estadoInput || estadoInput.value.trim() === "") {
        Swal.fire("Error", "Debes ingresar un estado.", "warning");
        return;
      }

      let estado = estadoInput.value.trim().toUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

      $.ajax({
        url: "query/validarestadohoja.php",
        type: "POST",
        data: { estado: estado },
        dataType: "json",
        success: function (data) {
          if (data.existe) {
            Swal.fire({
              icon: "warning",
              title: "Estado duplicado",
              text: "Ya existe una hoja activa con este nombre. ¿Deseas ser redirigido a la hoja?",
              showCancelButton: true,
              confirmButtonText: "Ir a la hoja",
              cancelButtonText: "Cancelar"
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = "?modulo=prospectos&accion=show&id=" + data.id;
              }
            });
          } else {
            form.submit();
          }
        }
      });
    });

    </script>';


/*       echo '
      <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            processing: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
             ajax: {
            url: "query/datatableprospectos.php",
            type: "POST",
            datatype: "json",
            data:{ 
              "fini": document.getElementById("fini2").value,
              "ffin": document.getElementById("ffin2").value,
              "hini": "",
              "hfin": "",
              "ugen" : "",
              "estatus" : ""
            }
            },
            pageLength: "50",
            responsivePriority: 1,
            order: [[0, "desc"]], // Ordenar en orden descendente por la columna 0
        });
        $("#myTable").on("draw.dt", function () {
          aplicarEstiloColumna(7);
        });
      </script>
      '; */

    }
    
function show(){      
    $estatus = busca($_GET['id'], "crm_capturaleads", "cc_id", "cc_estatus");
    echo '
      <link rel="stylesheet" href="assets/css/intlTelInput.css">
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

    echo '
    <script>
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

    $select = 'SELECT cc_fini, cc_hini, cc_hfin, cc_ugen, cc_ucancela FROM crm_capturaleads WHERE cc_id = "'.$_GET['id'].'"';
      $results = setq($select);
      list($fini, $hini, $hfin, $ugen, $ucancela) = $results->fetch_array();
      $fini = fecha_formato($fini, '', false);
      echo '
          <div class="modal fade text-xs-left" id="verdetalles" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h4 class="modal-title" id="myModalLabel33">Detalles de la página</h4>
                      </div>
                      <div class="container-dos">
                          <div class="listWrap">
                              <div class="table-responsive">
                                  <table id="tabladetalles" class="table table-striped table-bordered">
                                      <thead class="thead-dark">
                                          <tr>
                                              <th style="background: black; color: white;">&nbsp;Fecha</th>
                                              <th style="background: black; color: white;">Hora inicio</th>
                                              <th style="background: black; color: white;">Hora final</th>
                                              <th style="background: black; color: white;">Generó</th>
                                              <th style="background: black; color: white;">Finalizó</th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          ';
                                          if($hfin == ""){
                                              $hfin = "En captura";
                                              $ucancela = "En captura";
                                          }
                                          echo '
                                          <tr>                
                                              <td>&nbsp;'.$fini.'</td>
                                              <td>'.$hini.'</td>
                                              <td>'.$hfin.'</td>
                                              <td>'.$ugen.'</td>
                                              <td>'.$ucancela.'</td>
                                          </tr>
                                      </tbody>
                                  </table>
                              </div>
                          </div>
                      </div>
                      <br>
                      <div style="display: flex; justify-content: center;">
                          <button type="button" class="btn btn-sm btn-danger" id="cerrarModal"><i class="fas fa-window-close"></i> Cerrar</button>
                      </div>
                      <br>
                      <br>
                  </div>
              </div>
          </div>';

      
      // Agrega el siguiente script JavaScript para cerrar el modal cuando se hace clic en el botón "Cerrar"
      echo '
      <script>
          document.getElementById("cerrarModal").addEventListener("click", function() {
              $("#verdetalles").modal("hide");
          });
      </script>';
    

    $registros = $this->model->resultt->num_rows;

    

    $atras = '
    <a href="?modulo=prospectos&accion=index">
      <button class="btn btn-sm btn-warning">
        <i class="fa fa-arrow-left"></i> Atrás
      </button>
    </a>';  
    
    $detalles = '
    <button id="nuevo" type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#verdetalles" >
      <i class="fa fa-info"></i> Detalles
    </button>';
    

    if($estatus == "F"){
      toolbar($_GET['modulo'],$atras,'','', $detalles);
    }

    if($estatus == "A"){
      
      $fechahoja = busca($_GET['id'], "crm_capturaleads", "cc_id", "cc_fini");
    
      $disb = "disabled";
      if($registros >= 2){
        $disb = "";
      } 
    
    $otro = '<button type="button" id="guardarOrden" class="btn btn-sm btn-primary" '.$disb.'><i class="fas fa-clipboard-check"></i>Asignar prospectos</button>';
    $finalizar = '<button type="button" onClick="finalizar('.$_GET['id'].')" class="btn btn-sm btn-danger"><i class="fas fa-window-close"></i>Finalizar hoja</button>';
    

    $atras = '
    <a href="?modulo=prospectos&accion=index">
      <button class="btn btn-sm btn-warning">
        <i class="fa fa-arrow-left"></i> Atrás
      </button>
    </a>

    <button type="button" id="btnNuevoProspecto" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoProspecto">
      <i class="fas fa-user-plus"></i> Nuevo prospecto
    </button>
    ';  
    
    $detalles = '
    <button id="nuevo" type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#verdetalles" >
      <i class="fa fa-info"></i> Detalles
    </button>';

    $vendedores = '
      <a data-fancybox data-type="ajax" data-src="popup/setordenvendedores.php?id='.$_GET['id'].'" href="javascript:;" >
      <button id="vendedores" type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#vervendedores" >
        <i class="fas fa-sort-alpha-up"></i> Vendedores
      </button>
        </a>
      ';

      $filtro = '
        <form autocomplete="off" action="?modulo=leadscapturados&accion=index" method="post" id="filtro" class="">
          <div class="mb-5" hidden>
            <label class="mr-sm-2">Page:</label>
            <div class="position-relative has-icon-left">
              <input type="text" id="page" name="page" class="form-control" value="'.$page.'" required>
            </div>
          </div>
          <div class="mb-2">
            <label class="mr-sm-2">Telefono:</label>
            <div class="position-relative has-icon-left">
              <input type="number" id="telefono" name="telefono" placeholder="Buscar por número de telefono" class="form-control" value="'.$telefono.'" required>
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-5" style="max-width: 250px">
            <label for="">Buscar por vendedor:</label>
            <select id="uvendedor" class="form-control" name="uvendedor">
              <option value="">Todos</option>';
              $sqlvend = 'SELECT * FROM usuarios WHERE u_estatus = "A" AND u_grupo = "VENTAS"';
              $resultvend = setq($sqlvend);
              while($row = $resultvend->fetch_array()){
                if($vendedor == $row['u_id']) $sel = 'selected';
                else $sel = '';
                $filtro .= '<option value="'.$row['u_id'].'">'.$row['u_nmb'].' '.$row['u_apellidos'].'</option> '.$sel.'';
              }
              $filtro .= 
            '</select>
          </div>

          <div class="mb-5" style="max-width: 250px">
            <label for="">Buscar por estatus:</label>
            <select id="estatusfiltro" class="form-control" name="estatus">
              <option value="">Todos</option>
              <option value="X" '.$estatus.'>Sin negociación</option>
              <option value="A" '.$estatus.'>Asignados</option>
              <option value="R" '.$estatus.'>Reasignados</option>
            </select>
          </div>

          <div class="mb-2">
            <label class="mr-sm-2">Fecha:</label>
            <div class="position-relative has-icon-left">
              <input type="date" id="fecha" name="fecha" class="form-control" value="" required>
              <div class="form-control-position">
                <i class="icon-calendar5"></i>
              </div>
            </div>
          </div>
          <div class="mb-5">
            <label>Acciones:</label><br>
            <button type="button" onclick="mandar(0)" class="btn btn-success"><i class="fas fa-search"></i> Filtrar </button>
            <a href="?modulo=leadscapturados&accion=index"><button type="button" class="btn btn-warning">
              <span class="glyphicon glyphicon-record"></span> <i class="fas fa-broom"></i> Limpiar
            </a>
          </div>
        </form>
        ';

    if($estatus == "A"){
      toolbar($_GET['modulo'],$atras,'',$finalizar.$otro.$vendedores, $detalles);
    }
    


    echo '
    <style>
    .container { 
      display: flex;
      width: 100%;
      height: 100%;
      padding: 20px;
      background: white;
      justify-content: center;
    }

    .container-dos { 
      display: flex;
      width: 100%;  
      justify-content: center;
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
    
    .list thead tr:nth-child(2) th {
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

    .tabla-scroll {
      max-height: 300px; /* Establece la altura máxima que deseas */
      overflow-y: scroll;
    }
    
    .tabla-scroll table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .tabla-scroll th,
    .tabla-scroll td {
      padding: 8px;
      border: 1px solid #ccc;
    }
    
    .tabla-scroll thead {
      position: sticky;
      top: 0;
      background-color: #f5f5f5;
    }
    
    .table-container {
      overflow-x: auto;
    }
    table {
      width: 100%;
      max-width: 100%;
      border-collapse: collapse;
    }
    table, th, td {
      
      white-space: nowrap;
      /* Establece white-space en nowrap para evitar que el texto se rompa */
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
      //MODAL NUEVO PROSPECTO
      echo '
      <div class="modal fade" id="modalNuevoProspecto" tabindex="-1" aria-labelledby="modalNuevoProspectoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="modalNuevoProspectoLabel">Nuevo Prospecto</h5>
              <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <form id="formNuevoProspecto">
                <input type="hidden" id="lead" name="lead" value="' . $_GET['id'] . '>
                <input type="hidden" id="code">
                <input type="hidden" id="pais">
                <div class="row g-2">
                  <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono *</label>
                    <input type="number" id="telefono" name="telefono" maxlength="12" class="form-control" required>
                  </div>
                  <div class="col-md-4">
                    <label for="nmb" class="form-label">Nombre completo *</label>
                    <input type="text" id="nmb" name="nmb" class="form-control" required>
                  </div>
                  <div class="col-md-4">
                    <label for="cp" class="form-label">Código Postal</label>
                    <input type="number" id="cp" name="cp" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label for="correo" class="form-label">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control">
                  </div>
                  <div class="col-md-6">
                    <label for="obs" class="form-label">Observaciones</label>
                    <input type="text" id="obs" name="obs" class="form-control">
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button id="btnGuardarProspecto" type="button" class="btn btn-primary" onclick="addProspecto()">
                <i class="fas fa-save"></i> Guardar
              </button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Cancelar
              </button>
            </div>
          </div>
        </div>
      </div>

      ';
          ?>
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

    /* function mandar(id){
      document.getElementById("page").value = id;
      document.getElementById("filtro").submit();
    } */

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
          window.location.href = "?modulo=leadscapturados&accion=finalizar&id=" + id;
        }
        Swal.close();
      });
    }


    </script>
          <?php
          //Modal - Nuevo deal - TABLERO
          echo '
          <div class="modal fade text-xs-left" id="newtablero" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title" id="myModalLabel33">Nueva hoja de leadscapturados</h4>
                </div>
                <form method="post" action="?modulo=leadscapturados&accion=insert" autocomplete="off" >
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
      <form hidden id="formreg" method="post" action="?modulo=clientes&accion=edit" autocomplete="off">
        <input type="number" name="flag" value="1" hidden>
        <input type="text" id="idp" name="idp" hidden>
        <input type="text" id="nmbp" name="nmbp" hidden>
        <input type="text" id="telefonop" name="telefonop" hidden>
        <input type="text" id="correop" name="correop" hidden>
        <input type="text" id="cpp" name="cpp" hidden>
        <input type="text" id="observacionesp" name="observacionesp" hidden>
      </form>
      ';

      echo '
      <form hidden id="formtablero" method="post" action="?modulo=tableros&accion=index" autocomplete="off">
        <input type="number" name="flag" value="1" hidden>
        <!-- <input type="text" id="aliasp2" name="aliasp" hidden> -->
        <input type="text" id="idlead" name="idlead" hidden>
      </form>
      ';
      $nombres = array();
      $sqlnmb = 'SELECT u_nmb, u_apellidos, u_id FROM usuarios WHERE u_grupo = "VENTAS" AND u_estatus = "A" ';
      $resultnmb = setq($sqlnmb);
      while($rownmb = $resultnmb -> fetch_array()){
        $nombres[$rownmb['u_id']] = $rownmb['u_nmb'].' '.$rownmb['u_apellidos'];
      }


      echo '
      <div class="card mt-3">
        <div class="card-body row" >';
          echo '<div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive text-medium" >
              <center>
                <table width="90%" class="mb-0 table-hover table-striped" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">
                    <tr>
                      <th><b>Nombre</b></th>
                      <th><b>Teléfono</b></th>
                      <th><b>Correo</b></th>';
                      $sqlsaludo = 'SELECT u_saludo, u_grupo FROM usuarios WHERE u_id = "'.$_SESSION['uid'].'"';
                      $resultsaludo = setqemojis($sqlsaludo);
                      list($texto, $grupo) = $resultsaludo->fetch_array();
                      if($grupo == "ADMIN" || $grupo == "GERENCIA"){
                        echo '<th><b>Vendedor</b></th>';
                      }
                      echo '<th><b>Estatus</b></th>
                      <th><b>Asignado</b></th>
                      <th><b>Acercamiento</b></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>';
                    
                    while($row = $this->model->result->fetch_array()){
                      // if($_SESSION['uid'] == "ADMIN") echo $row['cl_vendedor'].'<br>';
                      $nmbcompleto = $nombres[$row['cl_vendedor']];
                      // Decodificar entidades HTML
                      $html = html_entity_decode($texto);
                      // Reemplazar saltos de línea con saltos de línea reales
                      $texto = str_replace('<br>', "\n", $html);
                      // Eliminar etiquetas HTML restantes
                      $texto = strip_tags($texto); 
                      $texto_codificado = urlencode($texto);
                      if(empty($texto)){
                        /* $phone = $row['cl_telefono']; */
                        $texton = "¡Hola, un gusto saludarte!";
                        $texto_codificado = urlencode($texton);
                      }
                      $numero_de_telefono = "+".$row['cl_code'].$row['cl_telefono']; // Reemplaza con tu número de teléfono en formato internacional
                      $link_whatsapp = "https://api.whatsapp.com/send?phone=$numero_de_telefono&text=$texto_codificado";
                      
                      $phone = '<a href="'.$link_whatsapp.'" target="_blank">'.'+'.$row['cl_code'].$row['cl_telefono'].'&nbsp;
                      <button style="border: 0px; background: white;" title="Contactar por WhatsApp">
                          <i class="fab fa-whatsapp" style="font-size: 20px;"></i>
                      </button>
                      </a>';

                      if($row['cl_estatus'] == "A"){
                        $estatusdesc = "Asignado";
                      } else if($row['cl_estatus'] == "R" && $grupo != "ADMIN" && $grupo != "GERENCIA"){
                        $estatusdesc = "Asignado";
                      } else if($row['cl_estatus'] == "R"){
                        $estatusdesc = "Reasignado";
                      } else {
                        $estatusdesc = "Sin negociación";
                        /* $acciones = '<center><span><em>Lead "Sin negociación"</em></span></center>'; */
                        $acciones = '';
                      }
                      if($row['cl_acercamiento'] == 0){
                        $check = "";
                      } else{
                        $check = " checked disabled";
                        /* $acciones = '<center><span><em>Se hizo acercamiento a este lead</em></span></center>'; */
                      }
echo '
<style>
  .estatus-select option[value="No iniciado"] {
    background-color: #00bcd4; /* Azul claro */
    color: white;
  }
  .estatus-select option[value="Correo Enviado"] {
    background-color: #4caf50; /* Verde */
    color: white;
  }
  .estatus-select option[value="No localizado"] {
    background-color: #f57c00; /* Naranja */
    color: white;
  }
  .estatus-select option[value="Reunion concretada"] {
    background-color: #1565c0; /* Azul fuerte */
    color: white;
  }
  .estatus-select option[value="Grupo Creado"] {
    background-color: #00897b; /* Verde azulado */
    color: white;
  }
  .estatus-select option[value="Buzon"] {
    background-color: #ffeb3b; /* Amarillo */
    color: black;
  }
  .estatus-select option[value="Negado"] {
    background-color: #424242; /* Gris oscuro */
    color: red;
  }
  .estatus-select option[value="Contactar Nuevamente"] {
    background-color: #b71c1c; /* Rojo fuerte */
    color: white;
  }
</style>

<script>
function aplicarColorSelect(select) {
    let color = "#00bcd4"; // default "No iniciado"
    let textColor = "white";

    switch (select.value) {
        case "": // si no tiene valor
        case "No iniciado": 
            color = "#00bcd4"; 
            break;
        case "Correo Enviado": 
            color = "#4caf50"; 
            break;
        case "No localizado": 
            color = "#f57c00"; 
            break;
        case "Reunion concretada": 
            color = "#1565c0"; 
            break;
        case "Grupo Creado": 
            color = "#00897b"; 
            break;
        case "Buzon": 
            color = "#ffeb3b"; 
            textColor = "black"; 
            break;
        case "Negado": 
            color = "#424242"; 
            textColor = "red"; 
            break;
        case "Contactar Nuevamente": 
            color = "#b71c1c"; 
            break;
    }

    select.style.backgroundColor = color;
    select.style.color = textColor;
}

// Aplicar al cargar la página
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".estatus-select").forEach(select => {
        aplicarColorSelect(select);
        select.addEventListener("change", function() {
            aplicarColorSelect(this);
        });
    });
});
</script>
';

$acciones = '
<div class="row">
  <div class="col-5">
    <button type="button" onClick="iniciarTablero('.$row['cl_id'].');" class="btn btn-sm btn-success" data-toggle="tooltip" title="Iniciar un tablero">
      <i class="fas fa-book-open"></i> Iniciar
    </button>
  </div>
  <div class="col-7">
    <select class="form-control form-control-sm estatus-select" onchange="cambiarEstatusLead(this, '.$row['cl_id'].')">
      <option value="No iniciado" '.($row['cl_observacion'] == "No iniciado" ? 'selected' : '').'>No iniciado</option>
      <option value="Correo Enviado" '.($row['cl_observacion'] == "Correo Enviado" ? 'selected' : '').'>Correo Enviado</option>
      <option value="No localizado" '.($row['cl_observacion'] == "No localizado" ? 'selected' : '').'>No localizado</option>
      <option value="Reunion concretada" '.($row['cl_observacion'] == "Reunion concretada" ? 'selected' : '').'>Reunión concretada</option>
      <option value="Grupo Creado" '.($row['cl_observacion'] == "Grupo Creado" ? 'selected' : '').'>Grupo Creado</option>
      <option value="Buzon" '.($row['cl_observacion'] == "Buzon" ? 'selected' : '').'>Buzón</option>
      <option value="Negado" '.($row['cl_observacion'] == "Negado" ? 'selected' : '').'>Negado</option>
      <option value="Contactar Nuevamente" '.($row['cl_observacion'] == "Contactar Nuevamente" ? 'selected' : '').'>Contactar Nuevamente</option>
    </select>
  </div>
</div>';


                      $checkb = '<center><input class="form-check-input consult-check" onclick="marcarAcercamiento('.$row['cl_id'].')" type="checkbox" name="select'.$row['cl_id'].'" id="select'.$row['cl_id'].'" '.$check.'></center>';
                      $hfasignacion = date('d-m-Y',strtotime($row['cl_fasigna'])).' '.$row['cl_hasigna'];

                      echo '<tr>
                        <td>'.$row['cl_nmb'].'</td>
                        <td>'.$phone.'</td>
                        <td>'.$row['cl_correo'].'</td>';
                        if($grupo == "ADMIN" || $grupo == "GERENCIA"){
                          echo '<td>'.$nmbcompleto.'</td>';
                        }
                        echo '<td>'.$estatusdesc.'</td>';
                        echo '<td>'.$hfasignacion.'</td>';
                        echo '<td>'.$checkb.'</td>
                        <td>'.$acciones .'</td>
                      </tr>';
                    }
                  echo '</tbody>';
                echo '</table>
              </center>
            </div>
          </div>
        </div>
      </div>
      
      ';

    
    if($_REQUEST['page'] == 0){
      $hidden = 'style="pointer-events: none;
      background: #70707026;
      color: black;"';
    }else $hidden = "";

    echo '<div class="col-md-12 mt-5 text-xs-center">
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

            //$_REQUEST['categoria'],$_REQUEST['marca'],$_REQUEST['page'],$_REQUEST['unidad'],$_REQUEST['linean'],$_REQUEST['esquema']
    $sqlf = '';
    if(!empty($estatus)){
      $sqlf .= ' cl_estatus = "'.$estatus.'"';
    } else{
      $sqlf .= ' cl_estatus IN ("R", "A")';
    }
    $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
    if($grupo == 'ADMIN' || $grupo == 'GERENCIA'){
      if(!empty($vendedor)){
        $sqlf .= ' AND cl_vendedor = "'.$vendedor.'"';
      } else{
        $sqlf .= '';
      }
    } else {
      if(!empty($vendedor) && $vendedor != $_SESSION['uid']){
        $sqlf .= '';
        $caso = 1;
      } else{
        $sqlf .= ' AND cl_vendedor = "'.$_SESSION['uid'].'"';
      }
    }

    $bloque = 50;
    if(trim($sqlf) == ""){
      $sql = 'SELECT COUNT(*) FROM crm_leads';
    } else {
      $sql = 'SELECT COUNT(*) FROM crm_leads WHERE '.$sqlf.'';
    }
    
    $resultpg = setq($sql);
    list($numg) =  $resultpg->fetch_array();
    $pageres = ceil($numg/$bloque);

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
  </div>'; 

      echo '<script>

      /* function mandar(id){
        var telefono = document.getElementById("telefono");
        var estatusfiltro = document.getElementById("estatusfiltro");
        var fecha = document.getElementById("fecha");
        var vendedor = document.getElementById("uvendedor");

        if(id == 0){          
          estatusfiltro.value = "";
          fecha.value = "";
          vendedor.value = "";
          telefono.value = "";
          page.value = "0";
        } 

        //$("#myTable").DataTable().clear().draw();
        $("#myTable").DataTable().destroy();

        $("#myTable").DataTable({
          paging: true,
          scrollY: 400,
          processing: true,
          ordering: false,
          language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
          },
          ajax: {
            url: "query/datatableleadscapturados.php",
            type: "POST",
            datatype: "json",
            data:{ 
              "estatusfiltro": estatusfiltro.value,
              "fecha": fecha.value,
              "vendedor": vendedor.value,
              "page": page.value,
              "telefono": telefono.value
            }
          },
          pageLength: "50",
          responsivePriority: 1,
        });
      } */

      function mandar(id){
        document.getElementById("page").value = id;
        document.getElementById("filtro").submit();
      }

      function registrarCliente(id){
        $.ajax({
          url: "query/consultalead.php", 
          type: "POST", 
          dataType: "json", 
          data: {
            "id": id
          },
          success: function(data) {
            document.getElementById("idp").value = id;
            document.getElementById("nmbp").value = data["nmb"];
            document.getElementById("telefonop").value = data["telefono"];
            document.getElementById("correop").value = data["correo"];
            document.getElementById("cpp").value = data["cp"];
            document.getElementById("observacionesp").value = data["observaciones"];
            document.getElementById("formreg").submit();
          },
          error: function(jqXHR, textStatus, errorThrown) {
              // Manejo de errores
              console.log("Error: " + textStatus);
          }
      });
      }

      function iniciarTablero(id){
        var formulario = document.getElementById("formtablero");
        document.getElementById("idlead").value = id;
        formulario.submit();
      }
      
 
      function marcarAcercamiento(id){
        var checkb = document.getElementById("select"+id);
        var estatus = 0;

        // Verifica si está marcado
        if (checkb.checked) {
          estatus = 1;
        } else {
          estatus = 0;
        }

        $.ajax({
          url: "query/marcaracercamiento.php", 
          type: "POST",  
          data: {
            "id": id,
            "estatus": estatus
          },
          success: function(data) {
            //mandar(1);
          },
          error: function(jqXHR, textStatus, errorThrown) {
              // Manejo de errores
              console.log("Error: " + textStatus);
          }
      });
      }
      //mandar(0);

      function cambiarEstatusLead(select, idLead) {
        const nuevoEstatus = select.value;

        $.ajax({
          url: "query/actualiza_estatus_observacion.php",
          type: "POST",
          data: {
            id: idLead,
            estatus: nuevoEstatus
          },
          success: function(response) {
            //Swal.fire({
              //icon: "success",
              //title: "Actualizado",
              //text: "Estatus actualizado correctamente."
            //});
          },
          error: function() {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "No se pudo actualizar el estatus."
            });
          }
        });
      }

    </script>';


     /*  echo '
      <script>
       $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            processing: true,
            ordering: false,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
             ajax: {
            url: "query/datatableleadscapturados.php",
            type: "POST",
            datatype: "json",
            data:{ 
              "estatusfiltro": "",
              "fecha": ""
            }
            },
            pageLength: "50",
            responsivePriority: 1,
        });
      </script>
      '; */


            } else{
      echo '
      <div class="card">
          <div class="card-body">';
          echo '<div class=" col-12 table-responsive">
          <center><table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
          <thead class="bg-light-blue bg-darken-2 ">
            <tr>
              <th><b>Telefono</b></th>
              <th><b>Nombre</b></th>
              <th><b>Código postal</b></th>
              <th><b>Correo</b></th>
              <th><b>Observaciones</b></th>
              <th><b>Hora de asignación</b></th>
              <th><b>Fecha de asignación</b></th>
            </tr>
          </thead>';
          echo '<tbody>';
          $fecha = '';
          while($row = $this->model->result->fetch_array()){
          echo'
            <tr class="">
              <th style="height: 44.84px;">'.$row['cl_telefono'].'</span></th>
              <th style="height: 44.84px;"><span>'.$row['cl_nmb'].'</span></th>
              <th style="height: 44.84px;">'.$row['cl_cp'].'</span></th>
              <th style="height: 44.84px;">'.$row['cl_correo'].'</span></th>
              <th style="height: 44.84px;">'.$row['cl_observacion'].'</span></th>
              <th style="height: 44.84px;">'.$row['cl_hasigna'].'</span></th>
              <th style="height: 44.84px;">'.$row['cl_fasigna'].'</span></th>
            </tr>';

            $fecha = $row['cl_fasigna'];
          }
          echo '
          </tbody>';
        echo '</table>
        </div>
      </div>';
        }
      ?>
      <script src="assets/js/intlTelInput.js"></script>
      <script>
      // Ejecutamos el focus cuando se cargue la pagina
      window.onload = setFocus;
      //Hacemos focus en el elemento nombre
      function setFocus() {
          const inputElement = document.getElementById('telefono');
          inputElement.focus();
      }

document.getElementById("btnNuevoProspecto").addEventListener("click", function () {
    document.getElementById("id").value = "";
    document.getElementById("lead").value = "<?php echo $_GET['id']; ?>";
    document.getElementById("nmb").value = "";
    document.getElementById("cp").value = "";
    document.getElementById("correo").value = "";
    document.getElementById("telefono").value = "";
    document.getElementById("obs").value = "";
    //document.getElementById("code").value = "";
    document.getElementById("pais").value = "";
    
    // Reinicia el texto del botón por si vienes de una edición
    document.getElementById("btnGuardarProspecto").innerHTML = '<i class="fas fa-save"></i> Guardar';

    // Resetea validaciones o focus si usas
    $("#btnguardar").prop("disabled", true);

    // Restablece la lada por defecto
    iti.setCountry("mx");
});


      document.getElementById("btnguardar").addEventListener("click", function() {
        // Deshabilita el botón al hacer clic
        this.disabled = true;

        // Habilita el botón nuevamente después de 2 segundos
        setTimeout(function() {
            /* document.getElementById("btnguardar").disabled = false; */
        }, 2000); // 2000 milisegundos = 2 segundos
      });

  const inputs = document.querySelectorAll('.focusable');

  for (let i = 0; i < inputs.length; i++) {
      inputs[i].addEventListener('keydown', function(event) {
          if (event.key === 'Tab') {
              event.preventDefault();

              const currentInput = inputs[i];
              const nextInput = inputs[(i + 1) % inputs.length];

              if (currentInput.value !== '' || currentInput.id == "correo" || currentInput.id == "obs" ||
                  currentInput.id == "cp") {
                  nextInput.focus();
              }
          }
      });
  }

  //Evento que se ejecuta cuando el usuario presione la tecla de enter
  document.addEventListener('keydown', function(event) {
      // Verificar si la tecla presionada es Enter (keyCode 13 o key 'Enter')
      if (event.key === 'Enter' || event.keyCode === 13) {
          // Evitar el comportamiento predeterminado de la tecla Enter (evitar el envío de formularios)
          event.preventDefault();
          var boton = document.getElementById('btnguardar');
          if (!boton.hasAttribute('disabled')) {
              boton.click();
          }
      }
  });
  $("#myTable").DataTable({
      paging: true,
      scrollY: 400,
      processing: true,
      serverside: true,
      ordering: false,
      language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
      },
      pageLength: "50",
      responsivePriority: 1,
  });

  // Inicializar la tabla arrastrable
  const guardarOrdenBtn = document.getElementById("guardarOrden");

  guardarOrdenBtn.addEventListener("click", function() {
      guardarOrdenBtn.setAttribute("disabled", true);
      Swal.fire({
          icon: "question",
          title: "Atención",
          text: "¿Está seguro de finalizar la captura?",
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancelar',
          confirmButtonText: 'Estoy seguro'
      }).then((result) => {
          if (result.isConfirmed) {
              Swal.close();
              fcaptura();
          }
      })
  });

  function fcaptura() {
      window.location.href = "?modulo=prospectos&accion=fcaptura&id=" + <?php echo $_GET['id'];?>;
  }

  function setFocusInput(input) {
      const inputElement = document.getElementById(input);
      inputElement.focus();
  }

  <?php echo isset($onfocus); ?>

  document.addEventListener("DOMContentLoaded", function() {
      var phone = document.getElementById("telefono");
      var correoInput = document.getElementById('correo');
      var nmbInput = document.getElementById('nmb'); // Nuevo campo nmb
      var cpInput = document.getElementById('cp'); // Nuevo campo cp
      var miBoton = document.getElementById('btnguardar');

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      function updateGuardarButtonState() {
          let phoneValue = phone.value;

          /*     // Truncar el teléfono a 10 dígitos
              if (phoneValue.length > 0) {
                phoneValue = phoneValue.slice(0, 10);
                phone.value = phoneValue;
              } */

          const correo = correoInput.value;
          const nmbValue = nmbInput.value; // Valor del nuevo campo nmb
          const cpValue = cpInput.value; // Valor del nuevo campo cp

          const isPhoneValid = phoneValue.length >= 5.;
          const isCorreoValid = correo === "" || emailRegex.test(correo);
          const isNmbValid = nmbValue !== ""; // Campo nmb no debe estar vacío
          const isCpValid = cpValue !== ""; // Campo cp no debe estar vacío

          /* miBoton.disabled = !(isPhoneValid && isCorreoValid && isNmbValid && isCpValid); */
        miBoton.disabled = !(isPhoneValid && isCorreoValid && isNmbValid);
    }

    // Agregar eventos de escucha para detectar cambios en los campos
    phone.addEventListener('input', updateGuardarButtonState);
    correoInput.addEventListener('input', updateGuardarButtonState);
    nmbInput.addEventListener('input', updateGuardarButtonState); // Nuevo campo nmb
    cpInput.addEventListener('input', updateGuardarButtonState); // Nuevo campo cp
  });

  function addProspecto() {
      var guardarOrden = document.getElementById("guardarOrden");
      var lead = document.getElementById("lead").value;
      var nmb = document.getElementById("nmb").value;
      var cp = document.getElementById("cp").value;
      var email = document.getElementById("correo").value;
      var correo = email.toLowerCase();
      var telefono = document.getElementById("telefono").value;
      var code = 0;
        if(document.getElementById("code"))
        {
          code = document.getElementById("code").value
        };
      var pais = document.getElementById("pais").value;

      var observacion = document.getElementById("obs").value;
      var id = "";
      if(document.getElementById("id")){
        id = document.getElementById("id").value;
      }
      if (nmb == "") {
          alertSweet("error", "Atención", 'El campo "Nombre" no puede quedar vacío. Verifique');
      } else {
          $("#modalNuevoProspecto").modal('hide');

          $.ajax({
                  url: "query/addprospecto.php",
                  method: "POST",
                  dataType: 'json',
                  data: {
                      'nmb': nmb,
                      "cp": cp,
                      "correo": correo,
                      "telefono": telefono,
                      "code": code,
                      "pais": pais,
                      "observacion": observacion,
                      "lead": lead,
                      "id": id
                  },
              })
              .done(function(data) {
              //alert("DATAAA: " + data.respuesta);
                  if (data.respuesta == 41) { //El telefono ya existe en la pagina
                      alertSweet("error", "Atención", "Ya existe un registro para el vendedor " + data.agente +
                          " con el mismo número de telefono en la tabla. Verifique.");
                  } else if (data.respuesta == 42) { //El telefono ya existe en la pagina
                      alertSweet("error", "Atención", "Este contacto ya esta asignado al vendedor " + data.agente +
                          ".");
                  } else if (data.respuesta == 247) { //La sesión caducó
                      window.location.reload();
                  } else if (data.respuesta == 22) {
                      alertSweet("error", "Atención",
                          "No hay vendedores activos. No fue posible asignar los prospectos.");
                  } else {
                    /*
                      document.getElementById("mi-contenido").innerHTML = data.html;
                      document.getElementById("id").value = "";
                      document.getElementById("nmb").value = "";
                      document.getElementById("cp").value = "";
                      document.getElementById("correo").value = "";
                      document.getElementById("telefono").value = "";
                      document.getElementById("code").value = "";
                      document.getElementById("pais").value = "";
                      document.getElementById("obs").value = "";
                      document.getElementById("tel").value = data.tel;
                      document.getElementById("codes").value = data.codes;
                    */
                      $("#btnguardar").prop("disabled", true);
                      if (data.respuesta == 0) {
                          alertSweet("error", "Atención", "No se ha podido registrar el prospecto correctamente");
                      } else if (data.respuesta == 2) {
                          if (data.enviar == 1) {
                              fcaptura();
                          } else {
                              alertSweet("", "Aviso", "La página está llena");
                              /*
                              document.getElementById("mi-contenido").innerHTML = data.html;
                              document.getElementById("nmb").setAttribute("readonly", true);
                              document.getElementById("correo").setAttribute("readonly", true);
                              document.getElementById("cp").setAttribute("readonly", true);
                              document.getElementById("telefono").setAttribute("readonly", true);
                              document.getElementById("obs").setAttribute("readonly", true);
                              */
                          }
                      } else if (data.respuesta == 3) {
                          if (data.enviar == 1) {
                            /*
                              document.getElementById("mi-contenido").innerHTML = data.html;
                              document.getElementById("nmb").setAttribute("readonly", true);
                              document.getElementById("correo").setAttribute("readonly", true);
                              document.getElementById("cp").setAttribute("readonly", true);
                              document.getElementById("telefono").setAttribute("readonly", true);
                              document.getElementById("obs").setAttribute("readonly", true);
                              */
                              fcaptura();
                          }
                          alertSweet("success", "Correcto", "Registro actualizado correctamente");
                          iti.setCountry("mx");
                          document.getElementById("pais").value = "";
                          document.getElementById("code").value = "";
                      } else if(data.respuesta == 1){
                        fcaptura();
                      }

                      if (data.registros >= 2) {
                          guardarOrden.removeAttribute("disabled");
                      } else {
                          guardarOrden.setAttribute("disabled", true);
                      }
                  }
              })
      }
  }

  function editarLead(id) {
      var leadId = document.getElementById(
          "id"); // Cambiar el nombre de la variable para evitar conflicto con el parámetro "id"
      var lead = document.getElementById("lead");
      var nmb = document.getElementById("nmb");
      var cp = document.getElementById("cp");
      var correo = document.getElementById("correo");
      var telefono = document.getElementById("telefono");
      var code = document.getElementById("code");
      var codes = document.getElementById("codes");
      var tel = document.getElementById("tel");
      var pais = document.getElementById("pais");
      var obs = document.getElementById("obs"); // Cambiar el nombre de la variable a "obs"
      var btnguardar = document.getElementById("btnguardar");

      $.ajax({
              url: "query/traerprospecto.php",
              method: "POST",
              dataType: 'json',
              data: {
                  'id': id
              },
          })
          .done(function(data) {
              if (data.respuesta == 0) {
                  Swal.fire({
                      title: 'Atención',
                      text: 'El lead a editar ya tiene un tablero abierto. No es posible hacer cambios al registro.',
                      icon: 'error',
                  }).then((result) => {
                      if (result.isConfirmed) {
                          // El usuario hizo clic en el botón de confirmación
                          window.location.href = "?modulo=prospectos&accion=show&id=" +
                              <?php echo $_GET['id']; ?>;
                      } else {
                          // El usuario cerró la alerta sin hacer clic en el botón de confirmación
                          window.location.href = "?modulo=prospectos&accion=show&id=" +
                              <?php echo $_GET['id']; ?>;
                      }
                  });
              } else {
                  $('#modalNuevoProspecto').modal('show');
                  document.getElementById("btnGuardarProspecto").innerHTML = '<i class="fas fa-save"></i> Actualizar';

                  leadId.value = data.id; // Aquí debes asignar el valor al campo correcto, que parece ser "leadId" y no "id"
                  lead.value = data.lead;
                  nmb.value = data.nmb;
                  cp.value = data.cp;
                  correo.value = data.correo;
                  telefono.value = data.telefono;
                  code.value = data.code;
                  pais.value = data.pais;
                  obs.value = data.obs;
                  /* codes.value = data.codes;
                  tel.value = data.tel; */
                  // Aquí debes utilizar el nombre de la variable corregido a "obs"
                  /* console.log("PAIS: "+pais.value); */
                  // Establece la lada deseada en la instancia de intlTelInput
                  iti.setCountry(data.pais);

                  // Remover el atributo "disabled" de los campos y el botón
                  nmb.removeAttribute("readonly");
                  cp.removeAttribute("readonly");
                  correo.removeAttribute("readonly");
                  telefono.removeAttribute("readonly");
                  obs.removeAttribute("readonly");
                  btnguardar.removeAttribute("disabled");
                  setFocus();
              }
          });
  }

  async function compararTelefono() {
      var code = document.getElementById("code");
      var lead = document.getElementById("lead");
      var pais = document.getElementById("pais");

      var id = document.getElementById("id").value;
      var telefono0 = document.getElementById("telefono").value;
      var telefonosCadena = document.getElementById("tel").value;
      var codesCadena = document.getElementById("codes").value;
      var telefonosArray = telefonosCadena.split(',');
      var codesArray = codesCadena.split(',');

      var coincidencias = false;

      if (id !== "") {
          console.log("Entra");
          try {
              const data = await $.ajax({
                  url: "query/buscartel.php",
                  method: "POST",
                  data: {
                      'id': id,
                      "lead": lead.value,
                      "pais": pais.value,
                      "telefono": telefono0,
                      "code": code.value
                  },
              });

              if (data == 1) {
                  coincidencias = true;
              } else {
                  coincidencias = false;
              }
          } catch (error) {
              console.error("Error en la solicitud AJAX:", error);
          }
      } else {
          for (var i = 0; i < telefonosArray.length; i++) {
              var numeroTelefono = telefonosArray[i].trim();
              var codeTelefono = codesArray[i].trim();
              var telefonoConcat = code.value + telefono0;
              var telefonoConcatArray = codeTelefono + numeroTelefono;
              console.log("telefonoConcat: " + telefonoConcat + " === telefonoConcatArray: " + telefonoConcatArray);

              if (telefonoConcat === telefonoConcatArray) {
                  coincidencias = true;
                  break;
              }
          }
      }

      return coincidencias;
  }


  function borrarLead(id) {
      Swal.fire({
          title: 'Atención',
          html: "¿Estás seguro de borrar este registro?<br>Esta opción es irreversible.",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          cancelButtonText: 'Cancelar',
          confirmButtonText: 'Estoy seguro'
      }).then((result) => {
          if (result.isConfirmed) {
              var guardarOrden = document.getElementById("guardarOrden");
              var lead = document.getElementById("lead").value;
              var leadId = document.getElementById(
                  "id"); // Cambiar el nombre de la variable para evitar conflicto con el parámetro "id"
              var nmb = document.getElementById("nmb");
              var cp = document.getElementById("cp");
              var correo = document.getElementById("correo");
              var telefono = document.getElementById("telefono");
              var obs = document.getElementById("obs"); // Cambiar el nombre de la variable a "obs"
              var btnguardar = document.getElementById("btnguardar");
              $.ajax({
                      url: "query/eliminarprospecto.php",
                      method: "POST",
                      dataType: 'json',
                      data: {
                          'id': id,
                          "lead": lead
                      },
                  })
                  .done(function(data) {
                      document.getElementById("mi-contenido").innerHTML = data.html;
                      nmb.removeAttribute("readonly");
                      cp.removeAttribute("readonly");
                      correo.removeAttribute("readonly");
                      telefono.removeAttribute("readonly");
                      obs.removeAttribute("readonly");
                      btnguardar.setAttribute("disabled", true);
                      leadId.value = "";
                      nmb.value = "";
                      cp.value = "";
                      correo.value = "";
                      telefono.value = "";
                      obs.value = "";
                      tel.value = data.tel;
                      document.getElementById("codes").value = data.codes;
                      if (data.registros >= 2) {
                          guardarOrden.removeAttribute("disabled");
                      } else {
                          guardarOrden.setAttribute("disabled", true);
                      }
                      setFocus();
                  });
          }
      })
  }

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

  function cambiarvendedor(k) {
      var original = document.getElementById("venoriginal" + k);
      var vendedor = document.getElementById("idvendedor" + k);

      Swal.fire({
          title: 'Atención?',
          text: "¿Estás seguro de cambiar el vendedor de este lead?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'De acuerdo',
          cancelButtonText: 'Cancelar'
      }).then((result) => {
          if (result.isConfirmed) {
              $.ajax({
                      url: "query/cambiarvendedor.php",
                      method: "POST",
                      data: {
                          "id": k,
                          "vendedor": vendedor.value
                      },
                  })
                  .done(function(data) {
                      if (data == 0) {
                          Toast.fire({
                              icon: 'success',
                              title: 'Lead actualizado correctamente'
                          })
                          original.value = vendedor.value;
                      } else if (data == 2) {
                          Swal.fire({
                              title: 'Atención',
                              text: 'El lead a editar ya tiene un tablero abierto. No es posible hacer cambios al registro.',
                              icon: 'error',
                          }).then((result) => {
                              if (result.isConfirmed) {
                                  // El usuario hizo clic en el botón de confirmación
                                  window.location.href = "?modulo=prospectos&accion=show&id=" +
                                      <?php echo $_GET['id']; ?>;
                              } else {
                                  // El usuario cerró la alerta sin hacer clic en el botón de confirmación
                                  window.location.href = "?modulo=prospectos&accion=show&id=" +
                                      <?php echo $_GET['id']; ?>;
                              }
                          });

                      } else {
                          //Oucrrió un error al actualizar el lead
                          Toast.fire({
                              icon: 'error',
                              title: 'Oucrrió un error al actualizar el lead'
                          })
                          vendedor.value = original.value;
                      }
                  })
          } else {
              vendedor.value = original.value;
          }
      })

  }


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
      $.ajax({
              url: "query/buscarprospectos.php",
              method: "POST",
              data: {
                  "lead": "<?php echo $_GET["id"]; ?>"
              },
          })
          .done(function(data) {
              if (data > 0) {
                  Swal.fire({
                      title: "Atención",
                      html: "¿Que acción deseas realiza con los prospectos capturados antes de finalizar la hoja?",
                      icon: "question",
                      showCancelButton: true,
                      confirmButtonColor: "#3085d6",
                      cancelButtonColor: "#d33",
                      confirmButtonText: "Finalizar y asignar",
                      cancelButtonText: "Cancelar",
                      showDenyButton: true,
                      denyButtonColor: "teal",
                      denyButtonText: "Finalizar y NO asignar",
                      allowOutsideClick: false,
                  }).then((result) => {
                      if (result.isConfirmed) {
                          //Finalizó y asignó
                          window.location.href = "?modulo=prospectos&accion=finalizaryasignar&id=" + id;
                      } else if (result.isDenied) {
                          //Finalizó y NO asignó
                          window.location.href = "?modulo=prospectos&accion=finalizar&id=" + id;
                      }
                  });
              } else {
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
                      allowOutsideClick: false
                  }).then((result) => {
                      if (result.isConfirmed) {
                          window.location.href = "?modulo=prospectos&accion=finalizar&id=" + id;
                      }

                  });
              }
          })
  }

  //Inicio de las funciones de la lada
  let input = document.querySelector("#telefono");
  let iti = window.intlTelInput(input, {
      initialCountry: "auto",
      geoIpLookup: function(callback) {
          //console.log(callback);
          $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
              const countryCode = (resp && resp.country) ? resp.country : "us";
              callback(countryCode);
          });
      },
      hiddenInput: "full_phone",
      formatOnDisplay: false,
      separateDialCode: true,
      utilsScript: "https://s3-us-west-2.amazonaws.com/s.cdpn.io/32471/utils.js",
  });

  iti.promise.then(function() {
      var fullNumber = iti.getSelectedCountryData().dialCode;
      document.getElementById("code").value = fullNumber;

      var selectedCountry = iti.getSelectedCountryData().iso2;
      document.getElementById("pais").value = selectedCountry;
  });

  input.addEventListener("input", function() {
      updateCodeValue();
  });

  // Función para asignar el valor de la lada al input con id "code"
  function updateCodeValue() {
      var fullNumber = iti.getSelectedCountryData().dialCode;
      document.getElementById("code").value = fullNumber;

      var selectedCountry = iti.getSelectedCountryData().iso2;
      document.getElementById("pais").value = selectedCountry;
  }

  // Agrega un manejador de eventos al documento (o al elemento contenedor)
  document.addEventListener("click", function(event) {
      updateCodeValue()
  });
  //Fin de la funciones de la lada
  </script>
  <?php
}
  }
  ?>