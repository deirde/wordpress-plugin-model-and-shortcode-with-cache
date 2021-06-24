<?php
declare(strict_types=1);
ini_set('xdebug.remote_enable', '0');

define('ABSPATH', '');
define('DS', '/');

require_once 'vendor/autoload.php';
require_once('BeModelCacheHelper.php');
require_once('BeModelCacheInstance.php');
require_once('BeModelMenuWithCache.php');

use PHPUnit\Framework\TestCase;

final class §BeModelMenuWithCacheTest extends TestCase
{

    public function test_handleUpdateNavMenu_withNoDataFound(): void
    {
        $mock = $this->BeModelMenuWithCache->setMethods(
            [
                'getNavMenuLocations',
                'getNavMenuLocation',
                'addAction',
                'getCacheInstance'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn(null);
        $mock->method('getNavMenuLocations')->willReturn(["XYZ"]);
        $mock->method('getNavMenuLocation')->willReturn(null);
        $this->assertEquals(null, $mock->wpGetNavMenuItems('ABC', []));
        $mock->expects($this->never())->method('getNavMenuLocations');
        $mock->expects($this->once())->method('getNavMenuLocation')->with(
            $this->equalTo('ABC')
        );
        $mock->wpGetNavMenuItems('ABC', []);
    }

    public function test_handleUpdateNavMenu_withNoDataInCache(): void
    {
        $mock = $this->BeModelMenuWithCache->setMethods(
            [
                'getNavMenuLocations',
                'getNavMenuLocation',
                'addAction',
                'getCacheInstance',
                'addCacheKey',
                'getNavMenuItems',
                'setCacheInstance'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn(null);
        $mock->method('getNavMenuLocations')->willReturn(["ABC", "XYZ"]);
        $mock->method('getNavMenuLocation')->willReturn(0);
        $stdClass = new StdClass();
        $stdClass->toArray = function () {
        };
        $mock->method('getNavMenuItems')->willReturn([]);
        $this->assertEquals([], $mock->wpGetNavMenuItems('ABC', []));
        $mock->expects($this->never())->method('getNavMenuLocations');
        $mock->expects($this->once())->method('getNavMenuLocation')->with(
            $this->equalTo('ABC')
        );
        $mock->expects($this->once())->method('getCacheInstance')
            ->with($this->equalTo('a2bdc91c707c73d1d2a89b6a1ea62127'));
        $mock->expects($this->once())->method('addCacheKey')
            ->with(
                $this->equalTo('a2bdc91c707c73d1d2a89b6a1ea62127'),
                $this->equalTo(0)
            );
        $mock->expects($this->once())->method('getNavMenuItems')->with(
            $this->equalTo(0)
        );
        $mock->expects($this->once())->method('setCacheInstance')
            ->with(
                $this->equalTo('a2bdc91c707c73d1d2a89b6a1ea62127'),
                $this->equalTo([])
            );
        $mock->wpGetNavMenuItems('ABC', []);
    }

    public function test_handleUpdateNavMenu_withDataInCache(): void
    {
        $mock = $this->BeModelMenuWithCache->setMethods(
            [
                'getNavMenuLocations',
                'addAction',
                'getCacheInstance'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $mock->wpGetNavMenuItems('ABC', []));
        $mock->expects($this->once())->method('getCacheInstance')
            ->with($this->equalTo('a2bdc91c707c73d1d2a89b6a1ea62127'));
        $mock->wpGetNavMenuItems('ABC', []);
    }

    protected function setUp(): void
    {
        $this->MockBeModelCacheHelper = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelCacheHelper::class
        )
            ->setMethods(
                [
                    'cleanCacheById',
                    'getHttpHost',
                    'getRequestUri',
                    'getHttpUserAgent'
                ]
            )
            ->getMock();
        $this->MockBeModelCacheHelper->method('cleanCacheById')->willReturn(
            true
        );
        $this->BeModelCacheInstance = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelCacheInstance::class
        )
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->BeModelCacheInstance->psr16adapter = null;
        $this->BeModelMenuWithCache = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelMenuWithCache::class
        )
            ->setConstructorArgs(
                [$this->BeModelCacheInstance, $this->MockBeModelCacheHelper]
            )
            ->setMethods(null);
        $this->BeModelMenuWithCache->navMenuLocations = [];
    }

}
