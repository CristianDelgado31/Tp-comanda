<?php
// require_once 'Empleados/Mozo.php';
// require_once 'Empleados/Cocinero.php';
// require_once 'Empleados/Bartender.php';
// require_once 'Empleados/Cervecero.php';
// require_once 'Personas/Socio.php';

require_once 'Db/BaseDeDatos.php';

class Restaurante {

    public static function AgregarEmpleado($empleado) { // $empleado es un objeto JSON
        $nombre = $empleado->nombre;
        $apellido = $empleado->apellido;
        $estado = $empleado->estado;
        $rol = $empleado->rol;
        BaseDeDatos::AgregarEmpleado($nombre, $apellido, $rol, $estado);
    }
    
    public static function ListarEmpleados() {
        return BaseDeDatos::ListarEmpleados();
    }

    public static function AgregarSocio($socio) {
        $nombre = $socio->nombre;
        $apellido = $socio->apellido;
        BaseDeDatos::AgregarSocio($nombre, $apellido);
    }

    public static function AgregarProducto($producto) {
        $nombre = $producto->nombre;
        $tipo = $producto->tipo;
        $cantidad = $producto->cantidad;
        BaseDeDatos::AgregarProducto($nombre, $tipo, $cantidad);
    }

    public static function AgregarMesa($mesa) {
        $codigoIdentificacion = $mesa->codigoIdentificacion;
        $estado = $mesa->estado;
        BaseDeDatos::AgregarMesa($codigoIdentificacion, $estado);
    }

    public static function ListarMesas() {
        return BaseDeDatos::ListarMesas();
    }

    public static function AgregarPedido($pedido) {
        $codigoAlfanumerico = $pedido->codigoAlfanumerico;
        $nombreCliente = $pedido->nombreCliente;
        $estado = $pedido->estado;
        $tiempoEstimado = $pedido->tiempoEstimado;

        $listaProductos = BaseDeDatos::ListarProductos();

        foreach ($pedido->productos as $producto) {
            foreach ($listaProductos as $productoBD) {
                if ($producto->nombre == $productoBD['nombre']) {
                    BaseDeDatos::AgregarPedido($codigoAlfanumerico, $nombreCliente, $estado, $tiempoEstimado, $productoBD['id'], $producto->cantidad);
                }
            }
        }
        
    }

    public static function ListarPedidos() {
        return BaseDeDatos::ListarPedidos();
    }

}





?>