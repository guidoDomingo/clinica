<?php 
class Conexion{

/*=========================================
=            CONEXION POSTGRES            =
=========================================*/

 static public  function conectar(){
 		$contrasena = "wjstks";
		$usuario = "admindba";
		// $nombreBaseDeDatos = "thnpy_qc";
		$nombreBaseDeDatos = "crm_clinic_db";
		// $rutaServidor = "192.9.215.52";
		$rutaServidor = "192.168.0.39";
		$puerto = "5432";
		try {
		    $link = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $contrasena);
		    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    return $link;
		} catch (Exception $e) {
		    echo "Ocurrió un error con la base de datos: " . $e->getMessage();
		}

    }
}
