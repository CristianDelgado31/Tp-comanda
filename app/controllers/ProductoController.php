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
}





?>