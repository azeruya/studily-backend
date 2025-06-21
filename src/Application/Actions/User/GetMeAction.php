<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use App\Domain\Study\StudyLogRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetMeAction extends Action
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

        $user = $this->userRepository->findUserById($userId);
        $currentStreak = $this->studyLogRepository->getCurrentStreak($userId);
        $equippedCharacter = $this->userRepository->getEquippedCharacter($userId);
        $unlockedCharacters = $this->studyLogRepository->getUnlockedCharacters($userId);

        return $this->respondWithData([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'current_streak' => $currentStreak,
            'equipped_character' => $equippedCharacter,
            'unlocked_characters' => $unlockedCharacters,
        ]);
    }
}
