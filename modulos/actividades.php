<?php
  //ini_set('display_errors', 1);
  class actividades{
    var $model;
    var $view;
    function __construct(){
      $this->model = new modelactividades(isset($obj));
    }
    function index(){
      if(!isset($_REQUEST['tipo'])) $_REQUEST['tipo'] = "T";
      if(!isset($_REQUEST['estatus'])) $_REQUEST['estatus'] = "T";
      if(isset($_GET['ida'])) {
        $estnot = busca($_GET['ida'],'crm_actividadesd','cad_user = "'.$_SESSION['uid'].'" AND cad_actividad','cad_notificacion');
        if($estnot == 1){
          $sqlnot = 'UPDATE crm_actividadesd SET cad_notificacion = "0" WHERE cad_user = "'.$_SESSION['uid'].'" AND cad_actividad = "'.$_GET['ida'].'"';
          setq($sqlnot);
          echo '<script>
            window.location.reload();
          </script>';
        }
      }
      else $_GET['ida'] = NULL;
      $this->model->resultactividades($_REQUEST['tipo'],$_REQUEST['estatus']);
      $this->view = new viewactividades($this->model);
      $this->view->browse($_REQUEST['tipo'],$_REQUEST['estatus'],$_GET['ida']);
    }
    function show(){
      if(!isset($_REQUEST['usuario'])) $_REQUEST['usuario'] = $_SESSION['uid'];
      $this->model->json($_REQUEST['usuario']);
      $this->view = new viewactividades($this->model);
      $this->view->show($_REQUEST['usuario']);
      
    }
    /*function insertact(){
      if(!isset($_POST['liga'])) $_POST['liga'] = NULL;
      if(!isset($_GET['tablero'])) $tablero = 0;
      else $tablero = $_GET['tablero'];

      $this->model->SetDataActividad(NULL,$tablero,$_POST['actividad'],$_POST['nmbac'],$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['fini'],$_POST['duracion'],$_POST['showas'],$_POST['descripcion'],$_POST['lugar'],$_POST['liga'],$_POST['ubicacion']);
      $this->model->idt = $this->model->insertact();

      include('usuarios.php');
      $user = new modelusuarios();
      $user->select($_SESSION['uid']);

      $this->model->SetDataActdd($this->model->idt,"U",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_SESSION['uid']);
      $this->model->Invitadosact();

      if($_POST['responsable'] != $_SESSION['uid']){
        $user->select($_POST['responsable']);
        $this->model->SetDataActdd($this->model->idt,"R",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['responsable']);
        $this->model->Invitadosact();
      }
      if(isset($_POST['inv'])){
        for($i=0;$i<sizeof($_POST['inv']);$i++){
          $user->select($_POST['inv'][$i]);
          if(busca($_POST['inv'],'crm_actividadesd','cad_actividad='.$this->model->idt.' AND cad_user','COUNT(*)') == 0){
            $this->model->SetDataActdd($this->model->idt,"I",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['inv']);
            $this->model->Invitadosact();
          }
        }
      }

      if(isset($_GET['tablero'])){
        $this->model->SetDataActdd($this->model->idt,"C",$_POST['destino'],$_POST['telefono'],$_POST['correo'],NULL,"A",NULL);
        $this->model->Invitadosact();
      }else{
        for($i=0;$i<sizeof($_POST['ext']);$i++){
          $this->model->SetDataActdd($this->model->idt,"E",$_POST['ext'][$i],$_POST['teli'][$i],$_POST['corr'][$i],NULL,"A",NULL);
          $this->model->Invitadosact();
        }
      }

      redirect('?modulo=tableros&accion=show&id='.$this->model->tablero);
    }
    function updateact(){
      if(!isset($_POST['liga'])) $_POST['liga'] = NULL;

      $this->model->SetDataActividad($_GET['idact'],$_GET['tablero'],$_POST['actividad'],$_POST['nmbac'],$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['fini'],$_POST['duracion'],$_POST['showas'],$_POST['descripcion'],$_POST['lugar'],$_POST['liga'],$_POST['ubicacion']);
      $this->model->idt = $this->model->updateact();

      $this->model->SetDataActdd($_GET['idact'],"C",$_POST['destino'],$_POST['telefono'],$_POST['correo'],NULL,"A",NULL);
      $this->model->UpdInvitadosact();

      redirect('?modulo=tableros&accion=show&id='.$this->model->tablero);
    }*/
    function insertact(){
      if(!isset($_POST['liga'])) $_POST['liga'] = NULL;
      if(!isset($_GET['tablero'])) $tablero = 0;
      else $tablero = $_GET['tablero'];

      $this->model->SetDataActividad(NULL,$tablero,$_POST['actividad'],$_POST['nmbac'],$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['fini'],$_POST['duracion'],$_POST['showas'],$_POST['descripcion'],$_POST['lugar'],$_POST['liga'],$_POST['ubicacion']);
      $this->model->idt = $this->model->insertact();

      include('usuarios.php');
      $user = new modelusuarios();
      $user->select($_SESSION['uid']);

      $this->model->SetDataActdd($this->model->idt,"U",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_SESSION['uid'],NULL);
      $this->model->Invitadosact();

      if($_POST['responsable'] != $_SESSION['uid']){
        $user->select($_POST['responsable']);
        $this->model->SetDataActdd($this->model->idt,"R",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['responsable'],NULL);
        $this->model->Invitadosact();
      }
      if(isset($_POST['inv'])){
        for($i=0;$i<sizeof($_POST['inv']);$i++){
          $user->select($_POST['inv'][$i]);
          if(busca($_POST['inv'][$i],'crm_actividadesd','cad_actividad='.$this->model->idt.' AND cad_user','COUNT(*)') == 0){
            $this->model->SetDataActdd($this->model->idt,"I",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['inv'][$i],NULL);
            $this->model->Invitadosact();
          }
        }
      }

      if(isset($_GET['tablero'])){
        $this->model->SetDataActdd($this->model->idt,"C",$_POST['destino'],$_POST['telefono'],$_POST['correo'],NULL,"A",NULL,NULL);
        $this->model->Invitadosact();
      }
      if(isset($_POST['ext'])){
        for($i=0;$i<sizeof($_POST['ext']);$i++){
          $this->model->SetDataActdd($this->model->idt,"E",$_POST['ext'][$i],$_POST['teli'][$i],$_POST['corr'][$i],NULL,"A",NULL,NULL);
          $this->model->Invitadosact();
        }
      }
      /*if (isset($_SESSION['access_token']) && $_SESSION['access_token'] != "") {
        if($this->model->tablero == 0) $_SESSION['accionT'] = redirect('?modulo=actividades&accion=index');
        else $_SESSION['accionT'] = redirect('?modulo=tableros&accion=show&id='.$this->model->tablero);
        require_once 'vendor/autoload.php';
        $client = new Google_Client();
        $client->setAuthConfig("vendor/credentials.json");
        //$client->addScope('https://www.googleapis.com/auth/calendar');
        //$client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
        $client->setAccessType("offline");
        $client->setPrompt("consent");
        //$client->authenticate($_GET['code']);
        //$_SESSION['refresh_token'] = $client->getRefreshToken();
        crearActividad($_POST['nmbac'],$_POST['descripcion'],$_POST['fini'],$_POST['lugar'],$_POST['correoresp'],$_SESSION['accionT']);
      }else{
        if($this->model->tablero == 0) $_SESSION['nredirect'] = "actividad";
        else $_SESSION['nredirect'] = $this->model->tablero;
        crearToken($_POST['nmbac'],$_POST['descripcion'],$_POST['fini'],$_POST['lugar'],$_POST['correoresp']);
      }*/

      if(isset($_GET['cal'])) $link = '?modulo=actividades&accion=show';
      elseif($this->model->tablero == 0) $link = '?modulo=actividades&accion=index';
      else $link = '?modulo=tableros&accion=show&id='.$this->model->tablero;

      redirect($link);
    }
    function updateact(){
      if(!isset($_POST['liga'])) $_POST['liga'] = NULL;
      if(isset($_GET['origen']) && $_GET['origen'] == "1") $_GET['tablero'] = 0;
      
      $this->model->SetDataActividad($_GET['idact'],$_GET['tablero'],$_POST['actividad'],$_POST['nmbac'],$_POST['destino'],$_POST['telefono'],$_POST['correo'],$_POST['fini'],$_POST['duracion'],$_POST['showas'],$_POST['descripcion'],$_POST['lugar'],$_POST['liga'],$_POST['ubicacion']);
      $this->model->updateact();

      include('usuarios.php');
      $user = new modelusuarios();

      if(isset($_POST['inv'])){
        for($i=0;$i<sizeof($_POST['inv']);$i++){
          $user->select($_POST['inv'][$i]);
          if(isset($_POST['idi'.$_POST['inv'][$i]])){
            $this->model->SetDataActdd($_GET['idact'],"I",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['inv'][$i],$_POST['idi'.$_POST['inv'][$i]]);
          }else{
            if(busca($_POST['inv'],'crm_actividadesd','cad_actividad='.$_GET['idact'].' AND cad_user','COUNT(*)') == 0){
              $this->model->SetDataActdd($_GET['idact'],"I",$user->nmb.' '.$user->apellidos,$user->telefono,$user->mailcorp,NULL,"A",$_POST['inv'][$i],NULL);
              $this->model->Invitadosact();
            }
          }
        }
      }

      if(isset($_GET['tablero'])){
        $this->model->SetDataActdd($_GET['idact'],"C",$_POST['destino'],$_POST['telefono'],$_POST['correo'],NULL,"A",NULL,$_POST['idcliente']);
        $this->model->UpdInvitadosact();
      }
      $externo = $_POST['ex'];
      if(isset($_POST['ext'])){
        for($i=0;$i<sizeof($_POST['ext']);$i++){
          if($_POST['ex'] > 0 && $externo != 0){
            $this->model->SetDataActdd($_GET['idact'],"E",$_POST['ext'][$i],$_POST['teli'][$i],$_POST['corr'][$i],NULL,"A",NULL,$_POST['ide'.($i+1)]);
            $this->model->UpdInvitadosact();
            $externo --;
          }else{
            $this->model->SetDataActdd($_GET['idact'],"E",$_POST['ext'][$i],$_POST['teli'][$i],$_POST['corr'][$i],NULL,"A",NULL,NULL);
            $this->model->Invitadosact();
          }
        }
      }

      if(isset($_GET['cal'])) $link = '?modulo=actividades&accion=show';
      elseif($this->model->tablero == 0) $link = '?modulo=actividades&accion=index';
      else $link = '?modulo=tableros&accion=show&id='.$this->model->tablero;

      redirect($link);
    }
    function estatusactividad(){
      $sql = 'UPDATE crm_actividades SET ca_estatus = "'.mb_strtoupper($_GET['estatus']).'"';
      if(isset($_GET['desc'])) $sql .= ', ca_descripcion = "'.clearvmayus($_GET['desc']).'"';
      $sql .= 'WHERE ca_id = "'.$_GET['id'].'"';
      setq($sql);

      if($_SESSION['emp'] == "1"){
        $soporte = buscajdsuite($_GET['id'],'licencias_adicionalesd','lad_actividad','lad_soporte');
        if($soporte){
          if($_GET['estatus'] == "F"){
            $duracion = 1;
            $hres = buscajdsuite($soporte,'licencias_adicionales','la_id','la_hrestante');
            $hres = $hres - $duracion;
            $sqlupd = 'UPDATE licencias_adicionales SET la_hrestante = "'.$hres.'"';
            if($hres == 0) $sqlupd .= ' , la_estatus = "F"';
            $sqlupd .= '  WHERE la_id = "'.$soporte.'"';
            setqjdsuite($sqlupd);

            $sqlup2 = 'UPDATE licencias_adicionalesd SET lad_estatus = "F" WHERE lad_actividad = "'.$_GET['id'].'"';
            setqjdsuite($sqlup2);
          }else{
            $sqlupd = 'UPDATE licencias_adicionalesd SET lad_estatus = "C", lad_actividad = "0" WHERE lad_actividad = "'.$_GET['id'].'"';
            setqjdsuite($sqlupd);
          }
        }
      }

      if(!isset($_GET['origen']) || $_GET['origen'] == 2) {
        $tablero = busca($_GET['id'],'crm_actividades','ca_id','ca_tablero');
        redirect('?modulo=tableros&accion=show&id='.$tablero);
      }else redirect('?modulo=actividades&accion=index');
    }
    function deladjact(){
      $tablero = busca($_GET['idact'],'crm_actividades','ca_id','ca_tablero');
      $this->model->deladjact($_GET['idact']);

      $link = '?modulo=actividades&accion=index&ida='.$_GET['idact'];

      redirect($link);
    }
  }

  class modelactividades{
    function select(){
      $sql = 'SELECT * FROM crm_actividades WHERE ca_tablero = "0"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->ida = $row['ca_id'];
      $this->tableroa = $row['ca_tablero'];
      $this->accion = $row['ca_accion'];
      $this->nmb = $row['ca_nmb'];
      $this->hora = $row['ca_hora'];
      $this->duracion = $row['ca_duracion'];
      $this->lugar = $row['ca_lugar'];
      $this->ubicacion = $row['ca_ubicacion'];
      $this->adjunto = $row['ca_adjunto'];
      $this->descripcion = $row['ca_descripcion'];
      $this->liga = $row['ca_liga'];
      $this->vercomo = $row['ca_vercomo'];
      $this->estatus = $row['ca_estatus'];
    }
    function resultactividades($tipo,$estatus){
      $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
      
      $sql = 'SELECT crm_actividades.*,crm_acciones.ca_icono icon,crm_actividadesd.cad_user,crm_actividadesd.cad_tipomiembro FROM crm_actividades
              INNER JOIN crm_acciones ON crm_actividades.ca_accion = crm_acciones.ca_id
              INNER JOIN crm_actividadesd ON crm_actividades.ca_id = crm_actividadesd.cad_actividad 
              WHERE 1 = 1 GROUP BY ca_id';

      if($grupo != "ADMIN" && $grupo != "GERENCIA") $sql .= ' AND crm_actividadesd.cad_user = "'.$_SESSION['uid'].'"';           
      if($estatus == "X") $sql.= ' AND crm_actividades.ca_estatus IN ("C") ';
      elseif($estatus == "S") $sql.= ' AND crm_actividades.ca_estatus IN ("N") ';
      elseif($estatus == "F") $sql.= ' AND crm_actividades.ca_estatus IN ("F") ';
      elseif($estatus == "T") $sql.= '  ';
      if($tipo != "T") $sql.=' AND crm_actividades.ca_accion = "'.$tipo.'" ';
      $sql.=' ORDER BY crm_actividades.ca_estatus DESC, crm_actividades.ca_fecha DESC,crm_actividades.ca_hora ASC';
      $this->resultac = setq($sql);
    }
    function SetDataActividad($id,$tablero,$actividad,$nmbac,$destino,$telefono,$correo,$fini,$duracion,$showas,$descripcion,$lugar,$liga,$ubicacion){
      $fini = str_replace('T',' ',$fini);

      $this->id = $id;
      $this->tablero = $tablero;
      $this->actividad = clearvmayus($actividad);
      $this->nmbac = clearvmayus($nmbac);
      $this->fecha = date('Y-m-d',strtotime($fini));
      $this->hora = date('H:i:s',strtotime($fini));
      $this->duracion = $duracion;
      $this->showas = $showas;
      $this->descripcion = clearvmayus($descripcion);
      $this->lugar = clearvmayus($lugar);
      $this->liga = clearvminus($liga);
      $this->ubicacion = clearvminus($ubicacion);
    }
    function insertact(){
      $sql = 'INSERT INTO crm_actividades SET
              ca_tablero = "'.$this->tablero.'",
              ca_accion = "'.$this->actividad.'",
              ca_nmb = "'.$this->nmbac.'" ,
              ca_fecha = "'.$this->fecha.'",
              ca_hora = "'.$this->hora.'",
              ca_duracion = "'.$this->duracion.'",
              ca_lugar = "'.$this->lugar.'",
              ca_ubicacion = "'.$this->ubicacion.'",
              ca_descripcion = "'.$this->descripcion.'",
              ca_vercomo = "'.$this->showas.'"';
      setq($sql);
      $maxcu = getmax("ca_id","crm_actividades",false,false);

      if (isset($_FILES['adjunto']['name'])) {
        $diradj = 'adjuntos/actividades';
        if (!is_dir($diradj)) {
          @mkdir($diradj, 0777);
        }
        $info = new SplFileInfo(basename($_FILES['adjunto']['name']));
        $nombre_archivo = $_FILES['adjunto']['name'];
        $tamano_archivo = $_FILES['adjunto']['size'];
        $ext = $info->getExtension();
        $target = $diradj.'/'.$nombre_archivo;
        if($tamano_archivo > 3000000) $error = "6XMUE2";
        else{
          if (move_uploaded_file($_FILES['adjunto']['tmp_name'],  $target)){
            $sql = 'UPDATE crm_actividades SET ca_adjunto = "'.$nombre_archivo.'", ca_adjuntoext = "'.$ext.'"
                    WHERE ca_id = "'.$maxcu.'"';
            setq($sql);
          }
        }
      }

      return($maxcu);
    }
    function updateact(){
      $sql = 'UPDATE crm_actividades SET
              ca_nmb = "'.$this->nmbac.'" ,
              ca_fecha = "'.$this->fecha.'",
              ca_hora = "'.$this->hora.'",
              ca_duracion = "'.$this->duracion.'",
              ca_lugar = "'.$this->lugar.'",
              ca_ubicacion = "'.$this->ubicacion.'",
              ca_descripcion = "'.$this->descripcion.'",
              ca_vercomo = "'.$this->showas.'"
              WHERE ca_id = "'.$this->id.'"';
      setq($sql);
      
      if (isset($_FILES['adjunto']['name'])) {
        $diradj = 'adjuntos/actividades';
        if (!is_dir($diradj)) {
          @mkdir($diradj, 0777);
        }
        $info = new SplFileInfo(basename($_FILES['adjunto']['name']));
        $nombre_archivo = $_FILES['adjunto']['name'];
        $tamano_archivo = $_FILES['adjunto']['size'];
        $ext = $info->getExtension();
        $target = $diradj.'/'.$nombre_archivo;
        if($tamano_archivo > 3000000) $error = "6XMUE2";
        else{
          if (move_uploaded_file($_FILES['adjunto']['tmp_name'],  $target)){
            $sql = 'UPDATE crm_actividades SET ca_adjunto = "'.$nombre_archivo.'", ca_adjuntoext = "'.$ext.'"
                    WHERE ca_id = "'.$this->id.'"';
            setq($sql);
          }
        }
      }
    }
    function SetDataActdd($idact,$tipo,$destino,$telefono,$correo,$observaciones,$estatus,$user,$cadid){
      $this->idact = $idact;
      $this->tipo = clearvmayus($tipo);
      $this->destino = clearvmayus($destino);
      $this->telefono = $telefono;
      $this->correo = clearvminus($correo);
      $this->observaciones = clearvmayus($observaciones);
      $this->estatus = clearvmayus($estatus);
      $this->user = clearvmayus($user);
      $this->cadid = $cadid;
    }
    function Invitadosact(){
      $sql = 'INSERT INTO crm_actividadesd SET
              cad_actividad = "'.$this->idact.'",
              cad_tipomiembro = "'.$this->tipo.'",
              cad_miembro = "'.$this->destino.'",
              cad_user = "'.$this->user.'",
              cad_correo = "'.$this->correo.'",
              cad_telefono = "'.$this->telefono.'",
              cad_observaciones = "'.$this->observaciones.'",
              cad_estatus= "'.$this->estatus.'"';
      setq($sql);
    }
    function UpdInvitadosact(){
      $sql = 'UPDATE crm_actividadesd SET
              cad_miembro = "'.$this->destino.'",
              cad_correo = "'.$this->correo.'",
              cad_telefono = "'.$this->telefono.'",
              cad_observaciones = "'.$this->observaciones.'",
              cad_estatus= "'.$this->estatus.'"
              WHERE cad_actividad = "'.$this->idact.'" AND cad_id = "'.$this->cadid.'"';
      setq($sql);
    }
    function selectact($id){
      $sql = 'SELECT * FROM crm_actividades WHERE ca_id = "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->ida = $row['ca_id'];
      $this->tableroa = $row['ca_tablero'];
      $this->accion = $row['ca_accion'];
      $this->nmb = $row['ca_nmb'];
      $this->hora = $row['ca_hora'];
      $this->duracion = $row['ca_duracion'];
      $this->lugar = $row['ca_lugar'];
      $this->ubicacion = $row['ca_ubicacion'];
      $this->adjunto = $row['ca_adjunto'];
      $this->descripcion = $row['ca_descripcion'];
      $this->liga = $row['ca_liga'];
      $this->vercomo = $row['ca_vercomo'];
      $this->estatus = $row['ca_estatus'];
    }
    function json($user){
      $sql = 'SELECT ca_id,ca_estatus,cad_tipomiembro,cad_user FROM crm_actividades INNER JOIN crm_actividadesd 
              ON ca_id = cad_actividad WHERE ca_estatus IN ("N","P","F")';
      if($user == "0") $sql .= ' AND cad_user IN (SELECT u_id FROM usuarios WHERE u_empresa = "'.$_SESSION['emp'].'")';
      else $sql .= ' AND cad_user = "'.$user.'"';
      
      $this->resultac = setq($sql);
    }
    function deladjact($idact){
      $adjuntoext = busca($idact,'crm_actividades','ca_id','ca_adjuntoext');

      $sql = 'UPDATE crm_actividades SET ca_adjunto = "", ca_adjuntoext = NULL
              WHERE ca_id = "'.$idact.'"';
      setq($sql);

      unlink('Adjuntos/'.$_GET['idact'].'.'.$adjuntoext);
    }
  }

  class viewactividades{
    var $model;
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
        $this->model = $model;
        $this->tipoc = array("C"=>"Cliente","P"=>"Prospécto");
        $this->estatust = array("N"=>"Abierto","P"=>"Construcción","G"=>"Negociación","L"=>"Aplazado","A"=>"Vendido","F"=>"Finalizado","C"=>"Cancelado","O"=>"En Linea","X"=>"Perdido");
        $this->colestatust = array("N"=>"bg-yellow bg-accent-1","P"=>"bg-orange bg-accent-2","G"=>"bg-indigo bg-accent-3","L"=>"bg-amber bg-darken-4","A"=>"bg-success bg-accent-3","C"=>"bg-red bg-accent-4","F"=>"bg-blue bg-darken-2","X"=>"bg-red bg-darken-2");
    }
    function show($user){
      echo '<input type="hidden" id="user" value="'.$user.'" />';
      ?>
      <style>
        .evento-calendario:hover {
            cursor: pointer; /* Cambia el cursor al estilo de un puntero */
            /* Otros estilos que quieras aplicar al evento al pasar el ratón sobre él */
        }
      </style>
      <script>
        var URLdomain = window.location.host;
        var protocol = window.location.protocol;
        var user = document.getElementById("user").value;
        console.log(URLdomain + " - " + protocol);
        document.addEventListener('DOMContentLoaded', function() {
          var calendarEl = document.getElementById('calendar');
          var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar:{
              center: 'title',
              left: 'dayGridMonth,listWeek'
            },
            events:protocol+'//'+URLdomain+'/fabrica/json.php?id='+user,
            height: 600,
            timeZone: 'America/Mexico_City',
            eventClick:function(info){
              $("#pop"+info.event.id).click();
            },
          });
          calendar.render();
        });
      </script>
      <?php
      $botones = "";
      $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
      if($grupo != "ADMIN" && $grupo != "GERENCIA") $dis = "disabled";
      else $dis = "";
      $botones .='<form method="post">';
        $botones .= '<a href="?modulo=actividades&accion=index" >
            <button type="button" class="btn btn-sm btn-success">
              <i class="fas fa-list"></i> Actividades
            </button>
          </a>';
        $botones .='<a data-fancybox data-type="ajax" data-src="popup/newactividad.php?act=2&id=3" href="javascript:;">
            <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" data-placement="top" title="Nueva actividad"><i class="fa fa-plus"></i> Actividad</button>
          </a>';
        $botones .= '
            <select name="usuario" id="usuario" class="form-control-sm" onchange="submit();" '.$dis.'>
            <option value="0" >Todos</option>';
              $sqlu = 'SELECT u_id, u_nmb, u_apellidos FROM usuarios WHERE u_estatus = "A" AND u_id != "ADMIN"';
              $result = setq($sqlu);
              while($row = $result->fetch_array()){
                if($user == $row['u_id']) $usu = "selected"; else $usu = "";
                $botones .=  '<option value="'.$row['u_id'].'" '.$usu.'>'.$row['u_nmb'].' '.$row['u_apellidos'].'</option>';
              }
            $botones .= '</select>
      </form>';
      toolbar($_GET['modulo'], "", "", "", $botones);
      echo'<br><div id="calendar" style="background:white"></div>';
      echo '<div hidden>';
      while($row = $this->model->resultac->fetch_array()){
        if($row['cad_user'] != $_SESSION['uid']){
          echo'<a data-fancybox data-type="ajax" id="pop'.$row['ca_id'].'" data-src="popup/setactividad.php?idact='.$row['ca_id'].'&readon=1&orig=1&ver=1" href="javascript:;" >
            <button type="button" class="btn btn-primary" id="act'.$row['ca_id'].'"><i class="fa fa-eye"></i></button>
          </a>';
        }elseif($row['cad_tipomiembro'] != "U" && $row['ca_estatus'] != "F"){
          echo'<a data-fancybox data-type="ajax" id="pop'.$row['ca_id'].'" data-src="popup/setactividad.php?idact='.$row['ca_id'].'&readon=1&orig=1&ver=1" href="javascript:;" >
            <button type="button" class="btn btn-primary" id="act'.$row['ca_id'].'"><i class="fa fa-eye"></i></button>
          </a>';
        }else{
          if($row['ca_estatus'] == "N" || $row['ca_estatus'] == "P"){
            echo '<a data-fancybox data-type="ajax" id="pop'.$row['ca_id'].'" data-src="popup/setactividad.php?idact='.$row['ca_id'].'&user=U&orig=1&cal=1" href="javascript:;" >
              <button type="button" class="btn btn-info" id="act'.$row['ca_id'].'"/><i class="fa fa-pen"></i></button>
            </a>';
          }else{
            echo'<a data-fancybox data-type="ajax" id="pop'.$row['ca_id'].'" data-src="popup/setactividad.php?idact='.$row['ca_id'].'&readon=1&orig=1&ver=1" href="javascript:;" >
              <button type="button" class="btn btn-primary" id="act'.$row['ca_id'].'"><i class="fa fa-eye"></i></button>
            </a>';
          }
        }
      }
      echo '</div>';
    }
    function browse($tipo,$estatus,$ida){
      if($ida){
        echo '<script>
          $("#act"+'.$ida.').click();
        </script>';
      }
      ?>
      <script>
        /*function confirma(idac){
          $("#pop"+idac).click();
        }
        function cancela(idac){
          $("#popc"+idac).click();
        }*/
        function confirma(idac){
          var conf = confirm("¿Deseas Marcar la actividad como realizada?");
          if(conf == true){
            document.location.href="?modulo=actividades&accion=estatusactividad&estatus=F&id="+idac+"&origen=1";
          }
        }
        function cancela(idac){
          var conf = confirm("¿Deseas Marcar la actividad como cancelada?");
          if(conf == true){
            document.location.href="?modulo=actividades&accion=estatusactividad&estatus=C&id="+idac+"&origen=1";
          }
        }
      </script>
      <script>
        window.onload=function(){
          const valores = window.location.search;
          const urlParams = new URLSearchParams(valores);
          var actividad = urlParams.get('ida');
          if(actividad !== "undefined" && actividad !== null){
            document.getElementById("act"+actividad).click();
          }
        }
      </script>
      <?php
      $botones = "";
      echo '<div class="page-title-actions"><div class="d-inline-block dropdown">
        <form method="post">';
          
          $botones .= '<a href="?modulo=actividades&accion=show">
            <button type="button" class="mr-2 btn btn-sm btn-success"><i class="fas fa-calendar-alt"></i>Ver calendario</button>
          </a>';
          $botones .='<label for="tipo">Mostrar</label>
          <select name="tipo" id="tipo" class="form-control-sm mb-2 " onchange="submit();">';
          $botones .= '<option value="T" >Todos</option>';
            $sqltt = 'SELECT ca_id,ca_nmb FROM crm_acciones WHERE ca_estatus = "A" ORDER BY ca_orden';
            $resultt = setq($sqltt);
            while($rowtt = $resultt->fetch_array()){
              if($tipo == $rowtt['ca_id']) $esttt = "selected"; else $esttt = "";
              $botones .= '<option value="'.$rowtt['ca_id'].'" '.$esttt.'>'.$rowtt['ca_nmb'].'</option>';
            }
            $ests = "";
            $estN = "";
            $estP = "";
            $estF = "";
            $botones .= '</select>&nbsp;&nbsp;';
          if($estatus == "T") $ests = " selected ";
          elseif($estatus == "X") $estN = " selected ";
          elseif($estatus == "S") $estP = " selected ";
          elseif($estatus == "F") $estF = " selected ";
          $botones .= '<label for="estatus">Estatus</label>
          <select name="estatus" id="estatus" class="form-control-sm mb-2 " onchange="submit();">
            <option value="T" '.$ests.'>Todas</option>
            <option value="S" '.$estP.'>Nuevas</option>
            <option value="X" '.$estN.'>Canceladas</option>
            <option value="F" '.$estF.'>Finalizadas</option>
          </select>';
          $botones .='</form>
      </div>';
      toolbar($_GET['modulo'], '', '', '', $botones);
      echo '<div class="main-card mb-3">
        <div class="card-body row" >
          <div class="col-md-12 col-12 col-sm-12">
            <div class="table-responsive">
              <center>
                <table width="100%" class="mb-0 table table-hover table-striped" id="myTable">
                  <thead class="bg-light-blue bg-darken-2">
                    <tr>
                      <th width="23%"><b>Actividad</b></th>
                      <th width="20%"><b>Ejecutar</b></th>
                      <th width="30%"><b>Descripción</b></th>
                      <th width="10%"><b>Estatus</b></th>
                      <th width="22%"><b>Acción</b></th>
                    </tr>
                  </thead>';
                  while($row = $this->model->resultac->fetch_array()){
                    echo '<input type="hidden" name="nmbact'.$row['ca_id'].'" id="nmbact'.$row['ca_id'].'" value="'.busca($row['ca_id'],'crm_actividades','ca_id','ca_nmb').'">';
                    if($row['ca_duracion'] > 60){
                      $hour = floor($row['ca_duracion']/60);
                      $duracion = $hour.' Horas y '.($row['ca_duracion']-($hour*60)).' Mins';
                    }
                    else $duracion = $row['ca_duracion'].' Mins';
                    if($row['ca_estatus'] == "N"){ $alrt = ' class="alert alert-warning no-border" role="alert" '; $estatus = "Nuevo"; }
                    elseif($row['ca_estatus'] == "P"){ $alrt = ' class="alert alert-primary no-border" role="alert" '; $estatus = "Proceso"; }
                    elseif($row['ca_estatus'] == "F"){ $alrt = ' class="alert alert-success no-border" role="alert" '; $estatus = "Finalizado"; }
                    else{  $alrt = ' class="alert alert-danger no-border" role="alert" '; $estatus = "Cancelado"; }

                    $fini =  $row['ca_fecha'].' '.$row['ca_hora'];
                    $ffin = date('Y-m-d H:i:s',strtotime($fini.' + '.$row['ca_duracion'].' mins'));
                    echo '<tr>
                      <th class="font-small-3"><i class="'.$row['icon'].'"></i> '.$row['ca_nmb'].'</th>
                      <td>'.fecha_formato($ffin,true,false).'</td>
                      <td><label class="font-small-3">'.$row['ca_descripcion'].'</label></td>
                      <td '.$alrt.'>'.$estatus.'</td>
                      <td>';
                        if($row['cad_tipomiembro'] != "U" && $row['ca_estatus'] != "C"){
                          echo'<a data-fancybox data-type="ajax" data-src="popup/setactividad.php?idact='.$row['ca_id'].'&readon=1&orig=1&ver=1" href="javascript:;" >
                            <button type="button" class="btn btn-sm btn-primary" id="act'.$row['ca_id'].'"><i class="fas fa-eye" style="color: #ffffff;"></i></button>
                          </a>';
                        }else{
                          if($row['ca_estatus'] == "N" || $row['ca_estatus'] == "P"){
                            echo '<a data-fancybox data-type="ajax" data-src="popup/setactividad.php?idact='.$row['ca_id'].'&user=U&orig=1" href="javascript:;" >
                              <button type="button" class="btn btn-sm btn-info" id="act'.$row['ca_id'].'"/><i class="fa fa-pen"></i></button>
                            </a>';
                            $ext = busca($row['ca_id'],'crm_actividadesd','cad_tipomiembro IN ("C","E") AND cad_actividad','COUNT(*)');
                            if($ext > 0){
                              echo '<a data-fancybox data-type="ajax" data-src="popup/sendwhats?idact='.$row['ca_id'].'&rand='.rand().'" href="javascript:;">
                                <button type="button" class="btn btn-sm " style="background: #25D366;" data-toggle="tooltip" data-placement="top" title="Enviar mensaje al cliente por whatsapp"><i class="fab fa-whatsapp" style="color: #ffffff;"></i></button>
                              </a>';
                            }
                            echo '<button type="button" class="btn btn-sm btn-success" onclick="confirma('.$row['ca_id'].')"/><i class="fa fa-check"></i></button>';
                            echo '&nbsp;<button type="button" class="btn btn-sm btn-danger" onclick="cancela('.$row['ca_id'].')"/><i class="fa fa-times"></i></button>';
                          }else if($row['ca_estatus'] == "F"){
                            echo '<a data-fancybox data-type="ajax" data-src="popup/newactividad.php?tablero='.$row['ca_tablero'].'&act='.$row['ca_accion'].'&clone='.$row['ca_id'].'" href="javascript:;">
                              <button class="btn btn-sm btn-secondary" data-toggle="tooltip" data-placement="top" title="Clonar actividad"><i class="far fa-clone"></i></button>
                            </a>';
                          }
                        }
                      echo '</td>
                    </tr>';
                  }
                echo '</table>';
                if($this->model->resultac->num_rows == 0)echo'<h4>Sin registro de actividades</h4>';
              echo'</center>
            </div>
          </div>
        </div>
      </div>
      
      <script>
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
