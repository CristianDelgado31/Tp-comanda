<?php
require_once 'Db/BaseDeDatos.php';

class Producto {
    public $id;
    public $nombre;
    public $tipo;
    public $precio;
    
    public function __construct($nombre, $tipo, $precio) {
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->precio = $precio;

    }

    public static function MostrarLista(){
        $lista = BaseDeDatos::ListarProductos();
        $listaRetorno = array();

        foreach ($lista as $producto) {
            $productoAux = new Producto($producto['nombre'], $producto['tipo'], $producto['precio']);
            $productoAux->id = $producto['id'];
            array_push($listaRetorno, $productoAux); // array_push — Inserta uno o más elementos al final de un array
        }

        return $listaRetorno; // Devuelve un array de objetos Producto
    }

    public function AgregarProducto(){
        BaseDeDatos::AgregarProducto($this->nombre, $this->tipo, $this->precio);
    }

    public static function VerificarProducto($nombre){
        $listaProductos = BaseDeDatos::ListarProductos();
        foreach ($listaProductos as $producto) {
            if ($producto['nombre'] == $nombre) {
                return true; // El producto existe
            }
        }
        return false; // El producto no existe
    }
}





?>