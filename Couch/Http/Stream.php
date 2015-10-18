<?php
namespace Couch\Http;

abstract class Stream
{
    protected $body;
    protected $headers = [];

    // alias
    public function getData() {
        return $this->body;
    }

    public function getBody() {
        return $this->body;
    }

    public function getHeader($key) {
        return isset($this->headers[$key])
            ? $this->headers[$key] : null;
    }

    public function getHeaderAll() {
        return $this->headers;
    }

    abstract public function setBody($body);
    abstract public function setHeader($key, $value);
}