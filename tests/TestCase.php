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

namespace Tests\Kilip\SanctumORM;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Illuminate\Support\Facades\Hash;
use Kilip\LaravelDoctrine\ORM\KilipDoctrineServiceProvider;
use Kilip\LaravelDoctrine\ORM\Testing\ORMTestTrait;
use Kilip\SanctumORM\SanctumORMServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;
use LaravelDoctrine\Extensions\GedmoExtensionsServiceProvider;
use LaravelDoctrine\ORM\DoctrineServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Tests\Kilip\SanctumORM\Fixtures\Model\TestTokens;
use Tests\Kilip\SanctumORM\Fixtures\Model\TestUser;

class TestCase extends OrchestraTestCase
{
    use ORMTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        include __DIR__.'/Fixtures/routes.php';

        $this->recreateDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            DoctrineServiceProvider::class,
            GedmoExtensionsServiceProvider::class,
            KilipDoctrineServiceProvider::class,
            SanctumServiceProvider::class,
            SanctumORMServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $app['config'];

        $config->set('doctrine.managers.default.paths', [
            __DIR__.'/Fixtures/Model',
        ]);

        $config->set('auth.providers.users.driver', 'doctrine');
        $config->set('auth.providers.users.model', TestUser::class);
        $config->set('sanctum.orm.models.token', TestTokens::class);
        $config->set('sanctum.orm.models.user', TestUser::class);
        $config->set('sanctum.expiration', 3600);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     *
     * @return object|TestUser|null
     */
    protected function createUser($username = 'test', $password = 'test', $email = 'test@example.com')
    {
        $user = $this->getRepository(TestUser::class)
            ->findOneBy(['username' => $username]);
        if (!$user) {
            $manager = $this->getManager(TestUser::class);
            $user    = new TestUser();
            $user->setUsername($username)
                ->setEmail($email)
                ->setPassword(Hash::make($password));

            $manager->persist($user);
            $manager->flush();
        }

        return $user;
    }

    /**
     * @param $className
     *
     * @return ObjectRepository
     */
    protected function getRepository($className)
    {
        $manager = $this->getManager($className);

        return $manager->getRepository($className);
    }

    /**
     * @param $className
     *
     * @return ObjectManager
     */
    protected function getManager($className)
    {
        return app()->get('registry')->getManagerForClass($className);
    }

    /**
     * @param string $username
     */
    protected function loggedInAs($username = 'test')
    {
    }

    protected function verifyAttributes($ob, $name, $value, $defaultValue=null, $fluent=true)
    {
        $setter = [$ob, 'set'.$name];
        $getter = [$ob, 'get'.$name];

        $this->assertEquals($defaultValue, \call_user_func($getter));
        $retSetter = \call_user_func_array($setter, [$value]);
        if ($fluent) {
            $this->assertInstanceOf(TestTokens::class, $retSetter);
        }
        $this->assertEquals($value, \call_user_func($getter));
    }
}
