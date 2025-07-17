<?php
  class Grupo{
    var $model;
    var $view;
    function __construct(){
      $this->model= new modelGrupo(isset($obj)); // crea modelo
    }
    function index(){
      $this->model->result();
      $this->view=new viewGrupo($this->model);
      $this->view->browse();
    }
    function edit(){
      if(!isset($_GET['id'])) $_GET['id'] = NULL;

      $this->model->select($_GET['id']);
      $this->view = new viewGrupo($this->model);
      $this->view->edit();
    }
    function show(){
        if(!isset($_GET['id'])) $_GET['id'] = NULL;
        if(!isset($_GET['modul'])) $_GET['modul'] = NULL;

      $this->model->select($_GET['id']);
      $this->model->resultg();
      $this->model->resultM();
      $this->view = new viewGrupo($this->model);
      $this->view->show($_GET['modul']);
    }
    function showdetalle(){
      $this->model->select($_GET['id']);
      $this->model->resultacciones();
      $this->viewconfig=new ViewConfiguracion($this->model);
      $this->viewconfig->showmodulo();
    }
    function insert(){
      if($_POST['estatus'] == "1") $estatus = 'A'; else $estatus = "I";
      $this->model->setData($_POST['id'], $_POST['nmb'], $estatus,$_POST['desc']);
      $this->model->insert();
      redirect('?modulo=grupo&accion=show&id='.$this->model->id);
    }
    function delete(){
      $this->model->select($_GET['id']);
      $this->model->delete();
    }
    function update(){
      if(isset($_POST['estatus'])) $estatus = "1"; else $estatus = "2";
    
      $this->model->setData($_GET['id'], $_POST['nmb'], $estatus,NULL);
      $this->model->update();
    
      if($estatus == "1"){
        redirect("?modulo=grupo&accion=index&id=".$this->model->id);
      }
      else{
        redirect("?modulo=grupo&accion=index");
      }
    }
    function insertmodulo(){
        //$nombre=busca($_POST['producto'],'productos','p_id','p_nmb');
      $this->model->setDatadetalle($_POST['grupo'], $_POST['modulo'], '');
      $this->model->insertmodulo();
      redirect('?modulo=grupo&accion=show&id='.$this->model->id);
    }
    function deletemodulo(){
      $this->model->setDatadetalle($_GET['grupo'], $_GET['moduloid'], '');
      $this->model->deletemodulo();
      redirect('?modulo=grupo&accion=show&id='.$this->model->id);
    }
    function deleteaccion(){
      $this->model->setDatadetalle($_GET['grupo'], $_GET['moduloid'], $_GET['accionid']);
      $this->model->deleteaccion();
      redirect('?modulo=grupo&accion=show&id='.$this->model->id);
    }
    function asignapermiso(){
      mb_internal_encoding('UTF-8');
      $this->model->resultaccion($_GET['modul']);
      while($row = $this->model->resultacciones->fetch_array()){
        if(isset($_POST['ch'.$row['gd_accion']])){
          $sql = 'INSERT IGNORE INTO gruposd SET
                  gd_grupo = "'.$_GET['grupo'].'",
                  gd_modulo = "'.clearvmayus($_GET['modul']).'",
                  gd_accion = "'.$row['gd_accion'].'"';
          setq($sql) or die($sql);
        }else{
          $sql = 'DELETE FROM gruposd WHERE
                  gd_grupo = "'.$_GET['grupo'].'" AND
                  gd_modulo = "'.$_GET['modul'].'" AND
                  gd_accion = "'.$row['gd_accion'].'"';
          if($_GET['grupo'] != "ADMIN")
            setq($sql) or die($sql);
        }
        $desc = mb_strtoupper(trim($_POST['desc'.$row['gd_accion']]));

        $sqld = 'INSERT INTO grupos_accion SET
                ga_modulo = "'.$_GET['modul'].'",
                ga_accion = "'.clearvmayus($row['gd_accion']).'",
                ga_descripcion = "'.$desc.'"
                ON DUPLICATE KEY UPDATE
                ga_descripcion = "'.$desc.'"';
        setq($sqld) or die($sqld);
      }
      redirect("?modulo=grupo&accion=show&id=".$_GET['grupo']."");
    }
    function updatedesc(){
      $sql = 'INSERT INTO grupos_accion SET
                ga_descripcion = "'.strtoupper($_POST['descripcion']).'",
                ga_modulo = "'.$_POST['modul'].'",
                ga_accion = "'.$_POST['actio'].'"
              ON DUPLICATE KEY UPDATE  ga_descripcion = "'.strtoupper($_POST['descripcion']).'"';
      setq($sql) or die($sql);

        redirect("?modulo=grupo&accion=show&id=".$_POST['grupo'].'&modul='.$_POST['modul']);
    }
    function grupoempresa(){
      $sqlemp = 'SELECT * FROM empresas WHERE e_estatus = "A"';
      $resultemp = setq($sqlemp);
      while($row = $resultemp->fetch_array()){
        $estatus = busca($row['e_id'],'grupos_empresas','ge_grupo = "'.$_GET['id'].'" AND ge_empresa','ge_estatus');
        if($estatus){
          if($_POST['emp'.$row['e_id']]) $this->model->updategrupo($_GET['id'],$row['e_id'],"A");
          else if($estatus == "A") $this->model->updategrupo($_GET['id'],$row['e_id'],"I");
        }elseif(isset($_POST['emp'.$row['e_id']])) $this->model->insertgrupo($_GET['id'],$row['e_id'],'A');
      }

      redirect("?modulo=grupo&accion=show&id=".$_GET['id']."");
    }
    function copiar(){
      if(isset($_POST['grupo'])) $grupo = $_POST['grupo'];
      else $grupo = "0";

      if(isset($_GET['id'])) $id = $_GET['id'];
      else $id = NULL;

      $sql = 'SELECT * FROM gruposd WHERE gd_grupo = "'.$grupo.'" AND gd_modulo != "" ORDER BY gd_modulo, gd_grupo';
      $result = setq($sql);
      while($row = $result->fetch_array()){
        $sqld = 'INSERT INTO gruposd SET
                  gd_grupo = "'.$id.'",
                  gd_modulo = "'.clearvmayus($row['gd_modulo']).'",
                  gd_accion = "'.clearvmayus($row['gd_accion']).'"';
        setq($sqld);
      }

      redirect("?modulo=grupo&accion=show&id=".$_GET['id']."");
    }
  }

  class modelGrupo {
    function result() {
      
      $sql = 'SELECT * FROM grupos';
      $this->result=setq($sql);
      //return ;
    }
    function setData($id, $nmb, $estatus,$desc){
      $this->id = strtoupper($id);
      $this->nmb = strtoupper($nmb);
      $this->estatus = strtoupper($estatus);
      $this->descripcion = clearvmayus($desc);

      if($estatus == 2) $this->estatus = 0;
    }
    function setDatadetalle($grupo, $modulo, $accion){
      $this->id = strtoupper($grupo);
      $this->modulo = strtoupper($modulo);
      $this->accion = strtoupper($accion);
    }
    function select($id) {
      $sql = 'SELECT * FROM grupos WHERE g_id= "'.$id.'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['g_id'];
      $this->nmb = $row['g_nmb'];
      $this->estatus = $row['g_estatus'];
      if($this->estatus == 0)
      $this->estatus = 2;
    }
    function insert() {
      $sql = 'INSERT INTO grupos SET
      g_id = "'.$this->id.'",
      g_nmb = "'.$this->nmb.'",
      g_estatus = "'.$this->estatus.'",
      g_descripcion = "'.$this->descripcion.'"';
      setq($sql);

      $sql1 = 'INSERT INTO gruposd SET 
              gd_grupo = "'.$this->id.'",
              gd_modulo = "INDEX",
              gd_accion = "INDEX"';
      setq($sql1);
    }
    function update() {
      if($this->estatus == "1"){
        $sql = 'UPDATE grupos SET
                g_nmb = "'.$this->nmb.'",
                g_estatus = "A"
                WHERE g_id = "'.$this->id.'"';
        setq($sql);
        $this->updateusuarios($this->id,NULL,"A");
      }else{
        $sql = 'UPDATE grupos SET
                g_estatus = "I"
                WHERE g_id = "'.$this->id.'"';
        setq($sql);
        $this->updateusuarios($this->id,NULL,"I");
      }
      return;
    }
    function updateusuarios($id,$empresa,$estatus){
      if(busca($id,'usuarios','u_grupo','COUNT(*)')>0){
        $sql1 = 'UPDATE usuarios SET
                u_estatus= "'.$estatus.'"
                WHERE u_grupo = "'.$id.'" ';
        if($empresa) $sql1 .= 'AND u_empresa = "'.$empresa.'"';
        setq($sql1);
      }

      if(busca($id,'grupos_empresas','ge_grupo','COUNT(*)') > 0){
        $sql1 = 'UPDATE grupos_empresas SET
                ge_estatus = "'.$estatus.'"
                WHERE ge_grupo = "'.$id.'" ';
        if($empresa) $sql1 .= 'AND ge_empresa = "'.$empresa.'"';
        setq($sql1);
      }
    }
    function insertmodulo() {
      $sql = 'SELECT * FROM modulosd
      WHERE md_modulo = "'.$this->modulo.'" ';
      $result=setq($sql) or die($sql);
      while ($row=$result->fetch_array()){
          $sql='INSERT IGNORE INTO gruposd
        SET gd_grupo="'.$this->id.'",
        gd_modulo="'.$this->modulo.'",
        gd_accion="'.$row['md_accion'].'"';
        setq($sql) or die($sql);
      }
      return;
    }
    function deletemodulo() {
      $sql = 'DELETE FROM gruposd
      WHERE gd_grupo = "'.$this->id.'"
      AND gd_modulo = "'.$this->modulo.'"';
      setq($sql) or die($sql);
      return;
    }
    function deleteaccion() {
      $sql = 'DELETE FROM gruposd
      WHERE gd_grupo = "'.$this->id.'"
      AND gd_modulo = "'.$this->modulo.'"
      AND gd_accion = "'.$this->accion.'"
      ';
      setq($sql) or die($sql);
      //echo $sql.'<br><br>';
      return;
    }
    function resultgrupom($grupo) {
      //$sql = 'SELECT DISTINCT gd_modulo
      $sql =  'SELECT *
      FROM gruposd
      WHERE gd_grupo = "'.$grupo.'" AND gd_modulo != ""
      ORDER BY gd_modulo, gd_grupo';
      echo 'sql: '.$sql;
      $this->result=setq($sql) or die($sql);
      return $this->result;
    }
    function resultg() {
      /*$sql = 'SELECT DISTINCT(gd_modulo)
              FROM gruposd INNER JOIN menud ON gd_modulo = md_modulo INNER JOIN menu ON m_id = md_menu
              WHERE  gd_modulo != "" ORDER BY m_orden,md_orden,gd_accion';*/
      $sql = 'SELECT DISTINCT(gd_modulo) FROM gruposd INNER JOIN menud ON gd_modulo = md_modulo 
              INNER JOIN menu ON m_id = md_menu WHERE gd_modulo != "" ORDER BY m_orden,md_orden,md_menu,gd_accion;';
      $this->resultg=setq($sql) or die($sql);
    }
    function resultacciones($find,$grupo) {
      $sql = 'SELECT gd_accion FROM gruposd WHERE gd_grupo = "'.$grupo.'" AND gd_modulo = "'.$find.'"';
      $this->resultacciones=setq($sql) or die($sql);
      return;
    }
    function resultaccion($modulo) {
      $sql = 'SELECT DISTINCT (gd_accion) FROM menud
              INNER JOIN gruposd ON md_modulo = gd_modulo WHERE gd_modulo =  "'.$modulo.'"
              AND gd_accion !=  "" ORDER BY md_orden';
      $this->resultacciones=setq($sql) or die($sql);
      return;
    }
    function resultM(){
      $sql = 'SELECT DISTINCT(md_menu) menu FROM menud INNER JOIN menu ON m_id = md_menu 
              GROUP BY md_menu ORDER BY m_orden,md_orden;';
      /*$sql = 'SELECT DISTINCT(md_modulo), md_menu FROM menud INNER JOIN menu ON m_id = md_menu 
              WHERE m_barra = "1" GROUP BY md_modulo ORDER BY m_orden,md_orden;';*/
      $this->resultm = setq($sql) or die($sql);
    }
    function insertgrupo($grupo,$empresa,$est){
      $sql = 'INSERT INTO grupos_empresas SET ge_grupo = "'.$grupo.'",
              ge_empresa = "'.$empresa.'", ge_estatus = "'.$est.'"';
      setq($sql);
    }
    function updategrupo($grupo,$empresa,$est){
      $sql = 'UPDATE grupos_empresas SET ge_estatus = "'.$est.'"
              WHERE ge_grupo = "'.$grupo.'" AND ge_empresa = "'.$empresa.'"';
      setq($sql);
      $this->updateusuarios($grupo,$empresa,$est);
    }
  }

  class viewGrupo {
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
    }
    function browse() {
      //Sección: E1 Encabezado - Botones de acción
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

      $nuevo = '
      <a id="nuevo" href="?modulo=grupo&accion=edit">
        <button type="button" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Nuevo </button>
      </a>
      ';

      toolbar($_GET['modulo'],'','',$nuevo);
      
      //echo '<center><h5>Grupo de usuarios activos para la empresa</h5></center>';
      echo'
      <div class="card mt-3">
      <div class="card-body">
      ';
        echo '<div class="table-responsive">
          <center>
            <table class="table table-hover table-striped table-bordered" id="myTable">
              <thead class="bg-light-blue bg-darken-2">
                <tr>
                  <th>Id</th>
                  <th>Nombre</th>
                  <th width="25%">Descripción</th>
                  <th>Estatus</th>';
                  if($_SESSION['emp'] == 1)
                    echo '<th></th>';
                  echo'<th></th>
                </tr>
              </thead>';
              $i = 0;
              while ($row=$this->model->result->fetch_array()) {
                echo '<tr>
                  <th>'.$row['g_id'].'</th>
                  <td>'.$row['g_nmb'].'</td>
                  <td>'.$row['g_descripcion'].'</td>';
                  if( $row['g_estatus'] == "I" ) echo'<td>Inactivo</td>';
                  else echo'<td>Activo</td>';
                  //if($_SESSION['emp'] == 1){
                    echo '<td>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#inlineForm'.$row['g_id'].'"/>
                          <i class="icon-edit2"></i> Ver o cambiar
                        </button>
                    </td>';
                    $txtpermisos = 'Asignar permisos';
                  /* }else {
                    //echo'<td></td>';
                    $txtpermisos = 'Ver permisos';
                  } */
                  echo '
                  <td>
                    <a href="?modulo=grupo&accion=show&id='.$row['g_id'].'">
                      <button type="button" class="btn btn-outline-primary" />
                        <i class="icon-unlock-alt"></i> '.$txtpermisos.'
                      </button>
                    </a>
                  </td>';
                  echo '<div class="modal fade text-xs-center" id="inlineForm'.$row['g_id'].'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
                    <div class="modal-dialog modal-sm" role="document">
                      <div class="modal-content" style="max-width:100%">
                        <div class="modal-redirect">
                          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                          <label class="modal-title text-text-bold-600" id="myModalLabel33">Cambiar información de '.$row['g_nmb'].'</label>
                        </div>';
                        if($row['g_estatus'] == "A") $ch = 'checked="checked"';
                        else $ch = '';
                        echo'<form action="?modulo=grupo&accion=update&id='.$row['g_id'].'" method="post">
                          <div class="modal-body">
                            <label>Nombre del grupo: </label>
                            <div class="mb-5">
                              <input type="text" placeholder="Grupo" value="'.$row['g_nmb'].'" name="nmb" class="form-control" onfocus="this.select();" required="required" />
                            </div>
                            <div class="mb-5">
                              <input type="checkbox" name="estatus" class="flipswitch" '.$ch.' />
                            </div>';
                            echo'
                          </div>
                          <div class="modal-footer text-center">
                            <input type="submit" class="btn btn-outline-primary btn-lg" value="Guardar" />
                            <input type="reset" class="btn btn-outline-danger btn-lg" data-bs-dismiss="modal" value="Cerrar" />
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </tr>';
              }
              echo '
            </table>
          </center>
        </div>
        </div>
      </div>';

      echo '
    
      <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            }
        });
      </script>

      
      ';
    }
    function show() {
      if($_SESSION['emp'] != 1){
        $hidden = 'hidden';
        $read = 'readonly';
        $disabled = 'disabled';
        $checkread = 'onclick="javascript: return false;"';
      }else{
        $hidden = '';
        $read = '';
        $disabled = '';
        $checkread = '';
      }

      $atras = '

        <a href="?modulo=grupo&accion=index">
          <button class="btn btn-sm btn-warning">
            <i class="fa fa-arrow-left"></i> Atrás
          </button>
        </a>

      ';
      $otro = '

      <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#copiarpermisos"'.$hidden.'/>
      <i class="fas fa-copy"></i> Copiar permisos de..
      </button>

      ';


      toolbar($_GET['modulo'],$atras,'','',$otro);

      echo'
      <div class="modal fade text-xs-center" id="copiarpermisos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel33" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content" style="max-width:100%">
            <div class="modal-redirect">
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <label class="modal-title text-text-bold-600" id="myModalLabel33">Asignar permisos a '.$_GET['id'].'</label>
            </div>
            <form action="?modulo=grupo&accion=copiar&id='.$_GET['id'].'" method="post">
              <div class="modal-body">
                <label>Copiar de:</label>
                <div class="mb-5">
                  <select class="mb-5" name="grupo" required>
                    <option value="">Seleccione un grupo</option>';
                    $sql = 'SELECT DISTINCT(gd_grupo) FROM gruposd WHERE gd_grupo NOT IN ("ADMIN","'.$_GET['id'].'")';
                    $result = setq($sql);
                    while($rowcop = $result->fetch_array()){
                      echo '<option value="'.$rowcop['gd_grupo'].'" >'.busca($rowcop['gd_grupo'],'grupos','g_id','g_nmb').'</option>';
                    }
                    echo'
                  </select>
                </div>
              </div>
              <div class="modal-footer text-center">
                <input type="submit" class="btn btn-outline-primary btn-lg" value="Guardar" />
                <input type="reset" class="btn btn-outline-danger btn-lg" data-bs-dismiss="modal" value="Cerrar" />
              </div>
            </form>
          </div>
        </div>
      </div>';

      echo '<form method="post" name="updated" action="?modulo=grupo&accion=updatedesc">
        <input type="hidden" name="grupo" value="'.$this->model->id.'" />
        <input type="hidden" name="modul" id="modul" />
        <input type="hidden" name="actio" id="actio" />
        <input type="hidden" name="descripcion" id="descripcion" />
      </form>';
      $h = 0;
      echo'<div class="card mt-3">
        <form class="form" method="post" action="?modulo=grupo&accion=grupoempresa&id='.$_GET['id'].'" onsubmit="checkguardar();">
          <div class="card-body row">
            <div class="">
              <center><h5 class="card-title p-1">Permisos para el grupo "'.busca($_GET['id'],'grupos','g_id','g_nmb').'"</h5></center>';
              if($_SESSION['emp'] == 1){
                echo'<div class="form-row">
                  <div class="col-md-12">
                    <div class="card">
                      <div class="card-header">
                        <h4 class="card-title" id="basic-layout-form">Asignar grupo a empresa</h4>
                      </div>
                      <div class="card-body ">
                        <div class="card-block">
                          <div class="form-body">
                            <div class="row">';
                              $sqlemp = 'SELECT * FROM empresas WHERE e_estatus = "A"';
                              $resultemp = setq($sqlemp);
                              while($rowemp = $resultemp->fetch_array()){
                                if(busca($rowemp['e_id'],'grupos_empresas','ge_grupo = "'.$_GET['id'].'" AND ge_empresa','ge_estatus') == "A") $ckemp = 'checked';
                                else $ckemp = '';
                                $h++;
                                echo'<div class="col-md-4">
                                  <div class="mb-5">
                                    <input type="checkbox" name="emp'.$rowemp['e_id'].'" id="emp'.$h.'" '.$ckemp.'/> 
                                    <label for="emp'.$h.'">'.$rowemp['e_nmb'].'</label>
                                  </div>
                                </div>';
                              }
                            echo'</div>
                            <div class="form-actions">
                              <center>
                                <button type="submit" class="btn btn-primary" id="saveuser">
                                  <i class="fa fa-save"></i> Guardar
                                </button>
                              </center>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>';
              }
            echo'</div>
          </div>
        </form>';
      $i=0;
      $a = 0;
      while($row=$this->model->resultm->fetch_array()){
        if($_SESSION['emp'] != 1){
          $sqlcount = 'SELECT md_modulo FROM menud WHERE md_menu = "'.$row['menu'].'"';
          $resultcount = setq($sqlcount);
          $count = 0;
          while($rowcount = $resultcount->fetch_array()){
            $count += busca($rowcount['md_modulo'],'gruposd','gd_grupo = "'.$_GET['id'].'" AND gd_modulo','COUNT(*)');
          }
        }else{
          $count = 1;
        }
        if($count > 0){
          if($i%3 == 0) echo'<div class="card-body" style="height: auto; background-color:white">  ';
          $sqlm = 'SELECT DISTINCT(md_modulo) modulo FROM menud INNER JOIN menu ON m_id = md_menu 
                  WHERE md_menu = "'.$row['menu'].'" ORDER BY m_orden,md_orden,md_menu;';
          $resultm = setq($sqlm);

          echo '
          <div class="col-xl-4 col-lg-6 col-md-12 col-sm-12 col-xs-12" id="det'.$i.'" style="height:100px;">
            <hr>
            <div class="d-flex justify-content">
              <div class="col-md-10 col-sm-10 col-xs-10">
                <label>'.$row['menu'].'</label>
              </div>
              <div class="col-md-2 col-sm-2 col-xs-2" align="right">
                <button type="button" align="right" class="btn btn-link" onclick="detalleGrupo('.$i.');" >
                  <i class="fa fa-plus" id="icon'.$i.'"></i>
                </button>
                <input type="hidden" id="g'.$i.'" name="mostrar" value="0"></input>
              </div>
            </div>';
            //fa fa-minus
            $j = 0;
            while($rowm = $resultm->fetch_array()){
              if($_SESSION['emp'] != "1") $cmodulo = busca($rowm['modulo'],'gruposd','gd_modulo','COUNT(*)');
              else $cmodulo = 1;
              if($cmodulo > 0){
                $j++;
                echo'
                <div class="table table-responsive">
                  <form class="form'.$i.'" style="display:none" action="?modulo=grupo&accion=asignapermiso&grupo='.$this->model->id.'&modul='.$rowm['modulo'].'" method="post">
                    <center>
                      <table width="100%" class="table table-hover border">';    
                        $this->model->resultaccion($rowm['modulo']);
                        echo '<script>
                          function senddoc(num){
                            var modu = "'.$rowm['modulo'].'";
                            var acti = document.getElementById("act"+num).value;
                            var desc = document.getElementById("descri"+num).value;
          
                            document.getElementById("modul").value = modu;
                            document.getElementById("actio").value = acti;
                            document.getElementById("descripcion").value = desc;
                            document.updated.submit();
                          }
                        </script>';
                        echo'<tbody id="g'.$i.'" class="grupo'.$i.'" style="display:none">
                          <tr >
                            <th colspan="2" style="text-transform: uppercase;">'.$rowm['modulo'].'</th>
                            <td align="right">
                              <button type="button" id="btndet'.$a.'" class="btn btn-dark" onclick="detalleModulo('.$a.');" >
                                <i id="btnic'.$a.'" class="fa fa-eye"></i> <lable id="lbtnic'.$a.'">Mostrar</lable>
                              </button>
                            </td>
                            <td hidden><input type="hidden" id="m'.$a.'" name="mostrar" value="0"></input></td>
                          </tr>
                          <tr class="m'.$a.'" style="display:none">
                            <th width="5%">Acceso</th>
                            <th width="15%">Acción</th>
                            <th width="80%">Descripción</th>
                          </tr>
                          <tr class="m'.$a.'" style="display:none">
                            <td colspan="3">
                              <input type="checkbox" '.$hidden.' id="checkb'.$a.'" name="check" onclick="check1('.$a.')">
                              <label id="'.$a.'">Seleccionar todos</label>
                            </td>
                          </tr>';
                          $b = 0;
                          while ($row3=$this->model->resultacciones->fetch_array()) {
                            $b++;
                            if(busca($this->model->id,'gruposd','gd_accion = "'.$row3['gd_accion'].'" AND gd_modulo = "'.$rowm['modulo'].'" AND gd_grupo','COUNT(*)') > 0)
                              $check = "checked";
                            else $check = "";
                            $descripcion = busca($row3['gd_accion'],'grupos_accion','ga_modulo = "'.$rowm['modulo'].'" AND ga_accion','ga_descripcion');
                            echo '<input type="hidden" id="act'.$b.'" value="'.$row3['gd_accion'].'" >
                            <tr class="m'.$a.'" style="display:none">
                              <td ><input type="checkbox" '.$checkread.' class="checar'.$a.'" id="cb'.$a.'" name="ch'.$row3['gd_accion'].'" '.$check.' onclick="checarBox('.$a.');"/></td>
                              <td style="font-size:11px; text-transform: capitalize;">'.$row3['gd_accion'].'</td>
                              <td colspan="2">
                                <textarea '.$read.' style="font-size:11px;" name="desc'.$row3['gd_accion'].'" class="form-control desc'.$a.'" id="descri'.$a.'" rows="2">'.$descripcion.'</textarea>
                              </td> <!--onchange="senddoc('.$b.');" -->
                            </tr>';
                          }
                          if($_SESSION['emp'] == 1)
                          echo '<tr class="m'.$a.'" style="display:none">
                            <th></th>
                            <th align="center" colspan="2">
                              <button type="submit" align="center" '.$disabled.' '.$hidden.' class="btn btn-primary" id="actualizar"><i class="fa fa-redo"></i> Actualizar</button
                            </th>
                          </tr>';
                          echo'
                        </tbody>';
                      echo'</table>
                    </center>
                  </form>
                </div>';
                $a++;
              }
            }
          echo '</div>';  
          if(($i+1)%3 == 0)echo'</div><br><br>';
          $i++;
        }
      }
      echo '<script>
        function detalleGrupo(id){
          var mostrar = document.getElementById("g"+id).value;
          tbody = document.getElementsByClassName("grupo"+id);
          form = document.getElementsByClassName("form"+id);
          div = document.getElementById("det"+id);
          if(mostrar == "0"){
            div.style.height = "auto";
            $("#g"+id).val("1");
            document.getElementById("icon"+id).removeAttribute("class");
            document.getElementById("icon"+id).setAttribute("class","fa fa-minus");
            for (var i = 0; i < tbody.length; i++) {
              tbody[i].style.display= "table-header-group";
            }
            for (var i = 0; i < form.length; i++) {
              form[i].style.display= "block";
            }
          }else{
            document.getElementById("icon"+id).removeAttribute("class");
            document.getElementById("icon"+id).setAttribute("class","fa fa-plus");
            div.style.height = "110px";
            $("#g"+id).val("0");
            for (var i = 0; i < tbody.length; i++) {
              tbody[i].style.display= "none";
            }
            for (var i = 0; i < form.length; i++) {
              form[i].style.display= "none";
            }
          }
        }
        function detalleModulo(id){
          var mostrar = document.getElementById("m"+id).value;
          const img = document.createElement("i");
          //img.class = "fa fa-eye";
          tbody = document.getElementsByClassName("m"+id);
          $("#lbtnic"+id).html("");
          if(mostrar == "0"){
            document.getElementById("btnic"+id).removeAttribute("class");
            document.getElementById("btnic"+id).setAttribute("class","icon-eye-slash");
            $("#lbtnic"+id).html("Ocultar");
            $("#m"+id).val("1");
            for (var i = 0; i < tbody.length; i++) {
              tbody[i].style.display= "table-row";
            }
          }else{
            document.getElementById("btnic"+id).removeAttribute("class");
            document.getElementById("btnic"+id).setAttribute("class","fa fa-eye");
            $("#lbtnic"+id).html("Mostrar");
            $("#m"+id).val("0");
            for (var i = 0; i < tbody.length; i++) {
              tbody[i].style.display= "none";
            }
          }
        }
        function check1(box) {
          check = document.getElementById("checkb"+box);
          cb1 = document.getElementsByClassName("checar"+box);
          if (check.checked) {
            for (var i = 0; i < cb1.length; i++) {
              cb1[i].checked= true;
            }
            document.getElementById(box).innerHTML= "Desmarcar todos";
          }else{
            for (var i = 0; i < cb1.length; i++) {
              cb1[i].checked= false;
            }
            document.getElementById(box).innerHTML= "Seleccionar todos";
          }
        }
        function checarBox(box){
          check = document.getElementById("checkb"+box);
          cb1 = document.getElementsByClassName("checar"+box);
          var c = 0;
          var nc = 0;
          for (var i = 0; i < cb1.length; i++) {
            if(cb1[i].checked == false){
              nc += 1;
            }else{
              c += 1;
            }
          }
          if(c == cb1.length){
            check.checked = true;
            document.getElementById(box).innerHTML= "Desmarcar todos";
          }else if(nc > 0 && c > 0){
            check.checked = false;
            document.getElementById(box).innerHTML= "Seleccionar todos";
          }
        }
      </script>';
      
    }
    function edit() {
      $aestatus=array('2'=>'Inactivo', '1'=>'Activo');
      $accion='insert';
      $uatras = 'index';
      if (isset($this->model->id)){
        $accion='update';
        $uatras = 'index';
      }
      $atras = '
        <div class="div2 mb-1" id="bboton01">
          <a href="?modulo=grupo&accion='.$uatras.'" class="btn btn-sm btn-warning"><i class="fa fa-arrow-left"></i> Atrás</a>
        </div>';

      toolbar($_GET['modulo'],$atras,'','');
      echo '
      <center>
        <div class="table table-responsive">
          <form method=post action=?modulo=grupo&accion='.$accion.'>
            <table "max-wdith: 60%;" class="lista table table-bordered table-striped">
              <thead class="bg-primary text-white">
                <tr>
                  <th colspan=6>Grupo</th>
                </tr>
              </thead>';
              if ($accion=='update'){
                echo '
                <tr>
                  <th>Id</th>
                  <td><input type=hidden name=id value='.$this->model->id.'>'.$this->model->id.'</td>
                </tr>';
              }else {
                echo '
                <tr>
                <th>Id</th>
                  <td><input type=text name=id size="10" maxlength="8" class="form-control" required value="'.$this->model->id.'"></td>
                </tr>';
              }
              echo '
              <tr>
                <th>Nombre</th>
                <td><input type=text name=nmb size="52" maxlength="50" class="form-control" value="'.$this->model->nmb.'" required></td>
              </tr>
              <tr>
                <th>Descripción</th>
                <td><input type=text name=desc size="52" maxlength="500" class="form-control" value="'.$this->model->desc.'" required></td>
              </tr>';
              echo '
              <tr>
                <th>Estatus</th>
                <td>'.menu_select_array($aestatus, $this->model->estatus, 'estatus').'</td>
              </tr>
              <tr>
                <td colspan="2"><center><button type="submit" class="btn btn-sm text-white btn-success"><i class="fa fa-save"></i> Guardar</button></td>
              </tr>
            </table>
          </form>
        </div>
      </center>';
    }
  }
?>