<?php

class BaseDeDatos {
    private static $instance;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=db_restaurante;charset=utf8';
            $username = 'root';
            $password = '';
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            );
    
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            echo 'Error de conexión: ' . $e->getMessage();
        }
    }
    

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new BaseDeDatos();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public static function AgregarUsuario($nombre, $apellido, $rol, $email, $contrasenia, $estado = NULL) { // $estado es opcional
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido, rol, estado, email, contrasenia) VALUES (:nombre, :apellido, :rol, :estado, :email, :contrasenia)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contrasenia', $contrasenia);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
    }

    public static function ListarUsuarios() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM empleados");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array de arrays asociativos
    }

    // tabla productos
    public static function AgregarProducto($nombre, $tipo, $precio) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO productos (nombre, tipo, precio) VALUES (:nombre, :tipo, :precio)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':precio', $precio);
        $stmt->execute();
    }

    public static function ListarProductos() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM productos");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function AgregarMesa($codigoIdentificacion, $estado) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO mesas (codigoIdentificacion, estado) VALUES (:codigoIdentificacion, :estado)");
        $stmt->bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
    }

    public static function ListarMesas() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM mesas");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function AgregarPedido($codigoAlfanumerico, $nombreCliente, $codigoMesa, $estado, $precioFinal) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO pedidos (codigoAlfanumerico, nombreCliente, codigoMesa, estado, precioFinal) VALUES (:codigoAlfanumerico, :nombreCliente, :codigoMesa, :estado, :precioFinal)");
        $stmt->bindParam(':codigoAlfanumerico', $codigoAlfanumerico);
        $stmt->bindParam(':nombreCliente', $nombreCliente);
        $stmt->bindParam(':codigoMesa', $codigoMesa);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':precioFinal', $precioFinal);
        $stmt->execute();
    }

    public static function AgregarPedidoProducto($codigoPedido, $idProducto, $estado) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO pedido_producto (codigo_pedido, id_producto, estado) VALUES (:codigoPedido, :idProducto, :estado)");
        $stmt->bindParam(':codigoPedido', $codigoPedido);
        $stmt->bindParam(':idProducto', $idProducto);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
    }

    public static function ListarPedidos() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM pedidos");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function ListarPedidosProductos() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM pedido_producto");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// --------------------------------------------------------------------------------------------------------------------------------------------

    public static function ActualizarPedidoProducto($pedido) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE pedido_producto SET estado = :estado, id_usuario = :id_usuario, tiempo_producto = :tiempo_producto WHERE id = :id");
        $stmt->bindParam(':estado', $pedido['estado']);
        $stmt->bindParam(':id_usuario', $pedido['id_usuario']);
        $stmt->bindParam(':tiempo_producto', $pedido['tiempo_producto']);
        $stmt->bindParam(':id', $pedido['id']);
        $stmt->execute();

        return $pedido;
    }

    public static function ActualizarPedido($pedido) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE pedidos SET estado = :estado, tiempoEstimado = :tiempoEstimado WHERE id = :id");
        $stmt->bindParam(':estado', $pedido['estado']);
        $stmt->bindParam(':tiempoEstimado', $pedido['tiempoEstimado']);
        $stmt->bindParam(':id', $pedido['id']);
        $stmt->execute();
    }

    public static function ActualizarEstadoUsuario($usuario) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE empleados SET estado = :estado WHERE id = :id");
        $stmt->bindParam(':estado', $usuario['estado']);
        $stmt->bindParam(':id', $usuario['id']);
        $stmt->execute();
    }

    public static function ModificarEstadoMesa($codigoIdentificacion, $estado) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE mesas SET estado = :estado WHERE codigoIdentificacion = :codigoIdentificacion");
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $stmt->execute();
    }

