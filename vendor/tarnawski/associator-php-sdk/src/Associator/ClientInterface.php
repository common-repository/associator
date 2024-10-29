<?php

namespace Associator;

interface ClientInterface
{
    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @return mixed
     */
    public function request($url, $method = '', $data = []);
}
