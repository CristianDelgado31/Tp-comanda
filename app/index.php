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
// use Slim\Routing\RouteContext;

require_once '../vendor/autoload.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'controllers/UsuarioController.php';
require_once 'controllers/MesaController.php';
require_once 'controllers/ProductoController.php';
require_once 'controllers/PedidoController.php';


$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true); // Activa el manejo de errores

$app->addBodyParsingMiddleware(); // Middleware para parsear el body


// --------------------------------------------------------------------------------------------------------------------------------------------
$app->group('/usuarios', function (RouteCollectorProxy $group){
	$group->get('[/]', \UsuarioController::class . ':MostrarLista')->add(new AuthMiddleware("socio"));
	$group->post('[/]', \UsuarioController::class . ':AgregarUsuario')->add(new AuthMiddleware("socio"));
});

$app->group('/mesas', function (RouteCollectorProxy $group){
	$group->get('[/]', \MesaController::class . ':MostrarLista');
	$group->post('[/]', \MesaController::class . ':AgregarMesa');
});

$app->group('/productos', function (RouteCollectorProxy $group){
	$group->get('[/]', \ProductoController::class . ':MostrarLista');
	$group->post('[/]', \ProductoController::class . ':AgregarProducto');
});

$app->group('/pedidos', function (RouteCollectorProxy $group){
	$group->get('[/]', \PedidoController::class . ':MostrarLista');
	$group->post('[/]', \PedidoController::class . ':AgregarPedido');
	$group->put('/agarrarPedidoProducto/', \PedidoController::class . ':AgarrarPedidoProducto');
	$group->get('/listaPedidoProductos/', \PedidoController::class . ':ListarPedidosProductosPorRol');
});
// --------------------------------------------------------------------------------------------------------------------------------------------



$app->run();
?>