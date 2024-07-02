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

    public function ModificarProducto() {
        $listaProductos = self::MostrarLista();
        $productoExistente = null;
        $nombreDuplicado = false;
    
        foreach ($listaProductos as $producto) {
            if ($producto->id == $this->id) {
                $productoExistente = $producto;
            }
            if ($producto->nombre == $this->nombre && $producto->id != $this->id) {
                $nombreDuplicado = true;
            }
        }
    
        if ($productoExistente === null) {
            throw new Exception("No existe el ID");
            // return -1; // No existe el ID
        }
    
        if ($nombreDuplicado) {
            throw new Exception("El producto ya existe con ese nombre (ID diferente)");
            // return false; // El producto ya existe con ese nombre (ID diferente)
        }
    
        // Si llega hasta acá, el producto existe y no hay duplicado de nombre
        BaseDeDatos::ModificarProducto($this->id, $this->nombre, $this->tipo, $this->precio);
        // return true; // Producto modificado
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
            throw new Exception("No existe el ID");
            // return -1; // No existe el id
        }
        else if($flagFechaBaja == true){
            throw new Exception("El producto ya fue dado de baja anteriormente");
            // return false; // El producto ya fue dado de baja
        }
        $fecha_baja = date('Y-m-d');
        BaseDeDatos::EliminarProducto($id, $fecha_baja);
        // return true; // Producto eliminado
    }

    public static function GenerarHtmlDeProductos(){
        $listaProductos = self::MostrarLista();
        $html = '<h1>Lista de Productos</h1>';
        $html .= '<table border="1" width="100%">';
        $html .= '<tr><th>Nombre</th><th>Tipo</th><th>Precio</th></tr>';
        foreach ($listaProductos as $producto) {
            $html .= '<tr>';
            // $html .= '<td>' . $producto->id . '</td>';
            $html .= '<td>' . $producto->nombre . '</td>';
            $html .= '<td>' . $producto->tipo . '</td>';
            $html .= '<td>' . $producto->precio . '</td>';
            // $html .= '<td>' . $producto->fecha_baja . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    public static function ReactivarProducto($id){
        $listaProductos = self::MostrarLista();
        $flag = false;
        $flagFechaBaja = false;
        foreach ($listaProductos as $producto) {
            if ($producto->id == $id) {
                if($producto->fecha_baja == null){
                    $flagFechaBaja = true; // El producto no fue dado de baja
                }
                $flag = true;
                break;
            }
        }

        if($flag == false){
            throw new Exception("No existe el ID");
            // return -1; // No existe el id
        }
        else if($flagFechaBaja == true){
            throw new Exception("El producto no fue dado de baja anteriormente");
            // return false; // El producto no fue dado de baja
        }
        BaseDeDatos::ReactivarProducto($id);
        // return true; // Producto reactivado
    }
}





?>