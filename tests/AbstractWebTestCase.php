<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Base class for the project's functional tests.
 *
 * - ResetDatabase: recreates the test database from migrations before the first test of each class.
 * - Factories:    enables Zenstruck Foundry factories (UserFactory, etc.).
 */
abstract class AbstractWebTestCase extends WebTestCase
{
    use Factories;
    use ResetDatabase;
}
