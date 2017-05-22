<?php

namespace Tests\Unit;

use Tests\TestCase;
use Laracasts\Integrated\Extensions\Laravel as IntegrationTest;

class ExampleTest extends IntegrationTest
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->visit('/')
            ->see('hello');
    }

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        // TODO: Implement createApplication() method.
    }
}
