<?php

namespace App\Helper;

use App\Entity\Media;
use TCPDF;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PdfHelper extends TCPDF {
    public function __construct(
        private readonly Environment $twig,
        private readonly Media       $media
    )
    {
        parent::__construct();
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    public function Header(): void
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR', // locale
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            null,
            null,
            'EEE d MMM'
        );

        $headerHtml = $this->twig->render('pdf/pass-media/header.html.twig', [
            'media' => $this->media,
            'logoPath' => 'img/logo.png',
            'title' => 'Viking Cup ' . $this->media->getRound()->getEvent()->getYear(),
            'subtitle' => $this->media->getRound()->getName(),
            'fromDate' => $formatter->format($this->media->getRound()->getFromDate()),
            'toDate' => $formatter->format($this->media->getRound()->getToDate()),
        ]);

        // Header Content
        $this->writeHTML($headerHtml);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    public function Footer(): void
    {
        $footerHtml = $this->twig->render('pdf/pass-media/footer.html.twig', [
            'siteLink' => 'https://viking-cup.fr',
        ]);

        // Footer Content
        $this->SetY(-50);
        $this->writeHTML($footerHtml);
    }
}