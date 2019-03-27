<?php

namespace AuthenticationServer\Command;

use AuthenticationServer\Controller\ScopeController;
use AuthenticationServer\Controller\UserController;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\ScopeRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Bootstrap extends Command
{
    protected function configure()
    {
        $this->setName('server:init')
            ->setHelp('Bootstrap server.')
            ->setDescription('Bootstrap server.')
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

        // Questions
        $continueQuestion = new ConfirmationQuestion('Do you want to continue? [y] ');
        $clientIdentifierQuestion = new Question('Client identifier : ');
        $clientNameQuestion = new Question('Client name : ');
        $createNewOneQuestion = new ConfirmationQuestion('Create new one? [n] ', false);

        $output->writeln('BE CAREFULL !! This command have to be executed only on a new installation of authentication server.');

        if ($helper->ask($input, $output, $continueQuestion)) {

            $this->haveScope($output);

            do {
                $output->writeln('Create new admin client (with all scopes) : ');
                $identifier = $helper->ask($input, $output, $clientIdentifierQuestion);
                $name = $helper->ask($input, $output, $clientNameQuestion);

                if (is_null($identifier) || is_null($name)) {
                    throw new \Exception('Identifier and name cannot be null');
                }

                $this->createClient($identifier, $name, $output);

                $continue = $helper->ask($input, $output, $createNewOneQuestion);
            } while ($continue);
        }
    }

    /**
     * @param OutputInterface $output
     */
    private function haveScope(OutputInterface $output)
    {
        $output->writeln('Check scopes');

        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');

        $scopes = [
            UserController::SCOPE_ADMIN,
            UserController::SCOPE_READ,
            UserController::SCOPE_WRITE,
            UserController::SCOPE_USER_SCOPE,

            ScopeController::SCOPE_ADMIN,
            ScopeController::SCOPE_READ,
            ScopeController::SCOPE_WRITE
        ];

        foreach ($scopes as $scope) {
            if (is_null($scopeRepository->find($scope))) {
                $scopeRepository->create($scope);
            }
        }

        $output->writeln('Scopes OK.');
    }

    /**
     * @param $identifier
     * @param $name
     * @param OutputInterface $output
     * @return void
     */
    private function createClient($identifier, $name, OutputInterface $output)
    {
        $this->container->get('em')->flush();
        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');
        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');

        $scopes = $scopeRepository->findBy(['identifier' => ['auth:user', 'auth:scope']]);
        $clientRepository->create($identifier, $name);

        $client = $clientRepository->find($identifier);

        foreach ($scopes as $scope) {
            $client->addScope($scope);
        }

        $clientRepository->persist($client);

        $output->writeln('Client created');
        $output->writeln('Identifier : ' . $client->getIdentifier());
        $output->writeln('Name : ' . $client->getName());
        $output->writeln('Secret : ' . $client->getSecret());
    }
}
