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
    
    // private function __construct() {
    //     try {
    //         // Usar el socket en lugar de host y puerto
    //         $dsn = 'mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname=db_restaurante;charset=utf8';
    //         $this->connection = new PDO($dsn, 'root', '');
    //         $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     } catch (PDOException $e) {
    //         echo 'Error de conexión: ' . $e->getMessage();
    //     }
    // }
    
    

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

    public static function EliminarUsuario($usuario) { //$usuario es un array asociativo
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE empleados SET estado = :estado, fecha_baja = :fechaBaja WHERE id = :id");
        $stmt->bindParam(':id', $usuario['id']);
        $stmt->bindParam(':estado', $usuario['estado']);
        $stmt->bindParam(':fechaBaja', $usuario['fechaBaja']);
        $stmt->execute();
    }

}



?>