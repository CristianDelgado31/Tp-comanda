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
use TCPDF; // Para generar PDFs en los endpoints

require_once '../vendor/autoload.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'controllers/UsuarioController.php';
require_once 'controllers/MesaController.php';
require_once 'controllers/ProductoController.php';
require_once 'controllers/PedidoController.php';
require_once 'utils/AutentificadorJWT.php';
require_once 'middleware/RolMiddleware.php';


$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true); // Activa el manejo de errores

$app->addBodyParsingMiddleware(); // Middleware para parsear el body


// --------------------------------------------------------------------------------------------------------------------------------------------
$app->post('/usuario/login', \UsuarioController::class . ':Login');
$app->get('/cliente/tiempoEstimado' , \PedidoController::class . ':TiempoEstimadoDelPedido');
$app->post('/cliente/encuesta', \PedidoController::class . ':RealizarEncuesta');
$app->put('/usuario', \UsuarioController::class . ':ModificarUsuario')
	->add(AuthMiddleware::class . ':VerificarToken');


$app->group('/descargar-pdf' , function (RouteCollectorProxy $group){
	$group->get('/usuarios', \UsuarioController::class . ':DescargarPDFUsuarios');
	$group->get('/productos', \ProductoController::class . ':DescargarPDFProductos');
	$group->get('/pedidos', \PedidoController::class . ':DescargarPDFPedidos');
	$group->get('/mesas', \MesaController::class . ':DescargarPDFMesas');
})->add(new RolMiddleware(['socio', 'admin']))
->add(AuthMiddleware::class . ':VerificarToken');


$app->group('/usuarios', function (RouteCollectorProxy $group){
	$group->get('[/]', \UsuarioController::class . ':MostrarLista')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->post('[/]', \UsuarioController::class . ':AgregarUsuario')
		->add(new RolMiddleware(['admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
		
	$group->delete('/{id}', \UsuarioController::class . ':EliminarUsuario')
		->add(new RolMiddleware(['admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/exportarCSV', \UsuarioController::class . ':ListaUsuariosEnCSV')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['admin', 'socio'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->post('/importarCSV', \UsuarioController::class . ':ImportarUsuariosDesdeCSV')
		->add(new RolMiddleware(['admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
})->add(AuthMiddleware::class . ':VerificarToken');


$app->group('/mesas', function (RouteCollectorProxy $group) {
	$group->get('[/]', \MesaController::class . ':MostrarLista')
		->add(new RolMiddleware(['socio', 'mozo']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['mozo', 'socio']; // Ejemplo de roles permitidos
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->post('[/]', \MesaController::class . ':AgregarMesa')
		->add(new RolMiddleware(['socio']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->delete('/{id}', \MesaController::class . ':EliminarMesa')
		->add(new RolMiddleware(['socio']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->put('[/]', \MesaController::class . ':ModificarMesa')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/exportarCSV', \MesaController::class . ':ListaMesasEnCSV')
		->add(new RolMiddleware(['socio']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->post('/importarCSV', \MesaController::class . ':ImportarMesasDesdeCSV')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/masUsada', \MesaController::class . ':MostrarMesaMasUsada')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/menosUsada', \MesaController::class . ':MostrarMesaMenosUsada')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/masFacturo', \MesaController::class . ':MostrarMesaQueMasFacturo')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/menosFacturo', \MesaController::class . ':MostrarMesaQueMenosFacturo')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/mejoresComentarios', \MesaController::class . ':MostrarMejoresComentarios')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/peoresComentarios', \MesaController::class . ':MostrarPeoresComentarios')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->post('/facturacionEntreFechas', \MesaController::class . ':MostrarFacturacionEntreFechas')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
})->add(AuthMiddleware::class . ':VerificarToken');


$app->group('/productos', function (RouteCollectorProxy $group){
	$group->get('[/]', \ProductoController::class . ':MostrarLista');
	$group->post('[/]', \ProductoController::class . ':AgregarProducto')
		->add(new RolMiddleware(['admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->put('[/]', \ProductoController::class . ':ModificarProducto')
		->add(new RolMiddleware(['admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->delete('/{id}', \ProductoController::class . ':EliminarProducto')
		->add(new RolMiddleware(['admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/exportarCSV', \ProductoController::class . ':ExportarListaEnCSV')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->post('/importarCSV', \ProductoController::class . ':ImportarProductosDesdeCSV')
		->add(new RolMiddleware(['admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
})->add(AuthMiddleware::class . ':VerificarToken');


$app->group('/pedidos', function (RouteCollectorProxy $group){
	$group->get('[/]', \PedidoController::class . ':MostrarLista')
		->add(new RolMiddleware(['socio'])); // 'mozo' podria agregarlo
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->post('[/]', \PedidoController::class . ':AgregarPedido')
		->add(new RolMiddleware(['mozo']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['mozo'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->put('/agarrarPedidoProducto/', \PedidoController::class . ':AgarrarPedidoProducto')
		->add(new RolMiddleware(['cocinero', 'bartender', 'cervecero']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['cocinero', 'bartender', 'cervecero'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/listaPedidoProductos/', \PedidoController::class . ':ListarPedidosProductosPorRol')
		->add(new RolMiddleware(['cocinero', 'bartender', 'cervecero']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['cocinero', 'bartender', 'cervecero'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->put('/finalizarPedidoProducto/', \PedidoController::class . ':FinalizarProductoDePedido')
		->add(new RolMiddleware(['cocinero', 'bartender', 'cervecero']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['cocinero', 'bartender', 'cervecero'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->delete('/{id}', \PedidoController::class . ':EliminarPedido')
		->add(new RolMiddleware(['socio']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->put('[/]', \PedidoController::class . ':ModificarPedido')
		->add(new RolMiddleware(['mozo']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['mozo'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->put('/modificarProductoPedido', \PedidoController::class . ':ModificarProductoPedido')
		->add(new RolMiddleware(['mozo']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['mozo'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/exportarCSV/pedidos', \PedidoController::class . ':ExportarListaPedidosEnCSV')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/exportarCSV/pedidos_productos', \PedidoController::class . ':ExportarListaPedidosProductosCSV')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// 	});
	$group->post('/modificarEstadoPedido', \PedidoController::class . ':ModificarEstadoPedido')
		->add(new RolMiddleware(['mozo', 'socio']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['mozo', 'socio'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/cantOperaciones/sector', \PedidoController::class . ':CantidadOperacionesPorSector')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/cantOperaciones/sectorEmpleados', \PedidoController::class . ':ListOperacionesSectorConEmpleados')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/cantOperaciones/porEmpleado', \PedidoController::class . ':ListarOperacionesPorEmpleado')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/masVendido', \PedidoController::class . ':MostrarProductoMasVendido')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/menosVendido', \PedidoController::class . ':MostrarProductoMenosVendido')
		->add(new RolMiddleware(['socio', 'admin']));
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
	$group->get('/malTiempo', \PedidoController::class . ':MostrarPedidosMalDeTiempo')
		->add(new RolMiddleware(['socio', 'admin']));	
		// ->add(function (Request $request, RequestHandler $handler) {
		// 	$rolesPermitidos = ['socio', 'admin'];
		// 	return AuthMiddleware::VerificarRol($request, $handler, $rolesPermitidos);
		// });
})->add(AuthMiddleware::class . ':VerificarToken');


$app->run();
?>