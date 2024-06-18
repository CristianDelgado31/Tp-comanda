<?php
require_once 'Db/BaseDeDatos.php';

class Pedido {
    public $id;
    public $codigoAlfanumerico;
    public $productos;
    public $nombreCliente;
    public $codigoMesa;
    public $estado; // pendiente, en preparacion, listo, entregado
    public $tiempoEstimado; // el tiempo mas alto de todos los productos que pertenecen al pedido
    public $precioFinal; // lo calculo haciendo la suma de precios de los productos


    public function __construct($codigoAlfanumerico, $nombreCliente, $codigoMesa, $estado, $productos) {
        $this->codigoAlfanumerico = $codigoAlfanumerico;
        $this->nombreCliente = $nombreCliente;
        $this->codigoMesa = $codigoMesa;
        $this->estado = $estado;
        $this->productos = $productos;
        $this->precioFinal = 0;
    }

    public static function MostrarLista() {
        $listaPedidos = BaseDeDatos::ListarPedidos(); // esto devuelve un array de pedidos
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();
        $listaRetorno = array();

        foreach ($listaPedidos as $pedido) {
            $productos = array();
            foreach ($listaPedidosProductos as $pedidoProducto) {
                if ($pedido['codigoAlfanumerico'] == $pedidoProducto['codigo_pedido']) {
                    $productos[] = $pedidoProducto; // agrego el producto al pedido correspondiente
                }
            }
            $pedido['productos'] = $productos; // agrego los productos al pedido
            $listaRetorno[] = $pedido;
        }

        return $listaRetorno; // devuelvo un array de pedidos con sus productos
    }

    public static function ListarPedidosProductos($rol) {

        $arrUsuarios = array(
            "cocinero" => array("comida"), //postres?
            "bartender" => array("trago", "vino"),
            "cervecero" => array("cerveza")
        );

        // // faltan los mozos y socios
        // if($rol != "cocinero" && $rol != "bartender" && $rol != "cervecero"){
        //     return 0; // El rol ingresado no existe
        // }

        // Verifica si el rol no está en el arreglo
        if (!array_key_exists($rol, $arrUsuarios)) {
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
            return 1; // No hay pedidos pendientes
        }

        return $listaResult;
    }

    public function AgregarPedido(){
        //buscar si existe la mesa
        $listaMesas = BaseDeDatos::ListarMesas();
        $flagMesa = false;

        foreach ($listaMesas as $mesa) {
            if ($mesa['codigoIdentificacion'] == $this->codigoMesa) {
                if($mesa['estado'] != "libre"){
                    return -3; // La mesa no esta libre
                }
                $flagMesa = true;
                break;
            }
            // else {
            //     $flagMesa = true;
            // }
        }

        if(!$flagMesa){ // si es false es porque no encontro la mesa
            return -1; // La mesa no existe
        }

        //buscar si existe el pedido
        $listaPedidos = BaseDeDatos::ListarPedidos();

        foreach ($listaPedidos as $pedido) {
            if ($pedido['codigoAlfanumerico'] == $this->codigoAlfanumerico) {
                return -2; // El codigoAlfanumerico del pedido ya existe
            }
        }

        $listaProductos = BaseDeDatos::ListarProductos();

        $arrProductosPedido = array();

        foreach ($this->productos as $producto) {
            $flagProducto = false;
            foreach ($listaProductos as $productoBD) {
                if ($producto->nombre == $productoBD['nombre']) {
                    $flagProducto = true;
                    $productoBD['cantidad'] = $producto->cantidad; // agrego la cantidad al producto
                    $arrProductosPedido[] = $productoBD;
                }
            }
            if(!$flagProducto){ // si es false es porque no encontro el producto
                return -4; // Error -> No se guarda el pedido porque un producto no existe
            }
        }

        foreach ($arrProductosPedido as $productoBD) {
            $this->precioFinal += $productoBD['precio'] * $productoBD['cantidad']; // Usar cantidad del producto original
    
            // Si la cantidad es mayor a 1, se agregan tantos productos como cantidad haya
            if($productoBD['cantidad'] > 1){
                for ($i = 0; $i < $productoBD['cantidad']; $i++) {
                    BaseDeDatos::AgregarPedidoProducto($this->codigoAlfanumerico, $productoBD['id'], "pendiente");
                }
            }
            else {
                BaseDeDatos::AgregarPedidoProducto($this->codigoAlfanumerico, $productoBD['id'], "pendiente");
            }
        }

        BaseDeDatos::ModificarEstadoMesa($this->codigoMesa, "con cliente esperando pedido");
        BaseDeDatos::AgregarPedido($this->codigoAlfanumerico, $this->nombreCliente, $this->codigoMesa, $this->estado, $this->precioFinal);

    }

