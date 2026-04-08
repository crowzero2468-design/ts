<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'LogoutController::logout');
$routes->post('forgot/checkUser', 'Auth::checkUser');
$routes->post('forgot/updatePassword', 'Auth::updatePassword');

$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);

$routes->get('search/ward', 'SearchController::ward');
$routes->get('search/technician', 'SearchController::technician');

$routes->post('trouble/saveResponse', 'TroubleController::saveResponse');
$routes->post('trouble/markDone', 'TroubleController::markDone');
$routes->get('dashboard/check-new', 'DashboardController::checkNewTrouble');
$routes->get('actlog', 'TSHistoryController::index', ['filter' => 'auth']);
$routes->get('tshistory/getData', 'TSHistoryController::getData');
$routes->get('tech', 'TechController::index', ['filter' => 'auth']);
$routes->post('tech/toggleStatus/(:num)', 'TechController::toggleStatus/$1');
$routes->post('tech/update/(:num)', 'TechController::update/$1');
$routes->post('tech/delete/(:num)', 'TechController::delete/$1');
$routes->get('ongoing', 'OngoingController::index', ['filter' => 'auth']);
$routes->post('trouble/delete', 'OngoingController::delete');
$routes->get('dashboard/refreshTodayTable', 'DashboardController::refreshTodayTable', ['filter' => 'auth']);
$routes->get('dashboard/refresh-counts', 'DashboardController::refreshCounts', ['filter' => 'auth']);

$routes->get('chat/(:any)', 'MessageController::index/$1');
$routes->post('trouble/endorse', 'TroubleController::endorse');

$routes->get('tshistory/printForm', 'TSHistoryController::printForm');

// ==========================
// MESSAGE ROUTES (ORDER MATTERS)
// ==========================

$routes->get('message', 'MessageController::index', ['filter' => 'auth']);

$routes->get('message/unreadTotal', 'MessageController::unreadTotal');
$routes->get('message/unreadCount', 'MessageController::unreadCount');

$routes->get('message/fetch/(:any)', 'MessageController::fetch/$1');
$routes->post('message/send', 'MessageController::send');

$routes->post('message/typing', 'MessageController::typing');
$routes->get('message/checkTyping/(:any)', 'MessageController::checkTyping/$1');

// ⚠️ KEEP THIS LAST (wildcard)
$routes->get('message/(:any)', 'MessageController::index/$1', ['filter' => 'auth']);




$routes->get('profile', 'Profile::index' , ['filter' => 'auth']);
$routes->post('profile/update', 'Profile::update', ['filter' => 'auth']);
// $routes->post('profile/update-name', 'Profile::updateName');
// $routes->post('profile/update-password', 'Profile::updatePassword');
// $routes->post('profile/update-username', 'Profile::updateUsername');
// $routes->get('dashboard', 'UserController::index', ['filter' => 'auth']);

$routes->post('schedule/import', 'ScheduleController::import');
$routes->get('sched', 'ScheduleController::index', ['filter' => 'auth']);

$routes->post('tech/store', 'TechController::store');

// ============================
// SCHEDULER OVERRIDE SWITCH
// ============================

$routes->get('tech/setOverride/(:num)', 'TechController::setOverride/$1');
$routes->post('trouble/startNow', 'TroubleController::startNow');
$routes->post('trouble/saveAck', 'TroubleController::saveAck');


// ============================
// New Updates
// ============================

$routes->get('equip', 'EquipmentController::index');
$routes->post('equipment/save', 'EquipmentController::save');
$routes->get('equipment/getData', 'EquipmentController::getData');
$routes->get('equipment/form', 'EquipmentController::form');
$routes->post('/equipment/importExcel', 'EquipmentController::importExcel');
$routes->get('equipment/get/(:num)', 'EquipmentController::get/$1');
$routes->post('equipment/update', 'EquipmentController::update');
$routes->post('equipment/delete', 'EquipmentController::delete');


$routes->get('pmc', 'PmcController::index');
$routes->get('pmc/data', 'PmcController::getData');
$routes->get('pmc/wards', 'PmcController::getWards');
$routes->post('savePms', 'PmcController::savePms');
$routes->get('pmc/form', 'PmcController::form');



$routes->get('temp', 'TempController::index');
$routes->get('temp/getData', 'TempController::getData');
$routes->post('temp/add', 'TempController::add'); 
$routes->get('temp/getSingle/(:num)', 'TempController::getSingle/$1');
$routes->post('temp/update', 'TempController::update');
$routes->post('temp/delete', 'TempController::delete');
$routes->get('temperature/report', 'TempController::TempReport');





$routes->get('speedtest', 'SpeedtestController::index');
$routes->get('speedtest/fetchData', 'SpeedtestController::fetchData');
$routes->get('speedtest/get/(:num)', 'SpeedtestController::get/$1');      
$routes->post('speedtest/add', 'SpeedtestController::add');
$routes->post('speedtest/update', 'SpeedtestController::update');        
$routes->post('speedtest/delete', 'SpeedtestController::delete');         
$routes->get('speedtest/viewForm', 'SpeedtestController::viewForm');
$routes->post('speedtest/importExcel', 'SpeedtestController::importExcel');






$routes->get('dash2', 'Dashboard2Controller::index');