<?php
  //ini_set('display_errors','1');
  class prodsat {
    var $model;
    var $view;
    function __construct() {
      $this->model = new modelproductos(isset($obj));
    }
    function index() {
      if (!isset($_GET['id'])) $_GET['id'] = NULL;
      if (!isset($_GET['empresa'])) $_GET['empresa'] = $_SESSION['emp'];

      $this->model->result($_GET['empresa']);
      $this->view = new viewproductos($this->model);
      $this->view->show();
    }
    function insert() {
      if (!isset($id)) $id = NULL;
      $nombre = busca($_POST['nmb'],'prodsat','p_nmb','p_nmb');
      $this->model->setData($id, $_POST['nmb'], $_POST['estatus']);
      $this->model->insert();
      redirect('?modulo=prodsat&accion=index');
    }
    function disable() {
      $this->model->setData($_GET['id'], $_POST['nmb'], $_POST['estatus']);
      $this->model->disable();
      redirect('?modulo=prodsat&accion=index');
    }
    function enable() {
      $this->model->setData($_GET['id'], $_POST['nmb'], $_POST['estatus']);
      $this->model->enable();
      redirect('?modulo=prodsat&accion=index');
    }
    function update() {
      $this->model->setData($_POST['id'], $_POST['nmb'], $_POST['estatus']);
      $this->model->update();
      redirect('?modulo=prodsat&accion=index');
    }
    function show() {
      if (!isset($_GET['id'])) $_GET['id'] = NULL;

      $this->model->result($_GET['id']);
      // $this->model->resultclient();
      $this->view = new viewproductos($this->model);
      $this->view->show();
    }
    function indexps(){
      if (!isset($_REQUEST['division'])) $_REQUEST['division'] = NULL;
      if (!isset($_REQUEST['grupo'])) $_REQUEST['grupo'] = NULL;
      if (!isset($_REQUEST['clase'])) $_REQUEST['clase'] = NULL;
      if (!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;

      if(($_REQUEST['division'] && $_REQUEST['grupo']) || $_REQUEST['nmb'])
        $this->model->resultps($_REQUEST['division'],$_REQUEST['grupo'],$_REQUEST['clase'],$_REQUEST['nmb']);

      $this->view = new viewproductos($this->model);
      $this->view->browseps($_REQUEST['division'],$_REQUEST['grupo'],$_REQUEST['clase'],$_REQUEST['nmb']);
    }
    function setproducto(){
      if (!isset($_REQUEST['division'])) $_REQUEST['division'] = NULL;
      if (!isset($_REQUEST['grupo'])) $_REQUEST['grupo'] = NULL;
      if (!isset($_REQUEST['clase'])) $_REQUEST['clase'] = NULL;
      if (!isset($_REQUEST['nmb'])) $_REQUEST['nmb'] = NULL;

      $this->model->resultps($_GET['division'],$_GET['grupo'],$_GET['clase'],$_POST['nmb']);
      $this->model->insertps();

      redirect('?modulo=prodsat&accion=index');
    }
    function delps(){
      $sqld = 'DELETE FROM prodsat WHERE p_id = "'.$_GET['id'].'"';
      setq($sqld) or die($sqld);

      redirect('?modulo=prodsat&accion=index');
    }
    function updatealias(){
      $sqlu = 'UPDATE prodsat SET p_alias = "'.mb_strtoupper($_POST['alias']).'"
              WHERE p_id = "'.$_GET['id'].'"';
      setq($sqlu) or die($sqlu);

      redirect('?modulo=prodsat&accion=index&id='.$_GET['id']);
    }
    function showdet(){
      echo '<table class="lista">
              <tr><th colspan="5">Caracteristias del Producto/Servicio</th></tr>
              <tr>
                <th>Clave</th>
                <th>División</th>
                <th>Grupo</th>
                <th>Clase</th>
                <th>&nbsp;</th>
              </tr>
              ';
        $sql = 'SELECT * FROM cfdi_prodserv WHERE cp_clave = "'.$_GET['clave'].'"';
        $result = setq($sql) or die($sql);
        while($row = $result->fetch_array()){
          echo '<tr>
                  <th>'.$row['cp_clave'].'</th>
                  <td>'.busca($row['cp_division'],'catps_divisiones','cd_clave','cd_nmb').'</td>
                  <td>'.busca($row['cp_grupo'],'catps_grupos','cg_division = "'.$row['cp_division'].'" AND cg_grupo','cg_nmb').'</td>
                  <th>'.busca($row['cp_clase'],'catps_clases','cc_division = "'.$row['cp_division'].'" AND cc_grupo = "'.$row['cp_grupo'].'" AND cc_clase','cc_nmb').'</th>
                </tr>';
        }
      echo '</table>';
    }
  }

  /* -----------------------------------------------MODEL---------------------------------------------------------------------- */

  class modelproductos {
    function setData($id, $nmb, $estatus) {
      $this->id = strtoupper($id);
      $this->nmb = strtoupper($nmb);
      $this->estatus = strtoupper($estatus);
    }
    function select($id) {
      $sql = 'SELECT * FROM prodsat WHERE p_id= "' . $_GET['id'] . '" AND p_empresa = "'.$_SESSION['emp'].'"';
      $result = setq($sql);
      $row = $result->fetch_array();
      $this->id = $row['p_id'];
      $this->nmb = $row['p_nmb'];
      $this->estatus = $row['p_estatus'];
    }
    function insert() {
      $sql = 'SELECT MAX(p_id) FROM prodsat  WHERE p_empresa = "'.$_SESSION['emp'].'"';
      $result = setq($sql) or die(mysql_error());
      list($this->id) = $result->fetch_array();
      $this->id++;
      $this->estatus = 0;
      if (isset($_POST['estatus'])) {
        $this->estatus = 1;
      }
      if(busca($this->nmb,'prodsat','p_empresa = "'.$_SESSION['emp'].'" AND p_nmb','p_id'))
        die(alert_back("Nombre de unidad duplicado",true));

      $sql = 'INSERT INTO prodsat SET
              p_id = "' . $this->id . '",
              p_nmb = "' . $this->nmb . '",
              p_empresa = "' . $_SESSION['emp'] . '",
              p_estatus = ' . $this->estatus;    //   die($sql);
      setq($sql) or die(mysql_error() . $sql);
    }
    function result($empresa) {
      $sql = 'SELECT * FROM prodsat WHERE  p_empresa = "'.$empresa.'" ORDER BY 1';
      $this->resultalm = setq($sql) or die($sql);
    }
    function disable() {
      $sql = 'DELETE FROM prodsat WHERE p_id="' . $_GET['id'] . '" AND p_empresa = "'.$_SESSION['emp'].'"';
      setq($sql) or die($sql);
    }
    function enable() {
      $sql = 'UPDATE prodsat SET p_estatus = 1 WHERE p_id="' . $_GET['id'] . '" AND p_empresa = "'.$_SESSION['emp'].'"';
      setq($sql) or die($sql);
    }
    function update() {
      $this->estatus = 0;
      if (isset($_POST['estatus'])) { $this->estatus = 1;}
      $sql = 'UPDATE prodsat SET
              p_nmb = "'.$this->nmb.'",
              p_empresa = "'.$_SESSION['emp'].'",
              p_estatus = '.$this->estatus.' WHERE  p_id = "'.$this->id.'" 
              AND p_empresa = "'.$_SESSION['emp'].'"';
      setq($sql) or die(mysql_error() . $sql);
    }
    function resultps($division,$grupo,$clase,$nmb){
      if(!$nmb){
        $sql = 'SELECT * FROM cfdi_prodserv
                WHERE cp_division = "'.$division.'" AND cp_grupo = "'.$grupo.'"';
        if($clase) $sql.=' AND cp_clase = "'.$clase.'"';
      }else{
        $sql = 'SELECT * FROM cfdi_prodserv WHERE cp_nmb LIKE "%'.$nmb.'%" OR cp_clave LIKE "%'.$nmb.'%"';
        if($clase) $sql.=' AND cp_clase = "'.$clase.'"';
      }
      $this->result = setq($sql) or die($sql);
      $this->resultn = setq($sql) or die($sql);
    }
    function insertps(){
      while($row = $this->result->fetch_array()){
        if(isset($_POST['ch'.$row['cp_clave']])){
          $sqli = 'INSERT IGNORE INTO prodsat SET
                    p_empresa = "'.$_SESSION['emp'].'",
                    p_alias = "'.mb_strtoupper($row['cp_nmb']).'",
                    p_nmb = "'.$row['cp_nmb'].'",
                    p_claveps = "'.$row['cp_clave'].'",
                    p_estatus = "A"';
          setq($sqli) or die($sqli);
        }
      }
    }
  }

  /* -----------------------------------------------VIEW---------------------------------------------------------------------- */

  class viewproductos {
    var $model;
    function __construct($model) {
      $this->model = $model;
      $this->tipops = array("S"=>"Servicio","P"=>"Producto");
    }
    function edit() {
      $accion = 'insert';
      if (isset($this->model->id)) {
        $accion = 'update';
      }
      //Sección: E1 Encabezado - Botones de acción
      echo '<div class="page-title-actions row">
        <div class="col-4 col-md-1">';
          echo '<a href="?modulo=prodsat&accion=edit" accesskey="">
            <button class="mb-2 mr-2 btn btn-warning">
              <i class="fa fa-arrow-left"></i> Regresar a listado
            </button>
          </a>
        </div>';
        echo '<center> 
          <table border="0" cellspacing="0" width="100%" > <tr><td width="40%" valign="top">
            <form method=post action=?modulo=prodsat&accion='.$accion.'>
              <table border="0" cellspacing="0" width="100%" class="lista">
                <thead><tr><th colspan=5>prodsat</th></tr></thead>';
                if ($accion == 'update') {
                  echo '<tr>
                    <th><b>Id</b></th>
                    <td><input type=hidden name=id value="'.$this->model->id.'" required autofocus  placeholder="Campo Obligatorio">'.$this->model->id.'</td>
                  </tr>';
                }else {
                  echo '<tr>
                    <th><b>Id</b></th>
                    <td><input type=hidden name=id value="'.$this->model->id.'" required placeholder="Campo Obligatorio"></td>
                  </tr>';
                }
                echo '<tr>
                  <th><b>Nombre</b></th>
                  <td>
                    <input type="tex" />
                  </td>
                </tr>';
                echo '<tr>
                  <th><b>Estatus</b></th>';
                  if(busca($this->model->id,'prodsat','p_id','p_estatus') == 0 AND $accion=="update")
                    echo'<td><input type="checkbox" name=estatus ></td>';
                  else
                    echo'<td><input type="checkbox" name=estatus checked></td>';
                echo '</tr>
                <tr>
                  <td colspan="2" align="center">
                    <input id="guardar" class="botont" type=submit name=save value="">
                  </td>
                </tr>
              </table>
            </form>
          </center>';
          echo '</td><td valign="top" width="60%">';
          echo'<table border="0" cellspacing="0" width="30%" >';
            $this->model->result();
            $this->show();
          echo'</table></td></tr></table>';
    }
    function show() {
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
          $nuevo = '<a id="nuevo" href="?modulo=prodsat&accion=indexps" accesskey="">
            <button type="button" class="btn btn-primary"><i class="fa fa-plus"></i> Nuevo</button>
          </a>';
          toolbar('PRODUCTOS SAT', '', '', $nuevo);
        echo '<div class="table-responsive" ><center>
        <table border="0" cellspacing="1" width="100%" class=" table table-hover table-striped" id="myTable">
          <thead class="bg-light-blue bg-darken-2 p-1">
            <tr><th colspan="6">Listado de productos usables</th></tr>
            <tr>
              <th width="5%"><center>Clave</center></th>
              <th width="15%"><center>Alias</center></th>
              <th width="25%"><center>Producto</center></th>
              <th width="35%"><center>División</center></th>
              <th width="20%"><center>Grupo</center></th>
              <th width="5%"><center>Acción</center></th>
            </tr>
          </thead><tbody>';
          $autof = "";
          while ($row = $this->model->resultalm->fetch_array()) {
            $div = substr($row['p_claveps'],0,2);
            $grup = substr($row['p_claveps'],2,2);
            echo '<tr><th><center><b>'.$row['p_claveps'].'</b></center></th>
              <td>
                <form action="?modulo=prodsat&accion=updatealias&id='.$row['p_id'].'" method="post">
                  <input type="text" class="form-control" style="font-size:8pt;" name="alias" value="'.$row['p_alias'].'" onfocus="this.select();" onchange="submit();" '.$autof.' />
                </form>
              </td>
              <td><b>'.$row['p_nmb'].'</b></td>
              <td>'.busca($div,'catps_divisiones','cd_clave','cd_nmb').'</td>
              <td>'.busca($grup,'catps_grupos','cg_division = "'.$div.'" AND cg_grupo','cg_nmb').'</td>';
              if($row['p_estatus'] == "A")
                echo '<td>
                  <a href="?modulo=prodsat&accion=delps&id='.$row['p_id'].'">
                    <button class="btn btn-sm btn-danger"><i class="fas fa-eraser"></i> Borrar</button>
                  </a>
                </td>';
            echo '</tr>';
            if(isset($_GET['id']) && $_GET['id'] == $row['p_id']) $autof = "autofocus";
            else $autof = "";
          }
        echo '<tbody></table></center></div>
        
        <script>
        $("#myTable").DataTable( {
            paging: true,
            scrollY: 400,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json",
            },
            responsivePriority: 1,
            pageLength:50,
        });
      </script>';
    }
    function browseps($division,$grupo,$clase,$nmb){
      $atras = '<a class="" href="?modulo=prodsat&accion=index">
        <button class="btn btn-warning">
          <i class="fa fa-arrow-left"></i> Atrás
        </button>
      </a>';
      toolbar('PRODUCTOS SAT', $atras);
      if($division) $grp = true; else $grp = false;
      echo '<div class="container mt-5">
      <form method="post">
        <table >
          <tr>
            <td>Division</td><td style="text-align:left;"> '.menu_select_db('catps_divisiones', 'cd_clave', 'cd_nmb', $division, 'division', 'XXX',  true, false, 'S', false,false,false,true).'</td>
          </tr>
          <tr>
            <td>Grupo</td><td style="text-align:left;"> '.menu_select_db('catps_grupos', 'cg_grupo', 'cg_nmb', $grupo, 'grupo', 'cg_division = "'.$division.'"',  true, false, 'S', false,false,false,$grp).'</td>
          </tr>
          <tr>
            <td>Clase</td><td style="text-align:left;"> '.menu_select_db('catps_clases', 'cc_clase', 'cc_nmb', $clase, 'clase', 'cc_division = "'.$division.'" AND cc_grupo = "'.$grupo.'"',  true, false, 'S', false,false,false,$grp).'</td>
          </tr>
          <tr>
            <td>Buscar por nombre:</td>
            <td style="text-align:left;"> <input type="text" name="nmb" onfocus="this.select();" class="form-control" value="'.$nmb.'" />
              <button type="submit" class="btn btn-primary" /><i class="fa fa-search"></i> Buscar</button>
            </td>
          </tr>';
        echo '</table>
      </form>
      </div>';
      ?>
      <script>
        function seleccionar_todo(){
          for (i=0;i<document.f1.elements.length;i++)
            if(document.f1.elements[i].type == "checkbox")
              document.f1.elements[i].checked=1
        }
        function desseleccionar_todo(){
          for (i=0;i<document.f1.elements.length;i++)
            if(document.f1.elements[i].type == "checkbox")
              document.f1.elements[i].checked=0
        }    
      </script>
      <?php
      echo '<form name="f1" class="table-responsive mt-2" method="post" action="?modulo=prodsat&accion=setproducto&division='.$division.'&grupo='.$grupo.'&clase='.$clase.'">
        <input type="hidden" value="'.$nmb.'" name="nmb"  />
        <table class="table table-hover table-striped" width="100%">
          <thead class="bg-primary bg-darken-2 text-white">';
            if(isset($this->model->resultn))
            echo '<tr>
              <th>
                <button type="button" class="btn btn-success" id="marcar" onclick="seleccionar_todo();" /><i class="icon-circle"></i> Marcar</button>
              </th>
              <th>
                <button type="button" class="btn btn-danger" id="marcar" onclick="desseleccionar_todo();" /><i class="icon-circle-o"></i> Marcar</button>
              </th>
              <th>
                <button type="submit" class="btn btn-secondary" id="marcar" /><i class="fa fa-save"></i> Agregar productos seleccionados a mi catalogo</button>
              </th>
              <th></th>
            </tr>';
            echo '<tr>
              <th width="8%">Seleccionar</th>
              <th width="17%">Clave</th>
              <th width="60%">Nombre</th>
              <th width="15%">Tipo</th>
            </tr>
          </thead>';
          if(isset($this->model->resultn)){
            while($row = $this->model->result->fetch_array()){
              if(busca($row['cp_clave'],'prodsat','p_claveps','COUNT(*)') > 0) $cveps = "checked";
              else $cveps = "";
              echo '<tr>
                <th><input type="checkbox" name="ch'.$row['cp_clave'].'" '.$cveps.' /></th>
                <td>'.$row['cp_clave'].'</td>';
                echo '<td>'.$row['cp_nmb'].'</td>
                <td>'.$this->tipops[busca($row['cp_division'],'catps_divisiones','cd_clave','cd_ps')].'</td>
              </tr>';
            }
          }else
            echo '<tr>
              <td colspan="6"><span style="color: #CC0000">Para realizar una busqueda mas precisa se recomienda seleccionar al menos DIVISIÓN Y GRUPO</span></td>
            </tr>';
        echo '</table></form>';
    }
  }
?>