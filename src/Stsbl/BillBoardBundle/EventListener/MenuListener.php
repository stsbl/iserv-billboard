<?php
// src/Stsbl/BillBoardBundle/EventListener/MenuListener.php
namespace Stsbl\BillBoardBundle\EventListener;

use IServ\AdminBundle\EventListener\AdminMenuListenerInterface;
use IServ\CoreBundle\Event\MenuEvent;
use IServ\CoreBundle\EventListener\MainMenuListenerInterface;
use Stsbl\BillBoardBundle\Security\Privilege;

/*
 * The MIT License
 *
 * Copyright 2017 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
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
