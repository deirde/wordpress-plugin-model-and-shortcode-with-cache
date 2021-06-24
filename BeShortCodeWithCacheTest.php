<?php

declare(strict_types=1);
ini_set('xdebug.remote_enable', '0');

define('ABSPATH', '');
define('DS', '/');

require_once 'vendor/autoload.php';
require_once('BeModelCacheHelper.php');
require_once('BeModelCacheInstance.php');
require_once('BeShortCodeWithCache.php');

use PHPUnit\Framework\TestCase;

final class BeShortCodeWithCacheTest extends TestCase
{

    public function test_handleUpdatePost_withNoDataFound(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'addAction',
                'getCacheInstance',
                'deleteCacheInstance',
                'addShortcode'
            ]
        )->getMock();
        $mock->expects($this->once())->method('getCacheInstance')
            ->with(
                $this->equalTo(
                    $this->MockBeModelCacheHelper::CACHE_FRAGMENT_SHORT_CODE
                )
            );
        $mock->expects($this->never())->method('deleteCacheInstance');
        $mock->handleUpdatePost();
    }

    public function test_handleUpdatePost_withDataFound(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'addAction',
                'getCacheInstance',
                'deleteCacheInstance',
                'addShortcode'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn(['A' => [], 'B' => []]);
        $mock->expects($this->once())->method('getCacheInstance')
            ->with(
                $this->equalTo(
                    $this->MockBeModelCacheHelper::CACHE_FRAGMENT_SHORT_CODE
                )
            );
        $mock->expects($this->exactly(3))
            ->method('deleteCacheInstance')
            ->withConsecutive(
                [$this->MockBeModelCacheHelper::CACHE_FRAGMENT_SHORT_CODE],
                ['A'],
                ['B']
            );
        $mock->handleUpdatePost();
    }

    public function test_includeTemplateFile_withFileFound(): void
    {
        $mock = $this->BeShortCodeWithCache->disableOriginalConstructor()
            ->setMethods(
                [
                    'addAction',
                    'getCacheInstance',
                    'addShortcode',
                    'getTemplateFile',
                    'doAction'
                ]
            )->getMock();
        $mock->method('getTemplateFile')->willReturn('ABC');
        $stdClass = new StdClass();
        $stdClass->query_vars = ['A' => 'B'];
        $this->assertTrue($mock->includeTemplateFile('ABC', [], $stdClass));
    }

    public function test_includeTemplateFile_withFileNotFound(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'getCacheInstance',
                'addShortcode',
                'getTemplateFile',
                'addAction',
            ]
        )->getMock();
        $stdClass = new StdClass();
        $stdClass->query_vars = [];
        $this->assertFalse($mock->includeTemplateFile('ABC', [], $stdClass));
    }

    public function test_isTemplateChanged_withFileNotChanged(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'getCacheInstance',
                'setCacheInstance',
                'deleteCacheInstance',
                'addShortcode',
                'addAction',
                'getCacheKey',
                'getTemplateFile',
            ]
        )->getMock();
        $mock->expects($this->never())->method('setCacheInstance');
        $mock->expects($this->never())->method('deleteCacheInstance');
        $mock->isTemplateChanged([]);
    }

    public function test_isTemplateChanged_withFileChanged(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'getCacheInstance',
                'setCacheInstance',
                'deleteCacheInstance',
                'addShortcode',
                'addAction',
                'getCacheKey',
                'getTemplateFile',
            ]
        )->getMock();
        $mock->method('getCacheInstance')->with(
            $this->MockBeModelCacheHelper::CACHE_FRAGMENT_SHORT_CODE
        )->willReturn(['A' => ['templateTime' => 123]]);
        $mock->method('getCacheKey')->willReturn('A');
        $mock->method('getTemplateFile')->willReturn(__FILE__);
        $mock->expects($this->once())->method('setCacheInstance')->with(
            $this->equalTo(
                $this->MockBeModelCacheHelper::CACHE_FRAGMENT_SHORT_CODE
            )
        );
        $mock->expects($this->once())->method('deleteCacheInstance')->with(
            $this->equalTo('A')
        );
        $mock->isTemplateChanged([]);
    }

    public function test_render_withCacheSaved(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'addShortcode',
                'addAction',
                'getView',
                'getTemplateFile',
                'setTemplateFile',
                'isTemplateChanged',
                'getCacheInstance',
                'getCacheKey',
                'setCacheInstance',
                'addKey',
                'includeTemplateFile',
                'getWpQuery'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn('');
        $mock->method('getTemplateFile')->willReturn(__FILE__);
        $stdClass = new StdClass();
        $mock->method('getWpQuery')->willReturn($stdClass);
        $mock->expects($this->once())->method('getCacheKey');
        $mock->expects($this->once())->method('setTemplateFile');
        $mock->expects($this->once())->method('getTemplateFile');
        $mock->expects($this->once())->method('isTemplateChanged');
        $mock->expects($this->once())->method('getCacheInstance');
        $mock->expects($this->once())->method('setCacheInstance');
        $mock->expects($this->once())->method('addKey');
        $mock->render([]);
    }

    public function test_render_withCacheNotSaved(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'addShortcode',
                'addAction',
                'getView',
                'getTemplateFile',
                'setTemplateFile',
                'isTemplateChanged',
                'getCacheInstance',
                'getCacheKey',
                'setCacheInstance',
                'addKey',
                'includeTemplateFile',
                'getWpQuery'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn('ABC');
        $mock->method('getTemplateFile')->willReturn(__FILE__);
        $stdClass = new StdClass();
        $mock->method('getWpQuery')->willReturn($stdClass);
        $mock->expects($this->once())->method('getCacheKey');
        $mock->expects($this->once())->method('setTemplateFile');
        $mock->expects($this->once())->method('getTemplateFile');
        $mock->expects($this->once())->method('isTemplateChanged');
        $mock->expects($this->once())->method('getCacheInstance');
        $mock->expects($this->never())->method('setCacheInstance');
        $mock->expects($this->never())->method('addKey');
        $mock->render([]);
    }

    public function test_addKey_withKeyNotSet(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'getCacheInstance',
                'setCacheInstance',
                'addShortcode',
                'addAction',
                'getTemplateFile'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn([]);
        $mock->method('getTemplateFile')->willReturn(__FILE__);
        $mock->expects($this->once())->method('setCacheInstance')->with(
            $this->equalTo(
                $this->MockBeModelCacheHelper::CACHE_FRAGMENT_SHORT_CODE
            )
        );
        $mock->addKey('', []);
    }

    public function test_addKey_withKeyAlreadySet(): void
    {
        $mock = $this->BeShortCodeWithCache->setMethods(
            [
                'getCacheInstance',
                'setCacheInstance',
                'addShortcode',
                'addAction',
                'getTemplateFile'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn(['ABC' => []]);
        $mock->method('getTemplateFile')->willReturn(__FILE__);
        $mock->expects($this->never())->method('setCacheInstance');
        $mock->addKey('ABC', []);
    }

    protected function setUp(): void
    {
        $this->MockBeModelCacheHelper = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelCacheHelper::class
        )->getMock();
        $this->BeModelCacheInstance = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelCacheInstance::class
        )
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->BeModelCacheInstance->psr16adapter = null;
        $this->BeShortCodeWithCache = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeShortCodeWithCache::class
        )
            ->setConstructorArgs(
                [$this->BeModelCacheInstance, $this->MockBeModelCacheHelper]
            )
            ->setMethods(null);
    }
}
