<?php
namespace controllers;

use Zyte\classes\ResponseException as ResponseException;
use Zyte\classes\Controller as Controller;
use Zyte\classes\Query as Query;
use Zyte\classes\Email as Email;
use models\UserModel as UserModel;
use models\VerificationModel as VerificationModel;

class VerifyRegisterController extends Controller {

  public function __invoke () {
    #var_dump($this->parameters);
    if (isset($this->parameters['id']) && isset($this->parameters['code'])) {
      $db = new Query();
      $db->connect();

      $verification = new VerificationModel($db);
      $object = $verification->getById($this->parameters['id'], 1, ['code', 'user', 'expire']);

      if ($object) {

        if ($object[0]->code === $this->parameters['code']) {

          if (strtotime($object[0]->expire) >= time()) {
            $userId = $object[0]->user;
            $userModel = new UserModel($db);

            $userModel->update(
              ['verified_date', 'verified'],
              [
                [date('Y-m-d H:i:s', time()), 'str'],
                [1, 'int'],
                [$userId, 'int']
              ]
            );
            $verification->delete($this->parameters['id'], 1);
            return true;

          } else {
            throw new ResponseException(403, 'Code has expired', 'Code has expired', 4009, '4009');
          }
        } else {
          throw new ResponseException(403, 'Code invalid', 'Invalid verification code', 4009, '4009');
        }
      } else {
        throw new ResponseException(400, 'Invalid ID', 'Verification code matching the id was not found', 4009, '4009');
      }
    } else {
      throw new ResponseException(404, 'Required parameters not set', 'Parameter does not meet the regex requirements or/and are not set', 4009, '4009');
    }

  }

}
