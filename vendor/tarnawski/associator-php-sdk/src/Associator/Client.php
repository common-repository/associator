<?php

namespace Associator;

use Associator\Exception\AssociatorException;

class Client implements ClientInterface
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';

    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @return mixed
     * @throws AssociatorException
     */
    public function request($url, $method = self::HTTP_GET, $data = [])
    {
        $curl = curl_init();

        $settings[CURLOPT_URL] = $url;
        $settings[CURLOPT_RETURNTRANSFER] = 1;
        $settings[CURLOPT_HTTPHEADER] = ['Content-Type:application/json'];
        $settings[CURLOPT_CONNECTTIMEOUT] = 0;
        $settings[CURLOPT_TIMEOUT] = 600;

        if ($method === self::HTTP_POST) {
            $settings[CURLOPT_CUSTOMREQUEST] = "POST";
            $settings[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $settings);
        $response = curl_exec($curl);

        if (curl_error($curl)) {
            $error = curl_error($curl);
        }

        curl_close($curl);

        if (isset($error)) {
            throw new AssociatorException($error);
        }

        return $response;
    }
}
