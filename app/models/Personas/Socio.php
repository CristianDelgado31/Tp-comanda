<?php
require_once 'Persona.php';

class Socio extends Persona {
    public static $cantidadSocios = 0;
    private static $maxSocios = 3;

    public function __construct($nombre, $apellido) {
        if (self::$cantidadSocios >= self::$maxSocios) {
            throw new Exception("No se pueden registrar más de " . self::$maxSocios . " socios.");
        }

        parent::__construct($nombre, $apellido);
        self::$cantidadSocios++;
        
        echo "Ya hay " . self::$cantidadSocios . " socios registrados.\n";
        
    }

}

// Ejemplo de uso
// try {
//     $socio1 = new Socio("Juan", "Perez");
//     $socio2 = new Socio("Ana", "Gomez");
//     $socio3 = new Socio("Luis", "Martinez");
//     // Intentar crear un cuarto socio lanzará una excepción
//     $socio4 = new Socio("Maria", "Lopez");
// } catch (Exception $e) {
//     echo "Error: " . $e->getMessage();
// }


?>