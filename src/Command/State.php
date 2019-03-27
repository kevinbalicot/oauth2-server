<?php

namespace AuthenticationServer\Command;

use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\ScopeRepository;
use AuthenticationServer\Repository\UserRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class State extends Command
{
    protected function configure()
    {
        $this->setName('server:state')
            ->setHelp('State of server.')
            ->setDescription('State of server.')
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
        $this->container->get('em')->flush();
        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');
        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('user_repository');

        $clients = $clientRepository->findAll();
        $scopes = $scopeRepository->findAll();
        $users = $userRepository->findAll();

        $output->writeln('========== CLIENTS ==========');

        /** @var Client $client */
        foreach ($clients as $client) {
            $output->writeln('Identifier: ' . $client->getIdentifier());
            $output->writeln('Name: ' . $client->getName());
            $output->writeln('----------');
        }

        $output->writeln('========== USERS ==========');

        /** @var User $user */
        foreach ($users as $user) {
            $output->writeln('Identifier: ' . $user->getIdentifier());
            $output->writeln('Email: ' . $user->getEmail());
            $output->writeln('First Name: ' . $user->getFirstName());
            $output->writeln('Last Name: ' . $user->getLastName());
            $output->writeln('----------');
        }

        $output->writeln('========== SCOPES ==========');

        /** @var Scope $scope */
        foreach ($scopes as $scope) {
            $output->writeln($scope->getIdentifier());
        }
    }
}