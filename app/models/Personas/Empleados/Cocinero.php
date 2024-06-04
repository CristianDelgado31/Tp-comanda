<?php
require_once 'Empleado.php';

class Cocinero extends Empleado {
    
    public function __construct($nombre, $apellido, $estado) {
        parent::__construct($nombre, $apellido, $estado);
    }


    public function cocinar($comida) {
        echo "Cocinando " . $comida . "\n";
    }
}





?>