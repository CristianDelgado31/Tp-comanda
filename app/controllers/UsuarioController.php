<?php
// require_once 'Db/BaseDeDatos.php';
require_once 'models/Personas/Empleado.php';
require_once 'models/Personas/Socio.php';

// use TCPDF;

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

        $payload = json_encode(array("listaUsuario" => $lista));

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

        try {
            $usuario = Persona::VerificarUsuario($email, $contrasenia);

            if($usuario != null) {
                $datos = array("usuario" => $usuario); // se guarda el usuario en el token
                $token = AutentificadorJWT::CrearToken($datos);
                $payload = json_encode(array('jwt' => $token));
                $response = $response->withStatus(201); // 201 es el codigo de status que indica que se creo un recurso
            } else {
                $payload = json_encode(array("error" => "Usuario no encontrado"));
                $response = $response->withStatus(404);
            }
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

    }

    public static function EliminarUsuario($request, $response, $args) {
        $id = $args['id']; 

        if(!is_numeric($id)) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        try {
            Persona::CambiarEstadoUsuario($id, "eliminado");
            $payload = json_encode(array("mensaje" => "Usuario eliminado con exito"));
            $response->getBody()->write($payload);
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            $response = $response->withStatus(400);
        } finally {
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

    }

    public static function ModificarEstadoUsuario($request, $response, $args) {
        $body = json_decode($request->getBody());

        if(!isset($body->id) || !isset($body->estado)) {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        $id = $body->id;

        if(!is_numeric($id)) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        try {
            Persona::CambiarEstadoUsuario($id, $body->estado);
            $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
            $response->getBody()->write($payload);
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            $response = $response->withStatus(400);
        } finally {
            return $response
              ->withHeader('Content-Type', 'application/json');
        }
    }

    public static function ModificarUsuario($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');

        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);

        $id = $data->usuario->id;
        $nombre = $data->usuario->nombre;
        $apellido = $data->usuario->apellido;
        $rol = $data->usuario->rol;
        $estado = $data->usuario->estado;
        $email = $data->usuario->email;
        $contrasenia = NULL; // $data->usuario->contrasenia -> esto en el token esta con "" por seguridad

        // cambios al usuario
        $cambiosUsuario = json_decode($request->getBody());

        if($cambiosUsuario == NULL) {
            $response->getBody()->write(json_encode(array("error" => "No hay datos para modificar")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        // isset verifica si la variable esta definida y no es null, definir quiere decir que se le asigno un valor
        // acá se elimina la cuenta de uno mismo
        if(isset($cambiosUsuario->eliminarCuenta) && $cambiosUsuario->eliminarCuenta == true) {
            Persona::CambiarEstadoUsuario($id, "eliminado");
            
            $payload = json_encode(array("mensaje" => "Usuario eliminado con exito"));
            $response->getBody()->write($payload);
            $response->withStatus(200);
            
            return $response
              ->withHeader('Content-Type', 'application/json');
        }
        
        if (isset($cambiosUsuario->nombre)) {
            $nombre = $cambiosUsuario->nombre;
        }
        if (isset($cambiosUsuario->apellido)) {
            $apellido = $cambiosUsuario->apellido;
        }
        if (isset($cambiosUsuario->email)) {
            $email = $cambiosUsuario->email;
            $listaUsuarios = Persona::MostrarLista();

            foreach ($listaUsuarios as $usuarioDB) {
                if ($usuarioDB->email == $email) {
                    $response->getBody()->write(json_encode(array("error" => "El email ya esta registrado")));
                    $response->withStatus(400);
                    return $response
                      ->withHeader('Content-Type', 'application/json');
                }
            }
        }
        if (isset($cambiosUsuario->contrasenia)) {
            $contrasenia = password_hash($cambiosUsuario->contrasenia, PASSWORD_DEFAULT);
        }
        

        $usuario = new Persona($nombre, $apellido, $rol, $email, $contrasenia, $estado);
        $usuario->id = $id;
        $usuario->ModificarUsuario();

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
        $response->getBody()->write($payload);

        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ListaUsuariosEnCSV($request, $response, $args) {
        $lista = Persona::MostrarLista();

        $csv = ""; 

        foreach ($lista as $usuario) {
            $csv .= $usuario->id . "," . $usuario->nombre . "," . $usuario->apellido . "," . $usuario->rol . "," . $usuario->email . "," . $usuario->estado . "," . $usuario->fechaBaja . "\n";
        }

        $response->getBody()->write($csv);
        return $response
          ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="usuarios.csv"');
    }


    public static function ImportarUsuariosDesdeCSV($request, $response, $args) {
        $archivo = $_FILES["usuarios"];
        
        if ($archivo["type"] != "text/csv") {
            $response->getBody()->write(json_encode(array("error" => "El archivo debe ser de tipo CSV")));
            $response = $response->withStatus(400);
            return $response->withHeader('Content-Type', 'application/json');
        }
    
        // Abrir el archivo CSV
        $file = fopen($archivo["tmp_name"], "r");
        
        $listaUsuarios = Persona::MostrarLista();
        $contadorRolSocios = 0;
    
        foreach ($listaUsuarios as $usuarioDB) {
            if ($usuarioDB->rol == "socio" && $usuarioDB->estado == "activo") {
                $contadorRolSocios++;
            }
        }
    
        $errores = [];
        $usuariosImportados = 0;
    
        // Leer cada línea del archivo CSV
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            // Verificar si la cantidad de datos en la fila es correcta
            if (count($data) == 5) {
                $nombre = $data[0];
                $apellido = $data[1];
                $rol = $data[2];
                $email = $data[3];
                $contrasenia = password_hash($data[4], PASSWORD_DEFAULT);
                $estado = "activo";
                
                // Verificar si el email es válido
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = "El email '$email' no es válido.";
                    continue;
                }

                $emailYaRegistrado = false;
                
                foreach ($listaUsuarios as $usuarioDB) {
                    if ($usuarioDB->email == $email) {
                        $emailYaRegistrado = true;
                        break;
                    }
                }
    
                if ($emailYaRegistrado) {
                    $errores[] = "El email '$email' ya está registrado.";
                    continue;
                }
    
                if ($rol == "socio" && $contadorRolSocios >= 3) {
                    $errores[] = "Ya hay 3 socios registrados. No se puede registrar el email '$email'.";
                    continue;
                }
    
                if ($rol == "socio") {
                    $contadorRolSocios++;
                }
    
                $usuario = new Persona($nombre, $apellido, $rol, $email, $contrasenia, $estado);
                $usuario->AgregarUsuario();
                $usuariosImportados++;
            } else {
                $errores[] = "Datos incompletos en una fila.";
            }
        }
    
        // Cerrar el archivo CSV
        fclose($file);
    
        if (count($errores) > 0) {
            $response->getBody()->write(json_encode(array("errores" => $errores, "usuariosImportados" => $usuariosImportados)));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        } else {
            $response->getBody()->write(json_encode(array("mensaje" => "Usuarios importados con éxito", "usuariosImportados" => $usuariosImportados)));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    

    public static function DescargarPDFUsuarios($request, $response, $args) {
        // Crea una nueva instancia de TCPDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // Genera el contenido HTML para el PDF
        $html = Persona::GenerarHtmlDeUsuarios();
        $pdf->writeHTML($html, true, false, true, false, '');

        // Salida como cadena
        $pdfOutput = $pdf->Output('usuarios.pdf', 'S'); // S: Devuelve el documento como una cadena

        // Configura la respuesta
        $response = $response->withHeader('Content-Type', 'application/pdf')
                             ->withHeader('Content-Disposition', 'attachment; filename="usuarios.pdf"')
                             ->withHeader('Content-Length', strlen($pdfOutput));

        $response->getBody()->write($pdfOutput);

        return $response;
    }
    
    public static function EstadisticaLogUsuarios($request, $response, $args) {
        $lista = Persona::EstadisticaLogUsuariosPor30Dias();

        $payload = json_encode(array("estadistica logs id usuarios en 30 dias" => $lista));

        $response->getBody()->write($payload);

        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}




?>