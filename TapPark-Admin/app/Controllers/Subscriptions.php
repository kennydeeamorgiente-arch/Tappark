<?php

namespace App\Controllers;

use App\Models\SubscriptionModel;

class Subscriptions extends BaseController
{
    protected $subscriptionModel;

    public function __construct()
    {
        $this->subscriptionModel = new SubscriptionModel();
        helper('activity'); // Load activity helper for logging
    }

    /**
     * Main index - returns the view
     */
    public function index()
    {
        // Check if this is an AJAX request
        if ($this->request->isAJAX()) {
            // Return only the content (for AJAX loading)
            return view('pages/subscriptions/index');
        }
        
        // Return full page (for direct URL access, non-AJAX)
        $data = [
            'content' => view('pages/subscriptions/index')
        ];
        return view('main_layout', $data);
    }

    /**
     * Get plans list (AJAX endpoint)
     */
    public function list()
    {
        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'price_range' => $this->request->getGet('price_range'),
            'hours_range' => $this->request->getGet('hours_range'),
            'status' => $this->request->getGet('status')
        ];

        // Get pagination params
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 25;
        $offset = ($page - 1) * $perPage;

        // Get plans
        $plans = $this->subscriptionModel->getPlansWithFilters($filters, $perPage, $offset);
        $totalPlans = $this->subscriptionModel->getPlansCount($filters);
        $stats = $this->subscriptionModel->getSubscriptionStats();

        // Calculate pagination info
        $totalPages = ceil($totalPlans / $perPage);
        $showingFrom = $totalPlans > 0 ? (($page - 1) * $perPage) + 1 : 0;
        $showingTo = min($page * $perPage, $totalPlans);

        return $this->response->setJSON([
            'success' => true,
            'data' => $plans,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total' => $totalPlans,
                'total_pages' => $totalPages,
                'showing_from' => $showingFrom,
                'showing_to' => $showingTo
            ],
            'stats' => $stats
        ]);
    }

    /**
     * Get single plan by ID (AJAX endpoint)
     */
    public function get($planId)
    {
        $plan = $this->subscriptionModel->getPlanById($planId);

        if (!$plan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Plan not found'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * Create new plan (AJAX endpoint)
     */
    public function create()
    {
        $data = [
            'plan_name' => $this->request->getPost('plan_name'),
            'cost' => $this->request->getPost('cost'),
            'number_of_hours' => $this->request->getPost('number_of_hours'),
            'description' => $this->request->getPost('description') ?? ''
        ];

        // Validate
        if (!$this->validate([
            'plan_name' => 'required|min_length[3]|max_length[120]|is_unique[plans.plan_name]',
            'cost' => 'required|numeric|greater_than_equal_to[0]',
            'number_of_hours' => 'required|integer|greater_than[0]'
        ])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $planId = $this->subscriptionModel->createPlan($data);

        if ($planId) {
            // Log activity
            log_create('Subscription Plan', $planId, $data['plan_name']);

            // Get the full plan data for dynamic table update
            $newPlan = $this->subscriptionModel->find($planId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Plan created successfully',
                'data' => $newPlan  // Return full plan data
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to create plan'
        ])->setStatusCode(500);
    }

    /**
     * Update plan (AJAX endpoint)
     */
    public function update($planId)
    {
        $plan = $this->subscriptionModel->find($planId);

        if (!$plan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Plan not found'
            ])->setStatusCode(404);
        }

        $data = [
            'plan_name' => $this->request->getPost('plan_name'),
            'cost' => $this->request->getPost('cost'),
            'number_of_hours' => $this->request->getPost('number_of_hours'),
            'description' => $this->request->getPost('description') ?? ''
        ];

        // Validate
        if (!$this->validate([
            'plan_name' => "required|min_length[3]|max_length[120]|is_unique[plans.plan_name,plan_id,{$planId}]",
            'cost' => 'required|numeric|greater_than_equal_to[0]',
            'number_of_hours' => 'required|integer|greater_than[0]'
        ])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        $result = $this->subscriptionModel->updatePlan($planId, $data);
        
        if ($result) {
            // Log activity
            log_update('Subscription Plan', $planId, $data['plan_name']);

            // Get the updated plan data for dynamic table update
            $updatedPlan = $this->subscriptionModel->find($planId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Plan updated successfully',
                'data' => $updatedPlan  // Return full plan data
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to update plan',
            'errors' => $this->subscriptionModel->errors()
        ])->setStatusCode(500);
    }

    /**
     * Delete plan (AJAX endpoint)
     */
    public function delete($planId)
    {
        $plan = $this->subscriptionModel->find($planId);

        if (!$plan) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Plan not found'
            ])->setStatusCode(404);
        }

        $result = $this->subscriptionModel->deletePlan($planId);

        if ($result === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete plan with active subscriptions'
            ])->setStatusCode(400);
        }

        if ($result) {
            // Log activity
            log_delete('Subscription Plan', $planId, $plan['plan_name']);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Plan deleted successfully'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to delete plan'
        ])->setStatusCode(500);
    }

    /**
     * Export plans to CSV
     */
    public function export()
    {
        // Get filters from request
        $filters = [
            'search' => $this->request->getGet('search'),
            'price_range' => $this->request->getGet('price_range'),
            'hours_range' => $this->request->getGet('hours_range')
        ];

        $plans = $this->subscriptionModel->getAllPlans($filters);

        // Generate CSV
        $filename = 'subscription_plans_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 (helps Excel recognize UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, [
            'Plan ID',
            'Plan Name',
            'Cost (₱)',
            'Hours Included',
            'Total Subscribers',
            'Active Subscribers',
            'Description'
        ]);
        
        // CSV Data
        foreach ($plans as $plan) {
            fputcsv($output, [
                $plan['plan_id'],
                $plan['plan_name'],
                number_format($plan['cost'], 2),
                $plan['number_of_hours'],
                $plan['total_subscribers'] ?? 0,
                $plan['active_subscribers'] ?? 0,
                $plan['description'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
}

