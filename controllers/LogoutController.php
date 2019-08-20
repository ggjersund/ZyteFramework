<?php
namespace controllers;

use \DateTime as DateTime;
use Zyte\classes\ResponseException as ResponseException;
use Zyte\classes\Controller as Controller;
use Zyte\classes\Query as Query;
use Zyte\classes\JWT as JWT;
use Zyte\classes\Cookie as Cookie;
use models\BruteforceModel as BruteforceModel;
use models\UserModel as UserModel;
use models\TokenModel as TokenModel;

class LogoutController extends Controller {

  public function __invoke () {

    if ($this->parameters['type'] === 'cookie') {

      $cookie = new Cookie();
      $token = $cookie->get('AUTH-TOKEN');

      if ($token) {
        $jwt = new JWT();
        $jwt->put($token);

        if ($jwt->validate()) {

          $data = $jwt->getPayload();

          $db = new Query();
          $db->connect();

          $tm = new TokenModel($db);
          $tm->delete($data->token);

          $cookie->delete('AUTH-TOKEN', '/', ZYTE_API_DOMAIN, true, true);

          return true;

        } else {
          throw new ResponseException(403, 'Invalid authentication token', 'The authentication token served is invalid', 4009, '4009');
        }
      } else {
        throw new ResponseException(400, 'Token to remove is not set', 'The authentication token you are trying to logout is not sent with the request', 4009, '4009');
      }
    }
    else if ($this->parameters['type'] === 'header') {
      throw new ResponseException(500, 'Could not resolve logout via header', 'Logout via header authentication is not yet available', 4009, '4009');
    }
    else {
      throw new ResponseException(400, 'Invalid parameter', 'Parameter does not meet the regex requirements', 4009, '4009');
    }
  }

}
