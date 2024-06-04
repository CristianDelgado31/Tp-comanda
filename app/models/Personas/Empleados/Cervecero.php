<?php
require_once 'Empleado.php';

class Cervecero extends Empleado {
    
    public function __construct($nombre, $apellido, $estado) {
        parent::__construct($nombre, $apellido, $estado);
    }
}




?>