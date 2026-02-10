<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Login routes (must come before generic routes)
$routes->get('login', 'Login::index');
$routes->post('login/process', 'Login::process');
$routes->get('logout', 'Login::logout');

// Profile routes
$routes->post('profile/update', 'Profile::update');
$routes->post('profile/change-password', 'Profile::changePassword');
$routes->post('profile/save-app-settings', 'Profile::saveAppSettings');
$routes->post('profile/save-database-config', 'Profile::saveDatabaseConfig');

// Specific routes (must come before generic route)
$routes->get('dashboard', 'Dashboard::index');
$routes->get('reports', 'Reports::index');
$routes->get('reports/export', 'Reports::export');
$routes->get('analytics', function() {
    return redirect()->to('/reports');
});
$routes->get('logs', 'Logs::index');
$routes->get('logs/api', 'Logs::api');
$routes->get('logs/export', 'Logs::export');

// Feedback routes
$routes->get('feedback', 'Feedback::index');
$routes->get('feedback/export', 'Feedback::export');
$routes->get('feedback/view/(:num)', 'Feedback::view/$1');
$routes->get('feedback/stats', 'Feedback::stats');
$routes->post('feedback/reply', 'Feedback::reply');

// Users routes
$routes->get('users', 'Users::index');
$routes->get('users/list', 'Users::list');
$routes->get('users/get/(:num)', 'Users::get/$1');
$routes->get('users/getUserTypes', 'Users::getUserTypes');
$routes->post('users/create', 'Users::create');
$routes->post('users/update/(:num)', 'Users::update/$1');
$routes->post('users/delete/(:num)', 'Users::delete/$1');
$routes->get('users/export', 'Users::export');

// Walk-in Guests routes (Users section)
$routes->get('users/getWalkInGuests', 'Users::getWalkInGuests');
$routes->get('users/getWalkInGuestDetails/(:num)', 'Users::getWalkInGuestDetails/$1');
$routes->get('users/exportWalkInGuests', 'Users::exportWalkInGuests');
$routes->get('users/getAttendantsList', 'Users::getAttendantsList');

// Staff routes (Users section)
$routes->get('users/getStaffList', 'Users::getStaffList');
$routes->get('users/getStaffDetails/(:num)', 'Users::getStaffDetails/$1');
$routes->get('users/exportStaff', 'Users::exportStaff');
$routes->get('users/getParkingAreas', 'Users::getParkingAreas');
$routes->get('users/getStaffUserTypes', 'Users::getStaffUserTypes');

// Subscriptions routes
$routes->get('subscriptions', 'Subscriptions::index');
$routes->get('subscriptions/list', 'Subscriptions::list');
$routes->get('subscriptions/get/(:num)', 'Subscriptions::get/$1');
$routes->post('subscriptions/create', 'Subscriptions::create');
$routes->post('subscriptions/update/(:num)', 'Subscriptions::update/$1');
$routes->post('subscriptions/delete/(:num)', 'Subscriptions::delete/$1');
$routes->get('subscriptions/export', 'Subscriptions::export');

// Attendants routes
$routes->get('attendants', 'Attendants::index');
$routes->get('attendants/list', 'Attendants::list');
$routes->get('attendants/get/(:num)', 'Attendants::get/$1');
$routes->get('attendants/getUserTypes', 'Attendants::getUserTypes');
$routes->get('attendants/getParkingAreas', 'Attendants::getParkingAreas');
$routes->post('attendants/create', 'Attendants::create');
$routes->post('attendants/update/(:num)', 'Attendants::update/$1');
$routes->post('attendants/delete/(:num)', 'Attendants::delete/$1');
$routes->get('attendants/export', 'Attendants::export');

// Guest Bookings routes
$routes->get('attendants/getGuestBookings', 'Attendants::getGuestBookings');
$routes->get('attendants/getAttendantsList', 'Attendants::getAttendantsList');
$routes->get('attendants/getGuestBookingDetails/(:num)', 'Attendants::getGuestBookingDetails/$1');
$routes->get('attendants/exportGuestBookings', 'Attendants::exportGuestBookings');

// Parking Areas routes
$routes->get('parking/areas', 'ParkingAreas::index');
$routes->get('parking/areas/list', 'ParkingAreas::list');
$routes->get('parking/areas/get/(:num)', 'ParkingAreas::get/$1');
$routes->get('parking/areas/getVehicleTypes', 'ParkingAreas::getVehicleTypes');
$routes->post('parking/areas/create', 'ParkingAreas::create');
$routes->post('parking/areas/update/(:num)', 'ParkingAreas::update/$1');
$routes->post('parking/areas/delete/(:num)', 'ParkingAreas::delete/$1');
$routes->get('parking/areas/(:num)/sections', 'ParkingAreas::getSections/$1');
$routes->get('parking/areas/sections/get/(:num)', 'ParkingAreas::getSection/$1');
$routes->post('parking/areas/sections/create', 'ParkingAreas::createSection');
$routes->post('parking/areas/sections/update/(:num)', 'ParkingAreas::updateSection/$1');
$routes->post('parking/areas/sections/delete/(:num)', 'ParkingAreas::deleteSection/$1');
$routes->post('parking/areas/createWithSections', 'ParkingAreas::createWithSections');
$routes->get('api/geoapify/autocomplete', 'GeoapifyProxy::autocomplete');

// Parking Overview routes
$routes->get('parking/overview', 'ParkingAreas::overview');
$routes->get('api/parking/overview', 'ParkingAreas::list'); // Reuse list method for overview API
$routes->get('api/parking/sections/(:num)', 'ParkingAreas::getOverviewSections/$1');
$routes->get('api/parking/section-grid/(:num)', 'ParkingAreas::getSectionGrid/$1');
$routes->get('api/parking/layout/(:num)/(:num)', 'ParkingAreas::getLayout/$1/$2');
$routes->post('api/parking/save-layout', 'ParkingAreas::saveLayout');

// Dynamic page loading route - handles all page navigation
// IMPORTANT: These routes must be LAST to catch all page routes
// Examples: /users, /parking/areas, /analytics
// 
// Handle nested routes FIRST (e.g., parking/areas) with two segments
$routes->get('(:segment)/(:segment)', 'PageController::load/$1/$2');
// Handle single segment routes (e.g., users, analytics) - dashboard handled above
$routes->get('(:segment)', 'PageController::load/$1');
