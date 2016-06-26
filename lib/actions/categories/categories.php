<?php
require_once(dirname(dirname(dirname(dirname(__FILE__))))."/config/config.php");

// array("requestBody" => $requestBody, "requestURL" => $strRequestURL, "method" => $functionName )
function handle_category($handleData) {

	if($handleData["method"] == "category_create") {
		// parse params for method and return the result
		return category_create($handleData["requestBody"]);

	} elseif($handleData["method"] == "category_update") {

		// get the category name
		$dataObject["category_name"] = explode('/', $handleData["requestURL"])[2];
		$dataObject["category_data"] = $handleData["requestBody"];
		return category_update($dataObject);

	} elseif($handleData["method"] == "category_delete") {

		// get the category name
		$dataObject["category_name"] = explode('/', $handleData["requestURL"])[2];
		return category_delete($dataObject);

	} elseif($handleData["method"] == "category_get") {

		// get the category name
		$dataObject["category_name"] = explode('/', $handleData["requestURL"])[2];
		return category_get($dataObject);

	} else {

		$response["error"]["code"] = 10118;
		$response["error"]["message"] = "Method not handled";
		return $response;
	}
}


/**
* 
* 
*/
function category_create($dataObject)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;

	$result = array();

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			INSERT INTO `categories`
			SET
				`category_name`=".$objDatabaseConnection->quote($dataObject["category_name"]).",
				`category_level`=".$objDatabaseConnection->quote($dataObject["category_level"]).",
				`category_type`=".$objDatabaseConnection->quote($dataObject["category_type"]).",
				`category_scores`=".$objDatabaseConnection->quote($dataObject["category_scores"]).",
				`category_created_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT)).",
				`category_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
			;");
		
		if(!$affectedRows) {
			$result["error"]["code"] = 10140;
			$result["error"]["message"] = "Not added. No rows affected";
		} else {
			$result = category_get(array("category_name" => $dataObject["category_name"]));
		}
		
	} catch(PDOException $err) {
		$result["error"]["code"] = 10099;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}

/**
* 
* 
*/
function category_update($dataObject)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	
	$result = array();
	// trying to update non-existent user
	// if($userExists["data"] === false) {
	// 	$result["error"]["code"] = 10124;
	// 	$result["error"]["message"] = "User does not exist";
	// 	return $result;
	// }

	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$affectedRows = $objDatabaseConnection->exec("
			UPDATE `categories`
			SET
				`category_level`=".$objDatabaseConnection->quote($dataObject["category_data"]["category_level"]).",
				`category_type`=".$objDatabaseConnection->quote($dataObject["category_data"]["category_type"]).",
				`category_scores`=".$objDatabaseConnection->quote($dataObject["category_data"]["category_scores"])."
			WHERE 
				`category_name`=".$objDatabaseConnection->quote($dataObject["category_name"])."
			");

		if(!$affectedRows) {
			$result["data"] = false;
		} else {
			// update timestamp with latest modification
			$objDatabaseConnection->exec("
				UPDATE `categories`
				SET	
					`category_updated_timestamp`=".$objDatabaseConnection->quote(gmdate(TIMEZONE_FORMAT))."
				WHERE 
					`category_name`=".$objDatabaseConnection->quote($dataObject["category_name"])."
			");

			$result["data"] = true;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10097;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}


/**
* This function deletes users.
* @param number $functionData.
* @return true/false.
*/
function category_delete($dataObject)
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
			FROM `categories`
			WHERE 
				`category_name`=".$objDatabaseConnection->quote($dataObject["category_name"])."
			");

		if(!$affectedRows) {
			$result["data"] = false;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10098;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}


function category_get($dataObject)
{
	$strDSN=DB_TYPE.":host=".DB_HOST.";dbname=".DB_NAME;
	$result = NULL;
	try
	{
		$objDatabaseConnection=new PDO($strDSN, DB_USER, DB_PASS, array (
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		
		$resultSet = $objDatabaseConnection->query("
			SELECT *
			FROM `categories`
			WHERE 
				`category_name`=".$objDatabaseConnection->quote($dataObject["category_name"])."
			")->fetch(PDO::FETCH_ASSOC);

		if(!$resultSet) {
			$result["data"] = false;
		} else {
			$result["data"] = $resultSet;
		}
	} catch(PDOException $err) {
		$result["error"]["code"] = 10096;
		$result["error"]["message"] = "PDO error: ".json_encode($err->getMessage());
	}

	return $result;
}