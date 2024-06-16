<?php
// require_once '../Persona.php';
require_once 'Persona.php';

class Empleado extends Persona {
    public $estado; // Activo, Inactivo, Vacaciones, etc.
    public $fechaBaja; // Fecha en la que se dio de baja
    
    public function __construct($nombre, $apellido, $rol, $estado = "activo") { // si estado no se pasa, por defecto es activo
        parent::__construct($nombre, $apellido, $rol);
        $this->estado = $estado;
    }

    public function AgregarEmpleado(){
        $nombre = $this->nombre;
        $apellido = $this->apellido;
        $rol = $this->rol;
        $estado = $this->estado;
        BaseDeDatos::AgregarUsuario($nombre, $apellido, $rol, $estado);
    }
}







?>