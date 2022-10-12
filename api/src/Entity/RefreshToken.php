<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken as BaseRefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
#[ORM\Entity(repositoryClass: RefreshTokenRepository::class)]
#[ORM\Table("refresh_tokens")]
class RefreshToken extends BaseRefreshToken
{
}
