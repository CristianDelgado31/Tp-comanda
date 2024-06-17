<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;
require_once __DIR__ . '/../models/Personas/Persona.php';
// require_once __DIR__ . '/../models/Db/BaseDeDatos.php';

class AuthMiddleware {

    private $rol;

    public function __construct($rol){
        $this->rol = $rol;
    }

    public function __invoke(Request $request, RequestHandler $requestHandler){
        return $this->auth($request, $requestHandler);
    }

    function auth(Request $request, RequestHandler $requestHandler){
        $response = new ResponseClass();
        $flag = false;
        echo "Entro al authMW \n";
        $params = $request->getQueryParams();

        if(isset($params["nombre"], $params["apellido"])){
            $nombre = $params["nombre"];
            $apellido = $params["apellido"];

            $listaEmpleados = Persona::MostrarLista();
            if($listaEmpleados != null){
                foreach($listaEmpleados as $empleado){
                    if($empleado->nombre == $nombre && $empleado->apellido == $apellido){
                        $flag = true;
                        if($empleado->rol == $this->rol){
                            $response = $requestHandler->handle($request);
                        }
                    }
                }
                
                if($flag == false){
                    $response->getBody()->write(json_encode(array("error" => "Este usuario no tiene rol de ".$this->rol)));
                }
            }
            else{
                $response->getBody()->write(json_encode(array("error" => "No hay usuario en la base de datos")));
            }
        }
        else {
            // no tiene credenciales
            $response->getBody()->write(json_encode(array("error" => "Complete los campos nombre y apellido")));
        }

        echo "Salgo del authMW \n";

        return $response;
    }
}






?>