<?php
require_once 'Db/BaseDeDatos.php';

class Mesa {
    public $id;
    public $codigoIdentificacion;
    public $estado; // ocupada o libre
    public $fechaBaja;

    public function __construct($codigoIdentificacion, $estado) {
        $this->codigoIdentificacion = $codigoIdentificacion;
        $this->estado = $estado;
    }


    public static function MostrarLista(){
        $lista = BaseDeDatos::ListarMesas();
        $listaRetorno = array();

        foreach ($lista as $mesa) {
            $mesaAux = new Mesa($mesa['codigoIdentificacion'], $mesa['estado']);
            $mesaAux->id = $mesa['id'];
            $mesaAux->fechaBaja = $mesa['fecha_baja'];
            array_push($listaRetorno, $mesaAux); // array_push — Inserta uno o más elementos al final de un array
        }

        return $listaRetorno;
    }

    public function AgregarMesa(){
        BaseDeDatos::AgregarMesa($this->codigoIdentificacion, $this->estado);
    }

    public static function VerificarMesa($codigoMesa){
        $listaMesas = BaseDeDatos::ListarMesas();
        foreach ($listaMesas as $mesa) {
            if ($mesa['codigoIdentificacion'] == $codigoMesa) {
                return true; // La mesa existe
            }
        }
        return false; // La mesa no existe
    }

    public static function VerificarMesaPorId($id){
        $listaMesas = BaseDeDatos::ListarMesas();
        foreach ($listaMesas as $mesa) {
            if ($mesa['id'] == $id && $mesa['fecha_baja'] == null) {
                return $mesa; // La mesa existe y no esta dada de baja, mesa es un array asociativo
                
            }
        }
        return false; // La mesa no existe
    }

    public static function EliminarMesa($mesa){
        if($mesa['estado'] != 'libre'){
            return false; // La mesa no esta libre
        }
        $fechaBaja = date('Y-m-d H:i:s');
        BaseDeDatos::EliminarMesa($mesa['id'], $fechaBaja);
        return true; // La mesa fue eliminada
    }


    public static function ModificarMesa($idMesa, $codigoIdentificacion){ //Este codigo es el nuevo codigo de identificacion si se quiere modificar
        $listaMesa = BaseDeDatos::ListarMesas();
        $mesaResult = Mesa::VerificarMesaPorId($idMesa);

        if(!$mesaResult){
            throw new Exception('La mesa no existe');
            // return 2; // La mesa no existe
        }

        if($mesaResult['estado'] != 'libre'){
            throw new Exception('La mesa no esta libre para modificar');
            // return 1; // La mesa no esta libre para modificar
        }

        foreach ($listaMesa as $mesa) {
            if ($mesa['codigoIdentificacion'] == $codigoIdentificacion) {
                throw new Exception('La mesa ya existe');
                // return 3; // La mesa ya existe
            }
        }

        // Si llega a este punto, la mesa puede ser modificada
        BaseDeDatos::ModificarMesa($idMesa, $codigoIdentificacion);
        // return true; // La mesa fue modificada
    }

    public static function MostrarLaMesaUsada($criterio) {
        $lista = BaseDeDatos::ListarMesas();
        $mesaUsada = null;
        $cantidad = $criterio == 'mas' ? -1 : PHP_INT_MAX; // Inicializar basado en el criterio, -1 para 'mas y PHP_INT_MAX para 'menos'
    
        foreach ($lista as $mesa) {
            $cantidadMesa = $mesa['cantidad_usos'];
            
            if (($criterio == 'mas' && $cantidadMesa > $cantidad) || ($criterio == 'menos' && $cantidadMesa < $cantidad)) {
                $cantidad = $cantidadMesa;
                $mesaUsada = $mesa;
            }
        }
    
        return $mesaUsada;
    }

    public static function MostrarMesaQueFacturo($criterio = 'mas') {
        $listaEncuestas = BaseDeDatos::ListarEncuestas();
        $listaPedidos = BaseDeDatos::ListarPedidos();
    
        $mesasFacturacion = []; // Array para almacenar la facturación por mesa
    
        // Calcular la facturación por cada mesa
        foreach ($listaEncuestas as $encuesta) {
            $codigoPedido = $encuesta['codigo_pedido'];
            $pedidoEncontrado = null;
    
            // Buscar el pedido correspondiente a la encuesta
            foreach ($listaPedidos as $pedido) {
                if ($pedido['codigoAlfanumerico'] == $codigoPedido) {
                    $pedidoEncontrado = $pedido;
                    break;
                }
            }
    
            // Si se encontró el pedido, actualizar la facturación de la mesa
            if ($pedidoEncontrado) {
                $mesa = $pedidoEncontrado['codigoMesa'];
                $precioFinal = $pedidoEncontrado['precioFinal'];
    
                if (isset($mesasFacturacion[$mesa])) {
                    $mesasFacturacion[$mesa] += $precioFinal;
                } else {
                    $mesasFacturacion[$mesa] = $precioFinal;
                }
            }
        }
    
        // Encontrar la mesa según el criterio (mas o menos facturado)
        $mesaResultado = null;
        $valorComparativo = $criterio == 'mas' ? 0 : PHP_INT_MAX;
    
        foreach ($mesasFacturacion as $mesa => $facturacion) {
            if (($criterio == 'mas' && $facturacion > $valorComparativo) || ($criterio == 'menos' && $facturacion < $valorComparativo)) {
                $valorComparativo = $facturacion;
                $mesaResultado = $mesa;
            }
        }
    
        return $mesaResultado;
    }
    

