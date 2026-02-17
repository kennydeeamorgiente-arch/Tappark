// Layout Designer JavaScript
// Using jQuery



// SVG Functions for Layout Designer
// Based on the reference svg.html file

// Prevent redeclaration - only define if not already exists
if (typeof window.directionOptionsMap === 'undefined') {
    window.directionOptionsMap = {
        'road': ['horizontal', 'vertical'],
        'l-road': ['right-down', 'left-down', 'left-up', 'right-up'],
        't-road': ['up', 'down', 'left', 'right'],
        'intersection': ['right'], // Single direction since it's a cross-road
        'entrance': ['right', 'down', 'left', 'up'],
        'exit': ['right', 'down', 'left', 'up'],
        'oneway': ['right', 'down', 'left', 'up'],
        'two-way': ['horizontal', 'vertical'],
        'entry-exit': ['right', 'down', 'left', 'up']
    };
}

// Prevent redeclaration - only define if not already exists
if (typeof window.directionLabels === 'undefined') {
    window.directionLabels = {
        'horizontal': 'Horizontal ↔',
        'vertical': 'Vertical ↕',
        'right-down': 'Corner ↘',
        'left-down': 'Corner ↙',
        'left-up': 'Corner ↖',
        'right-up': 'Corner ↗',
        'right': 'Facing Right',
        'down': 'Facing Down',
        'left': 'Facing Left',
        'up': 'Facing Up',
        'none': 'Fixed Orientation'
    };
}

// Function to detect if vehicle type should be capacity-only
function shouldBeCapacityOnly(vehicleType, sectionMode) {
    // IMPORTANT: Always respect the section_mode from database
    // Don't force motorcycle/bicycle to be capacity-only - use what's actually stored
    return sectionMode === 'capacity_only';
}

// Function to get correct dimensions for sections
function getSectionDimensions(section) {
    const isCapacityOnly = shouldBeCapacityOnly(section.vehicle_type, section.section_mode);

    console.log('getSectionDimensions for', section.section_name, ':');
    console.log('- vehicle_type:', section.vehicle_type);
    console.log('- section_mode (from DB):', section.section_mode);
    console.log('- capacity (from DB):', section.capacity);
    console.log('- grid_width (from DB):', section.grid_width);
    console.log('- isCapacityOnly (final decision):', isCapacityOnly);

    if (isCapacityOnly) {
        return {
            rows: 1,
            cols: section.grid_width || section.columns,
            isCapacityOnly: true,
            displayText: `${section.capacity} capacity (width: ${section.grid_width || section.columns})`
        };
    } else {
        return {
            rows: section.rows,
            cols: section.columns,
            isCapacityOnly: false,
            displayText: `${section.rows}×${section.columns} (${section.rows * section.columns} slots)`
        };
    }
}

// Road base SVG (horizontal) - compact 50px with seamless connection
function roadBaseSVG() {
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <!-- Overlapping asphalt background (51x51) for gap-free connection -->
        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
        <!-- Center dashed line -->
        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
    </svg>`;
}

// Road with direction support using transform
function roadSVG(direction) {
    if (direction === 'vertical' || direction === 'up' || direction === 'down') {
        // Vertical road - rotate 90 degrees
        return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
            <g transform="rotate(90 25 25)">
                ${roadBaseSVG().replace(/<svg[^>]*>|<\/svg>/g, '')}
            </g>
        </svg>`;
    } else {
        // Horizontal road (default)
        return roadBaseSVG();
    }
}

// L-road with rotation support - seamless L shape
function lRoadSVG(direction) {
    const overlays = {
        'right-down': `
            <!-- Overlapping asphalt background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Horizontal center line -->
            <line x1="0" y1="25" x2="25" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
            <!-- Vertical center line -->
            <line x1="25" y1="25" x2="25" y2="50" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        `,
        'left-down': `
            <!-- Overlapping asphalt background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Horizontal center line -->
            <line x1="25" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
            <!-- Vertical center line -->
            <line x1="25" y1="25" x2="25" y2="50" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        `,
        'left-up': `
            <!-- Overlapping asphalt background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Horizontal center line -->
            <line x1="25" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
            <!-- Vertical center line -->
            <line x1="25" y1="0" x2="25" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        `,
        'right-up': `
            <!-- Overlapping asphalt background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Horizontal center line -->
            <line x1="0" y1="25" x2="25" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
            <!-- Vertical center line -->
            <line x1="25" y1="0" x2="25" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        `
    };

    const overlay = overlays[direction] || overlays['right-down'];

    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        ${overlay}
    </svg>`;
}


// T-road with rotation support - seamless T shape
function tRoadSVG(direction) {
    const overlays = {
        'up': `
            <!-- Overlapping asphalt background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Horizontal center line -->
            <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
            <!-- Vertical center line (top only) -->
            <line x1="25" y1="0" x2="25" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        `,
        'down': `
            <!-- Overlapping asphalt background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Horizontal center line -->
            <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
            <!-- Vertical center line (bottom only) -->
            <line x1="25" y1="25" x2="25" y2="50" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        `,
        'left': `
            <!-- Overlapping asphalt background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Horizontal center line (left only) -->
            <line x1="0" y1="25" x2="25" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
            <!-- Vertical center line -->
            <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        `,
        'right': `
            <!-- Overlapping asphalt background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Horizontal center line (right only) -->
            <line x1="25" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
            <!-- Vertical center line -->
            <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        `
    };

    const overlay = overlays[direction] || overlays['up'];

    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        ${overlay}
    </svg>`;
}
function intersectionSVG() {
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <!-- Overlapping asphalt background (51x51) -->
        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
        <!-- Horizontal center line -->
        <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
        <!-- Vertical center line -->
        <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3" stroke-dasharray="4,4" stroke-linecap="butt"/>
    </svg>`;
}

function directionToRotation(direction) {
    switch (direction) {
        case 'down':
        case 'vertical':
        case 'vertical-down':
            return 90;
        case 'left':
        case 'horizontal-left':
            return 180;
        case 'up':
        case 'vertical-up':
            return 270;
        default:
            return 0;
    }
}

// Parking slot SVG - clean 2D top-view design matching saved SVG
function parkingSlotSVG(sectionName = null, slotNumber = null) {
    // Build the label text: simple slot number only
    let labelText = 'P';
    if (slotNumber !== null) {
        labelText = String(slotNumber);
    }

    // Determine font size based on text length
    let fontSize = 14; // Larger for better visibility
    if (labelText.length > 3) {
        fontSize = 12; // Smaller for longer numbers
    } else if (labelText.length > 2) {
        fontSize = 13; // Medium for 3-digit numbers
    }

    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display: block;">
        <!-- Standard white background (50x50) -->
        <rect x="0" y="0" width="50" height="50" fill="#ffffff"/>
        <!-- Original parking lines -->
        <line x1="10" y1="25" x2="40" y2="25" stroke="#6c757d" stroke-width="1"/>
        <line x1="35" y1="10" x2="35" y2="40" stroke="#6c757d" stroke-width="1"/>
        <!-- Clean slot number text -->
        <text x="25" y="29" font-family="Arial, sans-serif" font-size="${fontSize}" font-weight="bold" fill="#2c3e50" text-anchor="middle" dominant-baseline="middle">${labelText}</text>
    </svg>`;
}

// Car icon (top-down, scaled to fit in 128 tile)
function vehicleCarSVG() {
    return `<svg viewBox="0 0 128 128" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display: block;">
        <rect x="24" y="16" width="80" height="96" rx="12" ry="12" fill="#1976d2" stroke="#111" stroke-width="1.5"/>
        <rect x="34" y="24" width="60" height="26" rx="4" fill="#b3e5fc" stroke="#111" stroke-width="1"/>
        <rect x="34" y="78" width="60" height="26" rx="4" fill="#b3e5fc" stroke="#111" stroke-width="1"/>
        <rect x="12" y="34" width="12" height="20" rx="3" fill="#222"/>
        <rect x="104" y="34" width="12" height="20" rx="3" fill="#222"/>
        <rect x="12" y="74" width="12" height="20" rx="3" fill="#222"/>
        <rect x="104" y="74" width="12" height="20" rx="3" fill="#222"/>
    </svg>`;
}

// Motor icon
function vehicleMotorSVG() {
    return `<svg viewBox="0 0 128 128" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display: block;">
        <circle cx="32" cy="28" r="10" fill="#333"/>
        <circle cx="96" cy="96" r="12" fill="#333"/>
        <rect x="44" y="30" width="40" height="58" rx="8" ry="8" fill="#c62828" stroke="#111" stroke-width="1.5"/>
        <rect x="50" y="60" width="28" height="16" fill="#555"/>
        <line x1="20" y1="26" x2="108" y2="26" stroke="#222" stroke-width="4"/>
    </svg>`;
}

// Bike icon
function vehicleBikeSVG() {
    return `<svg viewBox="0 0 128 128" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="display: block;">
        <circle cx="32" cy="96" r="16" stroke="#222" stroke-width="4" fill="none"/>
        <circle cx="96" cy="32" r="16" stroke="#222" stroke-width="4" fill="none"/>
        <line x1="32" y1="96" x2="72" y2="56" stroke="#2e7d32" stroke-width="6"/>
        <line x1="72" y1="56" x2="96" y2="32" stroke="#2e7d32" stroke-width="6"/>
        <rect x="60" y="52" width="12" height="6" fill="#444" transform="rotate(-20 66 55)"/>
        <line x1="96" y1="32" x2="116" y2="16" stroke="#444" stroke-width="4"/>
        <line x1="96" y1="32" x2="84" y2="12" stroke="#444" stroke-width="4"/>
    </svg>`;
}

// Entrance icon - seamless connection
function entranceSVG(direction = 'right') {
    const rotation = directionToRotation(direction);
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <g transform="rotate(${rotation} 25 25)">
            <!-- Asphalt base -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Lane marker -->
            <line x1="4" y1="25" x2="46" y2="25" stroke="#9ecb84" stroke-width="2" stroke-dasharray="4,4"/>
            <!-- Entry cue -->
            <line x1="10" y1="25" x2="34" y2="25" stroke="#52c41a" stroke-width="3.2" stroke-linecap="round"/>
            <polygon points="34,19 44,25 34,31" fill="#52c41a"/>
            <text x="11" y="16" font-family="Segoe UI, Arial" font-size="8" font-weight="700" fill="#c9f7a8">IN</text>
        </g>
    </svg>`;
}

// Exit icon - seamless connection
function exitSVG(direction = 'right') {
    const rotation = directionToRotation(direction);
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <g transform="rotate(${rotation} 25 25)">
            <!-- Asphalt base -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Lane marker -->
            <line x1="4" y1="25" x2="46" y2="25" stroke="#e6a2a2" stroke-width="2" stroke-dasharray="4,4"/>
            <!-- Exit cue -->
            <line x1="14" y1="25" x2="40" y2="25" stroke="#ff4d4f" stroke-width="3.2" stroke-linecap="round"/>
            <polygon points="40,19 46,25 40,31" fill="#ff4d4f"/>
            <text x="8" y="16" font-family="Segoe UI, Arial" font-size="7.5" font-weight="700" fill="#ffd7d7">OUT</text>
        </g>
    </svg>`;
}

// Oneway arrow icon - seamless connection
function onewaySVG(direction = 'right') {
    const rotation = directionToRotation(direction);
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <g transform="rotate(${rotation} 25 25)">
            <!-- Overlapping background (51x51) -->
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <!-- Base lane -->
            <line x1="4" y1="25" x2="46" y2="25" stroke="#ffd54f" stroke-width="2" stroke-linecap="round"/>
            <!-- Two flowing arrows -->
            <line x1="8" y1="19" x2="34" y2="19" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
            <polygon points="34,14 44,19 34,24" fill="#ffd54f"/>
            <line x1="8" y1="31" x2="34" y2="31" stroke="#ffd54f" stroke-width="3" stroke-linecap="round"/>
            <polygon points="34,26 44,31 34,36" fill="#ffd54f"/>
        </g>
    </svg>`;
}

// Two-way lane icon
function twoWaySVG(direction = 'horizontal') {
    const isVertical = direction === 'vertical';
    const groupTransform = isVertical ? 'rotate(90 25 25)' : '';
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <g transform="${groupTransform}">
            <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
            <rect x="6" y="6" width="38" height="38" rx="8" fill="#4a4a4a"/>
            <line x1="8" y1="25" x2="42" y2="25" stroke="#ffd54f" stroke-width="3"/>
            <line x1="10" y1="17" x2="30" y2="17" stroke="#ffffff" stroke-width="3.4" stroke-linecap="round"/>
            <polygon points="30,11 40,17 30,23" fill="#ffffff"/>
            <line x1="40" y1="33" x2="20" y2="33" stroke="#ffffff" stroke-width="3.4" stroke-linecap="round"/>
            <polygon points="20,27 10,33 20,39" fill="#ffffff"/>
        </g>
    </svg>`;
}

function entryExitSVG(direction = 'right') {
    let arrowLine = '<line x1="11" y1="35" x2="33" y2="35" stroke="white" stroke-width="3.1" stroke-linecap="round"/>';
    let arrowHead = '<polygon points="33,29 42,35 33,41" fill="white"/>';

    if (direction === 'left') {
        arrowLine = '<line x1="39" y1="35" x2="17" y2="35" stroke="white" stroke-width="3.1" stroke-linecap="round"/>';
        arrowHead = '<polygon points="17,29 8,35 17,41" fill="white"/>';
    } else if (direction === 'up') {
        arrowLine = '<line x1="25" y1="43" x2="25" y2="21" stroke="white" stroke-width="3.1" stroke-linecap="round"/>';
        arrowHead = '<polygon points="19,21 25,12 31,21" fill="white"/>';
    } else if (direction === 'down') {
        arrowLine = '<line x1="25" y1="17" x2="25" y2="39" stroke="white" stroke-width="3.1" stroke-linecap="round"/>';
        arrowHead = '<polygon points="19,39 25,48 31,39" fill="white"/>';
    }

    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
        <rect x="6" y="6" width="38" height="38" rx="8" fill="#4a4a4a"/>
        <rect x="7" y="8" width="17" height="13" rx="3.5" fill="#f44336"/>
        <text x="15.5" y="16.5" text-anchor="middle" font-family="Segoe UI, Arial" font-size="6.8" font-weight="900" fill="white">OUT</text>
        <rect x="26" y="8" width="17" height="13" rx="3.5" fill="#4CAF50"/>
        <text x="34.5" y="16.5" text-anchor="middle" font-family="Segoe UI, Arial" font-size="6.8" font-weight="900" fill="white">IN</text>
        ${arrowLine}
        ${arrowHead}
    </svg>`;
}

// Wall obstacle SVG - clean top-view
function wallSVG() {
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <rect x="0" y="0" width="51" height="51" fill="#6c757d"/>
        <!-- Wall texture lines -->
        <line x1="0" y1="12" x2="50" y2="12" stroke="#5a6268" stroke-width="1"/>
        <line x1="0" y1="25" x2="50" y2="25" stroke="#5a6268" stroke-width="1"/>
        <line x1="0" y1="38" x2="50" y2="38" stroke="#5a6268" stroke-width="1"/>
    </svg>`;
}

