<?php
require_once 'models/Producto.php';
// use TCPDF;

class ProductoController {

    public static function AgregarProducto($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo
        
        if(!isset($body['nombre']) || !isset($body['tipo']) || !isset($body['precio'])){
            $payload = json_encode(array("mensaje" => "Faltan datos"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        if(Producto::VerificarProducto($body['nombre'])){
            $payload = json_encode(array("mensaje" => "El producto ya existe"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $producto = new Producto($body['nombre'], $body['tipo'], $body['precio']);
        $producto->AgregarProducto();

        $payload = json_encode(array("mensaje" => "Producto agregado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(201);

    }

    public static function MostrarLista($request, $response, $args){
        $lista = Producto::MostrarLista();
        //payload es un array asociativo
        $payload = json_encode(array("listaProductos" => $lista)); // json_encode — Retorna la representación JSON del valor dado

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ModificarProducto($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo

        if(!isset($body['id']) || !isset($body['nombre']) || !isset($body['tipo']) || !isset($body['precio'])){
            $payload = json_encode(array("mensaje" => "Faltan datos (id, nombre, tipo, precio)"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        if(!is_numeric($body['id']) || $body['id'] <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser un numero mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        if(!is_numeric($body['precio']) || $body['precio'] <= 0){
            $payload = json_encode(array("mensaje" => "El precio debe ser un numero mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $producto = new Producto($body['nombre'], $body['tipo'], $body['precio']);
        $producto->id = $body['id'];

        try {
            $producto->ModificarProducto();
            $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
            $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("mensaje" => $e->getMessage()));
            $response = $response->withStatus(400);
        }
        finally {
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

    }

    public static function EliminarProducto($request, $response, $args){
        $id = $args['id'];
        if(!is_numeric($id) || $id <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser un numero mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        try {
            $result = Producto::EliminarProducto($id);
            $payload = json_encode(array("mensaje" => "Producto eliminado con exito"));
            $response->withStatus(200);
        } catch (Exception $e) {
            $payload = json_encode(array("mensaje" => $e->getMessage()));
            $response = $response->withStatus(400);
        }
        finally {
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

    }

    public static function ExportarListaEnCSV($request, $response, $args){
        $lista = Producto::MostrarLista();
        $csv = "";

        foreach($lista as $producto){
            $csv .= $producto->id . "," . $producto->nombre . "," . $producto->tipo . "," . $producto->precio . "," . $producto->fecha_baja . "\n";
        }

        $response->getBody()->write($csv);
        return $response
          ->withHeader('Content-Type', 'text/csv')
          ->withHeader('Content-Disposition', 'attachment; filename="productos.csv"');
    }

    public static function ImportarProductosDesdeCSV($request, $response, $args){
        $archivo = $_FILES['productos'];
        $csv = file_get_contents($archivo['tmp_name']);
        $lineas = explode("\n", $csv);
        $productos = array();
        $arrExito = array();
        $arrError = array();

        foreach($lineas as $linea){
            $producto = explode(",", $linea);
            if(count($producto) == 3){
                $producto = new Producto($producto[0], $producto[1], $producto[2]);
                $nombre = $producto->nombre;

                if(!Producto::VerificarProducto($nombre)){
                    $producto->AgregarProducto();
                    $arrExito[] = "El producto " . $nombre . " fue importado con exito";
                } else{
                    $arrError[] = "El producto " . $nombre . " ya existe";
                }
            }
        }

        $payload = json_encode(array("exitos" => $arrExito, "errores" => $arrError));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(201);
    }

    public static function DescargarPDFProductos($request, $response, $args){
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $lista = Producto::GenerarHtmlDeProductos();
        $pdf->writeHTML($lista, true, false, true, false, '');

        $pdfOutput = $pdf->Output('productos.pdf', 'S');

        $response = $response->withHeader('Content-Type', 'application/pdf')
                             ->withHeader('Content-Disposition', 'attachment; filename="productos.pdf"');

        $response->getBody()->write($pdfOutput);

        return $response;
    }
    
}





?>