<?php

namespace CascadeEnergy\ServiceDiscovery\Consul;

use SebastianBergmann\RecursionContext\Exception;

function json_decode($s) {
    if ($s == '') {
        $item = new \stdClass();
        $item->ServiceAddress = 'foo-address';
        $item->ServicePort = 123;
        return [$item];
    } else {
        return [];
    }
}

class ConsulHttpTest extends \PHPUnit_Framework_TestCase
{
    /** @var ConsulHttp */
    private $consulHttp;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $mockHttpClient;

    /** @var string */
    private $consulAddress;

    public function setUp()
    {
        $this->consulAddress = 'foo.bar.baz:1234';
        $this->mockHttpClient = $this
            ->getMockBuilder('GuzzleHttp\Client')
            ->setMethods(['get'])
            ->getMock();

        $this->consulHttp = new ConsulHttp($this->mockHttpClient, $this->consulAddress);
    }

    public function testCreation()
    {
        $this->assertAttributeSame($this->consulAddress, 'consulAddress', $this->consulHttp);
        $this->assertAttributeSame($this->mockHttpClient, 'httpClient', $this->consulHttp);

        $this->consulHttp = new ConsulHttp(null, 'foo');
        $this->assertAttributeInstanceOf('GuzzleHttp\Client', 'httpClient', $this->consulHttp);
    }

    public function testGetServiceAddressWithoutVersion()
    {
        $serviceName = 'foo-service';

        $mockResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $mockResponse
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('');

        $this->mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with("$this->consulAddress/v1/catalog/service/$serviceName?passing")
            ->willReturn($mockResponse);

        $expected = "http://foo-address:123";

        $this->assertEquals($expected, $this->consulHttp->getServiceAddress($serviceName));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Service: foo-service not found.
     */
    public function testGetServiceAddressFailure()
    {
        $serviceName = 'foo-service';

        $mockResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $mockResponse
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('fail');

        $this->mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with("$this->consulAddress/v1/catalog/service/$serviceName?passing")
            ->willReturn($mockResponse);

        $this->consulHttp->getServiceAddress($serviceName);
    }

    public function testGetServiceAddressWithVersion()
    {
        $serviceName = 'foo-service';
        $version = '1-0-0';

        $mockResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $mockResponse
            ->expects($this->once())
            ->method('getBody')
            ->willReturn('');

        $this->mockHttpClient
            ->expects($this->once())
            ->method('get')
            ->with("$this->consulAddress/v1/catalog/service/$serviceName?passing&tag=1-0-0")
            ->willReturn($mockResponse);

        $expected = "http://foo-address:123";

        $this->assertEquals($expected, $this->consulHttp->getServiceAddress($serviceName, $version));
    }
}

