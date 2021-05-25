<?php

/*
 * This file is part of the Sanctum ORM project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Kilip\SanctumORM\Model;

use Doctrine\ORM\Mapping as ORM;
use Kilip\SanctumORM\Contracts\SanctumUserInterface;
use Kilip\SanctumORM\Contracts\TokenModelInterface;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * Class Tokens.
 *
 * @ORM\MappedSuperclass
 */
abstract class Tokens implements TokenModelInterface
{
    use Timestamps;

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    protected $token;

    /**
     * @ORM\Column(type="array", nullable=true)
     *
     * @var array
     */
    protected $abilities = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    protected $lastUsedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Renegade\User\Domain\Entities\User", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="user_id")
     *
     * @var SanctumUserInterface|null
     */
    protected $owner;

    /**
     * Determine if the token has a given ability.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function can($ability)
    {
        return \in_array('*', $this->abilities, true) ||
            \array_key_exists($ability, array_flip($this->abilities));
    }

    /**
     * Determine if the token is missing a given ability.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function cant($ability)
    {
        return !$this->can($ability);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setLastUsedAt(\DateTime $date)
    {
        $this->lastUsedAt = $date;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastUsedAt()
    {
        return $this->lastUsedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(SanctumUserInterface $user)
    {
        $this->owner = $user;

        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setToken(string $token)
    {
        $this->token = $token;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setAbilities(array $abilities = ['*'])
    {
        $this->abilities = $abilities;

        return $this;
    }

    public function getAbilities()
    {
        return $this->abilities;
    }
}
