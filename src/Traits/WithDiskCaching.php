<?php

namespace Blinq\LLM\Traits;

trait WithDiskCaching
{
    public string $cachePath = "/tmp/blinq-openai";

    public function getCachePath() : string
    {
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }

        return $this->cachePath;
    }

    public function getCacheFile(string $path) : string
    {
        return $this->getCachePath() . "/" . $path;
    }

    public function createCacheKey(mixed $request)
    {
        if (is_array($request)) {
            $request = json_encode($request);
        }

        if (is_object($request)) {
            $request = json_encode($request);
        }

        return md5($request);
    }

    public function getCache(mixed $request) : mixed
    {
        $path = $this->createCacheKey($request);

        $cacheFile = $this->getCacheFile($path);

        if (!file_exists($cacheFile)) {
            return null;
        }

        return json_decode(file_get_contents($cacheFile), true);
    }

    public function setCache(mixed $request, mixed $result)
    {
        $path = $this->createCacheKey($request);

        $cacheFile = $this->getCacheFile($path);

        if (!file_exists(dirname($cacheFile))) {
            mkdir(dirname($cacheFile), 0777, true);
        }

        file_put_contents($cacheFile, json_encode($result));
    }
}
