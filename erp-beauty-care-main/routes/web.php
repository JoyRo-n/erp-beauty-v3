<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ErpController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccessController;

// Authentication Routes (Key Access Enforcement)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [ErpController::class, 'home'])->name('s01.home');
Route::post('/switch-role', [ErpController::class, 'switchRole'])->name('switch.role');
Route::post('/return-super', [ErpController::class, 'returnToSuperAccount'])->name('return.super');
Route::post('/reset-baseline', [ErpController::class, 'resetBaseline'])->name('reset.baseline');

// Cross-division access management (Admin/Owner), staff access requests & team notifications
Route::post('/access/grant', [AccessController::class, 'grant'])->name('access.grant');
Route::post('/access/{id}/revoke', [AccessController::class, 'revoke'])->name('access.revoke');
Route::post('/access/requests', [AccessController::class, 'submitRequest'])->name('access.request.submit');
Route::post('/access/requests/{id}/approve', [AccessController::class, 'approveRequest'])->name('access.request.approve');
Route::post('/access/requests/{id}/reject', [AccessController::class, 'rejectRequest'])->name('access.request.reject');
Route::post('/access/requests/{id}/cancel', [AccessController::class, 'cancelRequest'])->name('access.request.cancel');
Route::post('/notifications/read', [AccessController::class, 'markNotificationsRead'])->name('notifications.read');

// S-02: Form Customer dan Credit Terms
Route::get('/customers', [ErpController::class, 'customers'])->name('s02.customer');
Route::post('/customers', [ErpController::class, 'createCustomer'])->name('s02.create');

// S-03: Form Product dan Variant SKU
Route::get('/products', [ErpController::class, 'products'])->name('s03.product');
Route::post('/products', [ErpController::class, 'createProduct'])->name('s03.create');

// S-04: CRM Pipeline Kanban
Route::get('/crm', [ErpController::class, 'crm'])->name('s04.crm');
Route::post('/crm', [ErpController::class, 'createLead'])->name('s04.create');
Route::post('/crm/{id}/convert', [ErpController::class, 'convertLead'])->where('id', '.*')->name('s04.convert');

// S-05: Quotation dan Sales Order
Route::get('/sales', [ErpController::class, 'sales'])->name('s05.sales');
Route::post('/sales', [ErpController::class, 'createSalesOrder'])->name('s05.create');

// S-06: Stock Overview dan Reservation
Route::get('/inventory/stock', [ErpController::class, 'stock'])->name('s06.stock');

// S-07: Delivery Order dan Backorder
Route::get('/inventory/delivery', [ErpController::class, 'delivery'])->name('s07.delivery');
Route::post('/inventory/delivery/{do}/validate', [ErpController::class, 'validateDelivery'])->where('do', '.*')->name('s07.validate');

// S-08: Customer Invoice
Route::get('/invoicing/customer', [ErpController::class, 'invoices'])->name('s08.invoice');
Route::post('/invoicing/customer/{inv}/post', [ErpController::class, 'postInvoice'])->where('inv', '.*')->name('s08.post');

// S-09: Register Customer Payment
Route::get('/accounting/payments', [ErpController::class, 'payments'])->name('s09.payment');
Route::post('/accounting/payments', [ErpController::class, 'registerPayment'])->name('s09.register');

// S-10: RFQ dan Purchase Order
Route::get('/purchase/orders', [ErpController::class, 'purchase'])->name('s10.purchase');
Route::post('/purchase/orders', [ErpController::class, 'createPurchaseOrder'])->name('s10.create');

// S-11: Receipt dan Quality Acceptance
Route::get('/inventory/receipts', [ErpController::class, 'receipts'])->name('s11.receipt');
Route::post('/inventory/receipts/{rec}/validate', [ErpController::class, 'validateReceipt'])->where('rec', '.*')->name('s11.validate');

// S-12: Vendor Bill dan Three-Way Match
Route::get('/accounting/bills', [ErpController::class, 'bills'])->name('s12.bill');
Route::post('/accounting/bills/{bill}/post', [ErpController::class, 'postBill'])->where('bill', '.*')->name('s12.post');

// S-13: Journal Entries dan General Ledger
Route::get('/accounting/general-ledger', [ErpController::class, 'generalLedger'])->name('s13.ledger');

// S-14: Internal Stock Transfer
Route::get('/inventory/transfers', [ErpController::class, 'transfers'])->name('s14.transfer');
Route::post('/inventory/transfers', [ErpController::class, 'executeTransfer'])->name('s14.execute');

// S-15: Return dan Credit Note
Route::get('/sales/returns', [ErpController::class, 'returns'])->name('s15.return');
Route::post('/sales/returns', [ErpController::class, 'processReturn'])->name('s15.process');

// S-16: Employees dan Skill Matrix
Route::get('/employees', [ErpController::class, 'employees'])->name('s16.employee');
Route::post('/employees', [ErpController::class, 'createEmployee'])->name('s16.create');

// S-17: Beauty Treatment Completion
Route::get('/studio/treatments', [ErpController::class, 'treatments'])->name('s17.treatment');
Route::post('/studio/treatments', [ErpController::class, 'createTreatment'])->name('s17.create');
Route::post('/studio/treatments/{trt}/complete', [ErpController::class, 'completeTreatment'])->where('trt', '.*')->name('s17.complete');

// S-18: Operational Dashboard dan Audit
Route::get('/accounting/dashboard', [ErpController::class, 'dashboard'])->name('s18.dashboard');
Route::post('/demo/run-scenario', [ErpController::class, 'runScenarioDemo'])->name('demo.run');

// S-19: Vendor Payment and AP Settlement
Route::get('/accounting/vendor-payments', [ErpController::class, 'vendorPayments'])->name('s19.vendor_payment');
Route::post('/accounting/vendor-payments', [ErpController::class, 'registerVendorPayment'])->name('s19.register');
