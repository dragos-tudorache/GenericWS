<?php
require_once(dirname(dirname(__FILE__))."/config/config.php");
require_once("lib/components/php-webservice/GWSServer.php");
require_once("lib/components/php-webservice/RequestHandler.php");
// require_once("php/Mesher/users.php");
//GenericWS\lib\components\php-webservice
$allowedFunctionCalls = array(
	"user_add",
	"user_data",
	"user_delete",
	"user_update",
	"user_set_new_password",
	"user_name_to_user_id",
	"user_id_to_user_name",
	"users_get"
	);
$rh = new RequestHandler($allowedFunctionCalls);
$GWSServer = new GWSServer($rh);
// $MesherServer->setIdentity("public");
$GWSServer->processRequest();