// Pillar obstacle SVG - seamless connection
function pillarSVG() {
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <!-- Overlapping background (51x51) for seamless connection -->
        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
        <!-- Pillar circle -->
        <circle cx="25" cy="25" r="18" fill="#757575"/>
    </svg>`;
}

// Tree obstacle SVG - seamless connection
function treeSVG() {
    return `<svg viewBox="0 0 50 50" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" shape-rendering="crispEdges" style="display: block;">
        <!-- Overlapping background (51x51) for seamless connection -->
        <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
        <!-- Tree circle -->
        <circle cx="25" cy="25" r="18" fill="#4CAF50"/>
        <!-- Tree center -->
        <circle cx="25" cy="25" r="3" fill="#795548"/>
    </svg>`;
}

// Function to get SVG content for any element type
function getElementSVG(elementType, direction = 'right', sectionType = null, slotNumber = null, sectionName = null) {
    switch (elementType) {
        case 'road':
            // Static road design - no generator
            if (direction === 'horizontal') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;
            } else if (direction === 'vertical') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;
            }
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;

        case 'l-road':
            // Static L-road design - no generator
            if (direction === 'right-down') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <path d="M 0 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
                </svg>`;
            } else if (direction === 'right-up') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <path d="M 0 25 L 25 25 L 25 0" stroke="#ffd54f" stroke-width="3" fill="none"/>
                </svg>`;
            } else if (direction === 'left-down') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <path d="M 50 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
                </svg>`;
            } else if (direction === 'left-up') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <path d="M 50 25 L 25 25 L 25 0" stroke="#ffd54f" stroke-width="3" fill="none"/>
                </svg>`;
            }
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                <rect x="0" y="0" width="50" height="50" fill="#4a4a4a"/>
                <path d="M 0 25 L 25 25 L 25 50" stroke="#ffd54f" stroke-width="3" fill="none"/>
            </svg>`;

        case 't-road':
            // Static T-road design - proper T-shape, not intersection
            if (direction === 'up' || direction === 'top') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <!-- Horizontal line at top -->
                    <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    <!-- Vertical line going down from center -->
                    <line x1="25" y1="25" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;
            } else if (direction === 'down' || direction === 'bottom') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <!-- Horizontal line at bottom -->
                    <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                    <!-- Vertical line going up from center -->
                    <line x1="25" y1="0" x2="25" y2="25" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;
            } else if (direction === 'left') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <!-- Vertical line on left -->
                    <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                    <!-- Horizontal line going right from center -->
                    <line x1="25" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;
            } else if (direction === 'right') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <!-- Vertical line on right -->
                    <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
                    <!-- Horizontal line going left from center -->
                    <line x1="0" y1="25" x2="25" y2="25" stroke="#ffd54f" stroke-width="3"/>
                </svg>`;
            }
            // Default: T-shape pointing down
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                <rect x="0" y="0" width="50" height="50" fill="#4a4a4a"/>
                <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                <line x1="25" y1="0" x2="25" y2="25" stroke="#ffd54f" stroke-width="3"/>
            </svg>`;

        case 'intersection':
            // Static intersection design - cross shape (+)
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                <!-- Horizontal line -->
                <line x1="0" y1="25" x2="50" y2="25" stroke="#ffd54f" stroke-width="3"/>
                <!-- Vertical line -->
                <line x1="25" y1="0" x2="25" y2="50" stroke="#ffd54f" stroke-width="3"/>
            </svg>`;

        case 'entrance':
            // Modernized entrance design with bold arrow and IN text
            if (direction === 'left') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                    <path d="M 33 35 L 17 35 M 22 30 L 17 35 L 22 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
            } else if (direction === 'up') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                    <path d="M 25 40 L 25 30 M 20 35 L 25 30 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
            } else if (direction === 'down') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                    <path d="M 25 30 L 25 40 M 20 35 L 25 40 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
            }
            // Default: right
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                <rect x="6" y="6" width="38" height="38" rx="8" fill="#4CAF50"/>
                <text x="25" y="22" font-family="Segoe UI, Arial" font-size="13" font-weight="900" fill="white" text-anchor="middle">IN</text>
                <path d="M 17 35 L 33 35 M 28 30 L 33 35 L 28 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>`;

        case 'exit':
            // Modernized exit design with bold arrow and OUT text
            if (direction === 'left') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                    <path d="M 33 35 L 17 35 M 22 30 L 17 35 L 22 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
            } else if (direction === 'up') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                    <path d="M 25 40 L 25 30 M 20 35 L 25 30 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
            } else if (direction === 'down') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                    <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                    <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                    <path d="M 25 30 L 25 40 M 20 35 L 25 40 L 30 35" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
            }
            // Default: right
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                <rect x="0" y="0" width="51" height="51" fill="#4a4a4a"/>
                <rect x="6" y="6" width="38" height="38" rx="8" fill="#f44336"/>
                <text x="25" y="22" font-family="Segoe UI, Arial" font-size="11" font-weight="900" fill="white" text-anchor="middle">OUT</text>
                <path d="M 17 35 L 33 35 M 28 30 L 33 35 L 28 40" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>`;

        case 'oneway':
            return onewaySVG(direction);

        case 'two-way':
            return twoWaySVG(direction);

        case 'entry-exit':
            return entryExitSVG(direction);

        case 'wall':
            // Static wall design - no generator
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                <rect x="0" y="0" width="51" height="51" fill="#757575"/>
                <rect x="5" y="5" width="40" height="40" fill="#616161"/>
            </svg>`;

        case 'pillar':
            // Static pillar design - no generator
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                <rect x="0" y="0" width="51" height="51" fill="#9E9E9E"/>
                <circle cx="25" cy="25" r="18" fill="#757575"/>
                <circle cx="25" cy="25" r="12" fill="#616161"/>
            </svg>`;

        case 'tree':
            // Static tree design - no generator
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50" shape-rendering="crispEdges">
                <rect x="0" y="0" width="51" height="51" fill="#E8F5E8"/>
                <circle cx="25" cy="25" r="18" fill="#4CAF50"/>
                <circle cx="25" cy="25" r="3" fill="#795548"/>
            </svg>`;

        case 'section':
            // Static section design with proper ID - no generator
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                <rect x="0" y="0" width="50" height="50" fill="#ffffff" stroke="#dee2e6" stroke-width="1"/>
                <text x="25" y="20" text-anchor="middle" font-family="Arial" font-size="10" font-weight="bold" fill="#333">${sectionName || 'SLOT'}</text>
                <text x="25" y="35" text-anchor="middle" font-family="Arial" font-size="8" fill="#666">${slotNumber || '#'}</text>
            </svg>`;

        case 'vehicle':
            // Static vehicle design - no generator
            if (sectionType === 'car' || sectionType === 'tahp') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="5" y="15" width="40" height="20" fill="#2196F3" rx="3"/>
                    <rect x="10" y="10" width="25" height="15" fill="#1976D2" rx="2"/>
                    <circle cx="12" cy="38" r="4" fill="#333"/>
                    <circle cx="38" cy="38" r="4" fill="#333"/>
                </svg>`;
            } else if (sectionType === 'motor' || sectionType === 'motorcycle') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <rect x="15" y="10" width="20" height="30" fill="#FF9800" rx="2"/>
                    <rect x="18" y="8" width="14" height="20" fill="#F57C00" rx="1"/>
                    <circle cx="20" cy="40" r="3" fill="#333"/>
                    <circle cx="30" cy="40" r="3" fill="#333"/>
                </svg>`;
            } else if (sectionType === 'bike' || sectionType === 'bicycle') {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                    <circle cx="25" cy="25" r="8" fill="#4CAF50"/>
                    <circle cx="25" cy="25" r="2" fill="#2E7D32"/>
                    <circle cx="15" cy="40" r="2" fill="#333"/>
                    <circle cx="35" cy="40" r="2" fill="#333"/>
                </svg>`;
            }
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                <rect x="5" y="15" width="40" height="20" fill="#2196F3" rx="3"/>
                <circle cx="12" cy="38" r="4" fill="#333"/>
                <circle cx="38" cy="38" r="4" fill="#333"/>
            </svg>`;

        default:
            return `<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">
                <rect x="0" y="0" width="50" height="50" fill="#f8f9fa"/>
                <text x="25" y="25" text-anchor="middle" font-family="Arial" font-size="8" fill="#333">${elementType}</text>
            </svg>`;
    }
}

// Function to generate complete SVG layout for saving
function generateCompleteSVG() {
    // Reset slot counters to prevent ID jumping on re-save
    window.sectionSlotCounters = {};

    // Use DOM grid size if available, otherwise use bounds (which includes elements and sections)
    const grid = document.getElementById('layout-grid');
    const bounds = calculateGridBounds();

    // Determine target dimensions: either from the currently visible designer grid or from used bounds
    // Use actual content bounds for the export to avoid large empty spaces
    const maxRow = bounds.maxRow;
    const maxCol = bounds.maxCol;

    // Origin is ALWAYS (0,0) to follow the grid layout exactly
    const minRow = 0;
    const minCol = 0;

    const TILE_SIZE = 50;
    const COLS = maxCol + 1;
    const ROWS = maxRow + 1;

    // Overall SVG dimensions
    const W = COLS * TILE_SIZE;
    const H = ROWS * TILE_SIZE;

    // Generate area code from area name
    const areaCode = currentArea?.parking_area_name
        ? currentArea.parking_area_name.substring(0, 3).toUpperCase()
        : 'FPA';

    let svgContent = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}" xmlns:xlink="http://www.w3.org/1999/xlink" shape-rendering="crispEdges">
        <defs>
            <!-- Define patterns and gradients -->
            <pattern id="asphalt" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <rect width="20" height="20" fill="#4a4a4a"/>
                <circle cx="2" cy="2" r="1" fill="#3a3a3a" opacity="0.5"/>
                <circle cx="7" cy="7" r="1" fill="#3a3a3a" opacity="0.5"/>
                <circle cx="12" cy="12" r="1" fill="#3a3a3a" opacity="0.5"/>
                <circle cx="17" cy="17" r="1" fill="#3a3a3a" opacity="0.5"/>
            </pattern>
            <pattern id="concrete" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                <rect width="10" height="10" fill="#4a4a4a"/>
            </pattern>
            <linearGradient id="parkingGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#ffffff;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#f8f9fa;stop-opacity:1" />
            </linearGradient> 
            <!-- Soft neutral gradient for capacity-only sections -->
            <linearGradient id="capacityGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#f5f5f5;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#dcdde1;stop-opacity:1" />
            </linearGradient>
        </defs>
        
        <!-- Background -->
        <rect x="0" y="0" width="${W}" height="${H}" fill="url(#concrete)"/>`;

    // Create unified layout data
    const optimizedElements = (typeof optimizeLayoutData === 'function' ? optimizeLayoutData().elements : {}) || {};

    // Add elements using static getElementSVG function
    Object.entries(optimizedElements).forEach(([position, element]) => {
        const [row, col] = position.split(',').map(Number);
        if (row >= bounds.minRow && row <= bounds.maxRow && col >= bounds.minCol && col <= bounds.maxCol) {
            const x = col * TILE_SIZE;
            const y = row * TILE_SIZE;

            const elementSvg = getElementSVG(element.type, element.direction);
            if (elementSvg) {
                // Extract inner content from SVG (remove outer SVG tags)
                // Use a tighter regex to ensure we don't accidentally leave trailing tags
                const innerContent = elementSvg.replace(/^<svg[^>]*>/, '').replace(/<\/svg>$/, '');
                svgContent += `
                    <g transform="translate(${x}, ${y})" shape-rendering="crispEdges">
                        ${innerContent}
                    </g>`;
            }
        }
    });



    // Add sections using getElementSVG for consistency
    Array.from(placedSections.entries()).forEach(([sectionId, sectionData]) => {
        if (sectionData.startRow !== undefined && sectionData.startCol !== undefined) {
            // CRITICAL: Always use the most reliable section ID available
            // Priority order: parking_section_id (from layout data) > sectionId (map key) > generated ID
            const dbSectionId = sectionData.parking_section_id || sectionId || '';
            console.log('Section ID Source Analysis:', {
                parking_section_id: sectionData.parking_section_id,
                mapKey: sectionId,
                final_dbSectionId: dbSectionId,
                section_name: sectionData.section_name
            });
            
            let sectionName = sectionData.type || sectionData.section_name || 'section';
            if (sectionName.includes('_')) {
                sectionName = sectionName.split('_')[0];
            }

            // Check if this is a capacity-only section
            const isCapacityOnly = sectionData.section_mode === 'capacity_only';
            // Use finalSectionId (ensures real database ID is used)
            const finalSectionId = sectionData.parking_section_id || sectionId || '';
            const vehicleType = sectionData.vehicle_type || 'car';

            if (isCapacityOnly) {
                // For capacity-only sections, create a single block
                const startX = sectionData.startCol * TILE_SIZE;
                const startY = sectionData.startRow * TILE_SIZE;

                const orientation = sectionData.orientation || 'horizontal';
                const actualGridWidth = orientation === 'vertical' ? sectionData.rows : (sectionData.grid_width || sectionData.cols);

                let sectionWidth, sectionHeight;
                if (orientation === 'vertical') {
                    sectionWidth = 1 * TILE_SIZE;
                    sectionHeight = actualGridWidth * TILE_SIZE + 1; // 1px overlap
                } else {
                    sectionWidth = actualGridWidth * TILE_SIZE + 1; // 1px overlap
                    sectionHeight = 1 * TILE_SIZE;
                }

                svgContent += `
                <g transform="translate(${startX}, ${startY})" 
                   data-section-id="${dbSectionId}" 
                   data-vehicle-type="${vehicleType}"
                   data-section-mode="capacity_only">
                    <!-- Capacity-only section background -->
                    <rect x="0" y="0" width="${sectionWidth}" height="${sectionHeight}" 
                          fill="url(#capacityGradient)" stroke="#ff6b35" stroke-width="1.5" rx="4"/>
                    <!-- Section label -->
                    ${orientation === 'vertical' ?
                        `<rect x="5" y="5" width="${sectionWidth - 10}" height="25" fill="white" stroke="#495057" stroke-width="1" rx="3"/>
                         <text x="${sectionWidth / 2}" y="17" text-anchor="middle" font-family="Arial" font-size="12" font-weight="bold" fill="#333333">${sectionName}</text>` :
                        `<rect x="5" y="5" width="60" height="25" fill="white" stroke="#495057" stroke-width="1" rx="3"/>
                         <text x="35" y="17" text-anchor="middle" font-family="Arial" font-size="12" font-weight="bold" fill="#333333">${sectionName}</text>`
                    }
                </g>`;
            } else {
                // Regular slot-based section - use getElementSVG for each slot
                const sectionSlotCounter = getSectionSlotCounter(sectionName);

                for (let r = 0; r < sectionData.rows; r++) {
                    for (let c = 0; c < sectionData.cols; c++) {
                        const row = sectionData.startRow + r;
                        const col = sectionData.startCol + c;
                        const slotNumber = (r * sectionData.cols) + c + 1;
                        const uniqueSlotNumber = sectionSlotCounter + slotNumber - 1;
                        const uniquePaddedSlot = uniqueSlotNumber.toString().padStart(3, '0');
                        const slotId = `${areaCode}-${sectionName}-${uniquePaddedSlot}`;

                        const x = col * TILE_SIZE;
                        const y = row * TILE_SIZE;

                        // Use getElementSVG for each parking slot
                        const slotSvg = getElementSVG('section', null, null, slotNumber, sectionName);
                        if (slotSvg) {
                            const innerContent = slotSvg.replace(/<svg[^>]*>/, '').replace(/<\/svg>$/, '');
                            svgContent += `
                    <g transform="translate(${x}, ${y})"
                                   id="slot-${slotId}" 
                                   data-type="parking-slot" 
                                   data-section="${sectionName}" 
                                   data-slot="${uniquePaddedSlot}" 
                                   data-slot-id="${slotId}"
                                   data-local-slot="${slotNumber}"
                                   data-section-id="${dbSectionId}"
                                   data-vehicle-type="${vehicleType}">
                                    ${innerContent}
                                </g>`;
                        }
                    }
                }
            }
        }
    });

    svgContent += '</svg>';
    return svgContent;
}

// Get unique slot counter for section
function getSectionSlotCounter(sectionName) {
    if (!window.sectionSlotCounters) {
        window.sectionSlotCounters = {};
    }

    if (!window.sectionSlotCounters[sectionName]) {
        window.sectionSlotCounters[sectionName] = 1;
    }

    const counter = window.sectionSlotCounters[sectionName];
    // Update counter for next section of same name
    window.sectionSlotCounters[sectionName] += 1;

    return counter;
}


// Global variables
if (typeof areas === 'undefined') {
    var areas = [];
}

// Back button function - Navigate to parking overview
function goBack() {
    // Try jQuery method first
    if (typeof $ !== 'undefined' && $.fn.load) {
        $("#content").load(window.BASE_URL + "index.php/overview-management", function (response, status) {
            if (status === "error") {
                console.error('Failed to load parking overview page:', response);
                $("#content").html("<p>Failed to load parking overview page.</p>");
            }
        });
    } else {
        // Fallback method using fetch
        fetch(window.BASE_URL + "index.php/overview-management")
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('content').innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading parking overview page:', error);
                document.getElementById('content').innerHTML = "<p>Failed to load parking overview page.</p>";
            });
    }
}
if (typeof currentArea === 'undefined') {
    var currentArea = null;
}
if (typeof currentFloor === 'undefined') {
    var currentFloor = null;
}
if (typeof sections === 'undefined') {
    var sections = [];
}
if (typeof isDraggingElement === 'undefined') {
    var isDraggingElement = false;
}
if (typeof draggedElementPos === 'undefined') {
    var draggedElementPos = null;
}
if (typeof selectedElementDirection === 'undefined') {
    var selectedElementDirection = 'horizontal'; // Default direction for elements
}
if (typeof currentDirectionOptions === 'undefined') {
    var currentDirectionOptions = [];
}
if (typeof selectedElement === 'undefined') {
    var selectedElement = null;
}
if (typeof selectedSectionData === 'undefined') {
    var selectedSectionData = null;
}
if (typeof selectedCell === 'undefined') {
    var selectedCell = null;
}
if (typeof elementOrientationState === 'undefined') {
    var elementOrientationState = {};
}
if (typeof layoutData === 'undefined') {
    var layoutData = {};
}
if (typeof placedSections === 'undefined') {
    var placedSections = new Map();
}
if (typeof draggedSection === 'undefined') {
    var draggedSection = null;
}
if (typeof isDragging === 'undefined') {
    var isDragging = false;
}
if (typeof dragStartPos === 'undefined') {
    var dragStartPos = null;
}
if (typeof isPlacingSection === 'undefined') {
    var isPlacingSection = false;
}
if (typeof currentPlacingSection === 'undefined') {
    var currentPlacingSection = null;
}
if (typeof currentPlacingData === 'undefined') {
    var currentPlacingData = null;
}
if (typeof selectedSectionForEdit === 'undefined') {
    var selectedSectionForEdit = null;
}
if (typeof isDraggingSection === 'undefined') {
    var isDraggingSection = false;
}
if (typeof dragStartCell === 'undefined') {
    var dragStartCell = null;
}
if (typeof originalSectionData === 'undefined') {
    var originalSectionData = null;
}

// Clean up existing state when reloading
function cleanupLayoutDesignerState() {
    // Clear any existing event listeners and state
    if (typeof placedSections !== 'undefined') {
        placedSections.clear();
        updateDesignerStats();
    }
    if (typeof layoutData !== 'undefined') {
        layoutData = {};
    }

    // Reset all state variables
    currentArea = null;
    currentFloor = null;
    sections = [];
    selectedElement = null;
    selectedElementDirection = 'horizontal';
    selectedSection = null;
    selectedSectionData = null;
    selectedCell = null;
    draggedSection = null;
    isDragging = false;
    dragStartPos = null;
    isPlacingSection = false;
    currentPlacingSection = null;
    currentPlacingData = null;
    selectedSectionForEdit = null;
    isDraggingSection = false;
    dragStartCell = null;
    originalSectionData = null;
}

// Initialize the page
async function initializeLayoutDesigner() {
    // IMPORTANT: Only initialize if the layout designer elements exist on this page
    // This prevents errors when the script loads on non-parking pages (Dashboard, Users, etc.)
    const designerModal = document.getElementById('parkingLayoutDesignerModal');
    const areaSelect = document.getElementById('areaSelect');

    if (!designerModal && !areaSelect) {
        // Layout designer elements don't exist on this page, skip initialization
        console.log('Layout Designer: Not on parking page, skipping initialization');
        return;
    }

    // Clean up any existing state first
    cleanupLayoutDesignerState();

    await loadAreas();

    // Add global mouse up listener to clear drag targets (only if not already added)
    if (!window.layoutDesignerMouseUpListenerAdded) {
        document.addEventListener('mouseup', function () {
            if (isDraggingSection || isDraggingElement) {
                clearDragTargets();
                isDraggingSection = false;
                isDraggingElement = false;
                dragStartCell = null;
                draggedElementPos = null;
            }
        });
        window.layoutDesignerMouseUpListenerAdded = true;
    }

    // Add global mouse move listener for drag feedback
    if (!window.layoutDesignerMouseMoveListenerAdded) {
        document.addEventListener('mousemove', function (e) {
            if (isDraggingSection || isDraggingElement) {
                e.preventDefault();
                // Optional: Add visual drag feedback here
                console.log('Dragging in progress...');
            }
        });
        window.layoutDesignerMouseMoveListenerAdded = true;
    }
}

// Don't auto-initialize - wait for the parking page to load
// The openLayoutDesigner function will handle initialization when needed

// Initialize on DOM ready only if elements exist
document.addEventListener('DOMContentLoaded', function () {
    // Check if we're on a parking page
    if (document.getElementById('parkingLayoutDesignerModal') || document.getElementById('areaSelect')) {
        initializeLayoutDesigner();
    }
});

// Load parking areas from API
async function loadAreas() {
    // Check if we're on a parking page - if not, skip loading
    const areaSelect = document.getElementById('areaSelect');
    if (!areaSelect) {
        console.log('Layout Designer: Not on parking page, skipping area load');
        return;
    }

    try {
        const response = await fetch(window.APP_BASE_URL + 'api/parking/overview');

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        // Map the database fields to the expected format
        areas = (data.data || []).map(area => ({
            parking_area_id: area.parking_area_id,
            parking_area_name: area.parking_area_name,
            location: area.location || '',
            num_of_floors: area.num_of_floors || 1,
            status: area.status || 'active'
        }));

        populateAreaSelect();

    } catch (error) {
        console.error('❌ Error loading areas from API:', error);
        // Only show error if we're on the parking page
        if (document.getElementById('parkingLayoutDesignerModal')) {
            await showError('Failed to load parking areas. Please refresh the page.', 'Load Error');
        }
    }
}


