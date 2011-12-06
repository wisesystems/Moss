<?php
error_reporting(E_ALL | E_STRICT);

require __DIR__.'/../lib/Loader.php';
require __DIR__.'/../lib/ErrorHandler.php';
require __DIR__.'/../lib/response/ResponseInterface.php';
require __DIR__.'/../lib/response/Response.php';
require __DIR__.'/../lib/response/Response500.php';

if(version_compare(phpversion(), '6.0.0-dev', '<')) {
	function removeSlashes(&$value) {
		if(is_array($value)) {
			return array_map('removeSlashes', $value);
		}
		else {
			return stripslashes($value);
		}
	}

	if(get_magic_quotes_gpc()) {
		$_POST = array_map('removeSlashes', $_POST);
		$_GET = array_map('removeSlashes', $_GET);
		$_COOKIE = array_map('removeSlashes', $_COOKIE);
	}
}

try {
	$ErrorHandler = new \lib\ErrorHandler();
	$ErrorHandler->register();

	$nsFile = __DIR__.'/../settings/namespaces.xml';

	if(!is_file($nsFile)) {
		throw new RuntimeException('Namespace definitions can not be found');
	}

	$Loader = new \lib\Loader(null, '../');

	$xml = new \SimpleXMLElement(file_get_contents($nsFile));
	foreach($xml->children() as $i => $definition) {
		$Loader->addNamespace( (string) $definition->attributes()->namespace,  (string) $definition->attributes()->path);
	}

	//$Loader->registerMapper();
	$Loader->registerHandle();

	$Core = new \lib\Core();
	echo $Core->handle(new \lib\Request());
}
catch(\Exception $e) {
	echo new \lib\response\Response500(sprintf('Bad Moss: %s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
}