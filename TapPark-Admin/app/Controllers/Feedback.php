<?php

namespace App\Controllers;

use App\Models\FeedbackModel;

class Feedback extends BaseController
{
    protected $feedbackModel;

    public function __construct()
    {
        $this->feedbackModel = new FeedbackModel();
    }

    public function stats()
    {
        $stats = $this->feedbackModel->getFeedbackStats();
        return $this->response->setJSON($stats);
    }

    public function index()
    {
        $page = (int)($this->request->getGet('page') ?? 1);
        $perPage = (int)($this->request->getGet('per_page') ?? session('app_settings')['records_per_page'] ?? 25);
        $rating = $this->request->getGet('rating');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $sortBy = $this->request->getGet('sort_by');
        $sortDir = $this->request->getGet('sort_dir');
        if ($perPage < 1) {
            $perPage = 25;
        }
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $perPage;

        $filters = [
            'rating' => $rating,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];

        $sort = [
            'by' => $sortBy,
            'dir' => $sortDir
        ];

        // Get stats - Loading via AJAX now
        $stats = [
            'total' => '-',
            'average' => '-',
            'positive' => '-',
            'critical' => '-'
        ];

        $total = $this->feedbackModel->getFeedbackCount($filters);
        $items = $this->feedbackModel->getFeedbackList($perPage, $offset, $filters, $sort);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        $data = [
            'stats' => $stats,
            'feedbacks' => $items,
            'rating' => $rating,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'showing_from' => $total > 0 ? $offset + 1 : 0,
                'showing_to' => min($offset + $perPage, $total)
            ]
        ];

        if ($this->request->isAJAX()) {
            $isListUpdate = $this->request->getGet('list_only') === '1';
            if ($isListUpdate) {
                return view('pages/feedback/content', $data);
            }
            return view('pages/feedback/index', $data);
        }

        return view('pages/feedback/index', $data);
    }

    public function export()
    {
        $rating = $this->request->getGet('rating');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $sortBy = $this->request->getGet('sort_by');
        $sortDir = $this->request->getGet('sort_dir');

        $filters = [
            'rating' => $rating,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];

        $sort = [
            'by' => $sortBy,
            'dir' => $sortDir
        ];

        $rows = $this->feedbackModel->getFeedbackList(1000000, 0, $filters, $sort);

        $filename = 'feedback_export_' . date('Ymd_His') . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, [
            'Feedback ID',
            'User',
            'Email',
            'Rating',
            'Subscription ID',
            'Subscription Status',
            'Created At',
            'Content'
        ]);

        foreach ($rows as $r) {
            $fullName = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            fputcsv($handle, [
                $r['feedback_id'] ?? '',
                $fullName,
                $r['email'] ?? '',
                $r['rating'] ?? '',
                $r['subscription_id'] ?? '',
                $r['subscription_status'] ?? '',
                $r['created_at'] ?? '',
                $r['content'] ?? ''
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response->setBody($csv);
    }

    public function view($feedbackId)
    {
        $feedback = $this->feedbackModel->getFeedbackById($feedbackId);
        if (!$feedback) {
            if ($this->request->isAJAX()) {
                return view('pages/errors/404');
            }
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $comments = $this->feedbackModel->getFeedbackComments($feedbackId);

        $data = [
            'feedback' => $feedback,
            'comments' => $comments
        ];

        if ($this->request->isAJAX()) {
            return view('pages/feedback/view', $data);
        }

        return view('pages/feedback/view', $data);
    }

    public function reply()
    {
        $feedbackId = $this->request->getPost('feedback_id');
        $comment = $this->request->getPost('comment');

        if (empty($feedbackId) || empty($comment)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Feedback and comment are required.'
            ])->setStatusCode(400);
        }

        $adminUserId = session()->get('user_id');

        $insertId = $this->feedbackModel->addComment($feedbackId, $adminUserId, 'admin', $comment);

        if (!$insertId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to post reply.'
            ])->setStatusCode(500);
        }

        $newComment = $this->feedbackModel->getComment($insertId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Reply posted successfully.',
            'comment' => $newComment
        ]);
    }
}
