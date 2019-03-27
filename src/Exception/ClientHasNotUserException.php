<?php

namespace AuthenticationServer\Exception;

use League\OAuth2\Server\Exception\OAuthServerException;

class ClientHasNotUserException extends OAuthServerException
{
    public function __construct($message)
    {
        parent::__construct($message, 0, 'client_has_not_user', 400, null, null);
    }
}