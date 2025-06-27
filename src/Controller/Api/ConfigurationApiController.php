<?php

namespace App\Controller\Api;

use App\Business\ConfigurationBusiness;
use App\Dto\ConfigDto;
use App\Entity\Configuration;
use App\Helper\ConfigHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/configurations', name: 'api_config')]
class ConfigurationApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getConfigurations(
        ConfigurationBusiness $configurationBusiness
    ): Response
    {
        $configurations = $configurationBusiness->getConfigurations();

        return $this->json($configurations, 200, [], ['groups' => ['config']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createConfiguration(
        ConfigurationBusiness $configurationBusiness,
        #[MapRequestPayload] ConfigDto $configDto
    ): Response
    {
        $configuration = $configurationBusiness->createConfiguration($configDto);

        return $this->json($configuration, 201, [], ['groups' => ['config']]);
    }

    #[Route('/{configuration}', name: 'update', methods: ['PUT'])]
    public function updateConfiguration(
        ConfigurationBusiness $configurationBusiness,
        Configuration $configuration,
        #[MapRequestPayload] ConfigDto $configDto
    ): Response
    {
        $configuration = $configurationBusiness->updateConfiguration($configuration, $configDto);

        return $this->json($configuration, 200, [], ['groups' => ['config']]);
    }

    #[Route('/{configuration}', name: 'delete', methods: ['DELETE'])]
    public function deleteConfiguration(
        ConfigurationBusiness $configurationBusiness,
        Configuration $configuration
    ): Response
    {
        $configurationBusiness->deleteConfiguration($configuration);

        return new Response(null, 204);
    }

    #[Route('/checkLive', name: 'check_live', methods: ['GET'])]
    public function checkLive(
        ConfigurationBusiness $configurationBusiness
    ): Response
    {
        $liveConfiguration = $configurationBusiness->checkLive();

        return $this->json($liveConfiguration, 200, [], ['groups' => ['config']]);
    }

    #[Route('/liveId', name: 'live_id', methods: ['GET'])]
    public function getLiveVideoId(
        ConfigHelper $configHelper
    ): Response
    {
        $liveVideoId = $configHelper->getValue('YOUTUBE_LIVE_VIDEO_ID');

        return $this->json(['liveVideoId' => $liveVideoId]);
    }
}