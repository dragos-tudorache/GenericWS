<?php
// require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config/config.php");
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
function user_add($deviceID, $userName, $userPassword, $userMail, $userAddress=NULL)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	device_data($deviceID);
	
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			INSERT INTO `users`
			SET
				`device_id`=".$objDatabaseConnection->quote($deviceID).",
				`user_name`=".$objDatabaseConnection->quote($userName).",
				`user_password`=".$objDatabaseConnection->quote(md5($userPassword)).",
				`user_mail`=".$objDatabaseConnection->quote($userMail).",
				`user_address`=".$objDatabaseConnection->quote($userAddress).",
				`user_created_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT)).",
				`user_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
			;
			");

		if(!$affectedRows)
			throw new Exception("No user was added;");
		
	} catch(PDOException $err) {
		// echo 'PDO ERROR: ' . $err->getMessage();
		throw new Exception($err->getMessage());
	}
	return true;
}

/**
* This function fetches user data.
* @param number $userID.
* @return array containing the following keys:
* user_id,
* device_id,
* user_name,
* user_password,
* user_mail,
* user_address,
* user_created_timestamp,
* user_updated_timestamp
*/
function user_data($userID)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$resultSet=NULL;
	
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT * 
			FROM `users`
			WHERE 
				`user_id`=".$objDatabaseConnection->quote($userID)."
			")->fetch(PDO::FETCH_ASSOC);

		if(!$resultSet)
			throw new Exception("User does not exist.");
	}catch(PDOException $err) {
		// echo 'PDO ERROR: ' . $err->getMessage();
		throw new Exception($err->getMessage());
	}

	return $resultSet;
}

/**
* This function deletes users.
* @param number $userID.
* @return true. Throws  on error.
*/
function user_delete($userID)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			DELETE 
			FROM `users`
			WHERE 
				`user_id`=".$objDatabaseConnection->quote($userID)."
			");

		if(!$affectedRows)
			throw new Exception("Failed to delete user.");
	}catch(PDOException $err) {
		// echo 'PDO ERROR: ' . $err->getMessage();
		throw new Exception($err->getMessage());
	}

	return true;
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
function user_update($userID, $updateData)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			UPDATE `users`
			SET
				`user_name`=".$objDatabaseConnection->quote($updateData["user_name"]).",
				`user_password`=".$objDatabaseConnection->quote(md5($updateData["user_password"])).",
				`user_mail`=".$objDatabaseConnection->quote($updateData["user_mail"]).",
				`user_address`=".$objDatabaseConnection->quote($updateData["user_address"])."
			WHERE 
				`user_id`=".$objDatabaseConnection->quote($userID)."
			");

		if(!$affectedRows)
			throw new Exception("Must update at least one row.");
			
		$objDatabaseConnection->exec("
			UPDATE `users`
			SET	
				`user_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
			WHERE 
				`user_id`=".$objDatabaseConnection->quote($userID)."
		");
	}catch(PDOException $err) {
		// echo 'PDO ERROR: ' . $err->getMessage();
		throw new Exception($err->getMessage());
	}

	return true;
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
{/*
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
	
	return $resultSet;*/
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