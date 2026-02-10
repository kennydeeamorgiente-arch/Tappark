<?php

namespace App\Controllers;

class PageController extends BaseController
{
    /**
     * Dynamic page loader - handles all page routes via AJAX
     * 
     * @param string|null $segment1 First route segment (e.g., 'dashboard', 'parking')
     * @param string|null $segment2 Second route segment for nested routes (e.g., 'areas', 'overview')
     * @return string|mixed Returns view content for AJAX requests
     */
    public function load($segment1 = null, $segment2 = null)
    {
        // Build route from segments
        // Simple route: "dashboard" → $segment1 = "dashboard", $segment2 = null
        // Nested route: "parking/areas" → $segment1 = "parking", $segment2 = "areas"
        if ($segment2 !== null) {
            $route = $segment1 . '/' . $segment2;
        } else {
            $route = $segment1 ?? 'dashboard';
        }
        
        // Default route if none provided
        if (empty($route)) {
            $route = 'dashboard';
        }
        
        // Sanitize route - remove any dangerous characters
        $route = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $route);
        
        // Build view path based on route
        // IMPORTANT: CodeIgniter 4's view() function automatically handles forward slashes!
        // When you use view("pages/parking/areas/index"), CI4 converts it to:
        // app/Views/pages/parking/areas/index.php
        // 
        // Examples of how routes map to view paths:
        // Route: "dashboard" 
        //   → $viewPath = "pages/dashboard/index"
        //   → File: app/Views/pages/dashboard/index.php ✅
        //
        // Route: "parking/areas" (nested route with forward slash)
        //   → $viewPath = "pages/parking/areas/index"
        //   → File: app/Views/pages/parking/areas/index.php ✅
        //
        // Route: "parking/overview" (another nested route)
        //   → $viewPath = "pages/parking/overview/index"
        //   → File: app/Views/pages/parking/overview/index.php ✅
        //
        // Route: "users"
        //   → $viewPath = "pages/users/index"
        //   → File: app/Views/pages/users/index.php ✅
        $viewPath = "pages/{$route}/index";
        
        // If this is an AJAX request (from jQuery), return only the view content
        if ($this->request->isAJAX()) {
            try {
                // Set header to indicate this is HTML content
                $this->response->setHeader('Content-Type', 'text/html');
                return view($viewPath);
            } catch (\CodeIgniter\View\Exceptions\ViewException $e) {
                // View not found - return 404 view
                return view('pages/errors/404');
            }
        }
        
        // If not AJAX (direct URL access), return full page with layout
        // This allows users to directly access pages via URL
        try {
            $data = [
                'content' => view($viewPath)
            ];
            return view('main_layout', $data);
        } catch (\CodeIgniter\View\Exceptions\ViewException $e) {
            // View not found - throw 404 exception
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }
}

