<?php

namespace App\Service;

use App\Helper\ConfigHelper;
use App\Repository\ConfigurationRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BilletwebService
{
    private ?string $billetwebBaseUrlApi;
    private ?string $billetwebUserId;
    private ?string $billetwebKey;

    public function __construct(
        private HttpClientInterface $httpClient,
        ConfigHelper $configHelper
    )
    {
        $this->billetwebBaseUrlApi = $configHelper->getValue('BILLETWEB_BASE_URL_API');
        $this->billetwebUserId = $configHelper->getValue('BILLETWEB_USER_ID');
        $this->billetwebKey = $configHelper->getValue('BILLETWEB_KEY');
    }

    public function getEventAttendees(int $eventId): array
    {
        $url = $this->billetwebBaseUrlApi . '/event/' . $eventId . '/attendees';

        $response = $this->getRequest($url, 'GET');

        return $response->toArray();
    }

    public function getRequest(string $url, string $method): ResponseInterface
    {
        return $this->httpClient->request($method, $url, [
            'auth_basic' => [$this->billetwebUserId, $this->billetwebKey],
        ]);
    }
}