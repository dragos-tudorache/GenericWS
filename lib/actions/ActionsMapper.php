<?php

class ActionsMapper
{
	private $actionsMap = array(
		"GET" => array("user" => "user_get", "users" => "users_get"),
		"POST" => array("user" => "user_create"),
		"PUT" => array("user" => "user_update"),
		"DELETE" => array("user" => "user_delete")
	);

	public function __construct() {
	}

	public function getActionsMap() {
		return $this->actionsMap;
	}

}