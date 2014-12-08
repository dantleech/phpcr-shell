<?php

namespace PHPCR\Shell\Console\Command\Phpcr;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class NodeInfoCommand extends BasePhpcrCommand
{
    protected function configure()
    {
        $this->setName('node:info');
        $this->setDescription('Show information about the current node');
        $this->addArgument('path', InputArgument::REQUIRED, 'Path of node');
        $this->setHelp(<<<HERE
Show information about the current node
HERE
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->get('phpcr.session');
        $path = $input->getArgument('path');
        $nodeHelper = $this->get('helper.node');
        $currentNode = $session->getNodeByPathOrIdentifier($path);
        $formatter = $this->get('helper.result_formatter');

        $mixins = $currentNode->getMixinNodeTypes();
        $mixinNodeTypeNames = array();

        foreach ($mixins as $name => $mixin) {
            $mixinNodeTypeNames[] = $mixin->getName();
        }

        if ($nodeHelper->nodeHasMixinType($currentNode, 'mix:versionable')) {
            try {
                $isCheckedOut = $currentNode->isCheckedOut() ? 'yes' : 'no';
            } catch (\Exception $e) {
                $isCheckedOut = $formatter->formatException($e);
            }
        } else {
            $isCheckedOut = 'N/A';
        }

        try {
            $isLocked = $currentNode->isLocked() ? 'yes' : 'no';
        } catch (\Exception $e) {
            $isLocked = $formatter->formatException($e);
        }

        $info = array(
            'Path' => $currentNode->getPath(),
            'UUID' => $currentNode->hasProperty('jcr:uuid') ? $currentNode->getProperty('jcr:uuid')->getValue() : 'N/A',
            'Index' => $currentNode->getIndex(),
            'Primary node type' => $currentNode->getPrimaryNodeType()->getName(),
            'Mixin node types' => implode(', ', $mixinNodeTypeNames),
            'Checked out?' => $isCheckedOut,
            'Locked?' => $isLocked,
        );

        $table = $this->get('helper.table')->create();

        foreach ($info as $label => $value) {
            $table->addRow(array($label, $value));
        }

        $table->render($output);
    }
}
