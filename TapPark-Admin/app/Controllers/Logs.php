<?php

namespace App\Controllers;

use App\Models\LogsModel;

class Logs extends BaseController
{
    protected $logsModel;

    public function __construct()
    {
        $this->logsModel = new LogsModel();
    }

    public function index()
    {
        // Get filters and pagination from request
        $filters = [
            'action_type' => $this->request->getGet('action_type') ?? 'all',
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
            'user_id' => $this->request->getGet('user_id'),
            'search' => $this->request->getGet('search'),
            'per_page' => $this->request->getGet('per_page') ?? session('app_settings')['records_per_page'] ?? 25,
            'page' => $this->request->getGet('page') ?? 1
        ];

        // Get total count for pagination
        $totalLogs = $this->logsModel->getLogsCount($filters);
        
        // Get logs with filters and pagination
        $logs = $this->logsModel->getLogsWithFilters($filters);
        
        // Calculate pagination data
        $perPage = (int)$filters['per_page'];
        $currentPage = (int)$filters['page'];
        $totalPages = ceil($totalLogs / $perPage);
        
        // Calculate showing range
        $showingFrom = $totalLogs > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $showingTo = min($currentPage * $perPage, $totalLogs);
        
        // Get activity summary
        $summary = $this->logsModel->getActivitySummary(7);
        
        // Get action types for filter dropdown
        $actionTypes = $this->logsModel->getActionTypes();
        
        // Get activity timeline for chart
        $timeline = $this->logsModel->getActivityTimeline(30);
        
        // Get most active users
        $activeUsers = $this->logsModel->getMostActiveUsers(10, 30);

        $data = [
            'logs' => $logs,
            'filters' => $filters,
            'summary' => $summary,
            'actionTypes' => $actionTypes,
            'timeline' => $timeline,
            'activeUsers' => $activeUsers,
            'pagination' => [
                'total' => $totalLogs,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'showing_from' => $showingFrom,
                'showing_to' => $showingTo
            ]
        ];

        // Check if this is an AJAX request
        if ($this->request->isAJAX()) {
            // Check if this is a filter change or pagination change
            // (when filter_change=1, it means we're updating only the table content)
            $isFilterChange = $this->request->getGet('filter_change') === '1';
            
            if ($isFilterChange) {
                // Return only the logs content (table + pagination) for filter/pagination changes
                return view('pages/logs/content', $data);
            } else {
                // First AJAX load - return full logs page with stats, chart, filter, and table
                return view('pages/logs/index', $data);
            }
        }
        
        // Return full page (for direct URL access, non-AJAX)
        return view('pages/logs/index', $data);
    }

    /**
     * API endpoint to get logs data via AJAX (returns JSON)
     */
    public function api()
    {
        // Get filters and pagination from request
        $filters = [
            'action_type' => $this->request->getGet('action_type') ?? 'all',
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
            'user_id' => $this->request->getGet('user_id'),
            'search' => $this->request->getGet('search'),
            'per_page' => $this->request->getGet('per_page') ?? session('app_settings')['records_per_page'] ?? 25,
            'page' => $this->request->getGet('page') ?? 1
        ];

        // Get total count for pagination
        $totalLogs = $this->logsModel->getLogsCount($filters);
        
        // Get logs with filters and pagination
        $logs = $this->logsModel->getLogsWithFilters($filters);
        
        // Calculate pagination data
        $perPage = (int)$filters['per_page'];
        $currentPage = (int)$filters['page'];
        $totalPages = ceil($totalLogs / $perPage);
        
        // Calculate showing range
        $showingFrom = $totalLogs > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
        $showingTo = min($currentPage * $perPage, $totalLogs);
        
        // Get activity summary
        $summary = $this->logsModel->getActivitySummary(7);
        
        // Get most active users - always show last 30 days (not filtered by date range)
        // This gives a consistent view of overall activity, while the logs table is filtered
        // Explicitly pass null for dates to ensure it always uses the 30-day period
        // NOTE: We intentionally ignore any date filters here - active users should always show overall stats
        $activeUsers = $this->logsModel->getMostActiveUsers(10, 30, null, null);
        
        // Debug logging
        log_message('debug', 'Logs API - ActiveUsers count: ' . count($activeUsers));
        if (!empty($activeUsers)) {
            log_message('debug', 'Logs API - First active user: ' . json_encode($activeUsers[0]));
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'logs' => $logs,
                'summary' => $summary,
                'activeUsers' => $activeUsers ? $activeUsers : [],
                'pagination' => [
                    'total' => $totalLogs,
                    'per_page' => $perPage,
                    'current_page' => $currentPage,
                    'total_pages' => $totalPages,
                    'showing_from' => $showingFrom,
                    'showing_to' => $showingTo
                ]
            ]
        ]);
    }

    /**
     * Export activity logs to CSV based on current filters
     */
    public function export()
    {
        // Get filters from request (same as index)
        $filters = [
            'action_type' => $this->request->getGet('action_type') ?? 'all',
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
            'user_id' => $this->request->getGet('user_id'),
            'search' => $this->request->getGet('search')
        ];

        // Get ALL logs with filters (no pagination for export)
        $exportFilters = $filters;
        $exportFilters['per_page'] = 999999; // Get all records
        $exportFilters['page'] = 1;
        
        $logs = $this->logsModel->getLogsWithFilters($exportFilters);
        
        // Generate filename with current date
        $filename = 'Activity_Logs_' . date('Y-m-d_His') . '.csv';
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 (helps Excel recognize UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add CSV headers
        fputcsv($output, [
            'ID',
            'Timestamp',
            'User Name',
            'User Email',
            'Action Type',
            'Description',
            'Target ID',
            'Changed Field'
        ]);
        
        // Add data rows
        foreach ($logs as $log) {
            $userName = 'System';
            if (!empty($log['user_id']) && $log['user_id'] > 0 && !empty($log['first_name'])) {
                $userName = trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? ''));
            }
            
            // Format timestamp safely
            $timestamp = '';
            if (!empty($log['timestamp'])) {
                $time = strtotime($log['timestamp']);
                if ($time !== false) {
                    $timestamp = date('Y-m-d H:i:s', $time);
                } else {
                    $timestamp = $log['timestamp']; // Use original if can't parse
                }
            }
            
            fputcsv($output, [
                $log['logs_id'] ?? '',
                $timestamp,
                $userName,
                $log['email'] ?? '-',
                $log['action_type'] ?? 'GENERAL',
                $log['description'] ?? '',
                $log['target_id'] ?? '-',
                $log['change_field'] ?? '-'
            ]);
        }
        
        fclose($output);
        exit;
    }
}

