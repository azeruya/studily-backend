<?php

namespace App\Application\Actions\Character;

use App\Application\Actions\Action;
use App\Domain\Character\CharacterRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListCharactersAction extends Action
{
    private CharacterRepository $characterRepository;

    public function __construct(LoggerInterface $logger, CharacterRepository $characterRepository)
    {
        parent::__construct($logger);
        $this->characterRepository = $characterRepository;
    }

    protected function action(): Response
    {
        $characters = $this->characterRepository->getAllCharacters();

        return $this->respondWithData($characters);
    }
}
