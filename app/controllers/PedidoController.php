<?php
require_once 'models/Pedido.php';
// use TCPDF;

class PedidoController {

    public static function MostrarLista($request, $response, $args) {
        $pedidos = Pedido::MostrarLista();
        $payload = json_encode(array("listaPedidos" => $pedidos));
        $response->getBody()->write($payload); //payload es un array asociativo
        
        return $response
            ->withHeader('Content-Type', 'application/json');
    }


    public static function ListarPedidosProductosPorRol($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');

        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $rol = $data->usuario->rol;

        try {
            $pedidosProductos = Pedido::ListarPedidosProductos($rol);
            $payload = json_encode(array("listaPedidosProductos" => $pedidosProductos));
            $response = $response->withStatus(200);
            
        } catch (Exception $e) {
            $payload = json_encode(array("mensaje" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $pedidosProductos = Pedido::ListarPedidosProductos($rol);

        // if($pedidosProductos == 0){
        //     $response->getBody()->write(json_encode(array("error" => "El rol ingresado no existe")));
        //     return $response
        //         ->withHeader('Content-Type', 'application/json');
        // }
        // else if($pedidosProductos == 1){
        //     $response->getBody()->write(json_encode(array("error" => "No hay pedidos pendientes")));
        //     return $response
        //         ->withHeader('Content-Type', 'application/json');
        // }

        // $payload = json_encode(array("listaPedidosProductos" => $pedidosProductos));
        // $response->getBody()->write($payload);

        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }

    public static function AgregarPedido($request, $response, $args) {
        //sacar el id del usuario del jwt 
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario
        $id = $usuario->id;
        $cant_operaciones = $usuario->cant_operaciones;
        // Pedido::ActualizarOperacion($id, $cant_operaciones);

        $pedido = json_decode($request->getBody());
        // $productos = $pedido->productos;
        $pedido = new Pedido($pedido->codigoAlfanumerico,$pedido->nombreCliente ,$pedido->codigoMesa, $pedido->estado, $pedido->productos); // $pedido->productos es un array

        try {
            $result = $pedido->AgregarPedido();
            // si no hay un throw en la funcion AgregarPedido, se ejecuta lo siguiente
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Pedido agregado correctamente"));
            $response = $response->withStatus(201);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $result = $pedido->AgregarPedido();

        // if($result == -1){
        //     $response->getBody()->write(json_encode(array("error" => "La mesa no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == -2){
        //     $response->getBody()->write(json_encode(array("error" => "El codigoAlfanumerico del pedido ya existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == -3){
        //     $response->getBody()->write(json_encode(array("error" => "La mesa existe pero esta ocupada")));
        //     $response->withStatus(400);
        // }
        // else if($result == -4){
        //     $response->getBody()->write(json_encode(array("error" => "Uno de los productos no existe")));
        //     $response->withStatus(400);
        // }
        // else {
        //     Pedido::ActualizarOperacion($id, $cant_operaciones);
        //     $payload = json_encode(array("mensaje" => "Pedido agregado correctamente"));
        //     $response->getBody()->write($payload);
        //     $response->withStatus(201);
        // }
        
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }

    public static function AgarrarPedidoProducto($request, $response, $args) {
        $pedido = json_decode($request->getBody());
        
        $id_pedido_producto = $pedido->id_pedido_producto;
        $tiempoPreparacion = $pedido->tiempoPreparacion;
        $estado = $pedido->estado;
        $header = $request->getHeaderLine('Authorization');

        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        
        $id = $data->usuario->id;
        $cant_operaciones = $data->usuario->cant_operaciones;
        $email = $data->usuario->email;

        try {
            $result = Pedido::AgarrarProductoDePedido($email, $id_pedido_producto, $estado, $tiempoPreparacion);
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Pedido tomado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $result = Pedido::AgarrarProductoDePedido($email, $id_pedido_producto, $estado, $tiempoPreparacion); //nombre, $apellido

        // if($result == -1){
        //     $response->getBody()->write(json_encode(array("error" => "El empleado o usuario no existe")));
        // }
        // else if($result == 4){
        //     $response->getBody()->write(json_encode(array("error" => "El empleado esta ocupado")));
        // }
        // else if($result == 1){
        //     $response->getBody()->write(json_encode(array("error" => "El rol del usuario no tiene permisos para tomarlo")));
        // }
        // else if($result == 2){
        //     $response->getBody()->write(json_encode(array("error" => "El pedido_producto ingresado ya fue tomado")));
        // }
        // else if($result == 3){
        //     $response->getBody()->write(json_encode(array("error" => "El rol del empleado no tiene pedidos pendientes")));
        // }
        // else if($result == 5){
        //     $response->getBody()->write(json_encode(array("error" => "El pedido_producto ingresado no existe")));
        // }
        // else {
        //     // Pedido::ActualizarOperacion($id, $cant_operaciones); //descomentar despues esto
        //     $payload = json_encode(array("mensaje" => "Pedido tomado correctamente"));
        //     $response->getBody()->write($payload);
        // }

        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }


    public static function TiempoEstimadoDelPedido($request, $response, $args) {
        $request = $request->getQueryParams();
        $codigoPedido = $request["codigoPedido"] ?? "";
        $codigoMesa = $request["codigoMesa"] ?? "";

        if($codigoPedido == "" || $codigoMesa == "") {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        try {
            $tiempoEstimado = Pedido::TiempoEstimadoDelPedido($codigoPedido, $codigoMesa);
            $payload = json_encode(array("tiempoEstimado" => $tiempoEstimado));
            $response = $response->withStatus(200);    
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $tiempoEstimado = Pedido::TiempoEstimadoDelPedido($codigoPedido, $codigoMesa);

        // if($tiempoEstimado == -1){
        //     $response->getBody()->write(json_encode(array("error" => "El tiempo estimado no se pudo calcular porque el pedido no esta en preparacion")));
        //     $response->withStatus(404); 
        // }
        // else if($tiempoEstimado != null) {
        //     $payload = json_encode(array("tiempoEstimado" => $tiempoEstimado));
        //     $response->getBody()->write($payload);
        //     $response->withStatus(200);
        // } else if($tiempoEstimado == 0){
        //     $response->getBody()->write(json_encode(array("error" => "Pedido no encontrado")));
        //     $response->withStatus(404);
        // }

        // return $response
        //   ->withHeader('Content-Type', 'application/json');
    }

    public static function FinalizarProductoDePedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario
        $id = $usuario->id;
        $cant_operaciones = $usuario->cant_operaciones;

        try {
            $actualizarPedido = Pedido::FinalizarProductoDePedido($usuario);
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Producto finalizado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $actualizarPedido = Pedido::FinalizarProductoDePedido($usuario);

        // if($actualizarPedido == -1){
        //     $response->getBody()->write(json_encode(array("error" => "El usuario no tiene producto pedido en preparacion")));
        //     $response->withStatus(400);
        // }
        // else {
        //     Pedido::ActualizarOperacion($id, $cant_operaciones);
        //     $payload = json_encode(array("mensaje" => "Producto finalizado correctamente"));
        //     $response->getBody()->write($payload);
        //     $response->withStatus(200);
        // }
        
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }

    public static function EliminarPedido($request, $response, $args) {
        $id = $args['id'];

        if(isset($id) == false){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        try {
            $result = Pedido::EliminarPedido($id);
            $payload = json_encode(array("mensaje" => "Pedido eliminado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $result = Pedido::EliminarPedido($id);

        // if($result == 1){
        //     $response->getBody()->write(json_encode(array("error" => "El pedido no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == 2){
        //     $response->getBody()->write(json_encode(array("error" => "No se pudo eliminar el pedido porque esta en preparacion")));
        //     $response->withStatus(400);
        // }
        // else {
        //     $payload = json_encode(array("mensaje" => "Pedido eliminado correctamente"));
        //     $response->getBody()->write($payload);
        //     $response->withStatus(200);
        // }

        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }


    public static function ModificarPedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario
        $id = $usuario->id;
        $cant_operaciones = $usuario->cant_operaciones;

        $pedido = json_decode($request->getBody());

        if($pedido == null){
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if(!isset($pedido->id) || !is_numeric($pedido->id)){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if($pedido->id <= 0) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser mayor a 0")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $id = $pedido->id;
        $codigoAlfanumerico = "";
        $nombreCliente = "";
        $codigoMesa = "";
        $tiempoEstimado = "";

        if(isset($pedido->codigoAlfanumerico)){
            $codigoAlfanumerico = $pedido->codigoAlfanumerico;
        }
        if(isset($pedido->nombreCliente)){
            $nombreCliente = $pedido->nombreCliente;
        }
        if(isset($pedido->codigoMesa)){
            $codigoMesa = $pedido->codigoMesa;
        }
        if(isset($pedido->tiempoEstimado)){
            $tiempoEstimado = $pedido->tiempoEstimado;
        }
        
        if($codigoAlfanumerico == "" && $nombreCliente == "" && $codigoMesa == "" && $tiempoEstimado == ""){
            $response->getBody()->write(json_encode(array("error" => "No se envio ningun dato para modificar")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        try {
            $result = Pedido::ModificarPedido($id, $codigoAlfanumerico, $nombreCliente, $codigoMesa, $tiempoEstimado);
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Pedido modificado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $result = Pedido::ModificarPedido($id, $codigoAlfanumerico, $nombreCliente, $codigoMesa, $tiempoEstimado);

        // if($result == 1){
        //     $response->getBody()->write(json_encode(array("error" => "El pedido no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == 2){
        //     $response->getBody()->write(json_encode(array("error" => "El codigoAlfanumerico ingresado ya existe en un pedido")));
        //     $response->withStatus(400);
        // }
        // else if($result == 3){
        //     $response->getBody()->write(json_encode(array("error" => "La mesa ingresada no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == 4){
        //     $response->getBody()->write(json_encode(array("error" => "La mesa ingresada esta ocupada")));
        //     $response->withStatus(400);
        // }
        // else {
        //     Pedido::ActualizarOperacion($id, $cant_operaciones);
        //     $payload = json_encode(array("mensaje" => "Pedido modificado correctamente"));
        //     $response->getBody()->write($payload);
        //     $response->withStatus(200);
        // }

        // // $response->getBody()->write($payload);
        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }

    public static function ModificarProductoPedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario
        $id = $usuario->id;
        $cant_operaciones = $usuario->cant_operaciones;

        $pedido = json_decode($request->getBody());

        if($pedido == null){
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if(!isset($pedido->id) || !is_numeric($pedido->id)){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if($pedido->id <= 0) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser mayor a 0")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $id = $pedido->id;
        $codigo_pedido = "";
        $id_producto = "";
        $tiempo_producto = "";
        
        if(isset($pedido->codigo_pedido)){
            $codigo_pedido = $pedido->codigo_pedido;
        }
        if(isset($pedido->id_producto)){
            $id_producto = $pedido->id_producto;
        }

        if($codigo_pedido == "" && $id_producto == ""){
            $response->getBody()->write(json_encode(array("error" => "No se envio ningun dato para modificar")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
        
        try {
            $result = Pedido::ModificarProductoDePedido($id, $codigo_pedido, $id_producto, $tiempo_producto);
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Producto del pedido modificado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $result = Pedido::ModificarProductoDePedido($id, $codigo_pedido, $id_producto, $tiempo_producto);

        // if($result == 1){
        //     $response->getBody()->write(json_encode(array("error" => "El pedido no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == 2){
        //     $response->getBody()->write(json_encode(array("error" => "El codigo_pedido del pedido_producto no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == 3){
        //     $response->getBody()->write(json_encode(array("error" => "El id_producto del pedido_producto no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == 5) {
        //     $response->getBody()->write(json_encode(array("error" => "No se modifico ningun atributo del producto del pedido")));
        //     $response->withStatus(400);
        // }
        // else {
        //     Pedido::ActualizarOperacion($id, $cant_operaciones);
        //     $payload = json_encode(array("mensaje" => "Producto del pedido modificado correctamente"));
        //     $response->getBody()->write($payload);
        //     $response->withStatus(200);
        // }

        // return $response
        //     ->withHeader('Content-Type', 'application/json');

    }

    public static function ExportarListaPedidosEnCSV($request, $response, $args) {
        $lista = Pedido::ListaPedidosFormatoCSV();

        $response->getBody()->write($lista);
        return $response
            ->withHeader('Content-Type', 'application/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="pedidos.csv"');
    }

    public static function ExportarListaPedidosProductosCSV($request, $response, $args) {
        $lista = Pedido::ListaPedidosProductosCSV();

        $response->getBody()->write($lista);
        return $response
            ->withHeader('Content-Type', 'application/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="pedidos_productos.csv"');
    }


    public static function ModificarEstadoPedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $rol = $data->usuario->rol;


        $pedido = json_decode($request->getBody());

        if($pedido == null){
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if(!isset($pedido->id) || !is_numeric($pedido->id)){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if($pedido->id <= 0) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser mayor a 0")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $id = $pedido->id;

        try {
            $result = Pedido::ModificarEstadoPedido($id, $rol);
            // Si no hay un throw en la funcion ModificarEstadoPedido, se ejecuta lo siguiente
            if($rol != "socio"){
                $id = $data->usuario->id;
                $cant_operaciones = $data->usuario->cant_operaciones;
                Pedido::ActualizarOperacion($id, $cant_operaciones);
            }
            $payload = json_encode(array("mensaje" => "Estado del pedido modificado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $result = Pedido::ModificarEstadoPedido($id, $rol);

        // if($result == 1){
        //     $response->getBody()->write(json_encode(array("error" => "El pedido no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == 2){
        //     $response->getBody()->write(json_encode(array("error" => "No se pudo modificar el estado del pedido en este momento")));
        //     $response->withStatus(400);
        // }
        // else if($result == 3){
        //     $response->getBody()->write(json_encode(array("error" => "Solo los socios pueden cerrar")));
        //     $response->withStatus(400);
        // }
        // else if($result == 5){
        //     $response->getBody()->write(json_encode(array("error" => "La mesa no tiene encuesta realizada")));
        //     $response->withStatus(400);
        // }
        // else {

        //     if($rol != "socio"){
        //         $id = $data->usuario->id;
        //         $cant_operaciones = $data->usuario->cant_operaciones;
        //         Pedido::ActualizarOperacion($id, $cant_operaciones);
        //     }
        //     $payload = json_encode(array("mensaje" => "Estado del pedido modificado correctamente"));
        //     $response->getBody()->write($payload);
        //     $response->withStatus(200);
        // }

        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }


    public static function RealizarEncuesta($request, $response, $args) {
        $pedido = json_decode($request->getBody());
    
        if ($pedido == null) {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Validaciones de campos obligatorios
        if (empty($pedido->codigo_mesa) || empty($pedido->codigo_pedido) || !isset($pedido->puntuacion_mesa) || 
            !isset($pedido->puntuacion_restaurante) || !isset($pedido->puntuacion_mozo) || empty($pedido->descripcion)) {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos obligatorios")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Validaciones de puntuaciones (1 a 10)
        $validarPuntuacion = function($puntuacion) {
            return is_numeric($puntuacion) && $puntuacion >= 1 && $puntuacion <= 10;
        };
    
        if (!$validarPuntuacion($pedido->puntuacion_mesa) || 
            !$validarPuntuacion($pedido->puntuacion_restaurante) || 
            !$validarPuntuacion($pedido->puntuacion_mozo)) {
            $response->getBody()->write(json_encode(array("error" => "Las puntuaciones deben ser números entre 1 y 10")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Asignación de datos
        $codigo_mesa = $pedido->codigo_mesa;
        $codigo_pedido = $pedido->codigo_pedido;
        $puntuacion_mesa = $pedido->puntuacion_mesa;
        $puntuacion_restaurante = $pedido->puntuacion_restaurante;
        $puntuacion_mozo = $pedido->puntuacion_mozo;
        $descripcion = $pedido->descripcion; // Hasta acá es obligatorio
    
        // Opcionales
        $puntuacion_cocinero = isset($pedido->puntuacion_cocinero) ? $pedido->puntuacion_cocinero : null;
        $puntuacion_bartender = isset($pedido->puntuacion_bartender) ? $pedido->puntuacion_bartender : null;
        $puntuacion_cervecero = isset($pedido->puntuacion_cervecero) ? $pedido->puntuacion_cervecero : null;
    
        if($puntuacion_cocinero == null && $puntuacion_bartender == null && $puntuacion_cervecero == null) {
            $response->getBody()->write(json_encode(array("error" => "Debe puntuar al menos a un empleado")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if($puntuacion_cocinero != null && !$validarPuntuacion($puntuacion_cocinero) || 
            $puntuacion_bartender != null && !$validarPuntuacion($puntuacion_bartender) || 
            $puntuacion_cervecero != null && !$validarPuntuacion($puntuacion_cervecero)) {
            $response->getBody()->write(json_encode(array("error" => "Las puntuaciones deben ser números entre 1 y 10")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $result = Pedido::RealizarEncuesta($codigo_mesa, $codigo_pedido, $puntuacion_mesa, $puntuacion_restaurante, $puntuacion_mozo, $puntuacion_cocinero, $puntuacion_bartender, $puntuacion_cervecero, $descripcion);
            $payload = json_encode(array("mensaje" => "Encuesta realizada correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        // $result = Pedido::RealizarEncuesta($codigo_mesa, $codigo_pedido, $puntuacion_mesa, $puntuacion_restaurante, $puntuacion_mozo, $puntuacion_cocinero, $puntuacion_bartender, $puntuacion_cervecero, $descripcion);
    
        // if($result == -1){
        //     $response->getBody()->write(json_encode(array("error" => "La encuesta ya fue realizada")));
        //     $response->withStatus(400);
        // }
        // else if ($result == 1) {
        //     $response->getBody()->write(json_encode(array("error" => "El pedido no existe")));
        //     $response->withStatus(400);
        // }
        // else if($result == 2) {
        //     $response->getBody()->write(json_encode(array("error" => "El pedido no fue entregado")));
        //     $response->withStatus(400);
        // }
        // else if($result == 3) {
        //     // // La puntuacion del cocinero es obligatoria
        //     $response->getBody()->write(json_encode(array("error" => "Debe puntuar al cocinero")));
        //     $response->withStatus(400);
        // }
        // else if($result == 4) {
        //     // // La puntuacion del bartender es obligatoria
        //     $response->getBody()->write(json_encode(array("error" => "Debe puntuar al bartender")));
        //     $response->withStatus(400);
        // }
        // else if($result == 5) {
        //     // // La puntuacion del cervecero es obligatoria
        //     $response->getBody()->write(json_encode(array("error" => "Debe puntuar al cervecero")));
        //     $response->withStatus(400);
        // }
        // else if($result == 6) {
        //     // La mesa no esta en estado de encuesta
        //     $response->getBody()->write(json_encode(array("error" => "La mesa no esta en estado de encuesta")));
        //     $response->withStatus(400);
        // }
        // else if($result == 8){
        //     // La puntuacion del cocinero no es valida
        //     $response->getBody()->write(json_encode(array("error" => "La puntuacion del cocinero no es valida")));
        //     $response->withStatus(400);
        // }
        // else if($result == 9){
        //     // La puntuacion del bartender no es valida
        //     $response->getBody()->write(json_encode(array("error" => "La puntuacion del bartender no es valida")));
        //     $response->withStatus(400);
        // }
        // else if($result == 10){
        //     // La puntuacion del cervecero no es valida
        //     $response->getBody()->write(json_encode(array("error" => "La puntuacion del cervecero no es valida")));
        //     $response->withStatus(400);
        // }
        // else {
        //     $response->getBody()->write(json_encode(array("mensaje" => "Encuesta realizada correctamente")));
        //     $response->withStatus(200);
        // }

        // return $response
        //     ->withHeader('Content-Type', 'application/json');
    }
    

    // lista logs de empleados


    //lista cantidad de operaciones por sector (cocina, barra, cerveceria)
    public static function CantidadOperacionesPorSector($request, $response, $args) {
        $lista = Pedido::ListaOperacionesPorSector();

        $response->getBody()->write(json_encode(array("Lista operaciones por sector" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ListOperacionesSectorConEmpleados($request, $response, $args) {
        $lista = Pedido::ListaOperacionesSectorConEmpleados();

        $response->getBody()->write(json_encode(array("Lista operaciones por sector con empleados" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ListarOperacionesPorEmpleado($request, $response, $args) {
        $lista = Pedido::ListaOperacionesPorEmpleado();

        $response->getBody()->write(json_encode(array("Lista operaciones por empleado" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    
    public static function MostrarProductoMasVendido($request, $response, $args) {
        $producto = Pedido::ProductoMasMenosVendido("mas");

        $response->getBody()->write(json_encode(array("Producto mas vendido" => $producto)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function MostrarProductoMenosVendido($request, $response, $args) {
        $producto = Pedido::ProductoMasMenosVendido("menos");

        $response->getBody()->write(json_encode(array("Producto menos vendido" => $producto)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }


    public static function MostrarPedidosMalDeTiempo($request, $response, $args) {
        $lista = Pedido::ListaPedidosConMalTiempo();

        $response->getBody()->write(json_encode(array("Lista pedidos con mal tiempo" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    //PedidosCancelados


    public static function DescargarPDFPedidos($request, $response, $args) {
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $lista = Pedido::GenerarHtmlDePedidos();

        $pdf->writeHTML($lista, true, false, true, false, '');

        $pdfOutput = $pdf->Output('pedidos.pdf', 'S');

        $response = $response->withHeader('Content-Type', 'application/pdf')
                             ->withHeader('Content-Disposition', 'attachment; filename="pedidos.pdf"');

        $response->getBody()->write($pdfOutput);

        return $response;
    }
}






?>