// Populate area dropdown
function populateAreaSelect() {
    const select = document.getElementById('areaSelect');

    // Safety check - don't try to populate if element doesn't exist
    if (!select) {
        console.log('Layout Designer: areaSelect element not found, skipping populate');
        return;
    }

    select.innerHTML = '<option value="">Select an area...</option>';

    areas.forEach(area => {
        const option = document.createElement('option');
        option.value = area.parking_area_id;
        option.textContent = area.parking_area_name;
        select.appendChild(option);
    });
    select.value = '';
    resetInterface();
}


// Check if there are unsaved changes
function hasUnsavedChanges() {
    // If we just saved, don't show unsaved changes
    if (window.layoutDesignerSaved === true) {
        return false;
    }
    return Object.keys(layoutData).length > 0 || placedSections.size > 0;
}

// Check if there are significant unsaved changes (more than just a few elements)
function hasSignificantUnsavedChanges() {
    const elementCount = Object.keys(layoutData).length;
    const sectionCount = placedSections.size;

    // Only consider it significant if there are more than 3 elements or any sections
    return elementCount > 3 || sectionCount > 0;
}

// User preferences for confirmations
if (typeof window.userPreferences === 'undefined') {
    window.userPreferences = {
        skipConfirmations: false,
        lastConfirmationTime: 0
    };
}

// Show confirmation dialog for unsaved changes (with smart timing)
async function confirmUnsavedChanges(action, options = {}) {
    const requireAny = options.requireAny === true;
    const hasPendingChanges = requireAny ? hasUnsavedChanges() : hasSignificantUnsavedChanges();

    // Only show confirmation for required change level
    if (!hasPendingChanges) {
        return true;
    }

    // If user has chosen to skip confirmations, respect that
    if (window.userPreferences.skipConfirmations) {
        return true;
    }

    // Don't show confirmation if it was shown recently (within 10 seconds)
    const now = Date.now();
    if (now - window.userPreferences.lastConfirmationTime < 10000) {
        return true;
    }

    // Show a more user-friendly confirmation
    const elementCount = Object.keys(layoutData).length;
    const sectionCount = placedSections.size;
    const message = `You have unsaved work (${elementCount} elements, ${sectionCount} sections). Do you want to ${action} and lose this work?`;
    const result = await showDesignerConfirm(message, 'Unsaved Changes', 'Proceed', 'Stay');

    if (result) {
        // Ask if they want to skip future confirmations
        const skipFuture = await showDesignerConfirm('Skip future confirmations for this session?', 'Remember Choice', 'Skip', 'Keep Asking');
        if (skipFuture) {
            window.userPreferences.skipConfirmations = true;
        } else {
            window.userPreferences.lastConfirmationTime = Date.now();
        }
        return true;
    }
    return false;
}

// Restore visual state of filter dropdowns
function restoreFilterVisualState() {

    // Use setTimeout to ensure the area dropdown is fully populated
    setTimeout(() => {
        // Restore area selection
        if (currentArea) {
            const areaSelect = document.getElementById('areaSelect');
            if (areaSelect && areaSelect.options.length > 1) {
                areaSelect.value = currentArea.parking_area_id;

                // Ensure floor dropdown is populated for this area
                if (currentArea.num_of_floors > 0) {
                    populateFloorSelect();

                    // Restore floor selection after a short delay
                    setTimeout(() => {
                        if (currentFloor) {
                            const floorSelect = document.getElementById('floorSelect');
                            if (floorSelect && floorSelect.options.length > 1) {
                                floorSelect.value = currentFloor;

                                // Load sections for the restored area/floor
                                loadFloorSections();
                            }
                        }
                    }, 100);
                }
            }
        }
    }, 100);
} // Fixed floor dropdown restoration - cache busting comment

// Update save button appearance based on unsaved changes
function updateSaveButtonAppearance() {
    const saveBtn = document.getElementById('saveLayoutBtn');
    if (!saveBtn) return;

    if (hasUnsavedChanges()) {
        // Show unsaved changes indicator
        saveBtn.style.background = '#ff6b35';
        saveBtn.style.boxShadow = '0 0 10px rgba(255, 107, 53, 0.5)';
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Layout <span style="color: #ffeb3b;">●</span>';
    } else {
        // Normal appearance
        saveBtn.style.background = '#dc3545';
        saveBtn.style.boxShadow = 'none';
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Layout';
    }
}

// Load area data when area is selected
async function loadAreaData() {
    const areaId = document.getElementById('areaSelect').value;

    if (!areaId) {
        resetInterface();
        return;
    }

    // Check for unsaved changes before switching areas
    if (!(await confirmUnsavedChanges('switch areas'))) {
        // Reset the select to previous value
        const previousArea = currentArea ? currentArea.parking_area_id : '';
        document.getElementById('areaSelect').value = previousArea;
        return;
    }

    currentArea = areas.find(area => area.parking_area_id == areaId);

    if (!currentArea) {
        showError('Area not found');
        return;
    }

    // Clear existing grid content when changing areas
    await clearGrid();
    clearAllSelections();

    // Clear placed sections and layout data
    placedSections.clear();
    layoutData = {};
    currentFloor = null;

    // Populate floor dropdown
    populateFloorSelect();
}


// Populate floor dropdown
function populateFloorSelect() {
    const select = document.getElementById('floorSelect');
    select.innerHTML = '<option value="">Select a floor...</option>';

    for (let i = 1; i <= currentArea.num_of_floors; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = `Floor ${i}`;
        select.appendChild(option);
    }

    select.disabled = false;
    select.value = '';
}

// Load sections for selected floor
async function loadFloorSections() {
    const floor = document.getElementById('floorSelect').value;

    if (!floor) {
        showNoSections();
        return;
    }

    // Check for unsaved changes before switching floors
    if (!(await confirmUnsavedChanges('switch floors'))) {
        // Reset the select to previous value
        const previousFloor = currentFloor || '';
        document.getElementById('floorSelect').value = previousFloor;
        return;
    }

    currentFloor = floor;

    // Clear existing grid content when changing floors
    await clearGrid();
    clearAllSelections();

    // Clear placed sections and layout data
    placedSections.clear();
    layoutData = {};

    try {
        const response = await fetch(window.APP_BASE_URL + 'api/parking/sections/' + currentArea.parking_area_id);

        if (!response.ok) {
            throw new Error('Failed to load sections');
        }

        const data = await response.json();
        console.log('API Response:', data); // Debug: Log API response

        // Map the database fields to the expected format
        sections = (data.data || []).map(section => {
            console.log('=== RAW SECTION DATA FROM BACKEND ===');
            console.log('Full section object:', section);
            console.log('section_name:', section.section_name);
            console.log('vehicle_type (from DB):', section.vehicle_type);
            console.log('section_mode (from DB):', section.section_mode);
            console.log('capacity (from DB):', section.capacity);
            console.log('grid_width (from DB):', section.grid_width);
            console.log('rows (from DB):', section.rows);
            console.log('columns (from DB):', section.columns);

            return {
                parking_section_id: section.parking_section_id,
                section_name: section.section_name,
                vehicle_type: section.vehicle_type,
                rows: section.rows,
                columns: section.columns,
                floor: section.floor,
                section_mode: section.section_mode || 'slot_based',
                capacity: section.capacity || (section.rows * section.columns),
                grid_width: section.grid_width || section.columns,
                totalSlots: section.capacity || (section.rows * section.columns),
                occupiedSlots: Math.floor(Math.random() * (section.capacity || (section.rows * section.columns))), // Random for demo
                availableSlots: (section.capacity || (section.rows * section.columns)) - Math.floor(Math.random() * (section.capacity || (section.rows * section.columns)))
            };
        });

        console.log('=== PROCESSED SECTIONS ARRAY ===');
        console.log('Final sections array:', sections);

        displaySections();

        // Update floor filter status with filtered count
        const currentFloor = document.getElementById('floorSelect').value;
        const filteredSections = currentFloor && currentFloor !== 'all'
            ? sections.filter(section => section.floor == currentFloor)
            : sections;
        updateFloorFilterStatus(currentFloor, filteredSections.length);

        // Update section indicators after loading new sections
        updateSectionIndicators();

    } catch (error) {
        console.error('Error loading sections:', error);
        showNoSections();
    }
}


// Display sections in grid
function displaySections() {
    const container = document.getElementById('sectionsContainer');

    if (sections.length === 0) {
        showNoSections();
        return;
    }

    // Filter sections by current floor
    const currentFloor = document.getElementById('floorSelect')?.value;
    let filteredSections = sections;

    if (currentFloor && currentFloor !== 'all') {
        filteredSections = sections.filter(section => section.floor == currentFloor);
    }

    container.innerHTML = filteredSections.map(section => createSectionButton(section)).join('');
}

// Update floor filter status display
function updateFloorFilterStatus(floor, sectionCount) {
    const statusElement = document.getElementById('floorFilterStatus');
    const instructionsElement = document.getElementById('filterInstructions');

    // These elements don't exist in modal-only mode, skip if not found
    if (!statusElement || !instructionsElement) {
        return;
    }

    if (floor === 'all') {
        statusElement.textContent = `(All Floors - ${sectionCount} sections)`;
        instructionsElement.textContent = `Showing all sections from all floors`;
    } else {
        statusElement.textContent = `(Floor ${floor} - ${sectionCount} sections)`;
        instructionsElement.textContent = `Showing sections from Floor ${floor} only`;
    }
}

// Create section button for designer - Updated to use SVG icons
function createSectionButton(section) {
    // Debug: Log section data
    console.log('=== CREATING BUTTON FOR SECTION ===');
    console.log('section_name:', section.section_name);
    console.log('vehicle_type:', section.vehicle_type);
    console.log('section_mode (from DB):', section.section_mode);
    console.log('capacity (from DB):', section.capacity);
    console.log('grid_width (from DB):', section.grid_width);
    console.log('rows (from DB):', section.rows);
    console.log('columns (from DB):', section.columns);

    // IMPORTANT: ONLY use section_mode from database - NO forcing!
    const isCapacityOnly = section.section_mode === 'capacity_only';

    console.log('isCapacityOnly (from DB only):', isCapacityOnly);

    // Use appropriate dimensions based ONLY on section_mode
    let rows, cols, displayText;
    if (isCapacityOnly) {
        // Capacity-only: 1 row, grid_width columns, use capacity value
        rows = 1;
        cols = section.grid_width || section.columns;
        displayText = `${section.capacity} capacity (width: ${section.grid_width || section.columns})`;
        console.log('CAPACITY-ONLY mode - using:', { rows, cols, capacity: section.capacity, grid_width: section.grid_width });
    } else {
        // Slot-based: use rows and columns from database
        rows = section.rows;
        cols = section.columns;
        displayText = `${section.rows}×${section.columns} (${section.rows * section.columns} slots)`;
        console.log('SLOT-BASED mode - using:', { rows, cols });
    }

    console.log('Final displayText:', displayText);

    // Use the correct dimensions for preview and selection
    const previewGrid = createPreviewGrid(rows, cols, section.section_name, isCapacityOnly);

    // Get appropriate SVG icon based on vehicle type
    let vehicleSVG = vehicleCarSVG(); // default
    switch (section.vehicle_type) {
        case 'motorcycle': vehicleSVG = vehicleMotorSVG(); break;
        case 'bicycle': vehicleSVG = vehicleBikeSVG(); break;
        case 'car':
        default: vehicleSVG = vehicleCarSVG(); break;
    }

    // Make the SVG smaller for the button
    const smallVehicleSVG = vehicleSVG.replace('width="100%" height="100%"', 'width="18" height="18"');

    // Use the correct display text from the logic above
    const modeBadge = isCapacityOnly ?
        '<span class="badge bg-warning" style="font-size: 9px; margin-left: 4px;">Capacity-only</span>' : '';

    return `
        <button class="section-btn" data-section="${section.section_name}" data-rows="${rows}" data-cols="${cols}" onclick="selectSection('${section.section_name}', ${rows}, ${cols})">
            <div class="section-preview">
                <div class="preview-grid" style="grid-template-columns: repeat(${cols}, 1fr);">
                    ${previewGrid}
                </div>
            </div>
            
            <!-- Section Info -->
            <div class="section-info">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="font-weight: 700; color: #2c3e50; font-size: 1.1rem;">Section ${section.section_name}</div>
                    ${modeBadge}
                </div>
                
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <div style="background: linear-gradient(135deg, #800000, #600000); color: white; padding: 6px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                        <i class="${getVehicleIcon(section.vehicle_type)}"></i>
                        <span style="text-transform: capitalize;">${section.vehicle_type}</span>
                    </div>
                </div>
                
                <div style="font-size: 0.9rem; color: #6c757d; font-weight: 500;">
                    ${displayText}
                </div>
        </button>
    `;
}

// Get vehicle icon based on type (for backward compatibility)
function getVehicleIcon(vehicleType) {
    switch (vehicleType) {
        case 'car': return 'fas fa-car';
        case 'motorcycle': return 'fas fa-motorcycle';
        case 'bicycle': return 'fas fa-bicycle';
        default: return 'fas fa-parking';
    }
}

// Create test section card
function createTestSectionButton(section) {
    const visualGrid = createVisualGrid(section);
    const vehicleIcon = getVehicleIcon(section.vehicle_type);
    const statusColor = section.availableSlots > 0 ? '#28a745' : '#dc3545';
    const statusText = section.availableSlots > 0 ? 'Available' : 'Full';

    return `
        <div class="section-card" style="background: white; border: 2px solid #e9ecef; border-radius: 16px; padding: 24px; transition: all 0.3s ease; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;" 
             onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'; this.style.borderColor='#800000';" 
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'; this.style.borderColor='#e9ecef';" 
             onclick="selectSection('${section.section_name}', ${selectRows}, ${selectCols})">
            
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: linear-gradient(135deg, #800000, #600000); color: white; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="${vehicleIcon}"></i>
                    </div>
                    <div>
                        <div style="font-size: 1.3rem; font-weight: 700; color: #2c3e50; margin-bottom: 4px;">Section ${section.section_name}</div>
                        <div style="font-size: 0.9rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px;">${section.vehicle_type} Parking</div>
                    </div>
                </div>
                <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 8px 16px; border-radius: 25px; font-size: 0.85rem; font-weight: 600; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);">
                    Floor ${section.floor}
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div style="text-align: center; padding: 16px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 12px; border: 1px solid #e9ecef;">
                    <div style="font-size: 1.4rem; font-weight: 700; color: #2c3e50; margin-bottom: 4px;">${section.rows}×${section.columns}</div>
                    <div style="font-size: 0.8rem; color: #6c757d; font-weight: 600; text-transform: uppercase;">Dimensions</div>
                </div>
                <div style="text-align: center; padding: 16px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 12px; border: 1px solid #e9ecef;">
                    <div style="font-size: 1.4rem; font-weight: 700; color: #2c3e50; margin-bottom: 4px;">${section.totalSlots}</div>
                    <div style="font-size: 0.8rem; color: #6c757d; font-weight: 600; text-transform: uppercase;">Total Slots</div>
                </div>
                <div style="text-align: center; padding: 16px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 12px; border: 1px solid #e9ecef;">
                    <div style="font-size: 1.4rem; font-weight: 700; color: ${statusColor}; margin-bottom: 4px;">${section.availableSlots}</div>
                    <div style="font-size: 0.8rem; color: #6c757d; font-weight: 600; text-transform: uppercase;">Available</div>
                </div>
                <div style="text-align: center; padding: 16px; background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 12px; border: 1px solid #e9ecef;">
                    <div style="font-size: 1.4rem; font-weight: 700; color: #2c3e50; margin-bottom: 4px;">${statusText}</div>
                    <div style="font-size: 0.8rem; color: #6c757d; font-weight: 600; text-transform: uppercase;">Status</div>
                </div>
            </div>
            
            <!-- Layout Preview -->
            <div style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #e9ecef;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 15px;">
                    <i class="fas fa-th" style="color: #800000; font-size: 16px;"></i>
                    <p style="margin: 0; font-weight: 700; color: #2c3e50; font-size: 14px;">Layout Preview</p>
                </div>
                <div style="display: inline-grid; gap: 3px; margin: 10px 0; grid-template-columns: repeat(${section.columns}, 1fr);">
                    ${visualGrid}
                </div>
            </div>
            
            <!-- Hover Effect -->
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(128, 0, 0, 0.1), rgba(96, 0, 0, 0.1)); opacity: 0; transition: opacity 0.3s ease; pointer-events: none;"></div>
        </div>
    `;
}

// Create preview grid for section button - Updated to use SVG parking slots
function createPreviewGrid(rows, cols, sectionName = '', isCapacityOnly = false) {
    let grid = '';

    if (isCapacityOnly) {
        // Find the actual section data to get real capacity
        const sectionData = sections.find(s => s.section_name === sectionName);
        const actualCapacity = sectionData?.capacity || 0;

        // For capacity-only sections, show a single merged cell with section name only
        grid = `
            <div class="preview-cell" style="grid-column: 1 / -1; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #333; font-weight: bold; font-size: 10px;">
                ${sectionName}
            </div>
        `;
    } else {
        // Regular slot-based sections - show individual slots
        const totalCells = rows * cols;

        for (let i = 0; i < totalCells; i++) {
            // Slot number should be sequential: 1, 2, 3, 4, 5...
            const slotNumber = i + 1;
            // Use provided section name, otherwise try rotationPreviewData, or just show number
            let displaySectionName = sectionName || rotationPreviewData?.type || '';
            // Extract section name if it's an instance ID (e.g., "A_1234567890" -> "A")
            if (displaySectionName.includes('_')) {
                displaySectionName = displaySectionName.split('_')[0];
            }
            const smallParkingSVG = getElementSVG('section', null, null, slotNumber, displaySectionName).replace('width="100%" height="100%"', 'width="20" height="20"');
            grid += `<div class="preview-cell">${smallParkingSVG}</div>`;
        }
    }

    return grid;
}

// Create visual grid for test section
function createVisualGrid(section) {
    let grid = '';
    const totalSlots = section.rows * section.columns;

    for (let i = 0; i < totalSlots; i++) {
        const isOccupied = i < section.occupiedSlots;
        const slotNumber = i + 1;

        grid += `
            <div style="width: 20px; height: 20px; background: ${isOccupied ? '#ff6b6b' : '#51cf66'}; border-radius: 3px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; color: white;">
                ${slotNumber}
            </div>
        `;
    }

    return grid;
}


// Show no sections message
function showNoSections() {
    const container = document.getElementById('sectionsContainer');
    container.innerHTML = `
        <div class="no-sections-message">
            <i class="fas fa-info-circle"></i>
            <p>No sections found for this floor</p>
        </div>
    `;
}

// Reset interface
function resetInterface() {
    document.getElementById('floorSelect').disabled = true;
    document.getElementById('floorSelect').innerHTML = '<option value="">Select area first</option>';
    showNoSections();
}

