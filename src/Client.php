<?php

declare(strict_types=1);

namespace AsyncSoap;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use SoapClient;

/**
 * Class Client
 * @package AsyncSoap
 */
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

    /**
     * Client constructor.
     * @param       $wsdl
     * @param array $options
     * @throws \SoapFault
     */
    public function __construct($wsdl, array $options = [])
    {
        parent::__construct($wsdl, $options);
        $this->client = new \GuzzleHttp\Client();
    }

    /**
     * Add request to process asynchronously
     * @param string     $name Soap action or function name (i.e. AddInteger)
     * @param array      $arguments Arguments for function call (i.e. [['Arg1' => 1, 'Arg2' => 2]])
     * @param array|null $options
     * @param array|null $inputHeaders
     * @return $this
     */
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

    /**
     * Override parent method and set value to class properties to be
     * used for setting up async requests
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int    $version
     * @param int    $oneWay
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $oneWay = 0): string
    {
        $this->request  = $request;
        $this->location = $location;
        $this->action   = $action;
        $this->version  = $version;
        $this->oneWay   = $oneWay;
        return '';
    }

    /**
     * Process guzzle requests
     * @return array[]
     */
    public function process(): array
    {
        $requests = static function (array $requests) {
            foreach ($requests as $request) {
                yield $request;
            }
        };

        $responses = [];

        (new Pool($this->client, $requests($this->requests), [
            'concurrency' => 50,
            'fulfilled'   => static function (ResponseInterface $response) use (&$responses) {
                $responses[] = $response->getBody()->getContents();
            },
            'rejected'    => static function (RequestException $reason, $index) use (&$responses) {
                $response = $reason->getResponse();
                $responses[] = $response ? $response->getBody()->getContents() : $reason->getMessage();
            },
        ]))->promise()->wait(false);

        return $responses;
    }
}