<?php
// require_once '../Persona.php';
require_once 'Persona.php';

class Empleado extends Persona {
    // public $estado; // Activo, Inactivo, Vacaciones, etc.
    // public $fechaBaja; // Fecha en la que se dio de baja
    
    public function __construct($nombre, $apellido, $rol, $email, $contrasenia, $estado = "activo") {
        parent::__construct($nombre, $apellido, $rol, $email, $contrasenia, $estado);
        // $this->estado = $estado;
    }

    // public function AgregarEmpleado(){
    //     $nombre = $this->nombre;
    //     $apellido = $this->apellido;
    //     $rol = $this->rol;
    //     $estado = $this->estado;
    //     $email = $this->email;
    //     $contrasenia = $this->contrasenia;
    //     BaseDeDatos::AgregarUsuario($nombre, $apellido, $rol, $email, $contrasenia, $estado);
    // }
}







?>