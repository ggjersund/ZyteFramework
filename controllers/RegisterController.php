<?php
namespace controllers;

use Zyte\classes\ResponseException as ResponseException;
use Zyte\classes\Controller as Controller;
use Zyte\classes\Query as Query;
use Zyte\classes\Email as Email;
use models\UserModel as UserModel;
use models\VerificationModel as VerificationModel;

class RegisterController extends Controller {

  public function __invoke () {

    if (!isset($this->payload->hidden)
      && $this->payload->name
      && $this->payload->birthday
      && $this->payload->email
      && $this->payload->password1
      && $this->payload->password2
      && $this->payload->policy
    ) {

      if (filter_var($this->payload->email, FILTER_VALIDATE_EMAIL)
        && strlen($this->payload->password1) > 6
        && strlen($this->payload->password1) < 50
        && $this->payload->password1 === $this->payload->password2
        && $this->payload->policy === true
        && strtotime($this->payload->birthday) < time()
      ) {

        $db = new Query();
        $db->connect();
        $um = new UserModel($db);

        $users = $um->get(
          ['id'],
          ['email', 'deleted'],
          [
            [$this->payload->email, 'str'],
            [0, 'int']
          ]
        );

        if (!$users) {

          # ADD USER TO DB
          $um->put(
            $this->payload->name,
            date('Y-m-d H:i:s', strtotime($this->payload->birthday)),
            $this->payload->email,
            password_hash($this->payload->password1, PASSWORD_DEFAULT),
            (isset($this->payload->newsletter) ? 1 : 0)
          );

          # GET USER ID
          $userId = $db->lastInsertId();

          # CREATE VERIFICATION CODE
          $verification = new VerificationModel($db);
          $verificationCode = bin2hex(openssl_random_pseudo_bytes(30));
          $verificationExpire = date('Y-m-d H:i:s', strtotime('+1 hour'));
          $verification->put($verificationCode, $userId, 1, $verificationExpire);

          # GET VERIFICATION ID
          $verificationId = $db->lastInsertId();

          # SEND EMAIL FOR VERIFICATION
          $email = new Email();
          $email->charset('UTF-8');
          $email->setFrom('ikke-svar@example.com', 'ZyteFramework');
          $email->addAddress($this->payload->email, $this->payload->name);
          $email->subject('Verifiser din konto hos ZyteFramework');
          $email->isHTML(true);

          $content = '
            <p style="text-align: center">
              Velkommen som ny bruker hos ZyteFramework sitt prosjektsystem.<br>
              Du er snart ferdig med din registrering hos oss, alt vi trenger er å verifisere din e-postadresse.<br>
              Vennligst trykk på linken under for å verifisere din konto.
            </p>
            <div style="text-align:center;padding:8px;">
              <a style="background-color:#5cb85c;border: 1px solid #4cae4c;padding:8px;text-decoration:none;color:#fff;font-weight:bold;" href="https://example.com/verifiser-konto?id=' . $verificationId . '&code=' . $verificationCode . '">
                Verifiser konto
              </a>
            </div>';
          $email->body($content);
          $email->altBody('Her er din kontoverifikasjonskode: "' . $verificationCode . '" med verifikasjons id: "' . $verificationId . '". Vennligst gå til https://example.com/verifiser-konto/ for å verifisere din konto.');
          $sent = $email->send();

          if ($sent === true) {
            return true;
          } else {
            throw new ResponseException(500, 'Internal server error', $sent, 4009, '4009');
          }
        } else {
          throw new ResponseException(403, 'A user with that e-mail already exists', 'A user with that email already exists in the database', 4009, '4009');
        }
      } else {
        throw new ResponseException(400, 'Invalid input data', 'The input fields does not correspond to the required format', 4009, '4009');
      }
    } else {
      throw new ResponseException(400, 'Please fill in the required input fields', 'The request is missing required request payload', 4009, '4009');
    }
  }

}
