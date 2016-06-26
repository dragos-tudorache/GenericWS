<?php
require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config/config.php");

// array("requestBody" => $requestBody, "requestURL" => $strRequestURL, "method" => $functionName )
function handle_user($handleData) {

	if($handleData["method"] == "user_create") {
		// parse params for method and return the result
		return user_create($handleData["requestBody"]);

	} elseif($handleData["method"] == "user_get") {

		// get the user unique tag
		$dataObject["user_unique_tag"] = explode('/', $handleData["requestURL"])[2];
		return user_get($dataObject);

	} elseif($handleData["method"] == "user_delete") {

		// get the user unique tag
		$dataObject["user_unique_tag"] = explode('/', $handleData["requestURL"])[2];
		return user_delete($dataObject);

	} elseif($handleData["method"] == "user_update") {

		// get the user unique tag
		$dataObject["user_unique_tag"] = explode('/', $handleData["requestURL"])[2];
		$dataObject["update_data"] = $handleData["requestBody"];
		return user_update($dataObject);

	} elseif($handleData["method"] == "users_get") {

		return users_get();

	} else {

		$response["error"]["code"] = 10119;
		$response["error"]["message"] = "Method not handled";
		return $response;
	}
}


/**
* This function adds new users in database;
* @param number $deviceID.
* @param string $userName.
* @param string $userPassword.
* @param string $userMail.
* @param string $userAddress.
* @return true. Returns true on success. Throws on error.
*/
function user_create($dataObject)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;

	// get the user unique identifier
	$userID = $dataObject["user_unique_tag"];

	$result = array();

	$userExists = user_get(array("user_unique_tag" => $userID));

	// verify if user already exists
	if($userExists["data"] !== false) {
		$result["error"]["code"] = 10121;
		$result["error"]["message"] = "User already exists";
		return $result;
	}

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			INSERT INTO `users`
			SET
				`user_unique_tag`=".$objDatabaseConnection->quote($dataObject["user_unique_tag"]).",
				`user_first_name`=".$objDatabaseConnection->quote($dataObject["user_first_name"]).",
				`user_last_name`=".$objDatabaseConnection->quote($dataObject["user_last_name"]).",
				`user_mail`=".$objDatabaseConnection->quote($dataObject["user_mail"]).",
				`user_phone`=".$objDatabaseConnection->quote($dataObject["user_phone"]).",
				`user_address`=".$objDatabaseConnection->quote($dataObject["user_address"]).",
				`user_password_hash`=".$objDatabaseConnection->quote($dataObject["user_password_hash"]).",
				`user_role`=".$objDatabaseConnection->quote((int)$dataObject["user_role"]).",
				`user_hero_level`=".$objDatabaseConnection->quote((int)$dataObject["user_hero_level"]).",
				`user_created_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT)).",
				`user_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
			;");
		
		if(!$affectedRows) {
			$result["error"]["code"] = 10122;
			$result["error"]["message"] = "Not added. No rows affected";
		} else {
			$result = user_get(array("user_unique_tag" => $userID));
		}
		
	} catch(PDOException $err) {
		$result["error"]["code"] = 10110;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
* This function fetches user data.
* @param array userId.
* @return  NULL or array containing the following keys:
* `user_id`
* `user_name`
* `user_mail`
* `user_phone`
* `user_address`
* `user_password_hash`
* `user_role`
* `user_hero_level`
* `user_created_timestamp`
* `user_updated_timestamp`
*/
function user_get($dataObject)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$result = NULL;
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT 
				`user_unique_tag`,
				`user_first_name`,
				`user_last_name`,
				`user_mail`,
				`user_phone`,
				`user_address`,
				`user_password_hash`,
				`user_role`,
				`user_created_timestamp`,
				`user_updated_timestamp` 
			FROM `users`
			WHERE 
				`user_unique_tag`=".$objDatabaseConnection->quote($dataObject["user_unique_tag"])."
			")->fetch(PDO::FETCH_ASSOC);

		if(!$resultSet) {
			$result["data"] = false;
		} else {
			$result["data"] = $resultSet;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10109;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
* This function deletes users.
* @param number $functionData.
* @return true/false.
*/
function user_delete($dataObject)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;

	$result["data"] = true;

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			DELETE 
			FROM `users`
			WHERE 
				`user_unique_tag`=".$objDatabaseConnection->quote($dataObject["user_unique_tag"])."
			");

		if(!$affectedRows) {
			$result["data"] = false;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10108;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
* This function updates user data.
* @param number $userID;
* @param array $updateData. Contains the following keys;
* user_name,
* user_password,
* user_mail,
* user_address
* @return true. Updates on success throws error if no modification takes place.
*/
function user_update($dataObject)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	$result = array();

	// obtain required params
	$userData = $dataObject["update_data"];
	$userID = $dataObject["user_unique_tag"];

	// check if user exists
	$userExists = user_get(array("user_unique_tag" => $userID));
	// check if phone exists
	$phoneExists = phone_exists($userData["user_phone"]);
	// check if mail exists
	$mailExists = mail_exists($userData["user_mail"]);
	

	// trying to update non-existent user
	if($userExists["data"] === false) {
		$result["error"]["code"] = 10124;
		$result["error"]["message"] = "User does not exist";
		return $result;
	}

	// validate phone 
	// if($phoneExists["data"] !== false) {
	// 	$result["error"]["code"] = 10125;
	// 	$result["error"]["message"] = "Phone number already exists";
	// 	return $result;
	// }

	// validate mail
	// if($mailExists["data"] !== false) {
	// 	$result["error"]["code"] = 10126;
	// 	$result["error"]["message"] = "Mail already exists";
	// 	return $result;
	// }

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			UPDATE `users`
			SET
				`user_first_name`=".$objDatabaseConnection->quote($userData["user_first_name"]).",
				`user_last_name`=".$objDatabaseConnection->quote($userData["user_last_name"]).",
				`user_mail`=".$objDatabaseConnection->quote($userData["user_mail"]).",
				`user_phone`=".$objDatabaseConnection->quote($userData["user_phone"]).",
				`user_address`=".$objDatabaseConnection->quote($userData["user_address"]).",
				`user_password_hash`=".$objDatabaseConnection->quote($userData["user_password_hash"]).",
				`user_role`=".$objDatabaseConnection->quote((int)$userData["user_role"]).",
				`user_hero_level`=".$objDatabaseConnection->quote((int)$userData["user_hero_level"])."
			WHERE 
				`user_unique_tag`=".$objDatabaseConnection->quote($userID)."
			");

		if(!$affectedRows) {
			$result["data"] = false;
		} else {
			// update timestamp with latest modification
			$objDatabaseConnection->exec("
				UPDATE `users`
				SET	
					`user_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
				WHERE 
					`user_unique_tag`=".$objDatabaseConnection->quote($userID)."
			");

			$result["data"] = true;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10107;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
*
*/
function users_get()
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$result = NULL;
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT 
				`user_unique_tag`,
				`user_first_name`,
				`user_last_name`,
				`user_mail`,
				`user_phone`,
				`user_address`,
				`user_password_hash`,
				`user_role`,
				`user_created_timestamp`,
				`user_updated_timestamp` 
			FROM `users`
			ORDER BY `user_unique_tag`
			")->fetchAll(PDO::FETCH_ASSOC);

		if(!$resultSet) {
			$result["data"] = false;
		} else {
			$result["data"] = $resultSet;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10106;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}


/**
*
*
*
*/
function mail_exists($strUserMail) {
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;

	$result = NULL;
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT * 
			FROM `users`
			WHERE 
				`user_mail`=".$objDatabaseConnection->quote($strUserMail)."
			")->fetch(PDO::FETCH_ASSOC);

		if(!$resultSet) {
			$result["data"] = false;
		} else {
			$result["data"] = true;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10105;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
*
*
*
*/
function phone_exists($strUserPhone) {
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;

	$result = NULL;
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT * 
			FROM `users`
			WHERE 
				`user_phone`=".$objDatabaseConnection->quote($strUserPhone)."
			")->fetch(PDO::FETCH_ASSOC);

		if(!$resultSet) {
			$result["data"] = false;
		} else {
			$result["data"] = true;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10104;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
* Transforms user_name into user_id.
* @param string $userName;
* @return string $userID.
*/
function user_phone_to_user_unique_tag($strUserPhone)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$result=NULL;

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT `user_unique_tag`
			FROM `users`
			WHERE 
				`user_phone`=".$objDatabaseConnection->quote($strUserPhone)."
			;")->fetchColumn();
	
		if(count($resultSet) > 1) {
			$result["error"]["code"] = 10125;
			$result["error"]["message"] = "Multiple instances were found";
		} else if(!$resultSet) {
			$result["error"]["code"] = 10126;
			$result["error"]["message"] = "No user was found";
		} else {
			$result["data"] = $resultSet;
		}
	}catch(PDOException $err) {
		$result["error"]["code"] = 10103;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}
	
	return $result;
}

/**
* Transforms user_name into user_id.
* @param string $userName;
* @return string $userID.
*/
function user_mail_to_user_unique_tag($strUserMail)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$result=NULL;
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT `user_unique_tag`
			FROM `users`
			WHERE 
				`user_mail`=".$objDatabaseConnection->quote($strUserMail)."
			;")->fetchColumn();
	
		if(count($resultSet) > 1) {
			$result["error"]["code"] = 10125;
			$result["error"]["message"] = "Multiple instances were found";
		} else if(!$resultSet) {
			$result["error"]["code"] = 10126;
			$result["error"]["message"] = "No user was found";
		} else {
			$result["data"] = $resultSet;
		}
	}catch(PDOException $err) {
		$result["error"]["code"] = 10102;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}
	
	return $result;
}