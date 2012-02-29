<?php 

	//--- CONFIG ---//

	$log_key = "myfirstlog";
	$api_key = "db961642e48e48e4ab00ef60c90fa29e";
	
	//--- END CONFIG ---//

	require_once 'loggr.php';
	
	// creating class for using fluent syntax
	$loggr = new Loggr($log_key, $api_key);
	
	// create a simple event
	$loggr->Events->Create()
		->Text("Simple fluent event")
		->Post();
	
	// more complex event
	$world = "World";
	$loggr->Events->Create()
		->TextF("hello%s", $world)
		->Tags(array('tag1', 'tag2', 'tag3'))
		->Link("http://google.com")
		->Source("dave")
		->Data("foobar")
		->Value(3)
		->Geo(-14.456, 73.6879)
		->Post();
		
	// trace a variable
	$var = "TEST VAR";
	$loggr->Events->CreateFromVariable($var)
		->Text("Tracing TEST VAR")
		->Post();
	
	// trace an exception
	try {
		$error = 'Always throw this error';
		throw new Exception($error);
	} catch (Exception $e) {
		$loggr->Events->CreateFromException($e)
			->Text("Exception")
			->Post();
	}
	
	// alternatively you can use a non-fluent syntax
	$client = Loggr::LogClient($log_key, $api_key);
	
	// create a simple event
	$ev = Loggr::Event();
	$ev->Text = "Simple non-fluent event";
	$client->Post($ev);	

?>
