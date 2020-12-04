<?php

namespace Benzine\Redis;

use Monolog\Logger;

class Redis extends \Redis
{
    private string $host;
    private int $port;
    private int $timeout;
    private Logger $logger;

    /** @var Lua\AbstractLuaExtension[] */
    private array $scripts;

    public function __construct(Logger $logger, $host, $port = 6379, $timeout = 0.0)
    {
        $this->logger = $logger;
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        parent::__construct();
    }

    public function __call($name, $arguments)
    {
        foreach ($this->scripts as $script) {
            foreach ($script->getFunctionNames() as $functionName) {
                if (strtolower($name) == strtolower($functionName)) {
                    $script->load();

                    return $this->evalSha($script->getHash(), $arguments);
                }
            }
        }
    }

    public function isAvailable(): bool
    {
        try {
            $this->ping('am I human?');

            return true;
        } catch (\RedisException $redisException) {
            $this->logger->warning("Caught redis exception during isAvailable() call: {$redisException->getMessage()}");

            return false;
        }
    }

    /**
     * @param string $key
     *
     * @return bool|mixed|string
     */
    public function get($key)
    {
        if (!$this->isConnected()) {
            parent::pconnect($this->host, $this->port, $this->timeout);
            $this->initialiseExtensions();
        }

        return parent::get($key);
    }

    public function initialiseExtensions(): void
    {
        $this->scripts[] = new Lua\SetIfHigher($this);
        $this->scripts[] = new Lua\SetIfLower($this);
        $this->scripts[] = new Lua\ZAddIfHigher($this);
        $this->scripts[] = new Lua\ZAddIfLower($this);
    }

    public function connect($host, $port = 6379, $timeout = 0.0, $reserved = null, $retryInterval = 0, $readTimeout = 0.0): void
    {
        throw new \RedisException('Do not directly call connect()');
    }
}
