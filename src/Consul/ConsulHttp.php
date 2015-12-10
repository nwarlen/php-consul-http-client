<?php

namespace CascadeEnergy\ServiceDiscovery\Consul;

use CascadeEnergy\ServiceDiscovery\ServiceDiscoveryClientInterface;
use GuzzleHttp\Client;

class ConsulHttp implements ServiceDiscoveryClientInterface
{
    /** @var Client */
    private $httpClient;

    private $consulAddress;

    public function __construct($httpClient, $consulAddress)
    {
        if ($httpClient != null) {
            $this->httpClient = $httpClient;
        } else {
            $this->httpClient = new Client();
        }
        $this->consulAddress = $consulAddress;
    }

    /**
     * @param $serviceName
     * @param null $version
     *
     * @return string
     * @throws \Exception
     */
    public function getServiceAddress($serviceName, $version = null)
    {
        $url = "$this->consulAddress/v1/catalog/service/$serviceName?passing";

        if ($version != null) {
            $url .= "&tag=$version";
        }

        $consulRequest = $this->httpClient->get($url);
        $list = json_decode($consulRequest->getBody());

        if (!empty($list)) {
            $item = $list[array_rand($list)];
            return "http://$item->ServiceAddress:$item->ServicePort";
        }

        return null;
    }
}
