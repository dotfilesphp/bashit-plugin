<?php

declare(strict_types=1);

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\BashIt;

use Dotfiles\Core\Constant;
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Util\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $installDir;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Parameters
     */
    private $parameters;

    public function __construct(Dispatcher $dispatcher, Parameters $parameters, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;
        $this->parameters = $parameters;
        $this->logger = $logger;
        $this->installDir = $parameters->get('dotfiles.install_dir');
    }

    public static function getSubscribedEvents()
    {
        return array(
            Constant::EVENT_PRE_PATCH => 'onPatchEvent',
        );
    }

    public function onPatchEvent(PatchEvent $event): void
    {
        $this->logger->info('Installing <comment>Bash-IT</comment>');

        $bashItConfig = $this->renderConfig();
        $target = $this->installDir.'/bash-it.bash';
        file_put_contents($target, $bashItConfig, LOCK_EX);
        $this->logger->info("BashIt configuration written in: <comment>$target</comment>");

        $patch = <<<EOC
# Load Bash It
source "{$this->installDir}/bash-it.bash"
EOC;
        $event->addPatch('.bashrc', $patch);
        $this->copySource();
    }

    /**
     * @param mixed $installDir
     */
    public function setInstallDir($installDir): void
    {
        $this->installDir = $installDir;
    }

    private function copySource(): void
    {
        $fs = new Filesystem();
        $source = __DIR__.'/../vendor/bash-it/bash-it';
        if (!is_dir($source)) {
            $source = getenv('DOTFILES_BACKUP_DIR').'/vendor/bash-it/bash-it';
        }
        $finder = Finder::create()
            ->in($source)
            ->ignoreVCS(true)
            ->exclude('test')
            ->exclude('test_lib')
        ;
        $target = $this->parameters->get('dotfiles.vendor_dir').'/bash-it';
        $fs->mirror($source, $target, $finder, array('override' => true));
    }

    private function renderConfig()
    {
        $config = $this->parameters;
        $exports = array(
            'GIT_HOSTING' => $config->get('bash-it.git_hosting'),
            'BASH_IT_THEME' => $config->get('bash-it.theme_name'),
            'IRC_CLIENT' => $config->get('bash-it.irc_client'),
            'TODO' => $config->get('bash-it.todo'),
            'SCM_CHECK' => $config->get('bash-it.scm_check'),
            'BASH_IT_AUTOMATIC_RELOAD_AFTER_CONFIG_CHANGE' => $config->get('bash-it.automatic_reload'),

            // theme section
            'THEME_SHOW_CLOCK_CHAR' => $config->get('bash-it.theme.show_clock_char'),
            'THEME_CLOCK_CHAR_COLOR' => $config->get('bash-it.theme.clock_char_color'),
            'THEME_SHOW_CLOCK' => $config->get('bash-it.theme.show_clock'),
            'THEME_SHOW_CLOCK_COLOR' => $config->get('bash-it.theme.clock_color'),
            'THEME_CLOCK_FORMAT' => $config->get('bash-it.theme.clock_format'),
        );

        if (null !== ($test = $config->get('bash-it.short_hostname'))) {
            $exports['SHORT_HOSTNAME'] = $test;
        }

        if (null !== ($test = $config->get('bash-it.short_user'))) {
            $exports['SHORT_USER'] = $test;
        }

        if ($config->get('bash-it.short_term_line')) {
            $exports['SHORT_TERM_LINE'] = true;
        }

        if (null !== ($test = $config->get('bash-it.vcprompt_executable'))) {
            $exports['VCPROMPT_EXECUTABLE'] = $test;
        }

        // theme
        if (null !== ($test = $config->get('bash-it.theme.clock_char'))) {
            $exports['THEME_CLOCK_CHAR'] = $test;
        }

        ksort($exports);
        // begin generate contents
        $targetDir = $this->installDir.'/vendor/bash-it';
        $contents = array(
            "export BASH_IT=\"${targetDir}\"",
        );
        foreach ($exports as $name => $value) {
            if (is_string($value)) {
                $value = '"'.$value.'"';
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $contents[] = "export $name=$value";
            $this->logger->debug("+bash-config: export <comment>$name</comment> = <comment>$value</comment>");
        }
        if (!$config->get('bash-it.check_mail')) {
            $contents[] = 'unset MAILCHECK';
            $this->logger->debug('+bash-config: unset <comment>MAILCHECK</comment>');
        }

        $contents[] = 'source "$BASH_IT"/bash_it.sh';
        $contents[] = "\n";

        return implode("\n", $contents);
    }
}
