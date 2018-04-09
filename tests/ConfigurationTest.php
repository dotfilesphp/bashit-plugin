<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Plugins\BashIt\Tests;

use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Plugins\BashIt\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends BaseTestCase
{
    public function testTreeBuilder()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(),array());

        $this->assertEquals('git@git.domain.com',$config['git_hosting']);
        $this->assertEquals('atomic',$config['theme_name']);
    }
}
