<?php

namespace Themicly\Shopcrafty\Modules\CMS;

use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;

class CMSServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'CMS';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }
}
