<?php

namespace AuthenticationServer\Grant;

use AuthenticationServer\Entity\Client;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\UserRepository;
use AuthenticationServer\Service\HTTPClient;
use AuthenticationServer\Service\Mailer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;

class GoogleGrant extends PasswordGrant
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var ClientRepository
     */
    protected $clientRepository;

    /**
     * @var resource
     */
    protected $googleClient;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var HTTPClient
     */
    protected $httpClient;

    /**
     * GoogleGrant constructor.
     * @param UserRepository $userRepository
     * @param ClientRepository $clientRepository
     * @param RefreshTokenRepositoryInterface $refreshTokenRepository
     * @param Mailer $mailer
     * @param HTTPClient $httpClient
     */
    public function __construct(
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        Mailer $mailer,
        HTTPClient $httpClient
    ) {
        parent::__construct($userRepository, $refreshTokenRepository);

        $this->httpClient = $httpClient;
        $this->mailer = $mailer;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseTypeInterface $responseType
     * @param \DateInterval $accessTokenTTL
     * @return ResponseTypeInterface
     * @throws OAuthServerException
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {
        $accessToken = $this->getRequestParameter('access_token', $request);
        if (is_null($accessToken)) {
            throw OAuthServerException::invalidRequest('access_token');
        }

        /** @var Client $client */
        $client = $this->clientRepository->findOneBy(['identifier' => $this->getRequestParameter('client_id', $request)]);

        if (is_null($client)) {
            throw OAuthServerException::invalidRequest('client_id');
        }

        $response = $this->httpClient->get('https://www.googleapis.com/oauth2/v2/userinfo', ['Authorization: Bearer ' . $accessToken]);
        $userInfo = json_decode($response);

        if (isset($userInfo->error)) {
            throw OAuthServerException::invalidRequest('access_token (' . $userInfo->error->message . ')');
        }

        $user = $this->userRepository->findOneBy(['identifier' => $this->getUserIdentifier($userInfo)]);
        if (is_null($user)) {
            $user = $this->createUser($userInfo);
            $this->mailer->sendUserCreatedMail($user, $client);
        }

        if (!$client->hasUser($user)) {
            $client->addUser($user);
            $this->clientRepository->persist($client);
        }

        $request = $request->withParsedBody([
            'client_id' => $this->getRequestParameter('client_id', $request),
            'client_secret' => $this->getRequestParameter('client_secret', $request),
            'username' => $user->getIdentifier(),
            'password' => 'whatever'
        ]);

        return parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);
    }

    /**
     * @param $userInfo
     * @return \AuthenticationServer\Entity\User
     */
    private function createUser($userInfo)
    {
        $now = new \DateTime();
        $user = $this->userRepository->create(
            $this->getUserIdentifier($userInfo),
            md5($now->getTimestamp()),
            $userInfo->email,
            $userInfo->given_name,
            $userInfo->family_name,
            ['avatar' => $userInfo->picture]
        );

        $user->setGoogleId($userInfo->id);
        $this->userRepository->persist($user);

        return $user;
    }

    /**
     * @param $userInfo
     * @return string
     */
    private function getUserIdentifier($userInfo)
    {
        $identifier = 'google-' . $userInfo->id;
        if (isset($userInfo->email)) {
            $explodedEmail = explode('@', $userInfo->email);
            $identifier = $explodedEmail[0];
        }

        return $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'google';
    }
}
