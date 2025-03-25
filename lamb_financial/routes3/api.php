<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->json([], 201);
    });

Route::group(['prefix'=>'purchases', 'namespace'=>'Purchases'], function () {

    /*
    Route::apiResource('provisions/{id_compra}/accounting-seat', 'ProvisionsAccountingSeatController',
        ['only' => ['store', 'show', 'destroy', 'update']]);
    */
    Route::post('provisions/{id_compra}/accounting-seat', 'ProvisionsAccountingSeatController@store');
    Route::get('provisions/{id_compra}/accounting-seat/{id_seat}', 'ProvisionsAccountingSeatController@show');
    Route::delete('provisions/{id_compra}/accounting-seat/{id_seat}', 'ProvisionsAccountingSeatController@destroy');
    Route::put('provisions/{id_compra}/accounting-seat/{id_seat}', 'ProvisionsAccountingSeatController@update');
    Route::get('provisions/{id_compra}/accounting-seat', 'ProvisionsAccountingSeatController@getListbByIdCompra');

    Route::get('provisions/{id_compra}/accounting-seat/valida-require-ctacte/{id_cuentaaasi}', 'ProvisionsAccountingSeatController@validarSiRequiereCtaCte');
    Route::post('provisions/{id_compra}/finalizar', 'ProvisionsController@storeFinalizar');
    Route::get('provisions/notes-credit-debit', 'ProvisionsController@getProvisionsForNotes'); // {id_proveedor}


    Route::get('provisions', 'ProvisionsController@index');
    Route::get('provisions/{id_provision}', 'ProvisionsController@show');
    Route::post('provisions', 'ProvisionsController@store');
    Route::delete('provisions/{id_provision}', 'ProvisionsController@destroy');
    Route::put('provisions/{id_provision}', 'ProvisionsController@update');


    Route::get('receipt-for-fees/suspensiones', 'ReceiptForFeesController@getSuspensionRenta');
    Route::post('receipt-for-fees/{id_receipt_for_fees}/finalizar', 'ReceiptForFeesController@storeFinalizar');
    Route::post('receipt-for-fees/suspensiones', 'ReceiptForFeesController@addSuspensionRenta');
    Route::get('receipt-for-fees/{id_receipt_for_fees}', 'ReceiptForFeesController@show');
    Route::put('receipt-for-fees/{id_receipt_for_fees}', 'ReceiptForFeesController@update');

    Route::get('receipt-for-fees', 'ReceiptForFeesController@index');
    Route::post('receipt-for-fees', 'ReceiptForFeesController@store');

    Route::get('reports/report-ple-view', 'ReportsController@getReportPleView');
    Route::get('reports/report-ple-txt', 'ReportsController@getReportPleTxt'); 
    
    Route::get('reports/report-purchases-summary', 'ReportsController@getReportComprasResumen');
    Route::get('reports/check-of-issue', 'ReportsController@getCheckOfIssue');

    Route::get('reports/fees-record', 'ReportsController@getFeesRecord');
    Route::get('reports/pdf-fees-record', 'ReportsController@getPdfFeesRecord');

    Route::get('reports/pdf-report-purchases', 'ReportsController@getPdfShoppingRecord');

    Route::get('reports/withholding-record', 'ReportsController@getWithholdingRecord');
    Route::get('reports/pdf-withholding-record', 'ReportsController@getPdfWithholdingRecord');

    Route::get('reports/account-status', 'ReportsController@getAccountStatus');
    Route::get('reports/account-status-detail', 'ReportsController@getAccountStatusDetail');
    Route::get('reports/test', 'ReportsController@pdfTest');

    Route::get('reports/pdf-voucher-cover-page', 'ReportsController@getPdfVoucherCoverPage');
    
    // Route::apiResource('', 'PurchasesController', ['only' => ['store']]);
});
Route::group(['prefix'=>'accounting', 'namespace'=>'Accounting\Setup'], function () {
    Route::get('funding', 'AccountingController@listFunding');
    Route::get('contabilizados', 'AccountingController@listVoucherContabilizados');
    Route::put('contabilizados/{id_compra}', 'AccountingController@updateVoucherContabilizados');
    Route::get('contabilizados/get-not-contabilizados-user', 'AccountingController@getNotContabilizadosUser');
});

/*
Route::middleware('auth:api')->group(['prefix' => 'provisions'],function() {
    Route::apiResource('' , 'Purchases\ProvisionsController', ['only' => ['store']]);
});
*/

// Route::apiResource('conta-monedas', 'Purchases\ProvisionsController', ['only' => ['index', 'show']]);
/*
Route::group(['prefix' => 'provisions'], function () {
    Route::apiResource('/' , 'Purchases\ProvisionsController', ['only' => ['store']]);
});
*/
Route::group(['prefix'=>'service/auth', 'namespace'=>'Auth'], function () {
    
    Route::post('login', 'LoginLambController@LoginOauthdjMovil');

});

Route::group(['prefix'=>'service', 'namespace'=>'HumanTalent'], function () {
    
    Route::post('entity', 'ServiceapiController@getEntity');
    
    Route::post('entity', 'ServiceapiController@getEntity');
    Route::post('anho', 'ServiceapiController@getAnho');
    Route::post('listdepto', 'ServiceapiController@listProcessTicket');
    Route::post('listempleado', 'ServiceapiController@listPaymentTracing');
    Route::post('verpdf', 'ServiceapiController@previapdf');
    Route::post('verpdffirmado', 'ServiceapiController@boletaFirmadoPDF');
    Route::post('listafirmar', 'ServiceapiController@listaPreviaWin');
    Route::post('showpdf', 'ServiceapiController@obtenerpdffirmar');
    Route::post('listaprocesar', 'ServiceapiController@listaprocesar');
    Route::post('enviarpdf', 'ServiceapiController@generarProceso');
    
    Route::post('firmarwin', 'ServiceapiController@firmarwin');
    Route::post('logfirma', 'ServiceapiController@logfirma');
    
});