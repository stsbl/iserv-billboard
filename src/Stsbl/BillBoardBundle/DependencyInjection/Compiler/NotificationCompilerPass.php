<?php declare(strict_types = 1);
// src/Stsbl/BillBoardBundle/DependencyInjection/Compiler/NotificationCompilerPass.php
namespace Stsbl\BillBoardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compiler pass adds notification types to the stack of known ones.
 */
class NotificationCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('iserv.notification_manager')) {
            $container->getDefinition('iserv.notification_manager')
                ->addMethodCall('addType', ['billboard', 'Bill-Board'])
            ;
        }
    }
}
