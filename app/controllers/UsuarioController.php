<?php
// require_once 'Db/BaseDeDatos.php';
require_once 'models/Personas/Empleado.php';
require_once 'models/Personas/Socio.php';

class UsuarioController {

    public static function AgregarUsuario($request, $response, $args) { // $empleado es un objeto JSON
        $usuario = json_decode($request->getBody());
        $nombre = $usuario->nombre;
        $apellido = $usuario->apellido;
        $rol = $usuario->rol;
        // $estado = $usuario->estado;

        if($rol != "socio") {
            $estado = isset($usuario->estado) ? $usuario->estado : "activo"; // si estado no se pasa, por defecto es activo
            $empleado = new Empleado($nombre, $apellido, $rol, $estado);
            $empleado->AgregarEmpleado();
        } else {
            $socio = new Socio($nombre, $apellido, $rol);
            $socio->AgregarSocio();
        }

        $payload = json_encode(array("mensaje" => "Usuario agregado con exito"));

        $response->getBody()->write($payload);
        $response->withStatus(201); // 201 es el codigo de status que indica que se creo un recurso
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function MostrarLista($request, $response, $args) {
        $lista = Persona::MostrarLista();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function Login($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $nombre = $parametros["nombre"] ?? ""; // si no se pasa el nombre, por defecto es ""
        $apellido = $parametros["apellido"] ?? "";

        if($nombre == "" || $apellido == "") {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        $usuario = Persona::VerificarUsuario($nombre, $apellido);

        if($usuario != null) {
            $datos = array("usuario" => $usuario); // se guarda el usuario en el token
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
            $response->getBody()->write($payload);
            $response->withStatus(201); // 201 es el codigo de status que indica que se creo un recurso
        } else {
            $response->getBody()->write(json_encode(array("error" => "Usuario no encontrado")));
            $response->withStatus(404);
        }

        return $response
          ->withHeader('Content-Type', 'application/json');

    }


}








?>