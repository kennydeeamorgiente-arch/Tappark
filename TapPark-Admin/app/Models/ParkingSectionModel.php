<?php

namespace App\Models;

use CodeIgniter\Model;

class ParkingSectionModel extends Model
{
    protected $table = 'parking_section';
    protected $primaryKey = 'parking_section_id';
    protected $allowedFields = [
        'parking_area_id',
        'section_name',
        'section_type',
        'status',
        'floor',
        'rows',
        'columns',
        'start_row',
        'start_col',
        'vehicle_type_id',
        'section_mode',
        'capacity',
        'grid_width',
        'is_rotated',
        'vehicle_type',
        'created_at'
    ];
    protected $useTimestamps = false; // No updated_at in this table
    protected $createdField = 'created_at';
    
    /**
     * Get all sections for a specific parking area with vehicle type info
     */
    public function getSectionsByArea($areaId)
    {
        // SIMPLE QUERY - JUST SELECT ALL COLUMNS
        $result = $this->db->table($this->table)
            ->select('*')
            ->where('parking_area_id', $areaId)
            ->orderBy('section_name', 'ASC')
            ->get()
            ->getResultArray();
            
        return $result;
    }
    
    /**
     * Get section count by area
     */
    public function countByArea($areaId)
    {
        return $this->where('parking_area_id', $areaId)->countAllResults();
    }
    
    /**
     * Get total spots count for an area
     */
    public function getTotalSpotsByArea($areaId)
    {
        $result = $this->db->table($this->table)
            ->select('SUM(CASE WHEN section_mode = "capacity_only" THEN capacity ELSE (`rows` * `columns`) END) as total_spots', false)
            ->where('parking_area_id', $areaId)
            ->get()
            ->getRowArray();
        
        return (int)($result['total_spots'] ?? 0);
    }
    
    /**
     * Delete all sections for a specific area
     * (Called when deleting an area)
     */
    public function deleteByArea($areaId)
    {
        return $this->where('parking_area_id', $areaId)->delete();
    }
}

