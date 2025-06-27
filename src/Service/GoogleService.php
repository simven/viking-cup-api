<?php

namespace App\Service;

use App\Helper\ConfigHelper;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleService
{
    private ?string $channelId;
    private ?string $apiKey;
    private ?string $baseUrlAPi;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        ConfigHelper                         $configHelper
    )
    {
        $this->channelId = $configHelper->getValue('YOUTUBE_CHANNEL_ID');
        $this->apiKey = $configHelper->getValue('YOUTUBE_API_KEY');
        $this->baseUrlAPi = $configHelper->getValue('GOOGLE_BASE_URL_API');
    }

    public function getYoutubeLive(): array
    {
        $params = [
            'part' => 'snippet',
            'channelId' => $this->channelId,
            'eventType' => 'live',
            'type' => 'video',
            'key' => $this->apiKey,
        ];
        $url = $this->baseUrlAPi . '/youtube/v3/search?' . http_build_query($params);

        $response = $this->httpClient->request('GET', $url);

        return $response->toArray();
    }
}