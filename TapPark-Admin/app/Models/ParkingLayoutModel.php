<?php

namespace App\Models;

use CodeIgniter\Model;

class ParkingLayoutModel extends Model
{
    protected $table = 'parking_layout';
    protected $primaryKey = 'parking_layout_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'parking_area_id',
        'floor',
        'layout_data',
        'created_at',
        'updated_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get layout by area and floor
     */
    public function getLayoutByAreaAndFloor($areaId, $floor)
    {
        return $this->where('parking_area_id', $areaId)
                    ->where('floor', $floor)
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }

    /**
     * Save or update layout
     */
    public function saveLayout($areaId, $floor, $layoutData, $svgData = null)
    {
        // Check if layout exists
        $existing = $this->getLayoutByAreaAndFloor($areaId, $floor);
        
        // Prepare layout_data - convert to array if needed
        if (is_string($layoutData)) {
            $parsed = json_decode($layoutData, true);
            $layoutData = ($parsed !== null) ? $parsed : ['raw' => $layoutData];
        }
        
        // Ensure layoutData is an array
        if (!is_array($layoutData)) {
            $layoutData = [];
        }
        
        // If SVG data provided separately and not already in layout_data, add it
        if ($svgData && !isset($layoutData['svg_data'])) {
            $layoutData['svg_data'] = $svgData;
        }
        
        // Encode to JSON for storage
        $layoutDataJson = json_encode($layoutData);
        
        // Check for JSON encoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'ParkingLayoutModel::saveLayout - JSON encoding error: ' . json_last_error_msg());
            return false;
        }
        
        $data = [
            'parking_area_id' => $areaId,
            'floor' => (int)$floor, // Ensure floor is integer
            'layout_data' => $layoutDataJson
        ];

        try {
            if ($existing) {
                // Update existing
                $result = $this->update($existing['parking_layout_id'], $data);
                // update() returns number of affected rows (0 or more) or false on error
                if ($result !== false) {
                    // Return the existing ID even if 0 rows affected (data might be identical)
                    return $existing['parking_layout_id'];
                }
                log_message('error', 'ParkingLayoutModel::saveLayout - Update failed for layout_id: ' . $existing['parking_layout_id']);
                return false;
            } else {
                // Create new
                $id = $this->insert($data);
                // insert() returns the insert ID on success, false on failure
                if ($id !== false && $id > 0) {
                    return $id;
                }
                log_message('error', 'ParkingLayoutModel::saveLayout - Insert failed');
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'ParkingLayoutModel::saveLayout - Database error: ' . $e->getMessage());
            log_message('error', 'ParkingLayoutModel::saveLayout - Stack trace: ' . $e->getTraceAsString());
            // Also log the data structure (but truncate if too long)
            $dataLog = $data;
            if (isset($dataLog['layout_data']) && strlen($dataLog['layout_data']) > 500) {
                $dataLog['layout_data'] = substr($dataLog['layout_data'], 0, 500) . '... (truncated)';
            }
            log_message('error', 'ParkingLayoutModel::saveLayout - Data: ' . print_r($dataLog, true));
            return false;
        }
    }
}