// Select section for designer
function selectSection(sectionId, rows, cols) {

    // Check if this section type is already placed
    const existingSection = Array.from(placedSections.values()).find(s => s.type === sectionId);
    if (existingSection) {
        showError(`Section type "${sectionId}" is already placed. Only one instance allowed per section type.`, 'Section In Use');
        return;
    }

    // Clear any previous section selection
    clearSectionSelection();

    // Remove active from all section buttons
    document.querySelectorAll('.section-btn').forEach(btn => btn.classList.remove('active'));
    const selectedBtn = document.querySelector(`[data-section="${sectionId}"]`);
    if (selectedBtn) {
        selectedBtn.classList.add('active');
    }

    selectedSection = sectionId;

    // Find the full section data from the sections array
    const fullSectionData = sections.find(s => s.section_name === sectionId);

    // Store the complete section data for placement
    const sectionDataForPlacement = {
        rows: rows,
        cols: cols,
        section_mode: fullSectionData?.section_mode || 'slot_based',
        capacity: fullSectionData?.capacity || (rows * cols),
        grid_width: fullSectionData?.grid_width || cols,
        vehicle_type: fullSectionData?.vehicle_type || 'car'
    };

    selectedElement = null;
    isPlacingSection = true;
    currentPlacingSection = sectionId;
    currentPlacingData = sectionDataForPlacement;

    // Remove active from element buttons
    document.querySelectorAll('.element-btn').forEach(btn => btn.classList.remove('active'));
    prepareRotationControls(null);

    // Show cancel button
    const sectionControls = document.getElementById('sectionControls');
    if (sectionControls) {
        sectionControls.style.display = 'block';
    }

}

// Clear section selection
function clearSectionSelection() {
    selectedSection = null;
    selectedSectionData = null;
    isPlacingSection = false;
    currentPlacingSection = null;
    currentPlacingData = null;

    // Remove active from all sections
    document.querySelectorAll('.section-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = '';
        btn.style.color = '';
        btn.style.transform = '';
        btn.style.boxShadow = '';
    });

    // Hide cancel button
    const sectionControls = document.getElementById('sectionControls');
    if (sectionControls) {
        sectionControls.style.display = 'none';
    }

}

function focusAreaFloorSelection(areaSelect, floorSelect) {
    const modal = document.getElementById('parkingLayoutDesignerModal');
    const sidebar = modal ? modal.querySelector('.parking-designer-sidebar') : null;
    const modalBody = modal ? modal.querySelector('.parking-modal-body') : null;
    const section = modal ? modal.querySelector('.area-floor-section') : null;

    if (sidebar && typeof sidebar.scrollTo === 'function') {
        sidebar.scrollTo({ top: 0, behavior: 'smooth' });
    }
    if (modalBody && typeof modalBody.scrollTo === 'function') {
        modalBody.scrollTo({ top: 0, behavior: 'smooth' });
    }
    if (section && typeof section.scrollIntoView === 'function') {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    const missingArea = !areaSelect || !areaSelect.value;
    const missingFloor = !floorSelect || !floorSelect.value;
    const targetInput = missingArea ? areaSelect : (missingFloor ? floorSelect : null);

    if (targetInput && typeof targetInput.focus === 'function') {
        setTimeout(() => {
            try {
                targetInput.focus({ preventScroll: true });
            } catch (error) {
                targetInput.focus();
            }
        }, 180);
    }
}


// Select element
function selectElement(type) {

    // Check if area and floor are selected
    const areaSelect = document.getElementById('areaSelect');
    const floorSelect = document.getElementById('floorSelect');

    if (!areaSelect.value || !floorSelect.value) {
        // Show warning and highlight required fields
        areaSelect.style.borderColor = '#dc3545';
        areaSelect.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.2)';

        floorSelect.style.borderColor = '#dc3545';
        floorSelect.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.2)';

        showError('Please select an area and floor first', 'Selection Required');

        // Add warning labels if they don't exist
        const areaLabel = areaSelect.previousElementSibling;
        const floorLabel = floorSelect.previousElementSibling;
        if (areaLabel && !areaLabel.classList.contains('required-warning')) {
            areaLabel.classList.add('required-warning');
            areaLabel.style.color = '#dc3545';
            areaLabel.style.fontWeight = '600';
        }
        if (floorLabel && !floorLabel.classList.contains('required-warning')) {
            floorLabel.classList.add('required-warning');
            floorLabel.style.color = '#dc3545';
            floorLabel.style.fontWeight = '600';
        }

        focusAreaFloorSelection(areaSelect, floorSelect);
        return;
    }

    // Remove warning styling when valid
    areaSelect.style.borderColor = '';
    areaSelect.style.boxShadow = '';
    floorSelect.style.borderColor = '';
    floorSelect.style.boxShadow = '';

    // Remove warning labels
    const areaLabel = areaSelect.previousElementSibling;
    const floorLabel = floorSelect.previousElementSibling;
    if (areaLabel && areaLabel.classList.contains('required-warning')) {
        areaLabel.classList.remove('required-warning');
        areaLabel.style.color = '';
        areaLabel.style.fontWeight = '';
    }
    if (floorLabel && floorLabel.classList.contains('required-warning')) {
        floorLabel.classList.remove('required-warning');
        floorLabel.style.color = '';
        floorLabel.style.fontWeight = '';
    }

    // Toggle active state - if already selected, deselect
    const button = document.querySelector(`[data-type="${type}"]`);
    const isCurrentlyActive = button.classList.contains('active');

    // Remove active from all element buttons
    document.querySelectorAll('.element-btn').forEach(btn => btn.classList.remove('active'));

    if (!isCurrentlyActive) {
        // Select element
        button.classList.add('active');
        selectedElement = type;
        const storedDirection = elementOrientationState[type];
        if (storedDirection) {
            selectedElementDirection = storedDirection;
        }
    } else {
        // Deselect element
        selectedElement = null;
        selectedElementDirection = 'horizontal';
    }

    // Clear any element currently selected on the grid when picking a new tool
    document.querySelectorAll('.grid-cell.selected').forEach(c => c.classList.remove('selected'));
    selectedCell = null;

    prepareRotationControls(selectedElement);

    selectedSection = null;
    selectedSectionData = null;

    // Remove active from section buttons
    document.querySelectorAll('.section-btn').forEach(btn => btn.classList.remove('active'));
}

// Set element direction
function setElementDirection(direction) {
    selectedElementDirection = direction;
    updateRotationStatus();
    updateRotationButtonsState();
}

function prepareRotationControls(elementType) {
    const controls = document.getElementById('elementRotationControls');
    if (!controls) return;

    if (!elementType || elementType === 'clear') {
        controls.style.display = 'none';
        currentDirectionOptions = [];
        selectedElementDirection = 'none';
        updateRotationStatus();
        updateRotationButtonsState();
        return;
    }

    currentDirectionOptions = getDirectionOptionsForType(elementType);
    if (!currentDirectionOptions.includes(selectedElementDirection)) {
        selectedElementDirection = currentDirectionOptions.length > 0 ? currentDirectionOptions[0] : 'none';
    }

    controls.style.display = currentDirectionOptions.length === 0 ? 'none' : 'block';
    updateRotationButtonsState();
    updateRotationStatus();
}

function getDirectionOptionsForType(elementType) {
    return window.directionOptionsMap[elementType] ? [...window.directionOptionsMap[elementType]] : ['right'];
}

function rotateSelectedElement(step) {
    if (typeof step !== 'number') step = 1;

    let targetCell = null;
    if (selectedCell && selectedCell.dataset.elementType) {
        targetCell = selectedCell;
    } else {
        const fallbackCell = document.querySelector('.grid-cell.selected[data-element-type]');
        if (fallbackCell) {
            targetCell = fallbackCell;
            selectedCell = fallbackCell;
        }
    }

    if (targetCell) {
        const cellType = targetCell.dataset.elementType;
        const options = getDirectionOptionsForType(cellType);
        if (options.length > 0) {
            const current = targetCell.dataset.elementDirection || options[0];
            const idx = options.indexOf(current);
            const safeIdx = idx === -1 ? 0 : idx;
            const nextDir = options[(safeIdx + step + options.length) % options.length];
            applyDirectionToCell(targetCell, nextDir);
            selectedElement = cellType;
            selectedElementDirection = nextDir;
            elementOrientationState[cellType] = nextDir;
            targetCell.classList.add('rotation-flash');
            setTimeout(() => targetCell.classList.remove('rotation-flash'), 180);
            updateSaveButtonAppearance();
            updateRotationButtonsState();
            updateRotationStatus();
            return;
        }
    }

    if (selectedElement) {
        if (!currentDirectionOptions || currentDirectionOptions.length === 0) {
            currentDirectionOptions = getDirectionOptionsForType(selectedElement);
        }
        if (currentDirectionOptions.length > 0) {
            const currentIndex = currentDirectionOptions.indexOf(selectedElementDirection);
            const safeIndex = currentIndex === -1 ? 0 : currentIndex;
            const nextIndex = (safeIndex + step + currentDirectionOptions.length) % currentDirectionOptions.length;
            selectedElementDirection = currentDirectionOptions[nextIndex];
            elementOrientationState[selectedElement] = selectedElementDirection;
        }
    }

    updateRotationButtonsState();
    updateRotationStatus();
}

function applyDirectionToCell(cell, direction) {
    const type = cell.dataset.elementType;
    if (!type) return;
    cell.dataset.elementDirection = direction;
    const svgContent = getElementSVG(type, direction);
    if (svgContent) {
        cell.innerHTML = svgContent;
    }
    const row = parseInt(cell.dataset.row, 10);
    const col = parseInt(cell.dataset.col, 10);
    const position = `${row},${col}`;
    if (layoutData[position]) {
        layoutData[position].direction = direction;
    }
}

function updateRotationStatus() {
    const statusElement = document.getElementById('rotationStatus');
    if (!statusElement) return;
    const label = window.directionLabels[selectedElementDirection] || selectedElementDirection || 'Horizontal';
    statusElement.textContent = label;
}

function updateRotationButtonsState() {
    const controls = document.getElementById('elementRotationControls');
    if (!controls) return;
    const rotateButtons = controls.querySelectorAll('.rotation-btn');
    const disabled = !currentDirectionOptions || currentDirectionOptions.length <= 1;
    rotateButtons.forEach(btn => {
        btn.disabled = disabled;
        btn.style.opacity = disabled ? '0.5' : '1';
        btn.style.cursor = disabled ? 'not-allowed' : 'pointer';
    });
}

// Open layout designer
async function openLayoutDesigner() {
    const modal = document.getElementById('parkingLayoutDesignerModal');
    if (!modal) {
        showError('Parking layout designer modal not found!', 'Designer Not Found');
        return;
    }

    // Prevent body scrolling - store scroll position
    const scrollY = window.scrollY;
    const originalPaddingRight = window.getComputedStyle(document.body).paddingRight;

    // Set CSS variable for use in CSS
    document.documentElement.style.setProperty('--original-padding-right', originalPaddingRight);

    document.body.classList.add('layout-designer-modal-open');
    document.documentElement.classList.add('layout-designer-modal-open');
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.paddingRight = originalPaddingRight; // Preserve original padding
    document.body.style.marginRight = '0'; // No margin shift
    document.documentElement.style.overflow = 'hidden';

    // Store values for restoration
    window.layoutDesignerScrollY = scrollY;
    window.layoutDesignerOriginalPadding = originalPaddingRight;

    // Prevent Bootstrap/jQuery from adding padding when nested modals open
    if (typeof $ !== 'undefined') {
        $(document).off('show.bs.modal.layout-designer-fix');
        $(document).on('show.bs.modal.layout-designer-fix', '.modal', function () {
            // Immediately remove any padding Bootstrap tries to add
            setTimeout(() => {
                if (document.body.classList.contains('layout-designer-modal-open')) {
                    document.body.style.paddingRight = originalPaddingRight;
                    document.body.style.marginRight = '0';
                }
            }, 0);
        });
    }

    // Override the !important rule by setting style attribute
    modal.setAttribute('style', 'display: block !important; position: fixed !important; z-index: 9999 !important; left: 0 !important; top: 0 !important; width: 100% !important; height: 100% !important; background-color: rgba(0,0,0,0.5) !important;');

    // Clean up any existing state and reinitialize
    cleanupLayoutDesignerState();

    // Initialize the layout designer (loads areas and generates grid)
    await initLayoutDesigner();

    // Restore visual state of dropdowns after they are populated
    restoreFilterVisualState();
}

// Make openLayoutDesigner globally accessible
window.openLayoutDesigner = openLayoutDesigner;

// Close layout designer
async function closeParkingLayoutDesigner() {
    // Check for unsaved changes before closing (unless we just saved)
    if (window.layoutDesignerSaved !== true && !(await confirmUnsavedChanges('close the designer', { requireAny: true }))) {
        return;
    }

    const modal = document.getElementById('parkingLayoutDesignerModal');
    if (modal) {
        modal.setAttribute('style', 'display: none !important;');
        // Clear any selections
        selectedElement = null;
        selectedSection = null;
        selectedSectionData = null;
        selectedCell = null;
        // Reset saved flag
        window.layoutDesignerSaved = false;
    }

    // Restore body scrolling
    const scrollY = window.layoutDesignerScrollY || 0;
    const originalPadding = window.layoutDesignerOriginalPadding || '';

    document.body.classList.remove('layout-designer-modal-open');
    document.documentElement.classList.remove('layout-designer-modal-open');
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
    document.body.style.top = '';
    document.body.style.paddingRight = originalPadding;
    document.body.style.marginRight = '';
    document.documentElement.style.overflow = '';

    // Restore scroll position
    if (scrollY) {
        window.scrollTo(0, scrollY);
    }

    // Remove event handler
    if (typeof $ !== 'undefined') {
        $(document).off('show.bs.modal.layout-designer-fix');
    }

    // Clear stored values and CSS variable
    window.layoutDesignerScrollY = null;
    window.layoutDesignerOriginalPadding = null;
    document.documentElement.style.removeProperty('--original-padding-right');
}

// Initialize layout designer
async function initLayoutDesigner() {
    // Load areas first to populate the dropdowns
    await loadAreas();

    // Generate the grid
    generateGrid();
}

// Generate the grid
function generateGrid() {
    const grid = document.getElementById('layout-grid');
    if (!grid) {
        console.error('Layout grid not found!');
        return;
    }

    // Get current grid size or default to 8x8 (smaller)
    const currentRows = parseInt(grid.dataset.rows) || 8;
    const currentCols = parseInt(grid.dataset.cols) || 8;

    // Set grid template - use CSS variables for sizing
    grid.style.gridTemplateColumns = `repeat(${currentCols}, var(--grid-cell-size))`;
    grid.style.gridTemplateRows = `repeat(${currentRows}, var(--grid-cell-size))`;
    grid.style.gap = '0px'; // NO GAP for seamless connection
    grid.style.backgroundColor = '#f8f9fa'; // Light background instead of grid

    grid.innerHTML = '';

    for (let row = 0; row < currentRows; row++) {
        for (let col = 0; col < currentCols; col++) {
            const cell = document.createElement('div');
            cell.className = 'grid-cell';
            cell.style.border = 'none'; // Remove borders for seamless connection
            cell.style.backgroundColor = '#ffffff'; // White background like when area/floor is selected
            cell.style.width = 'var(--grid-cell-size)';
            cell.style.height = 'var(--grid-cell-size)';
            cell.style.transition = 'background-color 0.2s ease'; // Smooth hover effect
            cell.style.margin = '0'; // No margin for seamless connection
            cell.style.padding = '0'; // No padding for seamless connection
            cell.dataset.row = row;
            cell.dataset.col = col;

            cell.addEventListener('click', (e) => handleCellClick(cell, row, col, e));
            cell.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                handleCellMouseDown(cell, row, col, e);
            });
            cell.addEventListener('mouseup', (e) => handleCellMouseUp(cell, row, col, e));
            cell.addEventListener('mouseover', (e) => handleCellMouseOver(cell, row, col, e));
            cell.addEventListener('mouseout', (e) => handleCellMouseOut(cell, row, col, e));

            grid.appendChild(cell);
        }
    }

    // Update grid size data attributes
    grid.dataset.rows = currentRows;
    grid.dataset.cols = currentCols;

    // Update expand buttons
    updateExpandButtons();
}

// Expand grid in specified direction
function expandGrid(direction) {
    const grid = document.getElementById('layout-grid');
    if (!grid) return;

    const currentRows = parseInt(grid.dataset.rows) || 8;
    const currentCols = parseInt(grid.dataset.cols) || 8;

    let newRows = currentRows;
    let newCols = currentCols;

    if (direction === 'both-increase' && currentCols < 200 && currentRows < 200) {
        newCols = currentCols + 1;
        newRows = currentRows + 1;
    } else if (direction === 'both-decrease' && currentCols > 4 && currentRows > 4) {
        newCols = currentCols - 1;
        newRows = currentRows - 1;
    } else if (direction === 'right' && currentCols < 200) {
        newCols = currentCols + 1;
    } else if (direction === 'bottom' && currentRows < 200) {
        newRows = currentRows + 1;
    } else if (direction === 'left' && currentCols > 4) {
        newCols = currentCols - 1;
    } else if (direction === 'up' && currentRows > 4) {
        newRows = currentRows - 1;
    } else {
        return; // Already at limit or invalid direction
    }

    // Update grid size
    grid.dataset.rows = newRows;
    grid.dataset.cols = newCols;

    // Regenerate grid
    generateGrid();

    // Re-render existing content after grid is ready (small delay to ensure DOM is ready)
    setTimeout(() => {
        renderExistingContent();
    }, 10);

    // Update expand buttons visibility
    updateExpandButtons();
}

// Update expand buttons visibility and state
function updateExpandButtons() {
    const grid = document.getElementById('layout-grid');
    if (!grid) return;

    const currentRows = parseInt(grid.dataset.rows) || 8;
    const currentCols = parseInt(grid.dataset.cols) || 8;

    // Update both-increase and both-decrease buttons
    const increaseGrid = document.getElementById('increaseGrid');
    const decreaseGrid = document.getElementById('decreaseGrid');

    if (increaseGrid) {
        const canIncrease = currentCols < 200 && currentRows < 200;
        increaseGrid.style.display = canIncrease ? 'flex' : 'none';
        increaseGrid.title = canIncrease ? 'Increase Grid Size' : 'Maximum size reached (200x200)';
    }

    if (decreaseGrid) {
        const canDecrease = currentCols > 4 && currentRows > 4;
        decreaseGrid.style.display = canDecrease ? 'flex' : 'none';
        decreaseGrid.title = canDecrease ? 'Decrease Grid Size' : 'Minimum size reached (4x4)';
    }
}

