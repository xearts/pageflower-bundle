<?php
/*
 * Copyright (c) 2014-2015 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * This file is part of PHPMentorsPageflowerBundle.
 *
 * This program and the accompanying materials are made available under
 * the terms of the BSD 2-Clause License which accompanies this
 * distribution, and is available at http://opensource.org/licenses/BSD-2-Clause
 */

namespace PHPMentors\PageflowerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ReplaceTemplatingGlobalsDefinitionPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('templating.globals')) {
            foreach ($container->getDefinition('templating.globals')->getMethodCalls() as $methodCall) {
                list($method, $arguments) = $methodCall;
                $container->getDefinition('phpmentors_pageflower.conversational_global_variables')->addMethodCall($method, $arguments);
            }

            $container->setAlias('templating.globals', 'phpmentors_pageflower.conversational_global_variables');
        }
    }
}
