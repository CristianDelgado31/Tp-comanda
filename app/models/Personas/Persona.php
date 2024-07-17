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
    // public $cantidad_operaciones;
    
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
                // $empleado = new Empleado($usuario['nombre'], $usuario['apellido'], $usuario['rol'], $usuario['email'], "", $usuario['estado']); // original
                $empleado = new Empleado($usuario['nombre'], $usuario['apellido'], $usuario['rol'], $usuario['email'], "", 
                                         $usuario['estado'], $usuario['cant_operaciones']); // testeando

                $empleado->id = $usuario['id'];
                $empleado->fechaBaja = $usuario['fecha_baja'];
                // $empleado->cantidad_operaciones = $usuario['cant_operaciones'];
                array_push($listaRetorno, $empleado);
            } else {
                $socio = new Socio($usuario['nombre'], $usuario['apellido'], $usuario['rol'], $usuario['email'], "", $usuario['estado']);
                $socio->id = $usuario['id'];
                $socio->fechaBaja = $usuario['fecha_baja'];
                // $socio->cantidad_operaciones = $usuario['cantidad_operaciones']; // no deberia tener cantidad de operaciones -> test
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
        $usuarios = BaseDeDatos::ListarUsuarios();
        $html = <<<HTML
        <div style="margin-top: 20px;"> <!-- Agrega un margen superior de 20px -->
        <h1>Lista de usuarios</h1>
        <style>
            table {
                padding: 5px;
                border-collapse: collapse;
                width: 100%;
            }
            th, td {
                border: 1px solid black;
                padding: 5px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
        </style>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Rol</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Fecha de baja</th>
                <th>Cantidad de operaciones</th>
            </tr>
        HTML;
        
        foreach ($usuarios as $usuario) {
            $html .= '<tr>';
            $html .= '<td>' . $usuario['id'] . '</td>';
            $html .= '<td>' . $usuario['nombre'] . '</td>';
            $html .= '<td>' . $usuario['apellido'] . '</td>';
            $html .= '<td>' . $usuario['rol'] . '</td>';
            $html .= '<td>' . $usuario['email'] . '</td>';
            $html .= '<td>' . $usuario['estado'] . '</td>';
            $html .= '<td>' . $usuario['fecha_baja'] . '</td>';

            if($usuario['rol'] != "socio" && $usuario['rol'] != "admin") {
                $html .= '<td>' . $usuario['cant_operaciones'] . '</td>';
            } else {
                $html .= '<td>' . null . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $html .= '</div>';
        return $html;
    }
    
    public static function EstadisticaLogUsuariosPor30Dias() {
        $fechaActual = date("Y-m-d");
        $fechaAnterior = date("Y-m-d", strtotime($fechaActual . "-30 days"));
        $listaLogs = BaseDeDatos::ListarLogsPorFecha($fechaAnterior, $fechaActual);
        $listaUsuarios = self::MostrarLista();
        $estadistica = array();
    
        foreach ($listaUsuarios as $usuario) {
            $estadistica[$usuario->id] = 0;
        }
    
        foreach ($listaLogs as $log) {
            $estadistica[$log['id_usuario']]++;
        }
    
        return $estadistica;
    }
}






?>