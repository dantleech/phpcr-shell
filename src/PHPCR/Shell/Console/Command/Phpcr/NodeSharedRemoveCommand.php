<?php

namespace PHPCR\Shell\Console\Command\Phpcr;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use PHPCR\RepositoryInterface;

class NodeSharedRemoveCommand extends PhpcrShellCommand
{
    protected function configure()
    {
        $this->setName('node:shared:remove');
        $this->setDescription('Removes this node and every other node in the shared set of this node');
        $this->addArgument('path', InputArgument::REQUIRED, 'Path of node');
        $this->setHelp(<<<HERE
Removes this node and every other node in the shared set of this node.

This removal must be done atomically, i.e., if one of the nodes cannot
be removed, the method throws the exception <info>node:remove</info>
would have thrown in that case, and none of the nodes are removed.

If this node is not shared this method removes only this node.
HERE
        );

        $this->requiresDescriptor(RepositoryInterface::OPTION_SHAREABLE_NODES_SUPPORTED, true);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();
        $path = $session->getAbsPath($input->getArgument('path'));
        $currentNode = $session->getNode($path);
        $sharedSet = $currentNode->removeSharedSet();
    }
}
