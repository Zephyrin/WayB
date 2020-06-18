<?php

use Imbo\BehatApiExtension\Context;
use PHPUnit\Framework\Assert as Assertions;

class ApiContextAuth extends Context\ApiContext
{
    protected $token;
    public function getTokenFromLogin()
    {
        $this->token = '';
        $this->requireResponse();
        $body = $this->getResponseBody();
        if(isset($body->token))
        $this->token = "Bearer $body->token";
    }

    public function requestPath($path, $method = null)
    {
        $this->setRequestHeader("Authorization", "{$this->token}");

        return parent::requestPath($path, $method);
    }

    public function logout()
    {
        $this->setRequestHeader('Authorization', '');
        $this->token = "Bearer ";
    }

    public function theResponseBodyHasFields($nbField) {
        $this->requireResponse();

        $body = $this->getResponseBody();
        Assertions::assertEquals(count((array)$body), $nbField);
    }
}