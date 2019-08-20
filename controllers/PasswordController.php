<?php
namespace controllers;

use Zyte\classes\ResponseException as ResponseException;
use Zyte\classes\Controller as Controller;
use Zyte\classes\Query as Query;
use Zyte\classes\Email as Email;
use models\UserModel as UserModel;
use models\VerificationModel as VerificationModel;

class PasswordController extends Controller {

  public function __invoke () {

    if ($this->parameters['action'] === 'forgot') {
      if (isset($this->payload->email) && !isset($this->payload->hidden)) {
        if (filter_var($this->payload->email, FILTER_VALIDATE_EMAIL)) {
          $db = new Query();
          $db->connect();

          $um = new UserModel($db);
          $user = $um->get(
            ['id'],
            ['email', 'verified', 'deleted'],
            [
              [$this->payload->email, 'str'],
              [1, 'int'],
              [0, 'deleted']
            ]
          );
          if ($user) {

            # CREATE VERIFICATION CODE
            $verification = new VerificationModel($db);
            # DELETE ALL PREVIOUS CODES
            $verification->deleteExisting($user[0]->id, 2);
            $verificationCode = bin2hex(openssl_random_pseudo_bytes(30));
            $verificationExpire = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $verification->put($verificationCode, $user[0]->id, 2, $verificationExpire);

            # GET VERIFICATION ID
            $verificationId = $db->lastInsertId();

            # SEND EMAIL FOR VERIFICATION
            $email = new Email();
            $email->charset('UTF-8');
            $email->setFrom('ikke-svar@example.com', 'ZyteFramework);
            $email->addAddress($this->payload->email, $this->payload->name);
            $email->subject('Tilbakestilling av passordet ditt');
            $email->isHTML(true);
            $content = '
              <h2 style="text-align:center">Tilbakestilling av passordet ditt</h2>
              <p style="text-align:center">
                Du har sendt en etterspørsel om tilbakestilling av passordet ditt.<br>
                For å endre passordet må du trykke på linken under.<br>
                Vi gjør oppmerksom på at linken kun er gyldig over en kort periode,<br>og at det ved flere etterspørsler kun er den siste e-posten som er gyldig.
              </p>
              <br>
              <div style="text-align:center;padding:8px;">
                <a style="background-color:#5cb85c;border: 1px solid #4cae4c;padding:8px;text-decoration:none;color:#fff;font-weight:bold;" href="https://example.com/nytt-passord?id=' . $verificationId . '&code=' . $verificationCode . '">
                  Tilbakestill passord
                </a>
              </div>
              <br>
              <p style="text-align:center">
              Fungerer ikke linken?<br>
              Du kan også gå til https://example.com/nytt-passord<br>
              og fylle inn din unike kombinasjon sammen med ønsket passord.<br>
              </p>
              <p style="text-align:center"><b>ID: </b>' . $verificationId . '<br><b>Kode: </b>' . $verificationCode . '</p>';
            $email->body($content);
            $email->altBody('Her er din id-kode for tilbakestilling av passord: "' . $verificationCode . '". Koden din er: "' . $verificationId . '". Vennligst gå til https://example.com/nytt-passord for å endre ditt passord.');
            $sent = $email->send();

            if ($sent === true) {
              return true;
            } else {
              throw new ResponseException(500, 'Internal server error', $sent, 4009, '4009');
            }
          } else {
            throw new ResponseException(403, 'No user found', 'No user with request email has been found', 4009, '4009');
          }
        } else {
          throw new ResponseException(403, 'Invalid email adress', 'The email adress supplied is invalid', 4009, '4009');
        }
      } else {
        throw new ResponseException(400, 'Invalid input', 'Missing input fields and/or hidden input field is set', 4009, '4009');
      }
    }
    else if ($this->parameters['action'] === 'new') {
      if (isset($this->payload->id)
      && isset($this->payload->code)
      && isset($this->payload->password1)
      && isset($this->payload->password2)
      && !isset($this->payload->hidden)
      ) {
        if (($this->payload->password1 === $this->payload->password2)
        && strlen($this->payload->password1) > 6
        && strlen($this->payload->password1) < 50) {

          $db = new Query();
          $db->connect();

          # CHECK VERIFICATION CODE
          $verification = new VerificationModel($db);
          $object = $verification->getById($this->payload->id, 2, ['code', 'user', 'expire']);

          if ($object) {

            if ($object[0]->code === $this->payload->code) {

              if (strtotime($object[0]->expire) >= time()) {
                $userId = $object[0]->user;
                $userModel = new UserModel($db);

                $userModel->update(
                  ['password'],
                  [
                    [password_hash($this->payload->password1, PASSWORD_DEFAULT), 'str'],
                    [$userId, 'int']
                  ]
                );
                $verification->delete($this->payload->id, 2);
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
          throw new ResponseException(400, 'Invalid password', 'Password does not meet the requirements', 4009, '4009');
        }
      } else {
        throw new ResponseException(400, 'Invalid inputs', 'All fields must be set as required', 4009, '4009');
      }
    }
    else if ($this->parameters['action'] === 'update') {
      # AUTH CODE REQUIRED
    }
    else {
      throw new ResponseException(404, 'Invalid parameter', 'Parameter does not meet the regex requirements', 4009, '4009');
    }

  }

}
