<?php
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
        // Replace event dispatcher with exception aware one
        if ($container->hasDefinition('iserv.notification_manager')) {
            // Add t10n to messages.php for label
            $container->getDefinition('iserv.notification_manager')->addMethodCall('addType', ['billboard', 'Bill-Board']);
        }
    }
}
