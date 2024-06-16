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
        $params = $request->getQueryParams();
        $rol = $params['rol'];
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
        
        $id_pedido_producto = $pedido->id_pedido_producto;
        $nombre = $pedido->nombre;
        $apellido = $pedido->apellido;
        $tiempoPreparacion = $pedido->tiempoPreparacion;
        $estado = $pedido->estado;

        $result = Pedido::AgarrarProductoDePedido($nombre, $apellido, $id_pedido_producto, $estado, $tiempoPreparacion);

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
}






?>