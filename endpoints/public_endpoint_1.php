<?php
require_once(dirname(dirname(__FILE__))."/config/config.php");
require_once("lib/components/php-webservice/GWSServer.php");
require_once("lib/components/php-webservice/RequestHandler.php");
require_once("lib/actions/ActionsMapper.php");
require_once("lib/actions/users/users.php");

$allowedFunctionCalls = array(
	"user" => array("user_add", "user_data", "user_delete",	"user_update", "users_get"),
	"users" => array("v")
	);

$actionsMap = new ActionsMapper();
$rh = new RequestHandler($allowedFunctionCalls, $actionsMap->getActionsMap());
$GWSServer = new GWSServer($rh);
$GWSServer->processRequest();