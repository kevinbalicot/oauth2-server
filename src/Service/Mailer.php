<?php

namespace AuthenticationServer\Service;

use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\User;

class Mailer
{
    /**
     * @var HTTPClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    private $uri;

    /**
     * Mailer constructor.
     * @param HTTPClient $httpClient
     * @param string $uri
     */
    public function __construct(HTTPClient $httpClient, $uri)
    {
        $this->uri = $uri;
        $this->httpClient = $httpClient;
    }

    /**
     * @param User $user
     * @param Client $client
     * @return mixed
     */
    public function sendUserCreatedMail(User $user, Client $client)
    {
        $message = 'Hello ' . $user->getIdentifier() .',\n\nYour account was created for application ' . $client->getName() . '\n\n\nThanks you for your confidence.';

        return $this->httpClient->post($this->uri, [
            'to' => $user->getEmail(),
            'from' => 'noreply@' . $client->getIdentifier() . '.com',
            'subject' => 'Your account was created!',
            'message' => $message
        ]);
    }

    /**
     * @param User $user
     * @param string $secret
     * @return mixed
     */
    public function sendLostPasswordMail(User $user, $secret)
    {
        $message = 'Hello ' . $user->getIdentifier() .',\n\nYou have sent a reset password request.\n
Here is the security code to change your password : ' . $secret . '\n\n\nThanks you for your confidence.';

        return $this->httpClient->post($this->uri, [
            'to' => $user->getEmail(),
            'from' => 'noreply@auth.com',
            'subject' => 'Your reset password request!',
            'message' => $message
        ]);
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function sendResetPasswordMail(User $user)
    {
        $message = 'Hello ' . $user->getIdentifier() .',\n\nWe have received your request to change your password\n
The change is effective now, please reconnect.\n\n\nThanks you for your confidence.';

        return $this->httpClient->post($this->uri, [
            'to' => $user->getEmail(),
            'from' => 'noreply@auth.com',
            'subject' => 'Your password has been changed!',
            'message' => $message
        ]);
    }
}
