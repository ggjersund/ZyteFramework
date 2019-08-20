<?php
/**
*
* ZyteFramework
*
* A slim open source REST API for PHP version greater than 5.5
*
* @package            ZyteFramework
* @author             Gjert Ingar Gjersund
* @copyright          Copyright (c) 2017 -  2017, ZyteFramework.
* @license            Open Source Licence
* @link               https://zyteframework.com
*
*/

namespace Zyte;

use Zyte\classes\ResponseException as ResponseException;
use Zyte\classes\HttpHeader as HttpHeader;

class Zyte {
  private $routes = [];
  private $cors;
  private $https = ZYTE_API_HTTPS;

  # CONSTRUCT SETTINGS
  public function __construct ($cors = NULL) { $this->cors = $cors; }

  # ADD ROUTE
  private function addRoute ($route, $methods) {
    $route['route'] = array_values(array_filter(explode('/', rtrim(ltrim($route['route'], '/'), '/'))));
    $route['https'] = isset($route['https']) ? $route['https'] : $this->https;
    $route['method'] = $methods;
    $route['controller'] = $route['controller'] ? $route['controller'] : NULL;
    array_push($this->routes, $route);
  }

  # DEFAULT ROUTE
  public function route ($route) {
    $route['method'] = $route['method'] ? $route['method'] : ['POST','PUT','GET','DELETE','HEAD','PATCH'];
    $this->addRoute($route, $route['method']);
  }

  # POST ROUTE
  public function post ($route) { $this->addRoute($route, ['POST']); }

  # GET ROUTE
  public function get ($route) { $this->addRoute($route, ['GET']); }

  # PUT ROUTE
  public function put ($route) { $this->addRoute($route, ['PUT']); }

  # DELETE ROUTE
  public function delete ($route) { $this->addRoute($route, ['DELETE']); }

  # HEAD ROUTE
  public function head ($route) { $this->addRoute($route, ['HEAD']); }

  # PATCH ROUTE
  public function patch ($route) { $this->addRoute($route, ['PATCH']); }

