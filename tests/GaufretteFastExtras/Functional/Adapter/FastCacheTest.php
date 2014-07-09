<?php

namespace GaufretteFastExtras\Functional\Adapter;

use Gaufrette\Adapter\InMemory;
use Gaufrette\Filesystem;
use GaufretteFastExtras\Adapter\FastCache;

class FastCacheTest extends FunctionalTestCase
{
    public function setUp()
    {
        $this->filesystem = new Filesystem(new FastCache(new InMemory(), new InMemory()));
    }

    /**
     * @test
     */
    public function shouldNotNeedReloadAfterSourceChanged()
    {
        $source = new InMemory();
        $cache = new InMemory();
        $cachedFs = new FastCache($source, $cache, 30);

        // The source has been updated after the cache has been created.
        $mtime = time();
        $source->setFile('foo', 'baz', $mtime - 10);
        $cache->setFile('foo', 'bar', $mtime - 20);

        $this->assertFalse($cachedFs->needsReload('foo'));
        $this->assertEquals('bar', $cachedFs->read('foo'));
    }
}
