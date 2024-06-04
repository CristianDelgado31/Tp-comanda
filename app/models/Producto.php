<?php

class Producto {
    public $nombre;
    public $tipo; // comida o bebida
    public $cantidad;
    
    public function __construct($nombre, $tipo, $cantidad) {
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->cantidad = $cantidad;
    }
}





?>