<?php

declare(strict_types=1);

namespace App\Application\Actions\Character;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetEquippedCharacterAction extends Action
{
    private UserRepository $userRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
    }

    protected function action(): Response
    {
        $userId = $this->request->getAttribute('token')->sub;

        $character = $this->userRepository->getEquippedCharacter($userId);

        if (!$character) {
            return $this->respondWithData(['message' => 'No character equipped'], 200);
        }

        return $this->respondWithData($character, 200);
    }
}
