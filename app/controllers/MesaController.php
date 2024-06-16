<?php
require_once 'models/Mesa.php';

class MesaController {

    public static function AgregarMesa($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo
        
        if(!isset($body['codigoIdentificacion']) || !isset($body['estado'])){
            $payload = json_encode(array("mensaje" => "Faltan datos"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        // Si es true la mesa ya existe
        if(Mesa::VerificarMesa($body['codigoIdentificacion'])){
            $payload = json_encode(array("mensaje" => "La mesa ya existe"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $mesa = new Mesa($body['codigoIdentificacion'], $body['estado']);
        $mesa->AgregarMesa();

        $payload = json_encode(array("mensaje" => "Mesa agregada con exito"));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(201);

    }

    public static function MostrarLista($request, $response, $args){
        $lista = Mesa::MostrarLista();
        //payload es un array asociativo
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}






?>