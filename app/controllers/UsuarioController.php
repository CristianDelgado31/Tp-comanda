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
}








?>