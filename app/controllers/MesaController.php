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

    public static function EliminarMesa($request, $response, $args){
        $id = $args['id'];
        
        if(!is_numeric($id) || $id <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser numerico y mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $result = Mesa::VerificarMesaPorId($id);
        if(!$result){ // Si es false la mesa no existe
            $payload = json_encode(array("mensaje" => "La mesa no existe"));
            $response->withStatus(400);
        }
        else {
            $resultadoEliminar = Mesa::EliminarMesa($result);

            if(!$resultadoEliminar){
                $payload = json_encode(array("mensaje" => "La mesa no esta libre para eliminar"));
                $response->withStatus(400);
            }
            else {
                $payload = json_encode(array("mensaje" => "Mesa eliminada con exito"));
            }
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }

    public static function ModificarMesa($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo

        if(!isset($body['id']) || !isset($body['codigoIdentificacion'])){
            $payload = json_encode(array("mensaje" => "Faltan datos para modificar la mesa"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }
        $id = $body['id'];

        if(!is_numeric($id) || $id <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser numerico y mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $codigoIdentificacion = $body['codigoIdentificacion'];
        $result = Mesa::ModificarMesa($id, $codigoIdentificacion);

        if($result == 1){
            $payload = json_encode(array("mensaje" => "La mesa no esta libre para modificar"));
            $response->withStatus(400);
        }
        else if($result == 2){
            $payload = json_encode(array("mensaje" => "La mesa no existe"));
            $response->withStatus(400);
        }
        else if($result == 3){
            $payload = json_encode(array("mensaje" => "La mesa ya existe"));
            $response->withStatus(400);
        }
        else {
            $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }
}






?>