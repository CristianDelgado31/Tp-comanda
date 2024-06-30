<?php
require_once 'Persona.php';
// require_once '/home/cristian-delgado/Escritorio/Tp-comanda/app/models/Personas/Persona.php';

class Socio extends Persona {

    public function __construct($nombre, $apellido, $rol, $email, $contrasenia, $estado = "activo") {
        parent::__construct($nombre, $apellido, $rol, $email, $contrasenia, $estado);
    }

}





?>