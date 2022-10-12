<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class PatchUserVoter extends Voter
{

    private $security;
    private $request;

    public function __construct(Security $security, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->request = $requestStack->getCurrentRequest();
    }

    protected function supports($attribute, $subject):bool
    {
        if ( !in_array($attribute, ["PATCH_USER"]) ) {
            return false;
        }
        if (!$subject instanceof User) {
            return false;
        }


        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token):bool
    {
        $req = json_decode($this->request->getContent(), true);

        /** @var User */
        $user = $this->security->getUser();
        // Admins are allowed to patch via security attribute in User annotation

        switch ($attribute) {
            case 'PATCH_USER':
                // Not logged in users may only reset their password, nothing else:
                if (!$user
                    && array_key_exists("resetPasswordToken", $req) && array_key_exists("password", $req) &&  array_key_exists("repeatPassword", $req) && count($req) == 3 )   {
                    return true;
                }
                if ($subject == $user){
                    return true;
                }
                break;
        }

        return false;
    }
}
