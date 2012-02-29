<?php

class Loggr
{
	public $events;
	
	public function __construct($logKey, $apiKey)
	{
		$this->events = new Events($logKey, $apiKey);
	}
	
	public function trapExceptions()
	{
		set_error_handler(array($this, "errorHandler"));
		set_exception_handler(array($this, "exceptionHandler"));		
	}
	
	public function errorHandler($code, $message, $file, $line)
	{
		if ($code == E_STRICT && $this->reportESTRICT === false) return;
					
		ob_start();
		var_dump(debug_backtrace());
		$stack = str_replace("\n", "<br>", ob_get_clean());
		
		$data = "@html\r\n";
		$data .= "<b>MESSAGE:</b> " . $message . "<br>";
		$data .= "<b>FILE:</b> " . $file . ", " . $line . "<br>";
		$data .= "<b>CODE:</b> " . $code . "<br>";
		$data .= "<br><b>STACK TRACE:</b> " . $stack;
		
		$this->events->create()
			->text($message)
			->tags("error")
			->data($data)
			->post();
	}
	
	public function exceptionHandler($exception)
	{
		ob_start();
		var_dump($exception->getTrace());
		$stack = str_replace("\n", "<br>", ob_get_clean());
		
		$data = "@html\r\n";
		$data .= "<b>MESSAGE:</b> " . $exception->getMessage() . "<br>";
		$data .= "<b>FILE:</b> " . $exception->getFile() . ", " . $exception->getLine() . "<br>";
		$data .= "<b>CODE:</b> " . get_class($exception) . "<br>";
		$data .= "<br><b>STACK TRACE:</b> " . $stack;
		
		$this->events->create()
			->text($message)
			->tags("error exception")
			->data($data)
			->post();
	}


	public function __get($attribute) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			return $this->{$attribute};
		}
	}

	public function __set($attribute, $value) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			$this->{$attribute} = $value;
		}
	}
}

class Events
{
	private $_logKey;
	private $_apiKey;

	public function __construct($logKey, $apiKey)
	{
		$this->_logKey = $logKey;
		$this->_apiKey = $apiKey;
	}
	
	public function create()
	{
		return new FluentEvent($this->_logKey, $this->_apiKey);
	}

	public function createFromException($exception)
	{
		ob_start();
		var_dump($exception->getTrace(), 5);
		$stack = str_replace("\t", "----", str_replace("\n", "<br>", ob_get_clean()));
		
		$data = "<b>MESSAGE:</b> " . $exception->getMessage() . "<br>";
		$data .= "<b>FILE:</b> " . $exception->getFile() . ", " . $exception->getLine() . "<br>";
		$data .= "<b>CODE:</b> " . get_class($exception) . "<br>";
		$data .= "<br><b>BACK TRACE:</b> " . backtrace();
	
		return $this->create()
			->text($exception->getMessage())
			->tags("error " . get_class($exception))
			->data($data)
			->dataType(DataType::html);
	}

	public function createFromVariable($var)
	{
		ob_start();
		var_dump($var);
		$trace = str_replace("\t", "----", str_replace("\n", "<br>", ob_get_clean()));
		
		$data = "<pre>" . $trace . "</pre>";
	
		return $this->Create()
			->Data($data)
			->DataType(DataType::html);
	}

	public function __call($method, $params) {
		$method = lcfirst($method);
		if(method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}
	}
}

class FluentEvent
{
	public $event;
	
	private $_logKey;
	private $_apiKey;

	public function __construct($logKey, $apiKey)
	{
		$this->_logKey = $logKey;
		$this->_apiKey = $apiKey;
		$this->event = new Event();
	}

	public function post()
	{
		$client = new LogClient($this->_logKey, $this->_apiKey);
		$client->post($this->event);
		return $this;
	}

	public function text($text)
	{
		$this->event->text = $this->assignWithMacro($text, $this->event->text);
		return $this;
	}

	public function textF()
	{
		$args = func_get_args();
	    return $this->text(vsprintf(array_shift($args), array_values($args)));
	}
	
	public function addText($text)
	{
		$this->event->text .= $this->assignWithMacro($text, $this->event->text);
		return $this;
	}

	public function addTextF()
	{
		$args = func_get_args();
	    return $this->addText(vsprintf(array_shift($args), array_values($args)));
	}
	
	public function source($source)
	{
		$this->event->source = $this->assignWithMacro($source, $this->event->source);
		return $this;
	}

	public function sourceF()
	{
		$args = func_get_args();
	    return $this->source(vsprintf(array_shift($args), array_values($args)));
	}
	
	public function user($user)
	{
		$this->event->user = $this->assignWithMacro($user, $this->event->user);
		return $this;
	}

	public function userF()
	{
		$args = func_get_args();
	    return $this->user(vsprintf(array_shift($args), array_values($args)));
	}

	public function link($link)
	{
		$this->event->link = $this->assignWithMacro($link, $this->event->link);
		return $this;
	}

	public function linkF()
	{
		$args = func_get_args();
	    return $this->link(vsprintf(array_shift($args), array_values($args)));
	}
	
