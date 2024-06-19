<?php
require_once 'models/Pedido.php';

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
    
        // if ($header) {
        //     $token = trim(explode("Bearer", $header)[1]);
        // } else {
        //     $token = "";
        // }

        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $rol = $data->usuario->rol;

        $pedidosProductos = Pedido::ListarPedidosProductos($rol);

        if($pedidosProductos == 0){
            $response->getBody()->write(json_encode(array("error" => "El rol ingresado no existe")));
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
        else if($pedidosProductos == 1){
            $response->getBody()->write(json_encode(array("error" => "No hay pedidos pendientes")));
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $payload = json_encode(array("listaPedidosProductos" => $pedidosProductos));
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function AgregarPedido($request, $response, $args) {
        $pedido = json_decode($request->getBody());
        // $productos = $pedido->productos;
        $pedido = new Pedido($pedido->codigoAlfanumerico,$pedido->nombreCliente ,$pedido->codigoMesa, $pedido->estado, $pedido->productos); // $pedido->productos es un array

        $result = $pedido->AgregarPedido();

        if($result == -1){
            $response->getBody()->write(json_encode(array("error" => "La mesa no existe")));
            $response->withStatus(400);
        }
        else if($result == -2){
            $response->getBody()->write(json_encode(array("error" => "El codigoAlfanumerico del pedido ya existe")));
            $response->withStatus(400);
        }
        else if($result == -3){
            $response->getBody()->write(json_encode(array("error" => "La mesa existe pero esta ocupada")));
            $response->withStatus(400);
        }
        else if($result == -4){
            $response->getBody()->write(json_encode(array("error" => "Uno de los productos no existe")));
            $response->withStatus(400);
        }
        else {
            $payload = json_encode(array("mensaje" => "Pedido agregado correctamente"));
            $response->getBody()->write($payload);
            $response->withStatus(201);
        }
        
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function AgarrarPedidoProducto($request, $response, $args) {
        $pedido = json_decode($request->getBody());
        
        // $nombre = $pedido->nombre;
        // $apellido = $pedido->apellido;
        $id_pedido_producto = $pedido->id_pedido_producto;
        $tiempoPreparacion = $pedido->tiempoPreparacion;
        $estado = $pedido->estado;
        $header = $request->getHeaderLine('Authorization');

        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);

        // $nombre = $data->usuario->nombre;
        // $apellido = $data->usuario->apellido;
        $email = $data->usuario->email;


        $result = Pedido::AgarrarProductoDePedido($email, $id_pedido_producto, $estado, $tiempoPreparacion); //nombre, $apellido

        if($result == -1){
            $response->getBody()->write(json_encode(array("error" => "El empleado o usuario no existe")));
        }
        else if($result == 4){
            $response->getBody()->write(json_encode(array("error" => "El empleado esta ocupado")));
        }
        else if($result == 1){
            $response->getBody()->write(json_encode(array("error" => "El rol del usuario no tiene permisos para tomarlo")));
        }
        else if($result == 2){
            $response->getBody()->write(json_encode(array("error" => "El pedido_producto ingresado ya fue tomado")));
        }
        else if($result == 3){
            $response->getBody()->write(json_encode(array("error" => "El rol del empleado no tiene pedidos pendientes")));
        }
        else if($result == 5){
            $response->getBody()->write(json_encode(array("error" => "El pedido_producto ingresado no existe")));
        }
        else {
            $payload = json_encode(array("mensaje" => "Pedido tomado correctamente"));
            $response->getBody()->write($payload);
        }

        return $response
            ->withHeader('Content-Type', 'application/json');
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

        $tiempoEstimado = Pedido::TiempoEstimadoDelPedido($codigoPedido, $codigoMesa);

        if($tiempoEstimado == -1){
            $response->getBody()->write(json_encode(array("error" => "El tiempo estimado no se pudo calcular porque el pedido no esta en preparacion")));
            $response->withStatus(404); 
        }
        else if($tiempoEstimado != null) {
            $payload = json_encode(array("tiempoEstimado" => $tiempoEstimado));
            $response->getBody()->write($payload);
            $response->withStatus(200);
        } else if($tiempoEstimado == 0){
            $response->getBody()->write(json_encode(array("error" => "Pedido no encontrado")));
            $response->withStatus(404);
        }

        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function FinalizarProductoDePedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario

        $actualizarPedido = Pedido::FinalizarProductoDePedido($usuario);

        if($actualizarPedido == -1){
            $response->getBody()->write(json_encode(array("error" => "El usuario no tiene producto pedido en preparacion")));
            $response->withStatus(400);
        }
        else {
            $payload = json_encode(array("mensaje" => "Producto finalizado correctamente"));
            $response->getBody()->write($payload);
            $response->withStatus(200);
        }
        
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}






?>