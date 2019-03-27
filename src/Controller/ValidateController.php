<?php

namespace AuthenticationServer\Controller;

use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ValidateController
{
    /**
     * @var ResourceServer
     */
    private $server;

    /**
     * ValidateController constructor.
     * @param ResourceServer $server
     */
    public function __construct(ResourceServer $server)
    {
        $this->server = $server;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return \Psr\Http\Message\MessageInterface|ResponseInterface
     */
    public function validate(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {

            // Try to respond to the access token request
            $request = $this->server->validateAuthenticatedRequest($request);

            $validateContent = [
                'oauth_access_token_id' => $request->getAttribute('oauth_access_token_id'),
                'oauth_client_id' => $request->getAttribute('oauth_client_id'),
                'oauth_user_id' => $request->getAttribute('oauth_user_id'),
                'oauth_scopes' => $request->getAttribute('oauth_scopes')
            ];

            return $response->withJson($validateContent);

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
