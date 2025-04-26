<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Serializer\SerializerInterface;

readonly class AuthenticationSuccessListener
{
    public function __construct(
        private SerializerInterface $serializer
    ){}

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        $data = $event->getData();

        $data['user'] = $this->serializer->normalize($user, null, ['groups' => ["user"]]);

        $event->setData($data);
    }
}
