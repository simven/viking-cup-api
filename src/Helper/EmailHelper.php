<?php

namespace App\Helper;

use App\Business\EmailBusiness;
use App\Dto\EmailDto;
use App\Entity\Round;

readonly class EmailHelper
{
    public function __construct(
        private EmailBusiness $emailBusiness,
        private ConfigHelper $config
    )
    {}

    public function sendPreselectedEmail(string $email, Round $round, string $firstName): void
    {

        $emailDto = new EmailDto(
            fromName: 'Viking Cup',
            to: $email,
            subject: 'Confirmation de ta demande d\'accréditation média pour le ' . $round->getName() . ' de la Viking Cup',
            message: <<<HTML
                <p>Bonjour $firstName,</p>
                <p>Merci pour ta demande d’inscription en tant que média pour la prochaine édition de la <strong>Viking Cup</strong>.</p>
                <p>Ta demande a bien été reçue. Il s’agit pour le moment d’une <strong>demande d’accréditation</strong>, qui sera examinée avec attention par notre équipe.</p>
                <p>Tu recevras une réponse, qu’elle soit positive ou négative, <strong>au plus tard un mois avant le début de la compétition</strong>.</p>
                <p>Tu peux retrouver le règlement média en pièce jointe à ce mail.</p>
                <p>Merci pour l’intérêt que tu portes à la Viking Cup. N’hésite pas à nous contacter si tu as la moindre question.</p>
                <p>Bien cordialement,<br><strong>L’équipe Viking Cup</strong></p>
                HTML,
            attachment: 'https://viking-cup.fr/viking-cup-reglement-media.pdf'
        );

        $this->emailBusiness->sendEmail($emailDto);
    }

    public function sendSelectedEmail(string $email, Round $round, string $firstName): void
    {
        $emailDto = new EmailDto(
            fromName: 'Viking Cup',
            to: $email,
            subject: 'Confirmation de ta selection média pour le ' . $round->getName() . ' de la Viking Cup',
            message: <<<HTML
                <p>Bonjour $firstName,</p>
                <p>Félicitations ! Tu as été sélectionné·e pour couvrir la prochaine édition de la <strong>Viking Cup</strong> en tant que média.</p>
                <p>Si, pour une quelconque raison, tu ne peux finalement pas être présent·e, merci de nous en informer au plus vite afin que nous puissions réattribuer ta place.</p>
                <p>Une semaine avant le début de la compétition, tu recevras un lien vers une <strong>vidéo briefing obligatoire</strong> qui présente les consignes à respecter sur place. La validation de cette vidéo est indispensable pour accéder à l’événement en tant que média.</p>
                <p>Une fois la vidéo terminée, tu pourras télécharger directement ton <strong>pass média personnalisé</strong>, généré à ton nom.</p>
                <p>Merci encore pour ton intérêt pour la Viking Cup. On a hâte de t’accueillir sur l’événement !</p>
                <p>Bien cordialement,<br><strong>L’équipe Viking Cup</strong></p>
                HTML,
            attachment: 'https://viking-cup.fr/viking-cup-reglement-media.pdf'
        );

        $this->emailBusiness->sendEmail($emailDto);
    }

    public function sendELearningEmail(string $email, Round $round, string $firstName, string $uniqueId): void
    {
        $mediaUrl = $this->config->getValue('MEDIA_URL');

        $emailDto = new EmailDto(
            fromName: 'Viking Cup',
            to: $email,
            subject: 'Accès à la vidéo briefing pour le ' . $round->getName() . ' de la Viking Cup',
            message: <<<HTML
                <p>Bonjour $firstName,</p>
                <p>Tu peux dès à présent visionner la vidéo briefing obligatoire en cliquant sur le lien : <a href="$mediaUrl/e-learning/$uniqueId">$mediaUrl/e-learning/$uniqueId</a>.</p>
                <p>Une fois la vidéo terminée, tu pourras télécharger directement ton <strong>pass média personnalisé</strong>, généré à ton nom.</p>
                <p>Si, pour une quelconque raison, tu ne peux finalement pas être présent·e, merci de nous en informer au plus vite afin que nous puissions réattribuer ta place.</p>
                <p>Merci encore pour ton intérêt pour la Viking Cup. On a hâte de t’accueillir sur l’événement !</p>
                <p>Bien cordialement,<br><strong>L’équipe Viking Cup</strong></p>
                HTML
        );

        $this->emailBusiness->sendEmail($emailDto);
    }
}