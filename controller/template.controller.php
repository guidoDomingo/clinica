<?php 
class ControllerTemplate{
	static public function ctrTemplate(){
		include "view/template.php";
	}
	
	/**
	 * Obtiene la ruta actual y carga la vista correspondiente
	 */
	public static function getRoute() {
		if(isset($_GET["ruta"])){
			if($_GET["ruta"] == "rh_personas"){
				// Registro para depuración
				error_log("Cargando módulo rh_personas desde getRoute()");
				$module = "view/modules/rh_personas.php";
			} else {
				$module = "view/modules/".$_GET["ruta"].".php";
			}
			return $module;
		} else {
			$module = "view/modules/home.php";
			return $module;
		}
	}
}