<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;
// require_once __DIR__ . '/../utils/AutentificadorJWT.php';

class RolMiddleware
{
    private $roles;

    public function __construct($roles)
    {
        $this->roles = $roles;
    }

    public function __invoke(Request $request, RequestHandler $requestHandler){
        return $this->VerificalRol($request, $requestHandler);
    }

    private function VerificalRol(Request $request, RequestHandler $requestHandler) {
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
    
            if (in_array($rol, $this->roles)) {
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
            $payload = json_encode(array("error" => $e->getMessage()));
        }
    
        $response->getBody()->write($payload);
    
        return $response->withHeader('Content-Type', 'application/json');
    }


}







?>