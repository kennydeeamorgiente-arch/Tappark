<?php

/**
 * Activity Helper
 * 
 * Provides convenient functions for logging user activities throughout the application
 */

use App\Models\UserLogModel;

if (!function_exists('log_activity')) {
    /**
     * General activity logger
     * 
     * @param int $userId User ID performing the action
     * @param string $actionType Type of action (CREATE, UPDATE, DELETE, LOGIN, etc.)
     * @param string $description Description of the action
     * @param int|null $targetId ID of the affected record
     * @param string|null $changeField Field that was changed
     * @return bool|int
     */
    function log_activity($userId, $actionType, $description, $targetId = null, $changeField = null)
    {
        $request = service('request');
        $ip = $request ? $request->getIPAddress() : null;
        if (!empty($ip)) {
            $description .= " [ip: {$ip}]";
        }

        if (strlen($description) > 500) {
            $description = substr($description, 0, 500);
        }

        $logModel = new UserLogModel();
        $result = $logModel->logActivity($userId, $actionType, $description, $targetId, $changeField);
        if ($result === false) {
            log_message('error', 'Activity log insert failed: ' . json_encode($logModel->errors()));
        }
        return $result;
    }
}

if (!function_exists('log_create')) {
    /**
     * Log CREATE action
     * 
     * @param string $entityType Type of entity (User, Parking Area, etc.)
     * @param int $targetId ID of created record
     * @param string $entityName Name/identifier of created entity
     * @return bool|int
     */
    function log_create($entityType, $targetId, $entityName = '')
    {
        $session = session();
        $userId = $session->get('user_id') ?? 0;
        
        $description = $entityName 
            ? "Created {$entityType}: {$entityName}" 
            : "Created new {$entityType}";
        
        return log_activity($userId, 'CREATE', $description, $targetId);
    }
}

if (!function_exists('log_update')) {
    /**
     * Log UPDATE action
     * 
     * @param string $entityType Type of entity (User, Parking Area, etc.)
     * @param int $targetId ID of updated record
     * @param string $entityName Name/identifier of updated entity
     * @param string|null $changeField Specific field that changed
     * @param mixed $oldValue Old value (optional)
     * @param mixed $newValue New value (optional)
     * @return bool|int
     */
    function log_update($entityType, $targetId, $entityName = '', $changeField = null, $oldValue = null, $newValue = null)
    {
        $session = session();
        $userId = $session->get('user_id') ?? 0;
        
        $description = $entityName 
            ? "Updated {$entityType}: {$entityName}" 
            : "Updated {$entityType}";
        
        if ($changeField && $oldValue !== null && $newValue !== null) {
            $description .= " - Changed {$changeField} from '{$oldValue}' to '{$newValue}'";
        } elseif ($changeField) {
            $description .= " - Modified {$changeField}";
        }
        
        return log_activity($userId, 'UPDATE', $description, $targetId, $changeField);
    }
}

if (!function_exists('log_delete')) {
    /**
     * Log DELETE action
     * 
     * @param string $entityType Type of entity (User, Parking Area, etc.)
     * @param int $targetId ID of deleted record
     * @param string $entityName Name/identifier of deleted entity
     * @return bool|int
     */
    function log_delete($entityType, $targetId, $entityName = '')
    {
        $session = session();
        $userId = $session->get('user_id') ?? 0;
        
        $description = $entityName 
            ? "Deleted {$entityType}: {$entityName}" 
            : "Deleted {$entityType}";
        
        return log_activity($userId, 'DELETE', $description, $targetId);
    }
}

if (!function_exists('log_status_change')) {
    /**
     * Log status change action
     * 
     * @param string $entityType Type of entity
     * @param int $targetId ID of record
     * @param string $entityName Name/identifier of entity
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @return bool|int
     */
    function log_status_change($entityType, $targetId, $entityName, $oldStatus, $newStatus)
    {
        $session = session();
        $userId = $session->get('user_id') ?? 0;
        
        $description = $entityName 
            ? "{$entityType} '{$entityName}' status changed from '{$oldStatus}' to '{$newStatus}'" 
            : "{$entityType} status changed from '{$oldStatus}' to '{$newStatus}'";
        
        return log_activity($userId, 'STATUS_CHANGE', $description, $targetId, 'status');
    }
}

if (!function_exists('log_login')) {
    /**
     * Log successful login
     * 
     * @param int $userId User ID
     * @param string $userName User's name
     * @return bool|int
     */
    function log_login($userId, $userName)
    {
        return log_activity($userId, 'LOGIN', "Admin {$userName} logged in successfully");
    }
}

if (!function_exists('log_logout')) {
    /**
     * Log logout
     * 
     * @param int $userId User ID
     * @param string $userName User's name
     * @return bool|int
     */
    function log_logout($userId, $userName)
    {
        return log_activity($userId, 'LOGOUT', "Admin {$userName} logged out");
    }
}

if (!function_exists('log_failed_login')) {
    /**
     * Log failed login attempt
     * 
     * @param string $email Email used for login attempt
     * @return bool|int
     */
    function log_failed_login($email)
    {
        return log_activity(0, null, "Failed login attempt for email: {$email}");
    }
}

if (!function_exists('log_bulk_action')) {
    /**
     * Log bulk action on multiple records
     * 
     * @param string $actionType Action type
     * @param string $entityType Type of entity
     * @param int $count Number of records affected
     * @param string $details Additional details
     * @return bool|int
     */
    function log_bulk_action($actionType, $entityType, $count, $details = '')
    {
        $session = session();
        $userId = $session->get('user_id') ?? 0;
        
        $description = "Bulk {$actionType}: {$count} {$entityType} records";
        if ($details) {
            $description .= " - {$details}";
        }
        
        return log_activity($userId, 'BULK_' . strtoupper($actionType), $description);
    }
}

