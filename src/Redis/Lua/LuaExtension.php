<?php

namespace Benzine\Redis\Lua;

abstract class LuaExtension
{
    protected \Redis $redis;
    protected ?string $hash = null;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function getFunctionNames(): array
    {
        $name = explode('\\', get_called_class());

        return [
            end($name),
        ];
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function load(): void
    {
        if (!$this->hash) {
            $exists = $this->redis->script('exists', $this->getScript());
            if (!$exists[0]) {
                $this->hash = $this->redis->script('load', $this->getScript());
            }
        }
        //printf("Loaded \"%s\" as \"%s\"\n", $this->getFunctionNames()[0], $this->hash);
    }

    abstract protected function getScript(): string;
}
