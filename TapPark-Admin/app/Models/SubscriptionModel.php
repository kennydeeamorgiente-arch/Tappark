<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table = 'plans';
    protected $primaryKey = 'plan_id';
    protected $allowedFields = [
        'plan_name',
        'cost',
        'number_of_hours',
        'description'
    ];
    protected $useTimestamps = false;
    protected $validationRules = [
        'plan_name' => 'required|min_length[3]|max_length[120]|is_unique[plans.plan_name,plan_id,{plan_id}]',
        'cost' => 'required|numeric|greater_than_equal_to[0]|less_than[1000000]',
        'number_of_hours' => 'required|integer|greater_than[0]|less_than[10000]'
    ];
    protected $validationMessages = [
        'plan_name' => [
            'is_unique' => 'This plan name already exists'
        ]
    ];

    /**
     * Get plans with filters and pagination
     */
    public function getPlansWithFilters($filters = [], $limit = 10, $offset = 0)
    {
        $builder = $this->db->table('plans p')
            ->select('p.*, 
                     COALESCE((SELECT COUNT(*) FROM subscriptions WHERE plan_id = p.plan_id), 0) as total_subscribers,
                     COALESCE((SELECT COUNT(*) FROM subscriptions WHERE plan_id = p.plan_id AND status = "active"), 0) as active_subscribers')
            ->orderBy('p.cost', 'ASC');

        // Apply filters
        $this->applyFilters($builder, $filters);

        $results = $builder->limit($limit, $offset)->get()->getResultArray();
        
        return $results;
    }

    /**
     * Get total plans count with filters
     */
    public function getPlansCount($filters = [])
    {
        $builder = $this->db->table('plans p');

        // Apply filters
        $this->applyFilters($builder, $filters);

        return $builder->countAllResults();
    }

    /**
     * Apply filters to query builder
     */
    private function applyFilters($builder, $filters)
    {
        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('p.plan_id', $search)
                ->orLike('p.plan_name', $search)
                ->orLike('p.description', $search)
                ->groupEnd();
        }

        // Price range filter
        if (!empty($filters['price_range'])) {
            $range = $filters['price_range'];
            if ($range === '0-100') {
                $builder->where('p.cost >=', 0)->where('p.cost <=', 100);
            } elseif ($range === '100-500') {
                $builder->where('p.cost >', 100)->where('p.cost <=', 500);
            } elseif ($range === '500-1000') {
                $builder->where('p.cost >', 500)->where('p.cost <=', 1000);
            } elseif ($range === '1000-5000') {
                $builder->where('p.cost >', 1000)->where('p.cost <=', 5000);
            } elseif ($range === '5000+') {
                $builder->where('p.cost >', 5000);
            }
            // Legacy support for old ranges
            elseif ($range === '0-200') {
                $builder->where('p.cost >=', 0)->where('p.cost <=', 200);
            } elseif ($range === '200-500') {
                $builder->where('p.cost >', 200)->where('p.cost <=', 500);
            } elseif ($range === '1000+') {
                $builder->where('p.cost >', 1000);
            }
        }

        // Hours range filter
        if (!empty($filters['hours_range'])) {
            $range = $filters['hours_range'];
            if ($range === '1-10') {
                $builder->where('p.number_of_hours >=', 1)->where('p.number_of_hours <=', 10);
            } elseif ($range === '10-50') {
                $builder->where('p.number_of_hours >', 10)->where('p.number_of_hours <=', 50);
            } elseif ($range === '50-100') {
                $builder->where('p.number_of_hours >', 50)->where('p.number_of_hours <=', 100);
            } elseif ($range === '100-500') {
                $builder->where('p.number_of_hours >', 100)->where('p.number_of_hours <=', 500);
            } elseif ($range === '500+') {
                $builder->where('p.number_of_hours >', 500);
            }
            // Legacy support for old ranges
            elseif ($range === '0-50') {
                $builder->where('p.number_of_hours >=', 0)->where('p.number_of_hours <=', 50);
            } elseif ($range === '100+') {
                $builder->where('p.number_of_hours >', 100);
            }
        }
        
        // Status filter (for subscriptions)
        if (!empty($filters['status'])) {
            // Note: Plans don't have a status field, but we can filter by active/inactive based on subscribers
            // For now, we'll just pass it through if needed in the future
        }
    }

    /**
     * Get plan by ID
     */
    public function getPlanById($planId)
    {
        return $this->db->table('plans p')
            ->select('p.*, 
                     COALESCE((SELECT COUNT(*) FROM subscriptions WHERE plan_id = p.plan_id), 0) as total_subscribers,
                     COALESCE((SELECT COUNT(*) FROM subscriptions WHERE plan_id = p.plan_id AND status = "active"), 0) as active_subscribers')
            ->where('p.plan_id', $planId)
            ->get()
            ->getRowArray();
    }

    /**
     * Create new plan
     */
    public function createPlan($data)
    {
        return $this->insert($data);
    }

    /**
     * Update plan
     */
    public function updatePlan($planId, $data)
    {
        // Skip model validation since we validate in the controller
        $this->skipValidation(true);
        return $this->update($planId, $data);
    }

    /**
     * Delete plan
     */
    public function deletePlan($planId)
    {
        // Check if plan has any subscribers (active or inactive)
        $totalSubscribers = $this->db->table('subscriptions')
            ->where('plan_id', $planId)
            ->countAllResults();

        if ($totalSubscribers > 0) {
            return false; // Cannot delete plan with existing subscribers
        }

        return $this->delete($planId);
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats()
    {
        $totalPlans = $this->countAllResults();
        
        $totalSubscribers = $this->db->table('subscriptions')
            ->countAllResults();
        
        $activeSubscribers = $this->db->table('subscriptions')
            ->where('status', 'active')
            ->countAllResults();
        
        // Calculate total revenue from payments
        $revenueResult = $this->db->table('payments')
            ->selectSum('amount')
            ->where('status', 'paid')
            ->get()
            ->getRow();
        
        $totalRevenue = $revenueResult && $revenueResult->amount ? $revenueResult->amount : 0;

        return [
            'total_plans' => $totalPlans,
            'total_subscribers' => $totalSubscribers,
            'active_subscribers' => $activeSubscribers,
            'total_revenue' => $totalRevenue
        ];
    }

    /**
     * Get all plans (no pagination, for export)
     */
    public function getAllPlans($filters = [])
    {
        $builder = $this->db->table('plans p')
            ->select('p.*, 
                     COALESCE((SELECT COUNT(*) FROM subscriptions WHERE plan_id = p.plan_id), 0) as total_subscribers,
                     COALESCE((SELECT COUNT(*) FROM subscriptions WHERE plan_id = p.plan_id AND status = "active"), 0) as active_subscribers')
            ->orderBy('p.cost', 'ASC');

        // Apply filters
        $this->applyFilters($builder, $filters);

        return $builder->get()->getResultArray();
    }
}

