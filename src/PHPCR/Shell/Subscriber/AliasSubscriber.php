<?php

namespace PHPCR\Shell\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Helper\HelperSet;
use PHPCR\Shell\Event\PhpcrShellEvents;
use PHPCR\Shell\Event\CommandPreRunEvent;
use PHPCR\Shell\Console\Input\StringInput;

/**
 * Check to see if the input references a command alias and
 * modify the input to represent the command which it represents.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class AliasSubscriber implements EventSubscriberInterface
{
    /**
     * Lazy load helper
     *
     * @var HelperSet
     */
    protected $helperSet;

    public function __construct(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }

    protected function getConfig()
    {
        return $this->helperSet->get('config');
    }

    public static function getSubscribedEvents()
    {
        return array(
            PhpcrShellEvents::COMMAND_PRE_RUN => 'handleAlias',
        );
    }

    /**
     * Check for an alias and replace the input with a new string command
     * if the alias exists.
     *
     * @return string New command string (for testing purposes)
     */
    public function handleAlias(CommandPreRunEvent $event)
    {
        $input = $event->getInput();

        $commandName = $input->getFirstArgument();

        $aliasConfig = $this->getConfig()->getConfig('alias');

        if (!isset($aliasConfig[$commandName])) {
            return;
        }

        $commandTemplate = $aliasConfig[$commandName];
        $replaces = array();

        preg_match_all('{\{arg[0-9]+\}}', $commandTemplate, $matches);

        $args = array();
        if (isset($matches[0])) {
            $args = $matches[0];
        }

        $tokens = $input->getTokens();

        foreach ($tokens as $i => $token) {
            if (strstr($token, ' ')) {
                $token = escapeshellarg($token);
            }
            $replaces['{arg' . $i . '}'] = $token;
        }

        $command = strtr($commandTemplate, $replaces);

        foreach ($args as $arg) {
            $command = str_replace($arg, '', $command);
        }

        $command = trim($command);

        $newInput = new StringInput($command);
        $event->setInput($newInput);

        return $command;
    }
}
