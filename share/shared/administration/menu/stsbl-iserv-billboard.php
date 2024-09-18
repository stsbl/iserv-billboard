<?php

declare(strict_types=1);

use IServ\Bundle\AdminIntegration\Config\MenuConfigurator;
use IServ\Bundle\AdminIntegration\Config\MenuIcon;
use IServ\Bundle\AdminIntegration\Menu\Domain\AdminPage;

return static function (MenuConfigurator $config): void {
    $config
        ->get(AdminPage::MODULES->id())
        ->add(
            key: 'billboard',
            label: _('Bill-Board'),
            url: '/manage',
            icon: new MenuIcon(name: 'fa-billboard'),
            accessExpr: 'user.hasPrivilege("b1412593-5db4-4044-b53f-ed0b258b5cea")',
            moduleId: 'billboard',
        )
    ;
};
