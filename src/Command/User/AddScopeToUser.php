<?php

namespace AuthenticationServer\Command\User;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\Scope;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\ScopeRepository;
use AuthenticationServer\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AddScopeToUser extends Command
{
    protected function configure()
    {
        $this->setName('user:scope')
            ->addArgument('userIdentifier', InputArgument::OPTIONAL, 'User identifier')
            ->addArgument('scopeIdentifier', InputArgument::OPTIONAL, 'Scope identifier')
            ->setHelp('Add scope to user.')
            ->setDescription('Add scope to user.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $userIdentifier = $input->getArgument('userIdentifier');
        $scopeIdentifier = $input->getArgument('scopeIdentifier');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('user_repository');

        if (null === $userIdentifier) {
            $userIdentifierQuestion = new Question('What user identifier ? : ', null);
            $userIdentifier = $helper->ask($input, $output, $userIdentifierQuestion);
        }

        /** @var User $user */
        $user = $userRepository->find($userIdentifier);

        if (is_null($user)) {
            throw new EntityNotFoundException('User "' . $userIdentifier . '" not found.');
        }

        /** @var ScopeRepository $scopeRepository */
        $scopeRepository = $this->container->get('scope_repository');

        if (null === $scopeIdentifier) {
            $scopeIdentifierQuestion = new Question('What scope identifier ? : ', null);
            $scopeIdentifier = $helper->ask($input, $output, $scopeIdentifierQuestion);
        }

        /** @var Scope $scope */
        $scope = $scopeRepository->find($scopeIdentifier);

        if (is_null($scope)) {
            throw new EntityNotFoundException('Scope "' . $scopeIdentifier . '" not found.');
        }

        $user->addScope($scope);

        $userRepository->persist($user);

        $output->writeln('Scope "' . $scopeIdentifier . '" added at user "' . $userIdentifier . '"');
    }
}
