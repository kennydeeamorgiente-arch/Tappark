<!-- Enhanced Layout Designer Modal -->
<div id="parkingLayoutDesignerModal" class="parking-modal-overlay">
    <div class="parking-modal-container parking-layout-designer">
        <div class="parking-modal-header">
            <h3><i class="fas fa-map"></i> Parking Layout Designer</h3>
            <button class="parking-close-btn" onclick="closeParkingLayoutDesigner()">&times;</button>
        </div>
        
        <div class="parking-modal-body">
            <div class="parking-designer-container">
                <!-- Left Panel - Tools & Sections -->
                <div class="parking-designer-sidebar">
                    <!-- Area and Floor Selection -->
                    <div class="area-floor-section">
                        <h4><i class="fas fa-building"></i> Area & Floor Selection</h4>
                        <div class="selection-controls">
                            <div class="control-item">
                                <label for="areaSelect" class="required-label">Parking Area:</label>
                                <select id="areaSelect" onchange="loadAreaData()">
                                    <option value="">Select an area</option>
                                </select>
                            </div>
                            <div class="control-item">
                                <label for="floorSelect" class="required-label">Floor:</label>
                                <select id="floorSelect" onchange="loadFloorSections()" disabled>
                                    <option value="">Select area first</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grid Size Controls -->
                    <div class="grid-size-section" style="margin-bottom: 20px; padding: 15px; background: #ffffff; border-radius: 10px; border: 1px solid #e9ecef; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);">
                        <h4 style="margin: 0 0 12px 0; color: #495057; font-size: 14px; display: flex; align-items: center; gap: 8px;"><i class="fas fa-expand-arrows-alt"></i> Grid Size</h4>
                        <div class="grid-size-controls" style="display: flex; gap: 10px;">
                            <button id="decreaseGrid" class="grid-size-btn" onclick="expandGrid('both-decrease')" title="Decrease Grid Size (Rows & Columns)" style="background: #dc3545; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease; flex: 1;">
                                <i class="fas fa-minus"></i>
                                <span>Decrease</span>
                            </button>
                            <button id="increaseGrid" class="grid-size-btn" onclick="expandGrid('both-increase')" title="Increase Grid Size (Rows & Columns)" style="background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 6px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px; transition: all 0.2s ease; flex: 1;">
                                <i class="fas fa-plus"></i>
                                <span>Increase</span>
                            </button>
                        </div>
                        <div class="rotation-controls" id="elementRotationControls" style="display: none; margin-top: 15px; padding: 12px; background: #f8f9fa; border-radius: 10px; border: 1px solid #dee2e6;">
                            <h5 style="margin: 0 0 8px 0; font-size: 14px; color: #495057; display: flex; align-items: center; gap: 6px;"><i class="fas fa-sync-alt"></i> Element Orientation</h5>
                            <div class="rotation-buttons" style="display: flex; gap: 8px;">
                                <button onclick="rotateSelectedElement(-1)" class="rotation-btn" data-step="-1" style="background: linear-gradient(135deg, #3949ab, #283593); color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px;">
                                    <i class="fas fa-undo-alt"></i> Rotate
                                </button>
                                <button onclick="rotateSelectedElement(1)" class="rotation-btn" data-step="1" style="background: linear-gradient(135deg, #3949ab, #283593); color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px;">
                                    Rotate <i class="fas fa-redo-alt"></i>
                                </button>
                            </div>
                            <div id="rotationStatus" class="rotation-status" style="margin-top: 8px; font-size: 12px; color: #495057; font-weight: 600; letter-spacing: 0.3px;">Horizontal</div>
                        </div>
                    </div>
                    
                    <!-- Layout Elements -->
                    <div class="tools-section">
                        <h4><i class="fas fa-road"></i> Road Elements</h4>
                        <div class="element-buttons">
                            <button class="element-btn road" data-type="road" onclick="selectElement('road')" style="background: linear-gradient(135deg, #7b1fa2, #512da8); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">═</span>
                                <span>Road</span>
                            </button>
                            <button class="element-btn l-road" data-type="l-road" onclick="selectElement('l-road')" style="background: linear-gradient(135deg, #546e7a, #37474f); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">└</span>
                                <span>L-Road</span>
                            </button>
                            <button class="element-btn intersection" data-type="intersection" onclick="selectElement('intersection')" style="background: linear-gradient(135deg, #546e7a, #37474f); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">✚</span>
                                <span>Intersection</span>
                            </button>
                            <button class="element-btn t-road" data-type="t-road" onclick="selectElement('t-road')" style="background: linear-gradient(135deg, #795548, #5d4037); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">┴</span>
                                <span>T-Road</span>
                            </button>
                            <button class="element-btn entrance" data-type="entrance" onclick="selectElement('entrance')" style="background: linear-gradient(135deg, #2e7d32, #1b5e20); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">⤷</span>
                                <span>Entrance</span>
                            </button>
                            <button class="element-btn exit" data-type="exit" onclick="selectElement('exit')" style="background: linear-gradient(135deg, #c62828, #8e0000); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">⤶</span>
                                <span>Exit</span>
                            </button>
                            <button class="element-btn oneway" data-type="oneway" onclick="selectElement('oneway')" style="background: linear-gradient(135deg, #f57c00, #ef6c00); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">⇢</span>
                                <span>One Way</span>
                            </button>
                            <button class="element-btn two-way" data-type="two-way" onclick="selectElement('two-way')" style="background: linear-gradient(135deg, #f9a825, #f57f17); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">&harr;</span>
                                <span>Two Way</span>
                            </button>
                            <button class="element-btn entry-exit" data-type="entry-exit" onclick="selectElement('entry-exit')" style="background: linear-gradient(135deg, #1565c0, #0d47a1); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">&harr;</span>
                                <span>Entry/Exit</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Obstacle Elements -->
                    <div class="tools-section">
                        <h4><i class="fas fa-cube"></i> Obstacle Elements</h4>
                        <div class="element-buttons">
                            <button class="element-btn clear" data-type="clear" onclick="selectElement('clear')" style="background: linear-gradient(135deg, #616161, #424242); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">✕</span>
                                <span>Clear</span>
                            </button>
                            <button class="element-btn wall" data-type="wall" onclick="selectElement('wall')" style="background: linear-gradient(135deg, #6c757d, #495057); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">▬</span>
                                <span>Wall</span>
                            </button>
                            <button class="element-btn pillar" data-type="pillar" onclick="selectElement('pillar')" style="background: linear-gradient(135deg, #757575, #424242); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">●</span>
                                <span>Pillar</span>
                            </button>
                            <button class="element-btn tree" data-type="tree" onclick="selectElement('tree')" style="background: linear-gradient(135deg, #4caf50, #2e7d32); color: white; border: none; padding: var(--element-button-padding); border-radius: 10px; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: var(--element-button-gap); font-size: var(--element-button-font); min-height: calc(var(--grid-cell-size) * 1.3); justify-content: center;">
                                <span class="element-icon">🌳</span>
                                <span>Tree</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Parking Sections -->
                    <div class="sections-section">
                        <h4><i class="fas fa-th"></i> Parking Sections</h4>
                        <div id="sectionsContainer" style="max-height: 300px; overflow-y: auto; padding-right: 5px;">
                            <div class="no-sections-message">
                                <i class="fas fa-info-circle"></i>
                                <p>Select an area and floor to view available sections</p>
                            </div>
                        </div>
                        <div id="sectionControls" style="margin-top: 10px; display: none;">
                            <button onclick="clearSectionSelection()" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                <i class="fas fa-times"></i> Cancel Selection
                            </button>
                        </div>
                        
                        <!-- Section Edit Controls -->
                        <div id="sectionEditControls" style="margin-top: 10px; display: none; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 2px solid #007bff;">
                            <h5 style="margin: 0 0 10px 0; color: #007bff;"><i class="fas fa-pen"></i> Edit Section</h5>
                            <div id="selectedSectionInfo" style="margin-bottom: 10px; font-size: 12px; color: #666;"></div>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <button onclick="deleteSelectedSectionForEdit()" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                <button onclick="cancelSectionEdit()" style="background: #6c757d; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px;">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Designer Stats -->
                    <div class="designer-stats-section" style="margin-top: 20px; padding: 15px; background: #fffde7; border-radius: 10px; border: 1px solid #fff59d; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); margin-bottom: 20px;">
                        <h4 style="margin: 0 0 12px 0; color: #f57f17; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-chart-pie"></i> 
                            <span>Designer Stats</span>
                            <span class="badge bg-warning text-dark ms-auto" style="font-size: 10px; font-weight: normal; letter-spacing: 0.3px;">LIVE LAYOUT DATA</span>
                        </h4>
                        <div class="designer-stats-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                            <div class="designer-stat-item" style="text-align: center; padding: 8px; background: #ffffff; border-radius: 8px; border: 1px solid #fff9c4;">
                                <div id="designerStatSections" style="font-weight: bold; color: #f57f17; font-size: 16px;">0</div>
                                <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; font-weight: 600;">Sections</div>
                            </div>
                            <div class="designer-stat-item" style="text-align: center; padding: 8px; background: #ffffff; border-radius: 8px; border: 1px solid #fff9c4;">
                                <div id="designerStatSpots" style="font-weight: bold; color: #f57f17; font-size: 16px;">0</div>
                                <div style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; font-weight: 600;">Total Spots</div>
                            </div>
                        </div>
                        <div style="margin-top: 10px; font-size: 11px; color: #856404; font-style: italic; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-info-circle"></i>
                            <span>Counts items currently placed on this map.</span>
                        </div>
                    </div>

                    <!-- Section Controls -->
                    <div class="section-controls">
                        <button class="control-btn" id="deleteSectionBtn" onclick="deleteSelectedSection()" disabled>
                            <i class="fas fa-trash"></i>
                            <span>Delete Section</span>
                        </button>
                    </div>
                </div>
                
                <!-- Right Panel - Layout Grid -->
                <div class="parking-designer-main">
                    <div class="layout-header">
                        <h4><i class="fas fa-map"></i> Parking Layout Grid (12×16)</h4>
                        <div class="grid-controls">
                            <button class="btn-secondary" onclick="clearGrid()" style="background: #6c757d; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-eraser"></i>
                                Clear All
                            </button>
                            <button class="btn-info" onclick="loadExistingLayout()" style="background: #17a2b8; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px; margin-left: 8px;">
                                <i class="fas fa-folder-open"></i>
                                Load Layout
                            </button>
                            <button class="btn-primary" onclick="saveLayout()" style="background: #dc3545; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px; margin-left: 8px;">
                                <i class="fas fa-save"></i>
                                Save Layout
                            </button>
                        </div>
                    </div>
                    <div class="grid-container">
                        <div class="grid-labels-vertical" style="display: none;">
                            <div class="grid-label">1</div>
                            <div class="grid-label">2</div>
                            <div class="grid-label">3</div>
                            <div class="grid-label">4</div>
                            <div class="grid-label">5</div>
                            <div class="grid-label">6</div>
                            <div class="grid-label">7</div>
                            <div class="grid-label">8</div>
                            <div class="grid-label">9</div>
                            <div class="grid-label">10</div>
                            <div class="grid-label">11</div>
                            <div class="grid-label">12</div>
                        </div>
                        <div class="grid-labels-horizontal" style="display: none;">
                            <div class="grid-label">A</div>
                            <div class="grid-label">B</div>
                            <div class="grid-label">C</div>
                            <div class="grid-label">D</div>
                            <div class="grid-label">E</div>
                            <div class="grid-label">F</div>
                            <div class="grid-label">G</div>
                            <div class="grid-label">H</div>
                            <div class="grid-label">I</div>
                            <div class="grid-label">J</div>
                            <div class="grid-label">K</div>
                            <div class="grid-label">L</div>
                            <div class="grid-label">M</div>
                            <div class="grid-label">N</div>
                            <div class="grid-label">O</div>
                            <div class="grid-label">P</div>
                        </div>
                        <div id="layout-grid" class="layout-grid" style="background-image: none; border: 1px solid #dee2e6;"></div>
                    </div>
                    
                    <!-- Grid Info -->
                    <div class="grid-info">
                        <div class="info-item">
                            <i class="fas fa-info-circle"></i>
                            <span>Click to place elements, drag to move sections</span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Mobile-optimized for app integration</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="parking-modal-footer">
        </div>
    </div>
</div>

<!-- Designer Message Modal -->
<div class="modal fade" id="designerMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-header justify-content-center" style="background: linear-gradient(135deg, #800000 0%, #990000 100%); border: none;">
                <h5 class="modal-title text-white fw-semibold text-center w-100" id="designerMessageTitle">Notice</h5>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center text-center" id="designerMessageBody" style="font-size: 0.98rem; color: #495057; min-height: 130px; padding: 24px 28px;"></div>
            <div class="modal-footer justify-content-center gap-3 border-0 pb-4 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4 designer-modal-cancel d-none" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4 designer-modal-confirm">OK</button>
            </div>
        </div>
    </div>
</div>

