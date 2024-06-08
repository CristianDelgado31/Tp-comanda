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

    public static function AgregarUsuario($nombre, $apellido, $rol, $estado) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido, rol, estado) VALUES (:nombre, :apellido, :rol, :estado)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
    }

    public static function ListarUsuarios() {
        $db = self::getInstance()->getConnection(); // Obtiene la conexión a la base de datos
        //$stmt es un objeto PDOStatement que representa una sentencia SQL preparada
        $stmt = $db->query('SELECT * FROM empleados');
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array asociativo
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


}



?>