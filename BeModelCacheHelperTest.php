<?php

declare(strict_types=1);
ini_set('xdebug.remote_enable', '0');

define('ABSPATH', '');
define('DS', '/');

require_once 'vendor/autoload.php';
require_once('BeModelCacheHelper.php');

use PHPUnit\Framework\TestCase;

final class BeModelCacheHelperTest extends TestCase
{

    public function test_cacheDir_withDefaultValue(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getOption',
                'getDocumentRoot',
            ]
        )->getMock();
        $mock->method('getDocumentRoot')->willReturn('XYZ');
        $mock->method('getOption')->with($mock::CACHE_PATH)->willReturn(false);
        $this->assertEquals('XYZ/wp-content/be-cache', $mock->getCachePath());
        $mock->expects($this->once())->method('getDocumentRoot')->with();
        $mock->expects($this->once())->method('getOption')->with(
            $this->equalTo($mock::CACHE_PATH)
        );
        $mock->getCachePath();
    }

    public function test_cacheDir_withValueFromOption(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            ['getOption', 'getDocumentRoot']
        )->getMock();
        $mock->method('getDocumentRoot')->willReturn('XYZ');
        $mock->method('getOption')->with($mock::CACHE_PATH)->willReturn('ABC');
        $this->assertEquals('ABC', $mock->getCachePath());
        $mock->expects($this->never())->method('getDocumentRoot')->with();
        $mock->expects($this->once())->method('getOption')->with(
            $this->equalTo($mock::CACHE_PATH)
        );
        $mock->getCachePath();
    }

    public function test_getCacheEngine_withDefaultValue(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(['getOption'])
            ->getMock();
        $mock->method('getOption')->with($mock::CACHE_ENGINE)->willReturn(null);
        $this->assertEquals('Files', $mock->getCacheEngine());
        $mock->expects($this->once())->method('getOption')->with(
            $mock::CACHE_ENGINE
        );
        $mock->getCacheEngine();
    }

    public function test_getCacheEngine_withValueFromOption(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(['getOption'])
            ->getMock();
        $mock->method('getOption')->with($mock::CACHE_ENGINE)->willReturn(
            'XYZ'
        );
        $this->assertEquals('XYZ', $mock->getCacheEngine());
        $mock->expects($this->once())->method('getOption')->with(
            $mock::CACHE_ENGINE
        );
        $mock->getCacheEngine();
    }

    public function test_getThemeShortCodesDir_withDefaultValue(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(['getOption'])
            ->getMock();
        $mock->method('getOption')->with($mock::THEME_SHORT_CODES_DIR)
            ->willReturn(null);
        $this->assertEquals('shortcode', $mock->getThemeShortCodesDir());
        $mock->expects($this->once())->method('getOption')->with(
            $mock::THEME_SHORT_CODES_DIR
        );
        $mock->getThemeShortCodesDir();
    }

    public function test_getThemeShortCodesDir_withValueFromOption(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(['getOption'])
            ->getMock();
        $mock->method('getOption')->with($mock::THEME_SHORT_CODES_DIR)
            ->willReturn('XYZ');
        $this->assertEquals('XYZ', $mock->getThemeShortCodesDir());
        $mock->expects($this->once())->method('getOption')->with(
            $mock::THEME_SHORT_CODES_DIR
        );
        $mock->getThemeShortCodesDir();
    }

    public function test_cachePurge_withDataPurged(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getTime',
                'getOption',
                'updateOption',
                'getCachePurgeCycle',
                'clearCacheInstance'
            ]
        )->getMock();
        $mock->method('getTime')->willReturn(time());
        $mock->method('getOption')->with($mock::LAST_PURGE)->willReturn(
            time() - 200
        );
        $mock->method('updateOption')->with($mock::LAST_PURGE, time())
            ->willReturn(true);
        $mock->method('getCachePurgeCycle')->willReturn(100);
        $cacheInstance = new StdClass();
        $this->assertTrue($mock->cachePurge($cacheInstance));
        $mock->expects($this->once())->method('updateOption')
            ->with($this->equalTo($mock::LAST_PURGE), $this->equalTo(time()));
        $mock->expects($this->once())->method('getTime');
        $mock->expects($this->once())->method('getOption')->with(
            $this->equalTo($mock::LAST_PURGE)
        );
        $mock->expects($this->once())->method('getCachePurgeCycle');
        $mock->expects($this->once())->method('clearCacheInstance')->with(
            $this->equalTo($cacheInstance)
        );
        $mock->cachePurge($cacheInstance);
    }

    public function test_cachePurge_withDataNotPurged(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getTime',
                'getOption',
                'updateOption',
                'getCachePurgeCycle',
                'clearCacheInstance'
            ]
        )->getMock();
        $time = time();
        $mock->method('getTime')->willReturn($time);
        $mock->method('getOption')->with($mock::LAST_PURGE)->willReturn(
            time() - 100
        );
        $mock->method('updateOption')->with($mock::LAST_PURGE, time())
            ->willReturn(true);
        $mock->method('getCachePurgeCycle')->willReturn(200);
        $stdClass = new StdClass();
        $this->assertFalse($mock->cachePurge($stdClass));
        $mock->expects($this->never())->method('updateOption');
        $mock->expects($this->never())->method('updateOption');
        $mock->expects($this->once())->method('getTime')->with();
        $mock->expects($this->once())->method('getOption')->with(
            $this->equalTo($mock::LAST_PURGE)
        );
        $mock->expects($this->once())->method('getCachePurgeCycle')->with();
        $mock->expects($this->never())->method('clearCacheInstance')->with(
            $this->equalTo($stdClass)
        );
        $mock->cachePurge($stdClass);
    }

    public function test_getCachePurgeCycle_withDefaultValue(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(['getOption'])
            ->getMock();
        $this->assertEquals(86400, $mock->getCachePurgeCycle());
        $mock->expects($this->once())->method('getOption')->with(
            $this->equalTo($mock::CACHE_PURGE_CYCLE)
        );
        $mock->getCachePurgeCycle();
    }

    public function test_getCachePurgeCycle_withValueFromOption(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(['getOption'])
            ->getMock();
        $mock->method('getOption')->with($mock::CACHE_PURGE_CYCLE)->willReturn(
            123
        );
        $this->assertEquals(123, $mock->getCachePurgeCycle());
        $mock->expects($this->once())->method('getOption')->with(
            $this->equalTo($mock::CACHE_PURGE_CYCLE)
        );
        $mock->getCachePurgeCycle();
    }

    public function test_isMobile_withTrue(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(['getHttpUserAgent'])
            ->getMock();
        $httpUserAgent = 'Mozilla/5.0 (Linux; Android 7.0; SM-G930VC Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, 
            like Gecko) Version/4.0 Chrome/58.0.3029.83 Mobile Safari/537.36';
        $mock->method('getHttpUserAgent')->willReturn($httpUserAgent);
        $this->assertTrue($mock->isMobile());
        $mock->expects($this->once())->method('getHttpUserAgent')->with();
        $mock->isMobile();
    }

    public function test_isMobile_withFalse(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(['getHttpUserAgent'])
            ->getMock();
        $httpUserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, 
            like Gecko) Chrome/75.0.3770.142 Safari/537.36';
        $mock->method('getHttpUserAgent')->willReturn($httpUserAgent);
        $this->assertFalse($mock->isMobile());
        $mock->expects($this->once())->method('getHttpUserAgent')->with();
        $mock->isMobile();
    }

    public function test_getKey_withIsMobileFalse(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getRequestUri',
                'getHttpHost',
                'isMobile',
            ]
        )->getMock();
        $mock->method('getRequestUri')->willReturn('XYZ');
        $mock->method('getHttpHost')->willReturn('123');
        $mock->method('isMobile')->willReturn(false);
        $this->assertEquals(
            '664b5499bac1f61ebc23be8ae95ac532',
            $mock->getKey('myName', ['abc' => 'bcd'])
        );
        $mock->expects($this->once())->method('getRequestUri')->with();
        $mock->expects($this->once())->method('getHttpHost')->with();
        $mock->expects($this->once())->method('isMobile')->with();
        $mock->getKey('myName', ['abc' => 'bcd']);
    }

    public function test_getKey_withIsMobileTrue(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getRequestUri',
                'getHttpHost',
                'isMobile',
            ]
        )->getMock();
        $mock->method('getRequestUri')->willReturn('XYZ');
        $mock->method('getHttpHost')->willReturn('123');
        $mock->method('isMobile')->willReturn(true);
        $this->assertEquals(
            '2666b54c7a030b625151d5c5ed1f42e5',
            $mock->getKey('myName', ['abc' => 'bcd'])
        );
        $mock->expects($this->once())->method('getRequestUri')->with();
        $mock->expects($this->once())->method('getHttpHost')->with();
        $mock->expects($this->once())->method('isMobile')->with();
        $mock->getKey('myName', ['abc' => 'bcd']);
    }

    public function test_addKey_withSuccess(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getCacheInstance',
                'setCacheInstance'
            ]
        )->getMock();
        $stdClass = new StdClass();
        $this->assertTrue(
            $mock->addKey($stdClass, 'myName', 'myKey', 'myValue')
        );
        $mock->expects($this->once())->method('getCacheInstance')
            ->with($this->equalTo($stdClass), $this->equalTo('myName'));
        $mock->expects($this->once())->method('setCacheInstance')
            ->with($this->equalTo($stdClass), 'myName', ['myKey' => 'myValue']);
        $mock->addKey(new $stdClass, 'myName', 'myKey', 'myValue');
    }

    public function test_addKey_withKeyAlreadyInCache(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getCacheInstance',
                'setCacheInstance'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn(['myKey' => 'myName']);
        $stdClass = new StdClass();
        $this->assertFalse(
            $mock->addKey($stdClass, 'myName', 'myKey', 'myValue')
        );
        $mock->expects($this->once())->method('getCacheInstance')
            ->with($this->equalTo($stdClass), $this->equalTo('myName'));
        $mock->expects($this->never())->method('setCacheInstance');
        $mock->addKey($stdClass, 'myName', 'myKey', 'myValue');
    }

    public function test_cleanCacheById_withMatch(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getCacheInstance',
                'deleteCacheInstance'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn(['myKey' => 123]);
        $stdClass = new StdClass();
        $this->assertTrue($mock->cleanCacheById($stdClass, 'myName', 123));
        $mock->expects($this->exactly(2))->method('getCacheInstance')
            ->with($this->equalTo($stdClass), $this->equalTo('myName'));
        $mock->expects($this->once())->method('deleteCacheInstance')
            ->with($this->equalTo($stdClass), $this->equalTo('myKey'));
        $mock->cleanCacheById($stdClass, 'myName', 123);
    }

    public function test_cleanCacheById_withNoMatch(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getCacheInstance',
                'deleteCacheInstance'
            ]
        )->getMock();
        $mock->method('getCacheInstance')->willReturn(['myKey' => 456]);
        $stdClass = new StdClass();
        $this->assertFalse($mock->cleanCacheById($stdClass, 'myName', 123));
        $mock->expects($this->exactly(2))->method('getCacheInstance')
            ->with($this->equalTo($stdClass), $this->equalTo('myName'));
        $mock->expects($this->never())->method('deleteCacheInstance');
        $mock->cleanCacheById($stdClass, 'myName', 123);
    }

    public function test_cleanCacheById_withDataNotFound(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            [
                'getCacheInstance',
                'deleteCacheInstance'
            ]
        )->getMock();
        $stdClass = new StdClass();
        $this->assertFalse($mock->cleanCacheById($stdClass, 'myName', 123));
        $mock->expects($this->once())->method('getCacheInstance')
            ->with($this->equalTo($stdClass), $this->equalTo('myName'));
        $mock->expects($this->never())->method('deleteCacheInstance');
        $mock->cleanCacheById($stdClass, 'myName', 123);
    }

    public function test_flushCache(): void
    {
        $mock = $this->MockBeModelCacheHelper->setMethods(
            ['clearCacheInstance']
        )->getMock();
        $mock->method('clearCacheInstance')->willReturn(true);
        $stdClass = new StdClass();
        $mock->expects($this->once())->method('clearCacheInstance')->with(
            $this->equalTo($stdClass)
        );
        $mock->flushCache($stdClass);
    }

    protected function setUp(): void
    {
        $this->MockBeModelCacheHelper = $this->getMockBuilder(
            webforyou\be\modelWithCache\BeModelCacheHelper::class
        )
            ->setMethods(null);
    }
}
