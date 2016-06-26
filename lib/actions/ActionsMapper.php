<?php

class ActionsMapper
{
	private $actionsMap = array(
		"GET" => array("user" => "user_get", "users" => "users_get", "category" => "category_get"),
		"POST" => array("user" => "user_create", "login" => "login", "category" => "category_create"),
		"PUT" => array("user" => "user_update", "category" => "category_update"),
		"DELETE" => array("user" => "user_delete", "category" => "category_delete")
	);

	public function __construct() {
	}

	public function getActionsMap() {
		return $this->actionsMap;
	}

}