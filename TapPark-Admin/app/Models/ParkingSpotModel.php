<?php

namespace App\Models;

use CodeIgniter\Model;

class ParkingSpotModel extends Model
{
    protected $table = 'parking_spot';
    protected $primaryKey = 'parking_spot_id';
    protected $allowedFields = [
        'parking_section_id',
        'spot_number',
        'status',
        'spot_type',
        'grid_row',
        'grid_col',
        'is_occupied',
        'occupied_by',
        'occupied_at',
        'created_at'
    ];
    protected $useTimestamps = false; // No updated_at in this table
    protected $createdField = 'created_at';
    
    /**
     * Get all spots for a specific section
     */
    public function getSpotsBySection($sectionId)
    {
        return $this->where('parking_section_id', $sectionId)
            ->orderBy('grid_row', 'ASC')
            ->orderBy('grid_col', 'ASC')
            ->findAll();
    }
    
    /**
     * Get total spots count for a section
     */
    public function countBySection($sectionId)
    {
        return $this->where('parking_section_id', $sectionId)->countAllResults();
    }
    
    /**
     * Get available spots count for a section
     */
    public function countAvailableBySection($sectionId)
    {
        return $this->where('parking_section_id', $sectionId)
            ->where('status', 'available')
            ->countAllResults();
    }
    
    /**
     * Get occupied spots count for a section
     */
    public function countOccupiedBySection($sectionId)
    {
        return $this->where('parking_section_id', $sectionId)
            ->where('is_occupied', 1)
            ->countAllResults();
    }
    
    /**
     * Delete all spots for a specific section
     */
    public function deleteBySection($sectionId)
    {
        return $this->where('parking_section_id', $sectionId)->delete();
    }
    
    /**
     * Create spots for a section
     */
    public function createSpotsForSection($sectionId, $sectionName, $rows, $cols, $areaCode = 'UNK')
    {
        $db = \Config\Database::connect();
        
        // Get parking area information for area code if not provided
        if ($areaCode === 'UNK') {
            $areaInfo = $db->table('parking_area pa')
                ->select('pa.parking_area_name')
                ->join('parking_section ps', 'pa.parking_area_id = ps.parking_area_id')
                ->where('ps.parking_section_id', $sectionId)
                ->get()
                ->getRow();
            
            // Generate area code (first 3 letters, uppercase)
            if ($areaInfo && $areaInfo->parking_area_name) {
                $areaCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $areaInfo->parking_area_name), 0, 3));
                if (strlen($areaCode) < 3) {
                    $areaCode = str_pad($areaCode, 3, 'X');
                }
            }
        }
        
        $spots = [];
        
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $slotNumber = ($row * $cols) + $col + 1;
                // Format: AREA-SECTION-XXX (e.g., MAI-HI-004)
                $spotNumber = $areaCode . '-' . strtoupper($sectionName) . '-' . str_pad($slotNumber, 3, '0', STR_PAD_LEFT);
                
                $spots[] = [
                    'parking_section_id' => $sectionId,
                    'spot_number' => $spotNumber,
                    'status' => 'available',
                    'spot_type' => 'regular',
                    'grid_row' => $row,
                    'grid_col' => $col,
                    'is_occupied' => 0,
                    'occupied_by' => null,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        if (!empty($spots)) {
            $this->insertBatch($spots);
        }
        
        return count($spots);
    }
    
    /**
     * Update spot status
     */
    public function updateSpotStatus($spotId, $status, $occupiedBy = null)
    {
        $data = [
            'status' => $status,
            'is_occupied' => ($status === 'occupied') ? 1 : 0,
            'occupied_by' => ($status === 'occupied') ? $occupiedBy : null
        ];
        
        if ($status === 'occupied') {
            $data['occupied_at'] = date('Y-m-d H:i:s');
        } else {
            $data['occupied_at'] = null;
        }
        
        return $this->update($spotId, $data);
    }
    
    /**
     * Find spot by number within a section
     */
    public function findByNumberInSection($sectionId, $spotNumber)
    {
        return $this->where('parking_section_id', $sectionId)
            ->where('spot_number', $spotNumber)
            ->first();
    }
}
