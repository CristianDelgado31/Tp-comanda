<?php

class Archivos {

    public static function AveriguarExtension($archivo) {
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            throw new Exception("Tipo de archivo no soportado: $extension");
        }

        return $extension; // que valor devuelve? -> la extension del archivo por ejemplo -> jpg o jpeg o png
    }

    private static function MoverArchivo($ubicacion_temporal, $ruta_destino) {
        if (!file_exists(dirname($ruta_destino))) {
            mkdir(dirname($ruta_destino), 0777, true);
        }

        if (move_uploaded_file($ubicacion_temporal, $ruta_destino)) {
            return true;
        } else {
            throw new Exception("Hubo un error al subir el archivo");
        }
    }

    public static function SubirImagen($imagen, $nombre) {
        $extension = self::AveriguarExtension($imagen['name']);

        if ($extension == null) {
            return;
        }

        $ruta_destino = "FotosMesasPedidos/$nombre.$extension";
        self::MoverArchivo($imagen['tmp_name'], $ruta_destino);
    }

}



?>