<?php
require_once(dirname(dirname(__FILE__))."/config/config.php");
require_once("lib/components/php-webservice/GWSServer.php");
require_once("lib/components/php-webservice/RequestHandler.php");
require_once("lib/actions/ActionsMapper.php");
require_once("lib/actions/users/users.php");
require_once("lib/actions/login/login.php");

$allowedFunctionCalls = array(
	"login" => array("login")
	);

$actionsMap = new ActionsMapper();
$rh = new RequestHandler($allowedFunctionCalls, $actionsMap->getActionsMap());
$GWSServer = new GWSServer($rh);
$GWSServer->processRequest();