<?php

declare(strict_types=1);

namespace App\Application\Actions\Character;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use App\Domain\Study\StudyLogRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class EquipCharacterAction extends Action
{
    private UserRepository $userRepository;
    private StudyLogRepository $studyLogRepository;

    public function __construct(
        LoggerInterface $logger,
        UserRepository $userRepository,
        StudyLogRepository $studyLogRepository
    ) {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
        $this->studyLogRepository = $studyLogRepository;
    }

    protected function action(): Response
    {
        $userId = $this->request->getAttribute('token')->sub;
        $characterId = (int) $this->resolveArg('characterId');

        // Check if character is unlocked for the user
        $unlockedCharacters = $this->studyLogRepository->getUnlockedCharacters($userId);
        $unlockedCharacterIds = array_column($unlockedCharacters, 'id');

        if (!in_array($characterId, $unlockedCharacterIds, true)) {
            return $this->respondWithData(['error' => 'Character not unlocked by user'], 403);
        }

        // Equip the character
        $success = $this->userRepository->equipCharacter($userId, $characterId);

        if (!$success) {
            return $this->respondWithData(['error' => 'Failed to equip character'], 500);
        }

        return $this->respondWithData(['message' => 'Character equipped successfully'], 200);
    }
}
