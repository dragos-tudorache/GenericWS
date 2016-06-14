<?php
require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config/config.php");
require_once("lib/actions/users/users.php");

function handle_login($handleData) {

	if($handleData["method"] == "login") {
		// parse params for method and return the result
		return login($handleData["requestBody"]);
	} else {
		$response["error"]["code"] = 10120;
		$response["error"]["message"] = "Method not handled";
		return $response;
	}
}

/**
* This function adds new users in database;
* @return true. Returns true on success. Throws on error.
*/
function login($dataObject)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;

	$result = array();

	// get the user unique identifier
	$userIdentifier = $dataObject["login_identifier"];
		
	$userByMail = user_mail_to_user_unique_tag($userIdentifier);
	
	$userByPhoneNumber = user_phone_to_user_unique_tag($userIdentifier);
	
	if(!$userByMail["error"])
	{
		// create the object for user_get
		$userRequestData["user_unique_tag"] = $userByMail["data"];
		
		// fetch data
		$userData = user_get($userRequestData);

		// dispatch data only if password hash matches
		if($userData["data"]["user_password_hash"] == $dataObject["login_password_hash"]) {
			$result = $userData;
		} else {
			$result["error"]["code"] = 10130;
			$result["error"]["message"] = "Invalid user credentials";
		}
	} elseif(!$userByPhoneNumber["error"]) {
		// create the object for user_get
		$userRequestData["user_unique_tag"] = $userByPhoneNumber["data"];

		// fetch data
		$userData = user_get($userRequestData);
		
		// dispatch data only if password hash matches
		if($userData["data"]["user_password_hash"] == $dataObject["login_password_hash"]) {
			$result = $userData;
		} else {
			$result["error"]["code"] = 10131;
			$result["error"]["message"] = "Invalid user credentials";
		}
	} else {
		// user not found (by phone_number/mail)
		$result["error"]["code"] = 10121;
		$result["error"]["message"] = "User does not exists";
	}

	return $result;
}
