<?php
require_once 'Db/BaseDeDatos.php';

class Mesa {
    public $id;
    public $codigoIdentificacion;
    public $estado; // ocupada o libre
    public $fechaBaja;

    public function __construct($codigoIdentificacion, $estado) {
        $this->codigoIdentificacion = $codigoIdentificacion;
        $this->estado = $estado;
    }


    public static function MostrarLista(){
        $lista = BaseDeDatos::ListarMesas();
        $listaRetorno = array();

        foreach ($lista as $mesa) {
            $mesaAux = new Mesa($mesa['codigoIdentificacion'], $mesa['estado']);
            $mesaAux->id = $mesa['id'];
            $mesaAux->fechaBaja = $mesa['fecha_baja'];
            array_push($listaRetorno, $mesaAux); // array_push — Inserta uno o más elementos al final de un array
        }

        return $listaRetorno;
    }

    public function AgregarMesa(){
        BaseDeDatos::AgregarMesa($this->codigoIdentificacion, $this->estado);
    }

    public static function VerificarMesa($codigoMesa){
        $listaMesas = BaseDeDatos::ListarMesas();
        foreach ($listaMesas as $mesa) {
            if ($mesa['codigoIdentificacion'] == $codigoMesa) {
                return true; // La mesa existe
            }
        }
        return false; // La mesa no existe
    }

    public static function VerificarMesaPorId($id){
        $listaMesas = BaseDeDatos::ListarMesas();
        foreach ($listaMesas as $mesa) {
            if ($mesa['id'] == $id && $mesa['fecha_baja'] == null) {
                return $mesa; // La mesa existe y no esta dada de baja, mesa es un array asociativo
                
            }
        }
        return false; // La mesa no existe
    }

    public static function EliminarMesa($mesa){
        if($mesa['estado'] != 'libre'){
            return false; // La mesa no esta libre
        }
        $fechaBaja = date('Y-m-d H:i:s');
        BaseDeDatos::EliminarMesa($mesa['id'], $fechaBaja);
        return true; // La mesa fue eliminada
    }


    public static function ModificarMesa($idMesa, $codigoIdentificacion){
        $listaMesa = BaseDeDatos::ListarMesas();
        $mesaResult = Mesa::VerificarMesaPorId($idMesa);

        if(!$mesaResult){
            return 2; // La mesa no existe
        }

        if($mesaResult['estado'] != 'libre'){
            return 1; // La mesa no esta libre para modificar
        }

        foreach ($listaMesa as $mesa) {
            if ($mesa['codigoIdentificacion'] == $codigoIdentificacion) {
                return 3; // La mesa ya existe
            }
        }

        BaseDeDatos::ModificarMesa($idMesa, $codigoIdentificacion);
        return 0; // La mesa fue modificada
    }
}




?>