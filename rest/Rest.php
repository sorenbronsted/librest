<?php

class Rest {
  private $uri;
  private $arg;
  private $cls;
  private $uid;
  private static $allowedMethods = array("get", "delete", "post");

  /**
   * The uri can have the following forms:
   * 
   * /rest/class/uid                            returns a object by the given object or delete it
   * 
   * /rest/class/uid?method=method_name         which returns the result of calling the method
   *                                            on the object specified by the uid
   *                                            
   * /rest/class?method=method_name             which returns the result of calling the static method
   *                                            on the class
   *                                            
   * /rest/class?name=value...                  which return an array of objects which all qualifies with
   *                                            the name-value pair set
   *                                            
   * /rest/class                                will return object of the given cls or create or update it
   */
  public function __construct($uri, array $arg = array()) {
    set_error_handler(array(__CLASS__, 'throwError'), E_ALL | E_STRICT);
    
    $this->uri = $uri;
    $this->arg = $arg;
    $this->parseUri($uri);
  }
  
  public static function throwError($errno, $errstr, $errfile = "", $errline = 0, $errcontext = null) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
  }

  public static function run($server, $request) {
    $dic = DiContainer::instance();
    try {
      $dic->header->out('Content-type: application/json');
      $dic->log->debug(__CLASS__, "request: ".str_replace("\n", '', var_export($request, true)));

      $requestMethod = strtolower($server['REQUEST_METHOD']);

      if (!in_array($requestMethod, self::$allowedMethods)) {
        throw new ErrorException("Unsupported request method $requestMethod");
      }
      if (isset($request['_'])) {
        unset($request['_']);
      }
      self::authorize();
      $rest = new Rest($server['REQUEST_URI'], $request);
      $result = $rest->$requestMethod();
      return Json::encode($result);
    }
    catch (ValidationException $e) {
      return json_encode(array("error" => $e->errors()));
    }
    catch (ApplicationException $e) {
      return json_encode(array("error" => $e->getMessage()));
    }
    catch(RuntimeException $e) {
      $dic->log->error(__CLASS__, $e->getMessage());
	    $dic->header->out($server['SERVER_PROTOCOL']. " 500 ".$e->getMessage());
    }
    catch(ErrorException $e) {
      $dic->log->error(__CLASS__, $e->getMessage());
      $dic->log->error(__CLASS__, $e->getTraceAsString());
      $dic->header->out($server['SERVER_PROTOCOL']. " 500 ".$e->getMessage());
    }
  }

  public function get() {
    $result = null;
    $clazz = $this->cls;
    
    if ($this->uid) {
      $result = $clazz::getByUid($this->uid);
      if (!$result) {
        throw new ErrorException("No object found for uid: $this->uri");
      }
      if (count($this->arg) > 0 && isset($this->arg['method'])) {
	      $result = $this->callMethod($result);
      }
    }
    else {
    	$i = 0;
    	foreach(array('orderby', 'order', 'groupby', 'limit') as $x) {
    		if(array_key_exists($x, $this->arg)) {
    			$i++;
    		}
    	}
    	
    	$arguments = '';
    	$arguments .= (array_key_exists('order', $this->arg) && array_key_exists('orderby', $this->arg) ? ' '.$this->arg['orderby'].' '.strtoupper($this->arg['order']) : '');
    	$arguments .= (array_key_exists('groupby', $this->arg) ? ' GROUP BY '.$this->arg['groupby'] : '');
    	$arguments .= (array_key_exists('limit', $this->arg) ? ' LIMIT '.$this->arg['limit'] : '');
    	
    	foreach(array('orderby', 'order', 'groupby', 'limit') as $x) {
    		if(array_key_exists($x, $this->arg)) {
    			unset($this->arg[$x]);
    		}
    	}
    	
      if (count($this->arg) > 0) {
        if (array_key_exists("method", $this->arg)) {
          $result = $this->callStatic();
        }
        else {
            $result = $clazz::getBy($this->arg, empty($arguments) ? array() : array($arguments));
        }
      }
      else {
        $result = $clazz::getAll(empty($arguments) ? array() : array($arguments));
      }
    }
    return $result;
  }
  
  public function delete() {
    if (!($this->cls || $this->uid)) {
      throw new ErrorException("http.delete invalid uri $this->uri");
    }
    $clazz = $this->cls;
    $object = $clazz::getByUid($this->uid);
    $object->destroy();
  }
  
  public function post() {
    if (!$this->cls && !count($this->arg)) {
      throw new ErrorException("http.post invalid uri $this->uri");
    }
	  $clazz = $this->cls;
    $object = null;

    if (isset($this->uid)) {
      $object = $clazz::getByUid($this->uid);
    }
    else {
      $object = new $clazz();
    }
	  $object->setData($this->arg);
    $object->save();
    return (object)array("uid" => $object->uid);
  }

  private function parseUri() {
		DiContainer::instance()->log->debug(__CLASS__, "uri: $this->uri");
    if (empty($this->uri)) {
      throw new ErrorException("Invalid uri $this->uri");
    }
    
    $uri = preg_split("/\?/", $this->uri); // split argument from uri
    $tmp = preg_split("/\//", $uri[0]);    // split the uri into parts

    if (count($tmp) < 2 || count($tmp) > 4) {
      throw new ErrorException("Invalid uri $this->uri");
    }
    
    $this->cls = $tmp[2];
	  $inspect = new ReflectionClass($this->cls);
	  if (!in_array('RestEnable', $inspect->getInterfaceNames())) {
			throw new RuntimeException($this->cls.' does not implement RestEnable');
	  }

    if (count($tmp) > 3) {
      $this->uid = $tmp[3];
    }
  }
  
  private function callStatic() {
    $name = $this->arg["method"];
    unset($this->arg["method"]);
    $inspect = new ReflectionClass($this->cls);
    $method = $inspect->getMethod($name);
    return $method->invokeArgs(null, $this->arg);
  }

  private function callMethod($object) {
    $name = $this->arg["method"];
    unset($this->arg["method"]);
    $cls = get_class($object);
    $inspect = new ReflectionClass($cls);
    $method = $inspect->getMethod($name);
    return $method->invokeArgs($object, $this->arg);
  }

  private static function authorize() {
    try {
      $dic = DiContainer::instance();
      if (isset($dic->sso)) {
        $dic->sso->challengeCookie($dic->sso_cookieName);
      }
    }
    catch (NotAuthorizedException $e) {
      throw new AccessDeniedException();
    }
  }

}
