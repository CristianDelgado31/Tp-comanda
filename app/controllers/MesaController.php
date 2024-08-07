<?php
require_once 'BaseController.php';
require_once 'models/Mesa.php';
// use TCPDF;

class MesaController extends BaseController {

    public static function AgregarMesa($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo
        
        if(!isset($body['codigoIdentificacion']) || !isset($body['estado'])){
            $payload = json_encode(array("mensaje" => "Faltan datos"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        // Si es true la mesa ya existe
        if(Mesa::VerificarMesa($body['codigoIdentificacion'])){
            $payload = json_encode(array("mensaje" => "La mesa ya existe"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        if(strlen($body['codigoIdentificacion']) != 5){
            $payload = json_encode(array("mensaje" => "El codigoIdentificacion debe tener 5 caracteres"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $mesa = new Mesa($body['codigoIdentificacion'], $body['estado']);
        $mesa->AgregarMesa();

        $payload = json_encode(array("mensaje" => "Mesa agregada con exito"));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(201);

    }

    public static function MostrarLista($request, $response, $args){
        $lista = Mesa::MostrarLista();
        //payload es un array asociativo
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function EliminarMesa($request, $response, $args){
        $id = $args['id'];
        
        if(!is_numeric($id) || $id <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser numerico y mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $result = Mesa::VerificarMesaPorId($id);
        if(!$result){ // Si es false la mesa no existe
            $payload = json_encode(array("mensaje" => "La mesa no existe"));
            $response->withStatus(400);
        }
        else {
            $resultadoEliminar = Mesa::EliminarMesa($result);

            if(!$resultadoEliminar){
                $payload = json_encode(array("mensaje" => "La mesa no esta libre para eliminar"));
                $response->withStatus(400);
            }
            else {
                $payload = json_encode(array("mensaje" => "Mesa eliminada con exito"));
            }
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }

    public static function ModificarMesa($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo

        if(!isset($body['id']) || !isset($body['codigoIdentificacion'])){
            $payload = json_encode(array("mensaje" => "Faltan datos para modificar la mesa"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }
        $id = $body['id'];

        if(!is_numeric($id) || $id <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser numerico y mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $codigoIdentificacion = $body['codigoIdentificacion'];

        try {
            Mesa::ModificarMesa($id, $codigoIdentificacion);
            // si no se lanza una excepcion, la mesa se modifico con exito
            $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
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


    public static function ListaMesasEnCSV($request, $response, $args){
        $lista = Mesa::MostrarLista();
        $csv = "";
        foreach($lista as $mesa){
            $csv .= $mesa->id . "," . $mesa->codigoIdentificacion . "," . $mesa->estado . "\n";
        }

        $response->getBody()->write($csv);
        return $response
          ->withHeader('Content-Type', 'text/csv')
          ->withHeader('Content-Disposition', 'attachment; filename="mesas.csv"');
    }

    public static function ImportarMesasDesdeCSV($request, $response, $args){
        $archivo = $_FILES['mesas'];
        $nombreArchivo = $archivo['name'];
        $tipoArchivo = $archivo['type'];
        $tamanioArchivo = $archivo['size'];
        $temporalArchivo = $archivo['tmp_name'];

        if($tipoArchivo != "text/csv"){
            $payload = json_encode(array("mensaje" => "El archivo debe ser de tipo csv"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $lineas = file($temporalArchivo);
        $errores = array();
        $exitos = array();
        foreach($lineas as $linea){
            $datos = explode(",", $linea); // datos es un array
            $codigoIdentificacion = $datos[0];
            
            if(count($datos) != 2){
                $errores[] = "La linea " . $linea . " no tiene el formato correcto";
                continue;
            }
            $estado = $datos[1];
            

            if(strlen($codigoIdentificacion) != 5){ 
                $errores[] = "El codigo de identificacion " . $codigoIdentificacion . " debe tener 5 caracteres";
                continue;
            }

            if(Mesa::VerificarMesa($codigoIdentificacion)){
                $errores[] = "La mesa con codigo de identificacion " . $codigoIdentificacion . " ya existe";
            }
            else {
                $mesa = new Mesa($codigoIdentificacion, "libre");
                $mesa->AgregarMesa();
                $exitos[] = "Mesa con codigo de identificacion " . $codigoIdentificacion . " agregada con exito";
            }
        }

        $payload = json_encode(array("errores" => $errores, "exitos" => $exitos));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json')
          ->withStatus(200);
    }

    public static function MostrarMesaMasUsada($request, $response, $args){
        // $mesa = Mesa::MostrarLaMesaMasUsada();
        $mesa = Mesa::MostrarLaMesaUsada("mas");
        $payload = json_encode(array("mesaMasUsada" => $mesa));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public static function MostrarMesaMenosUsada($request, $response, $args){
        // $mesa = Mesa::MostrarLaMesaMenosUsada();
        $mesa = Mesa::MostrarLaMesaUsada("menos");
        $payload = json_encode(array("mesaMenosUsada" => $mesa));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function MostrarMesaQueMasFacturo($request, $response, $args){
        $mesa = Mesa::MostrarMesaQueFacturo("mas");
        $payload = json_encode(array("mesaQueMasFacturo" => $mesa));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function MostrarMesaQueMenosFacturo($request, $response, $args){
        $mesa = Mesa::MostrarMesaQueFacturo("menos");
        $payload = json_encode(array("mesaQueMenosFacturo" => $mesa));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function MostrarMejoresComentarios($request, $response, $args){
        $mesa = Mesa::MostrarMejoresComentarios();
        $payload = json_encode(array("mejoresComentarios" => $mesa));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function MostrarPeoresComentarios($request, $response, $args){
        $mesa = Mesa::MostrarPeoresComentarios();
        $payload = json_encode(array("peoresComentarios" => $mesa));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    //Lo que facturó entre dos fechas dadas.
    public static function MostrarFacturacionEntreFechas($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo
        
        if(!isset($body['fechaInicio']) || !isset($body['fechaFin'])){
            $payload = json_encode(array("mensaje" => "Faltan datos"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $fechaInicio = $body['fechaInicio'];
        $fechaFin = $body['fechaFin'];

        $mesa = Mesa::MostrarFacturacionEntreFechas($fechaInicio, $fechaFin);
        $payload = json_encode(array("facturacionEntreFechas" => $mesa));
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function DescargarPDFMesas($request, $response, $args){
        $request = $request->getQueryParams();
        $flagLogo = isset($request['logo']) ? true : false;

        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        if ($flagLogo) {
            self::MostrarLogo($pdf);
        }

        $html = Mesa::GenerarHtmlDeMesas();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdfOutput = $pdf->Output('mesas.pdf', 'S');

        $response = $response->withHeader('Content-Type', 'application/pdf')
                             ->withHeader('Content-Disposition', 'attachment; filename="mesas.pdf"');

        $response->getBody()->write($pdfOutput);

        return $response;
    }

    public static function ModificarEstadoMesa($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo
        
        if(!isset($body['id']) || !isset($body['estado'])){
            $payload = json_encode(array("mensaje" => "Faltan datos"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $id = $body['id'];
        $estado = $body['estado'];

        if(!is_numeric($id) || $id <= 0){
            $payload = json_encode(array("mensaje" => "El id debe ser numerico y mayor a 0"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }


        try {
            Mesa::ModificarEstado($id, $estado);
            // si no se lanza una excepcion, la mesa se modifico con exito
            $payload = json_encode(array("mensaje" => "Estado de la mesa modificado con exito"));
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

    public static function GuardarFotoMesaPedido($request, $response, $args){
        $body = $request->getParsedBody(); // devuelve un array asociativo
        
        if(!isset($body['codigoMesa']) || !isset($body['codigoPedido']) || !isset($_FILES['foto'])){
            $payload = json_encode(array("mensaje" => "Faltan datos"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        $codigoMesa = $body['codigoMesa'];
        $codigoPedido = $body['codigoPedido'];
        $foto = $_FILES['foto'];

        if($foto['type'] != "image/jpeg" && $foto['type'] != "image/png"){
            $payload = json_encode(array("mensaje" => "El archivo debe ser de tipo jpg o png"));
            $response->getBody()->write($payload);
            return $response
              ->withHeader('Content-Type', 'application/json')
              ->withStatus(400);
        }

        try {
            Mesa::GuardarFoto($codigoMesa, $codigoPedido, $foto);
            // si no se lanza una excepcion, la foto se guardo con exito
            $payload = json_encode(array("mensaje" => "Foto guardada con exito"));
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
}






?>