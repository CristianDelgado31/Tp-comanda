<?php

class Producto {
    public $nombre;
    public $tipo; // comida o bebida
    public $precio;
    
    public function __construct($nombre, $tipo, $precio) {
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->precio = $precio;

    }
}





?>