    public static function AgarrarProductoDePedido($nombre, $apellido, $id_pedido_producto, $estado, $tiempoPreparacion){
        $id_usuario = 0;
        $listaEmpleados = BaseDeDatos::ListarUsuarios();
        $flag = false;
        $empleadoEncontrado = null; // para guardar el empleado que agarra el pedido_producto

        foreach ($listaEmpleados as $empleado) { // empleado es un array asociativo
            if ($empleado['nombre'] == $nombre && $empleado['apellido'] == $apellido) {
                // si el empleado esta ocupado no se le permitira agarrar un pedido_producto
                if($empleado['estado'] == "ocupado"){
                    return 4; // El empleado esta ocupado
                }
                $id_usuario = $empleado['id'];
                break;
            }
        }

        $listaPedidos = BaseDeDatos::ListarPedidos();
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();

        $existeIdProducto = false;

        foreach($listaPedidosProductos as $pedidoProducto){
            if(intval($pedidoProducto['id']) == $id_pedido_producto){
                $existeIdProducto = true;
                break;
            }
        }

        if(!$existeIdProducto){
            return 5; // El pedido_producto ingresado no existe
        }

        if(self::verificarPermisos($id_pedido_producto, $id_usuario) == 1){
            return 1; // El rol del usuario no tiene permisos para tomarlo
        }
 
        if($id_usuario == 0){
            return -1; // El empleado/usuario no existe
        }

        foreach ($listaPedidosProductos as $pedidoProducto) { // pedidoProducto es un array asociativo
            if (intval($pedidoProducto['id']) == $id_pedido_producto) {
                if($pedidoProducto['estado'] != "pendiente"){
                    return 2; // El pedido_producto ya fue tomado por otro usuario
                }
                $pedidoProducto['estado'] = $estado;
                $pedidoProducto['id_usuario'] = $id_usuario;
                $pedidoProducto['tiempo_producto'] = $tiempoPreparacion;
                
                foreach ($listaPedidos as $pedido) {
                    if ($pedido['codigoAlfanumerico'] == $pedidoProducto['codigo_pedido']) {
                        $pedido['estado'] = "en preparacion";
                        if($pedido['tiempoEstimado'] == null || $pedido['tiempoEstimado'] < $tiempoPreparacion){
                            $pedido['tiempoEstimado'] = $tiempoPreparacion;
                        }
                        BaseDeDatos::ActualizarPedido($pedido);
                        break;
                    }
                }
                $arrUsuario = array("id" => $id_usuario, "estado" => "ocupado");
                BaseDeDatos::ActualizarUsuario($arrUsuario);
                BaseDeDatos::ActualizarPedidoProducto($pedidoProducto);
            }
        }
        

    }

