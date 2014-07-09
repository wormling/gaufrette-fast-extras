<?php

namespace GaufretteFastExtras\Adapter;

use Gaufrette\File;
use Gaufrette\Adapter;
use Gaufrette\Adapter\InMemory as InMemoryAdapter;

/**
 * FastCache adapter
 *
 * @package Gaufrette
 * @author  Antoine Hérault <antoine.herault@gmail.com>
 * @author Brian Smith <wormling@gmail.com>
 */
class FastCache implements Adapter,
 Adapter\MetadataSupporter
{
    /**
     * @var Adapter
     */
    protected $source;

    /**
     * @var Adapter
     */
    protected $cache;

    /**
     * @var integer
     */
    protected $ttl;

    /**
     * @var Adapter
     */
    protected $serializeCache;

    /**
     * Constructor
     *
     * @param Adapter $source         The source adapter that must be cached
     * @param Adapter $cache          The adapter used to cache the source
     * @param integer $ttl            Time to live of a cached file
     * @param Adapter $serializeCache The adapter used to cache serializations
     */
    public function __construct(Adapter $source, Adapter $cache, $ttl = 0, Adapter $serializeCache = null)
    {
        $this->source = $source;
        $this->cache = $cache;
        $this->ttl = $ttl;

        if (!$serializeCache) {
            $serializeCache = new InMemoryAdapter();
        }
        $this->serializeCache = $serializeCache;
    }

    /**
     * Returns the time to live of the cache
     *
     * @return integer $ttl
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Defines the time to live of the cache
     *
     * @param integer $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * {@InheritDoc}
     */
    public function read($key)
    {
        if ($this->needsReload($key)) {
            $contents = $this->source->read($key);
            $this->cache->write($key, $contents);
        } else {
            $contents = $this->cache->read($key);
        }

        return $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function rename($key, $new)
    {
        $this->source->rename($key, $new);

        return $this->cache->rename($key, $new);
    }

    /**
     * {@inheritDoc}
     */
    public function write($key, $content, array $metadata = null)
    {
        $this->source->write($key, $content);

        return $this->cache->write($key, $content);
    }

    /**
     * {@inheritDoc}
     */
    public function exists($key)
    {
        if ($this->cache->exists($key)) {
            return true;
        }
        
        return $this->source->exists($key);
    }

    /**
     * {@inheritDoc}
     */
    public function mtime($key)
    {
        return $this->source->mtime($key);
    }

    /**
     * {@inheritDoc}
     */
    public function keys()
    {
        $cacheFile = 'keys.cache';
        if ($this->needsRebuild($cacheFile)) {
            $keys = $this->source->keys();
            sort($keys);
            $this->serializeCache->write($cacheFile, serialize($keys));
        } else {
            $keys = unserialize($this->serializeCache->read($cacheFile));
        }

        return $keys;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        return $this->source->delete($key) && $this->cache->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function isDirectory($key)
    {
        return $this->source->isDirectory($key);
    }

    /**
     * {@inheritDoc}
     */
    public function setMetadata($key, $metadata)
    {
        if ($this->source instanceof MetadataSupporter) {
            $this->source->setMetadata($key, $metadata);
        }

        if ($this->cache instanceof MetadataSupporter) {
            $this->cache->setMetadata($key, $metadata);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($key)
    {
        if ($this->source instanceof MetadataSupporter) {
            return $this->source->getMetadata($key);
        }

        return false;
    }

    /**
     * Indicates whether the cache for the specified key needs to be reloaded
     *
     * @todo Would be nice to not reload the cached file if the source file hasn't changed based on mtime
     * @param string $key
     */
    public function needsReload($key)
    {
        $needsReload = true;

        if ($this->cache->exists($key)) {
            try {
                $needsReload = false;
                $dateCache = $this->cache->mtime($key);
                $dateExpires = $dateCache + $this->ttl;
                
                if ($dateExpires < time()) {
                    $dateSource = $this->source->mtime($key);
                    $needsReload = $dateExpires < $dateSource;
                }
            } catch (\RuntimeException $e) { }
        }

        return $needsReload;
    }

    /**
     * Indicates whether the serialized cache file needs to be rebuild
     *
     * @param string $cacheFile
     */
    public function needsRebuild($cacheFile)
    {
        $needsRebuild = true;

        if ($this->serializeCache->exists($cacheFile)) {
            try {
                $needsRebuild = time() - $this->ttl >= $this->serializeCache->mtime($cacheFile);
            } catch (\RuntimeException $e) { }
        }

        return $needsRebuild;
    }
}