<?php
require_once 'Persona.php';
// require_once '/home/cristian-delgado/Escritorio/Tp-comanda/app/models/Personas/Persona.php';

class Socio extends Persona {

    public function __construct($nombre, $apellido, $rol) {
        parent::__construct($nombre, $apellido, $rol);
    }

    public function AgregarSocio(){
        $nombre = $this->nombre;
        $apellido = $this->apellido;
        $rol = $this->rol;
        BaseDeDatos::AgregarUsuario($nombre, $apellido, $rol);
    }
}





?>