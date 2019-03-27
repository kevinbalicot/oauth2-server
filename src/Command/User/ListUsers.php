<?php

namespace AuthenticationServer\Command\User;

use AuthenticationServer\Command\Command;
use AuthenticationServer\Entity\User;
use AuthenticationServer\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListUsers extends Command
{
    protected function configure()
    {
        $this->setName('user:list')
            ->setHelp('Get list of users.')
            ->setDescription('Get list of users.')
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
        $this->container->get('em')->flush();

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('user_repository');

        /** @var ArrayCollection $users **/
        $users = $userRepository->findBy([], ['createdAt' => 'ASC']);

        if (count($users) === 0) {
            throw new EntityNotFoundException('No Users');
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
    }
}
