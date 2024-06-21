<?php
require_once 'Db/BaseDeDatos.php';

class Producto {
    public $id;
    public $nombre;
    public $tipo;
    public $precio;
    public $fecha_baja;
    
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
            $productoAux->fecha_baja = $producto['fecha_baja'];
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

    public function ModificarProducto(){
        $flagId = false;
        $flagNombre = false;
        $listaProductos = self::MostrarLista();
        foreach ($listaProductos as $producto) {
            if ($producto->nombre == $this->nombre) {
                if($producto->id == $this->id){
                    BaseDeDatos::ModificarProducto($this->id, $this->nombre, $this->tipo, $this->precio);
                    return true; // Producto modificado
                }
                else{
                    $flagNombre = true;
                }
            }
            if($producto->id == $this->id){
                $flagId = true;
            }
        }
        
        if($flagId == false){
            return -1; // No existe el id
        }
        else if($flagNombre == true){
            return false; // El producto ya existe con ese nombre (id diferente)
        }

        // Si llega hasta acá es porque no encontró el producto con ese nombre y el id es correcto
        BaseDeDatos::ModificarProducto($this->id, $this->nombre, $this->tipo, $this->precio);
        return true; // Producto modificado
    }

    public static function EliminarProducto($id){
        $listaProductos = self::MostrarLista();
        $flag = false;
        $flagFechaBaja = false;
        foreach ($listaProductos as $producto) {
            if ($producto->id == $id) {
                if($producto->fecha_baja != null){
                    $flagFechaBaja = true; // El producto ya fue dado de baja
                }
                $flag = true;
                break;
            }
        }

        if($flag == false){
            return -1; // No existe el id
        }
        else if($flagFechaBaja == true){
            return false; // El producto ya fue dado de baja
        }
        $fecha_baja = date('Y-m-d');
        BaseDeDatos::EliminarProducto($id, $fecha_baja);
        return true; // Producto eliminado
    }
}





?>