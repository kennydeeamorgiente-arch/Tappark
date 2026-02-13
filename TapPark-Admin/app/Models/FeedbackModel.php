<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getFeedbackList($limit = 25, $offset = 0, $filters = [], $sort = [])
    {
        try {
            $builder = $this->db->table('feedback f')
                ->select("f.feedback_id, f.user_id, f.subscription_id, f.rating, f.content, f.created_at, u.first_name, u.last_name, u.email, s.status AS subscription_status")
                ->join('users u', 'u.user_id = f.user_id', 'left')
                ->join('subscriptions s', 's.subscription_id = f.subscription_id', 'left')
                ->limit((int)$limit, (int)$offset);

            if (!empty($filters['rating'])) {
                $builder->where('f.rating', (int)$filters['rating']);
            }


            if (!empty($filters['date_from'])) {
                $builder->where('f.created_at >=', (string)$filters['date_from'] . ' 00:00:00');
            }

            if (!empty($filters['date_to'])) {
                $builder->where('f.created_at <=', (string)$filters['date_to'] . ' 23:59:59');
            }

            $allowedSort = [
                'feedback_id' => 'f.feedback_id',
                'rating' => 'f.rating',
                'created_at' => 'f.created_at'
            ];

            $sortByKey = $sort['by'] ?? null;
            $sortDir = strtolower($sort['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

            if ($sortByKey && isset($allowedSort[$sortByKey])) {
                $builder->orderBy($allowedSort[$sortByKey], $sortDir);
            } else {
                $builder->orderBy('f.created_at', 'DESC');
            }

            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'FeedbackModel::getFeedbackList - ' . $e->getMessage());
            return [];
        }
    }

    public function getFeedbackCount($filters = [])
    {
        try {
            $builder = $this->db->table('feedback f');

            if (!empty($filters['rating'])) {
                $builder->where('f.rating', (int)$filters['rating']);
            }


            if (!empty($filters['date_from'])) {
                $builder->where('f.created_at >=', (string)$filters['date_from'] . ' 00:00:00');
            }

            if (!empty($filters['date_to'])) {
                $builder->where('f.created_at <=', (string)$filters['date_to'] . ' 23:59:59');
            }

            return (int)$builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'FeedbackModel::getFeedbackCount - ' . $e->getMessage());
            return 0;
        }
    }

    public function getFeedbackById($feedbackId)
    {
        try {
            $builder = $this->db->table('feedback f')
                ->select("f.feedback_id, f.user_id, f.subscription_id, f.rating, f.content, f.created_at, u.first_name, u.last_name, u.email, s.status AS subscription_status")
                ->join('users u', 'u.user_id = f.user_id', 'left')
                ->join('subscriptions s', 's.subscription_id = f.subscription_id', 'left')
                ->where('f.feedback_id', (int)$feedbackId)
                ->limit(1);

            return $builder->get()->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'FeedbackModel::getFeedbackById - ' . $e->getMessage());
            return null;
        }
    }

    public function getFeedbackComments($feedbackId)
    {
        try {
            $builder = $this->db->table('feedback_comments fc')
                ->select("fc.feedback_comment_id, fc.feedback_id, fc.user_id, fc.role, fc.comment, fc.created_at, u.first_name, u.last_name, u.email")
                ->join('users u', 'u.user_id = fc.user_id', 'left')
                ->where('fc.feedback_id', (int)$feedbackId)
                ->orderby('fc.created_at', 'ASC');

            return $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'FeedbackModel::getFeedbackComments - ' . $e->getMessage());
            return [];
        }
    }

    public function getComment($commentId)
    {
        try {
            $builder = $this->db->table('feedback_comments fc')
                ->select("fc.feedback_comment_id, fc.feedback_id, fc.user_id, fc.role, fc.comment, fc.created_at, u.first_name, u.last_name, u.email")
                ->join('users u', 'u.user_id = fc.user_id', 'left')
                ->where('fc.feedback_comment_id', (int)$commentId)
                ->limit(1);

            return $builder->get()->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'FeedbackModel::getComment - ' . $e->getMessage());
            return null;
        }
    }

    public function addComment($feedbackId, $userId, $role, $comment)
    {
        try {
            $data = [
                'feedback_id' => (int)$feedbackId,
                'user_id' => $userId !== null ? (int)$userId : null,
                'role' => $role,
                'comment' => $comment
            ];

            $this->db->table('feedback_comments')->insert($data);
            return $this->db->insertID();
        } catch (\Exception $e) {
            log_message('error', 'FeedbackModel::addComment - ' . $e->getMessage());
            return false;
        }
    }
    public function getFeedbackStats()
    {
        try {
            $total = $this->db->table('feedback')->countAllResults();
            
            $query = $this->db->table('feedback')
                ->selectAvg('rating')
                ->get()
                ->getRow();
            $avg = $query ? (float)$query->rating : 0;

            $positive = $this->db->table('feedback')
                ->where('rating >', 3)
                ->countAllResults();

            $critical = $this->db->table('feedback')
                ->where('rating <=', 3)
                ->countAllResults();

            return [
                'total' => $total,
                'average' => $avg,
                'positive' => $positive,
                'critical' => $critical
            ];
        } catch (\Exception $e) {
            log_message('error', 'FeedbackModel::getFeedbackStats - ' . $e->getMessage());
            return [
                'total' => 0,
                'average' => 0,
                'positive' => 0,
                'critical' => 0
            ];
        }
    }
}
