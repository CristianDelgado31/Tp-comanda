<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;
require_once __DIR__ . '/../models/Personas/Persona.php';
// require_once __DIR__ . '/../models/Db/BaseDeDatos.php';
require_once __DIR__ . '/../utils/AutentificadorJWT.php';

class AuthMiddleware {

    public static function VerificarToken(Request $request, RequestHandler $requestHandler) {
        $response = new ResponseClass();
        $header = $request->getHeaderLine('Authorization');

		if($header) {
			$token = trim(explode("Bearer", $header)[1]);
		} else {
			$token = "";
		}

        try {
            AutentificadorJWT::VerificarToken($token);
            $esValido = true;
            $response = $requestHandler->handle($request); // si el token es valido, continua con la peticion
            return $response;
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
        }

        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');

    }

    public static function VerificarRol(Request $request, RequestHandler $requestHandler, $rolesPermitidos) {
        $response = new ResponseClass();
        $header = $request->getHeaderLine('Authorization');
    
        if ($header) {
            $token = trim(explode("Bearer", $header)[1]);
        } else {
            $token = "";
        }
    
        try {
            $data = AutentificadorJWT::ObtenerData($token);
            $rol = $data->usuario->rol;
    
            if (in_array($rol, $rolesPermitidos)) {
                // Si el rol está en los roles permitidos, continuar con la solicitud
                $response = $requestHandler->handle($request);
                return $response;
            } else {
                // Si el rol no está en los roles permitidos, devolver mensaje de error
                $payload = json_encode(array("error" => "Este usuario no tiene el rol adecuado para acceder a esta función"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
        } catch (Exception $e) {
            // Manejo de excepciones al decodificar el token
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
    
    

}






?>