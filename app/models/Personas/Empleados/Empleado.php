<?php
require_once '../Persona.php';

class Empleado extends Persona {
    public $estado; // Activo, Inactivo, Vacaciones, etc.
    public $fechaBaja; // Fecha en la que se dio de baja
    
    public function __construct($nombre, $apellido, $rol, $estado) {
        parent::__construct($nombre, $apellido, $rol);
        $this->estado = $estado;
    }
}







?>