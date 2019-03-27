<?php

namespace AuthenticationServer\Controller;

use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\ScopeRepository;
use AuthenticationServer\Repository\UserRepository;
use AuthenticationServer\Service\Mailer;
use AuthenticationServer\Service\Security;
use JsonSchema\Exception\JsonDecodingException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class UserController
{
    const SCOPE_ADMIN = 'auth:user';
    const SCOPE_READ = 'auth:user:read';
    const SCOPE_WRITE = 'auth:user:write';
    const SCOPE_USER_SCOPE = 'auth:user:scope';

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
     * @var Mailer
     */
    private $mailer;

    /**
     * UserController constructor.
     * @param UserRepository $userRepository
     * @param ScopeRepository $scopeRepository
     * @param ClientRepository $clientRepository
     * @param Security $security
     * @param Mailer $mailer
     */
    public function __construct(
        UserRepository $userRepository,
        ScopeRepository $scopeRepository,
        ClientRepository $clientRepository,
        Security $security,
        Mailer $mailer
    ) {
        $this->userRepository = $userRepository;
        $this->scopeRepository = $scopeRepository;
        $this->clientRepository = $clientRepository;
        $this->security = $security;
        $this->mailer = $mailer;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function find(ServerRequestInterface $request, ResponseInterface $response)
    {
        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_READ])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_READ]);
        }

        /** @var Client $client */
        $client = $this->clientRepository->findOneBy(['identifier' => $request->getAttribute('oauth_client_id', null)]);

        if (empty($request->getAttribute('oauth_user_id', null)) && null !== $client) {
            $users = $client->getUsers()->toArray();
        } else {
            $users = $this->userRepository->findAll();
        }

        return $response->withJson($users);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function findOne(ServerRequestInterface $request, ResponseInterface $response)
    {
        $route = $request->getAttribute('route');
        $extended = $request->getQueryParam('extended', false);
        $userIdentifier = $route->getArgument('identifier');

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_READ])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_READ]);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['identifier' => $userIdentifier]);
        if (null === $user) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No user found with identifier '.$userIdentifier]);
        }

        if ($extended) {
            return $response->withJson($user->jsonSerialize($extended));
        }

        return $response->withJson($user);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();

        $identifier = isset($requestParameters['identifier']) ? $requestParameters['identifier'] : null;
        $password = isset($requestParameters['password']) ? $requestParameters['password'] : null;
        $firstName = isset($requestParameters['firstname']) ? $requestParameters['firstname'] : null;
        $lastName = isset($requestParameters['lastname']) ? $requestParameters['lastname'] : null;
        $email = isset($requestParameters['email']) ? $requestParameters['email'] : null;

        try {
            $attributes = isset($requestParameters['attributes']) ? json_decode($requestParameters['attributes']) : [];
        } catch (JsonDecodingException $exception) {
            return $response->withStatus(400)
                ->withJson(['error' => 'json_decode_error', 'message' => $exception->getMessage(), 'hit' => 'Check attributes values']);
        }

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_WRITE])) {
            return $response->withStatus(403)
                    ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_WRITE]);
        }

        if (null === $identifier || null === $password || null === $email) {
            return $response->withStatus(400)
                ->withJson(['error' => 'parameter_missing', 'message' => 'Need identifier, email and password']);
        }

        $alreadyUser = $this->userRepository->findBy(['identifier' => $identifier]);

        if (count($alreadyUser) > 0) {
            return $response->withStatus(422)
                ->withJson(['error' => 'already_exists', 'message' => 'There are already an user with identifier '.$identifier]);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            return $response->withStatus(400)
                ->withJson(['error' => 'invalid_identifier', 'message' => 'Identifier has to match ^[a-zA-Z0-9_]+$']);
        }

        $user = $this->userRepository->create($identifier, $password, $email, $firstName, $lastName, $attributes);

        $clientIdentifier = $request->getAttribute('oauth_client_id');

        if (null !== $clientIdentifier) {
            /** @var Client $client */
            $client = $this->clientRepository->findOneBy(['identifier' => $clientIdentifier]);
            $client->addUser($user);

            $this->clientRepository->persist($client);
        }

        return $response->withJson($user);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();
        $route = $request->getAttribute('route');

        $userIdentifier = $route->getArgument('identifier');

        $password = isset($requestParameters['password']) ? $requestParameters['password'] : null;
        $email = isset($requestParameters['email']) ? $requestParameters['email'] : null;

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_WRITE])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_WRITE]);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['identifier' => $userIdentifier]);

        if (null === $user) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No user found with identifier '. $userIdentifier]);
        }

        if (isset($requestParameters['firstname'])) {
            $user->setFirstName($requestParameters['firstname']);
        }

        if (isset($requestParameters['lastname'])) {
            $user->setLastName($requestParameters['lastname']);
        }

        if (isset($requestParameters['attributes'])) {
            try {
                $attributes = json_decode($requestParameters['attributes']);
                $user->setAttributes($attributes);
            } catch (JsonDecodingException $exception) {
                return $response->withStatus(400)
                    ->withJson(['error' => 'json_decode_error', 'message' => $exception->getMessage(), 'hit' => 'Check attributes values']);
            }
        }

        $user = $this->userRepository->update($user, $password, $email);

        return $response->withJson($user);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function enabled(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();
        $route = $request->getAttribute('route');

        $userIdentifier = $route->getArgument('identifier');

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_WRITE])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_WRITE]);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['identifier' => $userIdentifier]);

        if (null === $user) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No user found with identifier '. $userIdentifier]);
        }

        $user->setEnabled(true);
        $user = $this->userRepository->persist($user);

        return $response->withJson($user);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    public function disabled(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();
        $route = $request->getAttribute('route');

        $userIdentifier = $route->getArgument('identifier');

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_WRITE])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_WRITE]);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['identifier' => $userIdentifier]);

        if (null === $user) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No user found with identifier '. $userIdentifier]);
        }

        $user->setEnabled(false);
        $user = $this->userRepository->persist($user);

        return $response->withJson($user);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function addScope(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();
        $route = $request->getAttribute('route');

        $userIdentifier = $route->getArgument('identifier');
        $identifier = isset($requestParameters['identifier']) ? $requestParameters['identifier'] : null;

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_USER_SCOPE])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_USER_SCOPE]);
        }

        if (null === $identifier) {
            return $response->withStatus(400)
                ->withJson(['error' => 'parameter_missing', 'message' => 'Need scope identifier']);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['identifier' => $userIdentifier]);
        /** @var Scope $scope */
        $scope = $this->scopeRepository->findOneBy(['identifier' => $identifier]);

        if (null === $user) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No user found with identifier '.$userIdentifier]);
        }

        if (null === $scope) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No scope found with identifier '.$identifier]);
        }

        $user->addScope($scope);
        $this->userRepository->persist($user);

        return $response->withStatus(201);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function removeScope(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();
        $route = $request->getAttribute('route');

        $userIdentifier = $route->getArgument('identifier');
        $scopeIdentifier = $route->getArgument('scope');

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_USER_SCOPE])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_USER_SCOPE]);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['identifier' => $userIdentifier]);
        /** @var Scope $scope */
        $scope = $this->scopeRepository->findOneBy(['identifier' => $scopeIdentifier]);

        if (null === $user) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No user found with identifier '.$userIdentifier]);
        }

        if (null === $scope) {
            return $response->withStatus(404)
                ->withJson(['error' => 'not_found', 'message' => 'No scope found with identifier '.$identifier]);
        }

        $user->removeScope($scope);
        $this->userRepository->persist($user);

        return $response->withStatus(201);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function createPasswordSecret(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();
        $email = isset($requestParameters['email']) ? $requestParameters['email'] : null;

        if (null === $email) {
            return $response->withStatus(400)
                ->withJson(['error' => 'parameter_missing', 'message' => 'Need email']);
        }

        $users = $this->userRepository->findBy(['email' => $email]);

        /** @var User $user */
        foreach ($users as $user) {
            $secret = Uuid::uuid4()->toString();

            $user->setPasswordSecret($secret);
            $this->userRepository->persist($user);

            $this->mailer->sendLostPasswordMail($user, $secret);
        }

        return $response->withStatus(204);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function resetPassword(ServerRequestInterface $request, ResponseInterface $response)
    {
        $requestParameters = (array) $request->getParsedBody();
        $secret = isset($requestParameters['secret']) ? $requestParameters['secret'] : null;
        $password = isset($requestParameters['password']) ? $requestParameters['password'] : null;

        if (null === $secret || null === $password) {
            return $response->withStatus(400)
                ->withJson(['error' => 'parameter_missing', 'message' => 'Need secret and new password']);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['passwordSecret' => $secret]);

        if (is_null($user)) {
            return $response->withStatus(400)
                ->withJson(['error' => 'wrong_secret', 'message' => 'Invalid secret']);
        }

        if ($this->userRepository->changePassword($user, $secret, $password)) {
            $this->mailer->sendResetPasswordMail($user);
        }

        return $response->withStatus(204);
    }
}
