<?php

namespace App\Business;

use App\Dto\ConfigDto;
use App\Entity\Configuration;
use App\Repository\ConfigurationRepository;
use App\Service\GoogleService;
use Doctrine\ORM\EntityManagerInterface;

readonly class ConfigurationBusiness
{
    public function __construct(
        private ConfigurationRepository $configurationRepository,
        private GoogleService $googleService,
        private EntityManagerInterface $em
    )
    {}

    public function getConfigurations(): array
    {
        return $this->configurationRepository->findAll();
    }

    public function createConfiguration(ConfigDto $configDto): Configuration
    {
        $configuration = $this->configurationRepository->findBy(['name' => $configDto->name]);
        if ($configuration) {
            throw new \Exception('Configuration already exists');
        }

        $configuration = new Configuration();
        $configuration->setName($configDto->name);
        $configuration->setDisplayName($configDto->displayName);
        $configuration->setValue($configDto->value);

        $this->em->persist($configuration);
        $this->em->flush();

        return $configuration;
    }

    public function updateConfiguration(Configuration $configuration, ConfigDto $configDto): Configuration
    {
        $configuration->setName($configDto->name);
        $configuration->setDisplayName($configDto->displayName);
        $configuration->setValue($configDto->value);

        $this->em->persist($configuration);
        $this->em->flush();

        return $configuration;
    }

    public function deleteConfiguration(Configuration $configuration): void
    {
        $this->em->remove($configuration);
        $this->em->flush();
    }

    public function checkLive(): ?Configuration
    {
        $liveData = $this->googleService->getYoutubeLive();

        if (isset($liveData['items']) && count($liveData['items']) > 0) {
            $videoId = $liveData['items'][0]['id']['videoId'] ?? null;
            if ($videoId) {
                $videoIdConfig = $this->configurationRepository->findOneBy(['name' => 'YOUTUBE_LIVE_VIDEO_ID']);

                if ($videoIdConfig === null) {
                    $videoIdConfig = new Configuration();
                    $videoIdConfig->setName('YOUTUBE_LIVE_VIDEO_ID');
                    $videoIdConfig->setDisplayName('YouTube Live Video ID');
                }

                $videoIdConfig->setValue($videoId);
                $this->em->persist($videoIdConfig);
                $this->em->flush();

                return $videoIdConfig;
            }
        }

        $videoIdConfig = $this->configurationRepository->findOneBy(['name' => 'YOUTUBE_LIVE_VIDEO_ID']);
        if ($videoIdConfig) {
            $this->em->remove($videoIdConfig);
            $this->em->flush();
        }

        return null;
    }
}