<?php

class GWSServer
{
	public $isNotification = false;
	
	public static $strErrorLogFilePath = "";

	public $HTTPResponseCode = 0;
	
	public $RequestHandler = null;

	const HTTP_200_OK = 200;
	
	const HTTP_201_CREATED = 201;

	const HTTP_204_NO_CONTENT = 204;
	
	const HTTP_401_UNAUTHORIZED = 401;

	const HTTP_403_FORBIDDEN = 403;

	const HTTP_500_INTERNAL_SERVER_ERROR = 500;
		
	public function __construct(RequestHandler $rh)
	{
		$this->RequestHandler = $rh;
	}
	
	public function processRequest($JSONRequest=NULL)
	{
		$this->_returnResponse($this->parseRequest($JSONRequest));

	}
	
	public function parseRequest($JSONRequest)
	{
		if(isset($_SERVER["REQUEST_METHOD"]) && !in_array($_SERVER["REQUEST_METHOD"], array("GET", "POST", "PUT", "DELETE")))
		{
			echo "HTTP request method ".$_SERVER["REQUEST_METHOD"]." ignored.";
			exit(0);
		}

		if(!isset($_SERVER['PATH_INFO']))
		{
			echo "HTTP invalid request URL";
			exit(0);
		}

		try
		{
			// get RAW request
			if(is_null($JSONRequest) && ($_SERVER["REQUEST_METHOD"]=="PUT" || $_SERVER["REQUEST_METHOD"]=="POST")) {
					$JSONRequest = file_get_contents("php://input");
			}
			
			// check if request is empty
			if(!strlen(trim($JSONRequest))) {
				throw new Exception("Invalid request.");
			}

			// try parsing the request as JSON object
			try
			{
				$request = json_decode($JSONRequest, true); 
			}
			catch(Exception $exc)
			{
				throw new Exception($exc->getMessage().". RAW request : ".$JSONRequest);
			}

			$strRequestURL = $_SERVER['PATH_INFO'];

			$objResult = $this->RequestHandler->process($request, $strRequestURL);

			if(isset($objResult["error"])) {
				$response =  $objResult["error"];
			} else {
				$response = $objResult["result"];
			}
			// file_put_contents(dirname(__FILE__)."/log.txt", json_encode($response));
			// $response["result"] = "'ok'";
						
		}
		catch(Exception $exc)
		{
			// try 
			// {
			// 	// $this->_log_exception($exc);
			// 	throw $exc;
			// }
			// catch(Exception $exc)
			// {
			// 	$response = $this->_encodeExceptionToJSON($exc);
			// }
			$response["result"] = "lol";
		}
		
		return $response;
	}

	protected function _returnResponse($receivedResponse)
	{		
		if(!$this->HTTPResponseCode) {
			$this->HTTPResponseCode = self::HTTP_500_INTERNAL_SERVER_ERROR;
		}
		
		static $arrHTTPResponseCodesToText = array(
			self::HTTP_200_OK=>"OK",
			self::HTTP_204_NO_CONTENT=>"No Content",
			self::HTTP_401_UNAUTHORIZED=>"Unauthorized",
			self::HTTP_403_FORBIDDEN=>"Forbidden",
			self::HTTP_500_INTERNAL_SERVER_ERROR=>"Internal Server Error"
		);
		
		if(isset($_SERVER["REQUEST_METHOD"]) && in_array($_SERVER["REQUEST_METHOD"], array("GET", "POST", "PUT", "DELETE"))){
			$this->_set_header("HTTP/1.1 ".(int)$this->HTTPResponseCode." ".$arrHTTPResponseCodesToText[(int)$this->HTTPResponseCode], true, $this->HTTPResponseCode);
		}

		$this->_set_header("Content-type: application/json",true, (int)$this->HTTPResponseCode);
		
		echo json_encode($receivedResponse);
		
		if(in_array($this->HTTPResponseCode, array(
			self::HTTP_200_OK, 
			self::HTTP_204_NO_CONTENT,
		)))
		{
			exit(0);
		}
		exit(1);
	}
	
	protected  function _set_header($header, $replace=true, $code)
	{
		if(isset($_SERVER["REQUEST_METHOD"]) && in_array($_SERVER["REQUEST_METHOD"], array("GET", "POST", "PUT", "DELETE")))
			header($header, $replace, $code);
	}


	protected function _encodeExceptionToJSON(Exception $exception)
	{
		$message = $exception->getMessage();
		$code = (int)$exception->getCode();
	
		if(!$this->HTTPResponseCode)	
			$this->HTTPResponseCode=self::HTTP_204_NO_CONTENT;
		
		$arrResponse["error"]=array(
			"message"=>$message,
			"code"=>$code,
		);
		
		return $arrResponse;
	}
	/*
	

	public function callFunction($functionName, $params)
	{
		$this->assertFunctionIsAllowed($functionName);
		
		if(!is_callable($functionName))
			throw new JSONRPC_Exception("Internal error. The function ".$functionName." is not defined or loaded.", JSONRPC_Exception::METHOD_NOT_FOUND);
				
		$result = call_user_func_array($functionName, $params);
	
		return $result;
	}

	public function assertFunctionIsAllowed($functionName)
	{
		if(!in_array($functionName, $this->whitelistedFunctions))
		{
			$this->HTTPResponseCode = self::HTTP_403_FORBIDDEN;
			throw new JSONRPC_Exception("The function \"".$functionName."\" is not whitelisted and/or does not exist.", JSONRPC_Exception::METHOD_NOT_FOUND);
		}
		
		return true;
	}
	
	//stackoverflow
	protected function _log_exception($exc)
	{
		try
		{
			if(strlen(self::$strErrorLogFilePath))
			{
				if(!file_exists(dirname(self::$strErrorLogFilePath)))
					mkdir(dirname(self::$strErrorLogFilePath), 0777, true);
				
				$strClientInfo="";
				if(array_key_exists("REMOTE_ADDR", $_SERVER))
					$strClientInfo.=" ".$_SERVER["REMOTE_ADDR"];
				if(array_key_exists("HTTP_USER_AGENT", $_SERVER))
					$strClientInfo.=" ".$_SERVER["HTTP_USER_AGENT"];
				
				$strErrorLine=PHP_EOL.PHP_EOL.str_repeat("-", 100).PHP_EOL.
					(isset($_SERVER["REQUEST_URI"])?$_SERVER["REQUEST_URI"].PHP_EOL:"").
					date("Y-m-d H:i:s u e").$strClientInfo.PHP_EOL.
					$exc->getFile()."#".$exc->getLine().PHP_EOL.
					"Error type: ".get_class($exc).". Error message: ".$exc->getMessage()." Error code: ".$exc->getCode().PHP_EOL.
					"Stack trace: ".$exc->getTraceAsString().PHP_EOL.PHP_EOL
				;
				error_log($strErrorLine, 3, self::$strErrorLogFilePath);
			}
		}
		catch(Exception $exc)
		{
		}
	}
	*/
	
}