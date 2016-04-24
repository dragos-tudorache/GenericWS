<?php
require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config/config.php");
// require_once("php/Mesher/devices.php");

/**
* This function adds new users in database;
* @param number $deviceID.
* @param string $userName.
* @param string $userPassword.
* @param string $userMail.
* @param string $userAddress.
* @return true. Returns true on success. Throws on error.
*/
function user_create($functionData)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;

	// get the user unique identifier
	$userID = explode('/', $functionData["reuqestURL"])[2];
	// get request body params
	$userData = $functionData["requestBody"];

	$result = array();

	$userExists = user_get($functionData);
	
	// verify if user already exists
	if($userExists !== NULL) 
	{
		$result["error"] = "User already exists.";
		return $result;
	}

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
				// `user_name`=".$objDatabaseConnection->quote($userData["user_name"]).",
		$affectedRows = $objDatabaseConnection->exec("
			INSERT INTO `users`
			SET
				`user_name`=".$objDatabaseConnection->quote($userID).",
				`user_mail`=".$objDatabaseConnection->quote($userData["user_mail"]).",
				`user_phone`=".$objDatabaseConnection->quote($userData["user_phone"]).",
				`user_address`=".$objDatabaseConnection->quote($userData["user_address"]).",
				`user_password_hash`=".$objDatabaseConnection->quote($userData["user_password_hash"]).",
				`user_role`=".$objDatabaseConnection->quote((int)$userData["user_role"]).",
				`user_hero_level`=".$objDatabaseConnection->quote((int)$userData["user_hero_level"]).",
				`user_created_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT)).",
				`user_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
			;");

		if(!$affectedRows) {
			$result["response"] = "Not added. Entry already exists.";
		} else {
			$result["response"] = "User added successfully.";
		}
		
	} catch(PDOException $err) {
		// file_put_contents(BASE_PATH."/log/log.txt", json_encode($err->getMessage())."\r\n", FILE_APPEND);
		$result["error"] = "Database error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
* This function fetches user data.
* @param array functionData.
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
function user_get($functionData)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	// get the user unique identifier
	$userID = explode('/', $functionData["reuqestURL"])[2];
	// get request body params
	$userData = $functionData["requestBody"];

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
				`user_name`=".$objDatabaseConnection->quote($userID)."
			")->fetch(PDO::FETCH_ASSOC);

		if($resultSet) {
			$result["response"] = $resultSet;
		}
	} catch(PDOException $err) {
		$result["error"] = "Database error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
* This function deletes users.
* @param number $functionData.
* @return true/false.
*/
function user_delete($functionData)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	// get the user unique identifier
	$userID = explode('/', $functionData["reuqestURL"])[2];
	// get request body params
	$userData = $functionData["requestBody"];

	$result["response"] = true;

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			DELETE 
			FROM `users`
			WHERE 
				`user_name`=".$objDatabaseConnection->quote($userID)."
			");

		if(!$affectedRows) {
			$result["response"] = false;
		}
	} catch(PDOException $err) {
		$result["error"] = "Database error: ".json_encode($err->getMessage());
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
function user_update($functionData)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	// get the user unique identifier
	$userID = explode('/', $functionData["reuqestURL"])[2];
	// get request body params
	$userData = $functionData["requestBody"];

	$result = array();

	$userExists = user_get($functionData);
	
	// trying to update non-existent user
	if($userExists === NULL || isset($userExists["error"])) 
	{
		$result["error"] = "Could not update. User does not exist.";
		return $result;
	}

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			UPDATE `users`
			SET
				`user_mail`=".$objDatabaseConnection->quote($userData["user_mail"]).",
				`user_phone`=".$objDatabaseConnection->quote($userData["user_phone"]).",
				`user_address`=".$objDatabaseConnection->quote($userData["user_address"]).",
				`user_password_hash`=".$objDatabaseConnection->quote($userData["user_password_hash"]).",
				`user_role`=".$objDatabaseConnection->quote((int)$userData["user_role"]).",
				`user_hero_level`=".$objDatabaseConnection->quote((int)$userData["user_hero_level"])."
			WHERE 
				`user_name`=".$objDatabaseConnection->quote($userID)."
			");

		if(!$affectedRows) {
			$result["response"] = "Nothing to update.";
		} else {
			// update timestamp with latest modification
			$objDatabaseConnection->exec("
				UPDATE `users`
				SET	
					`user_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
				WHERE 
					`user_name`=".$objDatabaseConnection->quote($userID)."
			");

			$result["response"] = "User updated successfully.";
		}
	} catch(PDOException $err) {
		$result["error"] = "Database error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
* This function updates user password.
* @param string $userID;
* @param string $userNewPassword.
* @return true. This function will not throw if nothing changes.
*/
function user_set_new_password($userID, $userNewPassword)
{/*
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			UPDATE `users`
			SET
				`user_password`=".$objDatabaseConnection->quote(md5($userNewPassword)).",
				`user_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
			WHERE 
				`user_id`=".$objDatabaseConnection->quote($userID)."
			");

	}catch(PDOException $err) {
		// echo 'PDO ERROR: ' . $err->getMessage();
		throw new Exception($err->getMessage());
	}

	return true;*/
}


/**
* Transforms user_name into user_id.
* @param string $userName;
* @return string $userID.
*/
function user_name_to_user_id($userName)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$resultSet=NULL;
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT `user_id`
			FROM `users`
			WHERE 
				`user_name`=".$objDatabaseConnection->quote($userName)."
			;")->fetchColumn();
	
		if(count($resultSet) > 1)
			throw new Exception("Multiple instances were found.");
		if(!$resultSet)
			throw new Exception("No user was found.");
	

	}catch(PDOException $err) {
		// echo 'PDO ERROR: ' . $err->getMessage();
		throw new Exception($err->getMessage());
	}
	
	return $resultSet;
}

/**
* Transforms user_name into user_id.
* @param string $userName;
* @return string $userID.
*/
function user_id_to_user_name($userID)
{/*
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$resultSet=NULL;

	user_data((int)$userID);

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT `user_name`
			FROM `users`
			WHERE 
				`user_id`=".(int)$userID."
			;")->fetchColumn();
	
		if(count($resultSet) > 1)
			throw new Exception("Multiple instances were found.");
		if(!$resultSet)
			throw new Exception("No result was found.");
	

	}catch(PDOException $err) {
		// echo 'PDO ERROR: ' . $err->getMessage();
		throw new Exception($err->getMessage());
	}
	
	return $resultSet;*/
}

/**
* Gets all users.
* @return array.
*/
function users_get()
{/*
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$resultSet=NULL;
	
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT 
				`user_id`,
				`user_name`,
				`device_id`
			FROM `users`
			ORDER BY `user_id`
			")->fetchAll(PDO::FETCH_ASSOC);

		if(!$resultSet)
			throw new Exception("No users exist.");
	}catch(PDOException $err) {
		// echo 'PDO ERROR: ' . $err->getMessage();
		throw new Exception($err->getMessage());
	}

	return $resultSet;*/
}