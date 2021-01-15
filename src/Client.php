<?php

declare(strict_types=1);

namespace AsyncSoap;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use SoapClient;

class Client extends SoapClient
{
    /** @var array */
    private $requests = [];

    /** @var string */
    private $request;

    /** @var string */
    private $location;

    /** @var string */
    private $action;

    /** @var int */
    private $version;

    /** @var int|bool */
    private $oneWay;
    /** @var \GuzzleHttp\Client */
    private $client;

    public function __construct($wsdl, array $options = [])
    {
        parent::__construct($wsdl, $options);
        $this->client = new \GuzzleHttp\Client();
    }

    public function addRequest(
        string $name,
        array $arguments,
        array $options = null,
        array $inputHeaders = null
    ): self {
        $this->__soapCall($name, $arguments, $options, $inputHeaders);

        $headers = [
            'Content-Type' => 'text/xml; charset="utf-8"',
            'SOAPAction'   => $this->action,
        ];

        if ($inputHeaders) {
            $headers = array_merge($headers, $inputHeaders);
        }

        $this->requests[] = new Request(
            'POST',
            $this->location,
            $headers,
            $this->request
        );

        return $this;
    }

    public function __doRequest($request, $location, $action, $version, $oneWay = 0): string
    {
        $this->request  = $request;
        $this->location = $location;
        $this->action   = $action;
        $this->version  = $version;
        $this->oneWay   = $oneWay;
        return '';
    }

    public function process(): array
    {
        $requests = static function (array $requests) {
            foreach ($requests as $request) {
                yield $request;
            }
        };

        $responses = [];
        $errors    = [];

        (new Pool($this->client, $requests($this->requests), [
            'concurrency' => 50,
            'fulfilled'   => static function (ResponseInterface $r) use (&$responses) {
                $responses[] = (string) $r->getBody();
            },
            'rejected'    => static function (RequestException $reason, $index) use (&$errors) {
                $errors[] = $reason;
            },
        ]))->promise()->wait(false);

        return [$responses, $errors];
    }
}