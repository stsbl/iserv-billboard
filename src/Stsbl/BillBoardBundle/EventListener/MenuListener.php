<?php
// src/Stsbl/BillBoardBundle/EventListener/MenuListener.php
namespace Stsbl\BillBoardBundle\EventListener;

use IServ\AdminBundle\EventListener\AdminMenuListenerInterface;
use IServ\CoreBundle\Event\MenuEvent;
use IServ\CoreBundle\EventListener\MainMenuListenerInterface;
use Stsbl\BillBoardBundle\Security\Privilege;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license http://gnu.org/licenses/gpl-3.0 GNU General Public License 
 */

class MenuListener implements MainMenuListenerInterface, AdminMenuListenerInterface {
    /**
     * @param \IServ\CoreBundle\Event\MenuEvent $event
     */
    public function onBuildMainMenu(MenuEvent $event)
    {
        // check if user is privileged
        if ($event->getAuthorizationChecker()->isGranted(Privilege::BILLBOARD)
            || $event->getAuthorizationChecker()->isGranted(Privilege::BILLBOARD_CREATE)
            || $event->getAuthorizationChecker()->isGranted(Privilege::BILLBOARD_MODERATE)
            || $event->getAuthorizationChecker()->isGranted(Privilege::BILLBOARD_MANAGE)
        ) {            
            $menu = $event->getMenu(self::COMMUNICATION);
            $item = $menu->addChild('billboard', array(
                'route' => 'crud_billboard_index',
                'label' => _('Bill-Board'),
                'extras' => array(
                  'icon' => 'billboard-empty',
                  'icon_style' => 'fugue',
                ),
            ));
            $item->setExtra('orderNumber', 20);
        }
    }
    
    /**
     * @param \IServ\CoreBundle\Event\MenuEvent $event
     */
    public function onBuildAdminMenu(MenuEvent $event)
    {
        // check if user is privileged
        if ($event->getAuthorizationChecker()->isGranted(Privilege::BILLBOARD_MANAGE))
        {
            $menu = $event->getMenu();
            $block = $menu->addChild(_('Bill-Board'));
            $block->setExtra('orderNumber', 30);

            $item = $block->addChild('billboard_admin', array(
                'route' => 'admin_billboard',
                'label' => _('Bill-Board'),
            ));
            $item->setExtra('icon', 'billboard-empty');
            $item->setExtra('icon_style', 'fugue');
            
            $item = $block->addChild('billboard_category', array(
                'route' => 'admin_billboard_category_index',
                'label' => _('Categories'),
            ));
            $item->setExtra('icon', 'category');
            $item->setExtra('icon_style', 'fugue');
        }
    }
}
