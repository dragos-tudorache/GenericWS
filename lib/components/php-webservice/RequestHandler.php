<?php 

class RequestHandler {

	private $allowedHandleMethods = array();

	private $actionsMapper = array();

	public function __construct($allowedFunctions, $actionsMap) {
		assert(is_array($allowedFunctions));
		$this->allowedHandleMethods = $allowedFunctions;
		$this->actionsMapper = $actionsMap;
	}

	public function process($strRequestMethod, $strRequestURL, $requestBody) {
		
		$response = array();
		// $response["result"]["body"] = $requestBody;
		// $response["result"]["url"] = $strRequestURL;
		// file_put_contents(BASE_PATH."/log/log.txt", "asdasas"."\r\n", FILE_APPEND);

		// parse URL for components
		$arrParsedURL = explode('/', $strRequestURL);

		// get the resource (e.g /user/username1 will return user)
		$strResource = $arrParsedURL[1];

		//check if the resource is set in the URL (user/..,actions...)
		if(!array_key_exists($strResource, $this->allowedHandleMethods))
		{
			$response["result"]["error"] = "Error: Resource does not exist, or operations not allowed on resource : ".$strResource;
		} else {
			// check if the actionsMapper has operation defined for resource (e.g. GET + user => user_get operation)
			$functionName = $this->actionsMapper[$strRequestMethod][$strResource];
			if(!strlen(trim($functionName))) 
			{
				$response["result"]["error"] = "Error: No operation defined for resource : ".$strResource;
			} else {

				$response["result"]["body"] = $this->actionsMapper[$strRequestMethod][$strResource];
				// $response["result"]["body"] = $this->call_user_func_array($functionName, array($strRequestURL, $requestBody));
			} 
		}

		// $response["result"]["body"] = $arrParsedURL[2];
		// $response["result"]["body"] = $this->actionsMapper[$strRequestMethod][$arrParsedURL[1]];
		

		return $response;
	}
}