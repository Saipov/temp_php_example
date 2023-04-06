<?php


namespace App\Security\Voter;

/**
 * Interface VoterInterface
 *
 * @package App\Security\Admin\Voter
 */
interface VoterInterface
{
    static public function getPermissions(): array;

    static public function getName(): string;
}
