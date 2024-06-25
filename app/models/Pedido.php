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
    // public $fecha_baja;


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
                if ($pedidoProducto['id_producto'] == $producto['id'] && $pedidoProducto['estado'] == "pendiente" && $producto['fecha_baja'] == null) {
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
            if ($mesa['codigoIdentificacion'] == $this->codigoMesa && $mesa['fecha_baja'] == null) {
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
                if ($producto->nombre == $productoBD['nombre'] && $productoBD['fecha_baja'] == null) {
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

        // Aumentar la cantidad de usos de la mesa
        self::ModificarCantidadUsoDeMesa($this->codigoMesa);

        BaseDeDatos::AgregarPedido($this->codigoAlfanumerico, $this->nombreCliente, $this->codigoMesa, $this->estado, $this->precioFinal);

    }

    public static function ModificarCantidadUsoDeMesa($codigo_mesa){
        $listaMesas = BaseDeDatos::ListarMesas();

        foreach ($listaMesas as $mesa) {
            if ($mesa['codigoIdentificacion'] == $codigo_mesa && $mesa['fecha_baja'] == null) {
                $mesa['cantidad_usos'] += 1;
                BaseDeDatos::ModificarCantidadUsoDeMesa($mesa['codigoIdentificacion'], $mesa['cantidad_usos']);
                break;
            }
        }
    }

    public static function AgarrarProductoDePedido($email, $id_pedido_producto, $estado, $tiempoPreparacion){ //nombre, $apellido
        $id_usuario = 0;
        $listaEmpleados = BaseDeDatos::ListarUsuarios();
        $flag = false;
        $empleadoEncontrado = null; // para guardar el empleado que agarra el pedido_producto

        foreach ($listaEmpleados as $empleado) { // empleado es un array asociativo
            //$empleado['nombre'] == $nombre && $empleado['apellido'] == $apellido
            if ($empleado['email'] == $email) { 
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
                        $tiempo = date('H:i:s');
                        BaseDeDatos::ModificarHoraEnPedido($pedido['id'], $tiempo, "inicio");
                        break;
                    }
                }
                $arrUsuario = array("id" => $id_usuario, "estado" => "ocupado");
                BaseDeDatos::ActualizarEstadoUsuario($arrUsuario);
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
                        if (in_array($producto['tipo'], $arrUsuarios[$rol])) { // 
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
                    // BaseDeDatos::ModificarEstadoMesa($pedido['codigoMesa'], "con cliente comiendo");
                    $tiempo = date('H:i:s');
                    BaseDeDatos::ModificarHoraEnPedido($pedido['id'], $tiempo, "fin");
                    
                    break;
                }
            }
        }

        $arrUsuario = array("id" => $usuario->id, "estado" => "activo"); // libre
        BaseDeDatos::ActualizarEstadoUsuario($arrUsuario); // Actualizar el estado del usuario
    }

    public static function EliminarPedido($id) {
        $listaPedidos = BaseDeDatos::ListarPedidos();
        $flag = false;
        $flagEstado = false;

        foreach ($listaPedidos as $pedido) {
            if ($pedido['id'] == $id && $pedido['fecha_baja'] == null) {

                if($pedido['estado'] == "en preparacion"){
                    $flagEstado = true; // No se puede eliminar un pedido en preparacion
                }

                $flag = true;
                break;
            }
        }

        if(!$flag){
            return 1; // El pedido no existe
        }

        if($flagEstado){
            return 2; // No se puede eliminar un pedido en preparacion
        }
        $fecha_baja = date("Y-m-d");
        BaseDeDatos::EliminarPedido($id, $fecha_baja);

        return 3; // Pedido eliminado correctamente
    }
    
    public static function ModificarPedido($id, $codigoAlfanumerico, $nombreCliente, $codigoMesa, $tiempoEstimado) {
        $listaPedidos = BaseDeDatos::ListarPedidos();
        $flag = false;
        $pedidoAModificar = null;
        $codigoDelaMesaAnterior = "";
        $codigoAlfanumericoAnterior = "";

        foreach ($listaPedidos as $pedido) {
            if ($pedido['id'] == $id && $pedido['fecha_baja'] == null) {
                
                if($codigoAlfanumerico != "") {
                    $codigoAlfanumericoAnterior = $pedido['codigoAlfanumerico'];
                    $pedido['codigoAlfanumerico'] = $codigoAlfanumerico;
                }
                if($nombreCliente != "") {
                    $pedido['nombreCliente'] = $nombreCliente;
                }
                if($codigoMesa != "") {
                    $codigoDelaMesaAnterior = $pedido['codigoMesa'];
                    $pedido['codigoMesa'] = $codigoMesa;
                }
                if($tiempoEstimado != "") {
                    $pedido['tiempoEstimado'] = $tiempoEstimado;
                }
                $pedidoAModificar = $pedido; // Guardar el pedido para devolverlo
                $flag = true;
                break;
            }
        }

        if(!$flag){
            return 1; // El pedido no existe
        }
        
        if($codigoAlfanumerico != ""){
            // Verifico si realmente no hay otro codigo alfanumerico en la base de datos
            foreach ($listaPedidos as $pedido) {
                if ($pedido['codigoAlfanumerico'] == $pedidoAModificar['codigoAlfanumerico'] && $pedido['id'] != $pedidoAModificar['id']) {
                    return 2; // El codigoAlfanumerico del pedido ya existe en otro pedido
                }
            }

            // modifico los productos del pedido en la base de datos
            $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();

            foreach ($listaPedidosProductos as $pedidoProducto) {
                if ($pedidoProducto['codigo_pedido'] == $codigoAlfanumericoAnterior) {
                    BaseDeDatos::ModificarCodigoPedidoProducto($pedidoProducto['id'], $pedidoAModificar['codigoAlfanumerico']);
                }
            }
        }

        if($codigoMesa != "") {
            //verifico si la mesa existe y esta libre
            $listaMesas = BaseDeDatos::ListarMesas();
            $flagMesa = false;
            $estadoMesaAnterior = "";
            foreach ($listaMesas as $mesa) {
                if($codigoDelaMesaAnterior == $mesa['codigoIdentificacion']){
                    $estadoMesaAnterior = $mesa['estado'];
                }

                if ($mesa['codigoIdentificacion'] == $pedidoAModificar['codigoMesa'] && $mesa['fecha_baja'] == null) {
                    if($mesa['estado'] != "libre"){
                        return 4; // La mesa no esta libre
                    }
                    $flagMesa = true;
                    // break;
                }
            }
    
            if(!$flagMesa){ // si es false es porque no encontro la mesa
                return 3; // La mesa no existe
            }
            
            //modifico el estado de la mesa nueva
            BaseDeDatos::ModificarEstadoMesa($pedidoAModificar['codigoMesa'], $estadoMesaAnterior);
            //modifico el estado de la mesa anterior
            BaseDeDatos::ModificarEstadoMesa($codigoDelaMesaAnterior, "libre");
        }

        //Si se llega hasta acá se modifican los datos en la base de datos
        BaseDeDatos::ModificarAtributosPedido($pedidoAModificar);
        
        return 5; // Pedido modificado correctamente
    }

    public static function ModificarProductoDePedido($id, $codigo_pedido, $id_producto){
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();
        $flagId = false;
        $pedidoProductoAModificar = null;
        $codigo_pedidoAnterior = "";
        $id_productoAnterior = "";
        $flagIdProductoIdentido = false;

        foreach ($listaPedidosProductos as $pedidoProducto) {
            if ($pedidoProducto['id'] == $id) {
                if($codigo_pedido != ""){
                    $codigo_pedidoAnterior = $pedidoProducto['codigo_pedido'];
                    $pedidoProducto['codigo_pedido'] = $codigo_pedido;
                }
                $id_productoAnterior = $pedidoProducto['id_producto'];
                if($id_producto != ""){
                    $pedidoProducto['id_producto'] = $id_producto;
                }

                $pedidoProductoAModificar = $pedidoProducto;
                $flagId = true;
                break;
            }
        }
        if($codigo_pedidoAnterior == $pedidoProductoAModificar['codigo_pedido'] && $id_productoAnterior == $pedidoProductoAModificar['id_producto'] || 
            $codigo_pedidoAnterior == $pedidoProductoAModificar['codigo_pedido'] && $id_producto == "" || 
            $codigo_pedido == "" && $id_productoAnterior == $pedidoProductoAModificar['id_producto']){
            return 5; // No se modifico ningun atributo
        }


        if(!$flagId){
            return 1; // El pedido_producto no existe
        }

        //verifico que exista un codigo_pedido identico en un registro de pedidos
        $flagCodigoPedido = false;
        $listaPedidos = BaseDeDatos::ListarPedidos();
        $pedidoAnterior = null;
        $pedidoNuevo = null;

        foreach ($listaPedidos as $pedido) {
            if ($pedido['codigoAlfanumerico'] == $pedidoProductoAModificar['codigo_pedido']) {
                //si funciona todo quiero esto comentado
                // if($pedido['estado'] == "pendiente" || $pedido['estado'] == "en preparacion"){
                //     return 6; // No se puede modificar un pedido en preparacion
                // }

                $flagCodigoPedido = true;
                $pedidoNuevo = $pedido; // guardo el pedido nuevo        
                // break;
            }

            if($codigo_pedidoAnterior == $pedido['codigoAlfanumerico']){
                $pedidoAnterior = $pedido;
            }
        }

        if(!$flagCodigoPedido){
            return 2; // El codigo_pedido del pedido_producto no existe
        }

        //verifico que exista un id_producto identico en un registro de productos si se modifico
        $flagIdProducto = false;
        $listaProductos = BaseDeDatos::ListarProductos();
        $productoEncontrado = null;
        $precioProductoAnterior = 0;

        foreach ($listaProductos as $producto) {
            if ($producto['id'] == $pedidoProductoAModificar['id_producto']) {
                $flagIdProducto = true;
                $productoEncontrado = $producto;
                // break;
            }

            if($id_productoAnterior == $producto['id']){
                $precioProductoAnterior = $producto['precio'];
            }

        }

        if(!$flagIdProducto){
            return 3; // El id_producto del pedido_producto no existe
        }
        
        $precioProducto = $productoEncontrado['precio'];
        

        //Si se llega hasta acá se modifican los datos en la base de datos
        $pedidoProductoAModificar['id_producto'] = $productoEncontrado['id'];
        BaseDeDatos::ModificarCodigoPedidoProducto($pedidoProductoAModificar['id'], $pedidoProductoAModificar['codigo_pedido'], $pedidoProductoAModificar['id_producto']);
        

        if($codigo_pedido != "") {
            $pedidoNuevo['precioFinal'] += $precioProducto;
            BaseDeDatos::ModificarPrecioFinalPedido($pedidoNuevo['id'], $pedidoNuevo['precioFinal']);


            //modifico el estado del pedido nuevo si es necesario
            $estadoNuevo = "error";
            $flagTiempoEstimadoNuevo = false;

            if($pedidoNuevo['estado'] == "pendiente" && $pedidoProductoAModificar['estado'] == "pendiente") {
                $estadoNuevo = "pendiente";
            }
            else if($pedidoNuevo['estado'] == "pendiente" && $pedidoProductoAModificar['estado'] == "en preparacion") {
                $estadoNuevo = "en preparacion";
                $flagTiempoEstimadoNuevo = true;
            }
            else if($pedidoNuevo['estado'] == "en preparacion" && $pedidoProductoAModificar['estado'] == "pendiente") {
                $estadoNuevo = "en preparacion";
            }
            else if($pedidoNuevo['estado'] == "en preparacion" && $pedidoProductoAModificar['estado'] == "en preparacion") {
                $estadoNuevo = "en preparacion";
                $flagTiempoEstimadoNuevo = true;
            }
            else if($pedidoNuevo['estado'] == "listo para servir" && $pedidoProductoAModificar['estado'] == "pendiente") {
                $estadoNuevo = "en preparacion";
            }
            else if($pedidoNuevo['estado'] == "listo para servir" && $pedidoProductoAModificar['estado'] == "en preparacion") {
                $estadoNuevo = "en preparacion";
                $flagTiempoEstimadoNuevo = true;
            }
            
            if($flagTiempoEstimadoNuevo) {
                if($pedidoNuevo['tiempoEstimado'] == null || $pedidoNuevo['tiempoEstimado'] < $pedidoProductoAModificar['tiempo_producto']){
                    $pedidoNuevo['tiempoEstimado'] = $pedidoProductoAModificar['tiempo_producto'];
                    BaseDeDatos::ActualizarPedido($pedidoNuevo); // pedidoNuevo es un array asociativo
                }
            }
            else {
                BaseDeDatos::ModificarEstadoPedido($pedidoNuevo['id'], $estadoNuevo);
            }
            
            //Acá modifico el precio final del pedido vinculado al pedido_producto viejo
            $pedidoAnterior['precioFinal'] -= $precioProductoAnterior;
            // si el precioFinal modificado es menor a 0, se lo setea en 0 y si es cero elimino el pedido
            if($pedidoAnterior['precioFinal'] <= 0){
                $fecha_baja = date("Y-m-d");
                BaseDeDatos::EliminarPedido($pedidoAnterior['id'], $fecha_baja);
                BaseDeDatos::ModificarEstadoMesa($pedidoAnterior['codigoMesa'], "libre");
                
            }
            else {
                BaseDeDatos::ModificarPrecioFinalPedido($pedidoAnterior['id'], $pedidoAnterior['precioFinal']);
            }
        }
        else {
            // Si no se modifico el codigo_pedido, se modifica el precio del pedido
            $resultadoPrecio = 0;
            $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();

            foreach ($listaPedidosProductos as $pedidoProducto) {
                if ($pedidoProducto['codigo_pedido'] == $pedidoProductoAModificar['codigo_pedido']) {
                    foreach ($listaProductos as $producto) {
                        if ($pedidoProducto['id_producto'] == $producto['id']) {
                            $resultadoPrecio += $producto['precio'];
                        }
                    }
                }
            }

            BaseDeDatos::ModificarPrecioFinalPedido($pedidoNuevo['id'], $resultadoPrecio);
        }
        return 4; // Pedido_producto modificado correctamente
    }

    public static function ListaPedidosFormatoCSV() {
        $listaPedidos = BaseDeDatos::ListarPedidos();

        // solo de pedidos 
        $csv = "codigoAlfanumerico,nombreCliente,codigoMesa,estado,tiempoEstimado,precioFinal\n";

        foreach ($listaPedidos as $pedido) {
            $csv .= $pedido['codigoAlfanumerico'] . "," . $pedido['nombreCliente'] . "," . $pedido['codigoMesa'] . "," . $pedido['estado'] . "," . $pedido['tiempoEstimado'] . "," . $pedido['precioFinal'] . "\n";
        }

        return $csv;
    }

    public static function ListaPedidosProductosCSV() {
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();

        $csv = "id,codigo_pedido,id_producto,estado,id_usuario,tiempo_producto,fecha_baja\n";

        foreach ($listaPedidosProductos as $pedidoProducto) {
            $csv .= $pedidoProducto['id'] . "," . $pedidoProducto['codigo_pedido'] . "," . $pedidoProducto['id_producto'] . "," . $pedidoProducto['estado'] . "," . $pedidoProducto['id_usuario'] . ",";
            $csv .= $pedidoProducto['tiempo_producto'] . "," . $pedidoProducto['fecha_baja'] . "\n";
        }

        return $csv;
    }

    public static function ModificarEstadoPedido($id, $rol){ //id es el id del pedido
        $listaPedidos = BaseDeDatos::ListarPedidos();
        $flag = false;
        $pedidoAModificar = null;

        foreach ($listaPedidos as $pedido) {
            if ($pedido['id'] == $id && $pedido['fecha_baja'] == null) {
                $pedidoAModificar = $pedido; // Guardar el pedido para devolverlo
                $flag = true;
                break;
            }
        }

        if(!$flag){
            return 1; // El pedido no existe
        }

        $mesaEncontrada = null; // para guardar la mesa del pedido

        $listaMesas = BaseDeDatos::ListarMesas();

        foreach ($listaMesas as $mesa) {
            if ($mesa['codigoIdentificacion'] == $pedidoAModificar['codigoMesa'] && $mesa['fecha_baja'] == null) {
                $mesaEncontrada = $mesa;
                break;
            }
        }
        
        if($pedidoAModificar['estado'] == "listo para servir" && $mesaEncontrada['estado'] == "con cliente esperando pedido" && $rol == "mozo"){
            $pedidoAModificar['estado'] = "entregado";
            $mesaEncontrada['estado'] = "con cliente comiendo";
        }
        else if($pedidoAModificar['estado'] == "entregado" && $mesaEncontrada['estado'] == "con cliente comiendo" && $rol == "mozo"){
            $mesaEncontrada['estado'] = "con cliente pagando";
        }
        else if($pedidoAModificar['estado'] == "entregado" && $mesaEncontrada['estado'] == "con cliente pagando" && $rol == "socio" && $mesaEncontrada['encuesta_realizada'] == 1){
            $mesaEncontrada['estado'] = "cerrada";
        }
        else if($pedidoAModificar['estado'] == "entregado" && $mesaEncontrada['estado'] == "con cliente pagando" && $rol == "socio" && $mesaEncontrada['encuesta_realizada'] == 0){
            return 5; // La mesa no tiene encuesta realizada
        }
        else if($pedidoAModificar['estado'] == "entregado" && $mesaEncontrada['estado'] == "con cliente pagando" && $rol != "socio"){
            return 3; // Solo un socio puede cerrar la mesa
        }
        else {
            return 2; // No se puede modificar el estado del pedido en este momento
        }

        
        BaseDeDatos::ModificarEstadoPedido($id, $pedidoAModificar['estado']);
        BaseDeDatos::ModificarEstadoMesa($mesaEncontrada['codigoIdentificacion'], $mesaEncontrada['estado']);

        return 4; // Pedido modificado correctamente
    }


    public static function RealizarEncuesta($codigo_mesa, $codigo_pedido, $puntuacion_mesa, $puntuacion_restaurante, $puntuacion_mozo, 
    $puntuacion_cocinero, $puntuacion_bartender, $puntuacion_cervecero, $descripcion) {

        $existeEncuesta = self::VerificarEncuesta($codigo_mesa, $codigo_pedido);

        if($existeEncuesta){ // si es true es porque ya existe la encuesta
            return -1; // La encuesta ya fue realizada
        }

        $listaPedidos = BaseDeDatos::ListarPedidos();
        $flag = false;
        $pedidoAModificar = null;

        foreach ($listaPedidos as $pedido) {
            if ($pedido['codigoAlfanumerico'] == $codigo_pedido && $pedido['fecha_baja'] == null) {
                $pedidoAModificar = $pedido; // Guardar el pedido para devolverlo
                $flag = true;
                break;
            }
        }

        if(!$flag){
            return 1; // El pedido no existe
        }

        if($pedidoAModificar['estado'] != "entregado"){
            return 2; // El pedido no fue entregado
        }

        //verico si las puntuciones de cocina, bartender y cervecero son validas
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();
        $listaProductos = BaseDeDatos::ListarProductos();

        $flagCocinero = false;
        $flagBartender = false;
        $flagCervecero = false;

        foreach ($listaPedidosProductos as $pedidoProducto) {
            if ($pedidoProducto['codigo_pedido'] == $codigo_pedido) {
                foreach ($listaProductos as $producto) {
                    if ($pedidoProducto['id_producto'] == $producto['id']) {
                        if($producto['tipo'] == "comida"){
                            $flagCocinero = true;
                        }
                        if($producto['tipo'] == "trago"){
                            $flagBartender = true;
                        }
                        if($producto['tipo'] == "cerveza"){
                            $flagCervecero = true;
                        }
                    }
                }
            }
        }

        if(!$flagCocinero && $puntuacion_cocinero != null){
            return 8; // La puntuacion del cocinero no es valida
        }
        if(!$flagBartender && $puntuacion_bartender != null){
            return 9; // La puntuacion del bartender no es valida
        }
        if(!$flagCervecero && $puntuacion_cervecero != null){
            return 10; // La puntuacion del cervecero no es valida
        }

        if($flagCocinero && $puntuacion_cocinero == null){
            return 3; // La puntuacion del cocinero es obligatoria
        }
        if($flagBartender && $puntuacion_bartender == null){
            return 4; // La puntuacion del bartender es obligatoria
        }
        if($flagCervecero && $puntuacion_cervecero == null){
            return 5; // La puntuacion del cervecero es obligatoria
        }

        //verifico que exista la mesa y que este en estado de encuesta
        $listaMesas = BaseDeDatos::ListarMesas();

        foreach ($listaMesas as $mesa) {
            if ($mesa['codigoIdentificacion'] == $codigo_mesa && $mesa['fecha_baja'] == null) {
                if($mesa['estado'] != "con cliente pagando"){
                    return 6; // La mesa no esta en estado de encuesta
                }
                break;
            }
        }

        $fecha = date("Y-m-d");
        BaseDeDatos::AgregarEncuesta($codigo_mesa, $codigo_pedido, $puntuacion_mesa, $puntuacion_restaurante, $puntuacion_mozo, 
        $puntuacion_cocinero, $puntuacion_bartender, $puntuacion_cervecero, $descripcion, $fecha);

        //modificar el encuesta_realizada de la mesa
        BaseDeDatos::ModificarEncuestaMesa($codigo_mesa, true);

        return 7; // Encuesta realizada correctamente
        
    }

    public static function VerificarEncuesta($codigo_mesa, $codigo_pedido) {
        $listaEncuestas = BaseDeDatos::ListarEncuestas();
        $flag = false;

        foreach ($listaEncuestas as $encuesta) {
            if ($encuesta['codigo_mesa'] == $codigo_mesa && $encuesta['codigo_pedido'] == $codigo_pedido) {
                return true; // La encuesta ya fue realizada
            }
        }

        return false; // La encuesta no existe
    }

    public static function ActualizarOperacion($id, $cant_operaciones) {
        $cant_operaciones++; // Incrementar la cantidad de operaciones // si el valor de cant_operaciones es 0, se le suma 1 y asi sucesivamente
        BaseDeDatos::ActualizarOperacion($id, $cant_operaciones);
    }

    public static function ListaOperacionesPorSector(){
        $listaUsuarios = BaseDeDatos::ListarUsuarios();
        $cantOperacionesCocina = 0;
        $cantOperacionesBarra = 0;
        $cantOperacionesCerveza = 0;
        $cantOperacionesMozo = 0;

        foreach ($listaUsuarios as $usuario) {
            if ($usuario['rol'] == "cocinero") {
                $cantOperacionesCocina += $usuario['cant_operaciones'];
            }
            if ($usuario['rol'] == "bartender") {
                $cantOperacionesBarra += $usuario['cant_operaciones'];
            }
            if ($usuario['rol'] == "cervecero") {
                $cantOperacionesCerveza += $usuario['cant_operaciones'];
            }
            if ($usuario['rol'] == "mozo") {
                $cantOperacionesMozo += $usuario['cant_operaciones'];
            }
        }

        $arrOperaciones = array("cocina" => $cantOperacionesCocina, "barra" => $cantOperacionesBarra, "cerveza" => $cantOperacionesCerveza , "mozo" => $cantOperacionesMozo);

        return $arrOperaciones; 
    }

    public static function ListaOperacionesSectorConEmpleados(){
        $listaUsuarios = BaseDeDatos::ListarUsuarios();
        $cantOperacionesCocina = 0;
        $cantOperacionesBarra = 0;
        $cantOperacionesCerveza = 0;
        $cantOperacionesMozo = 0;

        $arrEmpleadosCocina = array();
        $arrEmpleadosBarra = array();
        $arrEmpleadosCerveza = array();
        $arrEmpleadosMozo = array();

        foreach ($listaUsuarios as $usuario) {
            if ($usuario['rol'] == "cocinero") {
                $cantOperacionesCocina += $usuario['cant_operaciones'];
                $arrEmpleadosCocina[] = array("nombre" => $usuario['nombre'], "apellido" => $usuario['apellido']);
            }
            if ($usuario['rol'] == "bartender") {
                $cantOperacionesBarra += $usuario['cant_operaciones'];
                $arrEmpleadosBarra[] = array("nombre" => $usuario['nombre'], "apellido" => $usuario['apellido']);
            }
            if ($usuario['rol'] == "cervecero") {
                $cantOperacionesCerveza += $usuario['cant_operaciones'];
                $arrEmpleadosCerveza[] = array("nombre" => $usuario['nombre'], "apellido" => $usuario['apellido']);
            }
            if ($usuario['rol'] == "mozo") {
                $cantOperacionesMozo += $usuario['cant_operaciones'];
                $arrEmpleadosMozo[] = array("nombre" => $usuario['nombre'], "apellido" => $usuario['apellido']);
            }
        }

        $arrOperaciones = array("cocina" => $cantOperacionesCocina, "barra" => $cantOperacionesBarra, "cerveza" => $cantOperacionesCerveza , "mozo" => $cantOperacionesMozo);
        $arrEmpleados = array("cocina" => $arrEmpleadosCocina, "barra" => $arrEmpleadosBarra, "cerveza" => $arrEmpleadosCerveza , "mozo" => $arrEmpleadosMozo);

        return array($arrOperaciones, $arrEmpleados); 
    }


    //Cantidad de operaciones de cada uno por separado.
    public static function ListaOperacionesPorEmpleado(){
        $listaUsuarios = BaseDeDatos::ListarUsuarios();
        $arrOperaciones = array();

        foreach ($listaUsuarios as $usuario) {
            $arrOperaciones[] = array("nombre" => $usuario['nombre'], "apellido" => $usuario['apellido'], "rol" => $usuario['rol'], "cant_operaciones" => $usuario['cant_operaciones']);
        }

        return $arrOperaciones; 
    }


    public static function ProductoMasMenosVendido($dato){
        $listaEncuestas = BaseDeDatos::ListarEncuestas();
        $listaProductos = BaseDeDatos::ListarProductos();
        $listaPedidosProductos = BaseDeDatos::ListarPedidosProductos();
    
        $ventasPorProducto = [];
    
        // Inicializar el conteo de ventas en 0 para cada producto
        foreach($listaProductos as $producto){
            $ventasPorProducto[$producto['id']] = 0; // esto seria $ventasPorProducto["id del producto"] = 0;
        }
    
        // Contar las ventas para cada producto
        foreach($listaEncuestas as $encuesta){
            foreach($listaPedidosProductos as $pedidoProducto){
                if($encuesta['codigo_pedido'] == $pedidoProducto['codigo_pedido']){
                    if(isset($ventasPorProducto[$pedidoProducto['id_producto']])){
                        $ventasPorProducto[$pedidoProducto['id_producto']] += 1;
                    }
                }
            }
        }
    
        // Encontrar el producto más y menos vendido
        $productoMasVendido = null;
        $productoMenosVendido = null;
        $cantidadMaxima = -1; // Inicializar a un valor muy bajo
        $cantidadMinima = PHP_INT_MAX; // Inicializar a un valor muy alto
    
        foreach($listaProductos as $producto){
            $cantidadVendida = $ventasPorProducto[$producto['id']];
            
            if($cantidadVendida > $cantidadMaxima){
                $cantidadMaxima = $cantidadVendida;
                $productoMasVendido = $producto;
            }
    
            if($cantidadVendida < $cantidadMinima){
                $cantidadMinima = $cantidadVendida;
                $productoMenosVendido = $producto;
            }
        }
    
        // Devolver el resultado basado en el parámetro $dato
        if($dato == "mas"){
            return $productoMasVendido;
        } else if($dato == "menos"){
            return $productoMenosVendido;
        }
    
        return null;
    }


    public static function ListaPedidosConMalTiempo(){
        $listaPedidos = BaseDeDatos::ListarPedidos();
        $pedidosMalDeTiempo = [];
    
        foreach($listaPedidos as $pedido){
            // Verificar que tiempo_final y tiempo_inicio no sean nulos
            if (!is_null($pedido['tiempo_final']) && !is_null($pedido['tiempo_inicio'])) {
                $compararTiempo = strtotime($pedido['tiempo_final']) - strtotime($pedido['tiempo_inicio']);
                
                // Si el tiempo de preparación supera el tiempo estimado
                if($compararTiempo > ($pedido['tiempoEstimado'] * 60)){
                    $pedidosMalDeTiempo[] = $pedido;
                }
            }
        }
    
        return $pedidosMalDeTiempo;
    }
    
    
}

?>