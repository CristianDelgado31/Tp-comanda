<?php
// require_once '../Persona.php';
require_once 'Persona.php';

class Empleado extends Persona {
    public $cantidad_operaciones;

    // public function __construct($nombre, $apellido, $rol, $email, $contrasenia, $estado = "activo") { //agregue el parametro cantidad_operaciones test
    //     parent::__construct($nombre, $apellido, $rol, $email, $contrasenia, $estado);
    // }

    public function __construct($nombre, $apellido, $rol, $email, $contrasenia, $cantidad_operaciones, $estado = "activo") { //agregue el parametro cantidad_operaciones test
        parent::__construct($nombre, $apellido, $rol, $email, $contrasenia, $estado);
        $this->cantidad_operaciones = $cantidad_operaciones;
    }
}







?>