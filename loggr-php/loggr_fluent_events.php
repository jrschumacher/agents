<?php

class Loggr_FluentEvent
{
	public $event;
	
	private $_logKey;
	private $_apiKey;

	public function __construct($logKey, $apiKey)
	{
		$this->_logKey = $logKey;
		$this->_apiKey = $apiKey;
		$this->event = new Loggr_Event();
	}

	public function post()
	{
		$client = new Loggr_LogClient($this->_logKey, $this->_apiKey);
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