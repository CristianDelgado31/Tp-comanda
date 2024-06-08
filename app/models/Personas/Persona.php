<?php

// namespace app\models;

class Persona {
    public $nombre;
    public $apellido;
    public $rol;
    
    public function __construct($nombre, $apellido, $rol) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->rol = $rol;
    }
}






?>