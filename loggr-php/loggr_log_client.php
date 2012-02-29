<?php

class Loggr_LogClient
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
			if ($event->dataType == Loggr_DataType::html)
				$res .= "&data=" . "@html\r\n" . urlencode($event->data);
			else
				$res .= "&data=" . urlencode($event->data);
		}
		
		return $res;
	}
}