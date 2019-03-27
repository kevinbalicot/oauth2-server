<?php

namespace AuthenticationServer\Command;

use AuthenticationServer\Command\Client\AddScopeToClient;
use AuthenticationServer\Command\Client\AddUserToClient;
use AuthenticationServer\Command\Client\CreateClient;
use AuthenticationServer\Command\Client\DeleteClient;
use AuthenticationServer\Command\Client\InfoClient;
use AuthenticationServer\Command\Client\ListClients;
use AuthenticationServer\Command\Scope\CreateScope;
use AuthenticationServer\Command\Scope\DeleteScope;
use AuthenticationServer\Command\Scope\InfoScope;
use AuthenticationServer\Command\Scope\ListScopes;
use AuthenticationServer\Command\User\AddScopeToUser;
use AuthenticationServer\Command\User\CreateUser;
use AuthenticationServer\Command\User\DeleteUser;
use AuthenticationServer\Command\User\InfoUser;
use AuthenticationServer\Command\User\ListUsers;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Home extends Command
{
    protected function configure()
    {
        $this->setName('server:home')
            ->setHelp('Interactive home server CLI.')
            ->setDescription('Interactive home server CLI.')
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

        $this->displayMainMenu($output);

        $menuQuestion = new Question('Your choice : ', 0);
        $menu = (int) $helper->ask($input, $output, $menuQuestion);

        do {
            if ($menu === 1) {
                $this->handleUsersMenu($input, $output);
            } else if ($menu === 2) {
                $this->handleClientsMenu($input, $output);
            } else if ($menu === 3) {
                $this->handleScopesMenu($input, $output);
            }

            $this->displayMainMenu($output);
            $menu = (int) $helper->ask($input, $output, $menuQuestion);
        } while ($menu !== 0);
    }

    /**
     * @param OutputInterface $output
     */
    private function displayMainMenu(OutputInterface $output)
    {
        $output->writeln('========== MENU ==========');
        $output->writeln('1 - Users');
        $output->writeln('2 - Clients');
        $output->writeln('3 - Scopes');
        $output->writeln('0 - Quite');
    }

    /**
     * @param OutputInterface $output
     */
    private function displayUsersMenu(OutputInterface $output)
    {
        $output->writeln('========== USERS MENU ==========');
        $output->writeln('1 - List users');
        $output->writeln('2 - Info user');
        $output->writeln('3 - Create user');
        $output->writeln('4 - Add scope to user');
        $output->writeln('5 - Delete user');
        $output->writeln('0 - Return to main menu');
    }

    /**
     * @param OutputInterface $output
     */
    private function displayClientsMenu(OutputInterface $output)
    {
        $output->writeln('========== CLIENTS MENU ==========');
        $output->writeln('1 - List clients');
        $output->writeln('2 - Info client');
        $output->writeln('3 - Create client');
        $output->writeln('4 - Add user to client');
        $output->writeln('5 - Delete client');
        $output->writeln('6 - Add scope to client');
        $output->writeln('0 - Return to main menu');
    }

    /**
     * @param OutputInterface $output
     */
    private function displayScopesMenu(OutputInterface $output)
    {
        $output->writeln('========== SCOPES MENU ==========');
        $output->writeln('1 - List scopes');
        $output->writeln('2 - Info scope');
        $output->writeln('3 - Create scope');
        $output->writeln('5 - Delete scope');
        $output->writeln('0 - Return to main menu');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    private function handleUsersMenu(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $arguments = new ArrayInput([]);

        /** @var Application $application */
        $application = $this->getApplication();

        /** @var ListUsers $listUserCommand */
        $listUserCommand = $application->find('user:list');
        /** @var InfoUser $infoUserCommand */
        $infoUserCommand = $application->find('user:info');
        /** @var CreateUser $createUserCommand */
        $createUserCommand = $application->find('user:create');
        /** @var AddScopeToUser $addScopeToUserCommand */
        $addScopeToUserCommand = $application->find('user:scope');
        /** @var DeleteUser $deleteUserCommand */
        $deleteUserCommand = $application->find('user:delete');

        $this->displayUsersMenu($output);

        $menuQuestion = new Question('Your choice : ', 0);
        $menu = (int) $helper->ask($input, $output, $menuQuestion);

        do {
            if ($menu === 1) {
                $listUserCommand->run($arguments, $output);
            } else if ($menu === 2) {
                $infoUserCommand->run($arguments, $output);
            } else if ($menu === 3) {
                $createUserCommand->run($arguments, $output);
            } else if ($menu === 4) {
                $addScopeToUserCommand->run($arguments, $output);
            } else if ($menu === 5) {
                $deleteUserCommand->run($arguments, $output);
            }

            $this->displayUsersMenu($output);
            $menu = (int) $helper->ask($input, $output, $menuQuestion);
        } while ($menu !== 0);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    private function handleClientsMenu(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $arguments = new ArrayInput([]);

        /** @var Application $application */
        $application = $this->getApplication();

        /** @var ListClients $listCommand */
        $listCommand = $application->find('client:list');
        /** @var InfoClient $infoCommand */
        $infoCommand = $application->find('client:info');
        /** @var CreateClient $createCommand */
        $createCommand = $application->find('client:create');
        /** @var AddUserToClient $addUserCommand */
        $addUserCommand = $application->find('client:user');
        /** @var DeleteClient $deleteCommand */
        $deleteCommand = $application->find('client:delete');
        /** @var AddScopeToClient $addScopeToClient */
        $addScopeToClient = $application->find('client:scope');

        $this->displayClientsMenu($output);

        $menuQuestion = new Question('Your choice : ', 0);
        $menu = (int) $helper->ask($input, $output, $menuQuestion);

        do {
            if ($menu === 1) {
                $listCommand->run($arguments, $output);
            } else if ($menu === 2) {
                $infoCommand->run($arguments, $output);
            } else if ($menu === 3) {
                $createCommand->run($arguments, $output);
            } else if ($menu === 4) {
                $addUserCommand->run($arguments, $output);
            } else if ($menu === 5) {
                $deleteCommand->run($arguments, $output);
            } else if ($menu === 6) {
                $addScopeToClient->run($arguments, $output);
            }

            $this->displayClientsMenu($output);
            $menu = (int) $helper->ask($input, $output, $menuQuestion);
        } while ($menu !== 0);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    private function handleScopesMenu(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $arguments = new ArrayInput([]);

        /** @var Application $application */
        $application = $this->getApplication();

        /** @var ListScopes $listCommand */
        $listCommand = $application->find('scope:list');
        /** @var InfoScope $infoCommand */
        $infoCommand = $application->find('scope:info');
        /** @var CreateScope $createCommand */
        $createCommand = $application->find('scope:create');
        /** @var DeleteScope $deleteCommand */
        $deleteCommand = $application->find('scope:delete');

        $this->displayScopesMenu($output);

        $menuQuestion = new Question('Your choice : ', 0);
        $menu = (int) $helper->ask($input, $output, $menuQuestion);

        do {
            if ($menu === 1) {
                $listCommand->run($arguments, $output);
            } else if ($menu === 2) {
                $infoCommand->run($arguments, $output);
            } else if ($menu === 3) {
                $createCommand->run($arguments, $output);
            } else if ($menu === 4) {
                $deleteCommand->run($arguments, $output);
            }

            $this->displayScopesMenu($output);
            $menu = (int) $helper->ask($input, $output, $menuQuestion);
        } while ($menu !== 0);
    }
}