  # VALIDATE ROUTES, RETURN ALL VALID ROUTES
  private function validateRoute ($route, $path) {
    # NO PATH GIVEN
    if (!$path) {
      $c = 0;
      foreach ($route as $r) {
        if (isset($r['route'][0]) && ((strpos($r['route'][0], '[') !== 0) && (strpos($r['route'][0], ']') !== (strlen($r['route'][0]) - 1)))) { unset($route[$c]); }
        $c++;
      }
      return array_values($route);
    }
    # PATH GIVEN
    else {
      $rx = $route;
      $x = 0;
      foreach ($path as $p) {
        $c = 0;
        foreach ($route as $r) {

          # CREATE PARAMETER PATH IN ARRAY
          if (!empty($rx[$c])) {
            if (is_array($rx[$c])) {
              if (!array_key_exists('parameter', $rx[$c])) { $rx[$c]['parameter'] = []; }
            }
          }

          # IF PATH = ROUTE
          if (isset($r['route'][$x]) && $r['route'][$x] === $p) {
            $c++;
            continue;
          }
          # CHECK IF {} PARAMETER
          else if ((isset($r['route'][$x])) && (strpos($r['route'][$x], '{') === 0) && (strrpos($r['route'][$x], '}') === (strlen($r['route'][$x]) - 1))) {
            # VALIDATE PARAMETER
            if (strpos($r['route'][$x], ':')) {
              $regx = substr($r['route'][$x], strpos($r['route'][$x], ':') + 1, strlen($r['route'][$x]) - strpos($r['route'][$x], ':') - 2);
              if (!preg_match('/' . $regx . '/', $p)) {
                unset($rx[$c]);
                $c++;
                continue;
              }

              # ADD TO PARAMETER LIST
              if (array_key_exists($c, $rx) && is_array($rx[$c])) {
                $rx[$c]['parameter'][(substr($r['route'][$x], 1 , strpos($r['route'][$x], ':') - 1))] = $p;
              }

            } else {

              # ADD TO PARAMETER LIST
              if (array_key_exists($c, $rx) && is_array($rx[$c])) {
                $rx[$c]['parameter'][(substr($r['route'][$x], 1, strlen($r['route'][$x]) - 2))] = $p;
              }

            }
            $c++;
            continue;
          }
          # CHECK IF [] OPTIONAL PARAMETER
          else if ((isset($r['route'][$x])) && (strpos($r['route'][$x], '[') === 0) && (strrpos($r['route'][$x], ']') === (strlen($r['route'][$x]) - 1))) {
            # VALIDATE OPTIONAL PARAMETER
            if (strpos($r['route'][$x], ':')) {
              if (!strpos($r['route'][$x], ':.*')) {
                $regx = substr($r['route'][$x], strpos($r['route'][$x], ':') + 1, strlen($r['route'][$x]) - strpos($r['route'][$x], ':') - 2);
                if (!preg_match('/' . $regx . '/', $p)) {
                  unset($rx[$c]);
                  $c++;
                  continue;
                }

                # ADD TO PARAMETER LIST
                if (!empty($rx[$c])) {
                  if (array_key_exists($c, $rx) && is_array($rx[$c])) {
                    $rx[$c]['parameter'][(substr($r['route'][$x], 1 , strpos($r['route'][$x], ':') - 1))] = $p;
                  }
                }
              } else {

                # ADD TO PARAMETER LIST
                if (array_key_exists($c, $rx) && is_array($rx[$c])) {
                  $rx[$c]['parameter'][(substr($r['route'][$x], 1 , strpos($r['route'][$x], ':') - 1))] = [$p];
                }
              }
            } else {

              # ADD TO PARAMETER LIST
              if (array_key_exists($c, $rx) && is_array($rx[$c])) {
                $rx[$c]['parameter'][(substr($r['route'][$x], 1, strlen($r['route'][$x]) - 2))] = $p;
              }
            }
            $c++;
            continue;
          }
          # CHECK IF EMPTY AND LAST ROUTE IS [] OPTIONAL UNLIMITED PARAMETER
          else if ((!isset($r['route'][$x]))
            && (strpos($r['route'][count($r['route']) - 1], '[') === 0)
            && (strrpos($r['route'][count($r['route']) - 1], ']') === (strlen($r['route'][count($r['route']) - 1]) - 1))) {
              if (!strpos($r['route'][count($r['route']) - 1], ':.*')) {
                unset($rx[$c]);
              } else {
                # ADD TO PARAMETER LIST
                if (array_key_exists($c, $rx) && is_array($rx[$c])) {
                  array_push($rx[$c]['parameter'][(substr($r['route'][count($r['route']) - 1], 1 , strpos($r['route'][count($r['route']) - 1], ':') - 1))], $p);
                }
              }
              $c++;
              continue;
          }
          # IF NO MATCHES -> UNSET
          else {
            unset($rx[$c]);
            $c++;
            continue;
          }
        }
        $x++;
      }
      # REMOVE ROUTES THAT ARE TO LONG AND INVALID
      $c = 0;
      $routes = array_values($rx);
      foreach ($rx as $r) {
        if (count($r['route']) > count($path)) {
          if ((strpos($r['route'][count($path)], '[') !== 0)
            && (strpos($r['route'][count($path)], ']') !== (strlen($r['route'][count($path)] - 1)))) {
              unset($routes[$c]);
          }
        }
        $c++;
      }
      # RETURN VALID ROUTES
      return array_values($routes);
    }
  }

  # VALIDATE METHOD, RETURN ALL ROUTES WITH VALID METHODS
  private function validateMethod ($route) {
    $c = 0;
    foreach ($route as $r) {
      if (!in_array($_SERVER['REQUEST_METHOD'], $r['method'])) {
        unset($route[$c]);
      }
      $c++;
    }
    return array_values($route);
  }

  # CHECK IF OPTIONS METHOD
  private function optionsMethod () {
    return $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ? true : false;
  }

  # RUN CONTROLLER
  private function runController ($controller, $parameter) {

    # CHECK IF CONTROLLER EXIST
    if ($controller) {

      # IS CONTROLLER FUNCTION
      if (is_callable($controller) && $controller instanceof Closure) {
        return $controller($parameter);
      }

      # IS CONTROLLER CLASS
      else {

        # CONTROLLER CLASS
        $class = 'controllers\\' . $controller;
        if (class_exists($class)) {
          $object = new $class($parameter);
          return $object();
        } else {
          return false;
        }
      }
    } else { return false; }
  }

  # CORS MANAGEMENT - ALLOWED METHODS
  private function allowedMethods ($route) {
    $methods = [];
    foreach ($route as $r) {
      foreach ($r['method'] as $m) {
        if (!in_array($m, $methods)) { array_push($methods, $m); }
      }
    }
    return $methods;
  }

