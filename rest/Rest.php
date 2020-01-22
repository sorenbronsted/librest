<?php
namespace sbronsted;

use ReflectionClass;
use ErrorException;
use ReflectionMethod;
use RuntimeException;

class Rest {
  private $uri;
  private $arg;
  private $cls;
  private $uid;
	private $method;
	private static $dic;
  private static $allowedMethods = array("get", "delete", "post");

  /**
   * The uri can have the following forms:
   * 
   * /rest/class/uid            returns a object by the given object or delete it
   * 
   * /rest/class/uid/method     which returns the result of calling the method
   *                            on the object specified by the uid. Parameters to method are parsed as paramters
   *                                            
   * /rest/class/method         which returns the result of calling the static method on the class
   *                                            
   * /rest/class?name=value&... which return an array of objects which all qualifies with
   *                            the name-value pair set
   *                                            
   * /rest/class                will return object of the given cls or create or update it
   */
  public function __construct($uri, array $arg = array()) {
    set_error_handler(array(__CLASS__, 'throwError'), E_ALL | E_STRICT);
    
    $this->uri = $uri;
    $this->arg = $arg;
    $this->parseUri();
  }
  
  public static function throwError($errno, $errstr, $errfile = "", $errline = 0, $errcontext = null) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
  }

  public static function run($server, $request) {
  	if (!preg_match('#/rest/#', $server['REQUEST_URI'])) {
  		throw new RuntimeException('The uri must contain path element rest: '.$server['REQUEST_URI']);
	  }

    self::$dic = DiContainer::instance();
    try {
      self::$dic->header->out('Content-type: application/json');
      self::$dic->log->debug(__CLASS__, "request: ".str_replace("\n", '', var_export($request, true)));

      $requestMethod = strtolower($server['REQUEST_METHOD']);

      if (!in_array($requestMethod, self::$allowedMethods)) {
        throw new ErrorException("Unsupported request method $requestMethod");
      }
      if (isset($request['_'])) {
        unset($request['_']);
      }
      // eat of uri until we have an 'rest' part, which is the root part
      $parts = explode('/', $server['REQUEST_URI']);
      while ($parts[0] != 'rest') {
      	array_shift($parts);
      }
      $uri = '/'.implode('/', $parts);

	    self::$dic->log->debug(__CLASS__, "requestmethod: $requestMethod uri: $uri");
      self::authorize();
      $rest = new Rest($uri, $request);
      $result = $rest->$requestMethod();
      return Json::encode($result);
    }
    catch (ValidationException $e) {
      return json_encode(array("error" => $e->validations()));
    }
    catch (ApplicationException $e) {
      return json_encode(array("error" => $e->getMessage()));
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
      if (isset($this->method)) {
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

	    if (isset($this->method)) {
		    $result = $this->callStatic();
	    }
	    else if (count($this->arg) > 0) {
		    $result = $clazz::getBy($this->arg, empty($arguments) ? array() : array($arguments));
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

	  // Method case
    if (isset($this->method)) {
			$result = array();
			if(isset($this->uid) && !empty($this->uid)) {
				$object = $clazz::getByUid($this->uid);
				$result = $this->callMethod($object);
			}
			else {
				$result = $this->callStatic();
			}
			return $result;
		}

	  // Object case
	  $uid = 0;
		if (isset($this->arg['uid']) || isset($this->uid)) {
			$uid = isset($this->arg['uid']) ? $this->arg['uid'] : $this->uid;
		}
		if ($uid > 0) {
			$object = $clazz::getByUid($uid);
		}
		else {
			$object = new $clazz();
		}

		$object->setData($this->arg);
		$object->save();
    return (object)array("uid" => $object->uid);
  }

  private function parseUri() {
    if (empty($this->uri)) {
      throw new ErrorException("Invalid uri $this->uri");
    }
    
    $uri = preg_split("/\?/", $this->uri); // split argument from uri
    $tmp = preg_split("/\//", $uri[0]);    // split the uri into parts

    if (count($tmp) <= 2 || count($tmp) > 5) {
      throw new ErrorException("Invalid url $this->uri");
    }

    $name = self::$dic->config->rest_namespace;
    if (empty($name)) {
			throw new RuntimeException('You must provide name of namespace in section rest of config');
		}
		$this->cls = $name.'\\'.$tmp[2];
	  $inspect = new ReflectionClass($this->cls);
	  if (!in_array(RestEnable::class, $inspect->getInterfaceNames())) {
			throw new RuntimeException($this->cls.' does not implement RestEnable');
	  }

	  switch (count($tmp)) {
		  case 3:
				// Allready done
			  break;
		  case 4:
				if ($inspect->hasMethod($tmp[3])) {
					$this->method = $tmp[3];
				}
				else {
					$this->uid = $tmp[3];
				}
			  break;
		  case 5:
				$this->uid = $tmp[3];
				$this->method = $tmp[4];
			  break;
		  default:
			  throw new RuntimeException("url is to long");
	  }
  }
  
  private function callStatic() {
    $inspect = new ReflectionClass($this->cls);
    $method = $inspect->getMethod($this->method);
	  $this->orderArguments($method);
    return $method->invokeArgs(null, $this->arg);
  }

  private function callMethod($object) {
    $cls = get_class($object);
    $inspect = new ReflectionClass($cls);
    $method = $inspect->getMethod($this->method);
	  $this->orderArguments($method);
    return $method->invokeArgs($object, $this->arg);
  }

	private function orderArguments(ReflectionMethod $method) {
		$result = [];
		foreach ($method->getParameters() as $parameter) {
			$name = $parameter->getName();
			if (isset($this->arg[$name])) {
				$result[$name] = $this->arg[$name];
			}
			else {
				$result[$name] = null;
			}
		}
		$this->arg = $result;
	}

	private static function authorize() {
    if (self::$dic->restAuthenticator != null) {
	    if (!self::$dic->restAuthenticator instanceof AuthenticatorEnable) {
		    throw new RuntimeException('Must implement AuthenticatorEnable');
	    }
	    if (!self::$dic->restAuthenticator->hasAccess()) {
		    throw new RuntimeException('Access denied');
	    }
    }
  }

}
