<?php

namespace App\Domain\Character;

interface CharacterRepository
{
    public function getAllCharacters(): array;
    // ... other methods like unlockCharacter, etc.
}
