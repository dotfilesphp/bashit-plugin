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

use Dotfiles\Core\Tests\Helper\BaseTestCase;
use Dotfiles\Plugins\BashIt\BashItPlugin;
use Dotfiles\Plugins\BashIt\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BashItPluginTest extends BaseTestCase
{
    public function testPlugin(): void
    {
        $plugin = new BashItPlugin();
        $this->assertEquals('bash-it', $plugin->getName());

        $parameters = $this->createMock(ParameterBagInterface::class);
        $parameters->expects($this->once())
            ->method('add')
            ->with($this->isType('array'))
        ;

        $builder = $this->createMock(ContainerBuilder::class);
        $builder->expects($this->once())
            ->method('getReflectionClass')
            ->with(Configuration::class)
            ->willReturn(new \ReflectionClass(Configuration::class))
        ;
        $builder->expects($this->once())
            ->method('getParameterBag')
            ->willReturn($parameters)
        ;
        $builder->expects($this->once())
            ->method('fileExists')
            ->with($this->stringContains('services.yaml'))
        ;
        $plugin->load(array(), $builder);
    }
}
