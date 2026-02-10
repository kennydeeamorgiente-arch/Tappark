<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class GeoapifyProxy extends BaseController
{
    /**
     * Proxy endpoint that fetches autocomplete results from Geoapify while staying within CSP rules.
     */
    public function autocomplete(): ResponseInterface
    {
        $text = trim((string) $this->request->getGet('text'));

        if (strlen($text) < 3) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Query must be at least 3 characters long.'
            ]);
        }

        $limit = (int) ($this->request->getGet('limit') ?? 5);
        $limit = max(1, min(10, $limit));

        $filter = trim((string) ($this->request->getGet('filter') ?? 'countrycode:ph'));
        $apiKey = env('GEOAPIFY_API_KEY', 'ca1241f2a1f0481493c6614db845a901');

        if (empty($apiKey)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Geoapify API key is not configured.'
            ]);
        }

        $client = \Config\Services::curlrequest([
            'timeout' => 5,
        ]);

        $query = [
            'text' => $text,
            'limit' => $limit,
            'filter' => $filter,
            'format' => 'json',
            'apiKey' => $apiKey,
        ];

        try {
            $apiResponse = $client->get('https://api.geoapify.com/v1/geocode/autocomplete', [
                'query' => $query,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'GeoapifyProxy::autocomplete error: ' . $e->getMessage());
            return $this->response->setStatusCode(502)->setJSON([
                'success' => false,
                'message' => 'Unable to reach Geoapify at the moment.'
            ]);
        }

        $body = json_decode($apiResponse->getBody(), true);
        if ($body === null) {
            return $this->response->setStatusCode(502)->setJSON([
                'success' => false,
                'message' => 'Geoapify response was not valid JSON.'
            ]);
        }

        return $this->response
            ->setStatusCode($apiResponse->getStatusCode())
            ->setJSON($body);
    }
}