// Re-render existing content after grid regeneration
function renderExistingContent() {
    // Re-render elements
    Object.keys(layoutData).forEach(position => {
        const [row, col] = position.split(',').map(Number);
        const elementData = layoutData[position];
        const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);

        if (cell && elementData) {
            cell.classList.add(elementData.type);
            cell.dataset.elementType = elementData.type;
            cell.dataset.elementDirection = elementData.direction;

            const svgContent = getElementSVG(elementData.type, elementData.direction);
            if (svgContent) {
                cell.innerHTML = svgContent;
            }
        }
    });

    // Re-render sections
    Array.from(placedSections.entries()).forEach(([sectionId, sectionData]) => {
        renderSection(sectionId, sectionData);
    });
}

// Check if element type is an obstacle
function isObstacle(type) {
    return ['wall', 'pillar', 'tree', 'gate'].includes(type);
}

// Check if placement would cause collision with obstacles
function wouldOverlapObstacles(selectedType, targetRow, targetCol) {
    const targetPos = `${targetRow},${targetCol}`;
    const existingType = layoutData[targetPos]?.type;

    if (!existingType) return false;

    const selectedIsObstacle = isObstacle(selectedType);
    const existingIsObstacle = isObstacle(existingType);

    // Obstacles cannot overlap other obstacles
    if (selectedIsObstacle && existingIsObstacle) return true;

    // Roads cannot overlap walls/pillars
    if (['road', 'intersection', 'entrance', 'exit', 'oneway', 'two-way', 'entry-exit'].includes(selectedType) &&
        ['wall', 'pillar'].includes(existingType)) return true;

    // Walls/pillars cannot overlap roads
    if (['wall', 'pillar'].includes(selectedType) &&
        ['road', 'intersection', 'entrance', 'exit', 'oneway', 'two-way', 'entry-exit'].includes(existingType)) return true;

    return false;
}

// Check if a target cell is occupied by any section or element
function getCellDropConflict(targetCell, dragStartCell = null) {
    if (!targetCell) {
        return 'Target cell is invalid';
    }

    // Allow dropping back to original source cell
    if (dragStartCell && targetCell === dragStartCell) {
        return null;
    }

    if (targetCell.dataset.section) {
        return 'Target cell contains a parking section';
    }

    if (targetCell.dataset.elementType) {
        return `Target cell contains an existing element (${targetCell.dataset.elementType})`;
    }

    return null;
}

// Handle cell click
function handleCellClick(cell, row, col, event) {
    event.preventDefault();

    // Clear previous selection
    document.querySelectorAll('.grid-cell.selected').forEach(c => c.classList.remove('selected'));
    cell.classList.add('selected');
    selectedCell = cell;

    if (cell.dataset.section) {
        // Clicked on an existing section - select it for editing
        selectSectionForEdit(cell, row, col);
        return;
    }

    if (cell.dataset.elementType) {
        // Clicked on an existing element - select it for editing direction
        selectElementForEdit(cell, row, col);
        return;
    }

    if (isPlacingSection && currentPlacingData) {
        // Store clicked position for orientation placement
        window.lastClickedPosition = { row: row, col: col };
        placeEntireSection(cell, row, col);
    } else if (selectedElement) {
        // Check for obstacle collision before placing
        if (wouldOverlapObstacles(selectedElement, row, col)) {
            const obstacleNames = {
                'wall': 'Wall',
                'pillar': 'Pillar',
                'tree': 'Tree',
                'gate': 'Gate'
            };
            const targetPos = `${row},${col}`;
            const existingType = layoutData[targetPos]?.type;
            const message = `Cannot place ${obstacleNames[selectedElement] || selectedElement} here - would overlap with ${obstacleNames[existingType] || existingType}`;
            showError(message, 'Placement Blocked');
            return;
        }
        placeElement(cell, row, col);
    } else {
        // If clicking on empty cell without selection, clear any active selections
        clearAllSelections();
    }
}

function selectElementForEdit(cell, row, col) {
    const elementType = cell.dataset.elementType;
    const currentDirection = cell.dataset.elementDirection || 'horizontal';

    // Deselect previously selected cells
    document.querySelectorAll('.grid-cell.selected').forEach(c => c.classList.remove('selected'));
    cell.classList.add('selected');
    selectedCell = cell;

    if (selectedElement === 'clear') {
        removeElement(cell, row, col);
        return;
    }

    selectedCell = cell;
    selectedElement = elementType;
    selectedElementDirection = currentDirection;
    elementOrientationState[elementType] = currentDirection;
    prepareRotationControls(elementType);

    // Update UI to show element is selected
    document.querySelectorAll('.element-btn').forEach(btn => btn.classList.remove('active'));
    const elementButton = document.querySelector(`[data-type="${elementType}"]`);
    if (elementButton) {
        elementButton.classList.add('active');
    }

    document.getElementById('elementRotationControls').style.display = currentDirectionOptions.length === 0 ? 'none' : 'block';
    updateRotationStatus();

    // Clear section selections
    document.querySelectorAll('.section-btn').forEach(btn => btn.classList.remove('active'));
    selectedSection = null;
    selectedSectionData = null;
}

// Remove element from cell
function removeElement(cell, row, col) {
    // Clear visual
    cell.innerHTML = '';
    cell.className = 'grid-cell';
    delete cell.dataset.elementType;
    delete cell.dataset.elementDirection;

    // Remove from layout data
    const position = `${row},${col}`;
    if (layoutData[position]) {
        delete layoutData[position];
    }

    // Update save button
    updateSaveButtonAppearance();
}

// Select section for editing
function selectSectionForEdit(cell, row, col) {
    const sectionId = cell.dataset.section;
    const sectionData = placedSections.get(sectionId);

    console.log('ðŸ" selectSectionForEdit called with:', { sectionId, sectionData, row, col });

    if (!sectionData) {
        console.log('âŒ No section data found for:', sectionId);
        return;
    }

    // Clear any previous section edit selection
    document.querySelectorAll('.grid-cell.section-selected').forEach(c => c.classList.remove('section-selected'));
    console.log('ðŸ" Cleared previous section selections');

    // Highlight all cells of this section
    highlightSectionCells(sectionId);

    selectedSectionForEdit = sectionId;
    originalSectionData = { ...sectionData };
    rotationPreviewData = { ...sectionData };
    console.log('Section selected for editing - Original data:', originalSectionData);

    // Show edit controls
    const editControls = document.getElementById('sectionEditControls');
    const sectionInfo = document.getElementById('selectedSectionInfo');

    if (editControls) {
        editControls.style.display = 'block';
        console.log('Edit controls shown');
    } else {
        console.error('Edit controls element not found!');
    }

    if (sectionInfo) {
        sectionInfo.innerHTML = `
            <strong>Section:</strong> ${sectionData.type}<br>
            <strong>Size:</strong> ${sectionData.rows}Ã—${sectionData.cols}<br>
            <strong>Position:</strong> (${sectionData.startRow}, ${sectionData.startCol})
        `;
    }
}

// Clear all selections
function clearAllSelections() {
    clearSectionSelection();
    selectedElement = null;
    selectedCell = null;

    // Remove active from element buttons
    document.querySelectorAll('.element-btn').forEach(btn => btn.classList.remove('active'));

    // Hide element rotation controls
    document.getElementById('elementRotationControls').style.display = 'none';

    console.log('All selections cleared');
}

// Update section button indicators
function updateSectionIndicators() {
    // Get all placed section types
    const placedTypes = Array.from(placedSections.values()).map(s => s.type);

    // Update all section buttons
    document.querySelectorAll('.section-btn').forEach(btn => {
        const sectionType = btn.dataset.section;
        if (placedTypes.includes(sectionType)) {
            // Mark as placed (red indicator)
            btn.classList.add('placed');
            btn.style.background = '#dc3545';
            btn.style.color = 'white';
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';

            // Add placed indicator
            let indicator = btn.querySelector('.placed-indicator');
            if (!indicator) {
                indicator = document.createElement('span');
                indicator.className = 'placed-indicator';
                indicator.innerHTML = ' âœ"';
                indicator.style.fontWeight = 'bold';
                btn.appendChild(indicator);
            }
        } else {
            // Mark as available
            btn.classList.remove('placed');
            btn.style.background = '';
            btn.style.color = '';
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';

            // Remove placed indicator
            const indicator = btn.querySelector('.placed-indicator');
            if (indicator) {
                indicator.remove();
            }
        }
    });
}

// Highlight section cells
function highlightSectionCells(sectionId) {
    console.log('ðŸ" Highlighting section cells for:', sectionId);
    const cells = document.querySelectorAll(`[data-section="${sectionId}"]`);
    console.log('ðŸ" Found', cells.length, 'cells to highlight');

    cells.forEach(cell => {
        cell.classList.add('section-selected');
        console.log('ðŸ" Added section-selected class to cell:', cell);
    });
}

// Rotation functionality removed

// Rotation functionality removed

// SIMPLE ORIENTATION CHOICE - NO ROTATION
function showOrientationChoice() {
    if (!selectedSectionForEdit || !rotationPreviewData) return;

    // Show orientation choice modal
    const orientationModal = document.createElement('div');
    orientationModal.id = 'orientationModal';
    orientationModal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); z-index: 10000; display: flex; 
            align-items: center; justify-content: center;
        `;

    orientationModal.innerHTML = `
            <div style="background: white; padding: 30px; border-radius: 10px; text-align: center; max-width: 400px;">
                <h3>Choose Section Orientation</h3>
                <p>How do you want to place this section?</p>
                <div style="display: flex; gap: 20px; justify-content: center; margin: 20px 0;">
                    <button onclick="placeSectionHorizontal()" style="padding: 15px 25px; background: #800000; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
                        <i class="fas fa-arrows-alt-h"></i><br>Horizontal
                    </button>
                    <button onclick="placeSectionVertical()" style="padding: 15px 25px; background: #383838; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
                        <i class="fas fa-arrows-alt-v"></i><br>Vertical
                    </button>
                </div>
                <button onclick="cancelOrientationChoice()" style="padding: 10px 20px; background: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        `;

    document.body.appendChild(orientationModal);
}

// Place section horizontally
function placeSectionHorizontal() { // Updated to use SVG parking slots - Fixed background issue
    if (!selectedSectionForEdit || !rotationPreviewData) return;

    // Get section name from rotationPreviewData.type (remove instance ID if present)
    let sectionName = rotationPreviewData.type || '';
    if (sectionName.includes('_')) {
        sectionName = sectionName.split('_')[0];
    }

    // Get section data to check if it's capacity-only
    const originalSection = sections.find(s => s.section_name === sectionName);

    // Use the new detection function
    const isCapacityOnly = shouldBeCapacityOnly(originalSection?.vehicle_type, originalSection?.section_mode);

    // Define gridWidth for both capacity-only and regular sections
    const gridWidth = parseInt(originalSection?.grid_width || rotationPreviewData.cols);

    let startRow = rotationPreviewData.startRow;
    let startCol = rotationPreviewData.startCol;

    // For capacity_only sections, check overlap and fit NOW with correct dimensions
    if (isCapacityOnly) {
        // Get current grid dimensions
        const grid = document.getElementById('layout-grid');
        const currentCols = grid ? parseInt(grid.dataset.cols) || 8 : 8;

        // Check if section fits horizontally (1 row × gridWidth columns)
        if (startCol + gridWidth > currentCols) {
            showError(`Section does not fit horizontally at this position (Grid is ${currentCols} columns wide)`, 'Section Placement');
            cancelOrientationChoice();
            return;
        }

        // Check for overlapping with existing sections or elements
        for (let c = 0; c < gridWidth; c++) {
            const targetCol = startCol + c;
            const targetCell = document.querySelector(`[data-row="${startRow}"][data-col="${targetCol}"]`);

            if (targetCell) {
                // Check for sections
                if (targetCell.classList.contains('section') || targetCell.dataset.section) {
                    showError('Cannot place section here - overlaps with existing section', 'Section Placement');
                    cancelOrientationChoice();
                    return;
                }
                // Check for elements
                if (targetCell.dataset.elementType) {
                    showError('Cannot place section here - overlaps with existing element', 'Section Placement');
                    cancelOrientationChoice();
                    return;
                }
            }
        }
    }

    // Clear existing cells
    document.querySelectorAll(`[data-section="${selectedSectionForEdit}"]`).forEach(cell => {
        cell.className = 'grid-cell';
        cell.innerHTML = '';
        cell.removeAttribute('data-section');
        cell.removeAttribute('data-section-type');
        cell.removeAttribute('data-section-row');
        cell.removeAttribute('data-section-col');
        cell.removeAttribute('data-section-rows');
        cell.removeAttribute('data-section-cols');
        cell.style.cssText = '';
    });

    if (isCapacityOnly) {

        for (let c = 0; c < gridWidth; c++) {
            const targetCell = document.querySelector(`[data-row="${startRow}"][data-col="${startCol + c}"]`);

            if (targetCell) {
                targetCell.className = 'grid-cell section section-preview';
                targetCell.dataset.section = selectedSectionForEdit;
                targetCell.dataset.sectionType = rotationPreviewData.type;
                targetCell.dataset.sectionRow = 0;
                targetCell.dataset.sectionCol = c;
                targetCell.dataset.sectionRows = 1;
                targetCell.dataset.sectionCols = gridWidth;
                targetCell.style.backgroundColor = 'transparent';
                targetCell.style.border = '2px solid #ced4da';
                targetCell.style.boxShadow = '0 2px 4px rgba(206, 212, 218, 0.3)';
                targetCell.style.transition = 'all 0.2s ease';

                if (c === 0) {
                    // First cell - show capacity-only info
                    targetCell.innerHTML = `
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 5; pointer-events: none;">
                                <div style="background: white; color: #333; padding: 2px 8px; text-align: center; font-weight: bold; border-radius: 3px; font-size: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    ${sectionName}
                                </div>
                            </div>
                        `;
                    targetCell.classList.add('section-start');
                } else {
                    // Other cells - just show the background
                    targetCell.innerHTML = `
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; z-index: 5; pointer-events: none;"></div>
                        `;
                }
            }
        }
    } else {
        // Regular slot-based section - existing logic
        const rows = rotationPreviewData.rows;
        const cols = rotationPreviewData.cols;

        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const targetCell = document.querySelector(`[data-row="${startRow + r}"][data-col="${startCol + c}"]`);

                if (targetCell) {
                    targetCell.className = 'grid-cell section section-preview';
                    targetCell.dataset.section = selectedSectionForEdit;
                    targetCell.dataset.sectionType = rotationPreviewData.type;
                    targetCell.dataset.sectionRow = r;
                    targetCell.dataset.sectionCol = c;
                    targetCell.dataset.sectionRows = rows;
                    targetCell.dataset.sectionCols = cols;
                    targetCell.style.backgroundColor = 'transparent';
                    targetCell.style.border = '2px solid #45a049';
                    targetCell.style.boxShadow = '0 2px 4px rgba(76, 175, 80, 0.3)';
                    targetCell.style.transition = 'all 0.2s ease';

                    // Slot number should be sequential: 1, 2, 3, 4, 5... for each section
                    const slotNumber = (r * cols) + c + 1;
                    const svgContent = getElementSVG('section', null, null, slotNumber, sectionName);

                    if (r === 0 && c === 0) {
                        // First cell - show section name and info with parking slot SVG
                        targetCell.innerHTML = svgContent + `
                                <div style="position: absolute; top: 2px; left: 2px; background: white; color: #333; padding: 2px 4px; text-align: center; font-weight: bold; border-radius: 3px; font-size: 9px; box-shadow: 0 1px 3px rgba(0,0,0,0.2); z-index: 10; pointer-events: none;">
                                    ${sectionName} →
                                </div>
                                <div style="position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); font-size: 8px; color: white; text-align: center; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3); z-index: 10; pointer-events: none;">
                                    HORIZONTAL
                                </div>
                            `;
                        targetCell.classList.add('section-start');
                    } else {
                        // Other cells - SVG already contains the section name + number (e.g., "C-1")
                        targetCell.innerHTML = svgContent;
                    }
                }
            }
        }
    }

    // Store section data with correct dimensions
    placedSections.set(selectedSectionForEdit, {
        id: selectedSectionForEdit,
        type: rotationPreviewData.type,
        section_name: originalSection?.section_name || rotationPreviewData.type,
        startRow: startRow,
        startCol: startCol,
        rows: isCapacityOnly ? 1 : rotationPreviewData.rows,
        cols: isCapacityOnly ? gridWidth : rotationPreviewData.cols,
        orientation: 'horizontal',
        floor: currentFloor,
        section_mode: originalSection?.section_mode || 'slot_based',
        capacity: originalSection?.capacity || (rotationPreviewData.rows * rotationPreviewData.cols),
        grid_width: originalSection?.grid_width || rotationPreviewData.cols,
        vehicle_type: originalSection?.vehicle_type || 'car'
    });

    // Update designer stats
    updateDesignerStats();


    // Mark as having unsaved changes when placing section
    window.layoutDesignerSaved = false;

    // Clear selection after placement
    clearSectionSelection();

    // Clear the visual selection from the button
    document.querySelectorAll('.section-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = '';
        btn.style.color = '';
        btn.style.transform = '';
        btn.style.boxShadow = '';
    });

    console.log(`Section ${rotationPreviewData.type} placed horizontally as ${selectedSectionForEdit} at (${startRow}, ${startCol})`);
    console.log('Section selection cleared after placement');

    // Update section button indicators
    updateSectionIndicators();

    // Update save button appearance
    updateSaveButtonAppearance();

    // Check if section fits in current grid
    const grid = document.getElementById('layout-grid');
    if (grid) {
        const currentRows = parseInt(grid.dataset.rows) || 8;
        const currentCols = parseInt(grid.dataset.cols) || 8;

        // Define rows and cols for grid boundary check
        const finalRows = isCapacityOnly ? 1 : rotationPreviewData.rows;
        const finalCols = isCapacityOnly ? gridWidth : rotationPreviewData.cols;

        // Debug: Log the values
        console.log('=== GRID VALIDATION DEBUG ===');
        console.log('sectionName:', sectionName);
        console.log('isCapacityOnly:', isCapacityOnly);
        console.log('originalSection:', originalSection);
        console.log('gridWidth:', gridWidth);
        console.log('finalRows:', finalRows);
        console.log('finalCols:', finalCols);
        console.log('startRow:', startRow);
        console.log('startCol:', startCol);
        console.log('currentRows:', currentRows);
        console.log('currentCols:', currentCols);
        console.log('Check: startRow + finalRows =', startRow + finalRows, '>', currentRows, '?');
        console.log('Check: startCol + finalCols =', startCol + finalCols, '>', currentCols, '?');

        // Check if section extends beyond current grid
        const rowCheck = parseInt(startRow) + parseInt(finalRows) - 1;  // Force numeric conversion
        const colCheck = parseInt(startCol) + parseInt(finalCols) - 1;  // Force numeric conversion

        console.log('ACTUAL CHECK VALUES:');
        console.log('startRow type:', typeof startRow, 'value:', startRow);
        console.log('finalRows type:', typeof finalRows, 'value:', finalRows);
        console.log('startCol type:', typeof startCol, 'value:', startCol);
        console.log('finalCols type:', typeof finalCols, 'value:', finalCols);
        console.log('rowCheck:', rowCheck, '>=', currentRows, '?', rowCheck >= currentRows);
        console.log('colCheck:', colCheck, '>=', currentCols, '?', colCheck >= currentCols);

        if (rowCheck >= currentRows || colCheck >= currentCols) {
            showError(`Section extends beyond current grid (${currentRows}x${currentCols}). Use the + buttons to expand the grid.`);
        }
    }

    closeOrientationModal();
}

