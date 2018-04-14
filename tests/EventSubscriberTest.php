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

namespace Dotfiles\Plugins\BashIt\Tests;

use Dotfiles\Core\Constant;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Tests\BaseTestCase;

class EventSubscriberTest extends BaseTestCase
{
    public function testDispatch(): void
    {
        $dispatcher = $this->getService(Dispatcher::class);

        $event = new PatchEvent(array());
        $dispatcher->dispatch(Constant::EVENT_PRE_PATCH, $event);

        $patches = $event->getPatches();

        $installDir = $this->getParameters()->get('dotfiles.install_dir');
        $this->assertArrayHasKey('.bashrc', $patches);
        $this->assertDirectoryExists($installDir.'/vendor/bash-it');
        $this->assertFileExists($installDir.'/bash-it.bash');
    }
}
