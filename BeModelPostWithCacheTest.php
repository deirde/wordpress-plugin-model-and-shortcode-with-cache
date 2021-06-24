<?php

declare(strict_types=1);
ini_set('xdebug.remote_enable', '0');

define('ABSPATH', '');
define('DS', '/');

require_once 'vendor/autoload.php';
require_once('BeModelCacheHelper.php');
require_once('BeModelCacheInstance.php');
require_once('BeModelPostWithCache.php');

use PHPUnit\Framework\TestCase;

final class §BeModelPostWithCacheTest extends TestCase
{

    public function test_handleUpdatePost_withNoDataFound(): void
    {
        $mock = $this->BeModelPostWithCache->setMethods(
            [
                'addAction',
                'getCacheInstance',
                'deleteCacheInstance',
                'getPosts'
            ]
        )->getMock();
        $mock->expects($this->once())->method('getCacheInstance')
            ->with(
                $this->equalTo(
                    $this->MockBeModelCacheHelper::CACHE_FRAGMENT_POST
                )
            );
        $mock->expects($this->never())->method('deleteCacheInstance');
        $mock->expects($this->never())->method('getPosts');
        $mock->handleUpdatePost();
    }

    public function test_handleUpdatePost_withDataFound(): void
    {
        $mock = $this->BeModelPostWithCache->setMethods(
            [
                'addAction',
                'getCacheInstance',
                'deleteCacheInstance',
                'getPosts'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn(['A' => [], 'B' => []]);
        $mock->expects($this->once())->method('getCacheInstance')
            ->with(
                $this->equalTo(
                    $this->MockBeModelCacheHelper::CACHE_FRAGMENT_POST
                )
            );
        $mock->expects($this->exactly(3))->method('deleteCacheInstance')
            ->withConsecutive(
                [$this->MockBeModelCacheHelper::CACHE_FRAGMENT_POST],
                ['A'],
                ['B']
            );
        $mock->expects($this->exactly(2))->method('getPosts')->withConsecutive(
            [[]],
            [[]]
        );
        $mock->handleUpdatePost();
    }

    public function test_getPosts_withPostsFoundAndDataNotInCache()
    {
        $mock = $this->BeModelPostWithCache->setMethods(
            [
                'addAction',
                'getCacheInstance',
                'setCacheInstance',
                'setParams',
                'getParams',
                'loadPosts',
                'addCacheKey'
            ]
        )->getMock();
        $mock->method('getParams')->with(
            $this->MockBeModelCacheHelper::PARAM_TTL
        )->willReturn(999);
        $mock->method('getCacheInstance')->willReturn(null);
        $params = [
            'post__in' => [1, 2],
            'ttl'      => 99999,
            'lazyLoad' => false
        ];
        $mock->method('loadPosts')->willReturn([1, 2, 3]);
        $mock->expects($this->once())->method('setParams')->with(
            $this->equalTo($params)
        );
        $mock->expects($this->once())->method('getCacheInstance');
        $mock->expects($this->once())->method('loadPosts');
        $mock->expects($this->once())->method('addCacheKey');
        $mock->expects($this->once())->method('setCacheInstance');
        $mock->getPosts($params);
    }

    public function test_getPosts_withPostsFoundAndDataInCache()
    {
        $mock = $this->BeModelPostWithCache->setMethods(
            [
                'addAction',
                'getCacheInstance',
                'setCacheInstance',
                'setParams',
                'getParams',
                'loadPosts',
                'addCacheKey'
            ]
        )->getMock();
        $mock->method('getParams')->with(
            $this->MockBeModelCacheHelper::PARAM_TTL
        )->willReturn(999);
        $mock->method('getCacheInstance')->willReturn([1, 2, 3]);
        $params = [
            'post__in' => [1, 2],
            'ttl'      => 99999,
            'lazyLoad' => false
        ];
        $mock->method('loadPosts')->willReturn([1, 2, 3]);
        $mock->expects($this->once())->method('setParams')->with(
            $this->equalTo($params)
        );
        $mock->expects($this->once())->method('getCacheInstance');
        $mock->expects($this->never())->method('loadPosts');
        $mock->expects($this->never())->method('addCacheKey');
        $mock->expects($this->never())->method('setCacheInstance');
        $mock->getPosts($params);
    }

    protected function setUp(): void
    {
        $this->MockBeModelCacheHelper = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelCacheHelper::class
        )
            ->getMock();
        $this->BeModelCacheInstance = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelCacheInstance::class
        )
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->BeModelCacheInstance->psr16adapter = null;
        $this->BeModelPostWithCache = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelPostWithCache::class
        )
            ->setConstructorArgs(
                [$this->BeModelCacheInstance, $this->MockBeModelCacheHelper]
            )
            ->setMethods(null);
    }
}
