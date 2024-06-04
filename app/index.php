<?php


require_once '../vendor/autoload.php';
require_once 'models/Restaurante.php';

// $app = new \Slim\Slim();
$app = \Slim\Factory\AppFactory::create();


$app->get('/hola/', function ($request, $response, array $args) {
	$response->getBody()->write("Funciona!");
	return $response;
});

$app->post('/agregarEmpleado/', function ($request, $response, array $args) { 
	$empleado = json_decode($request->getBody()); // que cambia request y response? -> request es lo que recibe y response es lo que devuelve 
	Restaurante::AgregarEmpleado($empleado);
	// echo $empleado->nombre;
	// echo json_encode($empleado);
	return $response->withStatus(201); // 201 es el codigo de status que indica que se creo un recurso
});

$app->get('/listarEmpleados/', function ($request, $response, array $args) {
	$empleados = BaseDeDatos::ListarEmpleados();
	$response->getBody()->write(json_encode($empleados));
	return $response->withHeader('Content-Type', 'application/json'); // se indica que el contenido de la respuesta es un json
});

//productos
$app->post('/agregarProducto/', function ($request, $response, array $args) { 
	$producto = json_decode($request->getBody()); // que cambia request y response? -> request es lo que recibe y response es lo que devuelve 
	Restaurante::AgregarProducto($producto);
	// echo $empleado->nombre;
	// echo json_encode($empleado);
	return $response->withStatus(201);
});

$app->get('/listarProductos/', function ($request, $response, array $args) {
	$productos = BaseDeDatos::ListarProductos();
	$response->getBody()->write(json_encode($productos));
	return $response->withHeader('Content-Type', 'application/json');
});


//mesa
$app->post('/agregarMesa/', function ($request, $response, array $args) { 
	$mesa = json_decode($request->getBody()); // que cambia request y response? -> request es lo que recibe y response es lo que devuelve 
	Restaurante::AgregarMesa($mesa);
	// echo $empleado->nombre;
	// echo json_encode($empleado);
	return $response->withStatus(201);
});

$app->get('/listarMesas/', function ($request, $response, array $args) {
	$mesas = BaseDeDatos::ListarMesas();
	$response->getBody()->write(json_encode($mesas));
	return $response->withHeader('Content-Type', 'application/json');
});

//pedido
$app->post('/agregarPedido/', function ($request, $response, array $args) { 
	$pedido = json_decode($request->getBody()); // que cambia request y response? -> request es lo que recibe y response es lo que devuelve 
	Restaurante::AgregarPedido($pedido);
	// echo $empleado->nombre;
	// echo json_encode($empleado);
	return $response->withStatus(201);
});

$app->get('/listarPedidos/', function ($request, $response, array $args) {
	$pedidos = 	Restaurante::ListarPedidos();
	$response->getBody()->write(json_encode($pedidos));
	return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
?>