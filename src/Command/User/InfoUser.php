<?php

namespace AuthenticationServer\Command\User;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Client;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class InfoUser extends Command
{
    protected function configure()
    {
        $this->setName('user:info')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'User identifier')
            ->setHelp('Get infos user.')
            ->setDescription('Get infos user.')
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

        $userIdentifier = $input->getArgument('identifier');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('user_repository');

        if (null === $userIdentifier) {
            $userIdentifierQuestion = new Question('What user identifier ? : ', null);
            $userIdentifier = $helper->ask($input, $output, $userIdentifierQuestion);
        }

        /** @var User $user **/
        $user = $userRepository->findOneBy(['identifier' => $userIdentifier]);

        if (null === $user) {
            throw new EntityNotFoundException('User "' . $userIdentifier . '" not found');
        }

        $output->writeln('========== INFO ==========');

        $output->writeln('Identifier: ' . $user->getIdentifier());
        $output->writeln('Email: ' . $user->getEmail());
        $output->writeln('First Name: ' . $user->getFirstName());
        $output->writeln('Last Name: ' . $user->getLastName());
        $output->writeln('Enabled: ' . $user->isEnabled());

        if (count($user->getAttributes()) > 0) {
            $output->writeln('Attributes');

            foreach ($user->getAttributes() as $key => $value) {
                $output->writeln($key . ': ' .$value);
            }
        }

        $output->writeln('========== SCOPES ==========');

        /** @var Scope $scope */
        foreach ($user->getScopes() as $scope) {
            $output->writeln($scope->getIdentifier());
        }

        $output->writeln('========== CLIENTS ==========');

        /** @var Client $client */
        foreach ($user->getClients() as $client) {
            $output->writeln($client->getIdentifier());
        }
    }
}
