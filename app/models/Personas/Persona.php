<?php

require_once 'Db/BaseDeDatos.php';

class Persona {
    public $id;
    public $nombre;
    public $apellido;
    public $rol;
    
    public function __construct($nombre, $apellido, $rol) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->rol = $rol;
    }

    public static function MostrarLista() {
        $listaUsuarios = BaseDeDatos::ListarUsuarios();
    
        $listaRetorno = array();
    
        foreach ($listaUsuarios as $usuario) {
            if ($usuario['rol'] != "socio") {
                $empleado = new Empleado($usuario['nombre'], $usuario['apellido'], $usuario['rol'], $usuario['estado']);
                $empleado->id = $usuario['id'];
                array_push($listaRetorno, $empleado);
            } else {
                $socio = new Socio($usuario['nombre'], $usuario['apellido'], $usuario['rol'], $usuario['estado']);
                $socio->id = $usuario['id'];
                array_push($listaRetorno, $socio);
            }
        }
    
        return $listaRetorno; // Devuelve un array de objetos
    
    }
}






?>