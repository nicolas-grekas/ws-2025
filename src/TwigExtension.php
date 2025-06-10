<?php

namespace App;

use App\Repository\ConferenceRepository;
use Twig\Attribute\AsTwigFunction;
use Twig\Attribute\AsTwigTest;

class TwigExtension
{
    public function __construct(
        private ConferenceRepository $conferenceRepository
    ) {
    }

    #[AsTwigFunction('conferences')]
    public function getConferences()
    {
        return $this->conferenceRepository->findAll();
    }
}