<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;
use Slim\Factory\AppFactory;

require_once '../vendor/autoload.php';
require_once 'models/Restaurante.php';
require_once 'middleware/AuthMiddleware.php';

// $app = new \Slim\Slim();
$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true); // Activa el manejo de errores

$app->addBodyParsingMiddleware(); // Middleware para parsear el body

$app->get('/hola/', function ($request, $response, array $args) {
	$response->getBody()->write("Funciona!");
	return $response;
});

$app->post('/agregarUsuario/', function (Request $request, Response $response) { 
	$empleado = json_decode($request->getBody()); // que cambia request y response? -> request es lo que recibe y response es lo que devuelve 
	Restaurante::AgregarUsuario($empleado);
	// echo $empleado->nombre;
	// echo json_encode($empleado);
	return $response->withStatus(201); // 201 es el codigo de status que indica que se creo un recurso
})->add(new AuthMiddleware("socio"));

$app->get('/listarUsuarios/', function (Request $request, Response $response) {
	$empleados = BaseDeDatos::ListarUsuarios();
	$response->getBody()->write(json_encode($empleados));
	return $response->withHeader('Content-Type', 'application/json'); // se indica que el contenido de la respuesta es un json
})->add(new AuthMiddleware("socio"));

// //productos
$app->post('/agregarProducto/', function (Request $request, Response $response) { 
	$producto = json_decode($request->getBody()); // que cambia request y response? -> request es lo que recibe y response es lo que devuelve 
	Restaurante::AgregarProducto($producto);
	return $response->withStatus(201);
})->add(new AuthMiddleware("socio"));

$app->get('/listarProductos/', function (Request $request, Response $response) {
	$productos = BaseDeDatos::ListarProductos();
	$response->getBody()->write(json_encode($productos));
	return $response->withHeader('Content-Type', 'application/json');
});


//mesa
$app->post('/agregarMesa/', function (Request $request, Response $response) { 
	$mesa = json_decode($request->getBody()); // que cambia request y response? -> request es lo que recibe y response es lo que devuelve 
	Restaurante::AgregarMesa($mesa);
	// echo $empleado->nombre;
	// echo json_encode($empleado);
	return $response->withStatus(201);
});

$app->get('/listarMesas/', function (Request $request, Response $response) {
	$mesas = BaseDeDatos::ListarMesas();
	$response->getBody()->write(json_encode($mesas));
	return $response->withHeader('Content-Type', 'application/json');
});

//pedido
$app->post('/agregarPedido/', function (Request $request, Response $response) { 
	$pedido = json_decode($request->getBody()); // que cambia request y response? -> request es lo que recibe y response es lo que devuelve 
	Restaurante::AgregarPedido($pedido);
	// echo $empleado->nombre;
	// echo json_encode($empleado);
	return $response->withStatus(201);
})->add(new AuthMiddleware("mozo"));


$app->get('/listarPedidos/', function (Request $request, Response $response) {
	$pedidos = 	Restaurante::ListarPedidos();
	$response->getBody()->write(json_encode($pedidos));
	return $response->withHeader('Content-Type', 'application/json');
})->add(new AuthMiddleware("mozo"));

$app->put('/agarrarPedidoProducto/', function (Request $request, Response $response) {
	$pedido = json_decode($request->getBody());
	$result = Restaurante::AgarrarPedidoProducto($pedido);

	if($result == -1){
		$response->getBody()->write(json_encode(array("error" => "El empleado o usuario no existe")));
	}
	else if($result == 0)
	{
		$response->getBody()->write(json_encode(array("error" => "El empleado esta ocupado")));
	}
	else if($result == 1){
		$response->getBody()->write(json_encode(array("error" => "El pedido_producto ingresado no existe o su rol no tiene permisos para tomarlo")));
	}
	else if($result == 2){
		$response->getBody()->write(json_encode(array("error" => "El pedido_producto ingresado ya fue tomado")));
	}
	else if($result == 3){
		$response->getBody()->write(json_encode(array("error" => "El rol ingresado no tiene pedidos pendientes")));
	}
	else {
		$response->getBody()->write(json_encode($result)); // se devuelve el pedido_producto modificado
	}
	// se modificara el registro relacionado con el codigo_pedido de la tabla pedido_producto y se modificara el 
	// tiempo estimado del pedido si el tiempo_producto es mayor o si tiempo estimado es null -> preguntar al profe
	
	return $response->withHeader('Content-Type', 'application/json');
});

// podria agregarle un middleware de roles de los usuarios
$app->get('/listaPedidoProductos/', function (Request $request, Response $response) {
	$params = $request->getQueryParams();
	$pedido = Restaurante::ListarPedidosProductos($params["rol"]);
	
	if($pedido == 0){
		$response->getBody()->write(json_encode(array("error" => "El rol ingresado no existe")));
		return $response->withHeader('Content-Type', 'application/json');
	}
	else if($pedido == 1){
		$response->getBody()->write(json_encode(array("error" => "Lista de pedidos-productos esta vacia porque no tiene elementos con estados pendientes con el rol ingresado")));
		return $response->withHeader('Content-Type', 'application/json');
	}
	
	$response->getBody()->write(json_encode($pedido));
	return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
?>