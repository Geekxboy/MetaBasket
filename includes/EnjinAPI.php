<?php

class EnjinAPIMeta {
	
	public $app_id;
	public $app_secret;
	public $auth_token;
	public $network;
	public $identity_id;
	
	private $useURL = "https://cloud.enjin.io/";
	
	public function __construct($app_id, $app_secret, $network, $identity_id){
		$this->app_id = $app_id;
		$this->app_secret = $app_secret;
		$this->identity_id = $identity_id;
		
		$this->network = $network;
		
		switch($network) {
			case "Jumpnet":
				$this->useURL = "https://jumpnet.cloud.enjin.io/";
				break;
			case "Testnet":
				$this->useURL = "https://kovan.cloud.enjin.io/";
				break;
		}
	}
	
	function authorize() {
		$data = '{"grant_type":"client_credentials","client_id": "' . $this->app_id . '","client_secret":"' . $this->app_secret . '"}';
		$url = $this->useURL . 'oauth/token';

		$headers = array(
			"Accept: application/json",
			"Content-Type: application/json"
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		curl_close($ch);

		$data = json_decode($response, true);
		
		if (isset($data["access_token"])) {
			$this->auth_token = $data["access_token"];
			return $data;
		} else {
			return $data;
		}
	}
	
	function isAuthorized() {
		if ($this->auth_token == "") {
			return false;
		} else {
			return true;
		}
	}
	
	function run($type, $query) {
		$data = [ "query" => $type . " ". $query ];

		$url = $this->useURL . 'graphql';

		$auth_token = "Authorization: Bearer ". $this->auth_token;
		$app_id = "X-App-Id: ". $this->app_id;

		$headers = array(
			$auth_token,
			$app_id,
			"Accept: application/json",
			"Content-Type: application/json"
		);

		$postfields = json_encode($data);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		curl_close($ch);

		return json_decode($response, true);
	}
	
	function sendItem($addr, $token_id, $amount, $appID) {
		$identity_id = $this->identity_id;
		
		if ($appID == "") {
			$appID = $this->app_id;
		}
		
		$query = '
		advancedSendAll {
		  CreateEnjinRequest(
			identityId: ' . $identity_id . ', 
			type: ADVANCED_SEND, 
			appId: ' . $appID . ', 
			advanced_send_token_data: {
			  transfers: [
				{
				  token_id: "' . $token_id . '", 
				  to: "' . $addr . '", 
				  value: "1"
				}
			  ]
			}
		  ) {
			id
			encodedData
			transactionId
		  }
		}
		';
		
		$response = $this->run("mutation", $query);
		
		if (isset($response["errors"][0]["message"])) {
			return $response["errors"][0]["message"];
		} else {
			return $response["data"];
		}
	}
	
	function mintItem($addr, $token_id, $amount) {
		$identity_id = $this->identity_id;
		
		$type = $this->getTokenType($token_id);
		
		if ($type == "NFT") {
			return $this->mintNonFungibleItem($addr, $token_id, $amount);
		} else if ($type == "FT") {
			return $this->mintFungibleItem($addr, $token_id, $amount);
		} else {
			return $type;
		}
	}
	
	function mintNonFungibleItem($addr, $token_id, $amount) {
		$identity_id = $this->identity_id;
		
		$query = 'mintNonFungibleItems { CreateEnjinRequest( identityId: ' . $identity_id . ', appId: ' . $this->app_id . ', type: MINT, mint_token_data: { token_id: "' . $token_id . '", token_index: "0", recipient_address_array: [';
		
		$addresses = "";
		for ($i = 0; $i < $amount; $i++) {
			if ($addresses != "") {
				$addresses = $addresses . ",";
			}
			
			$addresses = $addresses . '"' . $addr . '"';
		}
			  
		$query = $query . $addresses. ']}) {id,encodedData,token{reserve}}}';	
		
		$response = $this->run("mutation", $query);
		
		if (isset($response["errors"][0]["message"])) {
			return $response["errors"][0]["message"];
		} else {
			return $response["data"];
		}
	}
	
	function mintFungibleItem($addr, $token_id, $amount) {
		$identity_id = $this->identity_id;
		
		$query = 'mintFungibleItems {CreateEnjinRequest(identityId: ' . $identity_id . ', type: MINT, appId: ' . $this->app_id . ', mint_token_data: {token_id: "' . $token_id . '", recipient_address_array: ["' . $addr . '" ], value_array: [' . $amount . ']}) {id,encodedData,token{reserve}}}';	
		
		$response = $this->run("mutation", $query);
		
		if (isset($response["errors"][0]["message"])) {
			return $response["errors"][0]["message"];
		} else {
			return $response["data"];
		}
	}
	
	function getTransactionData($id) {
		$identity_id = $this->identity_id;
		
		$query = '
		ViewTransactionData {
		  EnjinTransactions(id: ' . $id . ') {
			id, 
			transactionId, 
			type, 
			state,
			token {
			  reserve
			}
		  }
		}';
		
		$response = $this->run("query", $query);
		
		if (isset($response["data"]["EnjinTransactions"][0])) {
			return $response["data"]["EnjinTransactions"][0];
		} else {
			return "ERROR";
		}
	}

	function getItemData($token_id) {
		$identity_id = $this->identity_id;
		
		$query = '
			viewTokens {
				EnjinTokens(id: "' . $token_id . '") {
					id
					name
					creator
					meltValue
					meltFeeRatio
					meltFeeMaxRatio
					supplyModel
					totalSupply
					circulatingSupply
					reserve
					transferable
					nonFungible
					availableToMint
					itemURI
				}
			}
		';	
		
		$response = $this->run("query", $query);
		
		if (isset($response["data"]["EnjinTokens"][0])) {
			return $response["data"]["EnjinTokens"][0];
		} else {
			return false;
		}
	}

	function getTokenType($token_id) {
		$response = $this->getItemData($token_id);

		if (is_bool($response)) {
			return "Token Not Found";
		} else {
			return ($response['nonFungible'] ? "NFT" : "FT");
		}
	}
}

?>