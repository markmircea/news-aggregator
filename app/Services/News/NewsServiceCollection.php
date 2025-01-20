<?php

namespace App\Services\News;

class NewsServiceCollection
{
    private array $services = [];

    public function __construct(array $services)
    {
        foreach ($services as $service) {
            if (!($service instanceof NewsServiceInterface)) {
                throw new \InvalidArgumentException('All services must implement NewsServiceInterface');
            }
        }
        $this->services = $services;
    }

    public function getServices(): array
    {
        return $this->services;
    }
}
