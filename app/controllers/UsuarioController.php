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
        $email = $usuario->email;
        $contrasenia = $usuario->contrasenia;
        $contrasenia = password_hash($contrasenia, PASSWORD_DEFAULT);

        $listaUsuarios = Persona::MostrarLista();

        $contadorRolSocios = 0;

        foreach ($listaUsuarios as $usuarioDB) {
            if ($usuarioDB->email == $email) {
                $response->getBody()->write(json_encode(array("error" => "El email ya esta registrado")));
                $response->withStatus(400);
                return $response
                  ->withHeader('Content-Type', 'application/json');
            }

            if ($usuarioDB->rol == "socio" && $usuarioDB->estado == "activo") {
                $contadorRolSocios++;
            }
        }

        if ($rol == "socio" && $contadorRolSocios == 3) {
            $response->getBody()->write(json_encode(array("error" => "Ya hay 3 socios registrados")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        $estado = isset($usuario->estado) ? $usuario->estado : "activo"; // si estado no se pasa, por defecto es activo
        
        $usuario = new Persona($nombre, $apellido, $rol, $email, $contrasenia, $estado);
        $usuario->AgregarUsuario();

        $payload = json_encode(array("mensaje" => "Usuario agregado con exito"));

        $response->getBody()->write($payload);
        $response->withStatus(201); // 201 es el codigo de status que indica que se creo un recurso
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function MostrarLista($request, $response, $args) {
        $lista = Persona::MostrarLista();

        $listaRetorno = array();

        foreach ($lista as $usuario) {
            if($usuario->estado == "inactivo") {
                continue;
            }
            $listaRetorno[] = $usuario; // se guarda el usuario en el arrays
        }

        $payload = json_encode(array("listaUsuario" => $listaRetorno));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function Login($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $email = $parametros["email"] ?? ""; // si no se pasa el nombre, por defecto es ""
        $contrasenia = $parametros["contrasenia"] ?? "";

        if($email == "" || $contrasenia == "") {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        $usuario = Persona::VerificarUsuario($email, $contrasenia);

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

    public static function EliminarUsuario($request, $response, $args) {
        // no anda esta validacion
        // if (!isset($args['id']) || empty($args['id'])) {
        //     $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
        //     $response->withStatus(400);
        //     return $response
        //       ->withHeader('Content-Type', 'application/json');
        // }

        $id = $args['id']; 

        if(!is_numeric($id)) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        $usuario = Persona::EliminarUsuario($id);

        if($usuario == true) {
            $payload = json_encode(array("mensaje" => "Usuario eliminado con exito"));
            $response->getBody()->write($payload);
            $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(array("error" => "Usuario no encontrado")));
            $response->withStatus(404);
        }

        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}








?>