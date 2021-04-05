<?php


namespace FTX\Client;


use FTX\Client\Exceptions\TooManyRequestsException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use FTX\Client\Exceptions\NotFoundException;
use FTX\Client\Exceptions\UnauthorizedException;

class HttpExceptionHandler
{
    public function transformResponseToException(RequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        if(404 == $response->getStatusCode()) {
            throw new NotFoundException($this->getResponseMessage($response), $request, $response);
        }
        
        if(401 == $response->getStatusCode()) {
            throw new UnauthorizedException($this->getResponseMessage($response), $request, $response);
        }

        if(429 == $response->getStatusCode()) {
            throw new TooManyRequestsException($this->getResponseMessage($response), $request, $response);
        }
        
        return $response;
    }

    protected function getResponseMessage(ResponseInterface $response): string
    {
        $responseBody = $response->getBody();

        $responseBody->rewind();
        $decodedBody = json_decode($responseBody->getContents(), true);
        $responseBody->rewind();

        return isset($decodedBody['error']) ? $decodedBody['error'] : $response->getReasonPhrase();
    }
}