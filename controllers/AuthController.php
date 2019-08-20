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
use models\RoleModel as RoleModel;
use models\CompanyModel as CompanyModel;

class AuthController extends Controller {

  public function __invoke () {

    # JWT SENT VIA COOKIE
    if ($this->parameters['type'] === 'cookie') {

      $cookie = new Cookie();
      $token = $cookie->get('AUTH-TOKEN');

      if ($token) {
        $jwt = new JWT();
        $jwt->put($token);

        if ($jwt->validate()) {

          $db = new Query();
          $db->connect();

          $tm = new TokenModel($db);

          $data = $jwt->getPayload();

          $tokenDetails = $tm->get(['user', 'expire'], $data->token);

          if (!empty($tokenDetails)) {
            if (strtotime($tokenDetails[0]->expire) >= time()) {

              $remember = $data->remember ? [1, '+2 months'] : [0, '+1 hour'];
              $expire = date('Y-m-d H:i:s', strtotime($remember[1]));

              $tm->update($data->token, $expire);

              $updatedData = [
                'iss' => $_SERVER['SERVER_NAME'],
                'exp' => $expire,
                'user' => $data->user,
                'remember' => $remember[0],
                'token' => $data->token
              ];

              # CREATE JWT
              $jwt->create($updatedData);
              $newToken = $jwt->get();

              # UPDATE COOKIE
              $cookieExpire = DateTime::createFromFormat('Y-m-d H:i:s', $expire)->getTimestamp();
              $cookie->put('AUTH-TOKEN', $newToken, $cookieExpire, '/', ZYTE_API_DOMAIN, true, true);

              # CHECK IF USER STILL EXIST
              $user = new UserModel($db);

              $userInfo = $user->get(
                ['name', 'email'],
                ['id'],
                [
                  [$data->user, 'int']
                ]
              );

              if ($userInfo) {

                $returnObject = [];

                # GET USER DETAILS
                if ($_GET['user'] === 'expand') {
                  $returnObject['user'] = $userInfo;
                }
                # GET USER ROLE TYPES
                if ($_GET['role'] === 'expand') {
                  $role = new RoleModel($db);
                  $roleInfo = $role->get($data->user, 0);
                  if ($roleInfo) {
                    $returnObject['role'] = $roleInfo;
                  }
                }
                # GET USERS COMPANY CONNECTIONS AND TYPE OF CONNECTION
                if ($_GET['company'] === 'expand') {
                  $company = new CompanyModel($db);
                  $companyInfo = $company->allowedAccess($data->user);
                  if ($companyInfo) {
                    $returnObject['company'] = $companyInfo;
                  }
                }
                if (empty($returnObject)) { $returnObject = true; }

                # RETURN OBJECT
                return $returnObject;
              } else {
                throw new ResponseException(401, 'Invalid authentication token', 'The token supplied has expired', 4009, '4009');
              }
            } else {
              throw new ResponseException(401, 'Token expired', 'The token supplied has expired', 4009, '4009');
            }
          } else {
            throw new ResponseException(401, 'Token has been deleted', 'The token is valid, but has been deleted', 4009, '4009');
          }
        } else {
          throw new ResponseException(401, 'Invalid authentication token', 'The auth token supplied is invalid', 4009, '4009');
        }
      } else {
        throw new ResponseException(401, 'Token is not set', 'Authentication token is not being sent with the request', 4009, '4009');
      }
    }
    # JWT SENT VIA HTTP HEADER
    else if ($this->parameters['type'] === 'header') {
      throw new ResponseException(500, 'Could not resolve tokens sent via header', 'Token authentication sent via auth headers have not yet been applied', 4009, '4009');
    }
    # INVALID PARAMETER
    else {
      throw new ResponseException(400, 'Invalid parameter', 'Parameter does not meet the regex requirements', 4009, '4009');
    }
  }

}
