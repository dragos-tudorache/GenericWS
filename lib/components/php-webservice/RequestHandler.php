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

		// parse URL for components
		$arrParsedURL = explode('/', $strRequestURL);

		// get the resource (e.g /user/username1 will return user)
		$strResource = $arrParsedURL[1];

		//check if the resource is set in the URL (user/..,actions...)
		if(!array_key_exists($strResource, $this->allowedHandleMethods))
		{
			$response["error"] = "Error: Resource does not exist, or operations not allowed on resource : ".$strResource;
		} else {
			// check if the actionsMapper has operation defined for resource (e.g. GET + user => user_get operation)
			$functionName = $this->actionsMapper[$strRequestMethod][$strResource];
			if(!strlen(trim($functionName))) 
			{
				$response["error"] = "Error: Invalid operation";
			} else {

				// $response["result"]["body"] = $this->actionsMapper[$strRequestMethod][$strResource];
				if($strResource == "users") {
					$resourceAction = "handle_user";
				} else {
					$resourceAction = "handle_".strtolower($strResource);
				}
				// file_put_contents(BASE_PATH."/log/log.txt", json_encode($strResource)."\r\n", FILE_APPEND);
				$response = call_user_func(
					$resourceAction, 
					array("requestBody" => $requestBody, "requestURL" => $strRequestURL, "method" => $functionName )
				);
				// file_put_contents(BASE_PATH."/log/log.txt", json_encode($response)."\r\n", FILE_APPEND);
			} 
		}

		return $response;
	}
}