<?php
class aplicacion {
  var $default_module = 'Home';
  var $default_action = 'index';

  function carga($modulo){
    if(!file_exists('modulos/'.$modulo.'.php')){
      die('Error. No existe el archivo: modulos/'.$modulo.'.php');
    }
    include('modulos/'.$modulo.'.php');
    $this->modulo = new $modulo();
  }

  function ejecuta($accion){
    $this->modulo->$accion();
  }
}
?>