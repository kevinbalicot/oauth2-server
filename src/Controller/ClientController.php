<?php

namespace AuthenticationServer\Controller;

use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\ScopeRepository;
use AuthenticationServer\Repository\UserRepository;
use AuthenticationServer\Service\Security;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientController
{
    const SCOPE_ADMIN = 'auth:client';
    const SCOPE_CLIENT_USER = 'auth:client:user';

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ScopeRepository
     */
    private $scopeRepository;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     * @param ScopeRepository $scopeRepository
     * @param ClientRepository $clientRepository
     * @param Security $security
     */
    public function __construct(
        UserRepository $userRepository,
        ScopeRepository $scopeRepository,
        ClientRepository $clientRepository,
        Security $security
    ) {
        $this->userRepository = $userRepository;
        $this->scopeRepository = $scopeRepository;
        $this->clientRepository = $clientRepository;
        $this->security = $security;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function addUser(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();

        $identifier = isset($requestParameters['identifier']) ? $requestParameters['identifier'] : null;

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_CLIENT_USER])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_CLIENT_USER]);
        }

        if (null === $identifier) {
            return $response->withStatus(400)
                ->withJson(['error' => 'parameter_missing', 'message' => 'Need user identifier']);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['identifier' => $identifier]);
        /** @var Client $client */
        $client = $this->clientRepository->findOneBy(['identifier' => $request->getAttribute('oauth_client_id', null)]);

        if (null === $user) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No user found with identifier '.$identifier]);
        }

        if (null === $client) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No client found with identifier '.$request->getAttribute('oauth_client_id', null)]);
        }

        $client->addUser($user);
        $this->clientRepository->persist($client);

        return $response->withStatus(201);
    }
}