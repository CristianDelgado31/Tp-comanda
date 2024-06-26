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
            if ($usuario['email'] == $email && password_verify($contrasenia, $usuario['contrasenia'])) { //&& $usuario['estado'] == "activo" || $usuario['estado'] == "ocupado"
                if($usuario['estado'] == "eliminado") {
                    throw new Exception("El usuario se encuentra eliminado");
                } else if($usuario['estado'] == "suspendido") {
                    throw new Exception("El usuario se encuentra suspendido");
                }

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

    public static function CambiarEstadoUsuario($id, $estado) {
        $listaUsuarios = self::MostrarLista();
        $idFlag = false;

        foreach ($listaUsuarios as $usuario) {
            if ($usuario->id == $id) {    
                if($estado == "activo") {
                    if($usuario->estado == "activo") {
                        throw new Exception("El usuario ya se encuentra activo");
                    }
                    self::ActivarUsuario($id);
                } else if($estado == "suspendido") {
                    if($usuario->estado == "suspendido") {
                        throw new Exception("El usuario ya se encuentra suspendido");
                    }
                    self::SuspenderUsuario($id);
                } else if($estado == "eliminado") {
                    if($usuario->estado == "eliminado") {
                        throw new Exception("El usuario ya se encuentra eliminado");
                    }
                    self::EliminarUsuario($id);
                } else {
                    throw new Exception("El estado no es valido");
                }
                $idFlag = true;
                break;
            }
        }

        if(!$idFlag) {
            throw new Exception("El id usuario no existe");
        }
    }

    public static function EliminarUsuario($id) {
        $estado = "eliminado";
        $fechaBaja = date("Y-m-d");
        BaseDeDatos::ModificarEstadoUsuario(array("id" => $id, "fechaBaja" => $fechaBaja, "estado" => $estado));
    }

    public static function SuspenderUsuario($id) {
        $estado = "suspendido";
        BaseDeDatos::ModificarEstadoUsuario(array("id" => $id, "fechaBaja" => null, "estado" => $estado));
    }

    public static function ActivarUsuario($id) {
        $estado = "activo";
        BaseDeDatos::ModificarEstadoUsuario(array("id" => $id, "fechaBaja" => null, "estado" => $estado));
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


    public static function GenerarHtmlDeUsuarios() {
        $usuarios = self::MostrarLista();
        $html = '<h1>Lista de Usuarios</h1>';
        $html .= '<table border="1" width="100%">';
        $html .= '<tr><th>Nombre</th><th>Apellido</th><th>Rol</th><th>Email</th><th>Estado</th></tr>';
        
        foreach ($usuarios as $usuario) {
            $html .= '<tr>';
            $html .= '<td>' . $usuario->nombre . '</td>';
            $html .= '<td>' . $usuario->apellido . '</td>';
            $html .= '<td>' . $usuario->rol . '</td>';
            $html .= '<td>' . $usuario->email . '</td>';
            $html .= '<td>' . $usuario->estado . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        return $html;
    }
    
}






?>