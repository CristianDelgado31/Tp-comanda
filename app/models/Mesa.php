<?php

class Mesa {
    public $codigoIdentificacion;
    public $estado; // ocupada o libre

    public function __construct($codigoIdentificacion) {
        $this->codigoIdentificacion = $codigoIdentificacion;
    }

}




?>