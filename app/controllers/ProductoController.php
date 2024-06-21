<?php
require_once 'models/Producto.php';

class ProductoController {

    public static function AgregarProducto($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo
        
        if(!isset($body['nombre']) || !isset($body['tipo']) || !isset($body['precio'])){
            $payload = json_encode(array("mensaje" => "Faltan datos"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        if(Producto::VerificarProducto($body['nombre'])){
            $payload = json_encode(array("mensaje" => "El producto ya existe"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $producto = new Producto($body['nombre'], $body['tipo'], $body['precio']);
        $producto->AgregarProducto();

        $payload = json_encode(array("mensaje" => "Producto agregado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(201);

    }

    public static function MostrarLista($request, $response, $args){
        $lista = Producto::MostrarLista();
        //payload es un array asociativo
        $payload = json_encode(array("listaProductos" => $lista)); // json_encode — Retorna la representación JSON del valor dado

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ModificarProducto($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo

        if(!isset($body['id']) || !isset($body['nombre']) || !isset($body['tipo']) || !isset($body['precio'])){
            $payload = json_encode(array("mensaje" => "Faltan datos (id, nombre, tipo, precio)"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        if(!is_numeric($body['id']) || $body['id'] <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser un numero mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        if(!is_numeric($body['precio']) || $body['precio'] <= 0){
            $payload = json_encode(array("mensaje" => "El precio debe ser un numero mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $producto = new Producto($body['nombre'], $body['tipo'], $body['precio']);
        $producto->id = $body['id'];
        $result = $producto->ModificarProducto();

        if($result === true){
            $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
            $response->withStatus(200);
        }
        else if($result === false){
            $payload = json_encode(array("mensaje" => "El producto ya existe con ese nombre (id diferente)"));
            $response->withStatus(400);
        }
        else if($result === -1){
            $payload = json_encode(array("mensaje" => "No existe el id"));
            $response->withStatus(400);
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');

    }

    public static function EliminarProducto($request, $response, $args){
        $id = $args['id'];
        if(!is_numeric($id) || $id <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser un numero mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $result = Producto::EliminarProducto($id);

        if($result === true){
            $payload = json_encode(array("mensaje" => "Producto eliminado con exito"));
        }
        else if($result === false){
            $payload = json_encode(array("mensaje" => "El producto ya fue dado de baja anteriormente"));
        }
        else {
            $payload = json_encode(array("mensaje" => "No existe el id"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');

    }
}





?>