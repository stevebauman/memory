<?php namespace Orchestra\Memory\TestCase;

use Mockery as m;
use Orchestra\Memory\Provider;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Get Mock instance 1.
     *
     * @return MemoryDriverStub
     */
    protected function getStubInstanceOne()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $data = [
            'foo' => [
                'bar' => 'hello world',
            ],
            'username' => 'laravel',
        ];

        $handler->shouldReceive('initiate')->once()->andReturn($data);

        return new Provider($handler);
    }

    /**
     * Get Mock instance 2.
     *
     * @return MemoryDriverStub
     */
    protected function getStubInstanceTwo()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $data = [
            'foo' => [
                'bar' => 'hello world',
            ],
            'username' => 'laravel',
        ];

        $handler->shouldReceive('initiate')->once()->andReturn($data);

        $stub = new Provider($handler);
        $stub->put('foobar', function () {
            return 'hello world foobar';
        });
        $stub->get('hello.world', function () use ($stub) {
            return $stub->put('hello.world', 'HELLO WORLD');
        });

        return $stub;
    }

    /**
     * Test constructing Orchestra\Memory\Provider.
     *
     * @test
     */
    public function testConstructMethod()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $handler->shouldReceive('initiate')->once()->andReturn(['foo' => 'foobar']);

        $stub = new Provider($handler);

        $this->assertEquals('foobar', $stub->get('foo'));
        $this->assertEquals($handler, $stub->getHandler());
    }

    /**
     * Test Orchestra\Memory\Drivers\Driver::finish().
     *
     * @test
     */
    public function testFinishMethod()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $handler->shouldReceive('initiate')->once()->andReturn(['foo' => 'foobar'])
            ->shouldReceive('finish')->once()->with(['foo'            => 'foobar'])->andReturn(true);

        $stub = new Provider($handler);

        $this->assertTrue($stub->finish());
    }

    /**
     * Test Orchestra\Memory\Drivers\Driver::get() method.
     *
     * @test
     */
    public function testGetMethod()
    {
        $stub1 = $this->getStubInstanceOne();
        $stub2 = $this->getStubInstanceTwo();

        $this->assertEquals(['bar' => 'hello world'], $stub1->get('foo'));
        $this->assertEquals('hello world', $stub1->get('foo.bar'));
        $this->assertEquals('laravel', $stub1->get('username'));

        $this->assertEquals(['bar' => 'hello world'], $stub2->get('foo'));
        $this->assertEquals('hello world', $stub2->get('foo.bar'));
        $this->assertEquals('laravel', $stub2->get('username'));

        $this->assertEquals('hello world foobar', $stub2->get('foobar'));
        $this->assertEquals('HELLO WORLD', $stub2->get('hello.world'));
    }

    /**
     * Test Orchestra\Memory\Drivers\Driver::put() method.
     *
     * @test
     */
    public function testPutMethod()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $handler->shouldReceive('initiate')->once()->andReturn([]);

        $stub = new Provider($handler);

        $refl  = new \ReflectionObject($stub);
        $items = $refl->getProperty('items');
        $items->setAccessible(true);

        $this->assertEquals([], $items->getValue($stub));

        $stub->put('foo', 'foobar');

        $this->assertEquals(['foo' => 'foobar'], $items->getValue($stub));
    }

    /**
     * Test Orchestra\Memory\Drivers\Driver::forget() method.
     *
     * @test
     */
    public function testForgetMethod()
    {
        $handler = m::mock('\Orchestra\Contracts\Memory\Handler');

        $data = [
            'hello' => [
                'world' => 'hello world',
            ],
            'username' => 'laravel',
        ];

        $handler->shouldReceive('initiate')->once()->andReturn($data);

        $stub = new Provider($handler);

        $stub->forget('hello.world');

        $this->assertEquals([], $stub->get('hello'));
    }
}
