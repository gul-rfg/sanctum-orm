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

namespace Kilip\SanctumORM\Security;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Kilip\SanctumORM\Contracts\SanctumUserInterface;
use Kilip\SanctumORM\Manager\TokenManagerInterface;
use Illuminate\Support\Str;

class Guard
{
    /**
     * @var AuthFactory
     */
    private $authFactory;

    /**
     * @var int
     */
    private $expiration;

    /**
     * @var string
     */
    private $provider;

    /**
     * @var TokenManagerInterface
     */
    private $tokenManager;

    public function __construct(
        AuthFactory $authFactory,
        TokenManagerInterface $tokenManager,
        int $expiration = null,
        string $provider = null
    ) {
        $this->authFactory  = $authFactory;
        $this->tokenManager = $tokenManager;
        $this->expiration   = $expiration;
        $this->provider     = $provider;
    }

    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $authFactory = $this->authFactory;
        $manager     = $this->tokenManager;

        if ($user = $authFactory->guard(config('sanctum_orm.guard', 'web'))->user()) {
            return $this->supportsTokens($user)
                ? $manager->createTransientToken($user)
                : $user;
        }

        if ($token = $this->bearerToken()) {
            $accessToken = $manager->findToken($token);

            if (
                !$accessToken
                || (
                    $this->expiration &&
                    Carbon::instance($accessToken->getCreatedAt())->lte(now()->subMinutes($this->expiration)))
                    || !$this->hasValidProvider($accessToken->getOwner())
                ) {
                return null;
            }

            if ($this->supportsTokens($accessToken->getOwner())) {
                $accessToken->setLastUsedAt(now());

                return $manager->updateAccessToken($accessToken);
            }
        }

        return null;
    }

    /**
     * Determine if the tokenable model supports API tokens.
     *
     * @param mixed $tokenable
     *
     * @return bool
     */
    protected function supportsTokens($tokenable = null)
    {
        return $tokenable && $tokenable instanceof SanctumUserInterface;
    }

    /**
     * Determine if the tokenable model matches the provider's model type.
     *
     * @param SanctumUserInterface $owner
     *
     * @return bool
     */
    protected function hasValidProvider($owner)
    {
        if (null === $this->provider) {
            return true;
        }

        return $owner instanceof SanctumUserInterface;
    }
    
     /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    public function bearerToken()
    {
        $header = request()->header('authorization', '');

        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
    }
}
