<?php
require_once 'models/ILogo.php';

class BaseController implements ILogo
{
    public static function MostrarLogo($pdf) {
        $rutaLogo = __DIR__ . '/../models/logo/logo.png';
        if (file_exists($rutaLogo)) {
            // Puedes ajustar los parámetros de posición y tamaño según sea necesario
            $pdf->Image($rutaLogo, 170, 12, 19, 19, '', '', '', false, 300, '', false, false, 0, false, false, false);
        }
    }
}





?>