// Place section vertically
function placeSectionVertical() { // Updated to use SVG parking slots - Fixed background issue
    if (!selectedSectionForEdit || !rotationPreviewData) return;

    // Get cell that was clicked to determine starting position
    let startRow, startCol;

    console.log('=== VERTICAL DEBUG START ===');
    console.log('window.lastClickedPosition:', window.lastClickedPosition);
    console.log('rotationPreviewData:', rotationPreviewData);

    if (window.lastClickedPosition) {
        // Use stored clicked position for new placement
        startRow = window.lastClickedPosition.row;
        startCol = window.lastClickedPosition.col;
        console.log('Using stored clicked position:', startRow, startCol);
        // Clear stored position after using it
        window.lastClickedPosition = null;
    } else {
        // Fallback to rotation preview data
        startRow = rotationPreviewData.startRow;
        startCol = rotationPreviewData.startCol;
        console.log('Using rotation preview position:', startRow, startCol);
    }

    console.log('Final startRow:', startRow, 'startCol:', startCol);

    // Get section name from rotationPreviewData.type (remove instance ID if present)
    let sectionName = rotationPreviewData.type || '';
    if (sectionName.includes('_')) {
        sectionName = sectionName.split('_')[0];
    }

    // Get section data to check if it's capacity-only
    const originalSection = sections.find(s => s.section_name === sectionName);

    // Use the new detection function
    const isCapacityOnly = shouldBeCapacityOnly(originalSection?.vehicle_type, originalSection?.section_mode);

    // Define gridWidth for both capacity-only and regular sections (MUST BE DEFINED EARLY)
    const gridWidth = parseInt(originalSection?.grid_width || rotationPreviewData.cols);

    // For capacity_only sections, check overlap and fit NOW with correct dimensions
    if (isCapacityOnly) {
        // Get current grid dimensions
        const grid = document.getElementById('layout-grid');
        const currentRows = grid ? parseInt(grid.dataset.rows) || 8 : 8;

        // Check if section fits vertically (gridWidth rows × 1 column)
        if (startRow + gridWidth > currentRows) {
            showError(`Section does not fit vertically at this position (Grid is ${currentRows} rows high)`, 'Section Placement');
            cancelOrientationChoice();
            return;
        }

        // Check for overlapping with existing sections or elements
        for (let r = 0; r < gridWidth; r++) {
            const targetRow = startRow + r;
            const targetCol = startCol;
            const targetCell = document.querySelector(`[data-row="${targetRow}"][data-col="${targetCol}"]`);

            if (targetCell) {
                // Check for sections
                if (targetCell.classList.contains('section') || targetCell.dataset.section) {
                    showError('Cannot place section here - overlaps with existing section', 'Section Placement');
                    cancelOrientationChoice();
                    return;
                }
                // Check for elements
                if (targetCell.dataset.elementType) {
                    showError('Cannot place section here - overlaps with existing element', 'Section Placement');
                    cancelOrientationChoice();
                    return;
                }
            }
        }
    }

    // Clear existing cells
    document.querySelectorAll(`[data-section="${selectedSectionForEdit}"]`).forEach(cell => {
        cell.className = 'grid-cell';
        cell.innerHTML = '';
        cell.removeAttribute('data-section');
        cell.removeAttribute('data-section-type');
        cell.removeAttribute('data-section-row');
        cell.removeAttribute('data-section-col');
        cell.removeAttribute('data-section-rows');
        cell.removeAttribute('data-section-cols');
        cell.style.cssText = '';
    });

    if (isCapacityOnly) {

        // VERTICAL: grid_width becomes rows, iterate through rows (not columns)
        for (let r = 0; r < gridWidth; r++) {
            const targetRow = startRow + r;
            const targetCol = startCol;
            const targetCell = document.querySelector(`[data-row="${targetRow}"][data-col="${targetCol}"]`);

            if (targetCell) {
                targetCell.className = 'grid-cell section section-preview';
                targetCell.dataset.section = selectedSectionForEdit;
                targetCell.dataset.sectionType = rotationPreviewData.type;
                targetCell.dataset.sectionRow = r;
                targetCell.dataset.sectionCol = 0;
                targetCell.dataset.sectionRows = gridWidth;
                targetCell.dataset.sectionCols = 1;
                targetCell.style.backgroundColor = 'transparent';
                targetCell.style.border = '2px solid #ced4da';
                targetCell.style.boxShadow = '0 2px 4px rgba(206, 212, 218, 0.3)';
                targetCell.style.transition = 'all 0.2s ease';

                if (r === 0) {
                    // First cell - show capacity-only info
                    targetCell.innerHTML = `
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 5; pointer-events: none;">
                                <div style="background: white; color: #333; padding: 2px 8px; text-align: center; font-weight: bold; border-radius: 3px; font-size: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    ${sectionName}
                                </div>
                            </div>
                        `;
                    targetCell.classList.add('section-start');
                } else {
                    // Other cells - just show the background
                    targetCell.innerHTML = `
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; z-index: 5; pointer-events: none;"></div>
                        `;
                }
            }
        }
    } else {
        // Regular slot-based section - existing logic with vertical orientation
        const originalRows = rotationPreviewData.rows;
        const originalCols = rotationPreviewData.cols;

        // For vertical orientation, swap the dimensions
        const rows = originalCols; // Use original cols as new rows
        const cols = originalRows; // Use original rows as new cols

        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const targetCell = document.querySelector(`[data-row="${startRow + r}"][data-col="${startCol + c}"]`);

                if (targetCell) {
                    targetCell.className = 'grid-cell section section-preview';
                    targetCell.dataset.section = selectedSectionForEdit;
                    targetCell.dataset.sectionType = rotationPreviewData.type;
                    targetCell.dataset.sectionRow = r;
                    targetCell.dataset.sectionCol = c;
                    targetCell.dataset.sectionRows = rows;
                    targetCell.dataset.sectionCols = cols;
                    targetCell.style.backgroundColor = 'transparent';
                    targetCell.style.border = '2px solid #800000';
                    targetCell.style.boxShadow = '0 2px 4px rgba(128, 0, 0, 0.3)';
                    targetCell.style.transition = 'all 0.2s ease';

                    // Slot number should be sequential: 1, 2, 3, 4, 5... for each section
                    const slotNumber = (r * cols) + c + 1;
                    const svgContent = getElementSVG('section', null, null, slotNumber, sectionName);

                    if (r === 0 && c === 0) {
                        // First cell - show section name and info with parking slot SVG
                        targetCell.innerHTML = svgContent + `
                                <div style="position: absolute; top: 2px; left: 2px; background: white; color: #333; padding: 2px 4px; text-align: center; font-weight: bold; border-radius: 3px; font-size: 9px; box-shadow: 0 1px 3px rgba(0,0,0,0.2); z-index: 10; pointer-events: none;">
                                    ${sectionName} ↓
                                </div>
                                <div style="position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); font-size: 8px; color: white; text-align: center; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3); z-index: 10; pointer-events: none;">
                                    VERTICAL
                                </div>
                            `;
                        targetCell.classList.add('section-start');
                    } else {
                        // Other cells - SVG already contains the section name + number (e.g., "C-1")
                        targetCell.innerHTML = svgContent;
                    }
                }
            }
        }
    }

    // Define final dimensions for vertical placement (used for storing + bounds checks)
    const finalRows = parseInt(isCapacityOnly ? gridWidth : (rotationPreviewData.cols));
    const finalCols = parseInt(isCapacityOnly ? 1 : (rotationPreviewData.rows));

    // Store section data with swapped dimensions for vertical
    placedSections.set(selectedSectionForEdit, {
        id: selectedSectionForEdit,
        type: rotationPreviewData.type,
        section_name: originalSection?.section_name || rotationPreviewData.type,
        startRow: startRow,
        startCol: startCol,
        rows: finalRows,
        cols: finalCols,
        orientation: 'vertical',
        floor: currentFloor,
        section_mode: originalSection?.section_mode || 'slot_based',
        capacity: parseInt(originalSection?.capacity || (rotationPreviewData.rows * rotationPreviewData.cols)), // Ensure numeric
        grid_width: parseInt(isCapacityOnly ? 1 : (originalSection?.grid_width || rotationPreviewData.cols)), // Ensure numeric
        vehicle_type: originalSection?.vehicle_type || 'car'
    });

    // Update designer stats
    updateDesignerStats();


    console.log('=== SECTION DATA STORED ===');
    console.log('Stored dimensions:', finalRows, 'x', finalCols);
    console.log('gridWidth:', gridWidth);
    console.log('isCapacityOnly:', isCapacityOnly);
    console.log('Data types - rows:', typeof finalRows, 'cols:', typeof finalCols);

    // Check if section fits in current grid BEFORE placing
    const grid = document.getElementById('layout-grid');
    if (grid) {
        // Get actual grid size from dataset (most reliable)
        const actualRows = parseInt(grid.dataset.rows) || 8;
        const actualCols = parseInt(grid.dataset.cols) || 8;

        console.log('=== GRID BOUNDS CHECK ===');
        console.log('Start position:', startRow, startCol);
        console.log('Actual grid size:', actualRows, 'x', actualCols);
        console.log('gridWidth:', gridWidth);
        console.log('isCapacityOnly:', isCapacityOnly);

        const sectionRows = finalRows;
        const sectionCols = finalCols;

        console.log('Section dimensions:', sectionRows, 'x', sectionCols);
        console.log('End position:', startRow + sectionRows - 1, startCol + sectionCols - 1);
        console.log('Calculation: startRow + sectionRows - 1 =', startRow, '+', sectionRows, '- 1 =', startRow + sectionRows - 1);
        console.log('Calculation: startCol + sectionCols - 1 =', startCol, '+', sectionCols, '- 1 =', startCol + sectionCols - 1);

        // Check if section extends beyond current grid (0-indexed vs 1-indexed fix)
        if ((startRow + sectionRows) > actualRows || (startCol + sectionCols) > actualCols) {
            console.log('BOUNDS CHECK FAILED - Section does not fit');
            showError(`Section extends beyond current grid (${actualRows}x${actualCols}). Use + buttons to expand the grid.`);
            // Don't place if it doesn't fit - clear and return
            document.querySelectorAll(`[data-section="${selectedSectionForEdit}"]`).forEach(cell => {
                cell.className = 'grid-cell';
                cell.innerHTML = '';
                cell.removeAttribute('data-section');
                cell.removeAttribute('data-section-type');
                cell.removeAttribute('data-section-row');
                cell.removeAttribute('data-section-col');
                cell.removeAttribute('data-section-rows');
                cell.removeAttribute('data-section-cols');
                cell.style.cssText = '';
            });
            closeOrientationModal();
            return;
        } else {
            console.log('BOUNDS CHECK PASSED - Section fits!');
        }
    }

    // Mark as having unsaved changes when placing section
    window.layoutDesignerSaved = false;

    // Clear selection after placement
    clearSectionSelection();

    // Clear the visual selection from the button
    document.querySelectorAll('.section-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = '';
        btn.style.color = '';
        btn.style.transform = '';
        btn.style.boxShadow = '';
    });

    console.log(`Section ${rotationPreviewData.type} placed vertically as ${selectedSectionForEdit} at (${startRow}, ${startCol})`);
    console.log('Section selection cleared after placement');

    // Update section button indicators
    updateSectionIndicators();

    // Update save button appearance
    updateSaveButtonAppearance();

    closeOrientationModal();
}

// Close orientation modal
function closeOrientationModal() {
    const modal = document.getElementById('orientationModal');
    if (modal) {
        modal.remove();
    }
}

// Cancel orientation choice
function cancelOrientationChoice() {
    closeOrientationModal();
    // Clear the section selection
    selectedSectionForEdit = null;
    rotationPreviewData = null;
    isPlacingSection = false; // Add this to allow dragging to resume
}

// Save rotation
function saveRotation() {
    if (!selectedSectionForEdit || !rotationPreviewData) return;

    // Update the actual section data
    const sectionData = placedSections.get(selectedSectionForEdit);
    if (sectionData) {
        sectionData.rows = rotationPreviewData.rows;
        sectionData.cols = rotationPreviewData.cols;
        // Rotation functionality removed

        // Re-render with final data
        renderSection(selectedSectionForEdit, sectionData);

        // Update section info
        const sectionInfo = document.getElementById('selectedSectionInfo');
        if (sectionInfo) {
            sectionInfo.innerHTML = `
                <strong>Section:</strong> ${sectionData.type}<br>
                <strong>Size:</strong> ${sectionData.rows}Ã—${sectionData.cols}<br>
                <strong>Position:</strong> (${sectionData.startRow}, ${sectionData.startCol})<br>
                <strong>Status:</strong> Active
            `;
        }

        console.log('Section saved:', selectedSectionForEdit, sectionData.rows, sectionData.cols);
    }

}

// Check for overlapping with obstacles in section placement
function checkSectionOverlap(startRow, startCol, rows, cols, excludeSectionId) {
    for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
            const targetRow = startRow + r;
            const targetCol = startCol + c;
            const targetCell = document.querySelector(`[data-row="${targetRow}"][data-col="${targetCol}"]`);

            if (targetCell) {
                // Check for sections
                if (targetCell.dataset.section && targetCell.dataset.section !== excludeSectionId) {
                    return true;
                }
                // Check for all placed elements (not just obstacles)
                if (targetCell.dataset.elementType) {
                    return true;
                }
            }
        }
    }
    return false;
}

// PROPER RENDER - Same as initial placement
function renderSection(sectionId, sectionData) {
    // Clear existing cells
    document.querySelectorAll(`[data-section="${sectionId}"]`).forEach(cell => {
        cell.className = 'grid-cell';
        cell.innerHTML = '';
        cell.removeAttribute('data-section');
        cell.removeAttribute('data-section-type');
        cell.removeAttribute('data-section-row');
        cell.removeAttribute('data-section-col');
        cell.removeAttribute('data-section-rows');
        cell.removeAttribute('data-section-cols');
        cell.style.cssText = '';
    });

    // Check if this is a capacity-only section
    const isCapacityOnly = sectionData.section_mode === 'capacity_only';

    // Get section name
    let sectionName = sectionData.section_name || sectionData.type || '';
    if (sectionName.includes('_')) {
        sectionName = sectionName.split('_')[0];
    }

    if (isCapacityOnly) {
        // Capacity-only: Create single merged cell
        const startRow = sectionData.startRow;
        const startCol = sectionData.startCol;
        const orientation = sectionData.orientation || 'horizontal';

        // For vertical capacity-only, grid_width was set to 1, so we need to use rows as the actual width
        const actualGridWidth = orientation === 'vertical' ? sectionData.rows : (sectionData.grid_width || sectionData.cols);

        if (orientation === 'vertical') {
            // Vertical orientation: actualGridWidth becomes height, 1 column width
            for (let r = 0; r < actualGridWidth; r++) {
                const targetCell = document.querySelector(`[data-row="${startRow + r}"][data-col="${startCol}"]`);

                if (targetCell) {
                    targetCell.className = 'grid-cell section';
                    targetCell.dataset.section = sectionId;
                    targetCell.dataset.sectionType = sectionData.type;
                    targetCell.dataset.sectionRow = r;
                    targetCell.dataset.sectionCol = 0;
                    targetCell.dataset.sectionRows = actualGridWidth;
                    targetCell.dataset.sectionCols = 1;
                    targetCell.style.backgroundColor = 'transparent';
                    targetCell.style.border = 'none';
                    targetCell.style.boxShadow = 'none';
                    targetCell.style.transition = 'all 0.2s ease';

                    if (r === 0) {
                        // First cell - show section name only (NO CAPACITY DISPLAY for capacity_only)
                        targetCell.innerHTML = `
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; display: flex; align-items: center; justify-content: center; z-index: 5; pointer-events: none;">
                                <div style="background: white; color: #333; padding: 2px 8px; text-align: center; font-weight: bold; border-radius: 3px; font-size: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    ${sectionName}
                                </div>
                            </div>
                        `;
                        targetCell.classList.add('section-start');
                    } else {
                        // Other cells - just show the background (no content)
                        targetCell.innerHTML = `
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; z-index: 5; pointer-events: none;"></div>
                        `;
                    }
                }
            }
        } else {
            // Horizontal orientation (existing logic)
            for (let c = 0; c < actualGridWidth; c++) {
                const targetCell = document.querySelector(`[data-row="${startRow}"][data-col="${startCol + c}"]`);

                if (targetCell) {
                    targetCell.className = 'grid-cell section';
                    targetCell.dataset.section = sectionId;
                    targetCell.dataset.sectionType = sectionData.type;
                    targetCell.dataset.sectionRow = 0;
                    targetCell.dataset.sectionCol = c;
                    targetCell.dataset.sectionRows = 1;
                    targetCell.dataset.sectionCols = actualGridWidth;
                    targetCell.style.backgroundColor = 'transparent';
                    targetCell.style.border = 'none';
                    targetCell.style.boxShadow = 'none';
                    targetCell.style.transition = 'all 0.2s ease';

                    if (c === 0) {
                        // First cell - show section name only (NO CAPACITY DISPLAY for capacity_only)
                        targetCell.innerHTML = `
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; display: flex; align-items: center; justify-content: center; z-index: 5; pointer-events: none;">
                                <div style="background: white; color: #333; padding: 2px 8px; text-align: center; font-weight: bold; border-radius: 3px; font-size: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    ${sectionName}
                                </div>
                            </div>
                        `;
                        targetCell.classList.add('section-start');
                    } else {
                        // Other cells - just show the background (no content)
                        targetCell.innerHTML = `
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: #e9ecef; border: 2px solid #ced4da; border-radius: 4px; z-index: 5; pointer-events: none;"></div>
                        `;
                    }
                }
            }
        }
    } else {
        // Regular slot-based section - existing logic
        const rotation = sectionData.rotation || 0;
        let startRow = sectionData.startRow;
        let startCol = sectionData.startCol;
        let originalRows = sectionData.rows;
        let originalCols = sectionData.cols;

        // Determine actual dimensions based on rotation
        let rows, cols;
        if (rotation === 0 || rotation === 180) {
            rows = originalRows;
            cols = originalCols;
        } else {
            rows = originalCols;
            cols = originalRows;
        }

        // Render section with proper styling and slot labels
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const targetCell = document.querySelector(`[data-row="${startRow + r}"][data-col="${startCol + c}"]`);

                if (targetCell) {
                    targetCell.className = 'grid-cell section';
                    targetCell.dataset.section = sectionId;
                    targetCell.dataset.sectionType = sectionData.type;
                    targetCell.dataset.sectionRow = r;
                    targetCell.dataset.sectionCol = c;
                    targetCell.dataset.sectionRows = rows;
                    targetCell.dataset.sectionCols = cols;
                    targetCell.style.backgroundColor = 'transparent';
                    targetCell.style.border = 'none';
                    targetCell.style.boxShadow = 'none';
                    targetCell.style.transition = 'all 0.2s ease';

                    // Slot number should be sequential: 1, 2, 3, 4, 5... for each section
                    const slotNumber = (r * cols) + c + 1;

                    // Get SVG content for section (parking slot) with section name and slot number
                    const svgContent = getElementSVG('section', 'right', sectionName, slotNumber, sectionName);

                    if (r === 0 && c === 0) {
                        // First cell - show section name and info with SVG
                        const orientation = (rotation === 0 || rotation === 180) ? 'HORIZONTAL' : 'VERTICAL';
                        targetCell.innerHTML = svgContent + `
                            <div style="position: absolute; top: 2px; left: 2px; background: white; color: #333; padding: 2px; text-align: center; font-weight: bold; border-radius: 3px; font-size: 9px; box-shadow: 0 1px 3px rgba(0,0,0,0.2); z-index: 10; pointer-events: none;">
                                ${sectionName}
                            </div>
                            <div style="position: absolute; top: 2px; right: 2px; font-size: 7px; color: white; text-align: center; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.3); z-index: 10; pointer-events: none;">
                                ${orientation}
                            </div>
                        `;
                        targetCell.classList.add('section-start');
                    } else {
                        // Other cells - SVG already contains the section name + number (e.g., "C-1")
                        targetCell.innerHTML = svgContent;
                    }
                }
            }
        }
    }
}

