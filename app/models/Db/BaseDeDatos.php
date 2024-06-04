<?php

class BaseDeDatos {
    private static $instance;
    private $connection;
    
    private function __construct() {
        // Realizar la conexión a la base de datos
        $this->connection = new PDO('mysql:host=localhost;dbname=db_restaurante;charset=utf8', 'root', '');
        // Configurar PDO para que arroje excepciones en caso de error
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

    public static function AgregarEmpleado($nombre, $apellido, $rol, $estado) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido, rol, estado) VALUES (:nombre, :apellido, :rol, :estado)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
    }

    public static function ListarEmpleados() {
        $db = self::getInstance()->getConnection(); // Obtiene la conexión a la base de datos
        //$stmt es un objeto PDOStatement que representa una sentencia SQL preparada
        $stmt = $db->query('SELECT * FROM empleados');
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve un array asociativo
    }

    // tabla socios
    public static function AgregarSocio($nombre, $apellido) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO socios (nombre, apellido) VALUES (:nombre, :apellido)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->execute();
    }

    public static function ListarSocios() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM socios");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // tabla productos
    public static function AgregarProducto($nombre, $tipo, $cantidad) {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO productos (nombre, tipo, cantidad) VALUES (:nombre, :tipo, :cantidad)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':cantidad', $cantidad);
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

    public static function AgregarPedido($codigoAlfanumerico, $nombreCliente, $estado, $tiempoEstimado, $producto_id, $cantidadProducto) {

        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO pedidos (codigoAlfanumerico, nombreCliente, estado, tiempoEstimado, producto_id, cantidadProducto) VALUES (:codigoAlfanumerico, :nombreCliente, :estado, :tiempoEstimado, :producto_id, :cantidadProducto)");
        $stmt->bindParam(':codigoAlfanumerico', $codigoAlfanumerico);
        $stmt->bindParam(':nombreCliente', $nombreCliente);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':tiempoEstimado', $tiempoEstimado);
        $stmt->bindParam(':producto_id', $producto_id);
        $stmt->bindParam(':cantidadProducto', $cantidadProducto);
        $stmt->execute();
    }

    public static function ListarPedidos() {
        $db = self::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM pedidos");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}






?>