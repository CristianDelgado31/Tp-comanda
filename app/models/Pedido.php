<?php

class Pedido {
    public $codigoAlfanumerico;
    public $productos;
    public $nombreCliente;
    public $codigoMesa;
    public $estado; // pendiente, en preparacion, listo, entregado
    public $tiempoEstimado; // el tiempo mas alto de todos los productos que pertenecen al pedido
    public $precioFinal; // lo calculo haciendo la suma de precios de los productos


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