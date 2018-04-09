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

use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Plugins\BashIt\BashItPlugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BashItPluginTest extends BaseTestCase
{
    public function testPlugin(): void
    {
        $plugin = new BashItPlugin();
        $this->assertEquals('bashit', $plugin->getName());

        $builder = $this->createMock(ContainerBuilder::class);
        $builder->expects($this->once())
            ->method('fileExists')
            ->with($this->stringContains('services.yaml'))
        ;
        $plugin->load(array(), $builder);
    }
}
