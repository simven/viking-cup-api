<?php

namespace App\Helper;

use App\Repository\ConfigurationRepository;

readonly class ConfigHelper
{
    public function __construct(
        private ConfigurationRepository $configurationRepository
    )
    {}

    public function getValue(string $name): ?string
    {
        return $this->configurationRepository->findOneBy(['name' => $name])?->getValue();
    }
}