  # CORS MANAGEMENT - ALLOWED ORIGINS
  private function allowedOrigins ($allowed) {
    if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
      return (array_key_exists($_SERVER['HTTP_ORIGIN'], $allowed)) ? $_SERVER['HTTP_ORIGIN'] : 'NULL';
    }
  }

  # CORS MANAGEMENT - ALLOWED HEADERS
  private function allowedHeaders ($allowed) {
    if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
      return $allowed[$_SERVER['HTTP_ORIGIN']]['HEADERS'];
    }
  }

  # CORS MANAGEMENT - ALLOW CREDENTIALS
  private function allowedCredentials ($allowed) {
    if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
      return $allowed[$_SERVER['HTTP_ORIGIN']]['CREDENTIALS'];
    }
  }

  # CORS MANAGEMENT - SET HEADERS
  private function corsHeaders ($allowed) {
    header('Access-Control-Allow-Origin: ' . $this->allowedOrigins($allowed));
    header('Access-Control-Allow-Headers: ' . $this->allowedHeaders($allowed));
    header('Access-Control-Allow-Credentials: ' . $this->allowedCredentials($allowed));
  }

  # ROUTE RENDERING
  public function render () {
    try {

      # SET CORS HEADERS
      if ($this->cors) { $this->corsHeaders($this->cors); }

      # CHECK IF URL CLASS EXISTS
      $url = new classes\Url();

      # FIND VALID ROUTES
      $route = $this->validateRoute($this->routes, $url->path());

      if ($route) {

        # IF OPTIONS METHOD, ALSO RETURN ALLOWED METHODS
        if ($this->optionsMethod()) {

          $header = new HttpHeader();
          $header->setResponseHeader([
            'X-Frame-Options: SAMEORIGIN',
            'X-XSS-Protection: 1; mode=block',
            'X-Content-Type-Options: "nosniff"',
            'Content-Type: application/json; charset=UTF-8',
            'Date: ' . gmdate('D, d M Y H:i:s T'),
            'Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods($route))
          ]);
          $header->setResponseTls();
          exit();

        }

        # FIND VALID METHODS
        $route = $this->validateMethod($route);
        if ($route) {

          # CHECK IF MORE THAN 1 ROUTE AVAILABLE
          if (count($route) === 1) {

            # CHECK IF ACCORDING TO PROTOCOL
            if (($route[0]['https'] === $url->secure()) or ($route[0]['https'] === false)) {

              # CHECK IF XSRF CLASS EXIST
              $xsrf = new classes\XSRF();

              # VALIDATE XSRF COOKIE
              if ($xsrf->validate()) {

                # SET HEADERS
                $header = new HttpHeader();
                $header->setResponseHeader([
                  'X-Frame-Options: SAMEORIGIN',
                  'X-XSS-Protection: 1; mode=block',
                  'X-Content-Type-Options: "nosniff"',
                  'Content-Type: application/json; charset=UTF-8',
                  'Date: ' . gmdate('D, d M Y H:i:s T')
                ]);
                $header->setResponseCode(200);
                if ($route[0]['https']) { $header->setResponseTls(); }

                # RUN CONTROLLER
                $object = $this->runController($route[0]['controller'], $route[0]['parameter']);

                # RETURN OBJECT
                echo ")]}',\n" . json_encode($object);

              } else {
                throw new ResponseException(403, 'Cross-site Scripting Cookie invalid', 'XSRF Cookie invalid', 1005, '1005');
              }
            } else {
              throw new ResponseException(403, 'Invalid protocol. Please change to HTTPS', 'Not connecting through HTTPS', 1004, '1004');
            }
          } else {
            throw new ResponseException(409, 'Conflicting methods detected', 'There is more than one existing route that can be accessed through this method', 1003, '1003');
          }
        } else {
          throw new ResponseException(405, 'Method is not valid for this request', 'The requested data can not be accessed through this method', 1002, '1002');
        }
      } else {
        throw new ResponseException(404, 'Site not found', 'This route does not exist, or does not match the correct required parametric pattern', 1001, '1001');
      }
    }
    # CATCH ERRORS
    catch (ResponseException $e) {

      # ERROR CLASS
      $error = [
        'status' => $e->exception()['status'],
        'message' => $e->exception()['status_msg'],
        'developer' => $e->exception()['dev_msg'],
        'code' => $e->exception()['code'],
        'url' => 'https://www.example.com/feilmeldinger/' . $e->exception()['href'],
        'exception' => $e
      ];

      # SET HEADERS
      $header = new HttpHeader();
      $header->setResponseHeader([
        'X-Frame-Options: SAMEORIGIN',
        'X-XSS-Protection: 1; mode=block',
        'X-Content-Type-Options: "nosniff"',
        'Content-Type: application/json; charset=UTF-8',
        'Date: ' . gmdate('D, d M Y H:i:s T')
      ]);
      $header->setResponseTls();
      $header->setResponseCode($e->exception()['status']);

      # RETURN ERROR
      echo ")]}',\n" . json_encode($error, JSON_UNESCAPED_SLASHES);

      # EXIT SCRIPT
      exit();

    }
  }
};
