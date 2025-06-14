<?php 
//CONTROLLER
include "controller/consultas.controller.php";
include "controller/template.controller.php";
include "controller/archivos.controller.php";
include "controller/user.controller.php";
include "controller/register.controller.php";
include "controller/permisos.controller.php";

//MODEL
include "model/register.model.php";
include "model/archivos.model.php";
include "model/consultas.model.php";
include "model/personas.model.php";
include "model/permisos.model.php";
$template = new ControllerTemplate();
$template -> ctrTemplate();