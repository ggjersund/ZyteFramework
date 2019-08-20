<?php
/**
*
* The ZyteFrameworks Email class
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

class Email {
  private $object;

  public function __construct () {
    $this->object = new PHPMailer;
    $this->object->SMTPDebug = 2;
    if (ZYTE_EMAIL_SMTP) {
      $this->object->isSMTP();
      $this->object->Host = ZYTE_EMAIL_HOST;
      if (ZYTE_EMAIL_AUTH) {
        $this->object->SMTPAuth = ZYTE_EMAIL_AUTH;
        $this->object->Username = ZYTE_EMAIL_USERNAME;
        $this->object->Passowrd = ZYTE_EMAIL_PASSWORD;
        if (ZYTE_EMAIL_SECURE) {
          $this->object->SMTPSecure = ZYTE_EMAIL_SECURE;
          $this->object->Port = ZYTE_EMAIL_PORT;
        }
      }
    }
  }
  public function subject ($subject) {
    $this->object->Subject = $subject;
  }
  public function body ($body) {
    $content = '<div style="background-color: #eef2f3; padding: 10px;">';
    $content .= '<div style="margin: 10px auto 10px auto; padding: 10px; text-align: center; background-color: #ffffff; border: 1px solid #dddddd;">Logo kommer snart...</div>';
    $content .= '<div style="margin: 2px auto 10px auto; padding: 10px; background-color: #ffffff; border: 1px solid #dddddd;">';
    $content .= $body;
    $content .= '</div>';
    $content .= '<div style="margin: 10px auto 10px auto; padding: 2px; text-align: center; background-color: #ffffff; border: 1px solid #dddddd;"><p style="font-size:0.8em;color:#6d6d6d;">Copyright &#169; ZyteFramework</p></div>';
    $content .= '</div>';
    $this->object->Body = $content;
  }
  public function charset ($charset) {
    $this->object->CharSet = $charset;
  }
  public function altBody ($altbody) {
    $this->object->AltBody = $altbody;
  }
  public function setFrom ($email, $name) {
    $this->object->setFrom($email, $name);
  }
  public function addAddress ($email, $name) {
    $this->object->addAddress($email, $name);
  }
  public function addReplyTo ($email, $name) {
    $this->object->addReplyTo($email, $name);
  }
  public function addCC ($email) {
    $this->object->addCC($email);
  }
  public function addBCC ($email) {
    $this->object->addBCC($email);
  }
  public function isHTML ($html) {
    $this->object->isHTML($html);
  }
  public function send () {
    if ($this->object->send()) {
      return true;
    } else {
      return $this->object->ErrorInfo;
    }
  }

}
