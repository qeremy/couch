<?php
namespace Couch\Http;

use \Couch\Couch;
use \Couch\Client;
use \Couch\Util\PropertyTrait as Property;

class Request
{
    use Property;

    const METHOD_HEAD   = 'HEAD',
          METHOD_GET    = 'GET',
          METHOD_POST   = 'POST',
          METHOD_PUT    = 'PUT',
          METHOD_COPY   = 'COPY',
          METHOD_DELETE = 'DELETE';

    private $client;

    private $method;
    private $uri;

    private $body,
            $bodyRaw;
    private $headers = [
        'Accept' => 'application/json',
    ];

    public function __construct(Client $client) {
        $this->client = $client;
    }

    public function send() {
        $this->headers['User-Agent'] =
            'Couch/v'. Couch::VERSION .' (+http://github.com/qeremy/couch)';

        $agent = $this->client->couch->getHttpAgent();
        if (false === ($result = $agent->run($this))) {
            throw new \Exception('Error!');
        }

        @list($headers, $body) = explode("\r\n\r\n", $result, 2);

        $response = (new Response())
            ->setBody($body)
            ->setBodyRaw($body);

        $headers = Agent::parseResponseHeaders($headers);
        foreach ($headers as $key => $value) {
            $response->setHeader($key, $value);
        }

        if (isset($response->headers['_status']['code'],
                  $response->headers['_status']['text'])) {
            $response->setStatusCode($response->headers['_status']['code'])
                     ->setStatusText($response->headers['_status']['text']);
        }

        return $response;
    }

    public function setMethod($method) {
        $this->method = $method;
        return $this;
    }
    public function setUri($uri) {
        $this->uri = $uri;
        return $this;
    }

    public function setBody($body) {
        $this->body = $body;
        return $this;
    }
    public function setBodyRaw($bodyRaw) {
        $this->bodyRaw = $bodyRaw;
        return $this;
    }
    public function setHeader($key, $value) {
        $this->headers[$key] = $value;
        return $this;
    }
}
