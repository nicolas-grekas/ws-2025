<?php

namespace App;

use App\Repository\ConferenceRepository;
use Twig\Attribute\AsTwigFunction;

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