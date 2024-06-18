<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AutentificadorJWT {
    
    private static $claveSecreta = 'T3sT$JWT';
    private static $tipoEncriptacion = 'HS256';


    public static function CrearToken($datos){
        $ahora = time();
        $payload = array(
        	'iat'=>$ahora,
        	'exp' => $ahora + 120, // Tiempo de vida del token, 60 es un minuto
            'aud' => self::Aud(),
        	'data' => $datos,
        	'app' => "Tp comanda"
        );

        return JWT::encode($payload, self::$claveSecreta, self::$tipoEncriptacion); // Codificamos el token
    }

    public static function VerificarToken($token){
        if(empty($token)){
            throw new Exception("El token esta vacio.");
        } 

        try {
            $decodificado = JWT::decode(
                $token,
                new Key(self::$claveSecreta, self::$tipoEncriptacion)
            );
        } catch (ExpiredException $e) {
            throw new Exception("El token ha expirado.");
        } catch (Exception $e) {
            throw new Exception("Error al decodificar el token: " . $e->getMessage());
        }

        if($decodificado->aud !== self::Aud()){
            throw new Exception("El token no es valido.");
        }
    }

    public static function ObtenerPayload($token){

        if(empty($token)){
            throw new Exception("El token esta vacio.");
        }

        return JWT::decode(
            $token,
            new Key(self::$claveSecreta, self::$tipoEncriptacion)
        );
    }

    public static function ObtenerData($token){
        if(empty($token)){
            throw new Exception("El token esta vacio.");
        }

        return JWT::decode( // Decodificamos el token para obtener los datos del usuario
            $token,
            new Key(self::$claveSecreta, self::$tipoEncriptacion)
        )->data;
    }

    // Funcion para obtener el usuario que genero el token
    public static function Aud(){
        $aud = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }
        
        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();
        
        return sha1($aud); // Codificamos el valor en sha1
    }
}






?>