// Delete selected section for edit
async function deleteSelectedSectionForEdit() {
    if (!selectedSectionForEdit) return;

    const confirmed = await showDesignerConfirm('Are you sure you want to delete this section?', 'Delete Section', 'Delete', 'Cancel', 'danger');
    if (confirmed) {
        // Clear section cells completely
        document.querySelectorAll(`[data-section="${selectedSectionForEdit}"]`).forEach(cell => {
            cell.className = 'grid-cell';
            cell.innerHTML = '';
            cell.removeAttribute('data-section');
            cell.removeAttribute('data-section-type');
            cell.removeAttribute('data-section-row');
            cell.removeAttribute('data-section-col');
            cell.removeAttribute('data-section-rows');
            cell.removeAttribute('data-section-cols');
            cell.removeAttribute('data-section-start');
            cell.style.cssText = ''; // Clear all inline styles
            cell.classList.remove('section', 'section-preview', 'section-start', 'section-selected');
        });

        // Remove from placed sections
        placedSections.delete(selectedSectionForEdit);

        // Update indicators
        updateSectionIndicators();

        // Hide edit controls
        cancelSectionEdit();

        console.log('Section deleted:', selectedSectionForEdit);
    }
}

// Cancel section edit
function cancelSectionEdit() {
    // Clear section selection highlighting
    document.querySelectorAll('.grid-cell.section-selected').forEach(c => c.classList.remove('section-selected'));

    // Hide edit controls
    const editControls = document.getElementById('sectionEditControls');

    if (editControls) {
        editControls.style.display = 'none';
    }

    selectedSectionForEdit = null;
    originalSectionData = null;
    rotationPreviewData = null;
    // Rotation functionality removed

    console.log('Section edit cancelled');
}

// Handle cell mouse down for dragging
function handleCellMouseDown(cell, row, col, event) {
    console.log('=== MOUSE DOWN DEBUG ===');
    console.log('cell.dataset.section:', cell.dataset.section);
    console.log('isPlacingSection:', isPlacingSection);
    console.log('cell.dataset.elementType:', cell.dataset.elementType);
    console.log('event.target:', event.target);
    console.log('event.currentTarget:', event.currentTarget);

    // Prevent drag if we're placing a section
    if (isPlacingSection) {
        console.log('Drag prevented - isPlacingSection is true');
        return;
    }

    // Force prevent default and stop propagation immediately
    event.preventDefault();
    event.stopPropagation();

    // Check if clicking on a section
    if (cell.dataset.section) {
        console.log('Starting section drag');
        isDraggingSection = true;
        dragStartCell = cell;
        console.log('Started dragging section:', cell.dataset.section);
        return;
    }

    // Check if clicking on an element (but not obstacles)
    if (cell.dataset.elementType && !isObstacle(cell.dataset.elementType)) {
        console.log('Starting element drag');
        isDraggingElement = true;
        dragStartCell = cell;
        draggedElementPos = `${row},${col}`;
        console.log('Started dragging element:', cell.dataset.elementType);
        return;
    }

    console.log('Drag not started - no valid target');
}

// Handle cell mouse up for dropping
function handleCellMouseUp(cell, row, col, event) {
    if (isDraggingSection && dragStartCell && dragStartCell.dataset.section) {
        const sectionId = dragStartCell.dataset.section;
        const sectionData = placedSections.get(sectionId);

        if (sectionData && cell !== dragStartCell) {
            // Get actual grid size for bounds check
            const grid = document.getElementById('layout-grid');
            const actualRows = parseInt(grid?.dataset.rows) || 12;
            const actualCols = parseInt(grid?.dataset.cols) || 16;

            console.log('=== DRAG BOUNDS CHECK ===');
            console.log('Section data:', sectionData);
            console.log('Section orientation:', sectionData.orientation);
            console.log('Section dimensions:', sectionData.rows, 'x', sectionData.cols);
            console.log('Section data types - rows:', typeof sectionData.rows, 'cols:', typeof sectionData.cols);
            console.log('Target position:', row, col);
            console.log('Actual grid size:', actualRows, 'x', actualCols);

            // Ensure numeric values for calculation
            const sectionRows = parseInt(sectionData.rows);
            const sectionCols = parseInt(sectionData.cols);

            console.log('Numeric section dimensions:', sectionRows, 'x', sectionCols);
            console.log('Check: row + sectionRows =', row, '+', sectionRows, '=', row + sectionRows, '<=', actualRows, '?');
            console.log('Check: col + sectionCols =', col, '+', sectionCols, '=', col + sectionCols, '<=', actualCols, '?');

            if (row + sectionRows <= actualRows && col + sectionCols <= actualCols) {
                if (!checkSectionOverlap(row, col, sectionRows, sectionCols, sectionId)) {
                    // Update position but preserve orientation
                    sectionData.startRow = row;
                    sectionData.startCol = col;

                    // Ensure orientation is preserved
                    if (!sectionData.orientation) {
                        sectionData.orientation = 'vertical'; // Default to vertical if not set
                    }

                    console.log('Before render - sectionData.orientation:', sectionData.orientation);
                    renderSection(sectionId, sectionData);

                    // Update section info if editing
                    if (selectedSectionForEdit === sectionId) {
                        const sectionInfo = document.getElementById('selectedSectionInfo');
                        if (sectionInfo) {
                            sectionInfo.innerHTML = `
                                <strong>Section:</strong> ${sectionData.type}<br>
                                <strong>Size:</strong> ${sectionData.rows}×${sectionData.cols}<br>
                                <strong>Position:</strong> (${sectionData.startRow}, ${sectionData.startCol})<br>
                                <strong>Orientation:</strong> ${sectionData.orientation}
                            `;
                        }
                    }

                    console.log('Section moved to:', row, col, 'with orientation:', sectionData.orientation);
                } else {
                    showError('Cannot move section here - would overlap with existing content', 'Move Section');
                }
            } else {
                console.log('DRAG BOUNDS FAILED - Section does not fit');
                showError('Cannot move section here - would go out of bounds', 'Move Section');
            }
        }

        // Clear drag targets and reset drag state
        clearDragTargets();
        isDraggingSection = false;
        dragStartCell = null;
    } else if (isDraggingElement && dragStartCell && draggedElementPos) {
        // Handle element drag and drop
        const targetPos = `${row},${col}`;
        const elementType = dragStartCell.dataset.elementType;
        const elementDirection = dragStartCell.dataset.elementDirection || 'horizontal';

        // Store cell dataset before clearing
        const cellDataset = { ...dragStartCell.dataset };

        const dropConflict = getCellDropConflict(cell, dragStartCell);
        if (cell !== dragStartCell && elementType && !dropConflict && !wouldOverlapObstacles(elementType, row, col)) {
            // Clear original cell
            dragStartCell.className = 'grid-cell';
            dragStartCell.innerHTML = '';
            delete dragStartCell.dataset.elementType;
            delete dragStartCell.dataset.elementDirection;

            cell.classList.add(elementType);
            cell.dataset.elementType = elementType;
            cell.dataset.elementDirection = elementDirection;
            const svgContent = getElementSVG(elementType, elementDirection);
            if (svgContent) {
                cell.innerHTML = svgContent;
            }

            // Update layout data
            delete layoutData[draggedElementPos];
            layoutData[targetPos] = {
                type: elementType,
                element: elementType,
                direction: elementDirection
            };

            // Mark as having unsaved changes
            window.layoutDesignerSaved = false;

            console.log('Element moved from', draggedElementPos, 'to', targetPos);
        } else if (cell !== dragStartCell) {
            if (dropConflict) {
                showError(`Cannot move element here - ${dropConflict}`, 'Move Element');
            } else {
                showError('Cannot move element here - target is not allowed', 'Move Element');
            }
        }

        // Clear drag state and targets
        clearDragTargets();
        isDraggingElement = false;
        dragStartCell = null;
        draggedElementPos = null;
    }
}

// Handle cell mouse over for drag preview
function handleCellMouseOver(cell, row, col, event) {
    // Add hover effect to show cell boundary
    if (!cell.classList.contains('drag-target')) {
        cell.style.backgroundColor = '#f0f0f0';
    }

    if (isDraggingSection && dragStartCell && dragStartCell.dataset.section) {
        // Clear previous targets first to prevent trails
        clearDragTargets();
        // Add visual feedback for drag target
        cell.classList.add('drag-target');
    } else if (isDraggingElement && dragStartCell) {
        // Clear previous targets first to prevent trails
        clearDragTargets();
        // Add visual feedback for element drag target
        cell.classList.add('drag-target');
    }
}

// Handle cell mouse out to reset hover effect
function handleCellMouseOut(cell, row, col, event) {
    // Reset hover effect if not a drag target
    if (!cell.classList.contains('drag-target')) {
        cell.style.backgroundColor = '#ffffff';
    }
}

// Clear drag target highlighting
function clearDragTargets() {
    document.querySelectorAll('.drag-target').forEach(cell => {
        cell.classList.remove('drag-target');
    });
}

// Place element on grid
function placeElement(cell, row, col) {
    if (!selectedElement) return;

    // Check if cell has a section - if so, don't place element
    if (cell.dataset.section) {
        showError('Cannot place element on a section cell. Please select an empty cell.', 'Element Placement');
        return;
    }

    if (selectedElement === 'clear') {
        // Only clear if it's not a section cell
        if (!cell.dataset.section) {
            cell.className = 'grid-cell';
            cell.innerHTML = '';
            cell.removeAttribute('data-element-type');
            cell.removeAttribute('data-element-direction');

            // Remove from layout data
            const position = `${row},${col}`;
            delete layoutData[position];
            // Mark as having unsaved changes when clearing elements
            window.layoutDesignerSaved = false;
            console.log('Element cleared at:', row, col);
        }
        return;
    }

    // Clear existing element content but preserve section data if present
    if (!cell.dataset.section) {
        cell.className = 'grid-cell';
        cell.innerHTML = '';
    }

    // Add element class and SVG content
    cell.classList.add(selectedElement);
    cell.dataset.elementType = selectedElement;
    cell.dataset.elementDirection = selectedElementDirection;

    // Get SVG content for the element
    const svgContent = getElementSVG(selectedElement, selectedElementDirection);

    if (svgContent) {
        // If there's already content (like section labels), add SVG above it
        if (cell.innerHTML && cell.dataset.section) {
            cell.innerHTML = svgContent + cell.innerHTML;
        } else {
            cell.innerHTML = svgContent;
        }
    }

    // Store the element data
    const position = `${row},${col}`;
    layoutData[position] = {
        type: selectedElement,
        element: selectedElement,
        direction: selectedElementDirection
    };

    // Mark as having unsaved changes
    window.layoutDesignerSaved = false;

    selectedCell = cell;
    prepareRotationControls(selectedElement);
    updateRotationStatus();

    // Update save button appearance
    updateSaveButtonAppearance();

    // Check if we need to expand grid for new content
    const grid = document.getElementById('layout-grid');
    if (grid) {
        const currentRows = parseInt(grid.dataset.rows) || 8;
        const currentCols = parseInt(grid.dataset.cols) || 8;

        // If placing outside current grid, show warning
        if (row >= currentRows || col >= currentCols) {
            showError(`Element placed outside current grid (${currentRows}x${currentCols}). Use the + buttons to expand the grid.`, 'Element Placement');
        }
    }

    console.log('Element placed:', selectedElement, 'at', row, col);
    console.log('Layout data updated:', layoutData);
}

// Place section on grid
function placeSection(cell, row, col) {
    if (!selectedSection || !selectedSectionData) return;

    const { rows, cols } = selectedSectionData;

    // Get current grid dimensions
    const gridSize = document.getElementById('layout-grid');
    const currentRows = gridSize ? parseInt(gridSize.dataset.rows) || 8 : 12;
    const currentCols = gridSize ? parseInt(gridSize.dataset.cols) || 8 : 16;

    // Check if section fits
    if (row + rows > currentRows || col + cols > currentCols) {
        showError(`Section does not fit in this position! (Grid is ${currentRows}x${currentCols})`, 'Section Placement');
        return;
    }

    // Place section cells
    for (let r = row; r < row + rows; r++) {
        for (let c = col; c < col + cols; c++) {
            const targetCell = document.querySelector(`[data-row="${r}"][data-col="${c}"]`);
            targetCell.className = 'grid-cell occupied';
            targetCell.dataset.section = selectedSection;

            // Add slot label
            const slotNumber = ((r - row) * cols) + (c - col) + 1;
            targetCell.innerHTML = `<div class="slot-label">${selectedSection}${slotNumber.toString().padStart(2, '0')}</div>`;
        }
    }

    // Mark as having unsaved changes when placing section
    window.layoutDesignerSaved = false;

    console.log('Section placed:', selectedSection, 'at', row, col);
}

// Place entire section on grid (one-time placement) - SHOW ORIENTATION CHOICE
function placeEntireSection(cell, row, col) {
    if (!isPlacingSection || !currentPlacingData) return;

    const { rows, cols } = currentPlacingData;
    const sectionId = currentPlacingSection;

    // Check if this section type is already placed
    const existingSection = Array.from(placedSections.values()).find(s => s.type === sectionId);
    if (existingSection) {
        showError(`Section type "${sectionId}" is already placed. Only one instance allowed per section type.`, 'Section In Use');
        return;
    }

    // For capacity_only sections, we need to check orientation first before overlap
    // because the dimensions change based on orientation
    const isCapacityOnly = currentPlacingData.section_mode === 'capacity_only';

    if (!isCapacityOnly) {
        // Get current grid dimensions
        const grid = document.getElementById('layout-grid');
        const currentRows = grid ? parseInt(grid.dataset.rows) || 8 : 8;
        const currentCols = grid ? parseInt(grid.dataset.cols) || 8 : 8;

        // For regular sections, check basic fit and overlap first
        if (row + rows > currentRows || col + cols > currentCols) {
            showError(`Section does not fit at this position! (Grid is ${currentRows}x${currentCols})`, 'Section Placement');
            isPlacingSection = false; // Reset state
            return;
        }

        // Check for overlapping with existing sections or elements
        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < cols; c++) {
                const targetRow = row + r;
                const targetCol = col + c;
                const targetCell = document.querySelector(`[data-row="${targetRow}"][data-col="${targetCol}"]`);

                if (targetCell) {
                    // Check for sections
                    if (targetCell.classList.contains('section') || targetCell.dataset.section) {
                        showError('Cannot place section here - overlaps with existing section', 'Section Placement');
                        return;
                    }
                    // Check for elements
                    if (targetCell.dataset.elementType) {
                        showError('Cannot place section here - overlaps with existing element', 'Section Placement');
                        return;
                    }
                }
            }
        }
    }

    // Generate unique section instance ID
    const sectionInstanceId = `${sectionId}_${Date.now()}`;

    // Set up for orientation choice
    selectedSectionForEdit = sectionInstanceId;
    rotationPreviewData = {
        type: sectionId,
        startRow: row,
        startCol: col,
        rows: rows,
        cols: cols,
        isCapacityOnly: isCapacityOnly
    };

    // Show orientation choice modal
    showOrientationChoice();
}

// Rotation functionality removed

// Delete selected section
async function deleteSelectedSection() {
    if (!selectedSectionForEdit) return;

    const confirmed = await showDesignerConfirm('Are you sure you want to delete this section?', 'Delete Section', 'Delete', 'Cancel', 'danger');
    if (confirmed) {
        // Clear section cells completely
        document.querySelectorAll(`[data-section="${selectedSectionForEdit}"]`).forEach(cell => {
            cell.className = 'grid-cell';
            cell.innerHTML = '';
            cell.removeAttribute('data-section');
            cell.removeAttribute('data-section-type');
            cell.removeAttribute('data-section-row');
            cell.removeAttribute('data-section-col');
            cell.removeAttribute('data-section-rows');
            cell.removeAttribute('data-section-cols');
            cell.removeAttribute('data-section-start');
            cell.style.cssText = '';
            cell.classList.remove('section', 'section-preview', 'section-start', 'section-selected');
        });

        // Remove from placed sections
        placedSections.delete(selectedSectionForEdit);

        // Update designer stats
        updateDesignerStats();

        // Update indicators
        updateSectionIndicators();

        // Hide edit controls
        cancelSectionEdit();
    }
}

// Clear grid
async function clearGrid() {
    // Simple confirmation
    const elementCount = Object.keys(layoutData).length;
    const sectionCount = placedSections.size;

    if (elementCount > 0 || sectionCount > 0) {
        const confirmed = await showDesignerConfirm(`Clear all ${elementCount} elements and ${sectionCount} sections?`, 'Clear Layout', 'Clear All', 'Cancel', 'danger');
        if (!confirmed) {
            return;
        }
    }

    // Clear data structures FIRST before regenerating grid
    layoutData = {};
    placedSections.clear();
    updateDesignerStats();
    selectedSection = null;
    selectedSectionData = null;
    selectedSectionForEdit = null;
    originalSectionData = null;
    rotationPreviewData = null;

    // Clear all cell data and visuals
    document.querySelectorAll('.grid-cell').forEach(cell => {
        cell.className = 'grid-cell';
        cell.innerHTML = '';
        delete cell.dataset.section;
        delete cell.dataset.sectionType;
        delete cell.dataset.elementType;
        delete cell.dataset.elementDirection;
        cell.style.cssText = '';
    });

    // Reset section indicators
    updateSectionIndicators();

    // Update save button appearance
    updateSaveButtonAppearance();

    // Clear all selections
    clearAllSelections();

    console.log('✅ Grid cleared - all data and visuals removed');
}