	public function data($data)
	{
		$this->event->data = $this->assignWithMacro($data, $this->event->data);
		return $this;
	}

	public function dataF()
	{
		$args = func_get_args();
	    return $this->data(vsprintf(array_shift($args), array_values($args)));
	}
	
	public function addData($data)
	{
		$this->event->data .= $this->assignWithMacro($data, $this->event->data);
		return $this;
	}

	public function addDataF()
	{
		$args = func_get_args();
	    return $this->addData(vsprintf(array_shift($args), array_values($args)));
	}
	
	public function value($value)
	{
		$this->event->value = $value;
		return $this;
	}

	public function ValueClear()
	{
		$this->event->value = "";
		return $this;
	}

	public function tags($tags)
	{
		$this->event->tags = $tags;
		return $this;
	}

	public function addTags($tags)
	{
		$this->event->tags .= " " . $tags;
		return $this;
	}

	public function geo($lat, $lon)
	{
		$this->event->latitude = $lat;
		$this->event->longitude = $lon;
		return $this;
	}

	public function dataType($datatype)
	{
		$this->event->dataType = $datatype;
		return $this;
	}

	private function assignWithMacro($input, $baseStr)
	{
		return str_replace("$$", $baseStr, $input);
	}

	/**
	 * Call, magic method
	 * 
	 * Used for backwards compatibility
	 **/
	public function __call($method, $params) {
		$method = lcfirst($method);
		if(method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}
	}

	public function __get($attribute) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			return $this->{$attribute};
		}
	}

	public function __set($attribute, $value) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			$this->{$attribute} = $value;
		}
	}
}

class DataType
{
    const html = 0;
    const plaintext = 1;
}

class Event
{
	public $text;
	public $source;
	public $user;
	public $link;
	public $data;
	public $value;
	public $tags;
	public $latitude;
	public $longitude;
	public $dataType = DataType::plaintext;

	public function __get($attribute) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			return $this->{$attribute};
		}
	}

	public function __set($attribute, $value) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			$this->{$attribute} = $value;
		}
	}
}

class LogClient
{
	private $_logKey;
	private $_apiKey;

	function __construct($logKey, $apiKey)
	{
		$this->_logKey = $logKey;
		$this->_apiKey = $apiKey;
	}

	public function post($event)
	{
		// format data
		$qs = $this->createQuerystring($event);
		$data = "apikey=" . $this->_apiKey . "&" . $qs;
		
		// write without waiting for a response
		$fp = fsockopen('post.loggr.net', 80, $errno, $errstr, 30);	
		$out = "POST /1/logs/".$this->_logKey."/events HTTP/1.1\r\n";
		$out.= "Host: "."post.loggr.net"."\r\n";
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out.= "Content-Length: ".strlen($data)."\r\n";
		$out.= "Connection: Close\r\n\r\n";
		if (isset($data)) $out.= $data;
	
		fwrite($fp, $out);
		fclose($fp);
	}
	
	public function createQuerystring($event)
	{
		$res = "";
		$res .= "text=" . urlencode($event->text);
		if (isset($event->source)) $res .= "&source=" . urlencode($event->source);
		if (isset($event->user)) $res .= "&user=" . urlencode($event->user);
		if (isset($event->link)) $res .= "&link=" . urlencode($event->link);
		if (isset($event->value)) $res .= "&value=" . urlencode($event->value);
		if (isset($event->tags)) $res .= "&tags=" . urlencode($event->Tags);
		if (isset($event->latitude)) $res .= "&lat=" . urlencode($event->latitude);
		if (isset($event->longitude)) $res .= "&lon=" . urlencode($event->longitude);
		
		if (isset($event->data))
		{
			if ($event->dataType == DataType::html)
				$res .= "&data=" . "@html\r\n" . urlencode($event->data);
			else
				$res .= "&data=" . urlencode($event->data);
		}
		
		return $res;
	}
}

function backtrace()
{
    $output = "<div style='text-align: left; font-family: monospace;'>\n";
    $backtrace = debug_backtrace();

    foreach ($backtrace as $bt) {
        $args = '';
        foreach ($bt['args'] as $a) {
            if (!empty($args)) {
                $args .= ', ';
            }
            switch (gettype($a)) {
            case 'integer':
            case 'double':
                $args .= $a;
                break;
            case 'string':
                $a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
                $args .= "\"$a\"";
                break;
            case 'array':
                $args .= 'Array('.count($a).')';
                break;
            case 'object':
                $args .= 'Object('.get_class($a).')';
                break;
            case 'resource':
                $args .= 'Resource('.strstr($a, '#').')';
                break;
            case 'boolean':
                $args .= $a ? 'True' : 'False';
                break;
            case 'NULL':
                $args .= 'Null';
                break;
            default:
                $args .= 'Unknown';
            }
        }
        $output .= "<br />\n";
        $output .= "<b>file:</b> {$bt['line']} - {$bt['file']}<br />\n";
        $output .= "<b>call:</b> ".(isset($bt['class'])?$bt['class']:'').(isset($bt['type'])?$bt['type']:'').(isset($bt['function'])?$bt['function']:'')."($args)<br />\n";
    }
    $output .= "</div>\n";
    return $output;
}