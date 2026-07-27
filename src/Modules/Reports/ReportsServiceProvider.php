<?php

namespace Themicly\Shopcrafty\Modules\Reports;

use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;

class ReportsServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Reports';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }
}