// --------------------------------------------------------------------------------------------------------------------------------------------
    //EliminarUsuario se llamaba antes
    public static function ModificarEstadoUsuario($usuario) { //$usuario es un array asociativo
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE empleados SET estado = :estado, fecha_baja = :fechaBaja WHERE id = :id");
        $stmt->bindParam(':id', $usuario['id']);
        $stmt->bindParam(':estado', $usuario['estado']);
        $stmt->bindParam(':fechaBaja', $usuario['fechaBaja']);
        $stmt->execute();
    }

    public static function ModificarAtributosUsuario($id, $nombre, $apellido, $rol, $email, $contrasenia, $estado) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        if($contrasenia == NULL) {
            $stmt = $conn->prepare("UPDATE empleados SET nombre = :nombre, apellido = :apellido, rol = :rol, email = :email, estado = :estado WHERE id = :id");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id);
        } else {
            $stmt = $conn->prepare("UPDATE empleados SET nombre = :nombre, apellido = :apellido, rol = :rol, email = :email, contrasenia = :contrasenia, estado = :estado WHERE id = :id");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':contrasenia', $contrasenia);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id);
        }
        $stmt->execute();
    }

    public static function ModificarProducto($id, $nombre, $tipo, $precio) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE productos SET nombre = :nombre, tipo = :tipo, precio = :precio WHERE id = :id");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public static function EliminarProducto($id, $fecha_baja) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE productos SET fecha_baja = :fecha_baja WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha_baja', $fecha_baja);
        $stmt->execute();
    }

    public static function EliminarMesa($id, $fecha_baja) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE mesas SET fecha_baja = :fecha_baja WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha_baja', $fecha_baja);
        $stmt->execute();
    }

    public static function EliminarPedido($id, $fecha_baja) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE pedidos SET fecha_baja = :fecha_baja WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':fecha_baja', $fecha_baja);
        $stmt->execute();
    }

    public static function ModificarMesa($id, $codigoIdentificacion) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE mesas SET codigoIdentificacion = :codigoIdentificacion WHERE id = :id");
        $stmt->bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public static function ModificarCodigoPedidoProducto($id, $codigoAlfanumerico, $id_producto = NULL){
        $db = self::getInstance();
        $conn = $db->getConnection();
        if($id_producto != NULL) {
            $stmt = $conn->prepare("UPDATE pedido_producto SET codigo_pedido = :codigoAlfanumerico, id_producto = :id_producto WHERE id = :id");
            $stmt->bindParam(':id_producto', $id_producto);
            $stmt->bindParam(':codigoAlfanumerico', $codigoAlfanumerico);
            $stmt->bindParam(':id', $id);
        } else {
            $stmt = $conn->prepare("UPDATE pedido_producto SET codigo_pedido = :codigoAlfanumerico WHERE id = :id");
            $stmt->bindParam(':codigoAlfanumerico', $codigoAlfanumerico);
            $stmt->bindParam(':id', $id);
        }
        $stmt->execute();
    }

    public static function ModificarAtributosPedido($pedido) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE pedidos SET codigoAlfanumerico = :codigoAlfanumerico, nombreCliente = :nombreCliente, codigoMesa = :codigoMesa, tiempoEstimado = :tiempoEstimado WHERE id = :id");
        $stmt->bindParam(':codigoAlfanumerico', $pedido['codigoAlfanumerico']);
        $stmt->bindParam(':nombreCliente', $pedido['nombreCliente']);
        $stmt->bindParam(':codigoMesa', $pedido['codigoMesa']);
        $stmt->bindParam(':tiempoEstimado', $pedido['tiempoEstimado']);
        $stmt->bindParam(':id', $pedido['id']);
        $stmt->execute();
    }

    public static function ModificarPrecioFinalPedido($id, $precioFinal) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE pedidos SET precioFinal = :precioFinal WHERE id = :id");
        $stmt->bindParam(':precioFinal', $precioFinal);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public static function ModificarEstadoPedido($id, $estado) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE pedidos SET estado = :estado WHERE id = :id");
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }


    public static function AgregarEncuesta($codigo_mesa, $codigo_pedido, $puntuacion_mesa, $puntuacion_restaurante, $puntuacion_mozo, 
    $puntuacion_cocinero, $puntuacion_bartender, $puntuacion_cervecero, $descripcion, $fecha) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO encuesta_cliente (codigo_mesa, codigo_pedido, puntuacion_mesa, puntuacion_restaurante, puntuacion_mozo, puntuacion_cocinero, puntuacion_bartender, puntuacion_cervecero, descripcion, fecha) VALUES (:codigo_mesa, :codigo_pedido, :puntuacion_mesa, :puntuacion_restaurante, :puntuacion_mozo, :puntuacion_cocinero, :puntuacion_bartender, :puntuacion_cervecero, :descripcion, :fecha)");
        $stmt->bindParam(':codigo_mesa', $codigo_mesa);
        $stmt->bindParam(':codigo_pedido', $codigo_pedido);
        $stmt->bindParam(':puntuacion_mesa', $puntuacion_mesa);
        $stmt->bindParam(':puntuacion_restaurante', $puntuacion_restaurante);
        $stmt->bindParam(':puntuacion_mozo', $puntuacion_mozo);
        $stmt->bindParam(':puntuacion_cocinero', $puntuacion_cocinero);
        $stmt->bindParam(':puntuacion_bartender', $puntuacion_bartender);
        $stmt->bindParam(':puntuacion_cervecero', $puntuacion_cervecero);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();
    }

    public static function ListarEncuestas() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM encuesta_cliente");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 

    public static function ModificarEncuestaMesa($codigoIdentificacion, $bool) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE mesas SET encuesta_realizada = :bool WHERE codigoIdentificacion = :codigoIdentificacion");
        $stmt->bindParam(':bool', $bool);
        $stmt->bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $stmt->execute();
    }

    public static function AgregarLog($id_usuario, $fecha, $hora) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO logs (id_usuario, fecha, hora) VALUES (:id_usuario, :fecha, :hora)");
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hora', $hora);
        $stmt->execute();
    }

    public static function ActualizarOperacion($id_usuario, $cant_operacion) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE empleados SET cant_operaciones = :cant_operacion WHERE id = :id_usuario");
        $stmt->bindParam(':cant_operacion', $cant_operacion);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
    }


    public static function ModificarHoraEnPedido($id, $tiempo, $clave){
        $db = self::getInstance();
        $conn = $db->getConnection();
        if($clave == "inicio"){
            $stmt = $conn->prepare("UPDATE pedidos SET tiempo_inicio = :tiempo WHERE id = :id");
        } else {
            $stmt = $conn->prepare("UPDATE pedidos SET tiempo_final = :tiempo WHERE id = :id");
        }
        $stmt->bindParam(':tiempo', $tiempo);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
    }

    public static function ModificarCantidadUsoDeMesa($codigoIdentificacion, $cantidad_usos) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE mesas SET cantidad_usos = :cantidad_usos WHERE codigoIdentificacion = :codigoIdentificacion");
        $stmt->bindParam(':cantidad_usos', $cantidad_usos);
        $stmt->bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $stmt->execute();
    }

    public static function ReactivarProducto($id) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE productos SET fecha_baja = NULL WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    public static function ModificarEncuestaEnMesa() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE mesas SET encuesta_realizada = 0");
        $stmt->execute();
    }

    public static function ListarLogsPorFecha() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM logs ORDER BY fecha");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}



?>