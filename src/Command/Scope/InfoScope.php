<?php

namespace AuthenticationServer\Command\Scope;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\ScopeRepository;
use AuthenticationServer\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InfoScope extends Command
{
    protected function configure()
    {
        $this->setName('scope:info')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Scope identifier')
            ->setHelp('Get infos scope.')
            ->setDescription('Get infos scope.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $this->container->get('em')->flush();

        $identifier = $input->getArgument('identifier');

        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');

        if (null === $identifier) {
            $identifierQuestion = new Question('What scope identifier ? : ', null);
            $identifier = $helper->ask($input, $output, $identifierQuestion);
        }

        /** @var Scope $scope **/
        $scope = $scopeRepository->findOneBy(['identifier' => $identifier]);

        if (null === $scope) {
            throw new EntityNotFoundException('Scope "' . $identifier . '" not found');
        }

        $output->writeln('========== INFO ==========');

        $output->writeln('Identifier: ' . $scope->getIdentifier());

        $output->writeln('========== USERS ==========');

        /** @var User $user */
        foreach ($scope->getUsers() as $user) {
            $output->writeln($user->getIdentifier());
        }

        $output->writeln('========== CLIENTS ==========');

        /** @var Client $client */
        foreach ($scope->getClients() as $client) {
            $output->writeln($client->getIdentifier());
        }
    }
}
