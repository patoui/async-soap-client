# Async Soap Client
Simple Async Soap Client, one client uses cURL (CurlClient) and one uses Guzzle (Client)

## Usage

```php
(new \AsyncSoap\CurlClient('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1'))
    ->addRequest('AddInteger', [['Arg1' => 1, 'Arg2' => 2]])
    ->addRequest('AddInteger', [['Arg1' => 3, 'Arg2' => 4]])
    ->process();
    
// OR

(new \AsyncSoap\Client('https://www.crcind.com/csp/samples/SOAP.Demo.CLS?WSDL=1'))
    ->addRequest('AddInteger', [['Arg1' => 1, 'Arg2' => 2]])
    ->addRequest('AddInteger', [['Arg1' => 3, 'Arg2' => 4]])
    ->process();
```

## Benchmarks

There's a file to demonstrate benchmarks comparing cURL vs Guzzle vs native SoapClient. You may run the benchmarks yourself with `php benchmark.php`.

Here are some results:

```bash
php benchmark.php
ASYNC CURL CLIENT PROCESS TIME: 0.71027994155884
ASYNC GUZZLE CLIENT PROCESS TIME: 0.73910284042358
NATIVE CLIENT PROCESS TIME: 1.1765689849854

php benchmark.php 
ASYNC CURL CLIENT PROCESS TIME: 0.72476601600647
ASYNC GUZZLE CLIENT PROCESS TIME: 0.79913091659546
NATIVE CLIENT PROCESS TIME: 1.2507350444794

php benchmark.php
ASYNC CURL CLIENT PROCESS TIME: 0.71018481254578
ASYNC GUZZLE CLIENT PROCESS TIME: 0.74799108505249
NATIVE CLIENT PROCESS TIME: 1.2563538551331
```
