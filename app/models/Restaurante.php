<?php
// require_once 'Empleados/Mozo.php';
// require_once 'Empleados/Cocinero.php';
// require_once 'Empleados/Bartender.php';
// require_once 'Empleados/Cervecero.php';
// require_once 'Personas/Socio.php';

require_once 'Db/BaseDeDatos.php';

class Restaurante {

    public static function AgregarUsuario($empleado) { // $empleado es un objeto JSON
        $nombre = $empleado->nombre;
        $apellido = $empleado->apellido;
        $rol = $empleado->rol;
        $estado = $empleado->estado;

        BaseDeDatos::AgregarUsuario($nombre, $apellido, $rol, $estado);
    }
    
    public static function ListarUsuarios() {
        return BaseDeDatos::ListarUsuarios();
    }

    public static function AgregarProducto($producto) {
        $nombre = $producto->nombre;
        $tipo = $producto->tipo;
        $precio = $producto->precio;
        BaseDeDatos::AgregarProducto($nombre, $tipo, $precio);
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
        $codigoMesa = $pedido->codigoMesa;
        $estado = $pedido->estado;
        $precioFinal = 0;
        // $tiempoEstimado = $pedido->tiempoEstimado;

        $listaProductos = BaseDeDatos::ListarProductos();

        foreach ($pedido->productos as $producto) {
            foreach ($listaProductos as $productoBD) {
                if ($producto->nombre == $productoBD['nombre']) {
                    $precioFinal += $productoBD['precio'] * $producto->cantidad;
                    BaseDeDatos::AgregarPedidoProducto($codigoAlfanumerico, $productoBD['id'], "pendiente");
                    // echo $productoBD['id'];
                }
            }
        }


        BaseDeDatos::AgregarPedido($codigoAlfanumerico, $nombreCliente, $codigoMesa, $estado, $precioFinal);
    }

    public static function ListarPedidos() {
        $listaPedidos = BaseDeDatos::ListarPedidos(); // esto devuelve un array de pedidos
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();
    
        foreach ($listaPedidos as &$pedido) { // Accedemos a cada pedido por referencia
            $pedido['productos'] = array();
            foreach ($listaPedidosProductos as $pedidoProducto) {
                if ($pedido['codigoAlfanumerico'] == $pedidoProducto['codigo_pedido']) {
                    $pedido['productos'][] = $pedidoProducto; // agrego el producto al pedido correspondiente
                }
            }
        }
        unset($pedido); // Importante: romper la referencia después del bucle
    
        // echo json_encode($listaPedidos);
        return $listaPedidos;
    }
    

}





?>