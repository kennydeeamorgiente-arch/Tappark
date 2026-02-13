<?php

namespace App\Models;

use CodeIgniter\Model;

class ParkingAreaModel extends Model
{
    protected $table = 'parking_area';
    protected $primaryKey = 'parking_area_id';
    protected $allowedFields = [
        'parking_area_name',
        'location',
        'total_capacity',
        'num_of_floors',
        'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get all parking areas with calculated stats
     */
    public function getAreasWithStats()
    {
        // Get all areas with section counts and spot calculations
        $areas = $this->db->table($this->table . ' pa')
            ->select('
                pa.parking_area_id,
                pa.parking_area_name,
                pa.location,
                pa.total_capacity as capacity,
                pa.status,
                pa.created_at,
                pa.updated_at,
                COUNT(DISTINCT ps.parking_section_id) as total_sections,
                0 as total_spots,
                COALESCE(MAX(`ps`.`floor`), 1) as num_of_floors
            ', false)
            ->join('parking_section ps', 'pa.parking_area_id = ps.parking_area_id', 'left')
            ->groupBy('pa.parking_area_id')
            ->orderBy('pa.parking_area_name', 'ASC')
            ->get()
            ->getResultArray();
        // For each area, calculate real total spots and occupied spots
        foreach ($areas as &$area) {
            // Get stats from sections for this area
            $sectionStatsResult = $this->db->table('parking_section')
                ->select('
                    SUM(CASE 
                        WHEN section_mode = "capacity_only" THEN capacity 
                        ELSE (`rows` * `columns`) 
                    END) as total_spots
                ', false)
                ->where('parking_area_id', $area['parking_area_id'])
                ->get();

            $sectionStats = $sectionStatsResult ? $sectionStatsResult->getRow() : null;

            $area['total_spots'] = (int)($sectionStats ? $sectionStats->total_spots : 0);
            $area['occupied_spots'] = $this->getOccupiedSpots($area['parking_area_id']);
            $area['available_spots'] = $area['total_spots'] - $area['occupied_spots'];
            
            // Convert to integers
            $area['total_sections'] = (int)$area['total_sections'];
            $area['num_of_floors'] = (int)$area['num_of_floors'];
        }
        
        return $areas;
    }
    
    /**
     * Get occupied spots count for an area
     */
    private function getOccupiedSpots($areaId)
    {
        // Count reservations that are currently active (have start_time but no end_time)
        // joined through parking_spot and parking_section to parking_area
        $result = $this->db->table('reservations r')
            ->select('COUNT(DISTINCT r.parking_spots_id) as occupied')
            ->join('parking_spot pspot', 'r.parking_spots_id = pspot.parking_spot_id')
            ->join('parking_section ps', 'pspot.parking_section_id = ps.parking_section_id')
            ->where('ps.parking_area_id', $areaId)
            ->where('r.start_time IS NOT NULL')
            ->where('r.end_time IS NULL')
            ->where('r.booking_status !=', 'cancelled')
            ->get()
            ->getRowArray();
        
        return (int)($result['occupied'] ?? 0);
    }
    
    /**
     * Get overall statistics
     */
    public function getOverallStats()
    {
        $areas = $this->findAll();
        $activeAreas = 0;
        
        foreach ($areas as $area) {
            if ($area['status'] === 'active') {
                $activeAreas++;
            }
        }
        
        // Get total sections
        $sectionCount = $this->db->table('parking_section')
            ->countAllResults();
        
        // Get total spots using real logic: 
        // Sum (rows * columns) for slot-based (default) + capacity for capacity_only
        $spotStatsResult = $this->db->table('parking_section')
            ->select('
                SUM(CASE 
                    WHEN section_mode = "capacity_only" THEN capacity 
                    ELSE (`rows` * `columns`) 
                END) as total_spots
            ', false)
            ->get();

        $spotStats = $spotStatsResult ? $spotStatsResult->getRow() : null;

        $totalSpots = (int)($spotStats ? $spotStats->total_spots : 0);
        
        return [
            'total_areas' => count($areas),
            'active_areas' => $activeAreas,
            'total_sections' => $sectionCount,
            'total_spots' => $totalSpots
        ];
    }
    
    /**
     * Delete parking area and all associated sections
     */
    public function delete($id = null, bool $purge = false)
    {
        // First delete all sections in this area
        $sectionModel = new \App\Models\ParkingSectionModel();
        $sectionModel->deleteByArea($id);
        
        // Then delete the area itself
        return parent::delete($id, $purge);
    }
}