// Save layout
async function saveLayout() {
    if (!currentArea) {
        showError('Please select an area first');
        return;
    }

    if (!currentFloor) {
        showError('Please select a floor first');
        return;
    }

    // Get snapshot name from user using modal
    const snapshotName = await showDesignerModal({
        title: 'Save Layout Snapshot',
        message: `
            <div style="margin-bottom: 15px;">
                <label for="snapshotNameInput" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Layout Name:</label>
                <input type="text" id="snapshotNameInput" class="form-control" 
                       value="Layout ${currentArea.parking_area_name} Floor ${currentFloor} - ${new Date().toLocaleDateString()}" 
                       placeholder="Enter layout name..."
                       style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;">
            </div>
        `,
        confirmText: 'Save',
        cancelText: 'Cancel',
        showCancel: true,
        variant: 'primary'
    });

    // snapshotName will be the input value if there's an input, or true/false
    if (!snapshotName || snapshotName === true) {
        return;
    }

    const actualSnapshotName = snapshotName;

    try {

        const csrfToken = (typeof window.getCSRFToken === 'function') ? window.getCSRFToken() : null;

        // Optimize layout data to only include used area
        const optimizedData = optimizeLayoutData();
        const { bounds, elements, sections } = optimizedData;

        // Generate complete SVG layout
        const svgData = generateCompleteSVG();

        // Get actual grid dimensions for metadata
        const grid = document.getElementById('layout-grid');
        const rowsForSave = grid ? parseInt(grid.dataset.rows) || (bounds.maxRow + 1) : (bounds.maxRow + 1);
        const colsForSave = grid ? parseInt(grid.dataset.cols) || (bounds.maxCol + 1) : (bounds.maxCol + 1);

        // Prepare the layout data structure (API expects: area_id, floor, layout_data)
        const layoutPayload = {
            area_id: currentArea.parking_area_id,
            floor: parseInt(currentFloor), // Ensure floor is a number
            layout_data: {
                snapshot_name: actualSnapshotName, // Store name inside layout_data
                grid_bounds: bounds,
                grid_size: {
                    rows: rowsForSave,
                    columns: colsForSave
                },
                elements: elements,
                sections: sections,
                svg_data: svgData, // Add the complete SVG layout
                metadata: {
                    created_at: new Date().toISOString(),
                    created_by: 'admin',
                    version: '1.0',
                    optimized: true,
                    svg_generated: true
                }
            }
        };


        const response = await fetch(window.APP_BASE_URL + 'api/parking/save-layout', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
            },
            body: JSON.stringify(layoutPayload)
        });

        // Check if response has content before parsing
        const contentType = response.headers.get('content-type');
        let result;

        if (contentType && contentType.includes('application/json')) {
            const text = await response.text();
            try {
                result = text ? JSON.parse(text) : { success: false, message: 'Empty response from server' };
            } catch (e) {
                console.error('Failed to parse JSON response:', text);
                throw new Error(`Server returned invalid JSON: ${text.substring(0, 200)}`);
            }
        } else {
            const text = await response.text();
            console.error('Unexpected response type:', contentType, text.substring(0, 200));
            throw new Error(`Server returned ${response.status}: ${text.substring(0, 200)}`);
        }

        console.log('Save response:', result);

        if (!response.ok || !result.success) {
            const errorMsg = result.message || result.error || `HTTP ${response.status}: ${response.statusText}`;
            throw new Error(errorMsg);
        }

        if (result.success) {
            showSuccess(`Layout "${actualSnapshotName}" saved successfully!`);

            // Mark layout as saved (don't clear the actual layout)
            updateSaveButtonAppearance();

            // Update main page statistics if they were returned
            if (result.stats && typeof window.updateStats === 'function') {
                window.updateStats(result.stats);
            }

            // Reset user preferences since work is now saved
            window.userPreferences.skipConfirmations = false;
            window.userPreferences.lastConfirmationTime = 0;

            // Mark that we've saved - this will prevent the unsaved changes check
            window.layoutDesignerSaved = true;
        } else {
            throw new Error(result.message || 'Failed to save layout');
        }

    } catch (error) {
        console.error('❌ Error saving layout:', error);
        showError(`Failed to save layout: ${error.message}`);
    }
}

// Load existing layout
async function loadExistingLayout() {
    if (!currentArea) {
        showError('Please select an area first');
        return;
    }

    // Get current floor selection
    const currentFloor = document.getElementById('floorSelect')?.value;
    if (!currentFloor) {
        showError('Please select a floor first');
        return;
    }

    try {
        console.log('📥 Loading existing layout for area:', currentArea.parking_area_id, 'floor:', currentFloor);

        const response = await fetch(window.APP_BASE_URL + 'api/parking/layout/' + currentArea.parking_area_id + '/' + currentFloor);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('📥 Load response:', result);

        if (result.success && result.data) {
            const loadedLayoutData = result.data;
            console.log('📥 Layout data loaded:', loadedLayoutData);

            // Parse layout_data if it's a string
            let parsedLayoutData = loadedLayoutData.layout_data;
            if (typeof parsedLayoutData === 'string') {
                try {
                    parsedLayoutData = JSON.parse(parsedLayoutData);
                    console.log('✅ Parsed layout_data from string:', parsedLayoutData);
                } catch (e) {
                    console.error('❌ Error parsing layout_data:', e);
                    showError('Invalid layout data format');
                    return;
                }
            }

            // Ensure grid is large enough for the loaded layout
            const grid = document.getElementById('layout-grid');
            if (grid && parsedLayoutData.grid_size) {
                grid.dataset.rows = Math.max(8, parsedLayoutData.grid_size.rows || 0);
                grid.dataset.cols = Math.max(8, parsedLayoutData.grid_size.columns || 0);
            } else if (grid && parsedLayoutData.grid_bounds) {
                // Fallback to bounds if grid_size is missing
                grid.dataset.rows = Math.max(8, parsedLayoutData.grid_bounds.maxRow + 1);
                grid.dataset.cols = Math.max(8, parsedLayoutData.grid_bounds.maxCol + 1);
            }

            // Clear current layout and regenerate grid with new dimensions
            generateGrid();
            placedSections.clear();
            selectedSection = null;
            selectedSectionData = null;
            updateSectionIndicators();
            clearAllSelections();

            // Restore sections
            if (parsedLayoutData && parsedLayoutData.sections) {
                console.log('=== RAW LOADED SECTIONS DATA ===');
                console.log('parsedLayoutData.sections:', JSON.stringify(parsedLayoutData.sections, null, 2));

                const floorFilteredSections = parsedLayoutData.sections.filter(section =>
                    section.section_data.floor == currentFloor
                );

                console.log('=== FLOOR FILTERED SECTIONS ===');
                console.log('floorFilteredSections:', JSON.stringify(floorFilteredSections, null, 2));

                // Process each section (no need to group - we're now saving as single records)
                floorFilteredSections.forEach(section => {
                    const sectionData = section.section_data;

                    // Ensure section_mode is set
                    sectionData.section_mode = sectionData.section_mode || 'slot_based';

                    console.log('Loading section:', sectionData.section_name, 'sectionData:', sectionData);
                    console.log('section_mode:', sectionData.section_mode);
                    console.log('grid_width:', sectionData.grid_width);
                    console.log('capacity:', sectionData.capacity);

                    // Add to placedSections map
                    placedSections.set(sectionData.id, sectionData);
                    // When loading a layout, reset saved flag so new changes are tracked
                    window.layoutDesignerSaved = false;

                    // Use the same renderSection function for consistency
                    renderSection(sectionData.id, sectionData);
                });

                // Update designer stats after loading all sections
                updateDesignerStats();
            }

            // Restore elements
            if (parsedLayoutData && parsedLayoutData.elements) {
                Object.entries(parsedLayoutData.elements).forEach(([position, element]) => {
                    const [row, col] = position.split(',').map(Number);
                    const cell = document.querySelector(`[data-row="${row}"][data-col="${col}"]`);
                    if (cell && !cell.dataset.section) {
                        // Store element data
                        cell.dataset.elementType = element.type;
                        cell.dataset.elementDirection = element.direction || '';

                        // Add element CSS classes
                        cell.classList.add(element.type);
                        if (element.direction) {
                            cell.classList.add(`dir-${element.direction}`);
                        }

                        // Add SVG content for the element
                        const svgContent = getElementSVG(element.type, element.direction);
                        if (svgContent) {
                            cell.innerHTML = svgContent;
                        }

                        // Store element in layoutData object for saving
                        layoutData[position] = {
                            type: element.type,
                            element: element.type,
                            direction: element.direction || 'horizontal'
                        };
                    }
                });
            }

            showSuccess(`Layout loaded successfully for Floor ${currentFloor}!`);
            updateSectionIndicators();
        } else {
            showError('No layout data found for this area');
        }
    } catch (error) {
        console.error('âŒ Error loading layout:', error);
        showError(`Failed to load layout: ${error.message}`);
    }
}

// Save and close layout designer
function saveAndCloseParkingLayout() {
    saveLayout();
    closeParkingLayoutDesigner();
}

// Show error message
function showError(message, title = 'Error') {
    console.error(message);
    return showDesignerAlert(message, title, 'danger');
}

// Show success message
function showSuccess(message, title = 'Success') {
    console.log('✅', message);
    return showDesignerAlert(message, title, 'success');
}

// Calculate grid bounds from layout data
function calculateGridBounds() {
    let minRow = Infinity, maxRow = -1;
    let minCol = Infinity, maxCol = -1;

    // Check elements
    Object.keys(layoutData).forEach(position => {
        const [row, col] = position.split(',').map(Number);
        if (!isNaN(row) && !isNaN(col)) {
            minRow = Math.min(minRow, row);
            maxRow = Math.max(maxRow, row);
            minCol = Math.min(minCol, col);
            maxCol = Math.max(maxCol, col);
        }
    });

    // Check sections
    Array.from(placedSections.entries()).forEach(([sectionId, sectionData]) => {
        if (sectionData.startRow !== undefined && sectionData.startCol !== undefined) {
            minRow = Math.min(minRow, sectionData.startRow);
            maxRow = Math.max(maxRow, sectionData.startRow + sectionData.rows - 1);
            minCol = Math.min(minCol, sectionData.startCol);
            maxCol = Math.max(maxCol, sectionData.startCol + sectionData.cols - 1);
        }
    });

    // If no elements or sections, return default bounds
    if (minRow === Infinity) {
        return { minRow: 0, maxRow: 11, minCol: 0, maxCol: 15 };
    }

    return { minRow, maxRow, minCol, maxCol };
}

// Update designer statistics (sections and spots)
function updateDesignerStats() {
    const designerStatSections = document.getElementById('designerStatSections');
    const designerStatSpots = document.getElementById('designerStatSpots');

    if (!designerStatSections || !designerStatSpots) return;

    const totalSections = placedSections.size;
    let totalSpots = 0;

    Array.from(placedSections.values()).forEach(section => {
        const mode = section.section_mode || 'slot_based';
        if (mode === 'capacity_only') {
            totalSpots += parseInt(section.capacity || 0, 10);
        } else {
            const rows = parseInt(section.rows || 0, 10);
            const cols = parseInt(section.cols || 0, 10);
            totalSpots += (rows * cols);
        }
    });

    designerStatSections.textContent = totalSections;
    designerStatSpots.textContent = totalSpots;

    console.log(`📊 Designer Stats Updated: ${totalSections} sections, ${totalSpots} spots`);
}

// Optimize layout data to only include used area
function optimizeLayoutData() {
    const bounds = calculateGridBounds();
    const optimizedElements = {};
    const optimizedSections = [];

    // Only include elements within bounds
    console.log('=== PROCESSING ELEMENTS (OBSTACLES/ROADS) ===');
    Object.keys(layoutData).forEach(position => {
        const [row, col] = position.split(',').map(Number);
        const elementData = layoutData[position];

        if (elementData && elementData.type &&
            row >= bounds.minRow && row <= bounds.maxRow &&
            col >= bounds.minCol && col <= bounds.maxCol) {
            optimizedElements[position] = elementData;
            console.log('Saving element:', elementData.type, 'at', position, '(no ID needed - visual only)');
        }
    });

    // Include unique sections from placedSections (not from DOM to avoid duplicates)
    Array.from(placedSections.entries()).forEach(([sectionId, sectionData]) => {
        if (sectionData.startRow !== undefined && sectionData.startCol !== undefined) {
            // CRITICAL: Ensure we have the real database parking_section_id
            // For loaded layouts, sectionId should be the real database ID
            // For newly created sections, use parking_section_id from sectionData
            const realSectionId = sectionData.parking_section_id || sectionId;
            
            console.log('Section ID Debug:', {
                mapKey: sectionId,
                parking_section_id: sectionData.parking_section_id,
                realSectionId: realSectionId,
                section_name: sectionData.section_name
            });
            
            // Prepare section data without occupancy for capacity_only sections
            const sectionPayload = {
                ...sectionData
            };

            // Only add capacity information for NON capacity_only sections
            // For capacity_only sections, mobile app will handle occupancy display
            if (sectionData.section_mode !== 'capacity_only') {
                sectionPayload.current_capacity = 0; // This should be calculated from actual usage
                sectionPayload.max_capacity = sectionData.capacity || 0;
            }
            // Remove any existing occupancy fields for capacity_only sections
            else {
                delete sectionPayload.current_capacity;
                delete sectionPayload.max_capacity;
                delete sectionPayload.occupiedSlots;
                delete sectionPayload.availableSlots;
            }

            // Always save as ONE single record regardless of section mode
            // The visualization will handle rendering differently based on section_mode
            optimizedSections.push({
                position: `${sectionData.startRow},${sectionData.startCol}`,
                section_data: sectionPayload
            });

            console.log('Saving section:', sectionData.section_name, 'as single record (mode:', sectionData.section_mode || 'unknown', ')',
                sectionData.section_mode === 'capacity_only' ? '- NO OCCUPANCY DATA' : '- with capacity info');
        }
    });

    console.log('=== SAVE SUMMARY ===');
    console.log('Elements (obstacles/roads):', Object.keys(optimizedElements).length, 'items (no IDs)');
    console.log('Sections:', optimizedSections.length, 'items');
    console.log('Capacity-only sections:', optimizedSections.filter(s => s.section_data.section_mode === 'capacity_only').length, 'single records');
    console.log('Slot-based sections:', optimizedSections.filter(s => s.section_data.is_slot).length, 'individual slots');

    return {
        bounds: bounds,
        elements: optimizedElements,
        sections: optimizedSections
    };
}

// Force load areas function
async function forceLoadAreas() {
    // Check if we're on a parking page
    if (!document.getElementById('areaSelect') && !document.getElementById('parkingLayoutDesignerModal')) {
        console.log('Layout Designer: Not on parking page, skipping force load');
        return;
    }

    console.log('Force loading areas...');
    areas = []; // Clear existing areas
    await loadAreas(); // Reload areas from API
}




// Close modal when clicking outside
window.addEventListener('click', function (event) {
    const modal = document.getElementById('parkingLayoutDesignerModal');
    if (event.target === modal) {
        closeParkingLayoutDesigner();
    }
});

// Auto-initialize when page loads (for parking pages only)
// This will be called when layout_editor.php loads via AJAX
window.initLayoutEditorPage = function () {
    console.log('Layout Editor page initialized');
    if (typeof forceLoadAreas === 'function') {
        forceLoadAreas();
    }
};

// Note: We don't auto-init on jQuery ready for ALL pages
// Only initialize when the parking page is loaded

function showDesignerModal({
    title = 'Notice',
    message = '',
    confirmText = 'OK',
    cancelText = 'Cancel',
    showCancel = false,
    variant = 'primary'
} = {}) {
    return new Promise(resolve => {
        const modalEl = document.getElementById('designerMessageModal');
        const overlayEl = document.getElementById('parkingLayoutDesignerModal');

        // Safety check - if modal doesn't exist, just log and resolve
        if (!modalEl) {
            console.log('Layout Designer: designerMessageModal not found, using console instead');
            console.warn('Designer Message:', title, '-', message);
            resolve(true);
            return;
        }

        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        const titleEl = document.getElementById('designerMessageTitle');
        const bodyEl = document.getElementById('designerMessageBody');
        const confirmBtn = modalEl.querySelector('.designer-modal-confirm');
        const cancelBtn = modalEl.querySelector('.designer-modal-cancel');

        // Safety check for buttons
        if (!confirmBtn || !cancelBtn) {
            console.warn('Designer Message:', title, '-', message);
            resolve(true);
            return;
        }

        const baseZ = overlayEl ? parseInt(window.getComputedStyle(overlayEl).zIndex || '9990', 10) : 9990;
        modalEl.style.zIndex = baseZ + 20;
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: true, focus: true });

        titleEl.textContent = title;
        bodyEl.innerHTML = message;
        confirmBtn.textContent = confirmText;
        confirmBtn.className = `btn btn-${variant} designer-modal-confirm`;
        cancelBtn.textContent = cancelText;
        cancelBtn.classList.toggle('d-none', !showCancel);

        // Ensure backdrop is visible and properly styled for nested modals
        setTimeout(() => {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            const backdrop = backdrops.length > 0 ? backdrops[backdrops.length - 1] : null;
            if (backdrop) {
                backdrop.style.zIndex = baseZ + 15;
                backdrop.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            }
        }, 50);

        const cleanup = (result) => {
            confirmBtn.removeEventListener('click', onConfirm);
            cancelBtn.removeEventListener('click', onCancel);
            modalEl.removeEventListener('hidden.bs.modal', onHidden);
            resolve(result);
        };

        const onConfirm = () => {
            // Check if there's an input field and return its value
            const input = bodyEl.querySelector('input[id="snapshotNameInput"]');
            if (input) {
                const value = input.value.trim();
                bsModal.hide();
                cleanup(value || true); // Return input value or true if empty
            } else {
                bsModal.hide();
                cleanup(true);
            }
        };

        const onCancel = () => {
            bsModal.hide();
            cleanup(false);
        };

        const onHidden = () => {
            cleanup(false);
        };

        confirmBtn.addEventListener('click', onConfirm, { once: true });
        cancelBtn.addEventListener('click', onCancel, { once: true });
        modalEl.addEventListener('hidden.bs.modal', onHidden, { once: true });

        // Fallback: Also handle direct button clicks in case Bootstrap events fail
        confirmBtn.onclick = onConfirm;
        cancelBtn.onclick = onCancel;

        bsModal.show();
    });
}

function showDesignerAlert(message, title = 'Notice', variant = 'primary') {
    return showDesignerModal({ title, message, variant, showCancel: false });
}

function showDesignerConfirm(message, title = 'Confirm', confirmText = 'Confirm', cancelText = 'Cancel', variant = 'primary') {
    return showDesignerModal({ title, message, confirmText, cancelText, showCancel: true, variant });
}
