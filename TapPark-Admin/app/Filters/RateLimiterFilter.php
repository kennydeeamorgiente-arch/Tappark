<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * RateLimiterFilter
 * 
 * Specifically throttles POST, PUT, PATCH, and DELETE requests
 * to prevent rapid-fire data modifications while allowing normal browsing.
 */
class RateLimiterFilter implements FilterInterface
{
    /**
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        // Use a combination of IP and User ID (if logged in) for the bucket name
        $session = Services::session();
        $userId = $session->get('user_id') ?? 'guest';
        $ipAddress = str_replace([':', '.'], '_', $request->getIPAddress());
        $bucketName = "mod_limit_{$userId}_{$ipAddress}";

        // Limit: 60 requests per minute
        // This is generous for an admin (e.g., saving layouts) but blocks automated abuse.
        if ($throttler->check($bucketName, 60, MINUTE) === false) {
            $response = Services::response();
            
            if ($request->isAJAX()) {
                return $response->setJSON([
                    'success' => false,
                    'message' => 'Too many modification requests. Please wait a moment.'
                ])->setStatusCode(429);
            }

            return $response->setStatusCode(429)->setBody('Too Many Requests. Please wait a moment before trying again.');
        }
    }

    /**
     * @param array|null $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after
    }
}
