<?php

namespace Themicly\Shopcrafty\Tests;

use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Themicly\Shopcrafty\ShopcraftyServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [LivewireServiceProvider::class, ShopcraftyServiceProvider::class];
    }
}
