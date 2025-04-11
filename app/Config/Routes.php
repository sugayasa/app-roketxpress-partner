<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Index');
$routes->setDefaultMethod('main');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\Index::response404');
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->post('/', 'Index::index');
$routes->get('/', 'Index::main');
$routes->get('/logoutPage', 'Index::main', ['as' => 'logoutPage']);
$routes->get('/loginPage', 'Index::loginPage');
$routes->post('/mainPage', 'Index::mainPage', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('access/check', 'Access::check');
$routes->post('access/login', 'Access::login', ['filter' => 'auth:mustNotBeLoggedIn']);
$routes->get('access/logout/(:any)', 'Access::logout/$1');
$routes->get('access/captcha/(:any)', 'Access::captcha/$1');
$routes->post('access/getDataOption', 'Access::getDataOption', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/getDataDashboard', 'Access::getDataDashboard', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/unreadNotificationList', 'Access::unreadNotificationList', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/dismissNotification', 'Access::dismissNotification', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/dismissAllNotification', 'Access::dismissAllNotification', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/detailProfileSetting', 'Access::detailProfileSetting', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('access/saveDetailProfileSetting', 'Access::saveDetailProfileSetting', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('view/dashboard', 'View::dashboard', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/notification', 'View::notification', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/productList', 'View::productList', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/reservation', 'View::reservation', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/schedule', 'View::schedule', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/fee', 'View::fee', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/collectPayment', 'View::collectPayment', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/finance', 'View::finance', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('view/userFinanceSetting', 'View::userFinanceSetting', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('notification/getDataNotification', 'Notification::getDataNotification', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('productList/getDataProductList', 'ProductList::getDataProductList', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('reservation/getDataReservation', 'Reservation::getDataReservation', ['filter' => 'auth:mustBeLoggedIn']);
$routes->get('reservation/excelDetailReservation/(:any)', 'Reservation::excelDetailReservation/$1');
$routes->post('reservation/getDetailConfirmReservation', 'Reservation::getDetailConfirmReservation', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('reservation/submitReservationConfirmation', 'Reservation::submitReservationConfirmation', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('scheduleReservation/getDataScheduleReservation', 'ScheduleReservation::getDataScheduleReservation', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('scheduleReservation/getDetailScheduleReservation', 'ScheduleReservation::getDetailScheduleReservation', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('scheduleReservation/updateScheduleTime', 'ScheduleReservation::updateScheduleTime', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('scheduleReservation/confirmCollectPayment', 'ScheduleReservation::confirmCollectPayment', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('scheduleReservation/updateStatusSchedule', 'ScheduleReservation::updateStatusSchedule', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('fee/getDataDetailFee', 'Fee::getDataDetailFee', ['filter' => 'auth:mustBeLoggedIn']);
$routes->get('fee/excelDetailFee/(:any)', 'Fee::excelDetailFee/$1');

$routes->post('collectPayment/getDataCollectPayment', 'CollectPayment::getDataCollectPayment', ['filter' => 'auth:mustBeLoggedIn']);
$routes->get('collectPayment/excelCollectPayment/(:any)', 'CollectPayment::excelCollectPayment/$1');
$routes->post('collectPayment/searchListReservation', 'CollectPayment::searchListReservation', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('collectPayment/submitNewCollectPayment', 'CollectPayment::submitNewCollectPayment', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('collectPayment/getDetailCollectPayment', 'CollectPayment::getDetailCollectPayment', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('collectPayment/uploadSettlementReceipt/(:any)', 'CollectPayment::uploadSettlementReceipt/$1', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('collectPayment/submitSettlementCollectPayment', 'CollectPayment::submitSettlementCollectPayment', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('finance/getDetailFinancePartner', 'Finance::getDetailFinancePartner', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('finance/getDataListDepositHistory', 'Finance::getDataListDepositHistory', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('finance/getDetailWithdrawal', 'Finance::getDetailWithdrawal', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('finance/submitWithdrawalRequest', 'Finance::submitWithdrawalRequest', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('finance/submitOTPWithdrawalRequest', 'Finance::submitOTPWithdrawalRequest', ['filter' => 'auth:mustBeLoggedIn']);

$routes->post('userFinanceSetting/getDetailDataUserFinance', 'UserFinanceSetting::getDetailDataUserFinance', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userFinanceSetting/setPartnerSecretPIN', 'UserFinanceSetting::setPartnerSecretPIN', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userFinanceSetting/setPartnerBankAccount', 'UserFinanceSetting::setPartnerBankAccount', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userFinanceSetting/submitOTPDataBankAccount', 'UserFinanceSetting::submitOTPDataBankAccount', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userFinanceSetting/setActiveBankAccount', 'UserFinanceSetting::setActiveBankAccount', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userFinanceSetting/submitDataUserPartner', 'UserFinanceSetting::submitDataUserPartner', ['filter' => 'auth:mustBeLoggedIn']);
$routes->post('userFinanceSetting/deleteUserPartner', 'UserFinanceSetting::deleteUserPartner', ['filter' => 'auth:mustBeLoggedIn']);
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
