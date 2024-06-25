<?php

require_once 'Db/BaseDeDatos.php';

class Persona {
    public $id;
    public $nombre;
    public $apellido;
    public $rol;
    public $fechaBaja;
    public $email;
    public $contrasenia;
    public $estado;
    
    public function __construct($nombre, $apellido, $rol, $email, $contrasenia, $estado = "activo") {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->rol = $rol;
        $this->email = $email;
        $this->contrasenia = $contrasenia;
        $this->estado = $estado;
    }

    public static function MostrarLista() {
        $listaUsuarios = BaseDeDatos::ListarUsuarios();
    
        $listaRetorno = array();
        //tengo que agregar una condicion para que no muestre los usuarios eliminados
        foreach ($listaUsuarios as $usuario) {
            if ($usuario['rol'] != "socio") {
                $empleado = new Empleado($usuario['nombre'], $usuario['apellido'], $usuario['rol'], $usuario['email'], "", $usuario['estado']);
                $empleado->id = $usuario['id'];
                $empleado->fechaBaja = $usuario['fecha_baja'];
                array_push($listaRetorno, $empleado);
            } else {
                $socio = new Socio($usuario['nombre'], $usuario['apellido'], $usuario['rol'], $usuario['email'], "", $usuario['estado']);
                $socio->id = $usuario['id'];
                $socio->fechaBaja = $usuario['fecha_baja'];
                array_push($listaRetorno, $socio);
            }
        }
    
        return $listaRetorno; // Devuelve un array de objetos
    
    }

    public static function VerificarUsuario($email, $contrasenia) {
        $listaUsuarios = BaseDeDatos::ListarUsuarios();
    
        foreach ($listaUsuarios as $usuario) {
            // Verifica si el email y la contraseña coinciden
            if ($usuario['email'] == $email && password_verify($contrasenia, $usuario['contrasenia']) && $usuario['estado'] == "activo" || $usuario['estado'] == "ocupado") {
                $usuario['contrasenia'] = ""; // No se envia la contraseña

                //agrego un log en la base de datos
                $fecha = date("Y-m-d");
                $hora = date("H:i:s");
                BaseDeDatos::AgregarLog($usuario['id'], $fecha, $hora);

                return $usuario; // Devuelve un array asociativo
            }
        }
    
        return null; // Devuelve null si no se encontro el usuario
    }

    public static function EliminarUsuario($id) {
        $listaUsuarios = self::MostrarLista();

        foreach ($listaUsuarios as $usuario) {
            if ($usuario->id == $id) {

                if($usuario->estado == "inactivo") {
                    return false; // Si el usuario ya esta inactivo, no se puede eliminar
                }

                $fechaBaja = date("Y-m-d");
                $estado = "inactivo";
                $arrUsuario = array("id" => $id, "fechaBaja" => $fechaBaja, "estado" => $estado);
                BaseDeDatos::EliminarUsuario($arrUsuario);
                return true;
            }
        }

        return false;
    }


    public function AgregarUsuario(){
        $nombre = $this->nombre;
        $apellido = $this->apellido;
        $rol = $this->rol;
        $estado = $this->estado;
        $email = $this->email;
        $contrasenia = $this->contrasenia;
        BaseDeDatos::AgregarUsuario($nombre, $apellido, $rol, $email, $contrasenia, $estado);
    }


    public function ModificarUsuario(){
        $id = $this->id;
        $nombre = $this->nombre;
        $apellido = $this->apellido;
        $rol = $this->rol; // no se cambiará el rol
        $estado = $this->estado;
        $email = $this->email;
        $contrasenia = $this->contrasenia;
        BaseDeDatos::ModificarAtributosUsuario($id, $nombre, $apellido, $rol, $email, $contrasenia, $estado);
    }

    public static function ModificarEstado($id, $estado) {
        $usuario = BaseDeDatos::ActualizarEstadoUsuario(array("id" => $id, "estado" => $estado));
    }
}






?>