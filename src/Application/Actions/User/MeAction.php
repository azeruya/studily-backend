<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;

class MeAction extends UserAction
{
    protected function action(): Response
    {
        $token = $this->request->getAttribute('token');

        if (!$token || !isset($token->sub)) {
            return $this->respondWithData(['error' => 'Invalid token'], 401);
        }

        $userId = $token->sub;

        $user = $this->userRepository->findUserById($userId);

        if (!$user) {
            return $this->respondWithData(['error' => 'User not found'], 404);
        }

        return $this->respondWithData($user);
    }
}
