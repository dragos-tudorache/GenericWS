<?php 

class RequestHandler {

	private $whitelistedFunctions = array();

	public function __construct($allowedFunctions) {
		assert(is_array($allowedFunctions));
		$this->whitelistedFunctions = $allowedFunctions;
	}

	public function process($objBody, $strRequest) {
		//TODO

		$response = array("result"=>null);
		$response["result"]["body"] = $objBody;
		$response["result"]["url"] = $strRequest;

		return $response;
	}
}