<?php
class Seguridad{
  var $modulo;
  var $accion;

function Seguridad($modulo, $accion){ // constructor
  $this->modulo = strtoupper($modulo);
  $this->accion = strtoupper($accion);
}

  function privilegio(){
    $sql='SELECT u_grupo FROM usuarios WHERE u_id="'.$_SESSION['uid'].'"';
    $result = setq($sql);
    list($grupo) = $result->fetch_row();
    $sql='SELECT COUNT(*) FROM gruposd
  		WHERE gd_grupo="'.$grupo.'"
  		AND gd_modulo="'.$this->modulo.'"
  		AND gd_accion="'.$this->accion.'"';

    $result=setq($sql);
    if (!isset($privilegio)) $privilegio = NULL;
    if (!isset($modulo)) $modulo = NULL;
    if (!isset($accion)) $accion = NULL;
    list($privilegio)=$result->fetch_row();
    return $privilegio;
  }
}
?>