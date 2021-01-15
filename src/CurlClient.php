<?php

declare(strict_types=1);

namespace AsyncSoap;

use RuntimeException;
use SoapClient;

class CurlClient extends SoapClient
{
    /** @var false|resource */
    private $multi_handler;

    /** @var array */
    private $handlers;

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

    public function __construct($wsdl, array $options = [])
    {
        parent::__construct($wsdl, $options);
        if (!$this->multi_handler = curl_multi_init()) {
            throw new RuntimeException('Unable to initialize curl multi handler');
        }
    }

    public function addRequest(
        string $name,
        array $arguments,
        array $options = null,
        array $inputHeaders = null
    ): self {
        $this->__soapCall($name, $arguments, $options, $inputHeaders);

        $content_length = strlen($this->request);

        $ch = curl_init();

        $opts = [
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_URL            => $this->location,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $this->request,
            CURLOPT_HTTPHEADER     => [
                'Accept: text/xml',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
                'Content-Type: text/xml;charset="utf-8"',
                "SOAPAction: {$this->action}",
                "Content-Length: {$content_length}",
            ],
        ];

        curl_setopt_array($ch, $opts);

        if (isset($options['curl'])) {
            $opts = array_merge($opts, $options['curl']);
        }

        if ($inputHeaders) {
            $opts[CURLOPT_HEADER] = array_merge($opts[CURLOPT_HEADER], $inputHeaders);
        }

        curl_multi_add_handle($this->multi_handler, $ch);
        $id                  = (int) $ch;
        $this->handlers[$id] = $ch;

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
        $responses = [];
        $errors    = [];

        do {
            $res = curl_multi_exec($this->multi_handler, $running);
        } while ($running > 0);

        if ($res !== CURLM_OK) {
            foreach ($this->handlers as $handler) {
                curl_multi_remove_handle($this->multi_handler, $handler);
                curl_close($handler);
            }
            curl_multi_close($this->multi_handler);
            return [[], []];
        }

        while ($details = curl_multi_info_read($this->multi_handler)) {
            $info = curl_getinfo($details['handle']);

            $status = (int) $info['http_code'];

            if ($status !== 200) {
                curl_multi_remove_handle($this->multi_handler, $details['handle']);
                curl_close($details['handle']);
                continue;
            }

            $responses[] = curl_multi_getcontent($details['handle']);

            $id = (int) $details['handle'];
            curl_multi_remove_handle($this->multi_handler, $details['handle']);
            curl_close($details['handle']);
            unset($this->handlers[$id]);
        }

        curl_multi_close($this->multi_handler);

        return [$responses, $errors];
    }
}