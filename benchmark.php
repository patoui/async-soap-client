<?php

require_once __DIR__ . '/vendor/autoload.php';

$start = microtime(true);
(new \AsyncSoap\CurlClient('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1'))
    ->addRequest('AddInteger', [['Arg1' => 1, 'Arg2' => 2]])
    ->addRequest('AddInteger', [['Arg1' => 3, 'Arg2' => 4]])
    ->addRequest('AddInteger', [['Arg1' => 5, 'Arg2' => 6]])
    ->addRequest('AddInteger', [['Arg1' => 7, 'Arg2' => 8]])
    ->addRequest('AddInteger', [['Arg1' => 9, 'Arg2' => 10]])
    ->process();
$end = microtime(true);
echo 'ASYNC CURL CLIENT PROCESS TIME: ' . ($end - $start) . PHP_EOL;

$start = microtime(true);
(new \AsyncSoap\Client('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1'))
    ->addRequest('AddInteger', [['Arg1' => 1, 'Arg2' => 2]])
    ->addRequest('AddInteger', [['Arg1' => 3, 'Arg2' => 4]])
    ->addRequest('AddInteger', [['Arg1' => 5, 'Arg2' => 6]])
    ->addRequest('AddInteger', [['Arg1' => 7, 'Arg2' => 8]])
    ->addRequest('AddInteger', [['Arg1' => 9, 'Arg2' => 10]])
    ->process();
$end = microtime(true);
echo 'ASYNC GUZZLE CLIENT PROCESS TIME: ' . ($end - $start) . PHP_EOL;

$start = microtime(true);
$client = new SoapClient('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1');
$client->AddInteger(['Arg1' => 1, 'Arg2' => 2]);
$client->AddInteger(['Arg1' => 3, 'Arg2' => 4]);
$client->AddInteger(['Arg1' => 5, 'Arg2' => 6]);
$client->AddInteger(['Arg1' => 7, 'Arg2' => 8]);
$client->AddInteger(['Arg1' => 9, 'Arg2' => 10]);
$end = microtime(true);
echo 'NATIVE CLIENT PROCESS TIME: ' . ($end - $start) . PHP_EOL;

die();