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

namespace Kilip\SanctumORM\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Sanctum\Contracts\HasAbilities;

interface SanctumUserInterface extends Authenticatable
{
    /**
     * Determine if the current API token has a given scope.
     *
     * @param string $ability
     *
     * @return bool
     */
    public function tokenCan(string $ability);

    /**
     * Get the access token currently associated with the user.
     *
     * @return TokenModelInterface
     */
    public function currentAccessToken();

    /**
     * Set the current access token for the user.
     *
     * @param TokenModelInterface|HasAbilities $accessToken
     *
     * @return static
     */
    public function withAccessToken($accessToken);
}
