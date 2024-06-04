<?php
require_once '../Persona.php';

class Empleado extends Persona {
    public $estado;
    
    public function __construct($nombre, $apellido, $estado) {
        parent::__construct($nombre, $apellido);
        $this->estado = $estado;
    }
}







?>