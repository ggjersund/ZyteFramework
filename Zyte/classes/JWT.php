<?php
/**
*
* The ZyteFrameworks JSON Web Token class
*
* @package            ZyteFramework
* @subpackage         classes
* @author             ZyteFramework Open Source Team
* @copyright          Copyright (c) 2017 - 2017, ZyteFramework
* @license            Open Source
* @link               https://zyteframework.com/
*
*/

namespace Zyte\classes;

class JWT {

	/**
  * JWT class variables
  *
  * @var  string  JWT token
  */
	private $token;

	/**
  * Put JWT
  *
  * @access   public
  * @param   	string  	JWT token
  */
  public function put ($jwt) {
		$this->token = $jwt;
	}

	/**
  * Get JWT
  *
  * @access   public
  * @return   string  	JWT token
  */
  public function get () {
		return $this->token;
	}

	/**
  * Get JWT Payload
  *
  * @access   public
	* @param 		string	JWT token
	* @return   array  	JWT payload data
  */
	public function getPayload () {
		$array = explode('.', $this->token);
		return json_decode($this->JWTDecode($array[1]));
	}

	/**
  * Create JWT
  *
  * @access   public
	* @param 		array 	JWT payload data
  */
  public function create ($data) {
    $header = $this->JWTEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = $this->JWTEncode(json_encode($data));
		$signature = $this->JWTEncode(hash_hmac('sha256', $header . '.' . $payload, ZYTE_SECRET_JWT, true));
    $this->token = $header . '.' . $payload . '.' . $signature;
  }

	/**
  * Validate JWT
  *
  * @access   public
	* @param 		string 		JWT token
	* @return 	boolean 	JWT is valid
  */
  public function validate () {
    $array = explode('.', $this->token);
		$header = $array[0];
		$payload = $array[1];
    $signature = $this->JWTEncode(hash_hmac('sha256', $header . '.' . $payload, ZYTE_SECRET_JWT, true));
    return ($this->token === ($header . '.' . $payload . '.' . $signature)) ? true : false;
  }

	/**
  * Encode Base64 data
  *
  * @access   private
	* @param 		array 		Data to be encoded
	* @return 	string 		Encoded data
  */
  private function JWTEncode($data) {
		return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
	}

	/**
  * Decode Base64 data
  *
  * @access   private
	* @param 		string 		Data to be decoded
	* @return 	array 		Decoded data
  */
  private function JWTDecode($string) {
		return base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
	}

}
