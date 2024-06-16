<?php
require_once 'Db/BaseDeDatos.php';

class Mesa {
    public $id;
    public $codigoIdentificacion;
    public $estado; // ocupada o libre

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
}




?>