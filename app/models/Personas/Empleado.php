<?php
// require_once '../Persona.php';
require_once 'Persona.php';

class Empleado extends Persona {
    
    public function __construct($nombre, $apellido, $rol, $email, $contrasenia, $estado = "activo") {
        parent::__construct($nombre, $apellido, $rol, $email, $contrasenia, $estado);
    }

    
}







?>