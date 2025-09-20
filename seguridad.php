<?php
class Seguridad {
  private $modulo;
  private $accion;

  // Constructor moderno (PHP 5+)
  public function __construct($modulo, $accion){
    $this->modulo = strtoupper(trim((string)$modulo));
    $this->accion = strtoupper(trim((string)$accion));
  }

  // (Opcional) compatibilidad si en algún lugar llaman Seguridad(...) como método
  public function Seguridad($modulo, $accion){
    // PHP 7/8 ya no lo trata como constructor: delegamos al __construct
    $this->__construct($modulo, $accion);
  }

  public function privilegio(){
    if (empty($_SESSION['uid'])) return 0;

    // 1) Obtener grupo/rol del usuario
    $uid = addslashes($_SESSION['uid']);
    $sql = 'SELECT ur_rol FROM usuarios_rol WHERE ur_usuario="'.$uid.'" LIMIT 1';
    $result = setq($sql);
    $grupo = '';
    if ($result && ($row = $result->fetch_row())) {
      $grupo = $row[0];
    }
    if ($grupo === '') return 0; // sin rol → sin privilegio

    // 2) Verificar privilegio para módulo/acción
    $sql = 'SELECT COUNT(*) FROM gruposd
            WHERE gd_grupo="'.addslashes($grupo).'"
              AND gd_modulo="'.addslashes($this->modulo).'"
              AND gd_accion="'.addslashes($this->accion).'"';

    // Quita cualquier die/echo de debug aquí
    $result = setq($sql);
    $priv = 0;
    if ($result && ($row = $result->fetch_row())) {
      $priv = (int)$row[0];
    }
    return $priv;
  }
}
