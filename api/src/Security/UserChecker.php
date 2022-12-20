<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface {
    public function checkPreAuth(UserInterface $user) {
        if (!$user instanceof User) {
            return;
        }
        
        /*if (!$user->getIsConfirmed()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException(
                "Your account has not yet been confirmed. Please follow the link in the email that we sent you."
            );
        }*/

        if (!$user->getIsActive()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException(
                "Your account has been deactivated. Please contact our support for more information."
            );
        }
        
    }

    public function checkPostAuth(UserInterface $user) {
        if (!$user instanceof User) {
            return;
        }
    }
}
