<?php

namespace AuthenticationServer\Command;

use AuthenticationServer\Entity\Client;
use AuthenticationServer\Repository\ClientRepository;
use Doctrine\ORM\EntityNotFoundException;
use League\OAuth2\Server\AuthorizationServer;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Stream;
use Slim\Http\Uri;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Authorize extends Command
{
    protected function configure()
    {
        $this->setName('server:authorize')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Client identifier')
            ->addArgument('username', InputArgument::OPTIONAL, 'User username')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password')
            ->addArgument('scopes', InputArgument::OPTIONAL, 'User scopes')
            ->setHelp('Authorize user or client.')
            ->setDescription('Authorize user or client.')
        ;
    }

    /**
     * Create client
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $clientIdentifier = $input->getArgument('identifier');
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $scopes = $input->getArgument('scopes');

        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');

        if (null === $clientIdentifier) {
            $clientIdentifierQuestion = new Question('What client : ', null);
            $clientIdentifier = $helper->ask($input, $output, $clientIdentifierQuestion);
        }

        /** @var Client $client **/
        $client = $clientRepository->findOneBy(['identifier' => $clientIdentifier]);

        if (null === $client) {
            throw new EntityNotFoundException('Client "' . $clientIdentifier . '" not found');
        }

        if (null === $username) {
            $usernameQuestion = new Question('What username : ', null);
            $username = $helper->ask($input, $output, $usernameQuestion);
        }

        if (null === $password) {
            $passwordQuestion = new Question('What password : ', null);
            $passwordQuestion->setHidden(true);
            $password = $helper->ask($input, $output, $passwordQuestion);
        }

        if (null === $scopes) {
            $scopesQuestion = new Question('What scopes : ', null);
            $scopes = $helper->ask($input, $output, $scopesQuestion);
        }

        /** @var AuthorizationServer $server */
        $server = $this->container->get('authentication_server');

        $body = 'client_id=' . $client->getIdentifier() . '&client_secret=' . $client->getSecret(). '&grant_type=password&username=' . $username . '&password=' . $password . '&scope=' . $scopes;
        $resource = fopen('php://memory','r+');
        fwrite($resource, $body);

        $uri = new Uri('http', 'localhost', 80, '/authorize');

        $header = new Headers();
        $header->set('Content-Type', 'application/x-www-form-urlencoded');
        $header->set('Content-Length', strlen($body));
        $header->set('Accept', '*/*');

        $stream = new Stream($resource);
        $request = new Request('POST', $uri, $header, [], [], $stream);
        $response = new Response();

        $response = $server->respondToAccessTokenRequest($request, $response);
        $token = json_decode((string) $response->getBody());

        $output->writeln('========== AUTHORIZED ==========');
        $output->writeln('Token Type: ' . $token->token_type);
        $output->writeln('Expires In: ' . $token->expires_in . ' seconds (' . ($token->expires_in / 60) . 'min)');

        $output->writeln('========== ACCESS TOKEN ==========');
        $output->writeln($token->access_token);
        $output->writeln('========== REFRESH TOKEN ==========');
        $output->writeln($token->refresh_token);
        $output->writeln('========== HEADER TOKEN ==========');
        $output->writeln($token->token_type . ' ' . $token->access_token);
    }
}