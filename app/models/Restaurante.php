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

    //Mozo
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
    
    public static function ListarPedidosProductos($rol) {

        $arrUsuarios = array(
            "cocinero" => array("comida"), //postres?
            "bartender" => array("trago", "vino"),
            "cervecero" => array("cerveza")
        );

        // faltan los mozos y socios
        if($rol != "cocinero" && $rol != "bartender" && $rol != "cervecero"){
            return 0; // El rol ingresado no existe
        }

        $listaPedidosProductos= BaseDeDatos::ListarPedidosProductos();
        $listaProductos = BaseDeDatos::ListarProductos();
        $listaResult = array();

        foreach ($listaPedidosProductos as $pedidoProducto) {
            foreach ($listaProductos as $producto) {
                if ($pedidoProducto['id_producto'] == $producto['id'] && $pedidoProducto['estado'] == "pendiente") {
                    if (in_array($producto['tipo'], $arrUsuarios[$rol])) {
                        $listaResult[] = $pedidoProducto;
                    }
                }
            }
        }

        if(count($listaResult) == 0){
            return 1;
        }

        return $listaResult;
    }

    public static function AgarrarPedidoProducto($pedido) {
        $id_pedido_producto = $pedido->id_pedido_producto;
        $nombre = $pedido->nombre;
        $apellido = $pedido->apellido;
        $tiempoPreparacion = $pedido->tiempoPreparacion;
        $estado = $pedido->estado;

        $id_usuario = 0;
        $listaEmpleados = BaseDeDatos::ListarUsuarios();
        $listaPedidosProductosPorRol = null;
        $flag = false;
        $errorReturn = 0;
        foreach ($listaEmpleados as $empleado) {
            if ($empleado['nombre'] == $nombre && $empleado['apellido'] == $apellido) {
                // si el empleado esta ocupado no se le permitira agarrar un pedido_producto
                if($empleado['estado'] == "ocupado"){
                    return 0; // El empleado esta ocupado
                }
                else if($empleado['estado'] == "activo"){
                    $id_usuario = $empleado['id'];
                    $listaPedidosProductosPorRol = self::ListarPedidosProductos($empleado['rol']);
                    if($listaPedidosProductosPorRol == 1){ // si el rol no tiene pedidos pendientes
                        return 3;
                    }
                    $empleado['estado'] = "ocupado";
                    BaseDeDatos::ActualizarUsuario($empleado);
                }
                $flag = true;
                break;
            }
        }

        if(!$flag){
            return -1; // El empleado/usuario no existe
        }

        $listaPedidos = BaseDeDatos::ListarPedidos();

        foreach ($listaPedidosProductosPorRol as &$pedidoProducto) { // pedidoProducto es un array asociativo
            if ($pedidoProducto['id'] == $id_pedido_producto) {
                //esto esta demas
                if($pedidoProducto['estado'] != "pendiente")
                    return 2; // El pedido_producto ya fue tomado por otro usuario

                $pedidoProducto['estado'] = $estado;
                $pedidoProducto['id_usuario'] = $id_usuario;
                $pedidoProducto['tiempo_producto'] = $tiempoPreparacion;
                // return BaseDeDatos::ActualizarPedidoProducto($pedidoProducto);
                
                foreach ($listaPedidos as &$pedido) {
                    if ($pedido['codigoAlfanumerico'] == $pedidoProducto['codigo_pedido']) {
                        $pedido['estado'] = "en preparacion";
                        if($pedido['tiempoEstimado'] == null || $pedido['tiempoEstimado'] < $tiempoPreparacion){
                            $pedido['tiempoEstimado'] = $tiempoPreparacion;
                        }
                        BaseDeDatos::ActualizarPedido($pedido);
                        // return $pedido;
                        break;
                    }
                }
                return BaseDeDatos::ActualizarPedidoProducto($pedidoProducto);
            }
        }

    }

}





?>