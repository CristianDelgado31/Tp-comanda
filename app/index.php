<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require_once '../vendor/autoload.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'controllers/UsuarioController.php';
require_once 'controllers/MesaController.php';
require_once 'controllers/ProductoController.php';
require_once 'controllers/PedidoController.php';
require_once 'utils/AutentificadorJWT.php';


$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true); // Activa el manejo de errores

$app->addBodyParsingMiddleware(); // Middleware para parsear el body


// --------------------------------------------------------------------------------------------------------------------------------------------
$app->post('/usuario/login', \UsuarioController::class . ':Login');
$app->get('/cliente/tiempoEstimado' , \PedidoController::class . ':TiempoEstimadoDelPedido');

$app->group('/usuarios', function (RouteCollectorProxy $group){
	$group->get('[/]', \UsuarioController::class . ':MostrarLista');
	$group->post('[/]', \UsuarioController::class . ':AgregarUsuario');
})->add(function (Request $request, RequestHandler $handler) {
	$rolesPermitidos = ['socio'];
	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
})->add(AuthMiddleware::class . ':VerificarToken');


$app->group('/mesas', function (RouteCollectorProxy $group) {
	$group->get('[/]', \MesaController::class . ':MostrarLista')
		->add(function (Request $request, RequestHandler $handler) {
			$rolesPermitidos = ['mozo', 'socio']; // Ejemplo de roles permitidos
			return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		});
		
	$group->post('[/]', \MesaController::class . ':AgregarMesa')
		->add(function (Request $request, RequestHandler $handler) {
			$rolesPermitidos = ['socio'];
			return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		});
})->add(AuthMiddleware::class . ':VerificarToken');


$app->group('/productos', function (RouteCollectorProxy $group){
	$group->get('[/]', \ProductoController::class . ':MostrarLista');
	$group->post('[/]', \ProductoController::class . ':AgregarProducto')
		->add(function (Request $request, RequestHandler $handler) {
			$rolesPermitidos = ['socio'];
			return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		});
})->add(AuthMiddleware::class . ':VerificarToken');


$app->group('/pedidos', function (RouteCollectorProxy $group){
	$group->get('[/]', \PedidoController::class . ':MostrarLista')
		->add(function (Request $request, RequestHandler $handler) {
			$rolesPermitidos = ['socio'];
			return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		});
	$group->post('[/]', \PedidoController::class . ':AgregarPedido')
		->add(function (Request $request, RequestHandler $handler) {
			$rolesPermitidos = ['mozo', 'socio'];
			return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		});
	$group->put('/agarrarPedidoProducto/', \PedidoController::class . ':AgarrarPedidoProducto')
		->add(function (Request $request, RequestHandler $handler) {
			$rolesPermitidos = ['cocinero', 'bartender', 'cervecero'];
			return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		});
	$group->get('/listaPedidoProductos/', \PedidoController::class . ':ListarPedidosProductosPorRol')
		->add(function (Request $request, RequestHandler $handler) {
			$rolesPermitidos = ['cocinero', 'bartender', 'cervecero'];
			return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		});
	$group->put('/finalizarPedidoProducto/', \PedidoController::class . ':FinalizarProductoDePedido')
		->add(function (Request $request, RequestHandler $handler) {
			$rolesPermitidos = ['cocinero', 'bartender', 'cervecero'];
			return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		});
})->add(AuthMiddleware::class . ':VerificarToken');
// --------------------------------------------------------------------------------------------------------------------------------------------

// --------------------------------------------------------------------------------------------------------------------------------------------
// Endpoints para JWT
$app->group('/jwt', function(RouteCollectorProxy $group) {
	$group->post('/crearToken', function (Request $request, Response $response) {
		$parametros = $request->getParsedBody();

		$usuario = $parametros["usuario"];
		$perfil = $parametros["perfil"];
		$alias = $parametros["alias"];

		$datos = array("usuario" => $usuario, "perfil" => $perfil, "alias" => $alias);

		$token = AutentificadorJWT::CrearToken($datos);
		$payload = json_encode(array('jwt' => $token));
		
		$response->getBody()->write($payload);

		return $response->withHeader('Content-Type', 'application/json');

	});

	$group->get('/verificarToken', function (Request $request, Response $response) {
		$header = $request->getHeaderLine('Authorization');
		$esValido = false;

		if($header) {
			$token = trim(explode("Bearer", $header)[1]);
		} else {
			$token = "";
		}


		try {
			AutentificadorJWT::VerificarToken($token);
			$esValido = true;
		} catch (Exception $e) {
			$payload = json_encode(array("error" => $e->getMessage()));
		}

		if($esValido) {
			$payload = json_encode(array("valid" => $esValido));
		}

		$response->getBody()->write($payload);

		return $response->withHeader('Content-Type', 'application/json');
	});

	$group->get('/devolverPayload', function (Request $request, Response $response) {
		$header = $request->getHeaderLine('Authorization');
		
		if($header) {
			$token = trim(explode("Bearer", $header)[1]);
		} else {
			$token = "";
		}

		try {
			$payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayload($token)));
		} catch (Exception $e) {
			$payload = json_encode(array("error" => $e->getMessage()));
		}

		$response->getBody()->write($payload);

		return $response->withHeader('Content-Type', 'application/json');
	});

	$group->get('/devolverData', function (Request $request, Response $response) {
		$header = $request->getHeaderLine('Authorization');
		
		if($header) {
			$token = trim(explode("Bearer", $header)[1]);
		} else {
			$token = "";
		}

		try {
			$payload = json_encode(array('data' => AutentificadorJWT::ObtenerData($token)));
		} catch (Exception $e) {
			$response = new ResponseClass();
			$payload = json_encode(array("ERROR" => 'Hubo un error con el token'));
		}

		$response->getBody()->write($payload);

		return $response->withHeader('Content-Type', 'application/json');
	});
});

// --------------------------------------------------------------------------------------------------------------------------------------------

$app->run();
?>