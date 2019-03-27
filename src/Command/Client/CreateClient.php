<?php

namespace AuthenticationServer\Command\Client;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Controller\ClientController;
use AuthenticationServer\Controller\ScopeController;
use AuthenticationServer\Controller\UserController;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Repository\ClientRepository;
use AuthenticationServer\Repository\ScopeRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CreateClient extends Command
{
    protected function configure()
    {
        $this->setName('client:create')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Client identifier')
            ->addArgument('name', InputArgument::OPTIONAL, 'Client name')
            ->addArgument('isAdmin', InputArgument::OPTIONAL, 'Client is admin ?')
            ->setHelp('Create a new client.')
            ->setDescription('Create a new client.')
        ;
    }

    /**
     * Create client
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $this->container->get('em')->flush();

        $identifier = $input->getArgument('identifier');
        $name = $input->getArgument('name');
        $isAdmin = $input->getArgument('isAdmin');

        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->container->get('client_repository');

        if (null === $identifier) {
            $identifierQuestion = new Question('What client identifier ? : ', null);
            $identifier = $helper->ask($input, $output, $identifierQuestion);
        }

        $client = $clientRepository->find($identifier);

        if (null != $client) {
            throw new \Exception('Client "' . $identifier . '" already exists');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            throw new \Exception('Identifier has to match ^[a-zA-Z0-9_]+$');
        }

        if (null === $name) {
            $nameQuestion = new Question('What client name ? : ', null);
            $name = $helper->ask($input, $output, $nameQuestion);
        }

        $client = $clientRepository->create($identifier, $name);

        $output->writeln('========== CLIENT ==========');
        $output->writeln('Identifier: ' . $client->getIdentifier());
        $output->writeln('Name: ' . $client->getName());
        $output->writeln('Secret: ' . $client->getSecret());

        $isAdminQuestion = new ConfirmationQuestion('Client is admin? [n] ', false);

        if ($isAdmin || ($input->isInteractive() && $helper->ask($input, $output, $isAdminQuestion))) {

            /** @var ScopeRepository $scopeRepository */
            $scopeRepository = $this->container->get('scope_repository');

            foreach ([UserController::SCOPE_ADMIN, ScopeController::SCOPE_ADMIN, ClientController::SCOPE_ADMIN] as $scope) {

                /** @var Scope $searchScope */
                $searchScope = $scopeRepository->find($scope);
                if (!is_null($searchScope)) {
                    $output->writeln('Scope "' . $searchScope->getIdentifier() . '" added at client "' . $identifier . '"');
                    $client->addScope($searchScope);
                }
            }

            $clientRepository->persist($client);
        }
    }
}