    public static function verificarPermisos($id_pedido_producto, $id_usuario) {
        // Obtener la lista de productos desde la base de datos
        $listaProductos = BaseDeDatos::ListarProductos();
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();
        $listaUsuarios = BaseDeDatos::ListarUsuarios();
    
        // Definir los permisos por rol
        $arrUsuarios = array(
            "cocinero" => array("comida", "postres"), // Añadir "postres" si corresponde
            "bartender" => array("trago", "vino"),
            "cervecero" => array("cerveza")
        );

        // obtener el rol del usuario
        $rol = "";
        foreach ($listaUsuarios as $usuario) {
            if ($usuario['id'] == $id_usuario) {
                $rol = $usuario['rol'];
                break;
            }
        }

        // Verificar si el rol no está en el arreglo
        if (!array_key_exists($rol, $arrUsuarios)) {
            return 0; // El rol ingresado no existe
        }

        // Verificar si el producto pertenece al rol del usuario
        foreach ($listaPedidosProductos as $pedidoProducto) {
            if ($pedidoProducto['id'] == $id_pedido_producto) {
                foreach ($listaProductos as $producto) {
                    if ($pedidoProducto['id_producto'] == $producto['id']) {
                        if (in_array($producto['tipo'], $arrUsuarios[$rol])) {
                            return 0; // El empleado tiene permisos para tomar el producto
                        }
                    }
                }
            }
        }


        return 1; // El empleado no tiene permisos para tomar el producto
    }

    public static function TiempoEstimadoDelPedido($codigoPedido, $codigoMesa) {
        $listaPedidos = BaseDeDatos::ListarPedidos();
        $tiempoEstimado = 0;
    
        foreach ($listaPedidos as $pedido) {
            if ($pedido['codigoAlfanumerico'] == $codigoPedido && $pedido['codigoMesa'] == $codigoMesa) {
                // Verificar si $pedido['tiempoEstimado'] es null
                if (!is_null($pedido['tiempoEstimado'])) {
                    $tiempoEstimado = $pedido['tiempoEstimado'];
                } else {
                    // Manejar el caso donde $pedido['tiempoEstimado'] es null
                    $tiempoEstimado = -1;
                }
                break;
            }
        }
    
        if($tiempoEstimado == 0) {
            return 0; // El pedido no existe
        }
        else if($tiempoEstimado == -1){
            return -1; // El tiempo estimado es nulo (no se ha calculado)
        }
    
        return $tiempoEstimado;
    }
    

    public static function FinalizarProductoDePedido($usuario) {
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();
        $registroProductoPedido = null;

        foreach ($listaPedidosProductos as $productoPedido) {
            if ($productoPedido['id_usuario'] == $usuario->id && $productoPedido['estado'] == "en preparacion") {
                $productoPedido['estado'] = "listo para servir";
                BaseDeDatos::ActualizarPedidoProducto($productoPedido); // Actualizar el estado del producto
                $registroProductoPedido = $productoPedido; // Guardar el producto para devolverlo
                break;
            }
        }

        if ($registroProductoPedido === null) {
            return -1; //El usuario no tiene productos de pedido en preparación
        }

        $listaPedidos = BaseDeDatos::ListarPedidos();
        $contador = 0;

        foreach ($listaPedidosProductos as $productoPedido) {
            if ($productoPedido['codigo_pedido'] == $registroProductoPedido['codigo_pedido']) {
                if ($productoPedido['estado'] == "pendiente" || $productoPedido['estado'] == "en preparacion") {
                    $contador++; // Contar los productos que no están listos
                }
            }
        }

        if($contador == 1) { // Si es 1, significa que el producto que se acaba de finalizar es el último en preparación
            foreach ($listaPedidos as $pedido) {
                if ($pedido['codigoAlfanumerico'] == $registroProductoPedido['codigo_pedido']) {
                    $pedido['estado'] = "listo para servir";
                    BaseDeDatos::ActualizarPedido($pedido); // Actualizar el estado del pedido
                    break;
                }
            }
        }

        $arrUsuario = array("id" => $usuario->id, "estado" => "activo"); // libre
        BaseDeDatos::ActualizarUsuario($arrUsuario); // Actualizar el estado del usuario
    }
    
}


?>