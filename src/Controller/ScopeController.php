<?php

namespace AuthenticationServer\Controller;

use AuthenticationServer\Repository\ScopeRepository;
use AuthenticationServer\Service\Security;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ScopeController
{
    const SCOPE_ADMIN = 'auth:scope';
    const SCOPE_READ = 'auth:scope:read';
    const SCOPE_WRITE = 'auth:scope:write';

    /**
     * @var ScopeRepository
     */
    private $scopeRepository;

    /**
     * @var Security
     */
    private $security;

    /**
     * ScopeController constructor.
     * @param ScopeRepository $scopeRepository
     * @param Security $security
     */
    public function __construct(ScopeRepository $scopeRepository, Security $security)
    {
        $this->scopeRepository = $scopeRepository;
        $this->security = $security;
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

        $scopes = $this->scopeRepository->findAll();
        return $response->withJson($scopes);
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

        if (!$this->security->isGranted($request, [self::SCOPE_ADMIN, self::SCOPE_WRITE])) {
            return $response->withStatus(403)
                ->withJson(['error' => 'forbidden', 'message' => 'Need scope ' . self::SCOPE_WRITE]);
        }

        if (null === $identifier) {
            return $response->withStatus(400)
                ->withJson(['error' => 'parameter_missing', 'message' => 'Need identifier']);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            return $response->withStatus(400)
                ->withJson(['error' => 'invalid_identifier', 'message' => 'Identifier has to match ^[a-zA-Z0-9_]+$']);
        }

        $alreadyScope = $this->scopeRepository->findBy(['identifier' => $identifier]);

        if (count($alreadyScope) > 0) {
            return $response->withStatus(422)
                ->withJson(['error' => 'already_exists', 'message' => 'Already a scope with identifier '.$identifier]);
        }

        $scope = $this->scopeRepository->create($identifier);
        return $response->withJson($scope);
    }
}