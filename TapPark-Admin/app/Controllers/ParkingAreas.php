<?php

namespace App\Controllers;

use App\Models\ParkingAreaModel;
use App\Models\ParkingSectionModel;
use App\Models\ParkingLayoutModel;

class ParkingAreas extends BaseController
{
    protected $areaModel;
    protected $sectionModel;
    protected $layoutModel;
    
    public function __construct()
    {
        $this->areaModel = new ParkingAreaModel();
        $this->sectionModel = new ParkingSectionModel();
        $this->layoutModel = new ParkingLayoutModel();
        helper('activity');
    }
    
    /**
     * Display main parking areas page
     */
    public function index()
    {
        // Check if AJAX request
        if ($this->request->isAJAX()) {
            return view('pages/parking/areas/index');
        }
        
        // Return full page layout
        $data = [
            'content' => view('pages/parking/areas/index')
        ];
        return view('main_layout', $data);
    }
    
    /**
     * Display parking overview page
     */
    public function overview()
    {
        // Check if AJAX request
        if ($this->request->isAJAX()) {
            return view('pages/parking/overview/index');
        }
        
        // Return full page layout
        $data = [
            'content' => view('pages/parking/overview/index')
        ];
        return view('main_layout', $data);
    }
    
    /**
     * Get list of all parking areas with stats
     */
    public function list()
    {
        try {
            $areas = $this->areaModel->getAreasWithStats();
            $stats = $this->areaModel->getOverallStats();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $areas,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::list Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load parking areas'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get single parking area details
     */
    public function get($id)
    {
        try {
            $area = $this->areaModel->find($id);
            
            if (!$area) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parking area not found'
                ])->setStatusCode(404);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $area
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::get Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load parking area'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Create new parking area
     */
    public function create()
    {
        try {
            $data = [
                'parking_area_name' => $this->request->getPost('parking_area_name'),
                'location' => $this->request->getPost('location'),
                'num_of_floors' => $this->request->getPost('num_of_floors') ?: 1,
                'status' => $this->request->getPost('status') ?: 'active'
            ];
            
            // Validation
            if (!$this->validate([
                'parking_area_name' => 'required|min_length[3]|max_length[100]',
                'location' => 'required|min_length[3]|max_length[255]',
                'status' => 'in_list[active,inactive]'
            ])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ])->setStatusCode(400);
            }
            
            $areaId = $this->areaModel->insert($data);
            
            if ($areaId) {
                // Log activity
                log_create('parking_area', $areaId, $data['parking_area_name']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Parking area created successfully',
                    'data' => ['parking_area_id' => $areaId],
                    'stats' => $this->areaModel->getOverallStats()
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create parking area'
            ])->setStatusCode(500);
            
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::create Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while creating parking area'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Update parking area
     */
    public function update($id)
    {
        try {
            $area = $this->areaModel->find($id);
            
            if (!$area) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parking area not found'
                ])->setStatusCode(404);
            }
            
            $data = [
                'parking_area_name' => $this->request->getPost('parking_area_name'),
                'location' => $this->request->getPost('location'),
                'num_of_floors' => $this->request->getPost('num_of_floors') ?: 1,
                'status' => $this->request->getPost('status')
            ];
            
            // Validation
            if (!$this->validate([
                'parking_area_name' => 'required|min_length[3]|max_length[100]',
                'location' => 'required|min_length[3]|max_length[255]',
                'status' => 'in_list[active,inactive]'
            ])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ])->setStatusCode(400);
            }
            
            if ($this->areaModel->update($id, $data)) {
                // Log activity
                log_update('parking_area', $id, $data['parking_area_name']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Parking area updated successfully',
                    'stats' => $this->areaModel->getOverallStats()
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update parking area'
            ])->setStatusCode(500);
            
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::update Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating parking area'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Delete parking area
     */
    public function delete($id)
    {
        try {
            $area = $this->areaModel->find($id);
            
            if (!$area) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parking area not found'
                ])->setStatusCode(404);
            }
            
            if ($this->areaModel->delete($id)) {
                // Log activity
                log_delete('parking_area', $id, $area['parking_area_name']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Parking area deleted successfully',
                    'stats' => $this->areaModel->getOverallStats()
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete parking area'
            ])->setStatusCode(500);
            
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::delete Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while deleting parking area'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get sections for a specific area
     */
    public function getSections($areaId)
    {
        try {
            $sections = $this->sectionModel->getSectionsByArea($areaId);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $sections
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::getSections Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load sections'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get single section details
     */
    public function getSection($sectionId)
    {
        try {
            $section = $this->sectionModel->find($sectionId);
            
            if (!$section) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Section not found'
                ])->setStatusCode(404);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $section
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::getSection Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load section'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Create new section
     */
    public function createSection()
    {
        try {
            // Debug: Log all incoming POST data
            log_message('debug', 'Create Section - ALL POST DATA: ' . json_encode($_POST));
            log_message('debug', 'Create Section - Raw form data: ' . json_encode($this->request->getPost()));
            
            // Get each field individually for debugging
            $parking_area_id = $this->request->getPost('parking_area_id');
            $section_name = $this->request->getPost('section_name');
            $vehicle_type_id = $this->request->getPost('vehicle_type_id');
            $section_mode = $this->request->getPost('section_mode');
            $capacity = $this->request->getPost('capacity');
            $grid_width = $this->request->getPost('grid_width');
            $rows = $this->request->getPost('rows');
            $columns = $this->request->getPost('columns');
            $floor_number = $this->request->getPost('floor_number');
            
            log_message('debug', 'Create Section - Individual fields:');
            log_message('debug', '  parking_area_id: ' . $parking_area_id);
            log_message('debug', '  section_name: ' . $section_name);
            log_message('debug', '  vehicle_type_id: ' . $vehicle_type_id);
            log_message('debug', '  section_mode: ' . $section_mode);
            log_message('debug', '  capacity: ' . $capacity);
            log_message('debug', '  grid_width: ' . $grid_width);
            log_message('debug', '  rows: ' . $rows);
            log_message('debug', '  columns: ' . $columns);
            log_message('debug', '  floor_number: ' . $floor_number);
            
            $data = [
                'parking_area_id' => $parking_area_id,
                'section_name' => trim((string) $section_name),
                'section_type' => 'Custom', // Default to Custom
                'status' => 'active', // Default to active
                'floor' => $floor_number ?: 1,
                'rows' => $rows,
                'columns' => $columns,
                'start_row' => 0, // Default to 0
                'start_col' => 0, // Default to 0
                'vehicle_type_id' => $vehicle_type_id,
                'section_mode' => $section_mode ?: 'slot_based',
                'capacity' => $capacity,
                'grid_width' => $grid_width,
                'is_rotated' => 0, // Default to 0
                'vehicle_type' => 'car' // Will be updated below
            ];
            
            // Debug: Log the processed data
            log_message('debug', 'Create Section - Processed data: ' . json_encode($data));
            
            // Get vehicle type name from vehicle_type_id
            $vehicleType = $this->areaModel->db->table('vehicle_types')
                ->where('vehicle_type_id', $data['vehicle_type_id'])
                ->get()
                ->getRow();
            if ($vehicleType) {
                $data['vehicle_type'] = strtolower($vehicleType->vehicle_type_name);
            } else {
                $data['vehicle_type'] = 'car'; // default
            }
            
            // Handle capacity_only mode properly
            if ($data['section_mode'] === 'capacity_only') {
                // For capacity_only: force 1 row, columns = grid_width
                $data['rows'] = 1;
                $data['columns'] = $data['grid_width'];
                // Capacity is directly provided, don't calculate from rows*columns
                if (empty($data['capacity'])) {
                    $data['capacity'] = 0;
                }
            } else {
                // For slot_based: calculate capacity from rows*columns
                if (empty($data['capacity'])) {
                    $data['capacity'] = ($data['rows'] * $data['columns']) ?: 0;
                }
                if (empty($data['grid_width'])) {
                    $data['grid_width'] = $data['columns'] ?: 1;
                }
            }
            
            // Validation rules based on section mode
            $validationRules = [
                'parking_area_id' => 'required|integer',
                'section_name' => 'required|min_length[1]|max_length[3]',
                'floor_number' => 'permit_empty|integer',
                'vehicle_type_id' => 'required|integer',
                'section_mode' => 'permit_empty|in_list[slot_based,capacity_only]',
                'capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
                'grid_width' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[20]'
            ];
            
            // Custom validation messages
            $validationMessages = [
                'section_name' => [
                    'required' => 'Section name is required',
                    'min_length' => 'Section name is required',
                    'max_length' => 'Section name must be 1-3 characters only'
                ],
                'parking_area_id' => [
                    'required' => 'Parking area is required',
                    'integer' => 'Invalid parking area selected'
                ],
                'vehicle_type_id' => [
                    'required' => 'Vehicle type is required',
                    'integer' => 'Invalid vehicle type selected'
                ],
                'capacity' => [
                    'integer' => 'Capacity must be a number',
                    'greater_than_equal_to' => 'Capacity must be 0 or greater'
                ],
                'grid_width' => [
                    'integer' => 'Grid width must be a number',
                    'greater_than_equal_to' => 'Grid width must be 0 or greater',
                    'less_than_equal_to' => 'Grid width cannot exceed 20'
                ]
            ];
            
            // For slot_based mode, rows and columns are required
            if ($data['section_mode'] !== 'capacity_only') {
                $validationRules['rows'] = 'required|integer|greater_than[0]';
                $validationRules['columns'] = 'required|integer|greater_than[0]';
            } else {
                // For capacity_only mode, rows and columns are not required (we set them)
                $validationRules['rows'] = 'permit_empty|integer|greater_than[0]';
                $validationRules['columns'] = 'permit_empty|integer|greater_than[0]';
            }
            
            // Check for duplicate section name on the same floor
            $existingSection = $this->sectionModel->where([
                'parking_area_id' => $data['parking_area_id'],
                'section_name' => $data['section_name'],
                'floor' => $data['floor']
            ])->first();
            
            if ($existingSection) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Section '{$data['section_name']}' already exists on floor {$data['floor']}. Please choose a different name."
                ])->setStatusCode(400);
            }
            
            // Debug: Log validation rules
            log_message('debug', 'Create Section - Validation rules: ' . json_encode($validationRules));
            
            if (!$this->validate($validationRules, $validationMessages)) {
                log_message('debug', 'Create Section - Validation failed: ' . json_encode($this->validator->getErrors()));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ])->setStatusCode(400);
            }
            
            log_message('debug', 'Create Section - Attempting to insert: ' . json_encode($data));
            $sectionId = $this->sectionModel->insert($data);
            
            log_message('debug', 'Create Section - Insert result: ' . $sectionId);
            
            if ($sectionId) {
                // Create parking spots for this section
                $this->createParkingSpotsForSection($sectionId, $data);
                
                // Log activity
                log_create('parking_section', $sectionId, $data['section_name']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Section created successfully',
                    'data' => ['parking_section_id' => $sectionId],
                    'stats' => $this->areaModel->getOverallStats()
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create section'
            ])->setStatusCode(500);
            
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::createSection Error: ' . $e->getMessage());
            log_message('error', 'ParkingAreas::createSection Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Update section
     */
    public function updateSection($sectionId)
    {
        try {
            $section = $this->sectionModel->find($sectionId);
            
            if (!$section) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Section not found'
                ])->setStatusCode(404);
            }
            
            $data = [
                'section_name' => trim((string) $this->request->getPost('section_name')),
                'floor' => $this->request->getPost('floor_number') ?: 1,
                'rows' => $this->request->getPost('rows'),
                'columns' => $this->request->getPost('columns'),
                'vehicle_type_id' => $this->request->getPost('vehicle_type_id'),
                'section_mode' => $this->request->getPost('section_mode') ?: 'slot_based',
                'capacity' => $this->request->getPost('capacity'),
                'grid_width' => $this->request->getPost('grid_width')
            ];
            
            // Get vehicle type name from vehicle_type_id (same as createSection)
            $vehicleType = $this->areaModel->db->table('vehicle_types')
                ->where('vehicle_type_id', $data['vehicle_type_id'])
                ->get()
                ->getRow();
            if ($vehicleType) {
                $data['vehicle_type'] = strtolower($vehicleType->vehicle_type_name);
            } else {
                $data['vehicle_type'] = 'car'; // default
            }
            
            // Handle capacity_only mode properly
            if ($data['section_mode'] === 'capacity_only') {
                // For capacity_only: force 1 row, columns = grid_width
                $data['rows'] = 1;
                $data['columns'] = $data['grid_width'];
                // Capacity is directly provided, don't calculate from rows*columns
                if (empty($data['capacity'])) {
                    $data['capacity'] = 0;
                }
            } else {
                // For slot_based: calculate capacity from rows*columns
                if (empty($data['capacity'])) {
                    $data['capacity'] = ($data['rows'] * $data['columns']) ?: 0;
                }
                if (empty($data['grid_width'])) {
                    $data['grid_width'] = $data['columns'] ?: 1;
                }
            }
            
            // Validation rules based on section mode
            $validationRules = [
                'section_name' => 'required|min_length[1]|max_length[3]',
                'floor_number' => 'permit_empty|integer',
                'vehicle_type_id' => 'required|integer',
                'section_mode' => 'permit_empty|in_list[slot_based,capacity_only]',
                'capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
                'grid_width' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[20]'
            ];
            
            // Custom validation messages
            $validationMessages = [
                'section_name' => [
                    'required' => 'Section name is required',
                    'min_length' => 'Section name is required',
                    'max_length' => 'Section name must be 1-3 characters only'
                ],
                'vehicle_type_id' => [
                    'required' => 'Vehicle type is required',
                    'integer' => 'Invalid vehicle type selected'
                ],
                'capacity' => [
                    'integer' => 'Capacity must be a number',
                    'greater_than_equal_to' => 'Capacity must be 0 or greater'
                ],
                'grid_width' => [
                    'integer' => 'Grid width must be a number',
                    'greater_than_equal_to' => 'Grid width must be 0 or greater',
                    'less_than_equal_to' => 'Grid width cannot exceed 20'
                ]
            ];
            
            // For slot_based mode, rows and columns are required
            if ($data['section_mode'] !== 'capacity_only') {
                $validationRules['rows'] = 'required|integer|greater_than[0]';
                $validationRules['columns'] = 'required|integer|greater_than[0]';
            } else {
                // For capacity_only mode, rows and columns are not required (we set them)
                $validationRules['rows'] = 'permit_empty|integer|greater_than[0]';
                $validationRules['columns'] = 'permit_empty|integer|greater_than[0]';
            }
            
            // Check for duplicate section name on the same floor (excluding current section)
            $existingSection = $this->sectionModel->where([
                'parking_area_id' => $section['parking_area_id'],
                'section_name' => $data['section_name'],
                'floor' => $data['floor']
            ])->where('parking_section_id !=', $sectionId)->first();
            
            if ($existingSection) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Section '{$data['section_name']}' already exists on floor {$data['floor']}. Please choose a different name."
                ])->setStatusCode(400);
            }
            
            if (!$this->validate($validationRules, $validationMessages)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ])->setStatusCode(400);
            }
            
            if ($this->sectionModel->update($sectionId, $data)) {
                // Update parking spots for this section
                $this->updateParkingSpotsForSection($sectionId, $data);
                
                // Log activity
                log_update('parking_section', $sectionId, $data['section_name']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Section updated successfully',
                    'stats' => $this->areaModel->getOverallStats()
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update section'
            ])->setStatusCode(500);
            
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::updateSection Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating section'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Delete section
     */
    public function deleteSection($sectionId)
    {
        try {
            $section = $this->sectionModel->find($sectionId);
            
            if (!$section) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Section not found'
                ])->setStatusCode(404);
            }
            
            if ($this->sectionModel->delete($sectionId)) {
                // Delete parking spots for this section
                $db = \Config\Database::connect();
                $db->table('parking_spot')
                    ->where('parking_section_id', $sectionId)
                    ->delete();
                
                // Log activity
                log_delete('parking_section', $sectionId, $section['section_name']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Section deleted successfully',
                    'stats' => $this->areaModel->getOverallStats()
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete section'
            ])->setStatusCode(500);
            
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::deleteSection Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while deleting section'
            ])->setStatusCode(500);
        }
    }

    /**
     * Create parking area with sections (Wizard endpoint)
     */
    public function createWithSections()
    {
        try {
            // Get JSON input
            $json = $this->request->getJSON(true);
            
            if (!$json || !isset($json['area']) || !isset($json['sections'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid request data'
                ])->setStatusCode(400);
            }

            $areaData = $json['area'];
            $sectionsData = $json['sections'];

            // Validate area data using setData for JSON input
            $validation = \Config\Services::validation();
            $validation->setRules([
                'parking_area_name' => 'required|min_length[3]|max_length[100]',
                'location' => 'required|min_length[3]|max_length[255]',
                'num_of_floors' => 'permit_empty|integer|greater_than_equal_to[1]',
                'status' => 'required|in_list[active,inactive]'
            ]);

            if (!$validation->run($areaData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation->getErrors()
                ])->setStatusCode(400);
            }

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Create parking area
            $areaId = $this->areaModel->insert([
                'parking_area_name' => $areaData['parking_area_name'],
                'location' => $areaData['location'],
                'num_of_floors' => $areaData['num_of_floors'] ?? 1,
                'status' => $areaData['status']
            ]);

            if (!$areaId) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create parking area'
                ])->setStatusCode(500);
            }

            // Create sections
            $sectionCount = 0;
            $totalSpots = 0;

            foreach ($sectionsData as $section) {
                // Validate section data
                if (!isset($section['section_name']) || !isset($section['rows']) || !isset($section['columns']) || !isset($section['vehicle_type_id'])) {
                    continue;
                }

                $section['section_name'] = trim((string) $section['section_name']);
                if ($section['section_name'] === '' || strlen($section['section_name']) > 3) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Section name must be 1-3 characters only'
                    ])->setStatusCode(400);
                }

                // Check for duplicate section name on the same floor within this batch
                $floor = $section['floor_number'] ?? 1;
                foreach ($sectionsData as $otherSection) {
                    if ($otherSection !== $section && 
                        $otherSection['section_name'] === $section['section_name'] && 
                        ($otherSection['floor_number'] ?? 1) === $floor) {
                        $db->transRollback();
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => "Duplicate section name '{$section['section_name']}' on floor {$floor}. Each section name must be unique per floor."
                        ])->setStatusCode(400);
                    }
                }

                // Check for existing section in database
                $existingSection = $this->sectionModel->where([
                    'parking_area_id' => $areaId,
                    'section_name' => $section['section_name'],
                    'floor' => $floor
                ])->first();
                
                if ($existingSection) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Section '{$section['section_name']}' already exists on floor {$floor}. Please choose a different name."
                    ])->setStatusCode(400);
                }

                // Validate numeric fields
                if (!is_numeric($section['rows']) || $section['rows'] <= 0) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Section rows must be a positive number'
                    ])->setStatusCode(400);
                }

                if (!is_numeric($section['columns']) || $section['columns'] <= 0) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Section columns must be a positive number'
                    ])->setStatusCode(400);
                }

                if (!is_numeric($section['vehicle_type_id']) || $section['vehicle_type_id'] <= 0) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Vehicle type must be a valid selection'
                    ])->setStatusCode(400);
                }

                $sectionInsertData = [
                    'parking_area_id' => $areaId,
                    'section_name' => $section['section_name'],
                    'section_type' => 'Custom', // Default to Custom
                    'status' => 'active', // Default to active
                    'floor' => $section['floor_number'] ?? 1,
                    'rows' => (int)$section['rows'],
                    'columns' => (int)$section['columns'],
                    'start_row' => 0, // Default to 0
                    'start_col' => 0, // Default to 0
                    'vehicle_type_id' => (int)$section['vehicle_type_id'],
                    'section_mode' => $section['section_mode'] ?? 'slot_based',
                    'capacity' => $section['capacity'] ?? ($section['rows'] * $section['columns']),
                    'grid_width' => $section['grid_width'] ?? $section['columns'],
                    'is_rotated' => 0, // Default to 0
                    'vehicle_type' => 'car' // Will be updated below
                ];

                // Get vehicle type name from vehicle_type_id
                $vehicleType = $db->table('vehicle_types')
                    ->where('vehicle_type_id', $sectionInsertData['vehicle_type_id'])
                    ->get()
                    ->getRow();
                if ($vehicleType) {
                    $sectionInsertData['vehicle_type'] = strtolower($vehicleType->vehicle_type_name);
                } else {
                    $sectionInsertData['vehicle_type'] = 'car'; // default
                }

                // Handle capacity_only mode properly
                if ($sectionInsertData['section_mode'] === 'capacity_only') {
                    // For capacity_only: force 1 row, columns = grid_width
                    $sectionInsertData['rows'] = 1;
                    $sectionInsertData['columns'] = $sectionInsertData['grid_width'];
                    // Capacity is directly provided, don't calculate from rows*columns
                    if (empty($sectionInsertData['capacity'])) {
                        $sectionInsertData['capacity'] = 0;
                    }
                } else {
                    // For slot_based: calculate capacity from rows*columns
                    if (empty($sectionInsertData['capacity'])) {
                        $sectionInsertData['capacity'] = ($sectionInsertData['rows'] * $sectionInsertData['columns']) ?: 0;
                    }
                    if (empty($sectionInsertData['grid_width'])) {
                        $sectionInsertData['grid_width'] = $sectionInsertData['columns'] ?: 1;
                    }
                }

                $sectionId = $this->sectionModel->insert($sectionInsertData);

                if ($sectionId) {
                    $sectionCount++;
                    // Use capacity if available, otherwise calculate from rows*columns
                    $totalSpots += $sectionInsertData['capacity'];
                    
                    // Create parking spots for this section
                    $this->createParkingSpotsForSection($sectionId, $sectionInsertData);
                }
            }

            // Complete transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction failed'
                ])->setStatusCode(500);
            }

            // Log activity
            log_create('parking_area', $areaId, "Created parking area '{$areaData['parking_area_name']}' with {$sectionCount} sections and {$totalSpots} total spots");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Parking area and sections created successfully',
                'data' => [
                    'area_id' => $areaId,
                    'sections_created' => $sectionCount,
                    'total_spots' => $totalSpots
                ],
                'stats' => $this->areaModel->getOverallStats()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::createWithSections Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get vehicle types (for dropdowns)
     */
    public function getVehicleTypes()
    {
        try {
            $types = $this->areaModel->db->table('vehicle_types')
                ->select('vehicle_type_id, vehicle_type_name')
                ->orderBy('vehicle_type_name', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $types
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::getVehicleTypes Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load vehicle types'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get sections for overview (with vehicle type info)
     */
    public function getOverviewSections($areaId)
    {
        try {
            $sections = $this->sectionModel->db->table('parking_section ps')
                ->select('
                    ps.parking_section_id,
                    ps.parking_area_id,
                    ps.section_name,
                    ps.floor as floor_number,
                    ps.rows,
                    ps.columns,
                    ps.vehicle_type_id,
                    ps.section_mode,
                    ps.capacity,
                    ps.grid_width,
                    ps.status,
                    vt.vehicle_type_name,
                    (ps.rows * ps.columns) as total_spots
                ', false)
                ->join('vehicle_types vt', 'ps.vehicle_type_id = vt.vehicle_type_id', 'left')
                ->where('ps.parking_area_id', $areaId)
                ->where('ps.status', 'active')
                ->orderBy('ps.floor', 'ASC')
                ->orderBy('ps.section_name', 'ASC')
                ->get()
                ->getResultArray();
            
            // Format the data - ensure floor field exists
            foreach ($sections as &$section) {
                $section['floor'] = $section['floor_number'] ?? 1;
                $section['vehicle_type'] = $section['vehicle_type_name'] ?? 'Unknown';
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $sections
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::getOverviewSections Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load sections'
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get section grid with spot details
     */
    public function getSectionGrid($sectionId)
    {
        try {
            $db = \Config\Database::connect();
            
            // Get section details
            $section = $db->table('parking_section ps')
                ->select('
                    ps.parking_section_id,
                    ps.section_name,
                    ps.rows,
                    ps.columns,
                    ps.vehicle_type_id,
                    vt.vehicle_type_name
                ')
                ->join('vehicle_types vt', 'ps.vehicle_type_id = vt.vehicle_type_id', 'left')
                ->where('ps.parking_section_id', $sectionId)
                ->get()
                ->getRowArray();
            
            if (!$section) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Section not found'
                ])->setStatusCode(404);
            }
            
            // Get all parking spots for this section
            // Note: This assumes parking_spot table exists. If not, we'll create spots array from grid
            $spots = $db->table('parking_spot')
                ->select('
                    parking_spot_id,
                    spot_number,
                    status,
                    grid_row,
                    grid_col,
                    is_occupied,
                    occupied_by
                ')
                ->where('parking_section_id', $sectionId)
                ->orderBy('grid_row', 'ASC')
                ->orderBy('grid_col', 'ASC')
                ->get()
                ->getResultArray();
            
            // If no spots exist in database, generate them from grid dimensions
            if (empty($spots)) {
                $spots = [];
                $rows = (int)($section['rows'] ?? 0);
                $cols = (int)($section['columns'] ?? 0);
                $sectionName = $section['section_name'] ?? 'S';
                
                for ($row = 0; $row < $rows; $row++) {
                    for ($col = 0; $col < $cols; $col++) {
                        $spotNumber = $sectionName . '-' . str_pad(($row * $cols + $col + 1), 2, '0', STR_PAD_LEFT);
                        $spots[] = [
                            'parking_spot_id' => null,
                            'spot_number' => $spotNumber,
                            'status' => 'available',
                            'grid_row' => $row,
                            'grid_col' => $col,
                            'is_occupied' => 0,
                            'occupied_by' => null
                        ];
                    }
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'section' => $section,
                    'spots' => $spots
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::getSectionGrid Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load section grid: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Get layout for area and floor
     */
    public function getLayout($areaId, $floor)
    {
        try {
            $layout = $this->layoutModel->getLayoutByAreaAndFloor($areaId, $floor);
            
            if (!$layout) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Layout not found for this area and floor'
                ])->setStatusCode(404);
            }
            
            // Get sections for this area and floor for additional context
            try {
                $sections = $this->sectionModel->db->table('parking_section ps')
                    ->select('
                        ps.parking_section_id,
                        ps.section_name,
                        ps.rows,
                        ps.columns,
                        ps.floor,
                        vt.vehicle_type_name
                    ')
                    ->join('vehicle_types vt', 'ps.vehicle_type_id = vt.vehicle_type_id', 'left')
                    ->where('ps.parking_area_id', $areaId)
                    ->where('ps.floor', $floor)
                    ->where('ps.status', 'active')
                    ->get()
                    ->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'ParkingAreas::getLayout - Error loading sections: ' . $e->getMessage());
                $sections = [];
            }
            
            // Parse layout_data if it's a string
            $layoutData = $layout['layout_data'] ?? null;
            if (is_string($layoutData)) {
                $layoutData = json_decode($layoutData, true);
            }
            
            // Extract SVG data from layout_data if it exists
            $svgData = null;
            if (is_array($layoutData) && isset($layoutData['svg_data'])) {
                $svgData = $layoutData['svg_data'];
            }
            
            // Debug: Log the layout data structure to ensure section data is preserved
            if (is_array($layoutData) && isset($layoutData['sections'])) {
                log_message('debug', 'ParkingAreas::getLayout - Loading layout with ' . count($layoutData['sections']) . ' sections');
                foreach ($layoutData['sections'] as $index => $section) {
                    if (isset($section['section_data'])) {
                        $sectionName = $section['section_data']['section_name'] ?? 'Unknown';
                        $sectionId = $section['section_data']['parking_section_id'] ?? 'No ID';
                        log_message('debug', "ParkingAreas::getLayout - Section {$index}: {$sectionName} (ID: {$sectionId})");
                    }
                }
            }
            
            
            // Get vehicle types
            $vehicleTypes = [];
            if (!empty($sections)) {
                $vehicleTypes = array_filter(array_unique(array_column($sections, 'vehicle_type_name')));
            }
            $vehicleTypesStr = !empty($vehicleTypes) ? implode(', ', $vehicleTypes) : 'Mixed';
            
            // Calculate total spots
            $totalSpots = 0;
            foreach ($sections as $section) {
                $rows = isset($section['rows']) ? (int)$section['rows'] : 0;
                $columns = isset($section['columns']) ? (int)$section['columns'] : 0;
                $totalSpots += $rows * $columns;
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'parking_layout_id' => $layout['parking_layout_id'] ?? null,
                    'parking_area_id' => $layout['parking_area_id'] ?? $areaId,
                    'floor' => $layout['floor'] ?? $floor,
                    'layout_data' => $layoutData,
                    'svg_data' => $svgData, // Extract from layout_data if exists
                    'sections' => $sections,
                    'total_spots' => $totalSpots,
                    'vehicle_types' => $vehicleTypesStr,
                    'created_at' => $layout['created_at'] ?? null,
                    'updated_at' => $layout['updated_at'] ?? null
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::getLayout Error: ' . $e->getMessage());
            log_message('error', 'ParkingAreas::getLayout Stack Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to load layout: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Save layout for area and floor
     */
    public function saveLayout()
    {
        try {
            $json = $this->request->getJSON(true);
            
            if (!$json || !isset($json['area_id']) || !isset($json['floor']) || !isset($json['layout_data'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid request data'
                ])->setStatusCode(400);
            }
            
            $areaId = (int)$json['area_id'];
            $floor = (int)$json['floor'];
            $layoutData = $json['layout_data'];
            
            // SVG data might be inside layout_data or separate
            // If it's already in layout_data, don't extract it - let the model handle it
            $svgData = null;
            if (isset($json['svg_data']) && !empty($json['svg_data'])) {
                // SVG data provided separately
                $svgData = $json['svg_data'];
            }
            // Note: If svg_data is already inside layout_data, the model will detect it
            
            // Validate area exists
            $area = $this->areaModel->find($areaId);
            if (!$area) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parking area not found'
                ])->setStatusCode(404);
            }
            
            // Save layout - pass layoutData as-is, and svgData separately
            // The model will handle merging svgData into layoutData if needed
            $result = $this->layoutModel->saveLayout($areaId, $floor, $layoutData, $svgData);
            
            // Result can be the layout ID (which is > 0) or false on failure
            // Even if update returns 0 rows affected, the ID is still valid
            if ($result !== false && $result > 0) {
                // Sync parking spots based on layout sections
                try {
                    $this->syncParkingSpotsFromLayout($areaId, $floor, $layoutData);
                } catch (\Exception $syncError) {
                    // Log but don't fail - layout is saved, spots can be synced later
                    log_message('warning', 'ParkingAreas::saveLayout - Failed to sync parking spots: ' . $syncError->getMessage());
                }
                
                try {
                    $session = session();
                    $userId = $session->get('user_id') ?? 0;
                    log_activity($userId, 'UPDATE', "Saved layout for area {$areaId}, floor {$floor}", $result);
                } catch (\Exception $logError) {
                    // Log activity might fail, but don't fail the whole request
                    log_message('warning', 'ParkingAreas::saveLayout - Failed to log activity: ' . $logError->getMessage());
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Layout saved successfully',
                    'layout_id' => $result,
                    'stats' => $this->areaModel->getOverallStats()
                ]);
            } else {
                log_message('error', 'ParkingAreas::saveLayout - Model returned false or invalid ID: ' . ($result ?: 'false'));
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save layout'
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::saveLayout Error: ' . $e->getMessage());
            log_message('error', 'ParkingAreas::saveLayout Stack Trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save layout: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Sync parking spots from layout data
     * Creates/updates parking_spot records based on sections in the layout
     */
    private function syncParkingSpotsFromLayout($areaId, $floor, $layoutData)
    {
        $db = \Config\Database::connect();
        
        // Parse layout_data if it's a string
        if (is_string($layoutData)) {
            $layoutData = json_decode($layoutData, true);
        }
        
        if (!is_array($layoutData) || !isset($layoutData['sections'])) {
            log_message('debug', 'ParkingAreas::syncParkingSpotsFromLayout - No sections in layout data');
            return;
        }
        
        $sections = $layoutData['sections'];
        if (!is_array($sections) || empty($sections)) {
            log_message('debug', 'ParkingAreas::syncParkingSpotsFromLayout - Sections array is empty');
            return;
        }
        
        // Get all sections for this area and floor to map section names to IDs
        $dbSections = $db->table('parking_section')
            ->select('parking_section_id, section_name, rows, columns')
            ->where('parking_area_id', $areaId)
            ->where('floor', $floor)
            ->where('status', 'active')
            ->get()
            ->getResultArray();
        
        // Create a map of section name to section ID
        $sectionMap = [];
        foreach ($dbSections as $dbSection) {
            $sectionMap[$dbSection['section_name']] = $dbSection;
        }
        
        // Process each section in the layout
        foreach ($sections as $sectionItem) {
            if (!isset($sectionItem['section_data'])) {
                continue;
            }
            
            $sectionData = $sectionItem['section_data'];
            
            // Extract section name from type (remove instance ID suffix if present)
            $sectionName = $sectionData['type'] ?? $sectionData['section_name'] ?? null;
            if (!$sectionName) {
                continue;
            }
            
            // If section name has underscore (instance ID format like "A_1234567890"), extract just "A"
            if (strpos($sectionName, '_') !== false) {
                $sectionName = explode('_', $sectionName)[0];
            }
            
            // Find matching database section
            if (!isset($sectionMap[$sectionName])) {
                log_message('debug', "ParkingAreas::syncParkingSpotsFromLayout - Section '{$sectionName}' not found in database");
                continue;
            }
            
            $dbSection = $sectionMap[$sectionName];
            $sectionId = $dbSection['parking_section_id'];
            $rows = (int)($sectionData['rows'] ?? $dbSection['rows'] ?? 0);
            $cols = (int)($sectionData['columns'] ?? $sectionData['cols'] ?? $dbSection['columns'] ?? 0);
            
            if ($rows <= 0 || $cols <= 0) {
                continue;
            }
            
            // Delete existing spots for this section (we'll recreate them)
            $db->table('parking_spot')
                ->where('parking_section_id', $sectionId)
                ->delete();
            
            // Get parking area information for area code
            $areaInfo = $db->table('parking_area pa')
                ->select('pa.parking_area_name')
                ->join('parking_section ps', 'pa.parking_area_id = ps.parking_area_id')
                ->where('ps.parking_section_id', $sectionId)
                ->get()
                ->getRow();
            
            // Generate area code (first 3 letters, uppercase)
            $areaCode = 'UNK';
            if ($areaInfo && $areaInfo->parking_area_name) {
                // Take first 3 letters and make uppercase
                $areaCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $areaInfo->parking_area_name), 0, 3));
                if (strlen($areaCode) < 3) {
                    $areaCode = str_pad($areaCode, 3, 'X');
                }
            }
            
            // Create spots for this section
            // grid_row and grid_col are relative to the section (0 to rows-1, 0 to cols-1)
            for ($row = 0; $row < $rows; $row++) {
                for ($col = 0; $col < $cols; $col++) {
                    $slotNumber = ($row * $cols) + $col + 1;
                    // Format: AREA-SECTION-XXX (e.g., MAI-HI-004)
                    $spotNumber = $areaCode . '-' . strtoupper($sectionName) . '-' . str_pad($slotNumber, 3, '0', STR_PAD_LEFT);
                    
                    $db->table('parking_spot')->insert([
                        'parking_section_id' => $sectionId,
                        'spot_number' => $spotNumber,
                        'status' => 'available',
                        'grid_row' => $row,
                        'grid_col' => $col,
                        'is_occupied' => 0,
                        'occupied_by' => null
                    ]);
                }
            }
        }
        
        log_message('info', "ParkingAreas::syncParkingSpotsFromLayout - Synced spots for area {$areaId}, floor {$floor}");
    }
    
    /**
     * Create parking spots for a section
     * Called when creating a new section
     */
    private function createParkingSpotsForSection($sectionId, $sectionData)
    {
        try {
            $db = \Config\Database::connect();
            
            $sectionName = $sectionData['section_name'];
            $rows = (int)($sectionData['rows'] ?? 0);
            $cols = (int)($sectionData['columns'] ?? 0);
            
            // For capacity_only mode, use grid_width for columns and 1 row
            if ($sectionData['section_mode'] === 'capacity_only') {
                $rows = 1;
                $cols = (int)($sectionData['grid_width'] ?? $sectionData['columns'] ?? 0);
            }
            
            if ($rows <= 0 || $cols <= 0) {
                log_message('debug', "ParkingAreas::createParkingSpotsForSection - Invalid dimensions for section {$sectionName}: {$rows}x{$cols}");
                return;
            }
            
            log_message('debug', "ParkingAreas::createParkingSpotsForSection - Creating spots for section {$sectionName}: {$rows}x{$cols}");
            
            // Get parking area information for area code
            $areaInfo = $db->table('parking_area pa')
                ->select('pa.parking_area_name')
                ->join('parking_section ps', 'pa.parking_area_id = ps.parking_area_id')
                ->where('ps.parking_section_id', $sectionId)
                ->get()
                ->getRow();
            
            // Generate area code (first 3 letters, uppercase)
            $areaCode = 'UNK';
            if ($areaInfo && $areaInfo->parking_area_name) {
                // Take first 3 letters and make uppercase
                $areaCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $areaInfo->parking_area_name), 0, 3));
                if (strlen($areaCode) < 3) {
                    $areaCode = str_pad($areaCode, 3, 'X');
                }
            }
            
            // Create spots for this section
            for ($row = 0; $row < $rows; $row++) {
                for ($col = 0; $col < $cols; $col++) {
                    $slotNumber = ($row * $cols) + $col + 1;
                    // Format: AREA-SECTION-XXX (e.g., MAI-HI-004)
                    $spotNumber = $areaCode . '-' . strtoupper($sectionName) . '-' . str_pad($slotNumber, 3, '0', STR_PAD_LEFT);
                    
                    $db->table('parking_spot')->insert([
                        'parking_section_id' => $sectionId,
                        'spot_number' => $spotNumber,
                        'status' => 'available',
                        'spot_type' => 'regular',
                        'grid_row' => $row,
                        'grid_col' => $col,
                        'is_occupied' => 0,
                        'occupied_by' => null,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            log_message('info', "ParkingAreas::createParkingSpotsForSection - Created " . ($rows * $cols) . " spots for section {$sectionName} with format {$areaCode}-{$sectionName}-XXX");
            
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::createParkingSpotsForSection Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Update parking spots for a section
     * Called when updating a section (recreates spots if dimensions changed)
     */
    private function updateParkingSpotsForSection($sectionId, $sectionData)
    {
        try {
            $db = \Config\Database::connect();
            
            // Get current section data to check if dimensions changed
            $currentSection = $this->sectionModel->find($sectionId);
            if (!$currentSection) {
                log_message('error', "ParkingAreas::updateParkingSpotsForSection - Section {$sectionId} not found");
                return;
            }
            
            $sectionName = $sectionData['section_name'];
            $newRows = (int)($sectionData['rows'] ?? 0);
            $newCols = (int)($sectionData['columns'] ?? 0);
            
            // For capacity_only mode, use grid_width for columns and 1 row
            if ($sectionData['section_mode'] === 'capacity_only') {
                $newRows = 1;
                $newCols = (int)($sectionData['grid_width'] ?? $sectionData['columns'] ?? 0);
            }
            
            // Get current dimensions from database
            $currentRows = (int)($currentSection['rows'] ?? 0);
            $currentCols = (int)($currentSection['columns'] ?? 0);
            
            if ($currentSection['section_mode'] === 'capacity_only') {
                $currentRows = 1;
                $currentCols = (int)($currentSection['grid_width'] ?? $currentSection['columns'] ?? 0);
            }
            
            // Check if dimensions changed
            if ($newRows === $currentRows && $newCols === $currentCols) {
                log_message('debug', "ParkingAreas::updateParkingSpotsForSection - No dimension change for section {$sectionName}, skipping spot update");
                return;
            }
            
            log_message('debug', "ParkingAreas::updateParkingSpotsForSection - Updating spots for section {$sectionName}: {$currentRows}x{$currentCols} -> {$newRows}x{$newCols}");
            
            // Get parking area information for area code
            $areaInfo = $db->table('parking_area pa')
                ->select('pa.parking_area_name')
                ->join('parking_section ps', 'pa.parking_area_id = ps.parking_area_id')
                ->where('ps.parking_section_id', $sectionId)
                ->get()
                ->getRow();
            
            // Generate area code (first 3 letters, uppercase)
            $areaCode = 'UNK';
            if ($areaInfo && $areaInfo->parking_area_name) {
                // Take first 3 letters and make uppercase
                $areaCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $areaInfo->parking_area_name), 0, 3));
                if (strlen($areaCode) < 3) {
                    $areaCode = str_pad($areaCode, 3, 'X');
                }
            }
            
            // Delete existing spots for this section
            $db->table('parking_spot')
                ->where('parking_section_id', $sectionId)
                ->delete();
            
            // Create new spots for this section
            for ($row = 0; $row < $newRows; $row++) {
                for ($col = 0; $col < $newCols; $col++) {
                    $slotNumber = ($row * $newCols) + $col + 1;
                    // Format: AREA-SECTION-XXX (e.g., MAI-HI-004)
                    $spotNumber = $areaCode . '-' . strtoupper($sectionName) . '-' . str_pad($slotNumber, 3, '0', STR_PAD_LEFT);
                    
                    $db->table('parking_spot')->insert([
                        'parking_section_id' => $sectionId,
                        'spot_number' => $spotNumber,
                        'status' => 'available',
                        'spot_type' => 'regular',
                        'grid_row' => $row,
                        'grid_col' => $col,
                        'is_occupied' => 0,
                        'occupied_by' => null,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            log_message('info', "ParkingAreas::updateParkingSpotsForSection - Recreated " . ($newRows * $newCols) . " spots for section {$sectionName} with format {$areaCode}-{$sectionName}-XXX");
            
        } catch (\Exception $e) {
            log_message('error', 'ParkingAreas::updateParkingSpotsForSection Error: ' . $e->getMessage());
        }
    }
}

