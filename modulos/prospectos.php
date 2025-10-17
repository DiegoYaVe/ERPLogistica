<?php
ini_set('display_errors', 0);
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
      $estado = $_POST['estado'];

      // OJO: observacion viene de TinyMCE (HTML). NO la conviertas a mayúsculas.
      $internacional = filter_var($_POST['internacional'] ?? 0, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

      $this->model->setdata(
            "NULL",
            $_POST['ugen'],
            $fini[0],
            $fini[1],
            "NULL",
            "A",
            $_POST['observacion'],
            $estado, // pasar estado
            $internacional     // 0 o 1 ya normalizado
          );

      $this->model->insert();

      // Guarda archivos (si vienen)
      $this->model->save_files($this->model->idt, $_FILES['mediaFiles'] ?? null);

      $this->insertorden();
      redirect('?modulo=prospectos&accion=show&id='.$this->model->idt);
    }

    function update(){
      $idHoja = intval($_POST['idHoja'] ?? 0);
      if ($idHoja <= 0) { die(json_encode(['ok'=>false,'msg'=>'id inválido'])); }

      $fini = explode("T", $_POST['fini']);
      $estado = $_POST['estado'];

      $this->model->setdata(
        $idHoja,
        $_POST['ugen'],
        $fini[0],
        $fini[1],
        NULL, // no tocamos hfin aquí
        "A",
        $_POST['observacion'], // HTML crudo (TinyMCE)
        $estado
      );

      $this->model->update();  // nuevo en el modelo
      $this->model->save_files($idHoja, $_FILES['mediaFiles'] ?? null);

      // Si llamas por AJAX espera JSON; si no, redirecciona:
      if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['ok'=>true]); 
        return;
      }
      redirect('?modulo=prospectos&accion=show&id='.$idHoja);
    }

    function get_hoja(){
      die("Entra");
      $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
      if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['ok'=>false,'msg'=>'id inválido']);
        exit;
      }

      $row = $this->model->getHoja($id);

      header('Content-Type: application/json; charset=utf-8');
      echo json_encode([
        'ok' => $row ? true : false,
        'idHoja' => $row['cc_id'] ?? null,
        'estado' => $row['cc_estado'] ?? '',
        'fini' => isset($row['cc_fini']) ? date('Y-m-d\TH:i:s', strtotime($row['cc_fini'])) : '',
        'ugen' => $row['cc_ugen'] ?? '',
        'observacionHTML' => $row['cc_observacion'] ?? ''
      ]);
      exit; // MUY IMPORTANTE: evita que se renderice el layout
    }

    function list_files(){
      $id = intval($_GET['id'] ?? 0);
      $sql = 'SELECT ca_id, ca_filename, ca_filepath 
              FROM crm_capturaleads_archivos 
              WHERE cc_id = "'.$id.'" 
              ORDER BY ca_id DESC';
      $res = setq($sql);
      $files = [];
      while($r = $res->fetch_assoc()){
        $files[] = [
          'id' => $r['ca_id'],
          'filename' => $r['ca_filename'],
          'url' => $r['ca_filepath']   // Debe ser URL accesible (relativa pública)
        ];
      }
      header('Content-Type: application/json');
      echo json_encode($files);
    }

    function delete_file(){
      $id = intval($_POST['id'] ?? 0);
      $sql = 'SELECT ca_filepath FROM crm_capturaleads_archivos WHERE ca_id = "'.$id.'"';
      $res = setq($sql);
      $row = $res->fetch_array();
      if ($row) {
        $path = $_SERVER['DOCUMENT_ROOT'] . $row['ca_filepath'];
        if (is_file($path)) @unlink($path);
        setq('DELETE FROM crm_capturaleads_archivos WHERE ca_id = "'.$id.'"');
      }
      header('Content-Type: application/json');
      echo json_encode(['ok'=>true]);
    }

    function show(){
      if (!isset($_REQUEST['fini'])) $_REQUEST['fini'] = date('Y-m-d',strtotime('-30 days'));
      if (!isset($_REQUEST['ffin'])) $_REQUEST['ffin'] = date('Y-m-d'); 
      if (!isset($_REQUEST['cliente'])) $_REQUEST['cliente'] = NULL;
      if (!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = NULL;

      /* $grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');*/
      if (!isset($_REQUEST['vendedor'])) $_REQUEST['vendedor'] = $_SESSION['uid'];
      
      $this->model->resultlead($_GET['id'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['cliente'],$_REQUEST['estatus'],$_REQUEST['vendedor']);
      $this->view = new viewprospectos($this->model);
      $this->view->show($_GET['id'],$_REQUEST['fini'],$_REQUEST['ffin'],$_REQUEST['cliente'],$_REQUEST['estatus'],$_REQUEST['vendedor']);


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
      
                                                                                                                                                      
        $this->model->setdatalead("",$_POST['lead'],$_POST['nmb'], $_POST['comentarios'], $_POST['cp'],$_POST['correo'],$_POST['telefono'],$_POST['code'],$_POST['pais'],"P",$_POST['empresa'],$vendedor,$ncaptura,$_SESSION['uid'],"NULL",date("Y-m-d"));
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
    function setdata($id, $ugen, $fini, $hini, $hfin, $estatus, $observacion, $estado, $internacional){
      mb_internal_encoding("UTF-8");
      $this->id = $id;
      $this->ugen = clearvmayus($ugen);
      $this->fini = $fini;
      $this->hini = $hini;
      $this->hfin = $hfin; // puede ser NULL
      $this->estatus = clearvmayus($estatus);
      $this->observacion = $observacion; // HTML crudo, no upper
      $this->estado = clearvmayus($estado);
      $this->internacional = $internacional;
    }

    function insert(){
      $sql = 'INSERT INTO crm_capturaleads SET
              cc_fini = "'.$this->fini.'",
              cc_ugen = "'.$this->ugen.'",
              cc_hini = "'.$this->hini.'",
              cc_hfin = '.$this->hfin.',
              cc_estatus = "'.$this->estatus.'",
              cc_observacion = "'.$this->observacion.'",
              cc_estado = "'.$this->estado.'",
              cc_internacional ="'.$this->internacional.'" '; // nuevo campo
      setq($sql);

      $this->idt = getmax('cc_id','crm_capturaleads',false,false);
    }


    function setdatalead($id,$lead,$nmb,$comentarios,$cp,$correo,$telefono,$code,$pais,$estatus,$empresa,$vendedor,$ncaptura,$ucaptura,$hasigna,$fechaasigna,$consecutivo){
      $this->id = $id;
      $this->lead = $lead;
      $this->nmb = clearvmayus($nmb);
      $this->comentarios = $comentarios;
      $this->cp = $cp;
      $this->correo = $correo;
      $this->telefono = $telefono;
      $this->code = $code;
      $this->pais = $pais;
      $this->estatus = clearvmayus($estatus);
      $this->empresa = clearvmayus($empresa);
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
              cl_empresa = "'.$this->empresa.'",
              cl_comentarios = "'.$this->comentarios.'",
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
              cl_fasigna = "'.$this->fasigna.'",
              cl_fechalimite = "'.date("Y-m-d", strtotime("+7 days")).'"';
      $result = setq($sql);
      if ($result !== false) {
        $respuesta = 1; //La consulta fue exitosa
      } else{
        $respuesta = 0; //La consulta NO fue exitosa
      }
    
      return($respuesta);
    }
    
    function update(){
      $hfinSQL = is_null($this->hfin) ? 'NULL' : '"'.$this->hfin.'"';

      $sql = 'UPDATE crm_capturaleads SET
                cc_fini = "'.$this->fini.'",
                cc_ugen = "'.$this->ugen.'",
                cc_hini = "'.$this->hini.'",
                cc_hfin = '.$hfinSQL.',
                cc_estatus = "'.$this->estatus.'",
                cc_observacion = "'.mysqli_real_escape_string($GLOBALS['mysqli'],$this->observacion).'",
                cc_estado = "'.$this->estado.'"
              WHERE cc_id = "'.$this->id.'"';
      setq($sql);
    }

    function save_files($cc_id, $files){
      if (empty($files) || empty($files['name'])) return;

      $baseDir = '/uploads/prospectos/'.$cc_id.'/';      // URL pública relativa
      $absDir  = $_SERVER['DOCUMENT_ROOT'].$baseDir;     // ruta física
      if (!is_dir($absDir)) @mkdir($absDir, 0775, true);

      $names = $files['name'];
      $tmp   = $files['tmp_name'];
      $errs  = $files['error'];

      for ($i=0; $i < count($names); $i++){
        if ($errs[$i] !== UPLOAD_ERR_OK) continue;

        $safe = preg_replace('/[^a-zA-Z0-9._-]/','_', $names[$i]);
        $final = uniqid().'_'.$safe;

        if (move_uploaded_file($tmp[$i], $absDir.$final)) {
          $sql = 'INSERT INTO crm_capturaleads_archivos
                    (cc_id, ca_filename, ca_filepath, ca_uploaded_by)
                  VALUES
                    ("'.$cc_id.'","'.$safe.'","'.$baseDir.$final.'","'.($_SESSION['uid'] ?? '').'")';
          setq($sql);
        }
      }
    }

    function resultlead($id, $fini, $ffin, $proveedor, $estatus, $vendedor){
      /* $sql = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_fasigna = "'.date("Y-m-d").'" ORDER BY cl_estatus = "P" DESC, cl_id DESC'; */
      $sql = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_estatus != "R" AND cl_estatus != "X"';
      if ($fini || $ffin) {
        $sql .= ' AND DATE(cl_fasigna) BETWEEN "' . addslashes($fini) . '" AND "' . addslashes($ffin) . '"';
      }
      if ($vendedor) {
        $sql .= ' AND cl_ucaptura = "'.$vendedor.'"';
      }

      if ($estatus) {
        $sql .= ' AND cl_observacion = "'.$estatus.'"';
      }

      $sql .= ' ORDER BY cl_estatus = "P" DESC, cl_id DESC';
      $this->result = setq($sql);

      /* $sql = 'SELECT * FROM crm_leads WHERE cl_lead = "'.$id.'" AND cl_fasigna = "'.date("Y-m-d").'" AND cl_estatus = "P" ORDER BY cl_id DESC'; */
      $sql0 = 'SELECT * FROM crm_leads WHERE cl_estatus = "P" AND cl_fasigna = "'.date("Y-m-d").'" AND cl_lead = "'.$id.'"';

      if ($fini || $ffin) {
        $sql0 .= ' AND DATE(cl_fasigna) BETWEEN "' . addslashes($fini) . '" AND "' . addslashes($ffin) . '"';
      }
      
      if ($vendedor) {
        $sql0 .= ' AND cl_ucaptura = "'.$vendedor.'"';
      }

      if ($estatus) {
        $sql0 .= ' AND cl_observacion = "'.$estatus.'"';
      }

      $sql0 .= ' ORDER BY cl_id DESC';
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

    function getHoja($id){
      $sql = 'SELECT cc_id, cc_estado, cc_fini, cc_ugen, cc_observacion
              FROM crm_capturaleads
              WHERE cc_id = "'.$id.'" LIMIT 1';
      $res = setq($sql);
      if (!$res || $res->num_rows===0) return null;
      return $res->fetch_array();
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



    <select name="estatus" id="estatus" class="form-control" >';
    ?>
      <option value="" <?= ($estatus === "") ? "selected" : "" ?>>Todos</option>
      <option value="No iniciado" <?= ($estatus === "No iniciado") ? "selected" : "" ?>>No iniciado</option>
      <option value="Correo Enviado" <?= ($estatus === "Correo Enviado") ? "selected" : "" ?>>Correo Enviado</option>
      <option value="No localizado" <?= ($estatus === "No localizado") ? "selected" : "" ?>>No localizado</option>
      <option value="Reunión concretada" <?= ($estatus === "Reunión concretada") ? "selected" : "" ?>>Reunión concretada</option>
      <option value="Grupo Creado" <?= ($estatus === "Grupo Creado") ? "selected" : "" ?>>Grupo Creado</option>
      <option value="Buzón" <?= ($estatus === "Buzón") ? "selected" : "" ?>>Buzón</option>
      <option value="Negado" <?= ($estatus === "Negado") ? "selected" : "" ?>>Negado</option>
      <option value="Contactar Nuevamente" <?= ($estatus === "Contactar Nuevamente") ? "selected" : "" ?>>Contactar Nuevamente</option>
    <?php echo '
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
// Limpia TODO el modal de hoja
function resetHojaModal() {
  // 1) Quita cualquier handler que hayas agregado en "editar"
  $('#newtablero').off('shown.bs.modal._loadFiles');

  // 2) Reset del form
  const form = document.getElementById('formNuevaHoja');
  if (form) form.reset();

  // 3) Valores de control
  $('#idHoja').val('');
  $('#modo').val('insert');
  $('#internacional').prop('checked', false);

  // 4) Limpia editor
  if (tinymce.get('observacion')) tinymce.get('observacion').setContent('');

  // 5) Limpia archivos (nuevos y existentes)
  const inputFiles = document.getElementById('mediaFiles');
  const newFilesList = document.getElementById('newFilesList');
  if (inputFiles) inputFiles.value = '';
  if (newFilesList) { newFilesList.innerHTML=''; newFilesList.classList.add('d-none'); }
  $('#existingFilesList').empty();

  // 6) Refresca fecha/hora local actual para #fini (YYYY-MM-DDTHH:mm:ss)
  const pad = n => String(n).padStart(2,'0');
  const now = new Date();
  const valNow = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
  $('#fini').val(valNow);

  // (Opcional) Si tus IDs reales son otros para el título/botón, cámbialos aquí
  $('#myModalLabel33').text('Nueva hoja de prospectos'); // título visible
  // $('#btnRegistrarHoja').text('Registrar hoja nueva'); // si quieres cambiar el texto del botón
}
</script>

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
            // Limpia SIEMPRE antes de abrir el modal
              resetHojaModal();
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
                  <form method="post" action="?modulo=prospectos&accion=insert" enctype="multipart/form-data" autocomplete="off" id="formNuevaHoja">
                    <input type="hidden" id="idHoja" name="idHoja" value="">
                    <input type="hidden" id="modo" name="modo" value="insert">                    
                  <div class="modal-body row">
                    <div class="col-md-12 col-xs-12">
                      <label>Nombre de la hoja:</label>
                      <div class="mb-5">
                        <div class="position-relative has-icon-left">
                          <input type="text" name="estado" id="estado" class="form-control text-uppercase" oninput="this.value = this.value.toUpperCase();" required placeholder="Ingresa el nombre de la hoja">
                          <div class="form-control-position">
                            <i class="icon-map"></i>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-check mb-4">
                      <input class="form-check-input" type="checkbox" value="1" id="internacional" name="internacional">
                      <label class="form-check-label" for="internacional">
                        ¿Internacional? <small class="text-muted">(desmarcado = Nacional)</small>
                      </label>
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
                        <div class="d-flex align-items-center justify-content-between">
                          <label class="mb-1">Plantillas de correo:</label>
                          <small class="text-muted ms-2">Selecciona para precargar el contenido</small>
                        </div>
                        <div class="mb-3">
                          <select id="tplSelector" class="form-control">
                            <option value="">-- Selecciona una plantilla --</option>
                            <!-- aquí JS inyecta opciones -->
                          </select>
                        </div>
                      </div>

                      <div class="col-md-12 col-xs-12">
                        <label>Correo electrónico personalizado: </label>
                        <div class="mb-5">
                          <textarea id="observacion" class="form-control" rows="6" name="observacion"
                                    placeholder="Escribe un texto personalizado"></textarea>
                        </div>
                      </div>

                      <!-- Archivos multimedia -->
                      <div class="col-md-12 col-xs-12">
                        <label>Archivos multimedia (múltiples): </label>
                        <div class="mb-2">
                          <input type="file" id="mediaFiles" name="mediaFiles[]" class="form-control" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
                        </div>
                        <!-- archivos nuevos (antes de enviar) -->
                        <div id="newFilesList" class="list-group mb-2 d-none"></div>
                        <!-- archivos ya guardados (cuando edita) -->
                        <div>
                          <label class="mb-1">Archivos existentes:</label>
                          <div id="existingFilesList" class="list-group small"></div>
                        </div>
                      </div>

                      <div class="col-12" hidden>
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

            echo '<script src="assets/tinymce/tinymce.min.js"></script>';

            echo "<script>
  tinymce.init({
    selector: '#observacion',
    height: 280,
    menubar: false,
    plugins: 'link lists code table autoresize emoticons',
    toolbar: 'undo redo | bold italic underline | bullist numlist | link | table | emoticons | removeformat | code',
    branding: false,
license_key: 'gpl',
    base_url: 'assets/tinymce',
    suffix: '.min'
  });
</script>
";
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
                        <th width="10%"><b>Nombre</b></th>
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

        var modo = document.getElementById("modo").value;
        $.ajax({
          url: "query/validarestadohoja.php",
          type: "POST",
          data: { estado: estado },
          dataType: "json",
          success: function (data) {
            if (data.existe && modo != "update") {
              Swal.fire({
                icon: "warning",
                title: "Estado duplicado",
                text: "Ya existe una hoja activa von ese nombre. ¿Deseas ser redirigido a la hoja?",
                showCancelButton: true,
                confirmButtonText: "Ir a la hoja",
                cancelButtonText: "Cancelar"
              }).then((result) => {
                if (result.isConfirmed) {
                  window.location.href = "?modulo=prospectos&accion=show&id=" + data.id;
                }
              });
            } else ';
            echo " 
            if(modo == 'update'){
            const form = document.getElementById('formNuevaHoja');
            const fd = new FormData(form);

            // TinyMCE HTML
            if (tinymce.get('observacion')) {
              fd.set('observacion', tinymce.get('observacion').getContent());
            }

            // modo: insert | update
            const modo = $('#modo').val() || 'insert';
            fd.set('modo', modo);

            // en update, manda idHoja
            if (modo === 'update') {
              fd.set('idHoja', $('#idHoja').val());
            }

            fd.set('internacional', $('#internacional').is(':checked') ? 1 : 0);

            $.ajax({
              url: 'query/save_hoja.php',
              type: 'POST',
              data: fd,
              contentType: false,
              processData: false,
              dataType: 'json',
              success: function (r) {
                if (r && r.ok) {
                  $('#newtablero').modal('hide');
                  if (window.myTable && window.myTable.ajax) {
                    window.myTable.ajax.reload(null, false);
                  } else {
                    location.reload();
                  }
                } else {
                  alert((r && r.msg) ? r.msg : 'No fue posible guardar.');
                }
              },
              error: function (xhr) {
                console.error('save_hoja.php fail', xhr.status, xhr.responseText);
                alert('Error al enviar el formulario.');
              }
            });
            } else {
              form.submit();
            }
          }
        });
      });
      

      </script>";
      ?>
      <script>
          // previsualizar archivos NUEVOS (antes de enviar)
          const inputFiles = document.getElementById('mediaFiles');
          const newFilesList = document.getElementById('newFilesList');

          if (inputFiles) {
            inputFiles.addEventListener('change', () => {
              newFilesList.innerHTML = '';
              if (inputFiles.files.length > 0) {
                newFilesList.classList.remove('d-none');
                [...inputFiles.files].forEach((f, idx) => {
                  const item = document.createElement('div');
                  item.className = 'list-group-item d-flex justify-content-between align-items-center';
                  item.innerHTML = `
                    <span class="text-truncate" style="max-width:75%">${f.name} <small class="text-muted">(${(f.size/1024).toFixed(1)} KB)</small></span>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-index="${idx}">
                      <i class="fa fa-times"></i> Quitar
                    </button>`;
                  newFilesList.appendChild(item);
                });

                // quitar archivo de la selección (creando un nuevo FileList)
                newFilesList.addEventListener('click', (e) => {
                  const btn = e.target.closest('[data-remove-index]');
                  if (!btn) return;
                  const idx = parseInt(btn.getAttribute('data-remove-index'),10);
                  const dt = new DataTransfer();
                  [...inputFiles.files].forEach((f, i) => { if(i!==idx) dt.items.add(f); });
                  inputFiles.files = dt.files;
                  // refresco vista
                  const evt = new Event('change');
                  inputFiles.dispatchEvent(evt);
                }, {once:true});
              } else {
                newFilesList.classList.add('d-none');
              }
            });
          }

          // abrir modal MODO NUEVO (global para poder llamarla desde otros lados si quieres)
          window.openNuevoTablero = function(){
            resetHojaModal();
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('newtablero'));
            modal.show();
          };

          window.editarHoja = function(idHoja){
            console.log('Editar hoja con ID:', idHoja);
            resetHojaModal();
            $('#idHoja').val(idHoja);
            $('#modo').val('update');
            $('#modal-title-text').text('Editar hoja de prospectos');
            $('#btnText').text('Guardar cambios');

            const inputFiles = document.getElementById('mediaFiles');
            const newFilesList = document.getElementById('newFilesList');
            if (inputFiles) inputFiles.value = '';
            if (newFilesList) { newFilesList.innerHTML = ''; newFilesList.classList.add('d-none'); }

            $.getJSON('query/get_hoja.php?id=' + idHoja, function(res){
              $('#estado').val(res.estado || '');
              $('#fini').val(res.fini || '');
              $('#ugen').val(res.ugen || '');
              if (tinymce.get('observacion')) tinymce.get('observacion').setContent(res.observacionHTML || '');

              // <-- ámbito
              $('#internacional').prop('checked', !!(+res.internacional)); // 1 -> true

              // después muestras y cargas archivos
              $('#newtablero')
                .off('shown.bs.modal._loadFiles')
                .on('shown.bs.modal._loadFiles', function(){
                  window.loadExistingFiles(idHoja);
                });
              $('#newtablero').modal('show');
            })
            .fail(function(xhr){
              console.error('get_hoja falló', xhr.status, xhr.responseText);
              Swal.fire('Error','No se pudo cargar la hoja.','error');
            });
          };

          window.enviarCorreosHoja = function(idHoja){
            Swal.fire({
              icon: 'question',
              title: '¿Enviar correos masivos?',
              text: 'Se enviará un correo individual a cada lead único por correo de esta hoja.',
              showCancelButton: true,
              confirmButtonText: 'Sí, enviar',
              cancelButtonText: 'Cancelar'
            }).then((res) => {
              if (!res.isConfirmed) return;

              $.ajax({
                url: 'query/send_bulk_emails.php',
                type: 'POST',
                dataType: 'json',
                data: { idHoja: idHoja },
                success: function(r){
                  if (r && r.ok) {
                    const detalle = `Total leads: ${r.total}\nCorreos únicos: ${r.unicos}\nEnviados: ${r.enviados}\nFallidos: ${r.fallidos}`;
                    Swal.fire('Envío completado', detalle, 'success');
                  } else {
                    Swal.fire('Error', (r && r.msg) ? r.msg : 'No se pudo completar el envío.', 'error');
                  }
                },
                error: function(xhr){
                  console.error('send_bulk_emails.php fail', xhr.status, xhr.responseText);
                  Swal.fire('Error', 'Fallo la petición al servidor.', 'error');
                }
              });
            });
          }


    // Carga la lista de archivos existentes
      window.loadExistingFiles = function(idHoja){
        console.log("[loadExistingFiles] start, idHoja=", idHoja);

        const list = $('#existingFilesList');
        if (!list.length) {
          console.warn('[loadExistingFiles] #existingFilesList no existe aún en el DOM');
        }
        list.empty().append('<div class="list-group-item text-muted">Cargando...</div>');

        // Usa el mismo backend que ya tienes funcionando; si usas el módulo, mejor:
        $.ajax({
          //url: '?modulo=prospectos&accion=list_files',   // <-- si ya lo tienes en tu controlador
          url: 'query/list_archivos.php',             // <-- si lo pusiste como archivo suelto
          type: 'GET',
          data: { id: idHoja },
          dataType: 'json',
          cache: false
        })
        .done(function(files){
          console.log('[loadExistingFiles] ok, files=', files);
          list.empty();
          if(!files || !files.length){
            list.append('<div class="list-group-item text-muted">Sin archivos</div>');
            return;
          }
          files.forEach(function(f){
            const row = $(`
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div class="text-truncate" style="max-width:58%">
                  <i class="fa fa-paperclip me-1"></i>
                  <a href="${f.url}" target="_blank" class="text-truncate">${f.filename}</a>
                </div>
                <div class="btn-group">
                  <a href="${f.url}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-eye"></i> Ver
                  </a>
                  <a href="${f.url}" download class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-download"></i> Descargar
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-del-file" data-file-id="${f.id}">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </div>`);
            list.append(row);
          });
        })
        .fail(function(xhr){
          console.error('[loadExistingFiles] fail', xhr.status, xhr.responseText);
          $('#existingFilesList').empty()
            .append('<div class="list-group-item text-danger">No se pudieron cargar los archivos.</div>');
        });
      };

      // Eliminar archivo existente (delegado)
      $(document).on('click', '.btn-del-file', function(){
        const fileId = $(this).data('file-id');
        if(!confirm('¿Eliminar este archivo?')) return;

        $.post('query/delete_archivo.php', { id:fileId }, function(r){
          if (r && r.ok) {
            const idHoja = $('#idHoja').val();
            loadExistingFiles(idHoja);
          } else {
            Swal.fire('Error', r.msg || 'No se pudo eliminar el archivo', 'error');
          }
        }, 'json').fail(function(xhr){
          Swal.fire('Error', 'No se pudo eliminar (error de red).', 'error');
        });
      });
          // listar archivos existentes (privada; la usa editarHoja)
          function loadExistingFiles(idHoja){
            const list = $('#existingFilesList');
            list.empty();
            $.getJSON('?modulo=prospectos&accion=list_files&id=' + idHoja, function(files){
              if(!files || files.length===0){
                list.append('<div class="list-group-item text-muted">Sin archivos</div>');
                return;
              }
              files.forEach(f => {
                const row = $(`
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="${f.url}" target="_blank" class="text-truncate" style="max-width:75%">${f.filename}</a>
                    <div>
                      <a href="${f.url}" download class="btn btn-sm btn-outline-secondary"><i class="fa fa-download"></i></a>
                      <button type="button" class="btn btn-sm btn-outline-danger btn-del-file" data-file-id="${f.id}">
                        <i class="fa fa-trash"></i>
                      </button>
                    </div>
                  </div>`);
                list.append(row);
              });
            });
          }

          // eliminar archivo existente (delegado)
          $(document).on('click', '.btn-del-file', function(){
            const fileId = $(this).data('file-id');
            if(!confirm('¿Eliminar este archivo?')) return;
            $.post('?modulo=prospectos&accion=delete_file', { id:fileId }, function(){
              const idHoja = $('#idHoja').val();
              loadExistingFiles(idHoja);
            }, 'json');
          });

          // submit (insert/update) con archivos
          $('#btnRegistrarHoja').on('click', function(){
            

        })();
      </script>

      <script>
      (function(){
        const uId = "<?php echo addslashes($_SESSION['uid'] ?? ''); ?>";
        const selector = '#observacion'; // tu editor
        const sel = document.getElementById('tplSelector');

        // Asegura que Tiny esté listo antes de trabajar con él
        function withEditorReady(cb){
          const inst = tinymce.get('observacion');
          if (inst) { cb(inst); return; }
          const iv = setInterval(() => {
            const ed = tinymce.get('observacion');
            if (ed) { clearInterval(iv); cb(ed); }
          }, 80);
          // timeout opcional si quieres abortar después de X segundos
        }

        // Rellena opciones del select
        function loadTplTitles(){
          if (!sel) return;
          // limpia dejando el placeholder
          sel.innerHTML = '<option value="">-- Selecciona una plantilla --</option>';
          $.getJSON('query/user_tpl_titles.php', { u_id: uId }, function(r){
            if (!r || !r.ok) return;
            r.items.forEach(it => {
              const opt = document.createElement('option');
              opt.value = it.id;
              opt.textContent = it.title || ('Plantilla #'+it.id);
              sel.appendChild(opt);
            });
          });
        }

        // Cuando se abra el modal, cargamos títulos (para que siempre esté fresco)
        $('#newtablero').on('shown.bs.modal', function(){
          loadTplTitles();
        });

        // Al cambiar de plantilla: pedir confirmación y cargar HTML al editor
        sel?.addEventListener('change', function(){
          const tplId = parseInt(this.value || '0', 10);
          if (!tplId) return;

          Swal.fire({
            title: 'Usar esta plantilla',
            text: 'Esto reemplazará el contenido actual del correo personalizado. ¿Continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, reemplazar',
            cancelButtonText: 'Cancelar'
          }).then(result => {
            if (!result.isConfirmed) {
              this.value = ''; // vuelve al placeholder
              return;
            }
            $.getJSON('query/user_tpl_get.php', { id: tplId, u_id: uId }, function(r){
              if (r && r.ok) {
                withEditorReady(ed => {
                  ed.setContent(r.html || '');
                  // opcional: enfocar editor
                  ed.focus();
                });
              } else {
                Swal.fire('Error', (r && r.msg) ? r.msg : 'No se pudo cargar la plantilla', 'error');
              }
              // vuelve al placeholder para evitar re-cargar al abrir el modal
              sel.value = '';
            }).fail(xhr => {
              console.error('user_tpl_get fail', xhr.status, xhr.responseText);
              Swal.fire('Error','Fallo la petición','error');
              sel.value = '';
            });
          });
        });

        // Si tú mismo llamas a openNuevoTablero() o editarHoja(), Tiny ya existe;
        // si no, dale un pequeño delay para asegurar init antes de interacciones:
        // setTimeout(loadTplTitles, 300);

      })();
      </script>

      <?php


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
    
function show($idd,$fini,$ffin,$proveedor,$estatus_lead,$vendedor){      
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
    $otro = '<button type="button"
            onClick="enviarCorreosHoja('.$_GET['id'].');"
            class="btn btn-sm btn-info"
            title="Enviar correos a todos los leads de esta hoja">
      <i class="fa fa-envelope"></i> Correos
    </button>';
    $finalizar = '<button type="button" onClick="finalizar('.$_GET['id'].')" class="btn btn-sm btn-danger"><i class="fas fa-window-close"></i>Finalizar hoja</button>';
    
    ?>
    <script>
      window.enviarCorreosHoja = function(idHoja){
                Swal.fire({
                  icon: 'question',
                  title: '¿Enviar correos masivos?',
                  text: 'Se enviará un correo individual a cada lead único por correo de esta hoja.',
                  showCancelButton: true,
                  confirmButtonText: 'Sí, enviar',
                  cancelButtonText: 'Cancelar'
                }).then((res) => {
                  if (!res.isConfirmed) return;

                  $.ajax({
                    url: 'query/send_bulk_emails.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { idHoja: idHoja },
                    success: function(r){
                      if (r && r.ok) {
                        const detalle = `Total leads: ${r.total}\nCorreos únicos: ${r.unicos}\nEnviados: ${r.enviados}\nFallidos: ${r.fallidos}`;
                        Swal.fire('Envío completado', detalle, 'success');
                      } else {
                        Swal.fire('Error', (r && r.msg) ? r.msg : 'No se pudo completar el envío.', 'error');
                      }
                    },
                    error: function(xhr){
                      console.error('send_bulk_emails.php fail', xhr.status, xhr.responseText);
                      Swal.fire('Error', 'Fallo la petición al servidor.', 'error');
                    }
                  });
                });
              }
      </script>
    <?php
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
        $vendedores = '';

         

             $filtro = '
          <form class="" role="form" method="post" action="?modulo=prospectos&accion=show&id='.$_GET['id'].'" id="filtro">
            <div class="mb-5">
              <label for="">Cliente</label>
              <input type="text" name="cliente" id="cliente" placeholder="Nombre del cliente" class="form-control" value="'.$proveedor.'" />
            </div>';
            $filtro .='<div class="mb-5">
                <label for="tipom">Asesor</label>';
            //if($grupo == "GERENCIA" || $grupo == "ADMIN" || $grupo == "FINANZAS"){
              $sqlvd = 'SELECT * FROM usuarios WHERE u_grupo != "CHOFER"';
              $resultvd = setq($sqlvd);
              $filtro .= '<select id="vendedor" name="vendedor" class="form-control">
              <option value="" selected>TODOS</option>';
              while($rowvd = $resultvd -> fetch_array()){
                if($rowvd['u_id'] == $vendedor) $sel = 'selected';  
                else $sel = '';
                $filtro.='<option value="'.$rowvd['u_id'].'" '.$sel.'>'.$rowvd['u_nmb'].' '.$rowvd['u_apellidos'].'</option>';
              }
              $filtro .= '</select>';
              /*
            } else {
              $nmb = busca($_SESSION['uid'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
              $filtro .= '<input type="text" name="vendedor" id="vendedor" class="form-control" value="'.$nmb.'" readonly/>';
            }
              */
            $filtro .= '</div>';
            $filtro .='<div class="mb-5">
              <label for="tipom">Desde</label>
              <input type="date" name="fini" id="fini" class="form-control" value="'.$fini.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Hasta</label>
              <input type="date" name="ffin" id="ffin" class="form-control" value="'.$ffin.'" />
            </div>
            <div class="mb-5">
              <label for="tipom">Estatus</label>
              <select class="form-control" name="estatus" id="estatus" >';

                $filtro .= '<option value=""'.(($estatus_lead==='')?' selected':'').'>Todos</option>';
                $filtro .= '<option value="No iniciado"'.(($estatus_lead==='No iniciado')?' selected':'').'>No iniciado</option>';
                $filtro .= '<option value="Correo Enviado"'.(($estatus_lead==='Correo Enviado')?' selected':'').'>Correo Enviado</option>';
                $filtro .= '<option value="No localizado"'.(($estatus_lead==='No localizado')?' selected':'').'>No localizado</option>';
                $filtro .= '<option value="Reunión concretada"'.(($estatus_lead==='Reunión concretada')?' selected':'').'>Reunión concretada</option>';
                $filtro .= '<option value="Grupo Creado"'.(($estatus_lead==='Grupo Creado')?' selected':'').'>Grupo Creado</option>';
                $filtro .= '<option value="Buzón"'.(($estatus_lead==='Buzón')?' selected':'').'>Buzón</option>';
                $filtro .= '<option value="Negado"'.(($estatus_lead==='Negado')?' selected':'').'>Negado</option>';
                $filtro .= '<option value="Contactar Nuevamente"'.(($estatus_lead==='Contactar Nuevamente')?' selected':'').'>Contactar Nuevamente</option>';

            $filtro .= '
                </select>
            </div><!-- form group [search] -->
            <div class="mb-5">
              <label for="">Acciones</label> <br>
              <button type="button" onclick="mandar(0);" class="btn btn-info">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-filter"></i> Filtrar
              </button>
              <a href="?modulo=remisiones&accion=index"><button type="button" class="btn btn-warning">
                <span class="glyphicon glyphicon-record"></span> <i class="fas fa-times-circle"></i> Limpiar
              </button></a>
            </div>
          </form>';

        if($estatus == "A"){
          toolbar($_GET['modulo'],$atras,$filtro,$finalizar.$otro.$vendedores, $detalles);
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
            <h5 class="modal-title" id="modalNuevoProspectoLabel">Prospecto</h5>
            <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <form id="formNuevoProspecto" autocomplete="off">
              <!-- NUEVO: id para edición -->
              <input type="hidden" id="id" name="id" value="">
              <!-- FIX: comilla faltante en value -->
              <input type="hidden" id="lead" name="lead" value="' . htmlspecialchars($_GET['id'] ?? '', ENT_QUOTES, 'UTF-8') . '">
              <input type="hidden" id="code" name="code" value="">
              <input type="hidden" id="pais" name="pais" value="">
              <div class="row g-2">
                <div class="col-md-6">
                  <label for="empresa" class="form-label">Empresa</label>
                  <input type="text" id="empresa" name="empresa" class="form-control">
                </div>
                <div class="col-md-6">
                  <label for="telefono" class="form-label">Teléfono *</label>
                  <input type="number" id="telefono" name="telefono" maxlength="12" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label for="nmb" class="form-label">Nombre completo *</label>
                  <input type="text" id="nmb" name="nmb" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label for="cp" class="form-label">Código Postal</label>
                  <input type="number" id="cp" name="cp" class="form-control">
                </div>
                <div class="col-md-6">
                  <label for="correo" class="form-label">Correo electrónico</label>
                  <input type="email" id="correo" name="correo" class="form-control">
                </div>
                <div class="col-md-6">
                  <label for="comentarios" class="form-label">Comentarios</label>
                  <textarea id="comentarios" name="comentarios" class="form-control" rows="4" required></textarea>
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
    </div>';


          ?>

          <?php
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

          ?>
          <div class="card mt-3">
            <div class="card-body row" >
              <div class="col-md-12 col-12 col-sm-12">
                <style>
            /* --- Ajustes de tabla --- */
            #myTable { width: 100% !important; }
            #myTable thead th { white-space: nowrap; }
            #myTable tbody td { vertical-align: middle; }

            /* Cols con ancho mínimo para evitar “brincos” */
            .col-ico   { position: sticky; left: 0; background: #fff; z-index: 2; width: 48px; }
            .col-emp   { min-width: 180px; }
            .col-nom   { min-width: 180px; }
            .col-tel   { min-width: 150px; white-space: nowrap; }
            .col-mail  { min-width: 220px; }
            .col-fecha { min-width: 120px; }
            .col-acerc { min-width: 100px; text-align:center; }
            .col-acc   { min-width: 120px; }
            .col-com   { min-width: 260px; }

            /* Comentarios truncados con ellipsis */
            .comment-clip {
              max-width: 360px;
              display: inline-block;
              overflow: hidden;
              text-overflow: ellipsis;
              white-space: nowrap;
              vertical-align: bottom;
            }

            /* Select de estatus coloreado */
            .estatus-select { font-weight: 600; }
            .estatus-select option[value="No iniciado"]           { background:#00bcd4; color:#fff; }
            .estatus-select option[value="Correo Enviado"]        { background:#4caf50; color:#fff; }
            .estatus-select option[value="No localizado"]         { background:#f57c00; color:#fff; }
            .estatus-select option[value="Reunion concretada"]    { background:#1565c0; color:#fff; }
            .estatus-select option[value="Grupo Creado"]          { background:#00897b; color:#fff; }
            .estatus-select option[value="Buzon"]                 { background:#ffeb3b; color:#000; }
            .estatus-select option[value="Negado"]                { background:#424242; color:#ff4d4d; }
            .estatus-select option[value="Contactar Nuevamente"]  { background:#b71c1c; color:#fff; }

            /* Botón “Acciones” compacto */
            .btn-actions { white-space: nowrap; }
            .scroll-x { overflow-x: auto; }              /* el scroll vive aquí */
      #tblWrap { overflow: visible !important; }   /* no recortar dropdowns */
      /* por si algún padre crea stacking contexts */
      .dropdown-menu { z-index: 2000; }            /* por encima de cards/toolbars */
          </style>

          <script>
            function aplicarColorSelect(select) {
              let color = "#00bcd4", textColor = "white";
              switch (select.value) {
                case "Correo Enviado": color="#4caf50"; break;
                case "No localizado": color="#f57c00"; break;
                case "Reunion concretada": color="#1565c0"; break;
                case "Grupo Creado": color="#00897b"; break;
                case "Buzon": color="#ffeb3b"; textColor="black"; break;
                case "Negado": color="#424242"; textColor="red"; break;
                case "Contactar Nuevamente": color="#b71c1c"; break;
                case "": case "No iniciado": default: color="#00bcd4";
              }
              select.style.backgroundColor = color;
              select.style.color = textColor;
            }
            document.addEventListener("DOMContentLoaded", function() {
              document.querySelectorAll(".estatus-select").forEach(s => {
                aplicarColorSelect(s);
                s.addEventListener("change", function(){ aplicarColorSelect(this); });
              });
              // Tooltips Bootstrap 5 (si usas Bootstrap 5)
              if (window.bootstrap) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                  return new bootstrap.Tooltip(tooltipTriggerEl);
                });
              }
            });
          </script>
                <div class="scroll-x">
                <div class="table-responsive overflow-visible" style="height: 500px;" id="tblWrap">
                  <center>
                    <table id="myTable">
                      <thead class="bg-light-blue bg-darken-2">
                        <tr>
                    <th class="col-ico"></th>
                    <th class="col-emp"><b>Empresa</b></th>
                    <th class="col-nom"><b>Nombre</b></th>
                    <th class="col-tel"><b>Teléfono</b></th>
                    <th class="col-mail"><b>Correo</b></th>
                    <th class="col-fecha"><b>Fecha Límite</b></th>
                    <th class="col-fecha"><b></b></th>
                    <th class="col-acerc"><b>Acercamiento</b></th>
                    <th class="col-acc"></th>
                    <th class="col-com"><b>Comentarios</b></th>
                  </tr>
                      </thead>
                      <tbody>
                        <?php
                        
                  while($row = $this->model->result->fetch_array()){
                    $nmbcompleto = $nombres[$row['cl_vendedor']];
                    $html = html_entity_decode($texto);
                    $texto = str_replace('<br>', "\n", $html);
                    $texto = strip_tags($texto);
                    $texto_codificado = urlencode($texto);
                    if (empty($texto)) {
                      $texto_codificado = urlencode("¡Hola, un gusto saludarte!");
                    }

                    $numero_de_telefono = $row['cl_telefono'];
                    $link_whatsapp = "https://api.whatsapp.com/send?phone=$numero_de_telefono&text=$texto_codificado";

                    if ($row['cl_estatus'] == "A") {
                      $estatusdesc = "Asignado";
                    } else if ($row['cl_estatus'] == "R" && $grupo != "ADMIN" && $grupo != "GERENCIA") {
                      $estatusdesc = "Asignado";
                    } else if ($row['cl_estatus'] == "R") {
                      $estatusdesc = "Reasignado";
                    } else {
                      $estatusdesc = "Sin negociación";
                      $acciones = '';
                    }
                    $check = ($row['cl_acercamiento'] == 0) ? "" : " checked disabled";

                    // --- NUEVO: acciones en dropdown compacto ---
                    $acciones = '
                    <div class="dropdown">
                      <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                              data-bs-toggle="dropdown" data-bs-display="static">
                        Acciones
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item" href="javascript:void(0)" onclick="iniciarTablero('.$row['cl_id'].')">
                            <i class="fas fa-book-open me-2"></i>Iniciar tablero
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item" href="javascript:void(0)" onclick="editarLead('.$row['cl_id'].')">
                            <i class="bi bi-pencil-fill me-2"></i>Editar prospecto
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="borrarLead('.$row['cl_id'].')">
                            <i class="fa fa-trash me-2"></i>Eliminar
                          </a>
                        </li>
                        
                      </ul>
                    </div>';


    $checkb = '<input class="form-check-input consult-check" onclick="marcarAcercamiento('.$row['cl_id'].')"
                                      type="checkbox" name="select'.$row['cl_id'].'" id="select'.$row['cl_id'].'" '.$check.'>';

                    // Fechas / badge
                    $fasignaDt = !empty($row['cl_fasigna']) ? new DateTime($row['cl_fasigna']) : new DateTime('today');
                    if (!empty($row['cl_fechalimite'])) {
                      $limiteDt = new DateTime($row['cl_fechalimite']);
                    } else {
                      $limiteDt = (clone $fasignaDt)->modify('+7 days');
                    }
                    $fechaContactoStr = $limiteDt->format('d-m-Y');

                    $hoy = new DateTime('today');
                    $amarilloDesde = (clone $limiteDt)->modify('-2 days');
                    $badgeClass = 'badge bg-success';
                    if ($hoy > $limiteDt) {
                      $badgeClass = 'badge bg-danger';
                    } elseif ($hoy >= $amarilloDesde) {
                      $badgeClass = 'badge bg-warning text-dark';
                    }

                    $rutaIcono = busca($row['cl_ucaptura'], 'usuarios', 'u_id', 'u_icono');
                    $correoSafe = htmlspecialchars($row['cl_correo'] ?? '', ENT_QUOTES, 'UTF-8');
                    $comentSafe = htmlspecialchars($row['cl_comentarios'] ?? '', ENT_QUOTES, 'UTF-8');

      // 4) Render de fila
    echo '
                    <tr>
                      <td class="col-ico"><img src="'.$rutaIcono.'" alt="icono" style="width:24px; height:24px; object-fit:contain;"></td>
                      <td class="col-emp">'.$row['cl_empresa'].'</td>
                      <td class="col-nom">'.$row['cl_nmb'].'</td>
                      <td class="col-tel">
                        <a href="'.$link_whatsapp.'" target="_blank" class="text-decoration-none">
                          '.$row['cl_telefono'].'
                          <i class="fab fa-whatsapp ms-1" style="font-size: 18px;"></i>
                        </a>
                      </td>
                      <td class="col-mail">
                        <a href="mailto:'.$correoSafe.'">'.$correoSafe.'</a>
                      </td>
                      <td class="col-fecha">
                        <span class="'.$badgeClass.'" data-bs-toggle="tooltip" title="Vigencia de 7 días desde la asignación">'.$fechaContactoStr.'</span>
                      </td>
                      <td class="col-mail">
                        <div class="col-12">
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
                      </td>
                      <td class="col-acerc">
                        '.$checkb.'
                      </td>
                      <td class="col-acc">
                        '.$acciones.'
                      </td>
                      <td class="col-com">
                        <span class="comment-clip" title="'.$comentSafe.'">'.$comentSafe.'</span>
                      </td>
                    </tr>';

                          }
                        echo '</tbody>';
                      echo '</table>
                    </center>
                  </div>
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
                  ';

                  echo '
                  <script>
                    function mandar(id){
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
              echo '';
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
              echo '';
            }
            if($pageres<=9){
            }else{
              if($_REQUEST['page'] == ($pageres-5)) $nombre = ($pageres-2);
              else $nombre = '... '.($pageres-2);
              if($_REQUEST['page'] == $i) $active = "active";
              else $active = "";
              if($_REQUEST['page'] >= ($pageres-4)) echo '';
              else echo '';
              echo '';
            }
            if($_REQUEST['page'] == ($pageres-1) || $pageres <= 1) $hidden2 = 'style="pointer-events: none;
            background: #70707026;
            color: black;"';
            else $hidden2 = '';
            echo '
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
                window.location.reload();
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
          document.getElementById("empresa").value = "";
          document.getElementById("comentarios").value = "";

          document.getElementById("pais").value = "";
          
          // Reinicia el texto del botón por si vienes de una edición
          document.getElementById("btnGuardarProspecto").innerHTML = '<i class="fas fa-save"></i> Guardar';

          // Resetea validaciones o focus si usas
          $("#btnguardar").prop("disabled", true);

          // Restablece la lada por defecto
          iti.setCountry("mx");
      });

          if(document.getElementById("btnguardar")){
          document.getElementById("btnguardar").addEventListener("click", function() {
            // Deshabilita el botón al hacer clic
            this.disabled = true;

            // Habilita el botón nuevamente después de 2 segundos
            setTimeout(function() {
                /* document.getElementById("btnguardar").disabled = false; */
            }, 2000); // 2000 milisegundos = 2 segundos
          });
          }


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

          var empresa = document.getElementById("empresa").value;
          var comentarios = document.getElementById("comentarios").value;
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
                          'comentarios': comentarios,
                          "cp": cp,
                          "correo": correo,
                          "telefono": telefono,
                          "code": code,
                          "pais": pais,
                          "empresa": empresa,
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
                              window.location.reload();
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
      // cache de inputs
      var $id       = document.getElementById("id");
      var $lead     = document.getElementById("lead");
      var $nmb      = document.getElementById("nmb");
      var $cp       = document.getElementById("cp");
      var $correo   = document.getElementById("correo");
      var $telefono = document.getElementById("telefono");
      var $code     = document.getElementById("code");
      var $pais     = document.getElementById("pais");
      var $empresa      = document.getElementById("empresa");
      var $comentarios      = document.getElementById("comentarios");
      var $btnSave  = document.getElementById("btnGuardarProspecto"); // usa el id real del botón

      $.ajax({
        url: "query/traerprospecto.php",
        method: "POST",
        dataType: "json",
        data: { id: id }
      })
      .done(function(data) {
        // Si tu endpoint regresa {respuesta:0|1, ...}
        if (Number(data.respuesta) === 0) {
          Swal.fire({
            title: "Atención",
            text: "El lead a editar ya tiene un tablero abierto. No es posible hacer cambios al registro.",
            icon: "error"
          }).then(() => {
            window.location.href = "?modulo=prospectos&accion=show&id=<?php echo (int)($_GET['id'] ?? 0); ?>";
          });
          return;
        }

        // Abre el modal primero
        $('#modalNuevoProspecto').modal('show');

        // Cambia el texto del botón
        if ($btnSave) $btnSave.innerHTML = '<i class="fas fa-save"></i> Actualizar';

        // Normaliza nombres del JSON (usa el que llegue)
        var d = data.data ? data.data : data; // por si viene anidado

        // Soporta ambas convenciones cl_* o simple
        var v = {
          id:        d.id        ?? d.cl_id       ?? id,
          lead:      d.lead      ?? d.cl_lead     ?? ($lead ? $lead.value : ""),
          nmb:       d.nmb       ?? d.cl_nmb      ?? "",
          cp:        d.cp        ?? d.cl_cp       ?? "",
          correo:    d.correo    ?? d.cl_correo   ?? "",
          telefono:  d.telefono  ?? d.cl_tel      ?? "",
          code:      d.code      ?? d.cl_code     ?? "",
          pais:      d.pais      ?? d.cl_pais     ?? "",
          empresa:       d.empresa       ?? d.cl_empresa ?? "",
          comentarios:       d.comentarios       ?? d.cl_comentarios ?? ""
        };

        // Rellena los campos (solo si existen en el DOM)
        if ($id)       $id.value       = v.id;
        if ($lead)     $lead.value     = v.lead;
        if ($nmb)      $nmb.value      = v.nmb;
        if ($cp)       $cp.value       = v.cp;
        if ($correo)   $correo.value   = v.correo;
        if ($telefono) $telefono.value = v.telefono;
        if ($code)     $code.value     = v.code;
        if ($pais)     $pais.value     = v.pais;
        if ($empresa)      $empresa.value      = v.empresa;
        if ($comentarios)      $comentarios.value      = v.comentarios;

        // Protege el uso de intl-tel-input si existe
        if (window.iti && typeof iti.setCountry === "function") {
          var paisLower = (v.pais || "mx").toLowerCase();
          try { iti.setCountry(paisLower); } catch(e) { /* ignora */ }
        }

        // Si en algún flujo bloqueaste inputs/btn, re-habilita:
        if ($nmb)      $nmb.removeAttribute("readonly");
        if ($cp)       $cp.removeAttribute("readonly");
        if ($correo)   $correo.removeAttribute("readonly");
        if ($telefono) $telefono.removeAttribute("readonly");
        if ($empresa)      $empresa.removeAttribute("readonly");
        if ($comentarios)      $comentarios.removeAttribute("readonly");
        if ($btnSave)  $btnSave.removeAttribute("disabled");
      })
      .fail(function(xhr) {
        console.error(xhr);
        alert("Error de red al cargar el prospecto.");
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
                  var empresa = document.getElementById("empresa");
                  var comentarios = document.getElementById("comentarios");

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
                          window.location.reload();
                          document.getElementById("mi-contenido").innerHTML = data.html;
                          nmb.removeAttribute("readonly");
                          cp.removeAttribute("readonly");
                          correo.removeAttribute("readonly");
                          telefono.removeAttribute("readonly");
                          empresa.removeAttribute("readonly");
                          btnguardar.setAttribute("disabled", true);
                          leadId.value = "";
                          nmb.value = "";
                          comentarios.value = "";
                          cp.value = "";
                          correo.value = "";
                          telefono.value = "";
                          empresa.value = "";
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