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

class LoginController extends Controller {

  public function __invoke () {

    if ($this->payload->email && $this->payload->password && !isset($this->payload->hidden)) {

      if (filter_var($this->payload->email, FILTER_VALIDATE_EMAIL)
      && strlen($this->payload->password) > 6) {

        $db = new Query();
        $db->connect();

        $user = new UserModel($db);
        $result = $user->get(
          ['id', 'password'],
          ['email', 'verified', 'deleted'],
          [
            [$this->payload->email, 'str'],
            [1, 'int'],
            [0, 'int']
          ]
        );

        if ($result) {

          $bf = new BruteforceModel($db);
          $bruteforce = $bf->get($result[0]->id, $_SERVER['REMOTE_ADDR']);

          if (empty($bruteforce) || (($bruteforce[0]->count) < 10) || (strtotime($bruteforce[0]->datetime) <= strtotime('-1 hour'))) {

            if (password_verify($this->payload->password, $result[0]->password)) {

              $remember = isset($this->payload->remember) ? [1, '+2 months'] : [0, '+1 hour'];
              $expire = date('Y-m-d H:i:s', strtotime($remember[1]));

              $tm = new TokenModel($db);

              $tm->put($result[0]->id, $expire, $remember[0]);

              $jwt = new JWT();
              $data = [
                'iss' => $_SERVER['SERVER_NAME'],
                'exp' => $expire,
                'user' => $result[0]->id,
                'remember' => $remember[0],
                'token' => $db->lastInsertId()
              ];

              $jwt->create($data);
              $token = $jwt->get();
              $parameter = $this->parameters;

              if ($parameter['type'] === 'cookie') {
                $cookieExpire = DateTime::createFromFormat('Y-m-d H:i:s', $expire)->getTimestamp();
                $cookie = new Cookie();
                $cookie->put('AUTH-TOKEN', $token, $cookieExpire, '/', ZYTE_API_DOMAIN, true, true);
                return true;
              }
              else if ($parameter['type'] === 'jwt') {
                return ['jwt' => $token];
              }
              else {
                throw new ResponseException(400, 'Invalid parameter', 'Parameter does not meet the regex requirements', 4009, '4009');
              }

            } else {

              if (empty($bruteforce)) {
                $bf->put($result[0]->id, $_SERVER['REMOTE_ADDR']);
              } else {
                if (strtotime($bruteforce[0]->datetime) <= strtotime('-1 hour')) {
                  $bf->reset($result[0]->id, $_SERVER['REMOTE_ADDR']);
                } else {
                  $bf->add($result[0]->id, $_SERVER['REMOTE_ADDR']);
                }
              }
              throw new ResponseException(401, 'Incorrect password', 'Password is incorrect', 4009, '4009');

            }
          } else {
            throw new ResponseException(403, 'To many login attempts. Please wait one hour or reset your password to unlock your account', 'Bruteforced login detected', 4008, '4008');
          }
        } else {
          throw new ResponseException(401, 'E-mail does not exist', 'Email does not exist in the database or the account is deleted', 4006, '4006');
        }
      } else {
        throw new ResponseException(400, 'Incorect input in input fields', 'The input does not match the required pattern', 4002, '4002');
      }
    } else {
      throw new ResponseException(400, 'Please fill in the required input fields', 'The request is missing required request payload', 4001, '4001');
    }
  }
}
