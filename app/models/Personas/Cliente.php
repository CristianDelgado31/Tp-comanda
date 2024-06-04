<?php
require_once 'Persona.php';

class Cliente extends Persona {
    public $codigoPedido;
    public $codigoMesa;
    
    public function __construct($nombre, $apellido, $codigoPedido, $codigoMesa) {
        parent::__construct($nombre, $apellido);
        $this->codigoPedido = $codigoPedido;
        $this->codigoMesa = $codigoMesa;
    }
}






?>