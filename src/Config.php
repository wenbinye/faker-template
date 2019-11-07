<?php

namespace winwin\faker;

class Config
{
    const FAKER_CONFIG = '.faker-config';

    /**
     * @var array
     */
    private $config;

    private static $instance;

    private function __construct(array $config)
    {
        $this->config = $config;
    }

    private static function getInstance(): Config
    {
        if (!self::$instance) {
            if (file_exists(self::FAKER_CONFIG)) {
                $configFile = self::FAKER_CONFIG;
            } else {
                $configFile = self::home().'/.faker-config';
            }
            if (file_exists($configFile)) {
                static::setConfigFile($configFile);
            } else {
                static::$instance = new static([]);
            }
        }

        return self::$instance;
    }

    public function getItem($name, $default = null)
    {
        return $this->config[$name] ?? $default;
    }

    public static function get($name, $default = null)
    {
        return static::getInstance()->getItem($name, $default);
    }

    public static function setConfigFile(string $configFile)
    {
        if (!file_exists($configFile)) {
            throw new \RuntimeException(sprintf("Config file '$configFile' does not exist"));
        }
        $config = json_decode(file_get_contents($configFile), true);
        if (!is_array($config)) {
            throw new \RuntimeException("malformed json in '$configFile'");
        }
        self::$instance = new static($config);
    }

    private static function home()
    {
        // On Linux/Unix-like systems, use the HOME environment variable
        if ($homeDir = getenv('HOME')) {
            return $homeDir;
        }

        // Get the HOMEDRIVE and HOMEPATH values for Windows hosts
        $homeDrive = getenv('HOMEDRIVE');
        $homePath = getenv('HOMEPATH');

        return ($homeDrive && $homePath) ? $homeDrive.$homePath : null;
    }
}
