<?php

namespace App\Controller;

use App\Dto\TokenDto;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login_admin')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/api/token_check', methods: ['POST'])]
    public function checkToken(
        #[MapRequestPayload] TokenDto $token,
        JWTEncoderInterface $JWTEncoder,
        JWTTokenManagerInterface $JWTTokenManager,
        UserRepository $userRepository,
        SerializerInterface $serializer,
    ): Response
    {
        try {
            $decodedToken = $JWTEncoder->decode($token->token);

            if ($decodedToken) {
                $user = $userRepository->findOneBy(['username' => $decodedToken['username']]);
                $token = $JWTTokenManager->create($user);

                $tokenData = [
                    'token' => $token,
                    'user' => $serializer->normalize($user, null, ['groups' => ["user"]])
                ];
                return $this->json($tokenData);
            }

            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'Invalid token');
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }
}