    public static function MostrarMejoresComentarios() {
        $listaEncuestas = BaseDeDatos::ListarEncuestas();
        $mejoresComentarios = [];
    
        foreach ($listaEncuestas as $encuesta) {
            // Calculamos el promedio de las puntuaciones relevantes
            $puntuaciones = [
                $encuesta['puntuacion_mesa'] ?? 0,
                $encuesta['puntuacion_restaurante'] ?? 0,
                $encuesta['puntuacion_mozo'] ?? 0,
                $encuesta['puntuacion_cocinero'] ?? 0,
                $encuesta['puntuacion_bartender'] ?? 0,
                $encuesta['puntuacion_cervecero'] ?? 0,
            ];
    
            $puntuaciones = array_filter($puntuaciones, function ($value) {
                return $value > 0; // Filtrar solo puntuaciones válidas (mayores que 0)
            });
    
            if (empty($puntuaciones)) {
                continue; // Si no hay puntuaciones válidas, pasamos a la siguiente encuesta
            }
    
            $sumaPuntajes = array_sum($puntuaciones);
            $contador = count($puntuaciones);
            $puntajePromedio = $sumaPuntajes / $contador;
    
            // Filtramos los comentarios que tienen un puntaje promedio mayor o igual a 7
            if ($puntajePromedio >= 7) {
                $mejoresComentarios[] = $encuesta; // Agregar la encuesta completa a los mejores comentarios
            }
        }
    
        return $mejoresComentarios;
    }

    public static function MostrarPeoresComentarios() {
        $listaEncuestas = BaseDeDatos::ListarEncuestas();
        $peoresComentarios = [];
    
        foreach ($listaEncuestas as $encuesta) {
            // Calculamos el promedio de las puntuaciones relevantes
            $puntuaciones = [
                $encuesta['puntuacion_mesa'] ?? 0,
                $encuesta['puntuacion_restaurante'] ?? 0,
                $encuesta['puntuacion_mozo'] ?? 0,
                $encuesta['puntuacion_cocinero'] ?? 0,
                $encuesta['puntuacion_bartender'] ?? 0,
                $encuesta['puntuacion_cervecero'] ?? 0,
            ];
    
            $puntuaciones = array_filter($puntuaciones, function ($value) {
                return $value > 0; // Filtrar solo puntuaciones válidas (mayores que 0)
            });
    
            if (empty($puntuaciones)) {
                continue; // Si no hay puntuaciones válidas, pasamos a la siguiente encuesta
            }
    
            $sumaPuntajes = array_sum($puntuaciones);
            $contador = count($puntuaciones);
            $puntajePromedio = $sumaPuntajes / $contador;
    
            // Filtramos los comentarios que tienen un puntaje promedio menor a 4
            if ($puntajePromedio < 4) {
                $peoresComentarios[] = $encuesta; // Agregar la encuesta completa a los peores comentarios
            }
        }
    
        return $peoresComentarios;
    }

    public static function MostrarFacturacionEntreFechas($fechaInicio, $fechaFin) {
        $listaEncuestas = BaseDeDatos::ListarEncuestas();
        $listaPedidos = BaseDeDatos::ListarPedidos();
    
        $facturacionTotal = 0;
    
        foreach ($listaEncuestas as $encuesta) {
            $fechaEncuesta = $encuesta['fecha'];
    
            if ($fechaEncuesta >= $fechaInicio && $fechaEncuesta <= $fechaFin) {
                $codigoPedido = $encuesta['codigo_pedido'];
                $pedidoEncontrado = null;
    
                // Buscar el pedido correspondiente a la encuesta
                foreach ($listaPedidos as $pedido) {
                    if ($pedido['codigoAlfanumerico'] == $codigoPedido) {
                        $pedidoEncontrado = $pedido;
                        break;
                    }
                }
    
                // Si se encontró el pedido, sumar la facturación
                if ($pedidoEncontrado) {
                    $precioFinal = $pedidoEncontrado['precioFinal'];
                    $facturacionTotal += $precioFinal;
                }
            }
        }
    
        return $facturacionTotal;
    }

    public static function GenerarHtmlDeMesas() {
        $listaMesas = self::MostrarLista();
    
        // Utilizando heredoc para una mejor legibilidad
        $html = <<<HTML
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th, td {
                border: 1px solid black;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
        </style>
        <table>
            <tr>
                <th>ID</th>
                <th>Código de Identificación</th>
                <th>Estado</th>
                <th>Fecha de Baja</th>
            </tr>
        HTML;
    
        foreach ($listaMesas as $mesa) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($mesa->id) . '</td>';
            $html .= '<td>' . htmlspecialchars($mesa->codigoIdentificacion) . '</td>';
            $html .= '<td>' . htmlspecialchars($mesa->estado) . '</td>';
            $html .= '<td>' . htmlspecialchars($mesa->fechaBaja) . '</td>';
            $html .= '</tr>';
        }
    
        $html .= '</table>';
    
        return $html;
    }
    
    
}




?>