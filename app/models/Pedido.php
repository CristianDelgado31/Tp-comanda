<?php

class Pedido {
    public $codigoAlfanumerico;
    public $productos;
    public $nombreCliente;
    public $estado; // pendiente, en preparacion, listo, entregado
    public $tiempoEstimado; // en minutos


    public function __construct($codigoAlfanumerico, $nombreCliente) {
        $this->codigoAlfanumerico = $codigoAlfanumerico;
        $this->productos = array();
        $this->nombreCliente = $nombreCliente;
        $this->estado = "pendiente";
    }

    public function agregarProducto($producto) {
        array_push($this->productos, $producto);
    }
}



?>