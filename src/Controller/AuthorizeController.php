<?php

namespace AuthenticationServer\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizeController
{
    /**
     * @var AuthorizationServer
     */
    private $server;

    /**
     * AuthorizeController constructor.
     * @param AuthorizationServer $server
     */
    public function __construct(AuthorizationServer $server)
    {
        $this->server = $server;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\MessageInterface|ResponseInterface
     */
    public function authorize(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {

            // Try to respond to the access token request
            $response = $this->server->respondToAccessTokenRequest($request, $response);
            return $response->withAddedHeader('Content-Type', 'application/json');

        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be converted to a PSR-7 response
            return $exception->generateHttpResponse($response);

        } catch (\Exception $exception) {
            // Catch unexpected exceptions
            $body = $response->getBody();
            $body->write($exception->getMessage());

            return $response->withStatus(500)->withBody($body);
        }
    }
}