<?php

namespace AuthenticationServer\Command;

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use \Symfony\Component\Console\Command\Command as BaseCommand;

class Command extends BaseCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct($container, $name = null)
    {
        parent::__construct($name);

        $this->container = $container;
        $this->logger = $container->get('logger');
    }
}