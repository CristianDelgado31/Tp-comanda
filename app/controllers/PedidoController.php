<?php
require_once 'BaseController.php';
require_once 'models/Pedido.php';

// use TCPDF;

class PedidoController extends BaseController{

    public static function MostrarLista($request, $response, $args) {
        $pedidos = Pedido::MostrarLista();
        $payload = json_encode(array("listaPedidos" => $pedidos));
        $response->getBody()->write($payload); //payload es un array asociativo
        
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ListarPedidosProductosPorRol($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');

        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $rol = $data->usuario->rol;

        try {
            $pedidosProductos = Pedido::ListarPedidosProductos($rol);
            $payload = json_encode(array("listaPedidosProductos" => $pedidosProductos));
            $response = $response->withStatus(200);
            
        } catch (Exception $e) {
            $payload = json_encode(array("mensaje" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public static function AgregarPedido($request, $response, $args) {
        //sacar el id del usuario del jwt 
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario
        $id = $usuario->id;
        $cant_operaciones = $usuario->cant_operaciones;
        // Pedido::ActualizarOperacion($id, $cant_operaciones);

        $pedido = json_decode($request->getBody());
        // $productos = $pedido->productos;
        $pedido = new Pedido($pedido->codigoAlfanumerico,$pedido->nombreCliente ,$pedido->codigoMesa, $pedido->estado, $pedido->productos); // $pedido->productos es un array

        try {
            $result = $pedido->AgregarPedido();
            // si no hay un throw en la funcion AgregarPedido, se ejecuta lo siguiente
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Pedido agregado correctamente"));
            $response = $response->withStatus(201);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public static function AgarrarPedidoProducto($request, $response, $args) {
        $pedido = json_decode($request->getBody());
        
        $id_pedido_producto = $pedido->id_pedido_producto;
        $tiempoPreparacion = $pedido->tiempoPreparacion;
        $estado = $pedido->estado;
        $header = $request->getHeaderLine('Authorization');

        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        
        $id = $data->usuario->id;
        $cant_operaciones = $data->usuario->cant_operaciones;
        $email = $data->usuario->email;

        try {
            $result = Pedido::AgarrarProductoDePedido($email, $id_pedido_producto, $estado, $tiempoPreparacion);
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Pedido tomado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

    }


    public static function TiempoEstimadoDelPedido($request, $response, $args) {
        $request = $request->getQueryParams();
        $codigoPedido = $request["codigoPedido"] ?? "";
        $codigoMesa = $request["codigoMesa"] ?? "";

        if($codigoPedido == "" || $codigoMesa == "") {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        try {
            $tiempoEstimado = Pedido::TiempoEstimadoDelPedido($codigoPedido, $codigoMesa);
            $payload = json_encode(array("tiempoEstimado" => $tiempoEstimado));
            $response = $response->withStatus(200);    
        } catch (Exception $e) {
            $payload = json_encode(array("mensaje" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public static function FinalizarProductoDePedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario
        $id = $usuario->id;
        $cant_operaciones = $usuario->cant_operaciones;

        try {
            $actualizarPedido = Pedido::FinalizarProductoDePedido($usuario);
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Producto finalizado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public static function CancelarPedido($request, $response, $args) {
        $body = json_decode($request->getBody());

        if($body == null){
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if(!isset($body->codigo_pedido) || !isset($body->codigo_mesa)){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $codigo_pedido = $body->codigo_pedido;
        $codigo_mesa = $body->codigo_mesa;

        try {
            $result = Pedido::CancelarPedido($codigo_pedido, $codigo_mesa);
            $payload = json_encode(array("mensaje" => "Pedido cancelado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public static function EliminarPedido($request, $response, $args) {
        $id = $args['id'];

        if(isset($id) == false){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        try {
            $result = Pedido::EliminarPedido($id);
            $payload = json_encode(array("mensaje" => "Pedido eliminado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }


    public static function ModificarPedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario
        $id = $usuario->id;
        $cant_operaciones = $usuario->cant_operaciones;

        $pedido = json_decode($request->getBody());

        if($pedido == null){
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if(!isset($pedido->id) || !is_numeric($pedido->id)){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if($pedido->id <= 0) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser mayor a 0")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $id = $pedido->id;
        $codigoAlfanumerico = "";
        $nombreCliente = "";
        $codigoMesa = "";
        $tiempoEstimado = "";

        if(isset($pedido->codigoAlfanumerico)){
            $codigoAlfanumerico = $pedido->codigoAlfanumerico;
        }
        if(isset($pedido->nombreCliente)){
            $nombreCliente = $pedido->nombreCliente;
        }
        if(isset($pedido->codigoMesa)){
            $codigoMesa = $pedido->codigoMesa;
        }
        if(isset($pedido->tiempoEstimado)){
            $tiempoEstimado = $pedido->tiempoEstimado;
        }
        
        if($codigoAlfanumerico == "" && $nombreCliente == "" && $codigoMesa == "" && $tiempoEstimado == ""){
            $response->getBody()->write(json_encode(array("error" => "No se envio ningun dato para modificar")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        try {
            $result = Pedido::ModificarPedido($id, $codigoAlfanumerico, $nombreCliente, $codigoMesa, $tiempoEstimado);
            Pedido::ActualizarOperacion($id, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Pedido modificado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public static function ModificarProductoPedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario; // esto es un objeto con los datos del usuario
        $idUsuario = $usuario->id;
        $cant_operaciones = $usuario->cant_operaciones;

        $pedido = json_decode($request->getBody());

        if($pedido == null){
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if(!isset($pedido->id) || !is_numeric($pedido->id)){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if($pedido->id <= 0) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser mayor a 0")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $id = $pedido->id;
        $codigo_pedido = "";
        $id_producto = "";
        $tiempo_producto = "";
        
        if(isset($pedido->codigo_pedido)){
            $codigo_pedido = $pedido->codigo_pedido;
        }
        if(isset($pedido->id_producto)){
            $id_producto = $pedido->id_producto;
        }

        if($codigo_pedido == "" && $id_producto == ""){
            $response->getBody()->write(json_encode(array("error" => "No se envio ningun dato para modificar")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
        
        try {
            $result = Pedido::ModificarProductoDePedido($id, $codigo_pedido, $id_producto);
            Pedido::ActualizarOperacion($idUsuario, $cant_operaciones);
            $payload = json_encode(array("mensaje" => "Producto del pedido modificado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

    }

    public static function ExportarListaPedidosEnCSV($request, $response, $args) {
        $lista = Pedido::ListaPedidosFormatoCSV();

        $response->getBody()->write($lista);
        return $response
            ->withHeader('Content-Type', 'application/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="pedidos.csv"');
    }

    public static function ExportarListaPedidosProductosCSV($request, $response, $args) {
        $lista = Pedido::ListaPedidosProductosCSV();

        $response->getBody()->write($lista);
        return $response
            ->withHeader('Content-Type', 'application/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="pedidos_productos.csv"');
    }


    public static function ModificarEstadoPedido($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $data = AutentificadorJWT::ObtenerData($token);
        $rol = $data->usuario->rol;


        $pedido = json_decode($request->getBody());

        if($pedido == null){
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if(!isset($pedido->id) || !is_numeric($pedido->id)){ // si no se envia el id
            $response->getBody()->write(json_encode(array("error" => "El id debe ser un numero")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        if($pedido->id <= 0) {
            $response->getBody()->write(json_encode(array("error" => "El id debe ser mayor a 0")));
            $response->withStatus(400);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }

        $id = $pedido->id;

        try {
            $result = Pedido::ModificarEstadoPedido($id, $rol);
            // Si no hay un throw en la funcion ModificarEstadoPedido, se ejecuta lo siguiente
            if($rol != "socio"){
                $id = $data->usuario->id;
                $cant_operaciones = $data->usuario->cant_operaciones;
                Pedido::ActualizarOperacion($id, $cant_operaciones);
            }
            $payload = json_encode(array("mensaje" => "Estado del pedido modificado correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }


    public static function RealizarEncuesta($request, $response, $args) {
        $pedido = json_decode($request->getBody());
    
        if ($pedido == null) {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Validaciones de campos obligatorios
        if (empty($pedido->codigo_mesa) || empty($pedido->codigo_pedido) || !isset($pedido->puntuacion_mesa) || 
            !isset($pedido->puntuacion_restaurante) || !isset($pedido->puntuacion_mozo) || empty($pedido->descripcion)) {
            $response->getBody()->write(json_encode(array("error" => "Faltan datos obligatorios")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Validaciones de puntuaciones (1 a 10)
        $validarPuntuacion = function($puntuacion) {
            return is_numeric($puntuacion) && $puntuacion >= 1 && $puntuacion <= 10;
        };
    
        if (!$validarPuntuacion($pedido->puntuacion_mesa) || 
            !$validarPuntuacion($pedido->puntuacion_restaurante) || 
            !$validarPuntuacion($pedido->puntuacion_mozo)) {
            $response->getBody()->write(json_encode(array("error" => "Las puntuaciones deben ser números entre 1 y 10")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        // Asignación de datos
        $codigo_mesa = $pedido->codigo_mesa;
        $codigo_pedido = $pedido->codigo_pedido;
        $puntuacion_mesa = $pedido->puntuacion_mesa;
        $puntuacion_restaurante = $pedido->puntuacion_restaurante;
        $puntuacion_mozo = $pedido->puntuacion_mozo;
        $descripcion = $pedido->descripcion; // Hasta acá es obligatorio
    
        // Opcionales
        $puntuacion_cocinero = isset($pedido->puntuacion_cocinero) ? $pedido->puntuacion_cocinero : null;
        $puntuacion_bartender = isset($pedido->puntuacion_bartender) ? $pedido->puntuacion_bartender : null;
        $puntuacion_cervecero = isset($pedido->puntuacion_cervecero) ? $pedido->puntuacion_cervecero : null;
    
        if($puntuacion_cocinero == null && $puntuacion_bartender == null && $puntuacion_cervecero == null) {
            $response->getBody()->write(json_encode(array("error" => "Debe puntuar al menos a un empleado")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if($puntuacion_cocinero != null && !$validarPuntuacion($puntuacion_cocinero) || 
            $puntuacion_bartender != null && !$validarPuntuacion($puntuacion_bartender) || 
            $puntuacion_cervecero != null && !$validarPuntuacion($puntuacion_cervecero)) {
            $response->getBody()->write(json_encode(array("error" => "Las puntuaciones deben ser números entre 1 y 10")));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        try {
            $result = Pedido::RealizarEncuesta($codigo_mesa, $codigo_pedido, $puntuacion_mesa, $puntuacion_restaurante, $puntuacion_mozo, $puntuacion_cocinero, $puntuacion_bartender, $puntuacion_cervecero, $descripcion);
            $payload = json_encode(array("mensaje" => "Encuesta realizada correctamente"));
            $response = $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response = $response->withStatus(400);
        } finally {
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }
    }


    //lista cantidad de operaciones por sector (cocina, barra, cerveceria)
    public static function CantidadOperacionesPorSector($request, $response, $args) {
        $lista = Pedido::ListaOperacionesPorSector();

        $response->getBody()->write(json_encode(array("Lista operaciones por sector" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ListOperacionesSectorConEmpleados($request, $response, $args) {
        $lista = Pedido::ListaOperacionesSectorConEmpleados();

        $response->getBody()->write(json_encode(array("Lista operaciones por sector con empleados" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ListarOperacionesPorEmpleado($request, $response, $args) {
        $lista = Pedido::ListaOperacionesPorEmpleado();

        $response->getBody()->write(json_encode(array("Lista operaciones por empleado" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    
    public static function MostrarProductoMasVendido($request, $response, $args) {
        $producto = Pedido::ProductoMasMenosVendido("mas");

        $response->getBody()->write(json_encode(array("Producto mas vendido" => $producto)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function MostrarProductoMenosVendido($request, $response, $args) {
        $producto = Pedido::ProductoMasMenosVendido("menos");

        $response->getBody()->write(json_encode(array("Producto menos vendido" => $producto)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }


    public static function MostrarPedidosMalDeTiempo($request, $response, $args) {
        $lista = Pedido::ListaPedidosConMalTiempo();

        $response->getBody()->write(json_encode(array("Lista pedidos con mal tiempo" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    //PedidosCancelados
    public static function MostrarPedidosCancelados($request, $response, $args) {
        $lista = Pedido::ListaPedidosCancelados();

        $response->getBody()->write(json_encode(array("Lista pedidos cancelados" => $lista)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }


    public static function DescargarPDFPedidos($request, $response, $args) {
        $request = $request->getQueryParams();
        $flagLogo = isset($request['logo']) ? true : false;
        // // Ruta del logo
        // $rutaLogo = __DIR__ . '/../models/logo/logo.png';
    
        // Crear nuevo documento PDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
    
        // Agregar el logo si la bandera es verdadera usando el método MostrarLogo
        if ($flagLogo) {
            self::MostrarLogo($pdf);
        }
    
        // Generar el contenido del PDF
        $listaHtml = Pedido::GenerarHtmlDePedidos($pdf);
        $pdf->writeHTML($listaHtml, true, false, true, false, '');
    
        // Obtener la lista de pedidos
        $listaPedidos = BaseDeDatos::ListarPedidos();
        $rutaBaseImagenes = __DIR__ . '/../FotosMesasPedidos/';
        
        // Inicializar la posición Y para las imágenes
        $posicionY = $pdf->GetY() + 10;
    
        $pdf->Cell(0, 10, "IMAGENES:", 0, 1, 'L');
    
        foreach ($listaPedidos as $pedido) {
            // Agregar el ID del pedido encima de la imagen
            $pdf->SetY($posicionY);
            $pdf->Cell(0, 10, "ID: " . htmlspecialchars($pedido['id']), 0, 1, 'L');
        
            $rutaImagen = $rutaBaseImagenes . $pedido['nombre_foto'];
            if (file_exists($rutaImagen)) {
                $tipoImagen = pathinfo($rutaImagen, PATHINFO_EXTENSION);
                $pdf->Image($rutaImagen, 15, $pdf->GetY() + 2, 80, 50, $tipoImagen, '', '', true, 150, '', false, false, 1, false, false, false);
                // Ajustar la posición Y para la siguiente imagen
                $posicionY = $pdf->GetY() + 60; // Suma de la altura de la imagen más un margen
            }
        }
        
        // Salida del PDF
        $pdfOutput = $pdf->Output('pedidos.pdf', 'S');
        
        $response = $response->withHeader('Content-Type', 'application/pdf')
                             ->withHeader('Content-Disposition', 'attachment; filename="pedidos.pdf"');
        
        $response->getBody()->write($pdfOutput);
        
        return $response;
    }
    
    

    public static function EstadisticaEstados($request, $response, $args) {
        $estadistica = Pedido::EstadisticaEstadosPedidosPor30Dias();

        $response->getBody()->write(json_encode(array("estadistica estados pedidos" => $estadistica)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function EstadisticaVentas($request, $response, $args) {
        $estadistica = Pedido::EstadisticaVentasPor30Dias();

        $response->getBody()->write(json_encode(array("estadistica ventas" => $estadistica)));
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    
}






?>