<?php

namespace Ensi\LaravelElasticQuery\Adapter;

class ClientConfig
{
    public function __construct(private array $config)
    {
    }

    /**
     * Get an array of the elastic hosts from the configuration.
     * @return array
     */
    public function getHosts(): array
    {
        return $this->config['hosts'];
    }

    /**
     * Get the number of retries for the elastic client from the configuration.
     * @return int
     */
    public function getRetries(): int
    {
        return $this->config['retries'] ?? 1;
    }

    /**
     * Get the username for the elastic client from the configuration.
     * @return string
     */
    public function getUsername(): string
    {
        return $this->config['username'] ?? '';
    }

    /**
     * Get the password for the elastic client from the configuration.
     * @return string
     */
    public function getPassword(): string
    {
        return $this->config['password'] ?? '';
    }

    /**
     * Get the SSL verification setting for the elastic client from the configuration.
     * @return bool
     */
    public function getSSLVerification(): bool
    {
        return $this->config['ssl_verification'] ?? false;
    }
}