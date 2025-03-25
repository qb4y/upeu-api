<?php

use Financial\Routes\Setup\TraitRegistryRoutes;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization,Origin, Content-Type, X-Auth-Token, X-XSRF-TOKEN');

Route::get('p', function () {
    return view('pdf.human-talent.boleta');
});

Route::get('/', function () {
    return view('welcome');
});

// Route::get('certificado', function () {

//     $pdf = app('dompdf.wrapper');
//     $contxt = stream_context_create([
//       'ssl' => [
//           'verify_peer' => FALSE,
//           'verify_peer_name' => FALSE,
//           'allow_self_signed' => TRUE,
//       ]
//   ]);

//   $pdf = \PDF::setOptions(['isHTML5ParserEnabled' => true, 'isRemoteEnabled' => true]);
//   $pdf->getDomPDF()->setHttpContext($contxt);

//     $pdf->loadView('pdf/treasury/donaciones/certificado' );

//       $pdf->setPaper('a4', 'landscape');
//       return $pdf->stream('archivo.pdf');


// });

Route::get('ttcpdf', 'Report\Accounting\ReportLegalController@test')->name('testpdf');
Route::get('ttcpdf-upn', 'Report\Accounting\ReportLegalController@testUPN')->name('testpdf');
// Route::get('ttcpdf-upn', 'Report\Accounting\ReportLegalController@libro_mayor_upn')->name('testpdf');

Route::get('testpdf', 'Orders\OrdersController@myOrdersPdf')->name('testpdf');

Route::get('socket', 'Payonline\McController@socket')->name('socket');

Route::get('exportexcel', 'HumanTalent\PaymentsController@exportexcel')->name('exportexcel');

//Route::resource('lamb','UserController');
//Route::get('pruebasDIR/{id_anho}/{mes}', 'HumanTalent\PaymentsController@directorioBoleta')->name('pruebasDIR');
//Route::get('pruebasCER', 'HumanTalent\PaymentsController@pruebas')->name('pruebasCER');
Route::get('envioRespuesta/{id_aplicacion}/{id_origen}', 'Payonline\VisaController@envioRespuesta')->name('envioRespuesta');

Route::get('envioRespuesta1/{id_aplicacion}/{id_origen}', 'Payonline\VisapaymentController@envioRespuesta')->name('envioRespuesta1');


Route::get('firma/validar', 'HumanTalent\PaymentsController@validar')->name('firma/validar');

Route::get('firma/leer', 'HumanTalent\SignatureControlle@leer')->name('firma/leer');

Route::get('capcha', 'HumanTalent\SignatureControlle@capcha')->name('capcha');
Route::get('capchaajax', 'HumanTalent\SignatureControlle@capchaajax')->name('capchaajax');

Route::get('vercertificado', 'HumanTalent\SignatureControlle@vercertificado')->name('vercertificado');
Route::get('vercrl', 'HumanTalent\SignatureControlle@vercrl')->name('vercrl');


Route::get('json', 'HumanTalent\SignatureControlle@listCertificate')->name('json');

//Route::post('recepcion', 'Payonline\VisaController@recepcion')->name('recepcion');


//Route::get('pruebasSMS', 'HumanTalent\PaymentsController@sendSMS')->name('pruebasSMS');


Route::group(['namespace' => 'Auth'], function () {

    Route::post('login', 'LoginLambController@login')->name('login');
    Route::post('valid-tokens-oauth', 'LoginLambController@validTokensOauth')->name('login');
    Route::post('valid-token-callback', 'LoginLambController@validTokenCallback')->name('login');


    Route::post('login_mobile', 'LoginLambController@login_mobile')->name('login_mobile');
    Route::post('user_modules', 'LoginLambController@userModules');
    Route::post('user_module_child', 'LoginLambController@userModuleChild');
    Route::post('usermodule', 'LoginLambController@userModuleOld'); // BORRAR para lamb antiguo
    Route::get('usermodule', 'LoginLambController@userModule'); // para lamb nuevo
    Route::get('usermodule/{id}', 'LoginLambController@userModuleChildren'); //no se usa comentarlo

    Route::post('usermodulechild', 'LoginLambController@userModuleChildren');
    Route::get('resetPassord/{email}', 'LoginLambController@resetPasswordSendMail');
    Route::get('resetPassordValida/{token}', 'LoginLambController@resetPasswordValidaToken');
    Route::put('resetPassord', 'LoginLambController@resetPassword');
    //    Route::get('chulls', 'LoginLambController@prueba');
    //    Route::get('chullsita/{id}', function ($id) {
    //        //dd($id);
    //    });
    //NUEVA CONGIFURACION PARA LISTAR
    // Route::get('user-modules', 'LoginLambController@userModule')->middleware('resource:Auth/user-modules'); //LISTA DE ACCESOS
    Route::get('user-modules', 'LoginLambController@userModule'); //LISTA DE ACCESOS
    Route::get('user-modules/{id_modulo}/actions', 'LoginLambController@userModuleActions'); //ACCIONES POR ROL Y ACCESOS
    Route::get('user-modules/{id_modulo}/children', 'LoginLambController@userModuleChildren');
    Route::post('login-oauthdj-movil', 'LoginLambController@LoginOauthdjMovil');
    Route::get('user-modules-movil', 'LoginLambController@UserModuleMenuMovil');
    Route::get('version-app/{code}', 'LoginLambController@VersionAppMovil');
    Route::get('user-info-device', 'LoginLambController@UserInfoDevice');
    Route::get('datos-persona', 'LoginLambController@DatosPersona');
    Route::post('change-password-movil', 'LoginLambController@ChangePasswordMovil');
    Route::post('logout', 'LoginLambController@LogoutSession');

    // OTRAS APIS
});


Route::group(['prefix' => 'report', 'namespace' => 'Report\Accounting'], function () {
    Route::get('generateTxt', 'ReportController@generateTxt');
    Route::get('ple_diary', 'ReportController@pleDiary');
    Route::get('pdf_statement_account', 'ReportController@pdfStatementAccount');
    Route::get('download', 'ReportController@download');
    Route::get('debit', 'ReportController@debit')->name('reportTest');
    Route::get('ledge_assinet_pdf', 'ReportController@ledgeAssinetPdf');
    Route::post('statement_account', 'ReportController@statementAccount');
    Route::post('statement_account_summary', 'ReportController@statementAccountSummary');
    Route::post('checking_balance', 'ReportController@checkingBalance');
    Route::post('ledger', 'ReportController@ledger');
    Route::post('financial_indicators', 'ReportController@financialIndicators');
    Route::post('financial_statements', 'ReportController@financialStatements');
    Route::post('profit_loss_statement', 'ReportController@profitLossStatement');
    Route::post('ledge_assinet', 'ReportController@ledgeAssinet');
    Route::post('financial_analysis_department', 'ReportController@financialAnalysisDepartment');
    Route::post('statement_group_level1', 'ReportController@statementGroupLevel1');
    Route::post('statement_group_level2', 'ReportController@statementGroupLevel2');
    Route::post('statement_group_level3', 'ReportController@statementGroupLevel3');
    Route::post('statement_group', 'ReportController@statementGroup');
    Route::post('departmental_analysis', 'ReportController@departmentalAnalysis');
    Route::post('balance', 'TestController@balance');
    Route::post('balance_1', 'TestController@balance_1');
    Route::post('balance_2', 'TestController@balance_2');
    Route::post('balance_activo', 'TestController@balance_activo');
    Route::post('balance_res_gastos', 'TestController@balance_res_gastos');
    Route::post('balance_res_ingresos', 'TestController@balance_res_ingresos');
    Route::post('balance_resultados', 'TestController@balance_resultados');
    Route::post('balance_totales', 'TestController@balance_totales');
    Route::post('statement_upeu', 'ReportController@dataStatement');
    Route::post('statement_upeu_level', 'ReportController@statementUPeULevel');
    Route::post('diary_book_summary', 'ReportController@diaryBookSummary');
    Route::post('diary_book', 'ReportController@diaryBook');

    Route::get('diary-book-summary', 'ReportController@diaryBookSummary');
    Route::get('diary-book', 'ReportController@diaryBook');

    Route::get('statementaccount_entities_summary', 'ReportController@statementaccountEntitiesSummary');
    Route::get('statementaccount_entities', 'ReportController@statementaccountEntities');
    Route::get('files_send', 'ReportController@statementaccountEntitiesSummary');
    Route::get('files_view', 'ReportController@statementaccountEntitiesSummary');
    Route::get('debits_send', 'ReportController@statementaccountEntitiesSummary');
    Route::get('debits_receive', 'ReportController@statementaccountEntitiesSummary');
    // Nuevas Rutas

    //otro apis
});

Route::group(['prefix' => 'report', 'namespace' => 'Report\Management'], function () {
    // reportes gerenciales
    Route::get('departments-list/{id_empresa}/deptos', 'ManagementController@listDepartments');
    Route::get('checking-balance', 'ManagementController@getCheckingBalance');
    Route::get('checking-balance-pdf', 'ManagementController@getCheckingBalancePdf');
    Route::get('checking-balance-legal', 'ManagementController@getCheckingBalanceLegal');
    Route::get('checking-balance-legal-pdf', 'ManagementController@getCheckingBalanceLegalPdf');

    Route::get('deparment-by-entity', 'ManagementController@getDeparment');
    Route::get('deparment-by-entity-balance', 'ManagementController@getDeparmentBalance');

    Route::post('accounting-entries', 'ManagementController@uploadAccountingEntry');
    Route::post('accounting-entries-delete-file', 'ManagementController@deleteFileAccountingEntry');
    Route::get('accounting-entries', 'ManagementController@getAccountingEntries');
    Route::get('accounting-entries-pdf', 'ManagementController@getAccountingEntriesPdf');

    Route::get('list-accounts', 'ManagementController@getAccounts');
    Route::get('get-ctactes', 'ManagementController@getCtactes');

    Route::get('senior-accountant', 'ManagementController@getSeniorAccountant');
    Route::get('senior-accountant-pdf', 'ManagementController@getSeniorAccountantPdf');

    Route::get('financial-statements', 'ManagementController@getFinancialStatements');
    Route::get('financial-statements-pdf', 'ManagementController@getFinancialStatementsPdf');
    Route::get('financial-statements-legal', 'ManagementController@getFinancialStatementsLegal');
    Route::get('financial-statements-legal-pdf', 'ManagementController@getFinancialStatementsLegalPdf');
    // updateUserDownloadFile
    Route::get('account-status-lote', 'ManagementController@getAccountStatusLote');
    Route::get('account-status', 'ManagementController@getAccountStatus');
    Route::get('account-status-pdf', 'ManagementController@getAccountStatusPDF');
    Route::get('account-status-seats-notice-pdf', 'ManagementController@getAccountStatusSeatsNoticePDF');
    Route::post('account-status', 'ManagementController@uploadAccountStatus');
    Route::post('account-status-file-download', 'ManagementController@updateUserDownloadFile');
    Route::post('account-status-delete-file', 'ManagementController@deleteFileAccountStatus');
    Route::post('account-notice', 'ManagementController@addUpdateAccountNotice');

    Route::get('account-status-summary', 'ManagementController@getAccountStatusSummary');

    Route::get('type-entity', 'ManagementController@getTypeEntity');
    Route::get('file-type', 'ManagementController@getTipoArchivo');
    Route::post('file-type', 'ManagementController@addTipoArchivo');
    Route::put('file-type/{id_tipoarchivo}', 'ManagementController@editTipoArchivo');
    Route::delete('file-type/{id_tipoarchivo}', 'ManagementController@deleteTipoArchivo');

    Route::get('config-monthly-control', 'ManagementController@getConfigMonthlyControl');
    Route::post('config-monthly-control', 'ManagementController@addConfigMonthlyControl');
    Route::put('config-monthly-control/{id_archivo_mensual}', 'ManagementController@editConfigMonthlyControl');
    Route::delete('config-monthly-control/{id_archivo_mensual}', 'ManagementController@deleteConfigMonthlyControl');

    Route::get('file-group', 'ManagementController@getFileGroup');
    Route::post('file-group', 'ManagementController@addFileGroup');
    Route::put('file-group/{id_grupoarchivo}', 'ManagementController@editFileGroup');
    Route::delete('file-group/{id_grupoarchivo}', 'ManagementController@deleteFileGroup');

    Route::get('monthly-control', 'ManagementController@getMonthlyControl');
    Route::post('monthly-control-upload-file', 'ManagementController@uploadMonthlyControl');
    Route::post('monthly-control-delete-file', 'ManagementController@deleteFileMonthlyControl');
    Route::get('monthly-control-pdf', 'ManagementController@getMonthlyControlPdf');

    Route::get('travel-summary', 'ManagementController@getTravelSummary');
    Route::get('travel-summary-pdf', 'ManagementController@getTravelSummaryPDF');
    Route::get('travel-summary-excel', 'ManagementController@getTravelSummaryExcel');

    Route::get('budget-balance-summary', 'ManagementController@getBudgetBalanceSummary');
    Route::get('budget-balance', 'ManagementController@getBudgetBalance');
    Route::get('budget-balance-excel', 'ManagementController@getBudgetBalanceExcel');
    Route::get('budget-balance-pdf', 'ManagementController@getBudgetBalancePDF');
    Route::post('budget-balance-responsible', 'ManagementController@editResponsibleBudgetBalance');
    Route::get('budget-balance-responsible', 'ManagementController@getResponsible');

    Route::get('budget-balance-report', 'ManagementController@getBudgetBalanceReport');
    Route::get('budget-balance-report/responsibles', 'ManagementController@getBudgetBalanceResponsibles');
    Route::get('budget-balance-report/detail', 'ManagementController@getBudgetBalanceReportDetail');
    Route::get('budget-balance-report/general', 'ManagementController@getBudgetBalanceReportGeneral');
    Route::get('budget-balance-report/expenses', 'ManagementController@getBudgetBalanceReportExpenses');

    Route::get('financial-analysis', 'ManagementController@getFinancialAnalysis');

    Route::get('performance-report', 'ManagementController@getPerformanceReport');

    Route::get('monthly-control-summary', 'ManagementController@getMonthlyControlSummary');
    Route::get('monthly-control-summary-noscore', 'ManagementController@getMonthlyControlSummaryNoScore');
    Route::get('monthly-control-summary-rank', 'ManagementController@getMonthlyControlSummaryRanking');
    Route::get('expense-detail', 'ManagementController@getExpenseDetail');
    Route::get('income-detail', 'ManagementController@getIncomeDetail');
    Route::get('annual-budget-execution-pdf', 'ManagementController@getAnualBudgetExecutionPDF');
    Route::get('annual-budget-execution-excel', 'ManagementController@getAnualBudgetExecutionExcel');
    Route::get('budget-execution-pdf', 'ManagementController@getBudgetExecutionPDF');
    Route::get('budget-execution-excel', 'ManagementController@getBudgetExecutionExcel');
    Route::get('collaborator-year-month-search', 'ManagementController@getPersonsYearMonthSearch');

    Route::get('config-entity-depto-group', 'ManagementController@getEntityDeptoGroup');
    Route::post('config-entity-depto-group', 'ManagementController@addEntityDeptoGroup');
    Route::put('config-entity-depto-group/{id_grupo}', 'ManagementController@editEntityDeptoGroup');
    Route::delete('config-entity-depto-group/{id_grupo}', 'ManagementController@deleteEntityDeptoGroup');
    // obtener el array de deptos por entidad y grupo
    Route::get('config-entity-depto-group-get', 'ManagementController@getDeptoEntityGroup');
    Route::delete('config-entity-depto-group-anidado/{id_grupo}', 'ManagementController@deleteEntityGroupAndDeptos');

    Route::get('entity-group', 'ManagementController@getEntityGroup');
    Route::post('entity-group', 'ManagementController@addEntityGroup');
    Route::put('entity-group/{id_grupo}', 'ManagementController@editEntityGroup');
    Route::delete('entity-group/{id_grupo}', 'ManagementController@deleteEntityGroup');

    Route::get('travel-summary-by-functionary', 'ManagementController@getTravelSummaryByFunctionary');
    Route::get('travel-summary-by-functionary-pdf', 'ManagementController@getTravelSummaryByFunctionaryPDF');
    Route::get('travel-summary-by-functionary-excel', 'ManagementController@getTravelSummaryByFunctionaryExcel');

    Route::get('corporate-income-expenses', 'ManagementController@getCorporateIncomeExpenses');
    Route::get('corporate-income-expenses-pdf', 'ManagementController@getCorporateIncomeExpensesPdf');

    Route::get('config-monthly-control-year', 'ManagementController@getYearMonthControll');
    Route::post('config-monthly-control-copy', 'ManagementController@cvMonthControll');
    Route::get('cta-without-equivalences', 'ManagementController@ctaWithoutEquivalences');
    Route::post('import-calendar-monthly-control', 'ManagementController@importCalendarMonthlyControl');
});
Route::group(['prefix' => 'report', 'namespace' => 'Report\Treasury'], function () {
    Route::post('budget', 'TreasuryController@budget');
    Route::post('budget_upn', 'TreasuryController@budgetUPN');
    Route::post('pdfbudget_upn', 'TreasuryController@pdfbudgetUPN');
    Route::post('budget_upn_summary', 'TreasuryController@budgetUPN_summary');
    Route::post('budget_main', 'TreasuryController@budgetMain');
    Route::post('travels', 'TreasuryController@travels');
    Route::post('pdftravels', 'TreasuryController@pdftravels');
    Route::post('travels_summary', 'TreasuryController@travels_summary');
    Route::post('travels_detail', 'TreasuryController@travels_detail');
    Route::post('pdftravels_detail', 'TreasuryController@pdftravels_detail');
    Route::post('travels_detail_total', 'TreasuryController@travels_detail_total');
    Route::post('budget_detail', 'TreasuryController@budget_detail');
    Route::post('pdfbudgetUPN_detail', 'TreasuryController@pdfbudgetUPN_detail');
    Route::post('budget_detail_total', 'TreasuryController@budget_detail_total');

    Route::get('budget_detail_test', 'TreasuryController@budget_detail_test');


    Route::get('budgets-departments', 'TreasuryController@budgets_departments');
    Route::get('budgets-departments/{department}/details', 'TreasuryController@budgets_departments_details');

    Route::get('budgets-travels', 'TreasuryController@budgets_travels');
    Route::get('budgets-travels/{currentaccount}/details', 'TreasuryController@budgets_travels_details');

    Route::get('ren-my-vales', 'MyVouchersController@myValesList');
    //Nuevas Apis
    //Routes
});

Route::group(['prefix' => 'report', 'namespace' => 'Report\APS'], function () {
    Route::get('payroll', 'APSController@payroll');

    Route::post('summary_financial_statement', 'APSController@summaryFinancialStatement');

    Route::get('summary-financial-statement', 'APSController@summaryFinancialStatement');
    Route::get('detail-financial-statement', 'APSController@detailFinancialStatement');

    Route::post('personal_account_statement', 'APSController@personalAccountStatement');
    Route::post('list_personal', 'APSController@listPersonalAPS');

    Route::post('summary_help', 'APSController@summaryHelp');
    Route::post('prevision_social', 'APSController@previsionSocial');
    Route::post('payment_ticket', 'APSController@paymentTicket');
    Route::post('quinta_categoria', 'APSController@quintaCategoria');
    Route::post('exel', 'APSController@exel');

    Route::get('plame-jor', 'APSController@plameJOR');
    Route::get('plame-snl', 'APSController@plameSNL');
    Route::get('plame-rem', 'APSController@plameREM');
    Route::get('plame-hono', 'APSController@plameHonorarios');
    Route::get('plame-ps4', 'APSController@plamePS4');
    Route::get('plame-toc', 'APSController@plameTOC');
    Route::get('plame-for', 'APSController@plameFOR');

    //apis
});

Route::group(['prefix' => 'setup', 'namespace' => 'Setup'], function () {
    Route::get('getYear', 'SetupController@getYear');
    Route::get('getYear/{entity}', 'SetupController@getYearActivo');
    Route::get('getMonth', 'SetupController@getMonth');
    Route::get('getCompany', 'SetupController@getCompany');
    Route::get('getEntity', 'SetupController@getEntity');
    //Route::get('get_entity_by_type', 'SetupController@getEntityByType'); //ESTABA
    Route::get('get_entity_by_type', 'SetupController@getEntityByType'); //CAMBIADO SOLO ENTIDADES QUE EL USER TIENE ACCESO
    Route::get('get_entity_by_type_new', 'SetupController@getEntityByType_new');
    Route::get('getFund', 'SetupController@getFund');
    Route::get('getAccountingAccount', 'SetupController@getAccountingAccount');
    Route::get('getCurrentAccount', 'SetupController@getCurrentAccount');
    Route::post('get_department', 'SetupController@getDepartment');
    Route::post('get_restrictions', 'SetupController@getRestrictions');
    Route::post('get_type_current_accounts', 'SetupController@getTypeCurrentAccounts');
    Route::post('get_multiple_current_accounts', 'SetupController@getMultipleCurrentAccounts');
    Route::post('addRol', 'SetupController@addRol');
    Route::post('user_data', 'SetupController@user_data'); //desabilitar reemplazar
    Route::get('user-data', 'SetupController@userData'); //Este methodo queda
    Route::get('months-entities', 'SetupController@getMonthEntity');
    Route::get('entities-enterprise', 'SetupController@listEntitiesEnterprise');
    Route::get('my-companies/{id_empresa}/entities', 'SetupController@listEntitiesEnterpriseByUser');
    Route::get('my-companies/{id_empresa}/verify-all-entities', 'SetupController@listEntitiesEnterpriseVerifyAllEntities');
    Route::get('my-companies', 'SetupController@getCompanyByUser');
    Route::get('my-companies/{id_empresa}/{id_entidad}/deptos', 'SetupController@listDeptosEntitiesByUser');
    Route::get('my-companies/{id_empresa}/{id_entidad}/verify-all-deptos', 'SetupController@listDeptosEntitiesByUserVerifyAll');
    Route::resource('entity', 'EntityController');

    //configuración de representante legal
    Route::get('get_tipe_doc_re', 'SetupController@listTipoDocRepre');
    Route::get('list_depto', 'SetupController@listDepto');
    Route::get('doc_representative', 'SetupController@listDocRepresentative');
    Route::post('doc_representative', 'SetupController@addDocRepresentative');
    Route::get('doc_representative/{id_entideplegal}', 'SetupController@showDocRepresentative');
    Route::post('doc_representative/{id_entideplegal}', 'SetupController@editDocRepresentative');
    Route::delete('doc_representative/{id_entideplegal}', 'SetupController@deleteDocRepresentative');
    Route::get('doc_representative_filter', 'SetupController@DocRepresentativeFilters');

    Route::get('get-year-active-all', 'SetupController@getYearActivoAll');
    Route::get('get-year-active-all/{entity}', 'SetupController@getYearActivoAllByEntity');
    Route::get('years/year-active-by-users', 'SetupController@getYearActivoByIdEntidadUserSession');
    Route::get('procedures', 'SetupController@getProcedure');
    Route::get('arrangement', 'ArrangementController@getArrangement');
    Route::get('arrangement/{id_arreglo}/details', 'ArrangementController@getArrangementDetails');
    Route::get('arrangement-entries', 'ArrangementController@getArrangementEntries');
});


Route::group(['prefix' => 'setup', 'namespace' => 'Setup\Email'], function () {
    // configuracion de servidores de emails
    Route::get('config-emails', 'EmailController@listEmails');
    Route::get('config-emails/{id_entidad}/{alias}', 'EmailController@showEmailAlias');
    Route::get('config-emails/{id_email}', 'EmailController@showEmail');
    Route::post('config-emails', 'EmailController@addEmail');
    Route::put('config-emails/{id_email}', 'EmailController@updateEmail');
    Route::delete('config-emails/{id_email}', 'EmailController@deleteEmail');
});

Route::group(['prefix' => 'sale', 'namespace' => 'Sale\Operation'], function () {
    Route::post('create_operation', 'operationController@createOperation');
    Route::post('update_operation', 'operationController@updateOperation');
    Route::post('delete_operation', 'operationController@deleteOperation');
    Route::post('run_procedure', 'operationController@runProcedure');
});
Route::group(['prefix' => 'setup', 'namespace' => 'Setup\Modulo'], function () {
    //Route::post('rol', 'operationController@addRol');
    //Route::put('rol', 'operationController@updateRol');
    //Route::delete('rol', 'operationController@deleteRol');
    //Route::get('rol/{id}', 'operationController@showRol');
    //Route::get('rol', 'operationController@listRol');
    Route::get('modules', 'ModuloController@listModules');
    Route::get('modules/root', 'ModuloController@listModulesRoot');
    Route::get('modules/{id_modulo}', 'ModuloController@showModules');
    Route::post('modules', 'ModuloController@addModules');
    Route::put('modules/{id_modulo}', 'ModuloController@updateModules');
    Route::delete('modules/{id_modulo}', 'ModuloController@deleteModules');
    Route::get('modules/{id_modulo}/children', 'ModuloController@listModulesChildrens');

    Route::get('modules/{id_modulo}/actions', 'ModuloController@listModulesActions');
    Route::post('modules/{id_modulo}/actions', 'ModuloController@addModulesActions');
    Route::put('modules/{id_modulo}/actions/{id_accion}', 'ModuloController@updateModulesActions');
    Route::delete('modules/{id_modulo}/actions/{id_accion}', 'ModuloController@deleteModulesActions');
    // Route::post('my-entity-departments', 'ModuloController@addUsersEnterprises');
});
Route::group(['prefix' => 'setup', 'namespace' => 'Setup'], function () {
    Route::get('roles', 'RolController@listRoles');
    Route::get('roles/{id_rol}', 'RolController@showRoles');
    Route::post('roles', 'RolController@addRoles');
    Route::put('roles/{id_rol}', 'RolController@updateRoles');
    Route::delete('roles/{id_rol}', 'RolController@deleteRoles');
    Route::get('roles/{id_rol}/modules/{id_modulo}', 'RolController@listRolesModules');
    Route::post('roles/{id_rol}/modules', 'RolController@addRolModulo');
    Route::get('roles/{id_rol}/modules/{id_modulo}/actions', 'RolController@listRolesModulesActions');
    Route::post('roles/{id_rol}/modules/{id_modulo}/actions', 'RolController@addRolesModulesActions');


    Route::get('resource', 'RolController@listResources');
    Route::get('resource/{id_resource}', 'RolController@showResources');
    Route::post('resource', 'RolController@addResources');
    // apsi news
    Route::get('beca-rol/beca', 'RolController@lisTbecas');
    Route::get('beca-rol', 'RolController@becaRol');
    Route::delete('beca-rol/{id_tipo_requisito_beca}/{id_rol}', 'RolController@deleteBecaRol');
    Route::post('beca-rol', 'RolController@addBecaRol');
    Route::put('beca-rol/{id_rol}', 'RolController@updateBecaRol');
});

Route::group(['prefix' => 'setup', 'namespace' => 'Setup\Organization'], function () {
    Route::get('organization', 'OrganizationController@getInfoOrganization'); //listOrganization
    Route::get('area-authorized', 'OrganizationController@listAreaSedeArea');
    Route::post('area-authorized', 'OrganizationController@addAreaSedeArea');
    Route::get('organization/{id_org}/managers', 'ResponsableController@listResponsables');
    Route::post('organization/{id_org}/managers', 'ResponsableController@createAreaResponsables');
    Route::get('organization/{id_org}/managers/{id_responsable}', 'ResponsableController@getAreaResponsable');
    Route::put('organization/{id_org}/managers/{id_responsable}', 'ResponsableController@updateAreaResponsable');
    Route::delete('organization/managers/{id_responsable}', 'ResponsableController@deleteResponsables');
    Route::get('workers', 'ResponsableController@findWorker');
    Route::get('area-type', 'OrganizationController@listTypeOrganization');
    Route::post('organization', 'OrganizationController@createOrganization');
    Route::get('organization/{id_org}', 'OrganizationController@showOrganization');
    Route::put('organization/{id_org}', 'OrganizationController@updateOrganization');
    Route::delete('organization/{id_org}', 'OrganizationController@deleteOrganization');
    Route::post('organization/copy', 'OrganizationController@copyOrganization');
    Route::post('sede-area', 'SedeAreaController@createSedeArea');
    Route::get('sede-area/{id_sedearea}', 'SedeAreaController@getSedeArea');
    Route::put('sede-area/{id_sedearea}', 'SedeAreaController@updateSedeArea');
    Route::delete('sede-area/{id_sedearea}', 'SedeAreaController@deleteSedeArea');
    Route::get('sede-area-search', 'SedeAreaController@findSedeArea');
    Route::get('nivel-gestion', 'OrganizationController@listNivelGestion');
    Route::get('sede', 'OrganizationController@listSede');
    Route::get('deparments', 'OrganizationController@searchDepartment');
    Route::get('search-people', 'OrganizationController@searchPeople');
    //    Route::delete('roles/{id_rol}', 'RolController@deleteRoles');
    Route::get('areas-orders', 'OrganizationController@listAreasOrders');
    Route::get('areas-orders-to', 'OrganizationController@listAreasOrdersTo');
    Route::post('areas-orders', 'OrganizationController@CreateOrUpdateAreaOrder');
    Route::get('areas', 'OrganizationController@listAreas');
    Route::get('mis-areas', 'OrganizationController@misAreas');
    Route::resource('headquarter-area', 'HeadQuarterAreaController');
    Route::resource('entity-areas', 'AreaController');
});

Route::group(['prefix' => 'setup', 'namespace' => 'Setup\Person'], function () {
    Route::get('reniec-persons', 'PersonController@getDataReniec');
    Route::get('sunat-persons', 'PersonController@getDataSunat');
    Route::post('natural-persons', 'PersonController@addNaturalPerson');
    Route::post('legal-persons', 'PersonController@addLegalPerson');
    Route::resource('legal-person', 'LegalPersonController');
    Route::get('natural-persons', 'PersonController@listNaturalPersons');

    Route::get('legal-persons', 'PersonController@listLegalPersons');

    Route::get('legal-persons/and-natural-with-ruc', 'PersonController@listLegalPersonsAndNaturalWithRuc');
    Route::get('legal-persons/and-natural', 'PersonController@lisLegalPersonsAndNatural');

    Route::get('natural-persons/with-ruc', 'PersonController@listNaturalPersonsWithRuc');

    Route::get('persons/{id_persona}/bank-accounts', 'PersonController@listPersonBankAccounts');

    Route::get('document-types', 'PersonController@listDocumentType');
    Route::get('civil-status-types', 'PersonController@listCivilStatustType');
    Route::get('countries', 'PersonController@listCountry');
    Route::get('economic-activity-types', 'PersonController@listEconomicActivityType');

    Route::get('students-persons', 'PersonController@listStudentsPersons');
    Route::get('worker-persons', 'PersonController@showWorkerPersons');
    Route::post('persons-bank-account', 'PersonController@addPersonsBankAccount');
    Route::get('persons-bank-account', 'PersonController@listPersonsBankAccount');
    Route::post('users-image', 'PersonController@addUsersImage');
    Route::get('global-person', 'PersonController@SearchGlobalPerson');
});
Route::group(['prefix' => 'setup', 'namespace' => 'Setup\Modulo'], function () {
    //Route::get('modulo', 'ModuloController@listModulo');no se usa
    //Route::get('modulo/{id}', 'ModuloController@showModulo');
    Route::post('modulopost', 'ModuloController@showModulo');
    Route::post('modulo', 'ModuloController@addRolModulo');
    Route::get('persons', 'ModuloController@searchPerson');
    Route::post('createuser', 'ModuloController@createUser');
    Route::post('assignuserrol', 'ModuloController@assignUserRol');
    Route::post('showuserrol', 'ModuloController@showUserRol');
    Route::get('showuser', 'ModuloController@showUser');
    Route::get('edituser/{id}', 'ModuloController@verUser');
    Route::post('editusuario', 'ModuloController@editUser');
    Route::post('userdepto', 'ModuloController@userDepto');

    Route::get('plan_cta_enterprise', 'ModuloController@plan_ctas_enterprise');

    //Route::get('plan_cta_enterprise', 'ModuloController@plan_ctas_enterprise');

    Route::post('entitydepto', 'ModuloController@entityDepto');
    Route::get('entitydepto', 'ModuloController@entityDepartamento');

    Route::get('users', 'ModuloController@listUser');
    Route::post('users', 'ModuloController@createUser');
    Route::get('users/{id}', 'ModuloController@verUser');
    Route::patch('users/{id}', 'ModuloController@editUser');
    Route::get('users/{id}/roles', 'ModuloController@showUserRol');
    Route::post('users/{id}/roles', 'ModuloController@assignUserRol');
    Route::post('users/{id}/depto', 'ModuloController@assignUserDepto');
    Route::get('users/{id}/depto', 'ModuloController@userDepto');
    Route::get('profiles', 'ModuloController@listPerfil');
    Route::patch('users/{id}/depto/{id_depto}', 'ModuloController@UserDeptoDefault');
    Route::get('users/{id}/depto-parents', 'ModuloController@userDeptoParentAsigando');
    Route::get('users/{id}/deptos-parents', 'ModuloController@usersDeptosParents');
    Route::get('themes', 'ModuloController@listThemes');
    Route::get('users-themes', 'ModuloController@showUsersThemes');
    Route::post('users-themes', 'ModuloController@addUsersThemes');
    Route::get('users-entities', 'ModuloController@listUsersEntities');
    Route::get('my-entities', 'ModuloController@listMyEntities');
    Route::get('my-department', 'ModuloController@listMyDepartment');
    Route::post('my-entities-department', 'ModuloController@addUsersEnterprises');
    Route::get('my-entities-department', 'ModuloController@showUsersEnterprises');
    Route::get('my-department-childrens', 'ModuloController@listMyDepartmentsChildrens');
    Route::get('departments-childrens', 'ModuloController@listDepartmentsChildrens');
    Route::get('departments-entity', 'ModuloController@listEntityDepartments');

    //servicios
    Route::get("services/service-init", "ModuloController@listServicesByUser");
    Route::post("services/service-user", "ModuloController@servicesByUser");
});
Route::group(['prefix' => 'accounting', 'namespace' => 'Accounting\Setup'], function () {
    Route::get('config-vouchers', 'AccountingController@listConfigVoucher');
    Route::get('config-vouchers/{id_tipoasiento}', 'AccountingController@showConfigVoucher');
    Route::post('config-vouchers', 'AccountingController@addConfigVoucher');
    Route::post('config-vouchers/{id_entidad}/clone-config-voucher-by-entidad', 'AccountingController@cloneConfigVoucherByEntidad');
    Route::put('config-vouchers', 'AccountingController@updateConfigVoucher');
    Route::delete('config-vouchers/{year}/{entity}/{depto}/{id_tipoasiento}', 'AccountingController@deleteConfigVoucher');
    Route::get('tipo-asientos', 'AccountingController@listTipoAsiento');
    Route::get('tipo-asientos-voucher', 'AccountingController@showTipoAsientoVoucher');
    Route::get('depto-parents/{entity}', 'AccountingController@listDeptoParent');
    Route::get('depto-parents-sesion', 'AccountingController@listDeptoParentSesion');
    Route::get('tipo-comprobantes', 'AccountingController@listTipoComprobante');
    Route::get('tipo-plan', 'AccountingController@listTipoPlan');

    Route::get('type-vouchers', 'AccountingController@listTypeVoucher');
    Route::get('type-money', 'AccountingController@listTypeMoney');
    Route::get('type-restriction', 'AccountingController@listTypeRestriction');
    Route::get('type-igv', 'AccountingController@listTypeIGV');

    Route::get('config-periodos-status', 'AccountingController@showPeriodos');
    Route::post('config-periodos', 'AccountingController@addPeriodos');
    Route::get('config-periodos', 'AccountingController@listPeriodosMeses');
    Route::put('config-periodos', 'AccountingController@updatePeriodoMes');
    Route::put('config-periodos-changestatus', 'AccountingController@updatePeriodoChangeStatus');

    Route::get('automatic-vouchers', 'AccountingController@validarVoucherAutomatico');
    Route::get('vouchers', 'AccountingController@listVoucher');
    Route::delete('vouchers/{id_voucher}', 'AccountingController@deleteVoucher');
    Route::get('my-vouchers', 'AccountingController@listMyVoucher');
    Route::delete('my-vouchers/{id_voucher}', 'AccountingController@deleteMyVoucher');
    Route::get('vouchers/{id_voucher}', 'AccountingController@showVoucher');
    Route::post('vouchers', 'AccountingController@addVoucher');
    Route::patch('vouchers/{id_voucher}', 'AccountingController@updateVoucher');
    Route::put('vouchers/{id_voucher}', 'AccountingController@editVoucher');
    Route::get('reports/vouchers', 'AccountingController@listVoucherModules');
    Route::get('reports/my-vouchers', 'AccountingController@listMyVoucherModules');
    Route::post('vouchers-purchases', 'AccountingController@addVoucherPurchases');
    Route::get('aasinet-my-vouchers', 'AccountingController@listVoucherModulesAasinet');

    Route::get('reports/vouchers-all', 'AccountingController@listVoucherModulesAll');
    Route::get('reports/vouchers-inventories', 'AccountingController@listVoucherMoveInventories');

    //--
    Route::get('my-printing-documents', 'AccountingController@myListDocumentoImpresion');

    Route::get('printing-documents', 'AccountingController@listDocumentoImpresion');
    Route::get('printing-documents/{id_documento}', 'AccountingController@showDocumentoImpresion');
    Route::post('printing-documents', 'AccountingController@addDocumentoImpresion');
    Route::put('printing-documents/{id_documento}', 'AccountingController@updateDocumentoImpresion');
    Route::patch('printing-documents/{id_documento}', 'AccountingController@updateDocumentoImpresionAtrr');
    Route::delete('printing-documents/{id_documento}', 'AccountingController@deleteDocumentoImpresion');

    Route::get('printing-documents-details', 'AccountingController@listDocumentoImpresionDetails');
    Route::get('printing-documents-details/{id_docdetalle}', 'AccountingController@showDocumentoImpresionDetails');
    Route::post('printing-documents-details', 'AccountingController@addDocumentoImpresionDetails');
    Route::put('printing-documents-details/{id_docdetalle}', 'AccountingController@updateDocumentoImpresionDetails');
    Route::delete('printing-documents-details/{id_docdetalle}', 'AccountingController@deleteDocumentoImpresionDetails');
    Route::get('printing-documents/{id_docip}/test-print', 'PrintController@printDocument');

    Route::get('printing-documents-points', 'PrintController@listDocumentsPointsPrints');
    Route::get('printing-documents-points/{id_docip}', 'PrintController@showDocumentsPointsPrints');
    Route::post('printing-documents-points', 'PrintController@addDocumentsPointsPrints');
    Route::put('printing-documents-points/{id_docip}', 'PrintController@updateDocumentsPointsPrints');
    Route::delete('printing-documents-points/{id_docip}', 'PrintController@deleteDocumentsPointsPrints');
    Route::post('printing-documents-points-users', 'PrintController@addDocumentsPointsPrintsUsers');

    Route::get('tipo-grupo-cuentas', 'TipoGrupoContaController@listTipoGrupoContas');

    Route::get('plan-accounting-enterprise/pcge/v2', 'AccountingController@planAccountingEnterprisePcgeV2');

    //export pdf
    Route::get('plan-accounting-enterprise-export-pdf', 'AccountingController@getPCGEExportPdf'); //general
    Route::get('plan-accounting-enterprise-export-pcge', 'AccountingController@getexportToPdfPCGEaPCD'); //tab
    Route::get('plan-accounting-enterprise-export-pcd', 'AccountingController@getexportToPdfPCDaPCGE'); //tab
    Route::post('plan-accounting-enterprise/import-pcge','AccountingController@storeImportPCGE');//import add
    Route::post('plan-accounting-enterprise/import-pcd','AccountingController@storeImportPCD');//import add


    Route::put('plan-accounting-enterprise/v2/{id_cuentaempresarial}', 'AccountingController@updatePlanAccountingEnterpriseV2');
    Route::delete('plan-accounting-enterprise/v2/{id_cuentaempresarial}', 'AccountingController@deletePlanAccountingEnterpriseV2');

    Route::get('plan-accounting-enterprise/pcd/v2', 'AccountingController@planAccountingEnterprisePcdV2');

    Route::get('plan-accounting-enterprise/v2/equivalent/{id_cuentaempresarial}', 'AccountingController@listPlanAccountingEquivalentv2');
    Route::post('plan-accounting-enterprise/v2/equivalent/{id_cuentaempresarial}', 'AccountingController@addPlanAccountingEquivalentV2');
    Route::put('plan-accounting-enterprise/v2/equivalent/{id_cuentaempresarial}', 'AccountingController@updatePlanAccountingEquivalentV2');
    Route::delete('plan-accounting-enterprise/v2/equivalent/{id_cuentaempresarial}/{id_anho}/{id_empresa}/{id_tipoplan}', 'AccountingController@deletePlanAccountingEquivalentV2');
    Route::get('conta-empresas', 'AccountingController@getContaEmpresasV2');

    // Route::pust('plan-accounting-enterprise/v2/equivalent/{id_cuentaempresarial}/{}', 'AccountingController@addPlanAccountingEquivalentV2');
    // Route::get('plan-accounting-enterprise/v2/equivalent/{id_cuentaempresarial}', 'AccountingController@planAccountingEnterpriseV2');
    // Route::get('plan-accounting-enterprise/v2/{id_cuentaempresarial}/equivalent', 'AccountingController@planAccountingEnterpriseV2');


    Route::get('plan-accounting-enterprise', 'AccountingController@planAccountingEnterprise');
    Route::get('plan-accounting-enterprise/{id_cuentaempresarial}', 'AccountingController@showPlanAccountingEnterprise');
    Route::post('plan-accounting-enterprise', 'AccountingController@addPlanAccountingEnterprise');
    Route::put('plan-accounting-enterprise/{id_cuentaempresarial}', 'AccountingController@updatePlanAccountingEnterprise');
    Route::delete('plan-accounting-enterprise/{id_cuentaempresarial}', 'AccountingController@deletePlanAccountingEnterprise');
    Route::get('plan-accounting-denominational-search', 'AccountingController@listPlanAccountingDenominationalSearch');
    Route::get('plan-accounting-denominational-search-v2', 'AccountingController@listPlanAccountingDenominationalSearchV2');

    Route::get('denominational-account', 'AccountingController@listDenominationalAccount');
    Route::get('plan-accounting-denominational/{id_tipoplan}', 'AccountingController@listPlanAccountingDenominational');

    Route::get('plan-accounting-equivalent/{id_cuentaempresarial}', 'AccountingController@showPlanAccountingEquivalent');
    Route::post('plan-accounting-equivalent', 'AccountingController@addPlanAccountingEquivalent');
    Route::put('plan-accounting-equivalent/{id_cuentaempresarial}', 'AccountingController@updatePlanAccountingEquivalent');

    Route::get('accounting-entry', 'AccountingController@listAccountingEntry');
    Route::get('my-accounting-entry', 'AccountingController@listMyAccountingEntry'); // De mi entidad y departamento.

    Route::post('accounting-entry/{id_entidad}/clone-dynamics-by-entidad', 'AccountingController@cloneAccountingEntryByEntity');
    Route::post('accounting-entry/{id_dinamica}/clone-dynamics-by-id', 'AccountingController@cloneAccountingEntryByid');
    Route::get('accounting-entry/{id_dinamica}', 'AccountingController@showAccountingEntry');
    Route::post('accounting-entry', 'AccountingController@addAccountingEntry');
    Route::put('accounting-entry/{id_dinamica}', 'AccountingController@updateAccountingEntry');
    Route::patch('accounting-entry/{id_dinamica}', 'AccountingController@AccountingEntry');
    Route::delete('accounting-entry/{id_dinamica}', 'AccountingController@deleteAccountingEntry');
    Route::get('accounting-entry-validate', 'AccountingController@showAccountingEntryValidate');
    Route::get('accounting-recursivo-entry', 'AccountingController@listDepositoAccountingEntry');
    Route::put('accounting-recursivo-entry/{id_dinamica}', 'AccountingController@updateRecursivoAccountingEntry');
    Route::get('accounting-entry-chooses', 'AccountingController@listAccountingEntryChooses');

    Route::get('accounting-entry-details', 'AccountingController@listAccountingEntryDetails');
    Route::get('accounting-entry-details/{id_asiento}', 'AccountingController@showAccountingEntryDetails');
    Route::post('accounting-entry-details', 'AccountingController@addAccountingEntryDetails');
    Route::put('accounting-entry-details/{id_asiento}', 'AccountingController@updateAccountingEntryDetails');
    Route::patch('accounting-entry-details/{id_asiento}', 'AccountingController@AccountingEntryDetails');
    Route::delete('accounting-entry-details/{id_asiento}', 'AccountingController@deleteAccountingEntryDetails');


    Route::get('restriction-accounting', 'AccountingController@listRestriccionAccounting');
    Route::get('plan-accounting-enterprise-search', 'AccountingController@listPlanAccountingEnterpriseSearch');
    Route::get('plan-accounting-enterprise-search-v2', 'AccountingController@listPlanAccountingEnterpriseSearchV2');

    Route::get('list-moneda', 'AccountingController@listMoneda');
    Route::get('tipo-cambio', 'AccountingController@listTipoCambio');
    Route::post('tipo-cambio', 'AccountingController@addTipoCambio');
    Route::put('tipo-cambio/{anho}/{mes}/{moneda_main}/{moneda}', 'AccountingController@TipoCambio');
    Route::get('tipo-cambio/{fecha}', 'AccountingController@showTipoCambio');
    Route::put('tipo-cambio/{fecha}', 'AccountingController@updateTipoCambio');
    Route::delete('tipo-cambio/{fecha}/{id_moneda_main}/{id_moneda}', 'AccountingController@deleteTipoCambio');

    Route::get('indicador-lista', 'AccountingController@listIndicador');
    Route::get('seat-depto-unico', 'AccountingController@seatDeptoUnico');
    Route::get('seat-ctacte-unico ', 'AccountingController@seatCtaCteUnico');

    Route::get('asiento-depto-lista', 'AccountingController@listDeptoAsientoAccounting');
    Route::get('asiento-ctacte-lista', 'AccountingController@listCtaCteAsientoAccounting');

    Route::get('ctacte-accounting', 'AccountingController@listCtaCteAccounting');
    Route::get('ctacte-accounting-v2', 'AccountingController@listCtaCteAccountingV2');
    Route::get('checking-account', 'AccountingController@listCheckingAccount');
    Route::get('current-accounts', 'AccountingController@listCurrentAccounts');
    Route::get('ctacte-accounting-search', 'AccountingController@listCtaCteAccountingSearch');

    Route::get('group-level-depto', 'AccountingController@listEntityDepto');
    Route::get('group-level-parent', 'AccountingController@listLevelParent');

    Route::get('group-level', 'AccountingController@listGroupLevel');
    Route::post('group-level', 'AccountingController@addGroupLevel');
    Route::put('group-level/{id_nivel}', 'AccountingController@updateGroupLevel');
    Route::delete('group-level/{id_nivel}', 'AccountingController@deleteGroupLevel');

    Route::get('group-level-details', 'AccountingController@listGroupLevelDetails');
    Route::post('group-level-details', 'AccountingController@addGroupLevelDetails');

    Route::get('group-account', 'AccountingController@listGroupAccount');
    Route::post('group-account', 'AccountingController@addGroupAccount');
    Route::delete('group-account-v2/{id_cuenta}', 'AccountingController@deleteGroupAccountV2');
    Route::post('group-account-details', 'AccountingController@addGroupAccountDetails');
    Route::post('group-account-details-v2', 'AccountingController@addGroupAccountDetailsV2');
    Route::post('group-account-details-cte-v2', 'AccountingController@addGroupAccountDetailsCteV2');
    //Route::get('ctacte-accounting-group', 'AccountingController@listCtaCteAccountingGroup');
    Route::get('group-account-details-v2', 'AccountingController@listGroupAccountDetailsV2');
    Route::get('group-account-details', 'AccountingController@listGroupAccountDetails');
    Route::get('group-account-details-cte', 'AccountingController@listGroupAccountCTE');
    Route::get('group-account-details-cte-v2', 'AccountingController@listGroupAccountCteV2');
    Route::delete('group-account-details/{id_cdetalle}', 'AccountingController@deleteGroupAccountDetails');
    Route::delete('group-account-details-v2/{id_cta_cte}', 'AccountingController@deleteCteByIdV2');

    Route::get('type-arrangements', 'AccountingController@listTypeArrangements');
    Route::get('arrangements', 'AccountingController@listArrangements');
    Route::post('arrangements', 'AccountingController@addArrangements');
    Route::put('arrangements/{id_arreglo}/multiple', 'AccountingEntryController@updateMultipleAccountingEntry');

    Route::get('external-system', 'AccountingController@listExternalSystem');
    Route::get('external-system/{id_sistemaexterno}', 'AccountingController@showExternalSystem');
    Route::post('external-system', 'AccountingController@addExternalSystem');
    Route::put('external-system/{id_sistemaexterno}', 'AccountingController@updateExternalSystem');
    Route::delete('external-system/{id_sistemaexterno}', 'AccountingController@deleteExternalSystem');
    Route::get('external-system-seat', 'AccountingController@listExternalSystemSeat');
    //ASINET
    Route::get('seat-aasinet', 'AccountingController@listSeatAaasinet');
    Route::post('seat-aasinet-upload', 'AccountingController@uploadSeatAaasinet'); // se ha Cambiado a Post -  Para Mostrar los Mensajes

    Route::post('current-account-aasinet', 'AccountingController@createCurrentAccountAaasinet');

    Route::get('validate-vouchers', 'AccountingController@validateVoucher');

    //Listar Asitento
    Route::get('accounting-seat', 'AccountingController@listAccountingSeat');
    Route::get('accounting-seat/{id_asiento}', 'AccountingController@showAccountingSeat');
    Route::post('accounting-seat', 'AccountingController@addAccountingSeat');
    Route::put('accounting-seat/{id_asiento}', 'AccountingController@updateAccountingSeat');
    Route::delete('accounting-seat/{id_asiento}', 'AccountingController@deleteAccountingSeat');
    Route::get('accounting-entry/{id_sale}/sale', 'AccountingEntryController@getAccountingEntryBySale');
    // tes upload
    //Editar Arreglos desde modulos de Ventas y Compras
    Route::put('accounting-arrangement/{id_arreglo}', 'AccountingController@updateArrangement');

    //PROCESO DE TESIS
    Route::get('thesis-prices-lamb', 'AccountingEntryController@thesisPrices')->middleware('resource:accounting/thesis-prices-lamb');
    //SALDO ESTADO DE CUENTA
    Route::get('account-status-lamb', 'AccountingEntryController@accountStatus')->middleware('resource:accounting/account-status-lamb');

    //VOUCHER  CONTAWEB -  SUBIR ASSINET
    Route::get('cw-aasinet-my-vouchers', 'SeatCWController@listVoucherCWAasinet');
    Route::get('cw-seat-aasinet', 'SeatCWController@listSeatAaasinet');
    Route::post('cw-seat-aasinet-upload', 'SeatCWController@uploadSeatAaasinet');

    //VOUCHER  CONTAWEB-FJ -  SUBIR ASSINET
    Route::get('cw-fj-aasinet-my-vouchers', 'SeatCWFJController@listVoucherCWAasinet');
    Route::get('cw-fj-seat-aasinet', 'SeatCWFJController@listSeatAaasinet');
    Route::post('cw-fj-seat-aasinet-upload', 'SeatCWFJController@uploadSeatAaasinet');
});
Route::group(['prefix' => 'accounting/daily-book', 'namespace' => 'Accounting\Setup'], function () {
    Route::get('daily-book', 'AccountingController@getDailyBook');
    Route::get('daily-book-lotes', 'AccountingController@getDailyBookLotes');
    Route::get('daily-book-export', 'AccountingController@getDailyBookExport');
    Route::get('daily-book-export-pdf', 'AccountingController@getDailyBookExportPdf');
    Route::get('daily-book-export-pdf-upn', 'AccountingController@getDailyBookExportPdfUPN');
    Route::post('daily-book-calcular-pagination', 'AccountingController@calculatePaginationLibroDiario');
});
Route::group(['prefix' => 'accounting/ledger-book', 'namespace' => 'Accounting\Setup'], function () {
    Route::get('ledger-book', 'AccountingController@getLedgerBook');
    Route::get('ledger-book-lotes', 'AccountingController@getLedgerBookLotes');
    Route::get('ledger-book-export', 'AccountingController@getLedgerBookExport');
    Route::get('ledger-book-export-pdf', 'AccountingController@getLedgerBookExportPdf');
    Route::get('ledger-book-export-upn-pdf', 'AccountingController@getLedgerBookExportUpnPdf');
    Route::get('ledger-book-export-upn-pdf-totalizado', 'AccountingController@getLedgerBookExportUpnPdfTotalizado');
});

Route::group(['prefix' => 'accounting/operations', 'namespace' => 'Accounting\Setup'], function () {
    Route::get('accounting-entry', 'AccountingController@listAccountingEntryModule');
    Route::get('accounting-entry-residence', 'AccountingController@listAccountingEntryModuleResidence');

    Route::get('accounting-entry-almacen', 'AccountingController@listAccountingEntryModuleAlmacen');

    //Route::get('accounting-entry', 'AccountingController@listAccountingEntryModule');
    Route::post('vouchers-persons', 'AccountingController@assignVouchers');
    Route::get('vouchers-users', 'AccountingController@showVoucherModules');
});
Route::group(['prefix' => 'sales', 'namespace' => 'Sales'], function () {
    Route::get('getDireccion/{idPersona}', 'SalesController@getDireccion');
    Route::post('addDireccionPersona', 'SalesController@addDireccionPersona');
    Route::get('getTipoDireccion', 'SalesController@getTipoDireccion');
    Route::get('getTipoVia', 'SalesController@getTipoVia');
    Route::get('getTipoZona', 'SalesController@getTipoZona');
    Route::post('article', 'ArticleController@showArticle');

    Route::get('natural-legal-person', 'SalesController@listPerson');
    Route::post('sales', 'SalesController@addSales');
    Route::get('sales/{id_venta}/details', 'SalesController@listSalesDetails');
    Route::post('sales/{id_venta}/details/', 'SalesController@addSalesDetails');
    Route::post('sales/{id_venta}/details-seat/', 'SalesController@addSalesDetailsSeat');
    Route::put('sales/{id_vdetalle}/details-seat/', 'SalesController@updateSalesDetailsSeat');
    Route::post('sales/{id_venta}/sales-seat', 'SalesController@addSalesSeat');
    Route::get('sales/{id_venta}/sales-seat', 'SalesController@showSalesSeat');
    Route::get('type-sales', 'SalesController@listTypeSales');
    Route::get('types-sales', 'SalesController@listTypesSales');
    Route::delete('sales/{id_venta}/details/{id_vdetalle}', 'SalesController@deleteSalesDetails');
    Route::put('sales/{id_venta}/details/{id_vdetalle}', 'SalesController@updateSalesDetails');
    Route::get('sales/{id_venta}', 'SalesController@showSales');
    Route::get('sale/{id_venta}', 'SalesController@showSale');
    Route::get('sale/{id_venta}/details', 'SalesController@listSaleDetails');
    Route::put('sales/{id_venta}', 'SalesController@updateSales');
    Route::get('natural-legal-person/{id_persona}', 'SalesController@listPersonSucursal');
    Route::delete('sales/{id_venta}/details', 'SalesController@deleteSalesDetailsAll');
    Route::delete('sales/details-seat/{id_venta}', 'SalesController@deleteDetailSalesSeat');
    Route::post('sales/{id_venta}/finish-sales', 'SalesController@finishSalesStudent');

    Route::get('type-transactions', 'SalesController@listTypeTransaccion');
    Route::get('tipo-motivo-traslados', 'SalesController@listTipeMotivoTraslados');


    Route::post('type-transactions', 'SalesController@addTypeTransaccion');
    Route::put('type-transactions/{idtipotransaccion}', 'SalesController@updateTypeTransaccion');
    Route::delete('type-transactions/{idtipotransaccion}', 'SalesController@deleteTypeTransaccion');
    Route::get('type-transactions/mantenimiento/{idtipotransaccion}', 'SalesController@getMantenimientoTypeTransaccion');
    Route::get('type-transactions/mantenimiento', 'SalesController@listMantenimientoTypeTransaccion');
    Route::get('type-transactions/mantenimiento-all', 'SalesController@listMantenimientoAllTypeTransaccion');

    Route::post('conta-entidad-transactions', 'SalesController@addContaEntidadTransactions');
    Route::delete('conta-entidad-transactions/{id_entidad}/{id_tipotransaccion}', 'SalesController@deleteContaEntidadTransactions');

    Route::get('natural-legal-person-show/{id_persona}', 'SalesController@showNaturalPerson');
    Route::get('my-legal-person', 'SalesController@listPerson');
    //POLITICAS DE PRECIOS
    Route::get('warehouses-prices', 'PoliticsController@listPrices');
    Route::post('warehouses-prices', 'PoliticsController@addPrices');
    Route::put('warehouses-prices', 'PoliticsController@updatePrices');
    Route::get('types-politics', 'PoliticsController@listTypePolitics');
    Route::get('warehouses-politics', 'PoliticsController@listPolitics');
    Route::get('warehouses-politics/{id_politica}', 'PoliticsController@showPolitics');
    Route::post('warehouses-politics', 'PoliticsController@addPolitics');
    Route::put('warehouses-politics/{id_politica}', 'PoliticsController@updatePolitics');
    Route::delete('warehouses-politics/{id_politica}', 'PoliticsController@deletePolitics');
    Route::get('warehouses-politics-prices', 'PoliticsController@listPoliticsPrices');
    Route::post('warehouses-politics-prices', 'PoliticsController@addPoliticsPrices');
    Route::put('warehouses-politics-prices', 'PoliticsController@updatePoliticsPrices');
    Route::post('warehouses-prices-all', 'PoliticsController@addPricesAll');

    //CATEGORIAS DE CLIENTES
    Route::get('warehouses-politics-persons', 'PoliticsController@listPoliticsPersons');
    Route::post('warehouses-politics-persons', 'PoliticsController@addPoliticsPersons');
    Route::patch('warehouses-politics-persons', 'PoliticsController@updatePoliticsPersons');
    Route::put('warehouses-politics-persons/{id_politica}', 'PoliticsController@updatePoliticsPersonsAll');

    // SEHS VENTAS DE MERCADRÍAS SIN STOCK.
    Route::get('sales-without-stock/search-articles', 'SalesSehsController@searchArticulosWithoutStock');
    Route::get('sales-without-stock/{id_venta}/details-finazado', 'SalesSehsController@listSalesDetailsDeVentaFinalizada');

    // SEHS VENTAS DE MERCADRÍAS
    Route::get('sales-sehs/search-articles', 'SalesSehsController@searchArticulos');
    Route::post('sales-sehs', 'SalesSehsController@addAndUpdateSales');
    Route::put('sales-sehs/{id_venta}', 'SalesSehsController@addAndUpdateSales');
    Route::post('sales-sehs/{id_venta}/details', 'SalesSehsController@addSalesDetails');
    Route::put('sales-sehs/{id_venta}/details/{id_vdetalle}', 'SalesSehsController@updateSalesDetails');
    // Route::delete('sales-sehs/{id_venta}/details/{id_vdetalle}', 'SalesSehsController@deleteSalesDetails');
    Route::post('sales-sehs/{id_venta}/finalizar', 'SalesSehsController@finalizarSales');
    Route::get('sales-sehs', 'SalesSehsController@listMySalesSehs');
    Route::get('sales-sehs/anticipadas', 'SalesSehsController@listMySalesAnticipadas');
    Route::get('sales-sehs/anticipadas-to-search', 'SalesSehsController@listMySalesAnticipadasToSearch');
    Route::get('sales-sehs/saldo-anticipadas', 'SalesSehsController@listSaldoVentasAnticipadas');

    // SEHS DESPACHO DE VENTAS
    Route::post('sales-dispatchs', 'SalesSehsController@addSalesDispatchs');
    Route::get('sales-dispatchs', 'SalesSehsController@listSalesDispatchs');

    // SEHS NOTAS DE CREDITO O DEBITO
    Route::post('agree-notes-sehs', 'SalesNotesSehsController@addNotesSales');


    //VENTA DE ARTICULOS
    Route::get('products', 'SalesController@listSalesProducts');
    Route::post('sales-products', 'SalesController@addSalesProducts');
    Route::put('sales-products/{id_venta}', 'SalesController@updateSalesProducts');
    Route::post('sales-products/{id_venta}/details', 'SalesController@addSalesDetailsProducts');
    Route::put('sales-products/{id_venta}/details/{id_vdetalle}', 'SalesController@updateSalesDetailsProducts');
    //TRANSFERENCIAS
    Route::post('transfers', 'SalesController@addTransfers');
    Route::get('transfers/{id_transferencia}/details', 'SalesController@listTransfersDetails');
    Route::post('transfers/{id_transferencia}/details', 'SalesController@addTransfersDetails');
    Route::delete('transfers/{id_transferencia}/details/{id_tdetalle}', 'SalesController@deleteTransfersDetails');
    Route::put('transfers/{id_transferencia}', 'SalesController@updateTransfers');
    Route::get('transfers/{id_transferencia}/entry', 'SalesController@listTransfersEntry');
    Route::post('transfers/{id_transferencia}/entry', 'SalesController@addTransfersEntry');
    Route::delete('transfers/{id_transferencia}/entry/{id_tasiento}', 'SalesController@deleteTransfersEntry');
    Route::get('transfers', 'SalesController@listTransfers');
    Route::get('my-transfers', 'SalesController@listMyTransfers');
    Route::get('my-sales', 'SalesController@listMySales');
    Route::get('reprintTransfer/{id_transferencia}', 'SalesController@reprintTransfer');

    Route::get('transfers/{id_transferencia}/entry-asient', 'SalesController@listTransfersEntryAs');
    Route::get('transfers/{id_transferencia}/entry-asient-vnt', 'SalesController@listTransfersEntryAsVnt');
    Route::post('transfers/{id_transferencia}/entry-asient', 'SalesController@addTransfersEntryAs');
    Route::post('transfers/{id_transferencia}/entry-asient-vnt', 'SalesController@addTransfersEntryAsVnt');
    Route::post('transfers/{id_transferencia}/entry-asient-vnt-import', 'SalesController@addTransfersEntryAsVntImport');
    Route::post('transfers/import-excel-asiento', 'SalesController@addAsientosImportsExcelTranf');
    Route::delete('transfers/asiento/{id_vasiento}', 'SalesController@deleteAsientoTranf');
    Route::delete('transfers/asiento/{id_vasiento}/vnt', 'SalesController@deleteAsientoTranfVnt');
    Route::put('transfers/asiento/{id_vasiento}', 'SalesController@updateAsientoTranf');
    Route::put('transfers/sale-seat/{id_vasiento}', 'SalesController@updateSaleSeat');

    // Route::get('my-sales/anticipadas', 'SalesController@listMySalesAnticipadas');
    // Route::get('my-sales/anticipadas-to-search', 'SalesController@listMySalesAnticipadasToSearch');

    Route::get('my-notes', 'SalesController@listMyNotes');
    //VENTAS ARREGLOS
    Route::get('my-sales-arrangements', 'SalesController@listMySalesArrangements');
    Route::post('my-sales-arrangements/{id_venta}', 'SalesController@spCancelSales');
    Route::get('transfers-imports', 'SalesController@listTransfersImports');
    Route::post('transfers-imports', 'SalesController@addTransfersImports');
    Route::put('transfers-imports', 'SalesController@updateTransfersImports');
    Route::delete('transfers-imports', 'SalesController@deleteTransfersImports');

    Route::get('my-sales-arrangements/{id_venta}/{id_tipoorigen}', 'SalesController@showMySalesArrangements');
    // cambios
    Route::get('transfers-imports-test', 'SalesController@listTransfersImports');

    //Notas de Credito / Debito
    Route::get('types-notes', 'SalesController@listTypesNotes');
    Route::post('notes', 'SalesController@addNotes');
    Route::post('notes/{id_nota}/details', 'SalesController@addNotasDetails');
    Route::put('notes/{id_nota}', 'SalesController@updateNotes');
    Route::get('notes/{id_nota}/details', 'SalesController@listNotasDetails');
    Route::delete('notes/{id_nota}/details/{id_vdetalle}', 'SalesController@deleteNotesDetails');
    Route::get('voucher-dinamic-tools', 'SalesController@voucheDinamico');
    Route::get('depto-dinamic-tools', 'SalesController@listMyDepartmentSales');
    Route::get('search-serie', 'SalesController@searchSerie');
    Route::get('search-serie-numero', 'SalesController@searchSerieNumero');
    Route::get('list-venta-detalle', 'SalesController@listDetalleVenta');

    Route::post('agree-notes', 'SalesController@addNotesSales');

    Route::post('notes-cd-alumnos', 'SalesController@insertNotesStudent');
    Route::post('asiento-notes-alum', 'SalesController@insertAsiento');
    Route::get('asiento-notes-alum', 'SalesController@getAsientoAlumnoNotas');
    Route::delete('asiento-notes-alum/{id_vasiento}', 'SalesController@deleteAsientoAlumnoNotas');
    Route::put('asiento-notes-alum/{id_vasiento}', 'SalesController@updateAsientoAlumnoNotas');


    Route::post('finish-notes-alum', 'SalesController@finalizarDCNotasAlumnos');

    Route::post('import-excel-asiento', 'SalesController@addAsientosImportsExcel');


    Route::post('imprime-venta', 'SalesController@imprimeVenta');
    Route::post('imprime-venta-v2', 'SalesController@imprimeVentaV2');

    Route::get('direccion-venta', 'SalesController@getDirecionVenta');

    Route::post('seats-global', 'SalesController@addSeatGlobalSales');
    Route::get('seats-global', 'SalesController@listSeatsGlobal');
    Route::delete('seats-global/{id_vasiento}', 'SalesController@deleteSeatsGlobal');
    Route::put('seats-global/{id_vasiento}', 'SalesController@updateSeatsGlobal');
    Route::post('duplicate-seats-global', 'SalesController@duplicarSeatGlobalSales');

    Route::get('cod-bank', 'SalesController@expCodBank');

    //Vew Apsis Notas
    //==== Demos

    // CUENTAS POR COBRAR
    Route::get('accounts-receivable', 'SalesController@lisAccountsReceivable');
    Route::get('accounts-receivable-children', 'SalesController@lisAccountsReceivableChildren');
    Route::get('accounts-receivable-anticipos', 'SalesController@anticipos');
});
Route::group(['prefix' => 'sales/reports', 'namespace' => 'Sales'], function () {
    Route::get('sales', 'SalesController@salesBalances');
    Route::get('sales-advances', 'SalesController@salesBalancesAdvances');
    Route::get('sales-mov', 'SalesController@salesBalancesMov');
    Route::get('sales-mov-alumns', 'SalesController@salesBalancesMovAlumns');
    Route::get('sales-record', 'SalesController@salesRecord');
    Route::get('sales-accounting-entry', 'SalesController@salesAccountingEntry');
    Route::get('sales-details', 'SalesController@salesDetails');
    Route::get('sales-credit-personal', 'SalesController@creditPersonal');
    Route::get('sales-credit-personal-pdf', 'SalesController@creditPersonalPDF');
    Route::get('sales-family-top', 'SalesController@familiasVendidas');
    Route::get('sales-family-top-product', 'SalesController@productosFamiliasVendidas');
    Route::get('sales-family-top-product-pdf', 'SalesController@productosVendidasPDF');
    Route::get('months-top', 'SalesController@mesConMasVentas');

    Route::get('sales-record-pdf', 'SalesController@salesRecordPdf');
    // como jugando
    Route::get('advances-staff', 'SalesController@advancesStaff');

    Route::get('cajero-top', 'SalesController@cajeroTop');
    Route::get('cliente-top', 'SalesController@clienteTop');


    // COSTOS DE INVENTARIOS y PDF
    Route::get('inventories-costs', 'SalesController@listInventoriesCosts');
    Route::get('inventories-costs-pdf', 'SalesController@listInventoriesCostsPdf');

    //Resumen de Ventas Y PDF
    Route::get('sales-summary', 'SalesController@lisSalesSummary');
    Route::get('sales-summary-pdf', 'SalesController@lisSalesSummaryPdf');
    Route::get('sales-details-print', 'SalesController@lisSalesDetails');
    Route::get('sales-details-pdf', 'SalesController@lisSalesDetailsPdf');

    //EFACT - REPORTE
    Route::get('sales-series', 'SalesController@listSalesSeries');
    Route::get('sales-efac-sales', 'SalesController@showSaleEfac');
    Route::get('sales-efac', 'SalesController@listSalesEfac');
    Route::post('sales-send-efac', 'SalesController@sendSalesEfac');
    //mas apis

    //ESTADO DE CUENTA CLIENTES ALUMNOS
    Route::get('account-status', 'SalesController@listAccountStatus');
    Route::get('alumnos-portal', 'SalesController@studenAcademic');
    Route::post('account-status-pdf', 'SalesController@estadoCuentaPDF');
    Route::get('my-deposits', 'SalesController@showMyDeposits');

    Route::get('sales-mov/otros', 'SalesController@salesMovOtros');
    //// Vestas divversas
    Route::get('status-account-clients', 'SalesController@statusAccouentClients');
    Route::get('seats-status-account', 'SalesController@seatStatus');
    Route::get('seats-status-cero', 'SalesController@seatStatusTipoCero');

    // reportes sales
    Route::get('customer-account', 'Reports\CustomerAccountController@getCustomerAccounts');
});
Route::group(['prefix' => 'services', 'namespace' => 'Services'], function () {
    Route::get('tipo-cambio/{anho?}/{mes?}', 'ServicesController@TipoCambio');
    Route::post('upload-images', 'ServicesController@uploadImage');
    Route::post('create-file', 'ServicesController@createFile');
});

Route::group(['prefix' => 'treasury', 'namespace' => 'Treasury'], function () {
    //Route::get('deposit', 'BoxController@listPerson');
    //Route::post('deposit', 'BoxController@addSales');
    //Route::delete('sales/{id_venta}/details/{id_vdetalle}', 'SalesController@deleteSalesDetails');
    //Route::put('sales/{id_venta}/details/{id_vdetalle}', 'SalesController@updateSalesDetails');
    // Pagos varios
    Route::post('payments/to-small-box', 'ExpensesController@addPaymentsToSmallBox');
    Route::get('payments/to-small-box', 'ExpensesController@listPaymentsToSmallBox');
    Route::get('payments/to-small-box/sumary', 'ExpensesController@listPaymentsToSmallBoxSumary');
    Route::delete('payments/to-small-box/{id_payment}', 'ExpensesController@deletePaymentsToSmallBox');

    Route::get('payments-vouchers', 'ExpensesController@listPaymentsVoucher');
    Route::get('payments-vouchers/to-vales', 'ExpensesController@listPaymentsVoucherToVales');
    Route::get('payments', 'ExpensesController@listPayments');
    Route::get('payments/to-vales', 'ExpensesController@listPaymentsToVales');
    Route::get('payments/{id_pago}/details', 'ExpensesController@listPaymentsDetails');
    Route::get('payments/{id_vale}/vale', 'ExpensesController@listPaymentsVale');
    Route::get('payments-report/{id_vale}/vale', 'ExpensesController@listReportPaymentsVale');
    Route::delete('payments/{id_pago}/details/{id_detalle}', 'ExpensesController@deletePaymentsDetails');


    Route::get('payments/{id_pago}', 'ExpensesController@showPayments');
    Route::post('payments', 'ExpensesController@addPayments');
    Route::put('payments/{id_pago}', 'ExpensesController@updatePayments');
    Route::delete('payments/{id_pago}', 'ExpensesController@deletePayments');
    Route::post('payments/finish-payments', 'ExpensesController@finishPayments');
    Route::get('payments-expenses/{id_pgasto}', 'ExpensesController@showPaymentsExpenses');
    Route::get('payments-expenses/{id_pgasto}/seats', 'ExpensesController@showPaymentsExpensesSeat');
    Route::delete('payments-expenses/{id_pgasto}/seats/{id_gasiento}', 'ExpensesController@deletePaymentsExpensesSeat');

    Route::post('payments-expenses', 'ExpensesController@addPaymentsExpenses');
    Route::post('payments-expenses/upn', 'ExpensesController@addPaymentsExpensesUPN');
    Route::post('payments-providers', 'ExpensesController@addPaymentsProviders');
    Route::post('payments-providers/many', 'ExpensesController@addPaymentsProvidersMany');
    Route::post('payments-customers', 'ExpensesController@addPaymentsCustomers');
    Route::post('payments-expenses-seats/{id_pgasto}', 'ExpensesController@addPaymentsExpensesSeats');
    Route::get('payments-asientos-vale', 'ExpensesController@getPaymentEntrySeatVale');

    //Pagos Rendicion
    Route::post('payments-surrender', 'ExpensesController@addPaymentsSurrender');


    // Rendicion de vale como pago.
    Route::put('payments/{id_pago}/finalizar-rendicion-vale-upn', 'ExpensesController@finalizarRendicionValeUpn');


    Route::get('way-pay', 'IncomeController@listMedioPago')->middleware('resource:treasury/way-pay');
    Route::get('bank-account-type', 'IncomeController@listBanKAccountType');
    Route::get('financial-entities', 'IncomeController@listFinancialEntities');
    Route::get('financial-entities/{id_banco}', 'IncomeController@showFinancialEntities');
    Route::post('financial-entities', 'IncomeController@addFinancialEntities');
    Route::put('financial-entities/{id_banco}', 'IncomeController@updateFinancialEntities');
    Route::delete('financial-entities/{id_banco}', 'IncomeController@deleteFinancialEntities');


    Route::get('checkbooks', 'TreasuryController@listCheckbooks');
    Route::get('checkbooks/{id_chequera}', 'TreasuryController@showCheckbooks');
    Route::post('checkbooks', 'TreasuryController@addCheckbooks');
    Route::put('checkbooks/{id_chequera}', 'TreasuryController@updateCheckbooks');
    Route::delete('checkbooks/{id_chequera}', 'TreasuryController@deleteCheckbooks');


    Route::get('my-checkbooks', 'TreasuryController@listMyCheckbooks');
    Route::get('my-bank-accounts', 'TreasuryController@listMyBankAccounts');
    Route::get('my-accounts-by-bank', 'TreasuryController@showAccountsByBank');
    Route::get('type-tarjetas', 'TreasuryController@listTypeTarjetas');

    Route::get('card-type', 'IncomeController@listCardType');
    Route::get('deposit-type', 'IncomeController@listDepositType');
    Route::post('deposit', 'IncomeController@addDeposit');
    Route::post('deposit-student', 'IncomeController@addDepositStudent');
    Route::post('deposit-student-massive', 'IncomeController@addDepositStudentMassive');
    Route::get('deposit', 'IncomeController@cashRegister');
    Route::get('deposits-correct', 'IncomeController@depositsCorrect');
    Route::post('deposits-correct/{id_deposito}/confirm', 'IncomeController@depositsCorrectConfirm');
    Route::get('my-deposit', 'IncomeController@myCashRegister');
    Route::post('student-portal-voucher-deposit/massive-bank', 'IncomeController@addDepositStudentBank');
    Route::get('student-portal-voucher-deposit', 'IncomeController@voucherDepositsStudentsPortal');
    Route::put('student-portal-voucher-deposit/{id_dfile}', 'IncomeController@updateDepositsVouchesEstudents');
    Route::get('view-file', 'IncomeController@viewFileDepositos');
    Route::get('banks-students', 'IncomeController@listBank');
    Route::get('process-vou-dep/{id_dfile}', 'IncomeController@getPrrocess');
    Route::get('student-portal-voucher-deposit/voucher', 'IncomeController@getCajeroVouchers');
    Route::put('student-portal-voucher-deposit/net-refused/{id_dfile}', 'IncomeController@nextOrRefusedPaso');

    Route::get('deposit/resumen-caja', 'IncomeController@cashRegisterCajaPdf');
    Route::get('deposit/exportxlscaja', 'IncomeController@exportXlsCaja');

    //Route::get('my-bank-accounts-', 'TreasuryController@listMyBankAccounts');

    Route::get('deposit/{id_voucher}/voucher-depos-account', 'IncomeController@showVoucherDeposits');
    Route::get('deposit/{id_voucher}/voucher-depos-account-export', 'IncomeController@exportVoucherDeposits');
    Route::get('deposit/export-pdf', 'IncomeController@cashRegisterExportPdf');

    Route::get('bank-accounts', 'TreasuryController@listBankAccounts');
    Route::get('bank-accounts/{id_ctabancaria}', 'TreasuryController@showBankAccounts');
    Route::post('bank-accounts', 'TreasuryController@addBankAccounts');
    Route::put('bank-accounts/{id_ctabancaria}', 'TreasuryController@updateBankAccounts');
    Route::delete('bank-accounts/{id_ctabancaria}', 'TreasuryController@deleteBankAccounts');

    Route::get('vales-types', 'ExpensesController@listTypeVale');
    Route::get('vales', 'ExpensesController@listVales');
    Route::get('vales/{id_vale}', 'ExpensesController@showVale');
    Route::get('my-vales', 'ExpensesController@listMyVales');
    Route::get('my-vales/{id_vale}/detail-state', 'ExpensesController@showValeState');
    Route::post('my-vales', 'ExpensesController@addMyVale');
    Route::patch('my-vales/{id_vale}/authorize', 'ExpensesController@autorizaMyVale');
    Route::post('my-vales/{id_vale}/provision', 'ExpensesController@provisionarMyVale');
    Route::get('my-vales/clientes', 'ExpensesController@listValesClientes');
    Route::get('my-vales/cliente', 'ExpensesController@listValesCliente');
    Route::get('my-vales/vale-files/{id_vale}', 'ExpensesController@listValeFiles');
    Route::get('my-vales/files/{id_vale}', 'ExpensesController@showAllValeFile');
    Route::get('vales-proceso', 'ExpensesController@listValesProceso');
    Route::put('vales/{id_vale}', 'ExpensesController@autorizaVale');
    Route::patch('vales/{id_vale}', 'ExpensesController@autorizaVale');
    Route::get('my-vales/term-cond', 'ExpensesController@showTermCond');

    Route::post('vales/{id_vale}/pagar', 'ExpensesController@payVale');
    Route::post('vales/{id_vale}/deny', 'ExpensesController@denyVale');

    Route::get('vales/{id_vale}/deposits', 'ExpensesController@listValesDeposits');
    Route::delete('vales/{id_vale}/provision-anular', 'ExpensesController@deleteProvisionVale');
    Route::resource('voucher-cash', 'VoucherCash\VoucherCashController');
    Route::post('voucher-cash/deny', 'VoucherCash\VoucherCashController@denyVoucherCash');
    Route::resource('voucher-cash-file', 'VoucherCash\VoucherCashFileController');
    Route::get('authorize-vale-gasto', 'ExpensesController@listValeGastoAuthorize');
    Route::post('vale-gasto-seats', 'ExpensesController@addSeatValeGastoFile');
    Route::post('vale-gasto-transac-seats', 'ExpensesController@addSeatsTransValeGastoFile');
    Route::put('vale-gasto-seats/{id_vasiento}', 'ExpensesController@updateSeatValeGastoFile');
    Route::get('vale-gasto-seats/{id_vgasto}', 'ExpensesController@listSeatValeGastoFile');
    Route::delete('vale-gasto-seats/{id_vasiento}', 'ExpensesController@deleteSeatValeGastoFile');
    Route::put('authorize--refused-vale-gasto/{id_vgasto}', 'ExpensesController@autthorizeValeGastoFile');
    Route::get('gasto-athorizate-vale/{id_vale}', 'ExpensesController@gastosValeAutorizados');
    Route::get('importe-gasto-comprobante/{id_vale}', 'ExpensesController@importGastoComprobante');
    Route::get('estado-proceso-vale/{id_vale}', 'ExpensesController@estadoDelProcesosDelVale');
    Route::post('pago_gasto_asiento-vale', 'ExpensesController@addGastoVale');

    // Vales UPN
    Route::post('upn-vales', 'ExpensesController@addValeUPN');
    Route::get('upn-vales', 'ExpensesController@listValesUPN');
    Route::get('upn-vales/saldo', 'ExpensesController@listSaldoValesUPN');
    Route::get('upn-vales/a-rendir', 'ExpensesController@listValesARendirUPN');
    Route::get('upn-vales/{idVale}', 'ExpensesController@getValeUPN');
    // Route::post('upn-vales/{idVale}/rendir', 'ExpensesController@finalizarRendicionValeUPN');
    Route::delete('upn-vales/{idVale}', 'ExpensesController@deleteValeUPN');
    Route::put('upn-vales/{idVale}', 'ExpensesController@updateValeUPN');

    // Rendir vales con depósitos
    Route::post('upn-vales/{id_vale}/render-small-box', 'ExpensesController@addRendirValeWithSmallBoxDeposits');
    Route::post('upn-vales/{id_vale}/render-banks', 'ExpensesController@addRendirValeWithBanksDeposits'); // Trabajando.


    Route::get('vales/{id_vale}/accounting', 'ExpensesController@listValesAccounting');
    Route::get('vale-accounting-entries/{id_vale}', 'ExpensesController@showVoucherAccountingEntries');
    Route::put('vale-accounting-entries/{id_vale}', 'ExpensesController@editVoucherAccountingEntries');
    Route::delete('vale-accounting-entries/{id_vale}', 'ExpensesController@deleteVoucherAccountingEntries');
    Route::post('vale-accounting-entries/duplicate', 'ExpensesController@duplicateVoucherAccountingEntries');

    //Editar Asiento Autorizado --> 7124
    Route::put('vales/{id_asiento}/seat/{id_vale}', 'ExpensesController@editSeatVale');

    // execute process run

    Route::post('execute-operation-runprocess', 'ExpensesController@executeOperationProcessRun');

    Route::get('account-plan', 'ExpensesController@listAccountingPlan');
    Route::get('current-account', 'ExpensesController@listCurrentAccount');
    Route::get('department-account', 'ExpensesController@listDepartmentAccount');
    Route::get('deposits-imports', 'IncomeController@listDepositImports');
    Route::post('deposits-imports-students-import', 'IncomeController@studentsImport');
    Route::post('deposits-imports', 'IncomeController@addDepositImports');
    Route::put('deposits-imports', 'IncomeController@addDepositImportsFinish');

    //Import Depsoti Admission
    Route::post('deposits-imports-admission-import', 'IncomeController@admissionImport');
    Route::post('deposit-admission-massive', 'IncomeController@addDepositAdmissionMassive');
    Route::get('deposit-individual-admission', 'IncomeController@showAdmissionByCode');
    Route::post('deposit-individual-admission', 'IncomeController@addDepositAdmissionIndividual');
    //deductions
    Route::get('types-goods-services', 'ExpensesController@listTypesGoodsServices');
    Route::get('types-detraction-operations', 'ExpensesController@listTypesDetractionOperations');
    Route::get('deductions', 'ExpensesController@listDeductions');
    Route::post('deductions', 'ExpensesController@addDeductions');
    Route::get('deductions-summary', 'ExpensesController@listDeductionsSummary');
    Route::delete('deductions/{id_deduction}', 'ExpensesController@deleteDeductions');
    Route::delete('deductions-all/{id_deduction}', 'ExpensesController@deleteDeductionsAll');


    Route::get('detractions', 'ExpensesController@Detractions');
    Route::get('retentions-report', 'TreasuryController@Retentions');


    //retentions
    Route::get('retentions-summary', 'ExpensesController@listRetentionsSummary');
    Route::get('retentions', 'ExpensesController@listRetentions');

    Route::post('retentions', 'ExpensesController@addRetentions');
    Route::post('retentions/by-upn', 'ExpensesController@addRetentionsByUPN'); // by @vitmar
    Route::delete('retentions/{id_retecion}', 'ExpensesController@deleteRetentions');

    Route::put('retentions/{id_retencion}', 'ExpensesController@updateRetentions');
    Route::get('retentions/{id_retencion}', 'ExpensesController@getRetentionById');
    Route::get('retentions/{id_retencion}/purchases', 'ExpensesController@listRetentionsPurchases');
    Route::post('retentions/{id_retencion}/purchases', 'ExpensesController@addRetentionsPurchases');
    Route::delete('retentions/{id_retencion}/purchases/{id_retdetalle}', 'ExpensesController@deleteRetentionsPurchases');
    //Depositos al Banco
    Route::get('bank-deposits', 'ExpensesController@listBankDeposits');
    Route::post('bank-deposits', 'ExpensesController@addBankDeposits');
    Route::delete('bank-deposits/{id_depbanco}', 'ExpensesController@deleteBankDeposits');

    Route::get('box-deposits/to-vales', 'ExpensesController@listDepositsToValesUPN');
    Route::delete('box-deposits/{id_deposito}', 'ExpensesController@deleteDepositsToValesUPN');


    Route::get('exchange-rate-payment', 'ExpensesController@showExchangeRatePayment');

    //CIERRE DE CAJA - EFECTIVO
    Route::get('voucher-to-close', 'IncomeController@listVoucherCashClosing');
    Route::get('voucher-to-close/{id_cierre}/deposits', 'IncomeController@showVoucherCashClosingDeposits');
    Route::get('voucher-to-close/{id_cierre}/deposits-export', 'IncomeController@showVoucherCashClosingDepositsExport');
    Route::post('voucher-to-close', 'IncomeController@addCashClosing');

    //Reportes de Chques y telecredito = Pagos
    Route::get('my-payments', 'ExpensesController@myPayments');
    Route::get('my-payments-pdf', 'TreasuryController@myPaymentsPdf');
    Route::get('detractions/permissions', 'TreasuryController@detractionsPermissions');
    Route::get('retentions-permissions', 'TreasuryController@retentionsPermissions');
    Route::get('payments-permissions', 'TreasuryController@paymentsPermissions');

    //Resumen Pagos
    Route::get('my-payments-summary', 'ExpensesController@myPaymentsSummary');
    //Resumen Pagos -- pf
    Route::get('my-payments-summary-pdf', 'ExpensesController@myPaymentsSummaryPdf');

    // Nuevos Apis
    Route::get('tets-demo', 'ExpensesController@myPaymentsSummaryPdf');

    Route::get('control-collection', 'TreasuryController@listColectionControl');
    Route::post('control-collection/procesar', 'TreasuryController@generarDatosGrafico');
    Route::get('control-collection/grafic', 'TreasuryController@graficoColectionControl');
    Route::get('control-collection/grafic-detalle', 'TreasuryController@graficoColectionControlDetalle');
    // adds
    Route::get('control-collection/detalle', 'TreasuryController@listColectionControlDetalle');

    Route::get('certificado-donacion', 'TreasuryController@certificado');

    // Pagos bancarios bank-payments
    Route::resource('bank-payments', 'BankPaymentsController');

    ///// Valessss
    Route::post('file-vales', 'ExpensesController@saveNewFilesVale');
    Route::get('file-vales/{id_vale}', 'ExpensesController@fileVale');
    Route::delete('file-vales/{id_vfile}', 'ExpensesController@deleteFilesVale');
    Route::get('vales-tipo', 'ExpensesController@valeFilesList');
    Route::get('deposito-file/{id_deposito}', 'ExpensesController@depositoFile');
    Route::get('gasto-file/{id_pgasto}', 'ExpensesController@pgastoFile');

    /// Documentos
    Route::post('my-documents', 'TaxDocumentsController@addMyDocuments');
    Route::post('authorize-refused-my-documents', 'TaxDocumentsController@addAuthorizeRefusedDocuments');
    Route::get('my-documents', 'TaxDocumentsController@listMyDocument');
    Route::delete('my-documents/{id_documento}', 'TaxDocumentsController@deleteMyDocuments');
    Route::get('proceso-documents', 'TaxDocumentsController@getProcesosDocuments');
    Route::post('seats-my-documents', 'TaxDocumentsController@addSeats');
    Route::post('transaction-seats', 'TaxDocumentsController@addSeatsTransaction');
    Route::put('seats-my-documents/{id_casiento}', 'TaxDocumentsController@updateSeats');
    Route::get('seats-my-documents/{id_documento}', 'TaxDocumentsController@listSeats');
    Route::delete('seats-my-documents/{id_casiento}', 'TaxDocumentsController@deleteAsientoDocumeto');
    Route::get('process-documents/{id_documento}', 'TaxDocumentsController@processDocuments');
    Route::get('year-documents-dual', 'TaxDocumentsController@getYearDocuments');
    Route::get('pgasto-my-documents', 'TaxDocumentsController@listPgastoMyDocument');
    Route::post('pgasto-my-documents', 'TaxDocumentsController@addGastoMyDocument');
    Route::get('pago-my-documents/{id_pago}', 'TaxDocumentsController@getPaymentDocument');
    Route::get('payment-seats-my-documents/{id_pgasto}', 'TaxDocumentsController@getSeatsPagoDocument');
});

Route::group(['prefix' => 'inventories', 'namespace' => 'Inventories'], function () {
    Route::get('show_article', 'ArticleController@listArticle');

    Route::post('article', 'ArticleController@showArticle');

    Route::get('warehouses', 'WarehousesController@listWarehouses');
    Route::get('search-warehouse', 'WarehousesController@searchWarehouse');
    Route::get('warehouses-exist', 'WarehousesController@listWarehouseByExist');
    Route::get('warehouses/{id_almacen}', 'WarehousesController@showWarehouses');
    Route::post('warehouses', 'WarehousesController@addWarehouses');
    Route::put('warehouses/{id_almacen}', 'WarehousesController@updateWarehouses');
    Route::delete('warehouses/{id_almacen}', 'WarehousesController@deleteWarehouses');
    Route::get('warehouses-entities', 'WarehousesController@listWarehousesEntity');

    Route::get('warehouses-origins', 'WarehousesController@listWarehousesOrigins');
    Route::get('warehouses-destinations', 'WarehousesController@listWarehousesDestinations');

    Route::get('warehouses/{id_almacen}/users', 'WarehousesController@listWarehousesUsers');
    Route::get('warehouses/{id_almacen}/users/{id_persona}', 'WarehousesController@showWarehousesUsers');
    Route::post('warehouses/{id_almacen}/users', 'WarehousesController@addWarehousesUsers');
    Route::put('warehouses/{id_almacen}/users/{id_persona}', 'WarehousesController@updateWarehousesUsers');
    Route::delete('warehouses/{id_almacen}/users/{id_persona}', 'WarehousesController@deleteWarehousesUsers');

    Route::get('my-warehouses', 'WarehousesController@listMyWarehousesUsers');
    Route::patch('my-warehouses/{id_almacen}', 'WarehousesController@updateWarehousesUsersAssign');
    Route::get('my-warehouses/{id_anho}', 'WarehousesController@listMyWarehousesYears');
    Route::get('my-warehouses-stock', 'WarehousesController@listMyWarehousesStock');
    Route::post('my-warehouses', 'WarehousesController@addStockWarehouses');

    Route::post('warehouse-product', 'WarehousesController@addWarehouseProduct');
    Route::get('warehouse-product/{id_receta}', 'WarehousesController@getWarehouseProduct');
    Route::put('warehouse-product', 'WarehousesController@updateWarehouseProduct');

    Route::get('measurement-units', 'WarehousesController@listMeasurementUnits');
    Route::get('measurement-units/{id_unidadmedida}', 'WarehousesController@showMeasurementUnits');
    Route::post('measurement-units', 'WarehousesController@addMeasurementUnits');
    Route::put('measurement-units/{id_unidadmedida}', 'WarehousesController@updateMeasurementUnits');
    Route::delete('measurement-units/{id_unidadmedida}', 'WarehousesController@deleteMeasurementUnits');
    Route::get('articles/{id_articulo}/tree', 'WarehousesController@listArticlesTree');
    Route::get('articles', 'WarehousesController@listArticles');
    Route::get('articles/{id_articulo}/children', 'WarehousesController@listArticlesChildren');
    Route::get('articles/{id_articulo}', 'WarehousesController@showArticles');
    Route::post('articles', 'WarehousesController@addArticles');
    Route::post('articles-upload', 'WarehousesController@addArticlesUpload');
    Route::post('articles-upload-all', 'WarehousesController@addArticlesUploadALL');
    Route::post('articles-upload-all-upn', 'WarehousesController@addArticlesUploadALLUPN');
    Route::put('articles/{id_articulo}', 'WarehousesController@updateArticles');
    Route::delete('articles/{id_articulo}', 'WarehousesController@deleteArticles');
    Route::get('class', 'WarehousesController@listClass');
    Route::get('articles/{id_articulo}/codes', 'WarehousesController@listArticlesCodes');
    Route::post('articles/{id_articulo}/codes', 'WarehousesController@addArticlesCodes');
    Route::delete('articles/{id_articulo}/codes/{id_codarticulo}', 'WarehousesController@deleteArticlesCodes');
    Route::get('articles-find', 'WarehousesController@listWarehousesArticlesAll');
    Route::get('articles-search', 'WarehousesController@listArticlesSearch');
    Route::post('articles-imports', 'WarehousesController@addArticlesImports');
    Route::get('class-articles', 'WarehousesController@listClassArticles');
    Route::get('articles-class', 'WarehousesController@listArticlesClassParent');
    Route::get('articles-class/{id_almacen}/{id_articulo}', 'WarehousesController@listArticlesClassChild');
    Route::post('articles-structures', 'WarehousesController@addArticlesStructures');
    Route::put('articles-structures/{id_articulo}', 'WarehousesController@updateArticlesStructures');
    Route::delete('articles-structures/{id_articulo}', 'WarehousesController@deleteArticlesStructures');

    Route::get('warehouses/{id_almacen}/articles', 'WarehousesController@listWarehousesArticles');
    Route::get('warehouses/{id_almacen}/articles/{id_articulo}', 'WarehousesController@showWarehousesArticles');
    Route::post('warehouses/{id_almacen}/articles', 'WarehousesController@addWarehousesArticles');
    Route::post('warehouses/{id_almacen}/articles/{id_articulo}', 'WarehousesController@updateWarehousesArticles');
    Route::delete('warehouses/{id_almacen}/articles/{id_articulo}', 'WarehousesController@deleteWarehousesArticles');

    Route::get('warehouses/{id_almacen}/articles-class', 'WarehousesController@listWarehousesArticlesClass');
    Route::get('warehouses/{id_almacen}/articles-items', 'WarehousesController@listWarehousesArticlesItems');

    Route::get('warehouses/{id_almacen}/recipes', 'WarehousesController@listWarehousesRecipes');
    Route::get('warehouses/{id_almacen}/recipes/{id_receta}', 'WarehousesController@showWarehousesRecipes');
    Route::post('warehouses/{id_almacen}/recipes', 'WarehousesController@addWarehousesRecipes');
    Route::put('warehouses/{id_almacen}/recipes/{id_receta}', 'WarehousesController@updateWarehousesRecipes');
    Route::delete('warehouses/{id_almacen}/recipes/{id_receta}', 'WarehousesController@deleteWarehousesRecipes');

    Route::get('recipes/{id_receta}/articles', 'WarehousesController@listRecipesArticles');
    Route::get('recipes/{id_receta}/articles/{id_articulo}', 'WarehousesController@showRecipesArticles');
    Route::post('recipes/{id_receta}/articles', 'WarehousesController@addRecipesArticles');
    Route::put('recipes/{id_receta}/articles/{id_articulo}', 'WarehousesController@updateRecipesArticles');
    Route::delete('recipes/{id_receta}/articles/{id_articulo}', 'WarehousesController@deleteRecipesArticles');

    Route::get('type-operations', 'WarehousesController@listTypeOperations');

    Route::get('warehouses/{id_almacen}/documents', 'WarehousesController@listWarehousesDocuments');
    Route::get('warehouses/{id_almacen}/documents/{id_doc}', 'WarehousesController@showWarehousesDocuments');
    Route::post('warehouses/{id_almacen}/documents', 'WarehousesController@addWarehousesDocuments');
    Route::put('warehouses/{id_almacen}/documents/{id_doc}', 'WarehousesController@updateWarehousesDocuments');
    Route::delete('warehouses/{id_almacen}/documents/{id_doc}', 'WarehousesController@deleteWarehousesDocuments');

    Route::get('warehouses/{id_almacen}/almacen-categorias', 'WarehousesController@listWarehousesAlmacenCategorias');
    Route::post('warehouses/{id_almacen}/almacen-categorias', 'WarehousesController@addWarehousesAlmacenCategorias');
    Route::get('warehouses/{id_almacen}/almacen-categorias/{id_rcategoria}', 'WarehousesController@getWarehousesAlmacenCategorias');
    Route::delete('warehouses/{id_almacen}/almacen-categorias/{id_rcategoria}', 'WarehousesController@deleteWarehousesAlmacenCategorias');

    Route::get('my-type-operations', 'InventoriesController@listTypeOperations');
    Route::get('my-inventories-documents', 'InventoriesController@listDocuments');
    Route::get('my-inventories-warehouses-articles', 'WarehousesController@listWarehousesArticlesFind');

    Route::post('my-inventories-movements', 'InventoriesController@addInventoriesMovements');
    Route::put('my-inventories-movements/{id_movimiento}', 'InventoriesController@updateInventoriesMovements');

    Route::get('my-inventories-movements/{id_movimiento}/details', 'InventoriesController@listInventoriesDetails');
    Route::get('my-inventories-movements/{id_movimiento}/details/{id_movdetalle}', 'InventoriesController@showInventoriesDetails');
    Route::post('my-inventories-movements/{id_movimiento}/inventories-details', 'InventoriesController@addInventoriesDetails');
    Route::put('my-inventories-movements/{id_movimiento}/inventories-details/{id_movdetalle}', 'InventoriesController@updateInventoriesDetails');
    Route::delete('my-inventories-movements/{id_movimiento}/inventories-details/{id_movdetalle}', 'InventoriesController@deleteInventoriesDetails');
    Route::post('my-inventories-movements/{id_movimiento}/inventories-details-recipes', 'InventoriesController@addInventoriesDetailsRecipes');
    Route::post('my-inventories-transfers', 'InventoriesController@addInventoriesTransfers');
    Route::put('my-inventories-transfers/{id_movimiento}', 'InventoriesController@updateInventoriesTransfers');
    //Transferencia entre Almacenes
    Route::get('my-inventories-documents-transfer', 'InventoriesController@showWarehousesDocumentsOpertions');
    Route::get('kardex', 'InventoriesController@listKardex');
    Route::get('my-categories-articles', 'InventoriesController@listCategoriesArticles');
    Route::get('my-categories-articles/{id_almacen}', 'InventoriesController@listCategoriesWarehousesArticles');
    Route::put('my-categories-articles', 'InventoriesController@lllistCategoriesArticles');

    Route::get('my-warehouses-Assigneds', 'WarehousesController@showWarehousesUsersAssign');
    Route::get('my-warehouses-recipes', 'WarehousesController@listMyWarehousesRecipes');

    Route::get('stock', 'InventoriesController@listStockArticles');
    Route::get('receta-tipos', 'InventoriesController@listRecetaTipos');

    Route::get('warehouse-type', 'InventoriesController@listWerehouseType');

    Route::get('stock-all', 'InventoriesController@listStockArticlesAll');
    //Salidas Diversas
    Route::get('various-outputs', 'InventoriesController@listMiscellaneousOutputs');
    Route::get('various-outputs-pdf', 'InventoriesController@listMiscellaneousOutputspdf');

    //traslado de recetas
    Route::get('almacen-receta', 'InventoriesController@getALmacenReceta');

    Route::get('tipo-pedidos', 'PedidoRegistroController@listTipoPedidos');

    Route::get('pedido-registros/{id_pedido}', 'PedidoRegistroController@showPedidoRegistro');
    Route::get('pedido-registros', 'PedidoRegistroController@listPedidoRegistro');
    Route::post('pedido-registros', 'PedidoRegistroController@savePedidoRegistro');
    Route::put('pedido-registros/{id_pedido}', 'PedidoRegistroController@updatePedidoRegistro');
    Route::delete('pedido-registros/{id_pedido}', 'PedidoRegistroController@deletePedidoRegistro');
    Route::get('pedido-registros/{id_pedido}/detalles', 'PedidoRegistroController@listPedidoRegistroDetalle');
    Route::post('pedido-registros/{id_pedido}/detalles', 'PedidoRegistroController@savePedidoRegistroDetalle');
    // Route::put('pedido-registros/{id_pedido}/detalles/{id_detalle}', 'InventoriesController@updatePedidoRegistroDetalle');
    Route::delete('pedido-registros/detalles/{id_detalle}', 'PedidoRegistroController@deletePedidoRegistroDetalle');
    Route::delete('pedido-registros/{id_pedido}/detalles/delete-all', 'PedidoRegistroController@deletePedidoRegistroDetalleAll');
    Route::post('pedido-registros/{id_pedido}/finalizar', 'PedidoRegistroController@finalizarPedidoRegistro');
    Route::post('pedido-registros/{id_pedido}/change-state', 'PedidoRegistroController@changeStatePedidoRegistro');
    Route::get('pedido-registros/{id_pedido}/movimientos', 'PedidoRegistroController@listMovimientosByIdPedido');
    Route::post('pedido-registros/{id_pedido}/salida-mercaderia', 'PedidoRegistroController@salidaMercaderiaPedidoRegistro');
    Route::post('pedido-registros/{id_pedido}/ingreso-mercaderia', 'PedidoRegistroController@ingresoMercaderiaPedidoRegistro');

    Route::get('almacen-categorias', 'AlmacenCategoriasController@listAlmacenCategorias');
    Route::get('almacen-categorias/{id_rcategoria}', 'AlmacenCategoriasController@showAlmacenCategorias');
    Route::post('almacen-categorias', 'AlmacenCategoriasController@addAlmacenCategorias');
    Route::put('almacen-categorias/{id_rcategoria}', 'AlmacenCategoriasController@updateAlmacenCategorias');
    Route::delete('almacen-categorias/{id_rcategoria}', 'AlmacenCategoriasController@deleteAlmacenCategorias');

    Route::get('almacen-rubros', 'AlmacenRubrosController@listAlmacenRubros');
    Route::get('almacen-rubros/{id_rubro}', 'AlmacenRubrosController@showAlmacenRubros');
    Route::post('almacen-rubros', 'AlmacenRubrosController@addAlmacenRubros');
    Route::put('almacen-rubros/{id_rubro}', 'AlmacenRubrosController@updateAlmacenRubros');
    Route::delete('almacen-rubros/{id_rubro}', 'AlmacenRubrosController@deleteAlmacenRubros');

    //apis
    // mas apis de Demos
    // mas apis
});
Route::group(['prefix' => 'evaluations', 'namespace' => 'Evaluation'], function () {
    Route::get('periods', 'EvaluationController@listPeriod');
    Route::get('periods/{id_period}/period-details', 'EvaluationController@listPeriodDetails');
    Route::get('departments', 'EvaluationController@listEvaluationDepartments');
    Route::get('evaluations-indicators', 'EvaluationController@showEvaluationRegisters');
    Route::post('evaluations-indicators', 'EvaluationController@addEvaluationDetails');
    Route::get('types-notices/{id_tipo}', 'EvaluationController@showTypeNotices');
    Route::get('reports-deptos', 'EvaluationController@listEvaluationReports');
    Route::get('reports-trafficlight', 'EvaluationController@listEvaluationTrafficLight');
    Route::get('reports-departments', 'EvaluationController@listReportDepartments');
    Route::get('departments-assigneds', 'EvaluationController@listDepartmentsAssigneds');
    Route::get('indicators', 'EvaluationController@ListEvaluationIndicators');
    Route::post('evaluations-livelihoods', 'EvaluationController@addEvaluationLivelihoods');
    Route::delete('evaluations-livelihoods/{id_sustento}', 'EvaluationController@deleteEvaluationLivelihoods');
    Route::get('evaluations-livelihoods', 'EvaluationController@listEvaluationLivelihoods');
    Route::get('reports-departments-details', 'EvaluationController@listReportDepartmentsDetails');
    Route::get('reports-departments-months', 'EvaluationController@listReportDepartmentsMonths');
    // nes apis
});
Route::group(['prefix' => 'setup', 'namespace' => 'Process'], function () {
    Route::get('processes', 'ProcessController@listProcess');
    Route::get('processes/{id_proceso}', 'ProcessController@showProcess');
    Route::post('processes', 'ProcessController@addProcess');
    Route::put('processes/{id_proceso}', 'ProcessController@updateProcess');
    Route::delete('processes/{id_proceso}', 'ProcessController@deleteProcess');
    Route::get('processes/{id_proceso}/steps', 'ProcessController@listSteps');
    Route::get('processes/{id_proceso}/steps/{id_paso}', 'ProcessController@showSteps');
    Route::post('processes/{id_proceso}/steps', 'ProcessController@addSteps');
    Route::get('processes-type-steps', 'ProcessController@listProcessType');
    Route::put('processes/{id_proceso}/steps/{id_paso}', 'ProcessController@updateSteps');
    Route::delete('processes/{id_proceso}/steps/{id_paso}', 'ProcessController@deleteSteps');
    Route::get('processes/{id_proceso}/flows', 'ProcessController@listFlows');
    Route::get('processes/{id_proceso}/flows/{id_flujo}', 'ProcessController@showFlows');
    Route::post('processes/{id_proceso}/flows', 'ProcessController@addFlows');
    Route::put('processes/{id_proceso}/flows/{id_flujo}', 'ProcessController@updateFlows');
    Route::delete('processes/{id_proceso}/flows/{id_flujo}', 'ProcessController@deleteFlows');
    Route::get('dynamic-components', 'ProcessController@listComponents');
    Route::get('dynamic-components/{id_componente}', 'ProcessController@showComponents');
    Route::post('dynamic-components', 'ProcessController@addComponents');
    Route::put('dynamic-components/{id_componente}', 'ProcessController@updateComponents');
    Route::delete('dynamic-components/{id_componente}', 'ProcessController@deleteComponents');
    Route::get('processes/{id_proceso}/process-paso-run', 'ProcessController@listStepsEjecutations');
    Route::get('processes/{id_proceso}/process-paso-run/{id_paso}', 'ProcessController@showStepsEjecutations');
    Route::post('process-run', 'ProcessController@processRun');
    Route::post('process-paso-run', 'ProcessController@processPasoRun');

    //apis news
});

Route::group(['prefix' => 'purchases', 'namespace' => 'Purchases'], function () {
    Route::get('orders-pending', 'PurchasesController@listOrdersPending');
    Route::get('orders/{id_pedido}', 'PurchasesController@showOrders');
    Route::get('my-orders', 'PurchasesController@listMyOrders');
    Route::get('my-orders/{id_pedido}', 'PurchasesController@showMyOrders');
    Route::get('orders-registers', 'PurchasesController@listOrdersRegisters');
    Route::post('orders-registers', 'PurchasesController@addOrdersRegisters');
    Route::put('orders-registers/{id_pedido}', 'PurchasesController@updateOrdersRegisters');
    Route::delete('orders-registers/{id_pedido}', 'PurchasesController@deleteOrdersRegisters');
    // Route::post('operations/pending-vob/refused', 'PurchasesController@addRefused');
    Route::put('orders-refused/{id_pedido}', 'PurchasesController@saveOrdersRefused');
    Route::post('orders-refused/attached', 'PurchasesController@ordersRefusedAttach');
    Route::get('orders-details-to-dispatches', 'PurchasesController@listOrdersDetailsToDispatches');
    Route::get('orders-details', 'PurchasesController@listOrdersDetails');
    Route::post('orders-details', 'PurchasesController@addOrdersDetails');
    Route::put('orders-details/{id_detalle}', 'PurchasesController@updateOrdersDetails');
    Route::delete('orders-details/{id_detalle}', 'PurchasesController@deleteOrdersDetails');
    Route::put('orders-movilidad/{id_detalle}', 'PurchasesController@updateOrdersMovilidad');

    //listas de Gastos para Compras
    Route::get('expenses-purchases', 'PurchasesController@listPurchasesExpenses');

    // status

    Route::get('current-order-status/{id_pedido}', 'PurchasesController@getDetailOrderStatus');
    Route::get('purchases-flow-status/{id_pedido}', 'PurchasesController@getPurchasesStatus');
    Route::get('order-all-detail/{id_pedido}', 'PurchasesController@getAllOrderDetail');

    Route::get('orders-purchases', 'PurchasesController@listOrdersPurchases');
    Route::get('orders-purchases/{id_pcompra}', 'PurchasesController@showOrdersPurchases');
    Route::post('orders-purchases', 'PurchasesController@addOrdersPurchases');
    Route::delete('orders-purchases/{id_pcompra}', 'PurchasesController@deleteOrdersPurchases');

    // Route::get('purchases', 'PurchasesController@listPurchasesOrders');
    Route::get('purchases/{id_compra}', 'PurchasesController@showPurchases');
    Route::post('purchases', 'PurchasesController@addPurchases');
    Route::put('purchases/ret-detra/{id_compra}', 'PurchasesController@updatePurchasesRetDetra');
    // Route::delete('purchases-orders/{id_orden}', 'PurchasesController@deletePurchasesOrders');
    // Route::post('provisions/{id_pedido}/finalizer', 'PurchasesController@addFinalizer');
    // Route::patch('purchases-finalizer', 'PurchasesController@endPurchases');
    Route::patch('purchases-end/{id_compra}', 'PurchasesController@execPurchasesEnd');
    // Route::post('provisions/voucher/{id_compra}/accounting-seat-generate', 'PurchasesController@addAccountingSeatGenerate');
    // Route::post('purchases-seat-generate', 'PurchasesController@execPurchasesSeatGenerate');

    Route::get('purchases-details', 'PurchasesController@listPurchasesDetails');
    // Route::get('purchases-details/{id_compra}', 'PurchasesController@showPurchases');
    Route::post('purchases-details', 'PurchasesController@addPurchasesDetails');
    Route::post('purchases-details/import', 'PurchasesController@addPurchasesDetailsImport');
    // Route::delete('purchases-orders/{id_orden}', 'PurchasesController@deletePurchasesOrders');
    Route::delete('purchases-details/{id_detalle}', 'PurchasesController@deletePurchasesDetails');

    Route::delete('purchases-details/{id_compra}/delete-all', 'PurchasesController@deletePurchasesDetailsAll');

    Route::put('purchases-details', 'PurchasesController@putPurchasesDetails');
    Route::patch('purchases-details/{id_detalle}', 'PurchasesController@patchPurchasesDetails');
    Route::post('purchases/{id_compra}/details', 'PurchasesController@addPurchasesDetailsGenerate');
    Route::put('purchases/{id_compra}/details/{id_detalle}', 'PurchasesController@updatePurchasesDetails');

    Route::get('purchases/{id_compra}/seats-acounting', 'PurchasesController@listPurchasesSeatsAcounting');

    Route::get('purchases-typeigv', 'PurchasesController@listPurchasesTypeigv');
    Route::get('list-igv', 'PurchasesController@listIgv');

    Route::get('purchases-seats', 'PurchasesController@listPurchasesSeats');
    Route::post('purchases-seats', 'PurchasesController@addPurchasesSeats');
    // Route::put('provisions/voucher/{id_compra}/accounting-seat/{id_casiento}', 'PurchasesController@updateAccountingSeat');
    Route::delete('purchases-seats/{id_pasiento}', 'PurchasesController@deletePurchasesSeats');
    //Route::post('purchases-seats-generate', 'PurchasesController@execPurchasesSeatsGenerate'); // OK Jose
    Route::post('purchases-seats-generate', 'PurchasesController@addSeatsPurchases'); // New Marlo
    Route::post('purchases-seats-generate-inventories', 'PurchasesController@addSeatsPurchasesInventories'); // New Marlo
    Route::post('purchases/{id_compra}/seats-generate', 'PurchasesController@addSeatsPurchasesDynamic'); // New Marlo
    Route::post('purchases-seats-imports', 'PurchasesController@addPurchasesSeatsImports');
    Route::put('purchases/{id_compra}/seats/{id_casiento}', 'PurchasesController@updatePurchasesSeats');

    Route::get('purchases-orders', 'PurchasesController@listPurchasesOrders');
    Route::get('purchases-orders/{id_orden}', 'PurchasesController@showPurchasesOrders');
    Route::get('purchases-orders-by-orders/{id_pedido}', 'PurchasesController@showPurchasesOrdersByOrders');
    Route::post('purchases-orders', 'PurchasesController@addPurchasesOrders');
    Route::put('purchases-orders/{id_orden}', 'PurchasesController@updatePurchasesOrders');
    Route::delete('purchases-orders/{id_orden}', 'PurchasesController@deletePurchasesOrders');
    Route::patch('purchases-orders-end/{id_orden}', 'PurchasesController@patchPurchasesOrdersEnd');

    Route::get('purchases-orders-details', 'PurchasesController@listPurchasesOrdersDetails');
    Route::get('purchases-orders-details/{id_odetalle}', 'PurchasesController@showPurchasesOrdersDetails');
    Route::post('purchases-orders-details', 'PurchasesController@addPurchasesOrdersDetails');
    Route::delete('purchases-orders-details/{id_odetalle}', 'PurchasesController@deletePurchasesOrdersDetails');

    // Route::get('templates-details-purchases', 'PurchasesController@listTemplateDetailsPurchases');
    // Route::get('templates-details-purchases/{id_pdcompra}', 'PurchasesController@showTemplateDetailsPurchases');
    // Route::post('templates-details-purchases', 'PurchasesController@addTemplateDetailsPurchases');
    // Route::put('templates-details-purchases/{id_pdcompra}', 'PurchasesController@updateTemplateDetailsPurchases');
    // Route::delete('templates-details-purchases/{id_pdcompra}', 'PurchasesController@deleteTemplateDetailsPurchases');
    Route::get('orders-templates-purchases', 'PurchasesController@listOrdersTemplatesPurchases');
    Route::get('orders-templates-purchases/{id_ppcompra}', 'PurchasesController@showOrdersTemplatesPurchases');
    Route::post('orders-templates-purchases', 'PurchasesController@addOrdersTemplatesPurchases');
    Route::put('orders-templates-purchases/{id_pdcompra}', 'PurchasesController@updateOrdersTemplatesPurchases');
    Route::delete('orders-templates-purchases/{id_ppcompra}', 'PurchasesController@deleteOrdersTemplatesPurchases');
    Route::patch('orders-templates-purchases-end/{id_pedido}', 'PurchasesController@execOrdersTemplatesPurchasesEnd');
    // PEDIDO_PLANTILLA_COMPRA
    Route::get('types-orders', 'PurchasesController@listTypesOrders');/* nu-new */


    Route::get('process-flow-by-init', 'PurchasesController@listProcessFlowByInit');
    Route::post('process-step-run-next', 'PurchasesController@addProcessStepRunNext');
    Route::put('process-step-run-by-orders/{id_pedido}', 'PurchasesController@saveProcessStepRunByOrders');
    /* nu-new-alternative */
    Route::get('operations/pending', 'PurchasesController@listMyOperationsPending');
    Route::get('operations', 'PurchasesController@listMyOperations');
    Route::get('operations/summary', 'PurchasesController@summaryOperations');
    Route::post('operations/receipt/step-a', 'PurchasesController@runReceiptStepA');    /* BORRAR */
    Route::post('operations/receipt/step-preprovision', 'PurchasesController@addStepPreProvision'); /* BORRAR */
    Route::post('operations/receipt/close-preprovision', 'PurchasesController@closePreprovision');
    Route::post('operations/request-purchase/step-a', 'PurchasesController@addStepB'); /* BORRAR */
    Route::get('operations/request-purchase/step-attach/{id_pedido}', 'PurchasesController@listFilesReceipt');
    Route::post('operations/request-purchase/step-attach', 'PurchasesController@addPedidoFile');
    Route::delete('operations/request-purchase/step-attach/{id_pfile}', 'PurchasesController@deletePedidoFile');
    Route::put('operations/request-purchase/step-attach/{id_pedido}', 'PurchasesController@putPedidoFile');
    Route::get('operations/pending-vob', 'PurchasesController@listOperationsPendingVob');
    Route::get('operations/pending-vob/summary', 'PurchasesController@summaryOperationsPendingVob');
    Route::post('operations/pending-vob/refused', 'PurchasesController@addRefused');
    Route::post('operations/pending-vob/approved', 'PurchasesController@addApproved');
    Route::post('operations/pending-vob/agreement', 'PurchasesController@addStepE');
    Route::get('operations/pending-approval', 'PurchasesController@listOperationsPendingApproval');
    Route::get('operations/pending-approval/summary', 'PurchasesController@summaryOperationsPendingApproval');
    Route::post('operations/pending-approval/refused', 'PurchasesController@addRefused');
    Route::post('operations/pending-approval/approved', 'PurchasesController@addApprovedConsejo');
    Route::get('provisions', 'PurchasesController@listProvisions');
    Route::get('provisions/summary', 'PurchasesController@listProvisions2');
    Route::get('provisions/form/{id_pcompra}', 'PurchasesController@formProvisions');
    Route::get('provisions/request-receipt/{id_pcompra}', 'PurchasesController@showRequestReceipt');
    Route::patch('provisions/request/{id_pedido}', 'PurchasesController@editRequest');
    Route::post('orders-registers/{id_pedido}/aproved-atach', 'PurchasesController@addPedidoFileCU');
    Route::post('orders-registers/{id_pedido}/quotation', 'PurchasesController@addPedidoFileQuotation');
    Route::put('orders-registers/{id_pedido}/quotation/{id_cotizacion}', 'PurchasesController@updatePedidoFileQuotation');
    Route::get('orders-registers/{id_pedido}/quotation', 'PurchasesController@listPedidoFileQuotation');
    Route::get('orders-registers/{id_pedido}/quotation-selected', 'PurchasesController@listPedidoFileQuotationSelected');
    Route::post('orders-registers/{id_pedido}/aproved-al', 'PurchasesController@addPedidoFileAL');

    // Route::post('provisions/receipt', 'PurchasesController@addPurchases');
    Route::put('provisions/receipt/{id_compra}', 'PurchasesController@updatePurchases');
    Route::get('provisions/receipt/{id_compra}/details', 'PurchasesController@listPurchasesDetails');
    Route::post('provisions/receipt/{id_compra}/details', 'PurchasesController@addPurchasesDetails');
    Route::delete('provisions/receipt/{id_compra}/details/{id_detalle}', 'PurchasesController@deletePurchasesDetails');
    Route::put('provisions/receipt/{id_compra}/details/{id_detalle}', 'PurchasesController@putPurchasesDetails');
    Route::put('provisions/receipt/cancel/{id_compra}', 'PurchasesController@cancelPurchases');
    Route::post('provisions/voucher/create-dynamic-seat', 'PurchasesController@addCreateDynamicSeat');
    Route::get('provisions/voucher/chooser-aasinet', 'PurchasesController@chooserAasinet');
    Route::get('provisions/voucher/{id_compra}/accounting-seat', 'PurchasesController@listAccountingSeat');
    Route::post('provisions/voucher/{id_compra}/accounting-seat', 'PurchasesController@addAccountingSeat');
    Route::put('provisions/voucher/{id_compra}/accounting-seat/{id_casiento}', 'PurchasesController@updateAccountingSeat');
    Route::delete('provisions/voucher/{id_compra}/accounting-seat/{id_casiento}', 'PurchasesController@deleteAccountingSeat');
    Route::post('provisions/voucher/{id_compra}/accounting-seat-generate', 'PurchasesController@addAccountingSeatGenerate');
    Route::post('provisions/{id_pedido}/finalizer', 'PurchasesController@addFinalizer');
    Route::get('files-receipt/{id_pedido}', 'PurchasesController@listFilesReceipt');
    Route::get('files-download/{id_pfile}', 'PurchasesController@getFilesReceipt');
    Route::get('operations/pending-approval/{id_pedido}', 'PurchasesController@showOperation');
    Route::get('operations/pending-vob/{id_pedido}', 'PurchasesController@showOperation');
    Route::get('operations/{id_pedido}', 'PurchasesController@showOperation');
    Route::get('provisions/{id_pedido}', 'PurchasesController@showProvision');
    /* RUTAS EXTAS */
    Route::get('last-exchange-rates', 'PurchasesController@listExchangeRates');
    Route::get('pending-vouchers', 'PurchasesController@listPendingVouchers');
    Route::get('types-pay', 'PurchasesController@listTypesPay');
    Route::get('types-receipt', 'PurchasesController@listTypesReceipts');
    Route::get('types-currency', 'PurchasesController@lisTypesCurrency');
    Route::get('banks-account-box', 'PurchasesController@lisBanksAccountBox');
    Route::get('funds', 'PurchasesController@listFunds');
    Route::get('purchases-parents', 'PurchasesController@listPurchasesParents');
    /* REPORTE */
    Route::get('reports/report-purchases', 'PurchasesController@listReportPurchases');
    Route::get('reports/report-accounting-seat', 'PurchasesController@listReportAccountingSeat');
    Route::get('reports/report-provisions', 'PurchasesController@listReportProvisions');
    /* pruebas rutas */
    // Route::get('process-flow-by-init', 'PurchasesController@listProcessFlowByInit');
    // Route::post('process-step-run-next', 'PurchasesController@addProcessStepRunNext');
    // Route::put('process-step-run-by-orders/{id_pedido}', 'PurchasesController@saveProcessStepRunByOrders');
    Route::get('z', 'PurchasesController@z');
    Route::post('provisions', 'PurchasesController@addReceipt');

    Route::get('reports/my-report-purchases', 'PurchasesController@listMyReportPurchases');
    Route::get('reports/my-report-purchases-pdf', 'PurchasesController@listMyReportPurchasesPdf');

    Route::get('files-view/{id_pedido}', 'PurchasesController@viewFiles');

    //Pre-Provision-Almacen
    Route::get('orders-purchases/{id_pcompra}/details', 'PurchasesController@listOrdersPurchaseDetails');
    Route::post('orders-purchases/{id_pcompra}/details', 'PurchasesController@addOrdersPurchaseDetails');
    Route::post('orders-purchases/{id_pcompra}/details-generate', 'PurchasesController@addOrdersPurchaseDetailsGenerate');
    Route::put('orders-purchases/{id_pcompra}/details/{id_cdetalle}', 'PurchasesController@updateOrdersPurchaseDetails');
    Route::delete('orders-purchases/{id_pcompra}/details/{id_cdetalle}', 'PurchasesController@deleteOrdersPurchaseDetails');

    //Plantilla de servicios
    Route::post('orders-template/{id_pedido}/purchases', 'PurchasesController@addOrdersPurchaseTemplate');
    // NEW JULIO 2019
    Route::post('purchases-xml', 'PurchasesController@addPurchasesXml');

    //RECIBOS POR HONORARIOS
    Route::post('receipt-for-fees', 'ReceiptForFeesController@addStore');
    Route::put('receipt-for-fees/{id_compra}', 'ReceiptForFeesController@updateStore');
    Route::post('receipt-for-fees/{id_compra}/details', 'PurchasesController@addReceiptForFeesDetails');
    Route::patch('receipt-for-fees/{id_compra}', 'ReceiptForFeesController@updateReceiptForFees');

    //AJUSTES O TRANSFERENCIAS DE COMPRAS Y/O RECIBOS POR HONORARIOS
    Route::get('purchasses-of-settings', 'SettingsController@listPurchasesOfSettings');
    Route::post('purchasses-of-settings', 'SettingsController@addPurchasesOfSettings');
    Route::put('purchasses-of-settings/{id_ajuste}', 'SettingsController@updatePurchasesOfSettings');
    Route::delete('purchasses-of-settings/{id_ajuste}', 'SettingsController@deletePurchasesOfSettings');

    //SALDOS INICIALES
    Route::get('purchasses-balances', 'PurchasesController@listPurchasesBalances');
    Route::post('purchasses-balances', 'PurchasesController@importPurchasesBalances');
    Route::delete('purchasses-balances/{id_saldo}', 'PurchasesController@deleltePurchasesBalances');

    //voucher Automaitoc -- tes
    Route::get('automatic-vouchers', 'PurchasesController@showVoucherAutomatico');

    Route::get('fix-list', 'PurchasesController@showArrangement');
    Route::delete('fix-list/{id_arreglo}', 'PurchasesController@deleteArrangement');
    Route::get('fix-list/{id_origen}', 'PurchasesController@showArrangementPurchases');
    Route::get('get-asientos', 'PurchasesController@getListAsientosByIdDinamica');

    //ELIMINA COMPRA SIN TERMINAR DE PROVISIONAR
    Route::get('purchasses-pending/{id_pedido}', 'PurchasesController@showPurchasesPending');
    Route::delete('purchasses-pending/{id_pedido}', 'PurchasesController@deleltePurchasesPending');

    // REPORTE MIS COMPRAS - PARA VER COMPRAS PROVISIONADAS
    Route::get('my-purchases', 'PurchasesController@listMyPurchases');
    Route::get('my-purchases/{id_compra}', 'PurchasesController@showMyPurchases');
    Route::get('my-purchases-details/{id_detalle}', 'PurchasesController@showPurchasesDetails');
    // CUENTAS POR PAGAR
    Route::get('debts-to-pay', 'PurchasesController@lisDebtsToPay');

    //CARGAR ARTICULOS PARA LAS COMPRAS
    Route::get('articles', 'PurchasesController@listWarehousesArticles');
    Route::get('articles-all', 'PurchasesController@listWarehousesArticlesAll');
    //ELIMINAR PRE-PROVISION
    Route::delete('purchasses-pre-provision/{id_pedido}', 'PurchasesController@deleltePurchasesPreProvision');
    //News


    // DUPLICADO DE COMPRA
    Route::get('provider-purchases', 'PurchasesDuplicateController@providerPurchases'); // compras proveedor
    Route::get('data-purchase', 'PurchasesDuplicateController@detailPurchase'); // data de compra
    Route::post('duplicate-purchase', 'PurchasesController@addPurchasesDuplicated'); // guarda de compra y detalles con asiento


    //Proyecto
    Route::get('proyyecto', 'PurchasesController@getProyecto');
    Route::get('caja-vale', 'PurchasesController@getVale');
});
Route::group(['prefix' => 'purchases/setup', 'namespace' => 'Purchases\Setup'], function () {
    Route::get('types-templates', 'SetupController@listTypesTemplates');
    Route::get('types-templates/{id_tipoplantilla}', 'SetupController@showTypesTemplates');
    Route::post('types-templates', 'SetupController@addTypesTemplates');
    Route::put('types-templates/{id_tipoplantilla}', 'SetupController@updateTypesTemplates');
    Route::delete('types-templates/{id_tipoplantilla}', 'SetupController@deleteTypesTemplates');

    Route::get('types-project', 'SetupController@listTypeProject');
    Route::get('projects/{idProject}', 'SetupController@showProject');
    Route::get('projects', 'SetupController@listProject');
    Route::post('projects/add', 'SetupController@addProject');
    Route::post('projects/update/{idProject}', 'SetupController@updateProject');
    Route::put('projects/projects-change-state/{idProject}', 'SetupController@changeStateProject');
    Route::get('projects/compras/{idProject}', 'SetupController@listComprasByProyecto');
    Route::get('projects/acuerdos/{idProject}', 'SetupController@listAcuerdos');
    Route::post('projects/acuerdos/add', 'SetupController@addAcuerdo');

    Route::get('purchases-templates', 'SetupController@listPurchasesTemplates');
    Route::get('purchases-templates/{id_plantilla}', 'SetupController@showPurchasesTemplates');
    Route::post('purchases-templates', 'SetupController@addPurchasesTemplates');
    Route::put('purchases-templates/{id_plantilla}', 'SetupController@updatePurchasesTemplates');
    Route::delete('purchases-templates/{id_plantilla}', 'SetupController@deletePurchasesTemplates');

    Route::get('purchases-template-details', 'SetupController@listPurchasesTemplateDetails');
    Route::get('purchases-template-details/{id_pdetalle}', 'SetupController@showPurchasesTemplateDetails');
    Route::post('purchases-template-details', 'SetupController@addPurchasesTemplateDetails');
    Route::put('purchases-template-details/{id_pdetalle}', 'SetupController@updatePurchasesTemplateDetails');
    Route::delete('purchases-template-details/{id_pdetalle}', 'SetupController@deletePurchasesTemplateDetails');

    Route::get('purchases-entity-depto-templates', 'SetupController@listPurchasesEntityDeptoTemplates');
    Route::get('purchases-entity-depto-templates/{id_edplantilla}', 'SetupController@showPurchasesEntityDeptoTemplates');
    Route::post('purchases-entity-depto-templates', 'SetupController@addPurchasesEntityDeptoTemplates');
    Route::delete('purchases-entity-depto-templates/{id_edplantilla}', 'SetupController@deletePurchasesEntityDeptoTemplates');
});

Route::group(['prefix' => 'purchases/reports', 'namespace' => 'Purchases'], function () {
    Route::get('purchases', 'PurchasesController@purchasesBalances');
    Route::get('purchases/others-vouchers', 'PurchasesController@purchasesBalancesOthersVouchers');
    Route::get('purchases/of-retentions', 'PurchasesController@purchasesBalancesOfRetencion'); // Documentos para retención
    Route::get('purchases/to-update', 'PurchasesController@purchasesBalancesToUpdate');
    Route::get('purchases-all', 'PurchasesController@purchasesBalancesAll');

    //====== TAPAS DE COMPRAS

    Route::get('purchases-summary', 'PurchasesController@lisPurchasesSummary');
    Route::get('purchases-details', 'PurchasesController@lisPurchasesDetails');

    // para generar pdf export
    Route::get('purchases-summary-pdf', 'PurchasesController@lisPurchasesSummaryPdf');
    Route::get('purchases-details-pdf', 'PurchasesController@lisPurchasesDetailsPdf');

    Route::get('purchases-users', 'PurchasesController@lisUsersVoucher');
    // Detalle compra
    Route::get('resumen-compra-detalle', 'PurchasesController@getDetalleCompra');
    Route::get('seats-compra-detalle', 'PurchasesController@asientoCompra');

    Route::get('purchase-vale-relation', 'PurchasesController@purchasesValesRelacionados');

    Route::get('provider-accounts', 'ReportsController@getProviderAccounts');
});


Route::group(['prefix' => 'gth', 'namespace' => 'HumanTalent'], function () {
    Route::get('validardocumento', 'SignatureControlle@validarview');
    Route::post('validardocumento', 'SignatureControlle@validarDocumento')->name('gth/validardocumento');
});


Route::group(['prefix' => 'humantalent', 'namespace' => 'HumanTalent'], function () {

    Route::get('dashboard', 'DashboardController@dashboard');

    Route::get('process-tickets', 'PaymentsController@listProcessTicket');
    Route::get('process-tickets-area', 'PaymentsController@listProcessTicketArea');
    Route::get('process-tickets-listperson', 'PaymentsController@listProcessTicketPerson');
    Route::post('process-tickets', 'PaymentsController@generatePaymentsTickets');
    Route::post('process-tickets-notice', 'PaymentsController@sendEmail');
    Route::get('process-tickets-persona', 'SignatureControlle@personacertificado');


    Route::get('process-tickets-pdfweb', 'ServiceapiController@previapdfweb');
    Route::get('process-tickets-lstprevia', 'ServiceapiController@listaPrevia');


    Route::post('process-tickets/copy', 'PaymentsController@linkBoletaPDF'); ////*********************** */
    Route::post('process-tickets/delete', 'PaymentsController@unlinkBoletaPDF');

    Route::get('payments-tracings', 'PaymentsController@listPaymentTracing');
    Route::get('payments-tracings-sunafil', 'PaymentsController@listPaymentTracingSUNAFIL');
    Route::delete('payments-tracings/{id}', 'PaymentsController@deletePaymentTicket');

    Route::get('payments-process', 'PaymentsController@listProcesos');

    Route::get('payments-tickets-worker-anhos', 'PaymentsController@anhoPayments');
    Route::get('payments-tickets-worker', 'PaymentsController@listPaymentTicket');
    Route::post('payments-tickets-worker-show', 'PaymentsController@showBoletaPDF');
    Route::put('payments-tickets-worker/{clave}', 'PaymentsController@updateBoletaPDF');
    Route::get('payments-tickets-worker-download', 'PaymentsController@downloadBoletaPDF');
    Route::get('payments-tickets-worker-display', 'PaymentsController@displayBoletaPDF'); ////*********************** */
    Route::post('payments-tickets-worker-email', 'PaymentsController@emailBoletaPDF'); ////*********************** */
    Route::get('payments-tickets-worker-pdf', 'PaymentsController@getBoletaPDF');


    Route::get('certificates', 'SignatureController@listCertificate');
    Route::post('certificates', 'SignatureController@addCertificate');
    Route::get('certificates/{id_certificado}', 'SignatureController@showCertificate');
    Route::delete('certificates/{id_certificado}', 'SignatureController@deleteCertificate');
    Route::post('certificates/{id_certificado}', 'SignatureController@editCertificate');
    Route::post('certificates/{id_certificado}/depto', 'SignatureController@addCertificateDepto');
    Route::delete('certificates/{id_certificado}/depto/{id_entidad}/{id_depto}', 'SignatureController@deleteCertificateDepto');

    Route::post('certificates/{id_certificado}/validar', 'SignatureController@validarCertificado');

    Route::get('certificates/active', 'SignatureController@certificadoactivos');


    Route::get('mailmobiles', 'PaymentsController@listEmailCelular');
    Route::post('mailmobiles', 'PaymentsController@procEmailCelular');

    Route::get('personal-file', 'ReporteController@fichapersona');
    Route::get('personal-file-pdf', 'ReporteController@getPdfFichaPersona');
    Route::post('personal-upload-document', 'ReporteController@uploadDocument');
    Route::post('delete-upload-document', 'ReporteController@deleteDocument');
    Route::get('employee-file', 'ReporteController@fichapersonadni')->middleware('resource:humantalent/employee-file');


    Route::get('statement-afpnet', 'ReporteController@afpnet');
    Route::get('statement-afpnet-pdf', 'ReporteController@getPdfAFPNET');
    Route::get('statement-afpnet-orders-details', 'ReporteController@getPdfAFPNET');
    Route::get('statement-taxdistribution', 'ReporteController@taxdistribution');
    Route::get('statement-taxdistribution-pdf', 'ReporteController@getPdfTaxDistribution');

    Route::get('statement-taxdistribution-educative', 'ReporteController@taxdistributionEducative');
    Route::get('statement-taxdistribution-educative-pdf', 'ReporteController@getPdfTaxDistributionEducative');
    // Registro y actualización de plame impuesto
    Route::get('statement-taxplame', 'ReporteController@listTaxPlame');
    Route::post('statement-taxplame', 'ReporteController@addTaxPlame');

    Route::get('search-person', 'ReporteController@buscarPersona');
    Route::get('polygon-user-assis', 'AssistanceController@polygonUserAssis');
    Route::get('polygons', 'AssistanceController@PolygonsByDepartParent');
    Route::get('polygons/departaments-entity', 'AssistanceController@DepartmentsByEntity');
    Route::post('polygons', 'AssistanceController@PolygonsSave');
    Route::get('polygons/departaments', 'AssistanceController@DepartmentsPolygon');
    Route::post('polygons/departaments', 'AssistanceController@DepartmentsPolygonSave');
    Route::delete('polygons/departaments', 'AssistanceController@DepartmentsPolygonDelete');
    Route::get('polygons/persons', 'AssistanceController@PersonsPolygon');
    Route::post('polygons/persons', 'AssistanceController@PersonsPolygonSave');
    Route::delete('polygons/persons', 'AssistanceController@PersonsPolygonDelete');
    Route::get('polygons/dep_or_per', 'AssistanceController@SelectDepOrPerPolygon');
    Route::put('polygon', 'AssistanceController@PolygonConfigUpdate');
    Route::delete('polygon', 'AssistanceController@PolygonConfigDelete');
    Route::post('assistance', 'AssistanceController@SaveAssistance');
    Route::post('assistance-user-test', 'AssistanceController@AssistanceByUserTest');
    Route::post('reset-assistance-test', 'AssistanceController@ResetAsisTest');
    Route::post('save-user-device', 'AssistanceController@SaveUserDevice');
    Route::get('user-device', 'AssistanceController@UserDevice');
    Route::get('assistance-user', 'AssistanceController@AssistanceUser');
    Route::get('devices-user', 'AssistanceController@DevicesUser');
    Route::put('devices-key', 'AssistanceController@DevicesUserUpdateKey');
    Route::post('active-back-geo', 'AssistanceController@UsersActiveBackGeo');
    Route::get('areas-resp', 'AssistanceController@AreasResponsable');
    Route::put('change-device', 'AssistanceController@DeviceUserChangeDevice');
    Route::get('directory', 'DirectoryController@listDirectory');
    Route::get('exportxlsdirectory', 'DirectoryController@exportXlsDirectory');

    # Social Benefits
    // Route::get('years-payroll-registry', 'SocialBenefitsController@getYearPayrollRegistry');
    Route::get('cts-total', 'SocialBenefitsController@ctsTotal');
    Route::get('cts-calculation', 'SocialBenefitsController@ctsCalculation');
    Route::get('cts-calculation-pdf', 'SocialBenefitsController@getPdfCtsCalculation');
    Route::get('cts-summary', 'SocialBenefitsController@ctsSummary');
    Route::get('cts-summary-pdf', 'SocialBenefitsController@getPdfCtsSummary');
    Route::get('cal-cts-filter-id', 'SocialBenefitsController@getidCalCts');
    Route::get('cts-provision', 'SocialBenefitsController@ctsProvision');
    Route::get('cts-provision-pdf', 'SocialBenefitsController@getPdfCtsProvision');
    Route::get('download-format-cta-bancaria', 'SocialBenefitsController@getDataFormatCtaBancaria');

    Route::get('cts-constancia-pdf', 'SocialBenefitsController@getConstanciaDepCtsPerson');
    Route::get('person-bank-account', 'SocialBenefitsController@getPersonBankAccount');

    Route::get('gratification-total', 'SocialBenefitsController@gratificationTotal');
    Route::get('gratification-summary', 'SocialBenefitsController@gratificacionSummary');
    Route::get('gratification-summary-pdf', 'SocialBenefitsController@getPdfGratificationSummary');
    Route::get('gratification-provision', 'SocialBenefitsController@gratificationProvision');
    Route::get('gratification-provision-pdf', 'SocialBenefitsController@getPdfGratificationProvision');
    Route::get('gratification-calculation', 'SocialBenefitsController@gratificationCalculation');
    Route::get('gratification-calculation-pdf', 'SocialBenefitsController@getPdfGratificationCalculation');

    # Fifth Category
    Route::get('fifth-category-total', 'FifthCategoryController@fifthCategoryTotal');
    Route::get('fifth-category-projection', 'FifthCategoryController@fifthCategoryProjection');
    Route::get('fifth-category-projection-pdf', 'FifthCategoryController@getPdfFifthCategoryProjection');
    Route::get('fifth-category-adjustment', 'FifthCategoryController@fifthCategoryAdjustment');
    Route::get('fifth-category-adjustment-pdf', 'FifthCategoryController@getPdfFifthCategoryAdjustment');
    Route::get('fifth-category-certificate-pdf', 'FifthCategoryController@getPdfFifthCategoryCertificate');


    Route::get('dias', 'AssistanceController@DiasPolygon');
    Route::post('polygon-enable-day', 'AssistanceController@SavePolygonEnableDay');
    Route::get('polygon-enable-day', 'AssistanceController@GetPolygonEnableDay');
    Route::delete('polygon-enable-day', 'AssistanceController@DestroyPolygonEnableDay');
    Route::get('list-bank', 'SocialBenefitsController@listBank');
    Route::get('add-up-pers-bank-account', 'SocialBenefitsController@adUpPersonBankAccount');
    Route::post('import-person-account-bank', 'SocialBenefitsController@importPersonBankAccount');

    Route::get('cts-letter-pdf', 'SocialBenefitsController@getPdfCartaCtsLiquidation');
    Route::get('job-certificate-pdf', 'SocialBenefitsController@getPdfJobCertificateLiquidation');
    Route::get('affidavit-liquidation-pdf', 'SocialBenefitsController@getPdfAffidavitLiquidation');
    Route::get('holiday-record-pdf', 'SocialBenefitsController@getPdfHolidayRecord');
    Route::get('liquidation-calculation', 'SocialBenefitsController@getliquidationCalculation');
    Route::get('person-liquidation', 'SocialBenefitsController@dataPersonLiquidation');

    Route::get('cessation-type', 'SocialBenefitsController@listCessationType');
    Route::get('pension-system', 'SocialBenefitsController@listPensionSystem');

    #Ficha financiera
    Route::get('summary-accounts', 'FinancialStatementController@getSummaryAccounts');
    Route::get('summary-accounts-pdf', 'FinancialStatementController@getSummaryAccountsPdf');
    Route::get('fondos', 'FinancialStatementController@getFondos');

    Route::get('personal-financial-accounts', 'FinancialStatementController@getPersonalFinacialAccounts');
    Route::get('summary-accountsg-pdf', 'FinancialStatementController@getSummaryAccountsPdf');

    #control de conceptos
    Route::get('type-concepts', 'ConceptController@getTypeConcepts');
    Route::get('concepts', 'ConceptController@getConcepts');
    Route::get('paid-concepts', 'ConceptController@getPaidConcepts');


    #DetailInfo
    Route::get('person-info-detail/{id_persona}', 'ReporteController@personInfoDetail');

    Route::get('paid-concepts-pdf', 'ConceptController@getPDFPaidConcepts');
    Route::get('datos-generales', 'ReporteController@getDatosGenrales');
    # Reporte de analisis
    Route::get('ballot-concepts', 'ConceptController@getBallotConcepts');
    Route::get('ballot-concepts-pdf', 'ConceptController@getPDFBallotConcepts');
    Route::get('type-group-account', 'ConceptController@getTypeGroupAccount');
    Route::get('concepts-payroll-aps', 'ConceptController@getConceptsPayrollAps');
    Route::get('concepts-payroll-aps-pdf', 'ConceptController@getPDFConceptsPayrollAps');
    Route::get('balances-concepts', 'ConceptController@getBalanceConcepts');
    Route::get('balances-concepts-pdf', 'ConceptController@getPDFBalanceConcepts');
    Route::get('balance-equity-accounts', 'ConceptController@getBalanceEquityAccounts');
    Route::get('balance-equity-accounts-pdf', 'ConceptController@getPDFBalanceEquityAccounts');
    Route::get('account-balances-spending', 'ConceptController@getAccountBalancesSpending');
    Route::get('account-balances-spending-pdf', 'ConceptController@getPDFAccountBalancesSpending');
    Route::get('detail-equity-accounts', 'ConceptController@getDetailEquityAccounts');

    // concepto planilla cuenta
    Route::get('restriccion', 'ConceptController@getRestriccion');
    Route::get('type-payroll', 'ConceptController@getTypePayroll');
    Route::get('type-contract', 'ConceptController@getTypeContract');
    Route::get('type-plan', 'ConceptController@getTypePlan');
    Route::get('concept-payroll-account', 'ConceptController@getConceptPayrollAccount');

    // configuración de concepto planilla cuenta ficha
    Route::get('payroll_concept_account_tab', 'ConceptController@listPayrollConceptAccountTab');
    Route::get('payroll_concept_account_tab_get', 'ConceptController@getDataToEditPayrollConceptAccount');
    Route::post('payroll_concept_account_tab', 'ConceptController@addPayrollConceptAccountTab');
    Route::post('payroll_concept_account_tab/{id_cuentaaasi}', 'ConceptController@deletePayrollConceptAccountTab');

    //configuración plan ficha financiera
    Route::get('financial_statement_plan', 'ConceptController@getFinancialStatementPlan');
    Route::post('financial_statement_plan', 'ConceptController@addFinancialStatementPlan');
    Route::post('financial_statement_plan/{id_plan_fichafinanciera}', 'ConceptController@editFinancialStatementPlan');
    Route::delete('financial_statement_plan/{id_plan_fichafinanciera}', 'ConceptController@deleteFinancialStatementPlan');
    Route::post('copy-financial-statement', 'ConceptController@copyFinancialStatement');

    //configuración uit
    Route::get('uit', 'FifthCategoryController@getUit');
    Route::post('uit', 'FifthCategoryController@addUit');
    Route::post('uit/{id_uit}', 'FifthCategoryController@editUit');
    Route::delete('uit/{id_uit}', 'FifthCategoryController@deleteUit');

    // Planilla legal
    Route::get('legal-payroll', 'PayrollController@legalPayroll');
    Route::get('legal-payroll-excel', 'PayrollController@legalPayrollExcel');

    //report monthly
    Route::get('config-monthly-control', 'ReporteController@getConfigMonthlyControl');
    Route::post('config-monthly-control', 'ReporteController@addConfigMonthlyControl');
    Route::post('config-monthly-control/{id_archivo_gth}', 'ReporteController@editConfigMonthlyControl');
    Route::delete('config-monthly-control/{id_archivo_gth}', 'ReporteController@deleteConfigMonthlyControl');

    //Crud responsable de subir reportes para gth
    Route::get('responsible-reporting', 'ReporteController@getResponsibleReporting');
    Route::post('responsible-reporting', 'ReporteController@rudResponsibleReporting');

    // crud type file
    Route::get('file-type', 'ReporteController@getTipoArchivo');
    Route::post('file-type', 'ReporteController@addTipoArchivo');

    // control mensual
    Route::get('file-monthly-control', 'ReporteController@getMonthlyControl');
    Route::post('file-monthly-control-upload-file', 'ReporteController@uploadMonthlyControl');
    Route::post('file-monthly-control-delete-file', 'ReporteController@deleteFileMonthlyControl');
    Route::get('file-monthly-control-pdf', 'ReporteController@getMonthlyControlPdf');


    // modulo Reporte de archivos mensual
    Route::get('file-report-type-entity', 'FileReportMonthlyController@getTypeEntity');
    Route::get('file-report-file-type', 'FileReportMonthlyController@getTipoArchivo');
    Route::post('file-report-file-type', 'FileReportMonthlyController@addTipoArchivo');
    Route::get('file-report-type-file', 'FileReportMonthlyController@getTipoArchivoAnhoMes');

    Route::get('config-file-report-monthly-control', 'FileReportMonthlyController@getConfigMonthlyControl');
    Route::post('config-file-report-monthly-control', 'FileReportMonthlyController@addConfigMonthlyControl');
    Route::put('config-file-report-monthly-control/{id_archivo_mensual}', 'FileReportMonthlyController@editConfigMonthlyControl');
    Route::delete('config-file-report-monthly-control/{id_archivo_mensual}', 'FileReportMonthlyController@deleteConfigMonthlyControl');

    Route::get('file-report-file-group', 'FileReportMonthlyController@getFileGroup');
    Route::post('file-report-file-group', 'FileReportMonthlyController@addFileGroup');

    Route::get('file-report-monthly-control', 'FileReportMonthlyController@getMonthlyControl');
    Route::post('file-report-monthly-control-upload-file', 'FileReportMonthlyController@uploadMonthlyControl');
    Route::post('file-report-monthly-control-delete-file', 'FileReportMonthlyController@deleteFileMonthlyControl');
    Route::get('file-report-monthly-control-pdf', 'FileReportMonthlyController@getMonthlyControlPdf');
    Route::get('file-report-monthly-control-summary', 'FileReportMonthlyController@getMonthlyControlSummary');
});
Route::group(['prefix' => 'pwbi', 'namespace' => 'PW_BI'], function () {
    Route::get('bi-academics/{procedure}/{argv?}', 'BiController@BiData');
    Route::get('bi-accounting/{procedure}/{argv?}', 'BiController@BiDataAccount');
    Route::get('bi-pyd/{procedure}/{argv?}', 'BiController@BiDataPyD');
});
Route::group(['prefix' => 'cw', 'namespace' => 'cw'], function () {
    Route::get('estado-cuenta', 'CWController@dataEstadoCuenta');
    Route::get('plan-academico', 'CWController@dataPlanAlumno');
    Route::post('useracademico', 'CWController@userAcademico');
    Route::get('cargas-academicas', 'CWController@cargaAlumno');

    Route::get('cursos', 'CWController@cursosAlumno');


    Route::get('notas-alumno', 'CWController@cursosNotasAlumno');
    Route::post('acceso', 'CWController@acceso');
    Route::get('alumnos/{per_id}', 'CWController@datosAlumno');
    Route::get('contratos', 'CWController@datosContrato');
    Route::post('closesession', 'CWController@closeSession');
    Route::get('pay-balances', 'CWController@datosAlumnoVisa');
    Route::get('ver-notas/{per_id}', 'CWController@validaProrroga');
    Route::get('alertas', 'CWController@alumnoMSN');
    Route::post('alertas', 'CWController@dataMensajeReg');
    Route::get('image', 'CWController@image');
    Route::get('test', 'CWController@alumnoTest');
    //Route::get('acceso', 'CWController@login');
    Route::get('horario', 'CWController@periodoHorario');
    Route::get('alumnos', 'CWController@showAlumno');
    Route::get('data-alumns', 'CWController@dataAlumnoVisa');
    //portal docente
    Route::get('cargas-docente', 'CWController@cargaDocente');
    Route::get('cursos-docente', 'CWController@cargaCursoDocente');
    Route::get('cursos-docente/{curso_carga_id}', 'CWController@showCargaCursoDocente');
    Route::get('cursos-evaluacion', 'CWController@listRubroEvaluacion');
    Route::get('cursos-evaluacion/{evaluacion_id}', 'CWController@showRubroEvaluacion');
    Route::get('alumnos-evaluacion', 'CWController@listAlumnoEvaluacion');
    Route::post('alumnos-evaluacion', 'CWController@addAlumnoEvaluacion');
    Route::get('alumnos-nota', 'CWController@listaAlumnoNota');


    Route::get('horario-dias', 'CWController@listaDiaHorario');

    Route::get('asistencias', 'CWController@listaAsistencia');
    Route::get('cargas-docente/{curso_carga_id}/alumnos', 'CWController@listaAsistenciaNew');
    Route::get('asistencias/{id_asistencia}', 'CWController@listaAsistenciaEdit');
    Route::post('asistencias', 'CWController@procAsistencia');
    Route::delete('asistencias/{id_asistencia}', 'CWController@deleteAsistencia');
    //Route::post('asistencia-new', 'CWController@listaAsistenciaNew');

    //Asistencia a Cultura
    Route::get('escuelas', 'CWController@listEscuelas');
    Route::get('students-assist-control', 'CWController@listStudentsAssistControl');
    Route::post('students-assist-control', 'CWController@addStudentsAssistControl');
    Route::delete('students-assist-control/{per_id}/{ciclo}/{fecha}', 'CWController@deleteStudentsAssistControl');

    Route::get('admission-prices', 'CWController@precioAdmision')->middleware('resource:cw/admission-prices');
    Route::get('thesis-prices', 'CWController@thesisAdmision')->middleware('resource:cw/thesis-prices');
    Route::get('students-epg', 'CWController@showStudentsEPG')->middleware('resource:cw/students-epg');
    Route::get('students-semipresencial', 'CWController@showStudentsSemi')->middleware('resource:cw/students-semipresencial');
    Route::get('students', 'CWController@showStudents')->middleware('resource:cw/students');
    //datos para egresados
    Route::get('datos-egresados', 'CWController@showDNIPerson')->middleware('resource:cw/datos-egresados');
    Route::get('types-assistance', 'CWController@listTypesAssistance');
    Route::get('my-assists', 'CWController@showMyAssists');
    //Registrar Datos y Obetener Datos
    Route::post('registrar-datos', 'CWController@addRegistroDatos')->middleware('resource:cw/registrar-datos');

    Route::get('student-balances', 'CWController@saldosAlumno');

    Route::post('deposit-sale-eda', 'CWController@depSalesEda')->middleware('resource:cw/deposit-sale-eda');
    //====== nuevos metodos

});

Route::group(['prefix' => 'mobile', 'namespace' => 'Mobile'], function () {
    Route::post('login', 'OptionsController@login');
    Route::post('forget_password', 'OptionsController@forget_password');
    Route::post('user_data', 'OptionsController@user_data');
    Route::post('st_data', 'OptionsController@st_data');
    Route::post('st_data_items', 'OptionsController@st_data_items');
    Route::post('st_data_details', 'OptionsController@st_data_details');
    Route::post('dep_data', 'OptionsController@dep_data');
    Route::post('dep_data_items', 'OptionsController@dep_data_items');
    Route::post('dep_data_details', 'OptionsController@dep_data_details');
    Route::post('tra_data', 'OptionsController@tra_data');
    Route::post('tra_data_details', 'OptionsController@tra_data_details');
    Route::post('persons', 'OptionsController@persons');
    Route::post('persons_directory', 'OptionsController@persons_directory');
    Route::post('persons_directory_search', 'OptionsController@persons_directory_search');

    Route::post('st_data_salary_ingresos', 'OptionsController@st_data_salary_ingresos');
    Route::post('st_data_salary_descuentos', 'OptionsController@st_data_salary_descuentos');

    Route::post('st_data_salary_ingresos_ayudas', 'OptionsController@st_data_salary_ingresos_ayudas');
    Route::post('st_data_salary_descuentos_ayudas', 'OptionsController@st_data_salary_descuentos_ayudas');

    Route::post('st_data_salary_ingresos_viajes', 'OptionsController@st_data_salary_ingresos_viajes');
    Route::post('st_data_salary_descuentos_viajes', 'OptionsController@st_data_salary_descuentos_viajes');


    Route::post('persons_year', 'OptionsController@persons_year');
    Route::get('persons_year_search', 'OptionsController@persons_year_search');
    Route::post('financial_graphs_datagraph', 'OptionsController@financial_graphs_datagraph');
    Route::post('financial_graphs_datagraph_options', 'OptionsController@financial_graphs_datagraph_options');
    Route::get('version', 'OptionsController@version');
    Route::post('version_validate', 'OptionsController@version_validate');
    Route::post('profile_worker', 'OptionsController@profile_worker');
    Route::post('account_statement_salary_receipts_detail', 'OptionsController@st_data_salary_ingresos_array');
    Route::post('account_statement_salary_discounts_detail', 'OptionsController@st_data_salary_descuentos_array');
});

Route::group(['prefix' => 'budget', 'namespace' => 'Budget'], function () {
    Route::get('listdepto', 'BudgetController@listDepto');

    //proyecto
    Route::get('projects', 'ConfiguracionController@listProject');
    Route::get('projects/{id_proyecto}', 'ConfiguracionController@showProject');
    Route::post('projects', 'ConfiguracionController@addProject');
    Route::put('projects/{id_proyecto}', 'ConfiguracionController@updateProject');
    Route::patch('projects/{id_proyecto}', 'ConfiguracionController@updateEstadoProject');
    Route::delete('projects/{id_proyecto}', 'ConfiguracionController@deleteProject');


    //Eventos
    Route::get('events', 'ConfiguracionController@listEvent');
    Route::get('events/{id_evento}', 'ConfiguracionController@showEvent');
    Route::post('events', 'ConfiguracionController@addEvent');
    Route::put('events/{id_evento}', 'ConfiguracionController@updateEvent');
    Route::patch('events/{id_evento}', 'ConfiguracionController@updateEstadoEvent');
    Route::delete('events/{id_evento}', 'ConfiguracionController@deleteEvent');

    //Actividad
    Route::get('activity', 'ConfiguracionController@listActivity');
    Route::get('activity/{id_actividad}', 'ConfiguracionController@showActivity');
    Route::post('activity', 'ConfiguracionController@addActivity');
    Route::put('activity/{id_actividad}', 'ConfiguracionController@updateActivity');
    Route::patch('activity/{id_actividad}', 'ConfiguracionController@updateEstadoActivity');
    Route::delete('activity/{id_actividad}', 'ConfiguracionController@deleteActivity');

    //actividad distribucion
    Route::get('activitydist', 'ConfiguracionController@listActivityDist');
    Route::put('activitydist/{id_actividad}', 'ConfiguracionController@updateActivityDist');

    //presupuesto
    Route::get('budget', 'BudgetController@listBudget');
    Route::get('budget/{id_presupuesto}', 'BudgetController@showBudget');
    Route::post('budget', 'BudgetController@addBudget');
    Route::put('budget/{id_presupuesto}', 'BudgetController@updateBudget');
    Route::put('budget-estado/{id_presupuesto}', 'BudgetController@estadoBudget');
    Route::delete('budget/{id_presupuesto}', 'BudgetController@deleteBudget');

    //Presupuesto detalle
    Route::get('budgetdetail', 'BudgetController@listBudgetDetail');
    Route::get('budgetdetail-lista', 'BudgetController@listBudgetDetailRep');
    Route::get('budgetdetail-total', 'BudgetController@listBudgetDetailTotal');
    Route::get('budgetdetail-dist', 'BudgetController@listBudgetDetailDist');

    //Mis Eventos y Actividades
    Route::get('my-events', 'BudgetController@listMyEvents');
    Route::get('my-events-activities', 'BudgetController@listMyEventsActivities');
});
Route::group(['prefix' => 'budget/assistant', 'namespace' => 'Budget'], function () {
    //pregrado
    Route::get('concepto-pregrado', 'AuxiliarController@listConceptoPregrado');
    Route::put('concepto-pregrado', 'AuxiliarController@updateConceptoPregrado');
    Route::put('concepto-pregrado-proceso', 'AuxiliarController@updatePregradoConceptoProceso');
    //Route::post('concepto-pregrado', 'AuxiliarController@addConcepto');


    Route::get('proyeccion-pregrado', 'AuxiliarController@listPregradoProyeccion');
    Route::put('proyeccion-pregrado', 'AuxiliarController@updatePregradoProyeccion');
    //Route::post('proyeccion-pregrado', 'AuxiliarController@addProyeccion');


    Route::get('proceso-pregrado', 'AuxiliarController@listPregradoProceso');
    Route::put('proceso-pregrado', 'AuxiliarController@updatePregradoProceso');
    Route::get('proceso-pregrado/{id_pregado_proceso}/{tipo}', 'AuxiliarController@listPregradoProcesoConcepto');
    Route::post('proceso-pregrado', 'AuxiliarController@addProceso');
    Route::post('proceso-pregrado-inicial', 'AuxiliarController@generarProcesoInicial');

    Route::post('proceso-presupuesto', 'BudgetController@addBudgetOfAuxiliar');

    //residencia
    //Route::get('proceso-residencia','AuxiliarController@listPregradoProceso');
    Route::put('proceso-residencia', 'AuxiliarController@updateResidenciaProceso');
    //conservatorio
    //Route::get('proceso-conservatorio','AuxiliarController@listPregradoProceso');
    Route::put('proceso-conservatorio', 'AuxiliarController@updateConservatorioProceso');

    Route::put('proceso-idiomas', 'AuxiliarController@updateIdiomasProceso');

    //POSGRADO
    Route::get('proyeccion-posgrado', 'AuxiliarController@listPosgradoProyeccion');
    Route::post('proyeccion-posgrado', 'AuxiliarController@addPosgradoProyeccion');
    Route::put('proyeccion-posgrado', 'AuxiliarController@updatePosgradoProyeccion');
    Route::delete('proyeccion-posgrado/{id_proyeccion}', 'AuxiliarController@deletePosgradoProyeccion');
    //Route::get('proceso-posgrado', 'AuxiliarController@listPregradoProceso');
    Route::put('concepto-posgrado-proceso', 'AuxiliarController@updateConceptoPosgradoProceso');
    Route::put('concepto-posgrado-descuento', 'AuxiliarController@updatePregradoConceptoDescuento');
    //Route::put('proceso-posgrado', 'AuxiliarController@updatePosgradoProceso');
    //Route::post('presupuesto-pregrado','BudgetController@addBudgetOfAuxiliar');
    //residencia
    /*Route::get('proceso-residencia','AuxiliarController@listResidenciaProceso');
    Route::put('proceso-residencia', 'AuxiliarController@updateResidenciaProceso');
    //conservatorio
    Route::get('proceso-conservatorio','AuxiliarController@listConservatorioProceso');
    Route::put('proceso-conservatorio', 'AuxiliarController@updateConservatorioProceso');
      * */
    //tesis
    Route::get('payrolls', 'AuxiliarController@listPlanilla');
    Route::post('payrolls', 'AuxiliarController@presupuestoPlanilla');
    Route::get('payrolls-consolidado', 'AuxiliarController@listConsolPlanilla');
});

Route::group(['prefix' => 'budget/operations', 'namespace' => 'Budget'], function () {
    Route::get('axis', 'ConfiguracionController@listEjeActivo');
    Route::get('business-unit', 'ConfiguracionController@listUnitNegocioActivo');
    //Route::get('area', 'ConfiguracionController@listAreaActivo');

    Route::get('area-depto', 'ConfiguracionController@listAreaDepto');
    Route::get('projects', 'ConfiguracionController@listProyectoActivo');
    Route::get('assistant', 'ConfiguracionController@listAuxliarActivo');
    Route::get('assistant-anho', 'AuxiliarController@listAuxiliar');
    Route::get('ctacte', 'ConfiguracionController@listCtaCte');
    Route::get('events-project', 'ConfiguracionController@listEventProyecto');
    Route::get('activity-evento', 'BudgetController@listActivityEvento');
    Route::get('listdepto', 'ConfiguracionController@listDeptoActivo');
    Route::get('listdeptofac', 'ConfiguracionController@listDeptoFac');
    Route::get('listdeptotipo', 'ConfiguracionController@listDeptoPregrado');
    Route::get('listmencion', 'ConfiguracionController@listMenecion');

    Route::get('listrenovable', 'ConfiguracionController@listRenovable');
    Route::get('listsexo', 'ConfiguracionController@listSexo');
    Route::get('listedad', 'ConfiguracionController@listEdad');
    Route::get('listniveleducativo', 'ConfiguracionController@listNivelEducativo');
    Route::get('listestadocivil', 'ConfiguracionController@listEstadoCivil');
    Route::get('listtiempotrabajo', 'ConfiguracionController@listTiempoTrabajo');
    Route::get('listtemporada', 'ConfiguracionController@listTemporada');
    Route::get('listcondicionlaboral', 'ConfiguracionController@listCondicionLaboral');
    Route::get('listprofesion', 'ConfiguracionController@listProfesion');
    Route::get('listtipocontrato', 'ConfiguracionController@listTipoContrato');
    Route::get('listcolumn', 'ConfiguracionController@listColumnas');
    Route::get('conceptoaps', 'ConfiguracionController@listConceptoaps');
    Route::get('cargo', 'ConfiguracionController@listCargo');
    Route::get('cargo/{id_cargo}', 'ConfiguracionController@showCargo');
    Route::get('condicion-escala', 'ConfiguracionController@listCondicionEscala');
    Route::get('tipo-estatus', 'ConfiguracionController@listTipoEstatus');
    Route::get('tipo-pais', 'ConfiguracionController@listTipoPais');
    Route::get('departamento-area', 'ConfiguracionController@listDepatamentoArea');
});

Route::group(['prefix' => 'budget/payroll', 'namespace' => 'Budget'], function () {
    //parametro
    Route::get('parametro', 'PayrollController@listParametro');
    Route::put('parametro', 'PayrollController@updateParametro');
    Route::post('parametro', 'PayrollController@procParametro');
    //datos anteriores
    Route::get('planillaant', 'PayrollController@listConceptoPlanillaAnt');
    Route::post('planillaant', 'PayrollController@addConceptoPlanillaAnt');
    Route::delete('planillaant/{id_concepto_aps}/{columna_imp}', 'PayrollController@deleteConceptoPlanillaAnt');
    //concepto actividad
    Route::get('conceptoactividad', 'PayrollController@listConceptoActividad');
    Route::get('conceptoactividad/{id_concepto_actividad}', 'PayrollController@showConceptoActividad');
    Route::post('conceptoactividad', 'PayrollController@procConceptoActividad');
    Route::put('conceptoactividad/{id_concepto_actividad}', 'PayrollController@updateConceptoActividad');
    Route::delete('conceptoactividad/{id_concepto_actividad}', 'PayrollController@deleteConceptoActividad');

    //carga sueldo escala
    Route::get('sueldoescala', 'PayrollController@listCargoSueldoEscala');
    Route::get('sueldoescala-all', 'PayrollController@cargoSueldoEscalaAll');
    Route::get('sueldoescala/{showCargoSueldoEscala}', 'PayrollController@showCargoSueldoEscala');
    Route::put('sueldoescala', 'PayrollController@updateCargoSueldoEscala');
    Route::post('sueldoescala', 'PayrollController@procCargoSueldoEscala');
    Route::post('sueldoescala-new', 'PayrollController@addCargoSueldoEscala');
    Route::delete('sueldoescala/{id_cargosueldo_escala}', 'PayrollController@deleteCargoSueldoEscala');

    //proceso cargo
    Route::get('budget-role', 'PayrollController@listProcesoCargo');
    Route::get('budget-role/{id_cargo_proceso}', 'PayrollController@showProcesoCargo');
    Route::put('budget-role/{id_cargo_proceso}', 'PayrollController@updateProcesoCargo');
    Route::post('budget-role', 'PayrollController@addProcesoCargo');
    Route::delete('budget-role/{id_cargo_proceso}', 'PayrollController@deleteProcesoCargo');

    Route::put('budget/{id_presupuesto}', 'BudgetController@updateBudget');
    //planilla
    Route::get('budget-person', 'PayrollController@listPlanilla');
    //Route::post('proceso-planilla','PayrollController@procPlanilla');
    Route::get('budget-person/{id_entidad}/year/{id_anho}/person/{id_persona}', 'PayrollController@validarDatosAnt');
    Route::post('budget-person', 'PayrollController@addPlanilla');
    Route::patch('budget-person/{id_psto_planilla}', 'PayrollController@UpdatePlanillaPersona');
    Route::get('budget-person/{id_psto_planilla}', 'PayrollController@showPlanilla');
    Route::put('budget-person', 'PayrollController@updatePlanilla');
    Route::delete('budget-person/{id_psto_planilla}', 'PayrollController@deletePlanilla');

    //planilla distribucion
    Route::get('distribution-level', 'PayrollController@listPlanillaDist');
    Route::get('distribution-level/{id_psto_planilla_dist}', 'PayrollController@showPlanillaDist');
    Route::put('distribution-level/{id_psto_planilla_dist}', 'PayrollController@updatePlanillaDist');
    Route::post('distribution-level', 'PayrollController@addPlanillaDist');
    Route::delete('distribution-level/{id_psto_planilla_dist}', 'PayrollController@deletePlanillaDist');
    //planilla actividad
    Route::get('planilla-distdeta', 'PayrollController@listPlanillaDistDet');

    //ayuda
    Route::get('helps', 'PayrollController@listAyuda');
    Route::put('helps', 'PayrollController@updateAyuda');
    Route::post('helps', 'PayrollController@addAyuda');
    Route::delete('helps/{id_psto_ayuda}', 'PayrollController@deleteAyuda');

    //movilidad libre disponibilidad
    Route::get('provisions-transports', 'PayrollController@listMobLibDis');
    Route::put('provisions-transports', 'PayrollController@updateMobLibDis');
    Route::post('provisions-transports', 'PayrollController@addMobLibDis');
    Route::delete('provisions-transports/{id_psto_movlibdis}', 'PayrollController@deleteMobLibDis');

    //puntaje misionero
    Route::get('missionary-score', 'PayrollController@listPuntajeMis');
    Route::get('missionary-score/{id_entidad}/year/{id_anho}', 'PayrollController@valPuntajeMis');
    Route::get('missionary-score/{id_entidad}/year/{id_anho}/person/{id_persona}', 'PayrollController@validarDatosMis');
    Route::put('missionary-score', 'PayrollController@updatePuntajeMis');
    Route::post('missionary-score', 'PayrollController@addPuntajeMis');
    Route::post('missionary-score-proc', 'PayrollController@procPuntajeMis');
    Route::delete('missionary-score/{id_psto_puntaje_mis}', 'PayrollController@deletePuntajeMis');

    //vivienda
    Route::get('remuneration-dwelling', 'PayrollController@listVivienda');
    Route::put('remuneration-dwelling', 'PayrollController@updateVivienda');
    Route::post('remuneration-dwelling', 'PayrollController@addVivienda');
    Route::delete('remuneration-dwelling/{id_psto_vivienda}', 'PayrollController@deleteVivienda');
});

Route::group(['prefix' => 'budget/reports', 'namespace' => 'Budget'], function () {
    Route::get('pregrado-proyeccion', 'ReportController@listPregradoProyeccion');
    Route::get('pregrado-proceso', 'ReportController@listPregradoProceso');
    Route::get('proesad-proyeccion', 'ReportController@listProesadProyeccion');
    Route::get('proesad-proceso', 'ReportController@listProesadProceso');
    Route::get('asiento-resultado', 'ReportController@listResultado');

    Route::get('summary-payrolls', 'ReportController@listSummaryPayroll');
    Route::get('summary-payrolls/proyects/{id_proyecto}', 'ReportController@listSummaryEvent');
    Route::get('summary-payrolls/fatherareas/{id_area_padre}', 'ReportController@listSummarySubArea');
    Route::get('summary-payrolls/areas/{id_area}', 'ReportController@listSummaryDepartament');
});

Route::group(['prefix' => 'bi01', 'namespace' => 'Bi01'], function () {
    Route::get('bi_entidad', 'BiController@bi_entidad');
    Route::get('bi_mes', 'BiController@bi_mes');
    Route::get('bi_saldo_departamentos', 'BiController@bi_saldo_departamentos');
    Route::get('bi_extra/{procedure}/{argv}', 'BiController@bi_procedure');
});

Route::group(['prefix' => 'provider/services', 'namespace' => 'ProviderServices'], function () {
    Route::post('acceso-academico', 'ProviderController@accesoAcademico')->middleware('resource:provider/services/acceso-academico');
    Route::get('planilla', 'ProviderController@datosPlanilla')->middleware('resource:provider/services/planilla');
    Route::get('entities', 'ProviderController@listEntidades')->middleware('resource:provider/services/entities');
    Route::get('entities-departments/{id_entidad}', 'ProviderController@listEntidadesDepartments')->middleware('resource:provider/services/entities-departments');

    Route::get('personal-information', 'ProviderController@showPersonalInformation')->middleware('resource:provider/services/personal-information');
    // test
});

Route::group(['prefix' => 'visa', 'namespace' => 'Payonline'], function () {
    Route::get('shopping', 'VisaController@shopping');
    Route::post('tokens', 'VisaController@tokens')->name('visa/tokens');
    Route::get('tokensapp', 'VisaController@tokensapp')->name('visa/tokensapp');
    Route::post('print', 'VisaController@imprimir');
    Route::get('terminos', 'VisaController@terminos');
});

Route::group(['prefix' => 'mc', 'namespace' => 'Payonline'], function () {
    Route::get('shopping', 'McController@shopping');
    Route::post('tokens', 'McController@tokens')->name('mc/tokens');
    Route::post('print', 'McController@imprimir')->name('mc/print');
    Route::get('terminos', 'McController@terminos')->name('mc/terminos');
});
Route::group(['prefix' => 'interconexion', 'namespace' => 'Payonline'], function () {
    Route::get('deuda', 'InterconnectionController@listarDeuda');
});


Route::group(['prefix' => 'cleaningcontrol', 'namespace' => 'Cleaning'], function () {

    Route::get('group', 'CleaningController@listGroup');
    Route::get('group/{id_grupo}', 'CleaningController@showGroup');
    Route::post('group', 'CleaningController@addGroup');
    Route::put('group/{id_grupo}', 'CleaningController@updateGroup');
    Route::delete('group/{id_grupo}', 'CleaningController@deleteGroup');

    Route::get('operations/persons', 'CleaningController@listPersona');

    Route::get('groupintegrant', 'CleaningController@listGrupoIntegrantes');
    Route::get('groupintegrant/{id_grupo_integrante}', 'CleaningController@showGrupoIntegrantes');
    Route::post('groupintegrant/{id_grupo}', 'CleaningController@addGrupoIntegrantes');
    Route::put('groupintegrant/{id_grupo_integrante}', 'CleaningController@updateGrupoIntegrantes');
    Route::delete('groupintegrant/{id_grupo_integrante}', 'CleaningController@deleteGrupoIntegrantes');

    Route::get('groupservice', 'CleaningController@listGrupoServicio');
    Route::get('groupservice/{id_grupo_servicio}', 'CleaningController@showGrupoServicio');
    Route::post('groupservice/{id_grupo}', 'CleaningController@addGrupoServicio');
    Route::put('groupservice/{id_grupo_servicio}', 'CleaningController@updateGrupoServicio');
    Route::delete('groupservice/{id_grupo_servicio}', 'CleaningController@deleteGrupoServicio');


    Route::get('assists-control', 'CleaningController@listAssistsControl');
    Route::put('assists-control/{id_control}', 'CleaningController@updateAssistsControl');
    Route::get('assists-control-grupos', 'CleaningController@listGroupActivo');
    Route::get('assists-control-semana', 'CleaningController@listSemana');
    Route::get('assists-control-grupouser', 'CleaningController@listarGrupoUser');
    Route::get('assists-control-asistencia', 'CleaningController@listAssists');
});


Route::group(['prefix' => 'asist', 'namespace' => 'HumanTalent'], function () {

    Route::get('assists-control', 'AssistanceController@listAssistsControl');
    Route::put('assists-control/{id_control_culto}', 'AssistanceController@updateAssistsControl');
    Route::get('assists-control-deptos', 'AssistanceController@listAssists');
    Route::get('assists-control-semana', 'AssistanceController@listSemana');
    Route::get('assists-control-reports', 'AssistanceController@reportsAssistances');
    Route::get('assists-person-device', 'AssistanceController@AssistPersonDevice');
    Route::get('update-notif-assists', 'AssistanceController@UpdateNotifAssist');
});

Route::group(['prefix' => 'aps', 'namespace' => 'APS'], function () {

    Route::get('test', 'APSController@test');
});

Route::group(['prefix' => 'orders', 'namespace' => 'Orders'], function () {
    Route::get('orders-areas', 'OrdersController@listOrdersAreas');
    Route::post('orders', 'APSController@addOrders');
    Route::put('orders/{id_pedido}', 'OrdersController@updateOrders');
    Route::get('orders-articles', 'OrdersController@listWarehousesArticles');
    Route::get('orders-articles-all', 'OrdersController@listWarehousesArticlesAll');
    Route::get('orders-articles-test', 'OrdersController@listWarehousesArticles');
    Route::get('orders-types-forms', 'OrdersController@listTypesForms');
    Route::get('types-orders', 'OrdersController@listTypesOrders');
    Route::post('orders-seats-generate', 'OrdersController@addOrdersSeatsGenerate');
    Route::post('orders-seats-generate-template', 'OrdersController@addOrdersSeatsGenerateTemplate');
    Route::post('orders-seats-generate-budget', 'OrdersController@addOrdersSeatsGenerateBudget');
    Route::get('orders-seats', 'OrdersController@listOrdersSeats');
    Route::get('orders-seats/{id_pasiento}', 'OrdersController@showOrdersSeats');
    Route::post('orders-seats', 'OrdersController@addOrdersSeats');
    Route::delete('orders-seats/{id_pasiento}', 'OrdersController@deleteOrdersSeats');
    Route::patch('orders-seats-match/{id_pedido}', 'OrdersController@executeOrdersSeatsMatch');
    Route::patch('orders-seats-approved/{id_pedido}', 'OrdersController@executeOrdersSeatsApproved');
    Route::get('order-areas', 'SettingsController@listAreas');
    Route::get('orders-types-areas', 'OrdersController@listOrdersTypesAreas');
    Route::post('orders-types-areas', 'OrdersController@addOrdersTypesAreas');
    Route::put('orders-seats/{id_pasiento}', 'OrdersController@updateOrdersSeats');
    Route::post('orders-seats-imports', 'OrdersController@addOrdersSeatsImports');
    Route::get('types-orders-key', 'OrdersController@showTypesOrders');
    Route::get('order-areas-search', 'SettingsController@listAreasSearch');

    //lista de pedidos pendiente de: Aprobar, Autorizar y Ejecutar
    Route::get('orders-pending', 'OrdersController@listOrdersPending');
    Route::get('cars', 'OrdersController@listCars');
    Route::get('persons-drivers', 'OrdersController@listDrivers');

    //Ejecutar Pedidos - PEDIDO_DESPACHO
    Route::get('orders-dispatches', 'OrdersController@listOdersDispatches');
    Route::post('orders-dispatches', 'OrdersController@addOdersDispatches');
    Route::put('orders-dispatches/{id_pedido}', 'OrdersController@saveOrdersDispatchesOff');
    Route::delete('orders-dispatches/{id_despacho}', 'OrdersController@deleteOdersDispatches');
    Route::put('orders-dispatches/{id_despacho}/details', 'OrdersController@updateOdersDispatches');
    //Ejecutar Pedidos - PEDIDO_DESPACHO - MOVILIDAD
    Route::get('orders-to-dispatches', 'OrdersController@listOdersDispatchesMovi');
    Route::post('orders-to-dispatches', 'OrdersController@addOdersDispatchesMovi');
    Route::put('orders-to-dispatches/{id_pedido}', 'OrdersController@saveOrdersDispatchesMoviOff');
    Route::delete('orders-dispatches-cars/{id_movilidad}', 'OrdersController@deleteCars');
    Route::delete('orders-dispatches-drivers/{id_movilidad}', 'OrdersController@deleteDrivers');

    //Pedidos Canchas Deportivas
    Route::get('grass-schedule', 'OrdersController@listGrassSchedule');
    Route::post('orders-schedule', 'OrdersController@addOrdersDetailsShedule');
    Route::get('grass-shedule-details', 'OrdersController@listOrdersDetailsShedule');
    Route::delete('grass-shedule-details/{id_reserva}', 'OrdersController@deleteOrdersDetailsShedule');
    Route::put('grass-shedule-details/{id_reserva}/details', 'OrdersController@updateOrdersDetailsShedule');

    // Pedidos Canchas Deportivas resporte Sim y Reserva General
    Route::get('grass-schedule-reports', 'OrdersController@listGrassScheduleReports');

    ///envio correo

    Route::post('email-pedidos/{id_pedido}', 'OrdersController@emailPedido');
    Route::get('types-cars', 'OrdersController@listTypesCars');
    Route::get('articles-almacen', 'OrdersController@getArticlesAlmacen');
    Route::get('search-persona-trabajador', 'OrdersController@searchPersonaTrabajador');

    Route::get('depto-grass-sintetico', 'OrdersController@deptoGrasSintetico');
    Route::get('search-persona-trabajador-varios', 'OrdersController@searchPersonaTrabajadorVarios');
    Route::get('depto-to-id-depto', 'OrdersController@deptoToIdDepto');

    Route::get('search-articles-almacen', 'OrdersController@searchsArticlesAlmacen');
    Route::put('details-ejecutar-almacen/{id_detalle}', 'OrdersController@updateDetailsAlmacen');
    Route::get('report-my-order-to-my-area', 'OrdersController@listMyOrdersReportes');
    Route::delete('movilidad-details/{id_movilidad}', 'OrdersController@deleteOrdersDetailsMovilidad');
    Route::get('report-my-order-to-my-area-pdf', 'OrdersController@myOrdersPdf');


    Route::get('order-time-reservation', 'OrdersController@orderTimesReservation');
    Route::post('rango-reserva-detalle', 'OrdersController@addServiciosDetalle');

    Route::get('seat-order-dispaches', 'OrdersController@asientoOrderDispaches');


    Route::put('update-cantidad-autorize/{id_pedido}', 'OrdersController@updateCantidadPedidos');
    // Pedido
    Route::post('order-details-comunication', 'OrdersController@addPedidoDetalleAudio');
    Route::get('file-pedido-detalle/{id_detalle}', 'OrdersController@filePedidoDetalle');
    Route::get('time-module-block', 'OrdersController@getTimeLimiteBlock');
    Route::resource('order-register', 'OrderRegisterController');
});

Route::group(['prefix' => 'orders/reports', 'namespace' => 'Orders'], function () {
    Route::get('report-areas', 'OrdersController@listReportsAreas');
    Route::get('report-my-orders', 'OrdersController@genereReportMyOrders');
    Route::get('report-area-order', 'OrdersController@listOrderArea');
    Route::get('report-attentionorders-area', 'OrdersController@listOrdersByAreaDestino');

    Route::get('orders-imports', 'OrdersController@listOrdersDash');
    Route::get('report-orders-register', 'OrdersController@listMyOrdersReportesTotal');
    Route::get('orders-ejecutados-reports', 'OrdersController@pedidosEjecutadosReport');
    Route::get('orders-ejecutados-reports-firts', 'OrdersController@pedidosEjecutadosReportFirts');
    Route::get('orders-ejecutados-reports-firts-xls', 'OrdersController@pedidosEjecutadosReportFirtsXLS');

    Route::get('order-summary-parent', 'OrdersController@orderSummaryParent');
    Route::get('order-summary-parent-pdf', 'OrdersController@orderSummaryParentPDF');
    // tes apis
    //mas apis
    Route::get('test-merge', 'OrdersController@orderSummaryParentPDF');

    Route::get('search-departamento-origen', 'OrdersController@getDeptoOrigen');
    Route::get('detalle-dispatches-orders', 'OrdersController@getOrderEjecutadoVoucherDetalle');
});

Route::group(['prefix' => 'schools', 'namespace' => 'Schools'], function () {
    Route::get('migration-january', 'SchoolsController@listAreas');
    Route::get('areas', 'SchoolsController@listAreas');
    Route::post('areas', 'SchoolsController@addAreas');
    Route::put('areas/{id_curso}', 'SchoolsController@updateAreas'); // xxxxx
    Route::get('reservations', 'SchoolsController@listReservations'); // ???
    Route::get('reservations-my-students', 'SchoolsController@listReservationsMyStudents');
    Route::get('reservations-students/{num_documento}', 'SchoolsController@listReservationsStudents');
    Route::get('reservations-students-both', 'SchoolsController@listReservationsStudentsBoth');
    Route::get('reservations-students-parent', 'SchoolsController@listReservationsStudentsParent');
    Route::post('reservations', 'SchoolsController@addReservations');
    // Route::delete('reservations/{id_reserva}', 'SchoolsController@deleteReservations');x
    Route::patch('reservations/{id_reserva}', 'SchoolsController@patchReservations'); // ???
    Route::patch('reservations-paso-next/{id_reserva}', 'SchoolsController@patchReservationsPasoNext');
    Route::get('record-medical/{id_fmedica}', 'SetupController@showRecordMedical');
    Route::post('record-medical', 'SetupController@addRecordMedical');
    Route::put('record-medical/{id_fmedica}', 'SetupController@updateRecordMedical');
    Route::get('students-download', 'SchoolsController@execStudentsDownload');
    Route::get('hospital-essalud', 'SchoolsController@listHospitalEssalud');
    Route::get('allergys', 'SchoolsController@listAllergys');
    Route::get('employees', 'SchoolsController@listEmployees');
    Route::get('students-search', 'SchoolsController@listStudentsSearch');
    Route::get('questionnaires/{id_cuestionario}', 'SchoolsController@showQuestionnaires');
    Route::get('questionnaires/{id_cuestionario}/download', 'SchoolsController@downQuestionnairesDownload'); // 000
    Route::get('questionnaires-questions-evaluations', 'SchoolsController@listQuestionnairesQuestionsEvaluations');
    Route::post('questionnaires-evaluations', 'SchoolsController@listStudentsSearch');
    Route::put('questionnaires-evaluations', 'SchoolsController@listStudentsSearch');
    Route::patch('questionnaires-evaluations-approved', 'SchoolsController@saveQuestionnairesEvaluationsApproved');
    Route::get('students-attendances', 'SchoolsController@listStudentsSearch');
    Route::get('attendances', 'SchoolsController@listStudentsSearch');
    Route::get('transfers', 'SchoolsController@listTransfers');
    Route::post('transfers', 'SchoolsController@addTransfers');
    Route::put('transfers/{id_traslado}', 'SchoolsController@updateTransfers');
    Route::patch('transfers/{id_traslado}', 'SchoolsController@patchTransfers');
    Route::get('retirements', 'SchoolsController@listRetirements');
    Route::post('retirements', 'SchoolsController@addRetirements');
    Route::put('retirements/{id_retiro}', 'SchoolsController@updateRetirements');
    //Route::get('reservations', 'SchoolsController@listReservations');// ???
    //Route::post('reservations', 'SchoolsController@addReservations');
    //Route::delete('reservations/{id_reserva}', 'SchoolsController@deleteReservations');
    Route::get('agreements', 'SchoolsController@listAgreements'); // ???4
    Route::post('agreements', 'SchoolsController@addAgreements');
    Route::delete('agreements/{id_acuerdo}', 'SchoolsController@deleteAgreements');

    Route::get('periods-check', 'SchoolsController@listPeriodsCheck'); // <--
    Route::get('periods-po', 'SchoolsController@listPeriodsPO');
    Route::get('periods-of-enrollments', 'SchoolsController@listPeriodsOfEnrollments');
    Route::get('periods-areas-missing', 'SchoolsController@listPeriodsAreasMissing');
    Route::get('my-periods-stages', 'SchoolsController@listMyPeriodsStages');
    Route::get('my-periods-stages-grades', 'SchoolsController@listMyPeriodsStagesGrades');
    Route::get('my-periods-stages-grades-sections', 'SchoolsController@listMyPeriodsStagesGradesSections');
    Route::get('my-periods-stages-grades-areas', 'SchoolsController@listMyPeriodsStagesGradesAreas');
    Route::get('periods-stages-grades-areas', 'SetupController@listPeriodsNGAreas');
    Route::post('periods-stages-grades-areas', 'SchoolsController@addPeriodsStagesGradesAreas'); // falta - 2020-01-16
    Route::patch('periods-stages-grades-areas-nro-cupo/{id_pngcurso}', 'SchoolsController@patchPeriodsStagesGradesAreasNroCupo'); // falta - 2020-01-16
    Route::get('periods-stages-grades-sections', 'SetupController@listPeriodsSGSections'); // new 2019-12-26
    Route::put('periods-stages-grades-sections/{id_pngseccion}', 'SetupController@updatePeriodsStagesGradesSections'); // * new 2019-12-26
    Route::post('periods-stages-grades-sections', 'SetupController@addPeriodsStagesGradesSections'); // * new 2019-12-26
    Route::delete('periods-stages-grades-sections/{id_pngseccion}', 'SetupController@deletePeriodsStagesGradesSections'); // * new 2019-12-26
    Route::patch('periods-stages-grades-sections-save/{id_pngseccion}', 'SchoolsController@patchPeriodsStagesGradesSectionsSave');
    Route::get('periods-students-areas-both', 'SchoolsController@listPeriodsStudentsAreasBoth');
    Route::post('periods-students-areas', 'SchoolsController@addPeriodsStudentsAreas');
    Route::get('pmes-areas-teachers', 'SchoolsController@listPmesAreasTeachers');
    Route::get('units', 'SchoolsController@listUnits');
    Route::get('sessions', 'SchoolsController@listSessions');
    Route::post('sessions', 'SchoolsController@addSessions');
    Route::get('sessions-items', 'SchoolsController@listSessionsItems');
    Route::post('sessions-items', 'SchoolsController@addSessionsItems');
    Route::put('sessions-items/{id_sitem}', 'SchoolsController@updateSessionsItems');
    Route::delete('sessions-items/{id_sitem}', 'SchoolsController@deleteSessionsItems');
    Route::get('sessions-instruments', 'SchoolsController@listSessionsInstruments');
    Route::get('sessions-instruments-missing', 'SchoolsController@listSessionsInstrumentsMissing');
    Route::post('sessions-instruments', 'SchoolsController@addSessionsInstruments');
    Route::delete('sessions-instruments/{id_sesion}/{id_instrumento}', 'SchoolsController@deleteSessionsInstruments');
    Route::get('sessions-criteria', 'SchoolsController@listSessionsCriteria');
    Route::get('sessions-criteria-missing', 'SchoolsController@listSessionsCriteriaMissing');
    Route::post('sessions-criteria', 'SchoolsController@addSessionsCriteria');
    Route::delete('sessions-criteria/{id_sesion}/{id_ucriterio}', 'SchoolsController@deleteSessionsCriteria');
    Route::get('units-competencys-evaluations', 'SchoolsController@listUnitsCompetencysEvaluations');
    Route::get('units-competencys-by-evals-periods', 'SchoolsController@listUnitsCompetencysByEvalsPeriods');
    Route::get('units-learnings-precises-evaluations', 'SchoolsController@listUnitsLearningsPrecisesEvaluations');
    Route::post('units-learnings-precises-evaluations', 'SchoolsController@addUnitsLearningsPrecisesEvaluations');
    Route::put('units-learnings-precises-evaluations/{id_eunidad}', 'SchoolsController@updateUnitsLearningsPrecisesEvaluations');

    Route::post('file', 'SchoolsController@uploadFile');
    /////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////RERIFICACION DE SERVICIOS//////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////

    //Route::get('config-stage', 'SchoolsController@listConfigStage');//RETIRAR
    //Route::get('stage-grade', 'SchoolsController@listStageGrade');//RETIRAR
    //Route::get('types-pay', 'SchoolsController@listTypesPay');//RETIRAR
    //Route::get('types-pay-in-period', 'SchoolsController@listTypesPayInPeriod');//RETIRAR
    //Route::get('students/responsibles/proformas', 'SchoolsController@listSRProformas');//RETIRAR
    //Route::get('proformas-by-manager', 'SchoolsController@listProformasByManager');
    //Route::get('proformas-report/{id_proforma}', 'SchoolsController@showProformasReport');
    //Route::delete('proformas/{id_proforma}', 'SchoolsController@deleteProformas');
    //Route::get('proformas-details', 'SchoolsController@listProformasDetails');
    //Route::post('proformas-details', 'SchoolsController@addProformasDetails');
    //Route::delete('proformas-details/{id_pdetalle}', 'SchoolsController@deleteProformasDetails');
    //Route::get('nivel-config', 'SchoolsController@nivelConfig');
    //Route::get('criterions-typesdiscount', 'SchoolsController@listCriterionsTypesdiscount');
    //Route::get('criterions-typespay', 'SchoolsController@listCriterionsTypespay');
    Route::post('proformas', 'SchoolsController@addProformas');
    Route::get('list-religion', 'SchoolsController@listReligion');
    Route::get('list-type-lenguage', 'SchoolsController@listTipoIdioma');
    Route::get('list-level-instruction', 'SchoolsController@listLevelInstruction');
    Route::get('list-status-civil', 'SchoolsController@listStatusCivil');
    Route::get('list-operator-movil', 'SchoolsController@listOperatorMovil');
    Route::get('list-localization', 'SchoolsController@listLocalization');
    Route::get('list-pais', 'SchoolsController@listPais');
    Route::get('list-dep', 'SchoolsController@listDep');
    Route::get('list-prov', 'SchoolsController@listProv');
    Route::get('list-dist', 'SchoolsController@listDist');
    Route::get('types-phone', 'SchoolsController@listTypesPhone');
    Route::get('persons-manager', 'SchoolsController@listPersonsManager');
    Route::get('persons-manager-search', 'SchoolsController@listPersonsManagerSearch');
    Route::get('persons-manager/{id_persona}/{tipo_parentesco}', 'SchoolsController@showPersonsManager');
    Route::get('persons/{id_persona}', 'SchoolsController@personaById');
    Route::get('persons-manager/{id_persona}', 'SchoolsController@personaAdmision');
    Route::post('persons-manager', 'SchoolsController@addPersonsManager');
    Route::put('persons-manager/{id_persona}', 'SchoolsController@editPersonsManager');
    Route::get('list-tipo-parentesco', 'SchoolsController@listTipoParentesco');
    //Route::get('vacantes-disponibles', 'SchoolsController@vacantesDisponibles');
    //Route::get('numero-familia-dni', 'SchoolsController@numeroFamiliaByDni');
    //Route::get('datos-by-cod-familia', 'SchoolsController@datosByCodFamilia');
    Route::get('dato-by-cod-familia', 'SchoolsController@datosByCodFamiliaAndHijo');
    Route::get('dato-by-cod-fam-id-person', 'SchoolsController@datosByCodFamAndIdPerson');
    //Route::get('dato-by-cod-fam-id-hijo', 'SchoolsController@datosByCodFamAndIdHijo');
    Route::get('idiomas-by-id-persona', 'SchoolsController@idiomasByIdPersona');
    Route::get('correos-by-id-persona', 'SchoolsController@correosByIdPersona');
    Route::get('telefonos-by-id-persona', 'SchoolsController@telefonosByIdPersona');
    Route::get('dato-resp-finan-id-persona', 'SchoolsController@datoRespFinanByIdPerson');
    Route::get('persons-admision-search', 'SchoolsController@listPersonsAdmisionSearch');
    Route::post('admision', 'SchoolsController@addPersonAdmision');
    Route::get('admision/{id_alumno}', 'SchoolsController@personAdmisioShow');
    Route::get('admision-realizo-pago', 'SchoolsController@admisionRealizoPago');
    Route::get('types-address', 'SchoolsController@listTypesAddress');
    Route::put('institution-estado/{id_institucion}', 'SchoolsController@editSchoolInstitucionEstado');
    Route::get('types-virtual', 'SchoolsController@listTypesVirtual');
    Route::get('list-personas-campo', 'SchoolsController@listPersonasCampo');
    Route::post('categoria-trabajador', 'SchoolsController@addCategoriaTrabajador');
    Route::post('trabajador', 'SchoolsController@addTrabajador');
    Route::get('trabajador', 'SchoolsController@listTrabajador');
    Route::get('trabajador/{id_trabajador}/{id_categoria}', 'SchoolsController@deleteTrabajador');
    Route::post('trabajadores', 'SchoolsController@deleteTrabajadores');
    Route::get('list-union', 'SchoolsController@listUnion');
    Route::get('list-campo', 'SchoolsController@listCampo');
    Route::get('list-departamento', 'SchoolsController@listDeparment');
    //CITAS
    Route::get('meets', 'SchoolsController@listMeets');
    Route::post('meets', 'SchoolsController@addMeets');
    Route::delete('meets/{id_cita}', 'SchoolsController@deleteMeets');
    //FIN CITAS
    Route::get('schedules-meet', 'SchoolsController@listSchedulesMeet');
    Route::post('schedules-meet', 'SchoolsController@addSchedulesMeet');
    Route::delete('schedules-meet/{id_hcita}', 'SchoolsController@deleteSchedulesMeet');
    Route::get('persons-family', 'SchoolsController@listPersonsFamily');
    Route::get('list-institucion', 'SchoolsController@listInstitucion');
    Route::get('list-institucion_parametros', 'SchoolsController@listInstitucionParametros');
    Route::get('list-periodo-escolar', 'SchoolsController@listPeriodoEscolar');
    Route::post('institution', 'SchoolsController@addSchoolInstitucion');
    Route::put('institution/{id_institucion}', 'SchoolsController@editSchoolInstitucion');
    Route::delete('institution/{id_institucion}', 'SchoolsController@deleteSchoolInstitucion');
    Route::get('list-bimestre-open-close', 'SchoolsController@listBimestreOpenClose');
    Route::get('list-periodo-nivel', 'SchoolsController@listPeriodoNivel');
    Route::get('list-periodo-ngrado', 'SchoolsController@listPeriodoNGrado');
    Route::get('periodo-ngrado/{id}', 'SchoolsController@periodoNGrado');
    Route::get('list-periodo-ngseccion', 'SchoolsController@listPeriodoNGSeccion');
    Route::post('bimestre-open-close', 'SchoolsController@addBimestreOpenClose');
    Route::get('search-employee', 'SchoolsController@searchEmployee');
    Route::get('list-periodo-open-close', 'SchoolsController@listPeriodoOpenClose');
    Route::post('seccion-personal', 'SchoolsController@addSeccionPersonal');
    Route::get('seccion-personal', 'SchoolsController@listSPersonal');
    Route::put('seccion-personal/{id_spersonal}', 'SchoolsController@editSeccionPersonal');
    Route::get('seccion-personal/{id_spersonal}', 'SchoolsController@PersonalSeccionById');
    Route::get('list-status-civil', 'SchoolsController@listStatusCivil');
    Route::get('list-family-son', 'SchoolsController@listFamilySon');
    Route::get('list-tipo-parentesco', 'SchoolsController@listTipoParentesco');
    Route::get('numero-familia-dni', 'SchoolsController@numeroFamiliaByDni');
    Route::get('datos-by-cod-familia', 'SchoolsController@datosByCodFamilia');
    Route::get('dato-by-cod-fam-id-hijo', 'SchoolsController@datosByCodFamAndIdHijo');
    Route::get('categoria-trabajador', 'SchoolsController@listCategoriaTrabajador');
    Route::get('list-cursos-by-pngseccion', 'SchoolsController@listCursosByPngseccion');
    Route::get('list-cursos-by-pngrado-electivo', 'SchoolsController@listCursosByPNGradoElectivo');
    Route::get('list-cursos-by-docente', 'SchoolsController@listCursosBySDocente');
    Route::post('curso-docente', 'SchoolsController@addCursoDocente');
    Route::post('curso-docente-sub', 'SchoolsController@addSubCursoDocente');
    Route::post('curso-docente-sub-electivo', 'SchoolsController@addSubCursoDocenteElectivo');
    Route::put('curso-docente-sub/{id_dcurso}', 'SchoolsController@editSubCursoDocente');
    Route::put('curso-docente-sub-electivo/{id_dcurso}', 'SchoolsController@editSubCursoDocenteElectivo');
    Route::get('existsubcurso-by-idper-idpngcurso', 'SchoolsController@existSubCursoByIdPersIdPNGCurso');
    Route::post('cursos-docente-delete', 'SchoolsController@deleteCursosByIdSDocente');
    Route::get('list-cursos-by-id-cparent', 'SchoolsController@listCursosByIdCParent');
    Route::get('grado-seccion-by-id/{id}', 'SchoolsController@gradoSeccionById');
    Route::get('exist-personal-seccion-first', 'SchoolsController@existPersonalSeccionFirst');
    Route::get('exist-personal-grado-first', 'SchoolsController@existPersonalGradoFirst');
    Route::get('list-bimestre-byestado-automatico', 'SchoolsController@listBimestreByEstadoAutomatico');
    Route::get('personal-seccion-tipo', 'SchoolsController@PersonalSeccionTipoById');
    Route::get('ngseccion-by-id/{id_pngseccion}', 'SchoolsController@ngSeccionById');
    Route::get('list-alumnos-by-seccion', 'SchoolsController@listAlumnosBySeccion');
    Route::get('list-periodo-ngnotseccion', 'SchoolsController@listPeriodoNGNotSeccion');
    Route::post('cambio-alumno-seccion', 'SchoolsController@cambioAlumnoSeccion');
    Route::post('incidencia-j', 'SchoolsController@incidenciaJustificacion');
    Route::post('incidencia', 'SchoolsController@addIncidencia');
    Route::get('incidencia', 'SchoolsController@listIncidenciaAlumno');
    Route::delete('incidencia/{id_incidencia}', 'SchoolsController@deleteIncidenciaEvidencia');
    Route::get('matricula-by-id-persona-periodo', 'SchoolsController@matriculaByIdPersonaPeriodo');
    Route::get('search-alumno-periodo', 'SchoolsController@searchAlumnoByPeriodo');
    Route::get('evidencia_img/{filename}', 'SchoolsController@evidencia_img');

    Route::get('evidencia-by-id-incidencia', 'SchoolsController@evidenciaByIdIncidencia');
    Route::post('retirar-alumno-falta-alta', 'SchoolsController@retirarAlumnoFaltasAltas');
    Route::post('exoneracion-alumnos-curso', 'SchoolsController@exoneracionAddEstudiantes');
    Route::get('exoneracion-list-cursos-g', 'SchoolsController@exoneracionListCursosG');
    Route::get('exoneracion-list-estudiantes-curso-pg', 'SchoolsController@exoneracionListEstudiantesCursoPG');
    Route::post('exoneracion-del-estudiantes-curso-pga', 'SchoolsController@exoneracionDelEstudiantesCursoPGA');
    Route::get('exoneracion-estudiantes-seccion-curso', 'SchoolsController@exoneracionEstudiantesBySeccionCurso');
    Route::post('exoneracion-descripcion-evidencia', 'SchoolsController@exoneracionDesEvi');
    Route::get('exoneracion-descripcion-by-id/{id_ecurso}', 'SchoolsController@exoneracionDescripcionById');
    Route::get('exoneracion-evidencia-by-id/{id_ecurso}', 'SchoolsController@exoneracionIvidenciaById');
    Route::get('cambio-docente-curso', 'SchoolsController@cambioDocenteCursolist');
    Route::post('cambio-docente-curso', 'SchoolsController@cambioDocenteCursoUpdate');
    Route::get('sexo-tipo', 'SchoolsController@sexoTipo');
    Route::get('datos-institution-header', 'SchoolsController@datosInstitution');
    Route::get('list-persons-search', 'SchoolsController@listPersonsSearch');
    Route::get('pmes-curso-prof-list', 'SchoolsController@listBimestresPMesCursoProf');
    Route::get('periodo-mes-hijos', 'SchoolsController@periodoMesHijos');
    Route::post('units', 'SchoolsController@unitsAdd');
    Route::get('units-list-pngrado', 'SchoolsController@unitsByIdPNGrado');
    Route::get('units-list', 'SchoolsController@unitsLista');
    Route::get('units-by-pmeshijo', 'SchoolsController@unitsByIdPmesHijo');
    Route::get('units-by-pmeshijo-group-bimestre', 'SchoolsController@unitsByIdPNGradoGroupHijo');
    Route::post('thematic', 'SchoolsController@thematicAdd');
    Route::put('thematic/{id_tematica}', 'SchoolsController@thematicEdit');
    Route::get('thematic/{id_tematica}', 'SchoolsController@thematicById');
    Route::delete('thematic/{id_tematica}/{id_unidad}', 'SchoolsController@thematicDelete');
    Route::get('thematic-grado', 'SchoolsController@thematicGrado');
    Route::get('pmde', 'SchoolsController@pmdeList');
    Route::get('pmde-periodo', 'SchoolsController@pmdeListPeriodo');
    Route::post('pmde', 'SchoolsController@pmdeAdd');
    Route::get('agenda', 'SchoolsController@agendaList');
    Route::post('agenda', 'SchoolsController@agendaAdd');
    Route::put('agenda/{id_agenda}', 'SchoolsController@agendaEdit');
    Route::delete('agenda/{id_agenda}', 'SchoolsController@deleteAgenda');
    Route::get('search-alumno-reserva/{id_persona}', 'SchoolsController@searchAlumnoReserva');
    Route::post('confirmation-document', 'SchoolsController@confirmationDocument');
    Route::get('feligresia-list-periodo-ope', 'SchoolsController@feligresiaListByPeriodOperativo');
    Route::get('dowload-file/{carpeta}/{nombre_file}', 'SchoolsController@dowloadFile');
    Route::post('feligresia-confirmar', 'SchoolsController@feligresiaConfirmar');
    Route::get('filegresia-family-user', 'SchoolsController@feligresiaSearchFamiliaHijosByIdUser');
    Route::post('upload-file-feligresia', 'SchoolsController@uploadFileReservaFeligresia');
});
Route::group(['prefix' => 'schools/setup', 'namespace' => 'Schools'], function () {
    Route::get('persons-parentesco', 'SetupController@listPersonsParentesco');
    Route::get('persons-emergency', 'SetupController@listPersonsEmergency');
    Route::get('persons-emergency-none', 'SetupController@listPersonsEmergencyNone'); // listPersonsEmergency // listPersonsSearch
    Route::post('persons-emergency', 'SetupController@addPersonsEmergency');
    Route::delete('persons-emergency/{id_pemergencia}', 'SetupController@deletePersonsEmergency');
    Route::get('persons-mobility', 'SetupController@listPersonsMobility');
    Route::post('persons-mobility', 'SetupController@addPersonsMobility');
    Route::delete('persons-mobility/{id_pmovilidad}', 'SetupController@deletePersonsMobility');
    Route::get('persons-drivers-none', 'SetupController@listPersonsdriversNone');
    Route::post('persons-drivers', 'SetupController@addPersonsDrivers');
    // persons
    Route::get('persons-search', 'SetupController@listPersonsSearch');
    Route::get('persons-all/{id_persona}', 'SetupController@showPersonsAll');
    Route::post('persons', 'SetupController@addPersons');
    Route::put('persons/{id_persona}', 'SetupController@updatePersons');
    Route::post('persons-address', 'SetupController@addPersonsAddress');
    Route::put('persons-address/{id_direccion}', 'SetupController@updatePersonsAddress');
    Route::post('persons-document', 'SetupController@addPersonsDocument');
    Route::put('persons-document/{num_docum}', 'SetupController@updatePersonsDocument');
    Route::post('persons-natural', 'SetupController@addPersonsNatural');
    Route::put('persons-natural/{id_persona}', 'SetupController@updatePersonsNatural');
    Route::post('persons-natural-religion', 'SetupController@addPersonsNaturalReligion');
    Route::put('persons-natural-religion/{id_persona}', 'SetupController@updatePersonsNaturalReligion');
    Route::post('persons-phone', 'SetupController@addPersonsPhone');
    Route::put('persons-phone/{id_telefono}', 'SetupController@updatePersonsPhone');
    Route::post('persons-virtual', 'SetupController@addPersonsVirtual');
    Route::put('persons-virtual/{id_virtual}', 'SetupController@updatePersonsVirtual');
    Route::post('persons-natural-school', 'SetupController@addPersonsNaturalSchool');
    Route::put('persons-natural-school/{id_persona}', 'SetupController@updatePersonsNaturalSchool');
    // Persons Address
    Route::get('periods', 'SetupController@listPeriods');
    Route::get('periods/{id_periodo}', 'SetupController@showPeriods');
    Route::post('periods', 'SetupController@addPeriods');
    Route::put('periods/{id_periodo}', 'SetupController@updatePeriods');
    Route::put('periods-matricula/{id_periodo}', 'SetupController@updatePeriodsMatricula');
    Route::delete('periods/{id_periodo}', 'SetupController@deletePeriods');
    Route::patch('periods-confirm/{id_periodo}', 'SetupController@periodsConfirm');
    Route::get('periods-calendar', 'SetupController@listPeriodsCalendar');
    Route::put('periods-calendar/{id_pcalendario}', 'SetupController@updatePeriodsCalendar');
    Route::get('periods-area', 'SetupController@listPeriodsArea');
    Route::post('periods-area', 'SetupController@addPeriodsArea');
    Route::delete('periods-area/{id_pcurso}', 'SetupController@deletePeriodsArea');
    Route::patch('periods-area-import-year-before', 'SetupController@excePAImportYearBefore');
    // Route::get('plans', 'SetupController@listPeriodsArea');
    // Route::post('plans', 'SetupController@addPeriodsArea');
    // SCHOOL_PLAN_NIVEL_GRADO_CURSO // SCHOOL_PLAN_NIVEL_CONFIG_NOTA
    // Route::get('plans-stage-config-eval', 'SetupController@listPlansStageGradeArea'); // ?????
    Route::put('plans-stage-grade/{id_pngrado}', 'SetupController@updatePlansStageGrade');
    // Route::get('plans-stage-grade-area', 'SetupController@listPlansStageGradeArea'); // ?????
    // Route::get('plans-stage-grade-area-none', 'SetupController@listPlansStageGradeAreaNone'); // ?????
    Route::post('plans-stage-grade-area', 'SetupController@addPlansStageGradeArea');
    Route::put('plans-stage-grade-area/{id_pngcurso}', 'SetupController@updatePlansStageGradeArea');
    Route::delete('plans-stage-grade-area/{id_pngcurso}', 'SetupController@deletePlansStageGradeArea');
    Route::get('plans-stage-config-eval', 'SetupController@listPlansStageConfigEval');
    Route::post('plans-stage-config-eval', 'SetupController@addPlansStageConfigEval');
    Route::put('plans-stage-config-eval/{id_pncnota}', 'SetupController@updatePlansStageConfigEval');
    Route::get('plans-stages-all', 'SetupController@listPlansStagesAll');
    Route::get('plans-stages-areas-none', 'SetupController@listPlansSANone'); // loading // listPeriodsNGNone OK+
    Route::post('plans-stages-areas', 'SetupController@addPlansSAreas');
    Route::delete('plans-stages-areas/{id_pncurso}', 'SetupController@deletePlansSAreas');
    // x---
    Route::get('periods-stages-all', 'SetupController@listPeriodsStagesAll');
    Route::get('periods-stages', 'SetupController@listPeriodsStages'); // NEW OCT
    Route::get('periods-stages-grades', 'SetupController@listPeriodsSGrades'); // NEW OCT
    // Route::get('periods-stages-grades-sections', 'SetupController@listPeriodsSGSections'); // NEW OCT
    Route::get('periods-stages-grades-none', 'SetupController@listPeriodsNGNone');
    Route::post('periods-stages-grades', 'SetupController@addPeriodsSGrades');
    Route::delete('periods-stages-grades/{id_pngrado}', 'SetupController@deletePeriodsSGrades');
    // Route::get('periods-stages-grades-areas', 'SetupController@listPeriodsNGAreas'); top
    Route::post('periods-stages-grades-areas-sync', 'SetupController@addPeriodsNGAreaSync');
    // abajo temporal....
    // Route::put('periods-stages-grades-sections/{id_pngseccion}', 'SetupController@updatePeriodsStagesGradesSections');
    // Route::post('periods-stages-grades-sections', 'SetupController@addPeriodsStagesGradesSections');
    // Route::delete('periods-stages-grades-sections/{id_pngseccion}', 'SetupController@deletePeriodsStagesGradesSections');
});

Route::group(['prefix' => 'finances-student', 'namespace' => 'FinancesStudent'], function () {
    Route::get('students', 'StudentsController@listStudents');
    Route::get('students/{codigo}', 'StudentsController@showStudents');
    //apis

    Route::get('payDc', 'StudentsController@pagosDebiCredi');
    Route::get('contrato-alumno', 'StudentsController@contratoAlumn');
    Route::get('plan-pago-semestre', 'StudentsController@planPagoSemestre');
    Route::get('plan-pago', 'StudentsController@planPago');
    Route::post('prorroga', 'StudentsController@addProrroga');
    Route::get('prorroga', 'StudentsController@listProrroga');
    Route::get('prorroga-validate-code', 'StudentsController@validarCodigoProrroga');
    Route::post('masivo-prorroga', 'StudentsController@addProrrogaMasivo');
    Route::get('search-student', 'StudentsController@searchStudentGlobal');

    Route::post('descuento-vicerrectorado', 'StudentsController@addDescVicerectorado');
    Route::post('descuento-vicerrectorado/multiple', 'StudentsController@addDescVicerectoradoMultiple');
    Route::post('descuento-vicerrectorado/excel', 'StudentsController@checkStudentDiscountExcel');
    Route::get('descuento-vicerrectorado', 'StudentsController@listDescVicerectorado');
    Route::get('descuento-vicerrectorado-xls', 'StudentsController@listDescVicerectoradoxls');
    Route::delete('descuento-vicerrectorado/{id_alumno_descuento_vice}', 'StudentsController@deleteDescVicerectorado');
    Route::get('descuento-vicerrectorado/{id_alumno_descuento_vice}', 'StudentsController@showDescVicerectorado');
    Route::put('descuento-vicerrectorado/{id_alumno_descuento_vice}', 'StudentsController@updateDescVicerectorado');

    Route::get('situacion-matricula', 'StudentsController@situacionMatricula');
    Route::get('situacion-matricula/details', 'StudentsController@situacionMatriculaDetalle');
    Route::get('situacion-matricula/details-excel', 'StudentsController@situacionMatriculaExcel');
    Route::get('situacion-matricula/facultad', 'StudentsController@facultadSituacionMatriculaDetalle');
    Route::get('facultades', 'StudentsController@facultades');
    Route::get('facultades/details', 'StudentsController@facultadesDetalle');

    Route::get('plan-alumno/{id_persona}', 'StudentsController@listPlanAlumno');

    Route::get('escuela', 'StudentsController@escuelaEstadistica');
    Route::get('escuela/details', 'StudentsController@escuelaEstadisticaDetalle');

    Route::get('vivienda', 'StudentsController@situacionMatricula');
    Route::get('report-semeste', 'StudentsController@semestre');


    Route::get('report-tranfer-details', 'StudentsController@listTransferDetails');
    Route::get('report-tranfer-details-pdf', 'StudentsController@listTransferDetailsPdf');
    Route::get('report-tranfer-resumen', 'StudentsController@listTransferResumen');
    Route::get('report-tranfer-resumen-pdf', 'StudentsController@listTransferResumenPdf');
    Route::get('report-student-balance', 'ReportController@getStudentBalance');
    Route::get('report-summary-balance', 'ReportController@getSummaryBalance');


    Route::get('seguimiento-student', 'StudentsController@seguimientoAlumno');
    Route::get('seguimiento-student-excel', 'StudentsController@seguimientoAlumnoExcel');
    Route::put('seguimiento-student/{id_persona}/llamada', 'StudentsController@llamadaAlumno');
    Route::put('seguimiento-student/{id_persona}/mensaje', 'StudentsController@mensajeAlumno');

    Route::get('filter-facultad', 'StudentsController@getFacultad');
    Route::get('filter-escuela', 'StudentsController@getEscuela');

    Route::post('llamada-alumno-financial', 'StudentsController@llamadaAlumnoFinancial');
    Route::post('mensaje-alumno-financial', 'StudentsController@mensajeAlumnoFinancial');
    Route::get('bloqueo-alumno', 'StudentsController@bloqueoAlumno');
    Route::get('saldo-alumno', 'StudentsController@saldoAlumno');

    Route::get('tipo-contrato', 'StudentsController@tipoContrato');

    Route::get('anticipos-alumno', 'StudentsController@anticiposAlumno');

    Route::get('situacion-credito-matricula', 'StudentsController@situacionCreditoMatricula');
    Route::get('facultad-credito', 'StudentsController@facultadCreditos');
    Route::get('escuela-credito', 'StudentsController@escuelaCreditos');

    Route::get('student-croussing', 'StudentsController@studentCroussing');

    Route::get('resumen-detalle-llamada', 'StudentsController@detalleDeLlamada');
    Route::get('tipo_evidencia', 'StudentsController@listTipoEvidencia');
    // sales

    Route::post('sales', 'SalesController@createSale');
    Route::post('direccion', 'SalesController@saveUpdateDireccion'); ///cambiar direccion
    Route::get('lista-escuelas', 'StudentsController@listaDeEscuelas');


    Route::get('lista-refinanciemaiento-matricula', 'StudentsController@refinaciamientoEscuelaDetalle');
    Route::get('lista-refinanciemaiento-matricula/no-matricula', 'StudentsController@noMatriculadosRefinaciamientoEscuelaDetalle');
    Route::get('alumno-refinanciamiento', 'StudentsController@alumnoRefinanciamiento');
    Route::get('lista-refinanciemaiento-matricula/documentos', 'StudentsController@listaDocumentos');
    Route::post('convenio', 'StudentsController@inserConvenio');
    Route::get('convenio', 'StudentsController@listConvenio');
    Route::put('convenio/{id_cdetalle}', 'StudentsController@updateConvenioCumplio');
    Route::get('convenio/principal', 'StudentsController@listPrincipalConvenio');
    Route::get('convenio/stateCta', 'StudentsController@listPrincipalConvenioStateCta');
    Route::put('convenio/{id_convenio}/anular', 'StudentsController@updateConvenioAnular');
    Route::put('convenio/{id_cdetalle}/detalle', 'StudentsController@updateDetalleConvenio');
    Route::delete('convenio/{id_convenio}/delete-detalle', 'StudentsController@deleteDetalleConvenio');
    Route::delete('convenio/{id_convenio}', 'StudentsController@deleteConvenio');
    Route::post('convenio/new-detalle', 'StudentsController@nuevoDetalleConvenio');
    Route::get('convenio/refin-pdf', 'StudentsController@refinanciamientoPDf');
    Route::post('convenio/email', 'StudentsController@emailRefinanciacionConvenio');
    Route::get('convenio/convenio-pdf', 'StudentsController@convenioPDf');
    Route::get('sede-finanzas', 'StudentsController@getSede');
    Route::get('residencia-sede', 'StudentsController@getResidencia');
    Route::get('alumnos-internos', 'StudentsController@getListaAlumnoInterno');
    Route::get('indice-morosidad', 'StudentsController@generarIndiceMorosidad');
    Route::get('indice-morosidad/details', 'StudentsController@indiceMorosidadDetalle');
    Route::post('notas-finacieras', 'StudentsController@addNotasFinancieras');
    Route::get('notas-finacieras', 'StudentsController@listNotasFinancieras');
    Route::put('notas-finacieras/{id_compromiso}', 'StudentsController@updateNotasFinancieras');
    Route::get('financistas', 'StudentsController@listFinancista');
    Route::post('financistas', 'StudentsController@addFinancista');
    Route::post('financistas/masivo', 'StudentsController@addFinancistaMasivo');
    Route::get('indice-recuperacion', 'StudentsController@generarRecuperacion');
    Route::get('indice-recuperacion/detalle', 'StudentsController@indiceRecuperacionDetalle');
    Route::post('importar-exel/view-verificar', 'StudentsController@verificarExelAlumnos');

    Route::post('importar-exel', 'StudentsController@addSalesImports');

    Route::get('seguimiento-financistas/llamada-fin', 'StudentsController@metaLlamadas');
    Route::get('seguimiento-financistas/llamada-fin-detalle', 'StudentsController@metaLlamadasDetalle');
    Route::get('seguimiento-financistas/llamada-respondio-detalle', 'StudentsController@respondioLlamadasDetalle');
    Route::get('seguimiento-financistas/llamada-acumulado', 'StudentsController@metaLlamadasAcumulado');
    Route::get('seguimiento-financistas/llamada-fin-acumulado', 'StudentsController@metaLlamadasDetalleAcumulado');
    Route::get('seguimiento-financistas/llamada-respondio-acumulado', 'StudentsController@respondioLlamadasDetalleAcumulado');
    Route::get('seguimiento-financistas/deuda-fin', 'StudentsController@metaDeuda');
    Route::get('seguimiento-financistas/deuda-acumulada', 'StudentsController@metaDeudaAcumulado');
    Route::get('seguimiento-financistas/promesa-pago', 'StudentsController@metaPromesadePago');
    Route::get('seguimiento-financistas/promesa-pago-detalle', 'StudentsController@metaPromesadePagoDetalle');
    Route::get('seguimiento-financistas/promesa-pago-detalle-cumplio', 'StudentsController@metaPromesadePagoDetalleCumplio');

    Route::get('seguimiento-financistas/promesa-acumulado', 'StudentsController@metaPromesadePagoAcumulado');
    Route::get('seguimiento-financistas/promesa-acumulado-detalle', 'StudentsController@metaPromesadePagoDetalleAcumulado');
    Route::get('seguimiento-financistas/promesa-acumulado-detalle-cumplio', 'StudentsController@metaPromesadePagoDetalleAcumuladoCumplido');

    Route::get('seguimiento-financistas/financiamiento', 'StudentsController@metaFinanciamiento');
    Route::get('seguimiento-financistas/financiamiento-detalle', 'StudentsController@metaFinanciamientoDetalle');
    Route::get('seguimiento-financistas/financiamiento-acumulado', 'StudentsController@metaFinanciamientoAcumulado');
    Route::get('seguimiento-financistas/financiamiento-acumulado-detalle', 'StudentsController@metaFinanciamientoDetalleAcumulado');

    Route::get('seguimiento-financistas/student-financier/{id_alumno}', 'StudentsController@getStudentFinancier');

    Route::get('doc-pdf', 'StudentsController@obtenerPDFDoc');
    Route::get('descuento-becas', 'StudentsController@reporteDescuentoBecas');

    Route::post('config-finances', 'StudentsController@AgregarFinancista');
    Route::get('financistas/anexo', 'StudentsController@listFinancistaAnexo');
    Route::post('anexo', 'StudentsController@saveAnexo');
    Route::delete('anexo/{id_anexo}', 'StudentsController@deleteAnexo');
    Route::put('config-finances/{id_financista}', 'StudentsController@updateFinancista');
    Route::delete('financistas/anexo/{id_financista}', 'StudentsController@deleteFinancista');
    // Anular Contrato Y Venta / Transf
    Route::get('cancel-contract', 'StudentsController@listSalesContract');
    Route::post('cancel-contract', 'StudentsController@CancelContract');
    //metas financial
    Route::post('gestion-metas', 'StudentsController@saveMetas');
    Route::get('gestion-metas', 'StudentsController@getMetasSedes');
    Route::delete('gestion-metas/{id_meta}', 'StudentsController@deleteMetas');
    Route::put('gestion-metas/{id_semestre}', 'StudentsController@updateMetas');

    Route::get('filter-semestre-segui', 'StudentsController@getSemestreSegui');
    Route::get('filter-sede-segui', 'StudentsController@getSedeSegui');
    Route::get('filter-ciclo-segui', 'StudentsController@getCicloSegui');
    Route::get('docente-seguimiento', 'StudentsController@getDocenteSegui');
    Route::post('asignacion-docente', 'StudentsController@saveDocentes');

    Route::get('situacion-sedes-matricula', 'StudentsController@situacionSedesMatricula');

    Route::get('depto-program-studio/{id_programa_estudio}', 'StudentsController@deptoProgramStudie');

    Route::get('saldo-sedes', 'StudentsController@saldoSedes');

    Route::resource('collection-fees', 'CollectionFeesController');
    Route::resource('nc-special-discount', 'NCSpecialDiscountController');


    Route::post('finish-tramite-registro', 'StudentsController@finishTramiteRegistro');
    Route::post('asig-finances-student', 'StudentsController@addFinancistaGlobal');

    Route::post('send-mail', 'StudentsController@sendMail');
    Route::post('send-group-mail', 'StudentsController@sendGroupMail');
    Route::get('dinamic-pay', 'StudentsController@getDinamic');
    Route::get('valid-pay-free', 'StudentsController@validaPago');
});
Route::group(['prefix' => 'finances-student/paramter', 'namespace' => 'FinancesStudent'], function () {
    //anho parametro
    Route::get('params/search-persons', 'ParameterController@searchPersons');
    Route::get('params/anexo', 'ParameterController@anexo');
    Route::get('params/filter-estrategia', 'ParameterController@filterEstretegy');
    Route::get('params/perfil-estado-cuenta', 'ParameterController@perfilEstadoCuenta');
    Route::get('params/filter-falcultad', 'ParameterController@filterFacultadMeta');
    Route::get('params/filter-escuelas', 'ParameterController@filterEscuelaMeta');
    Route::get('params/search-student-global', 'ParameterController@searchStudent');
    Route::get('params/filter-semester-alumno', 'ParameterController@filterSemesterAlumno');
    Route::get('params/filter-program-alumno', 'ParameterController@filterProgramaAlumno');
    Route::get('params/filter-contrato-alumno', 'ParameterController@filterContractAlumno');
    Route::get('params/responsable-alumno', 'ParameterController@responsableEstudent');
    Route::get('params/sede-grafic-filial', 'ParameterController@sedeGraficFilial');
    Route::get('params/tipo-contrato-grafic-filial', 'ParameterController@tipoContratoFiliales');
    Route::get('params/tipo-modalidad-estudio-grafic-filial', 'ParameterController@tipoModalidadEstudioFiliales');
    Route::get('params/nivel-ensenanza-sedes', 'ParameterController@nivelEnsenanza');

    Route::post('transfers/file', 'ParameterController@postFile');
    Route::get('file-view', 'ParameterController@getFile');

    Route::get('valid-code-student', 'ParameterController@codigoAlumnoValid');
});
Route::group(['prefix' => 'finances-student/report', 'namespace' => 'FinancesStudent\Reports'], function () {
    Route::get('customer-charges', 'CustomerChargesController@getCustomerCharges');
    Route::get('enrrolled-payment-plan', 'EnrrolledPaymentController@getEnrrolledPaymentPlan');

    Route::get('collection-control', 'CollectionControlController@getCollectionControl');
    //    Route::resource('collection-fees', 'CollectionFeesController');

    //    Route::resource('collection-fees', 'CollectionFeesController');

    //Route::get('collection-fees', 'CollectionFeesController@getCollectionFees');
});


Route::group(['prefix' => 'setup', 'namespace' => 'FinancesStudent'], function () {

    Route::get('typemodality', 'SetupenrollmentController@listTypeModality');

    //plan de pagos
    Route::get('paymentplan', 'SetupenrollmentController@listPaymentPlan');
    Route::get('paymentplan/{id_planpago}', 'SetupenrollmentController@showPaymentPlan');
    Route::post('paymentplan', 'SetupenrollmentController@addPaymentPlan');
    Route::put('paymentplan/{id_planpago}', 'SetupenrollmentController@updatePaymentPlan');
    Route::delete('paymentplan/{id_planpago}', 'SetupenrollmentController@deletePaymentPlan');


    //configuracion parametro
    Route::get('configparameter', 'SetupenrollmentController@listConfigParameter');
    Route::get('configparameter/{id_config_parametro}', 'SetupenrollmentController@showConfigParameter');
    Route::post('configparameter', 'SetupenrollmentController@addConfigParameter');
    Route::put('configparameter/{id_config_parametro}', 'SetupenrollmentController@updateConfigParameter');
    Route::delete('configparameter/{id_config_parametro}', 'SetupenrollmentController@deleteConfigParameter');

    //criterio parametro
    Route::get('enrollmentcriterion', 'SetupenrollmentController@listEnrollmentCriterion');
    Route::get('enrollmentcriterion/{id_criterio}', 'SetupenrollmentController@showEnrollmentCriterion');
    Route::post('enrollmentcriterion', 'SetupenrollmentController@addEnrollmentCriterion');
    Route::put('enrollmentcriterion/{id_criterio}', 'SetupenrollmentController@updateEnrollmentCriterion');
    Route::delete('enrollmentcriterion/{id_criterio}', 'SetupenrollmentController@deleteEnrollmentCriterion');
});

Route::group(['prefix' => 'movil', 'namespace' => 'HumanTalent'], function () {
    Route::get('validate-device', 'AssistanceController@ValidateDevice');
    Route::post('assistance', 'AssistanceController@SaveAssistanceDevice');
    Route::post('reset', 'AssistanceController@ResetDeviceUser');
    Route::get('device', 'AssistanceController@DeviceUser');
});


//GTH
Route::group(['prefix' => 'gth/settings', 'namespace' => 'HumanTalentMgt'], function () {
    //Grupo escala
    Route::get('scale-group', 'ConfigurationController@ListScaleGroups');
    Route::get('scale-group/{id_scale_group}', 'ConfigurationController@ShowScaleGroup');
    Route::post('scale-group', 'ConfigurationController@AddScaleGroup');
    Route::put('scale-group/{id_scale_group}', 'ConfigurationController@UpdateScaleGroup');
    Route::delete('scale-group/{id_scale_group}', 'ConfigurationController@DeleteScaleGroup');

    //cargo puesto
    Route::get('position', 'ConfigurationController@ListPositions');
    Route::get('position/{id_puesto}', 'ConfigurationController@ShowPosition');
    Route::post('position', 'ConfigurationController@AddPosition');
    Route::put('position/{id_puesto}', 'ConfigurationController@UpdatePosition');
    Route::delete('position/{id_puesto}', 'ConfigurationController@DeletePosition');

    //parameters
    Route::get('parameters', 'ConfigurationController@ListParameters');
    Route::post('parameters', 'ConfigurationController@procParametro');
    Route::put('parameters/{id_parametro}', 'ConfigurationController@updateParametro');

    //perfil puesto
    Route::post('profile-position/profile', 'ConfigurationController@AddProfilePosition');
    Route::get('profile-position', 'ConfigurationController@ListProfilePositions');
    Route::delete('profile-position/{id_perfil_puesto}', 'ConfigurationController@DeleteProfilePositions');
    Route::get('profile-position/{id_perfil_puesto}', 'ConfigurationController@ShowListProfilePositions');

    Route::put('profile-position/{id_perfil_puesto}/situacions', 'ConfigurationController@updateSituationEducation');

    Route::post('profile-position', 'ConfigurationController@saveProfilePositionDatos');
    Route::put('profile-position/{id_perfil_puesto}', 'ConfigurationController@updateExperence');

    Route::get('staff-position', 'ConfigurationController@ListStaffPositions');
    Route::post('staff-position', 'ConfigurationController@SaveAfterStaffPosition');
    ///responsability
    Route::post('profile-position-resp-fun', 'ConfigurationController@addResponsabilityes');
    Route::delete('profile-position-resp-fun/{perfil_puesto_resp}', 'ConfigurationController@deleteResponsabilityes');
    Route::put('profile-position-resp-fun/{perfil_puesto_resp}', 'ConfigurationController@updateResponsability');
    ////funciones
    Route::post('profile-position-func', 'ConfigurationController@addFuntions');
    Route::delete('profile-position-func/{perfil_puesto_func}', 'ConfigurationController@deleteFuntions');
    Route::put('profile-position-func/{perfil_puesto_func}', 'ConfigurationController@updateFuntions');

    Route::get('profile-position-resp-fun', 'ConfigurationController@ListResponsFuncion');

    Route::post('profile-position-espdip', 'ConfigurationController@addDiplomations');
    Route::get('profile-position-espdip', 'ConfigurationController@listDiplomations');
    Route::delete('profile-position-espdip/{perfil_puesto_espdip}', 'ConfigurationController@deleteDiplomations');


    Route::post('profile-position-profesion', 'ConfigurationController@addProfesionOcupation');
    Route::get('profile-position-profesion', 'ConfigurationController@listProfesionOcupation');
    Route::delete('profile-position-profesion/{id_perfil_puesto_prof}', 'ConfigurationController@deletProfesionOcupation');

    Route::post('profile-levelLenguages', 'ConfigurationController@addLenguagesLevel');
    Route::get('profile-levelLenguages', 'ConfigurationController@listLenguagesLevel');
    Route::delete('profile-levelLenguages/{id_tipoidioma}', 'ConfigurationController@deleteLenguagesLevel');

    Route::post('profile-offimatica-level', 'ConfigurationController@addOffimaticaLevel');
    Route::get('profile-offimatica-level', 'ConfigurationController@listOffimaticaLevel');
    Route::delete('profile-offimatica-level/{id_conoci_inform}', 'ConfigurationController@deleteOffimaticaLevel');

    Route::post('profile-requirements', 'ConfigurationController@addRequiremnts');
    Route::get('profile-requirements', 'ConfigurationController@listRequiremnts');
    Route::delete('profile-requirements/{id_requisitos}', 'ConfigurationController@deleteRequiremnts');

    Route::get('list-trabajador-holidays', 'ConfigurationController@listTrabajadorHolidays');
    Route::get('list-trabajador-holidays/show', 'ConfigurationController@showTrabajadorHolidays');
    Route::post('group-competences', 'ConfigurationController@addGroupCompetences');
    Route::get('group-competences', 'ConfigurationController@listGroupCompetences');
    Route::delete('group-competences/{id_grupo_compentencia}', 'ConfigurationController@deleteGroupCompetences');
    Route::put('group-competences/{id_grupo_compentencia}', 'ConfigurationController@updateGroupCompetences');

    Route::post('competences-detail', 'ConfigurationController@addCompetencesGroup');
    Route::get('competences-detail', 'ConfigurationController@listCompetencesGroup');
    Route::delete('competences-detail/{id_grupo_compentencia_nivel}', 'ConfigurationController@deleteCompetencesGroup');
    Route::put('competences-detail/{id_grupo_compentencia_nivel}', 'ConfigurationController@updateCompetencesGroup');

    Route::post('competences-lb', 'ConfigurationController@addCompetenciasLb');
    Route::delete('competences-lb/{id_competencia}', 'ConfigurationController@deleteCompetenciasLb');
    Route::put('competences-lb/{id_competencia}', 'ConfigurationController@updateCompetenciasLb');

    Route::post('comitions-dir', 'ConfigurationController@addComitions');
    Route::get('comitions-dir', 'ConfigurationController@listComitions');
    Route::delete('comitions-dir/{id_perfil_puesto_comis_dir}', 'ConfigurationController@deleteComitions');

    Route::post('procces-dir', 'ConfigurationController@saveProcess');
    Route::get('procces-dir', 'ConfigurationController@listProcess');
    Route::delete('procces-dir/{id_perfil_puesto_proc}', 'ConfigurationController@deleteProcess');

    Route::post('jefe-funcional', 'ConfigurationController@saveJefeFun');
    Route::get('jefe-funcional', 'ConfigurationController@listJefeFun');
    Route::delete('jefe-funcional/{id_perfil_puesto_jefe_fun}', 'ConfigurationController@deleteJefeFun');

    Route::post('supervicion-funcional', 'ConfigurationController@saveSuperFunc');
    Route::get('supervicion-funcional', 'ConfigurationController@listSuperFunc');
    Route::delete('supervicion-funcional/{id_perfil_puesto_sup_fun}', 'ConfigurationController@deleteSuperFunc');
    Route::post('add-update-acceso-nivel', 'ConfigurationController@saveOrUpdateNivelesAcceso');
    Route::delete('add-update-acceso-nivel/{id_acceso_nivel}', 'ConfigurationController@deleteNivelesAcceso');
    Route::get('add-update-acceso-nivel', 'ConfigurationController@moduloList');
    Route::get('add-update-acceso-nivel/show/{id_acceso_nivel}', 'ConfigurationController@showModuleList');
    Route::post('add-update-acceso-nivel/details', 'ConfigurationController@saveDetailsAccesoNivel');
    Route::get('add-update-acceso-nivel/list-details/{id_acceso_nivel}', 'ConfigurationController@listAccesoNivelDetalle');
    Route::delete('add-update-acceso-nivel/details/{id_acceso_nivel_det}', 'ConfigurationController@deleteDetalleAccesLevel');
    Route::put('add-update-acceso-nivel/update-details/{id_acceso_nivel_det}', 'ConfigurationController@updateDetalisAcceso');
});
Route::group(['prefix' => 'gth/paramter', 'namespace' => 'HumanTalentMgt'], function () {

    //anho parametro
    Route::get('params/yearparameter', 'ParameterController@getYearParameter');
    Route::get('params/my-entities', 'ParameterController@listMyEntities');
    Route::get('params/position', 'ConfigurationController@positions');
    Route::get('params/searchs-group', 'ParameterController@SearchGlobalGroupScale');

    Route::get('params/levels', 'ParameterController@Level');
    Route::get('params/grade', 'ParameterController@Grade');
    Route::get('params/subgrades', 'ParameterController@SubGrad');

    Route::get('params/searchs-position', 'ParameterController@SearchGlobalPositions');
    Route::get('params/searchs-deparataments', 'ParameterController@SearchListDepartaments');
    Route::get('params/depto-entities', 'ParameterController@listDeptoEntidad');
    Route::get('params/year-salary-scale', 'ParameterController@getYearSalaryScale');
    Route::get('params/searchs-cargo-profile-position', 'ParameterController@SearchCargoProfilePositions');

    Route::get('params/year-staff-positions', 'ParameterController@getYearStaffPositions');
    Route::get('params/level-inteligence', 'ParameterController@getLevelIntelligence');


    Route::get('params/instructions-diplomations', 'ParameterController@listFormations');

    Route::get('params/searchs-profesion', 'ParameterController@searchProfesionOcupation');
    Route::get('params/requirements', 'ParameterController@getRequirements');
    Route::get('params/data-base-person', 'ParameterController@getDataP1');
    Route::get('params/searchs-pais', 'ParameterController@SearchPais');
    Route::get('params/ubigueo-provincia', 'ParameterController@getProv');
    Route::get('params/ubigueo-distrito', 'ParameterController@getDistri');
    Route::get('params/data-base-academic', 'ParameterController@getDataP2');
    Route::get('params/searchs-institucion', 'ParameterController@SearchInstitucion');
    Route::get('params/searchs-carrera', 'ParameterController@SearchCarrera');
    Route::get('params/searchs-person', 'ParameterController@SearchPersona');
    Route::get('params/searchs-person-no-worker', 'ParameterController@SearchPersonaNoWorker');
    Route::get('params/periodo-vac', 'ParameterController@getPeriodoHolidays');
    Route::get('params/competences', 'ParameterController@listCompetences');
    Route::get('params/type-leve-competences', 'ParameterController@getTypeLevelCompetences');
    Route::get('params/data-base-family', 'ParameterController@getDataP3');
    Route::get('params/data-base-labor', 'ParameterController@getDataP4');
    Route::get('params/grupo-escala', 'ParameterController@listGrupoEscala');
    Route::get('params/carrera', 'ParameterController@getCarrera');

    Route::get('params/reg-person', 'ParameterController@getDataRegPerson');

    Route::get('params/competences-lta', 'ParameterController@listCompetenciasLb');

    Route::get('params/position-group-competence', 'ParameterController@getGroupCompetences');
    Route::get('params/search-comitions', 'ParameterController@searchComitions');
    Route::get('params/search-process', 'ParameterController@searchProcess');

    Route::get('params/salary-scale-level/{id_perfil_puesto}', 'ParameterController@salaryScaleLevel');
    Route::get('params/estado-periodo-vac', 'ParameterController@getEstadoPeriodoVac');
    Route::get('params/tipo-suspension', 'ParameterController@getTipoSuspension');

    Route::get('params/type-control-persons', 'ParameterController@getControlsPersons');
    Route::get('params/search-trabajador', 'ParameterController@SearchTrabajador');
    Route::get('params/type-concept', 'ParameterController@getTypeConcept');
    Route::get('params/type-sunat-concept', 'ParameterController@getTypeSunatConcept');
    Route::get('params/type-aps-concept', 'ParameterController@getTypeApsConcept');

    Route::get('params/estado-lica-per', 'ParameterController@getEstadoLicaPer');

    Route::get('params/searchs-position-filtrado', 'ParameterController@SearchGlobalPositionsFiltrado');

    Route::get('params/type-payroll', 'ParameterController@getTypePayroll');
    Route::get('params/group-payroll', 'ParameterController@getGroupPayroll');

    Route::get('params/search-trabajador-overtime', 'ParameterController@SearchTrabajadorOverTime');
    Route::get('params/estado-overtime', 'ParameterController@getEstadoOvertime');

    Route::get('params/weekdays', 'ParameterController@getDays');
    Route::get('params/type-shedule', 'ParameterController@getTypeShedule');

    Route::get('params/getyear-assistance-control', 'ParameterController@getYearAssistance');

    Route::get('params/months', 'ParameterController@getMonths');
    Route::get('params/monthly-payment-type', 'ParameterController@getMonthlyPaymentType');
    Route::get('params/year-assign-month', 'ParameterController@getYearAssignMonth');
    Route::get('params/mileage-type', 'ParameterController@getMileageType');

    Route::get('params/search-concept-plla', 'ParameterController@searchConceptPlla');
    Route::get('params/estado-planilla', 'ParameterController@getEstadoPlanilla');
    Route::get('params/planilla_entidad', 'ParameterController@getPlanillaEntidad');

    Route::get('params/tipo_periodo_bs', 'ParameterController@getTypePeriodBS');
    Route::get('params/trabajador-search-holidays', 'ParameterController@searchTrabajadorHolidays');
    Route::get('params/periodo-asignado', 'ParameterController@listMyPeriodosAsig');
    Route::get('params/periodos-sin-asignar', 'ParameterController@getPeriodosSinAsig');

    Route::get('params/trabajador-aprobe', 'ParameterController@searchsTrabajadorAprobe');
    Route::get('params/puesto-trabajador', 'ParameterController@trabajadoresPuesto');

    Route::get('params/firma-trabajador', 'ParameterController@getFirmaTrabajador');

    Route::get('params/request-personal-data', 'ParameterController@getRequestPersonalData');

    Route::get('params/perfil-area', 'ParameterController@getPerfilPuestobyArea');

    Route::get('params/searchs-area-request', 'ParameterController@getAreaRequest');
    Route::get('params/get-ccosto-request', 'ParameterController@getCCosto');

    Route::get('params/nivel-modalidad', 'ParameterController@getNivelModalidad');
    Route::get('params/scale-salary-teacher-first', 'ParameterController@getFistScaleSalatyTeacher');

    Route::get('params/get-nivel-modalidad', 'ParameterController@getNivModInfo');
    Route::get('params/person-cost-by-hour', 'ParameterController@getInfoPersonaCBH');
    Route::get('params/get-info-cost-hour', 'ParameterController@getInfoCostoHour');

    Route::get('params/get-tipo-contrato', 'ParameterController@getTipoContrato');


    Route::get('params/get-area-parents', 'ParameterController@getAreaParents');
    Route::get('params/get-ocupations', 'ParameterController@searchOcupacion');
    Route::get('params/contract/worker-active', 'ParameterController@getDataWorkerActive');

    Route::get('params/get-scale-salary', 'ParameterController@getScaleSalary');
    Route::get('params/estado-vac-trab', 'ParameterController@getTipoEstadoVacTrab');
    Route::get('params/estado-solicitud', 'ParameterController@getEstadoSolicitud');
    Route::get('params/periodo-aprobe', 'ParameterController@getPeriodoAprobe');

    Route::get('params/my-entity-access', 'ParameterController@listMyEntityAccess');
    Route::get('params/acceso-nivel', 'ParameterController@getAccesoNivel');
    Route::get('params/my-depto-access', 'ParameterController@listMyDeptoAccess');
    Route::get('params/search-my-area-access', 'ParameterController@searchMyListAreasAccess');
    Route::get('params/arbol-list', 'ParameterController@listArbolProfilePosition');
    Route::get('params/personal-access-search', 'ParameterController@searchPersonaAccess');
    Route::get('params/module-father', 'ParameterController@moduleFather');
    // Route::get('params/module-list', 'ParameterController@moduloList');
    Route::get('params/tipo-nivel-vista', 'ParameterController@tipoNivelVista');
    Route::get('params/tipo-nivel-area', 'ParameterController@tipoNivelArea');
    Route::get('params/entidad-depto-area', 'ParameterController@entidadDeprtamentoAreaPersona');
    Route::get('params/areas-search-global', 'ParameterController@searchAreas');
});

Route::group(['prefix' => 'gth/payroll', 'namespace' => 'HumanTalentMgt'], function () {

    //escala salarial
    Route::get('salary-scale', 'PayrollController@ListSalaryScale');
    Route::get('salary-scale/{id_scale_group}', 'PayrollController@ShowScaleGroup');
    Route::post('salary-scale', 'PayrollController@AddSalaryScale');
    Route::put('salary-scale/{id_salary_scale}', 'PayrollController@UpdateSalaryScale');
    Route::delete('salary-scale/{id_salary_scale}', 'PayrollController@DeleteSalaryScale');

    //regimen pensionaria
    Route::get('pension-scheme', 'PayrollController@ListsPensionScheme');
    Route::put('pension-scheme/{id}', 'PayrollController@updatePensionScheme');

    //Asignación Mensual Planilla general
    Route::get('assign-monthly-searchperson', 'PayrollController@getSearchPersonAssign');
    Route::get('assign-monthly', 'PayrollController@ListGeneralPayment');
    Route::get('assign-monthly-xls', 'PayrollController@ListGeneralPaymentXLS');
    Route::post('assign-monthly-valid', 'PayrollController@ListGeneralPaymentValid');
    Route::post('assign-monthly', 'PayrollController@addGeneralPayment');
    Route::put('assign-monthly/{id_persona}', 'PayrollController@updateGeneralPayment');
    Route::post('assign-monthly-delete', 'PayrollController@DeleteGeneralPayment');
    Route::put('assign-monthly/{id_tipo_pago_mensual}/all', 'PayrollController@updateGeneralPaymentAll');

    //Asignación Mensual Planilla mensual
    Route::post('assign-month-payment', 'PayrollController@addMonthPayment');

    Route::get('type-pay-month', 'PayrollController@listTypePayMonth');
    Route::post('type-pay-month', 'PayrollController@addTypePayMonth');
    Route::delete('type-pay-month/{id_tipo_pago_mensual}', 'PayrollController@deleteTypePayMonth');
    Route::get('type-pay-month/{id_tipo_pago_mensual}', 'PayrollController@showTypePayMonth');
    Route::put('type-pay-month/{id_tipo_pago_mensual}', 'PayrollController@updateTypePayMonth');

    Route::post('assign-month-payment-delete', 'PayrollController@DeleteMonthPayment');
    Route::get('assign-month-payment-pdf', 'PayrollController@MonthPaymentPDF');

    Route::post('planilla-control', 'PayrollController@addPayprolControl');
    Route::get('planilla-control', 'PayrollController@listPayprolControl');
    Route::delete('planilla-control/{id_proc_planilla}', 'PayrollController@deletePayprolControl');
    Route::put('planilla-control/{id_proc_planilla}', 'PayrollController@updatePayprolControl');
    Route::put('planilla-control/{id_proc_planilla}/close', 'PayrollController@updatePayprolControlClose');

    //escala salarial docente
    Route::post('salary-scale-teacher/set-data', 'PayrollController@regSalaryScaleTeacher');
    Route::get('salary-scale-teacher/get-data', 'PayrollController@getSalaryScaleTeacher');
    Route::get('salary-scale-teacher/get-data-sp', 'PayrollController@getSalaryScaleTeacherSp');

    //cost by hour
    Route::get('cost-by-hour/sem-program', 'PayrollController@getSemestrePrograma');
    Route::get('cost-by-hour/cost-assigned', 'PayrollController@getCostAssigned');
    Route::post('cost-by-hour/cost-reg', 'PayrollController@regCostxHour');
    Route::put('cost-by-hour/cost-upd/{id_costo}', 'PayrollController@updCostxHour');
});

Route::group(['prefix' => 'gth/holidays', 'namespace' => 'HumanTalentMgt'], function () {

    //escala salarial
    Route::get('generate-vac-trab', 'HolidaysController@getGeneratePerioVac');
    Route::post('generate-vac-trab', 'HolidaysController@addProceGeneratePerioVac');
    Route::post('generate-vac-trab/masivo', 'HolidaysController@agregarProceGeneratePerioVacMasivo');

    Route::post('periodo-vacational', 'HolidaysController@saveProgramaming');
    Route::get('periodo-vacational', 'HolidaysController@listProgramingVacation');
    Route::delete('periodo-vacational/{id_rol_vacacion}', 'HolidaysController@deleteProgramingVacation');
    Route::put('periodo-vacational/{id_rol_vacacion}', 'HolidaysController@updateProgramingVacation');
    Route::put('periodo-vacational/{id_rol_vacacion}/confirmacion-salida', 'HolidaysController@updateVacacionesConfirm');
    Route::put('periodo-vacational/{id_rol_vacacion}/confirmacion-retorno', 'HolidaysController@updateRetornoVacacionesConfirm');
    Route::get('periodo-vacational/pdf', 'HolidaysController@myPapeletaHolidays');

    Route::post('periodo-vacational/papeleta-email', 'HolidaysController@emailPapeletaSalida');


    Route::get('aprobar-vacacion', 'HolidaysController@listAprobeHeader');
    Route::put('aprobar-vacacion/{id_periodo_vac_trab}', 'HolidaysController@updateAprobeHeaderChild');

    Route::post('reschedule-vacation', 'HolidaysController@rescheduleVacation');
    // Route::post('reschedule-vacation', 'HolidaysController@rescheduleVacation');
    Route::get('reschedule-vacation/{id_parent}', 'HolidaysController@getRescheduleVacation');

    Route::get('plla-period-holidays', 'HolidaysController@listPeriodHolidays');
    Route::post('plla-period-holidays', 'HolidaysController@addPeriodHolidays');
    Route::put('plla-period-holidays/{id_periodo_vac}', 'HolidaysController@updatePeriodHolidays');
    Route::delete('plla-period-holidays/{id_periodo_vac}', 'HolidaysController@deletePeriodHolidays');

    Route::post('request-holidays', 'HolidaysController@addSolicitudHolidays');
    Route::get('request-holidays', 'HolidaysController@listReques');
    Route::get('request-holidays/show', 'HolidaysController@showRequest');
    Route::get('request-holidays/{id_sol_vac_adel}', 'HolidaysController@listAdelantoDetalle');
    Route::put('request-holidays/{id_sol_vac_adel}', 'HolidaysController@updateSolicitudHolidays');
    Route::delete('request-holidays/detalle/{id_sol_vac_adel_det}', 'HolidaysController@deleteSolDetalle');
    Route::delete('request-holidays/{id_sol_vac_adel}', 'HolidaysController@deleteSolicitud');
    Route::put('request-holidays/aprobe/{id_sol_vac_adel}', 'HolidaysController@refusedAlularSolicitud');
    Route::post('request-holidays/adelanto-vac', 'HolidaysController@agregarAdelantoVacacional');
    Route::get('list-holidays', 'HolidaysController@listTrabajadorHolidays');
});

Route::group(['prefix' => 'gth/licenses', 'namespace' => 'HumanTalentMgt'], function () {

    Route::get('type-suspension', 'LicensesController@listTypeSuspension');
    Route::get('type-suspension/{id_tipo_suspension}', 'LicensesController@showTypeSuspension');
    Route::post('type-suspension', 'LicensesController@addTypeSuspension');
    Route::put('type-suspension/{id_tipo_suspension}', 'LicensesController@updateTypeSuspension');
    Route::delete('type-suspension/{id_tipo_suspension}', 'LicensesController@deleteTypeSuspension');

    Route::post('register-licenses-permits', 'LicensesController@addRegisterLisensesPermits');
    Route::get('register-licenses-permits', 'LicensesController@listRegisterLisensesPermits');
    Route::put('register-licenses-permits/{id_licencia_permiso}', 'LicensesController@updateRegisterLisensesPermits');


    // Route::put('reschedule-vacation/{id_rol_vacacion}/others', 'HolidaysController@updateRescheduleVacation');
});


Route::group(['prefix' => 'gth/contract', 'namespace' => 'HumanTalentMgt'], function () {
    Route::get('workers/workers', 'EmployeeController@listWorker');
    Route::get('workers/workers-first-person', 'EmployeeController@searchFirsPerson');
    Route::post('workers/workers-first-person', 'EmployeeController@personalInformation');
    Route::post('workers/workers-academic-social', 'EmployeeController@academicoSocial');
    Route::post('workers/workers-family', 'EmployeeController@dataFamily');

    Route::get('workers/workers-direcction', 'EmployeeController@listDirecction');
    Route::put('workers/workers-direcction/{id_direccion}', 'EmployeeController@updateDireccion');
    Route::post('workers/workers-direcction', 'EmployeeController@addDireccion');
    Route::delete('workers/workers-direcction/{id_direccion}', 'EmployeeController@deleteDireccion');

    //Route::get('workers/workers-social', 'EmployeeController@listWorkerSocial');
    Route::get('workers/workers-academic', 'EmployeeController@listWorkerAcademic');
    //Route::put('workers/workers-social/{dni}', 'EmployeeController@updateAcademicoSocial');
    Route::put('workers/workers-academic/{dni}', 'EmployeeController@updateAcademicoSocial');

    Route::get('workers/workers-superior', 'EmployeeController@superiorWorker');
    Route::get('workers/workers-superior-son', 'EmployeeController@hijoMayorSuperior');
    Route::delete('workers/workers-superior/{id_item}', 'EmployeeController@deleteSuperior');
    Route::post('workers/workers-superior', 'EmployeeController@addSuperiorNivel');
    Route::post('workers/workers-superior-test', 'EmployeeController@updateSuperiro');


    Route::get('workers/workers-family', 'EmployeeController@parentWorker');
    Route::get('workers/workers-family-parents', 'EmployeeController@getParents');
    Route::post('workers/workers-family-parents', 'EmployeeController@updateParents');

    Route::post('workers/workers-family-parents-prueba', 'EmployeeController@saveFile');

    Route::delete('workers/workers-family/{id_vinculo_familiar}', 'EmployeeController@deleteParentesco');

    Route::get('workers/workers-labor', 'EmployeeController@getAspectoLaboral');
    Route::post('workers/workers-labor', 'EmployeeController@addBank');
    Route::post('workers/workers-labor-ctas', 'EmployeeController@addCtaBank');
    Route::post('workers/workers-labor-update', 'EmployeeController@updateLabor');
    Route::put('workers/workers-labor-bank/{id_pbancaria}', 'EmployeeController@deleteBank');
    Route::post('workers/workers-labor-bank-update', 'EmployeeController@updateBank');

    Route::get('workers/workers-account', 'EmployeeController@getAccountBank');

    Route::get('estado-count', 'ContractController@estado_cont');

    Route::get('estado-count/list', 'ContractController@listEstadoContDepto');
    Route::delete('estado-count/{id_estado_cont_dept}', 'ContractController@deleteListEstadoContDepto');
    Route::put('estado-count/{id_estado_cont_dept}', 'ContractController@updateEstadoCont');
    Route::post('estado-count', 'ContractController@addEstadoContDept');

    Route::get('workers/grades-information', 'EmployeeController@getInformationAcademic');
    Route::post('workers/grades-information', 'EmployeeController@addInformationAcademic');
    Route::post('workers/update-grades-information', 'EmployeeController@updateInformationAcademic');

    Route::get('workers/basic-formation', 'EmployeeController@getBasicFormation');
    Route::post('workers/basic-formation', 'EmployeeController@createBasicFormation');
    Route::put('workers/basic-formation/{id_basic_formation}', 'EmployeeController@updateBasicFormation');

    Route::post('workers/upload-file-academic', 'EmployeeController@uploadFileAcademic');

    Route::get('workers/academic-training', 'EmployeeController@getAcademicTraining');
    Route::post('workers/academic-training', 'EmployeeController@createAcademicTraining');
    Route::put('workers/academic-training/{id_training}', 'EmployeeController@updateAcademicTraining');

    Route::get('workers/academic-article', 'EmployeeController@getAcademicArticle');
    Route::post('workers/academic-article', 'EmployeeController@createAcademicArticle');
    Route::put('workers/academic-article/{id_article}', 'EmployeeController@updateAcademicArticle');

    Route::get('workers/academic-proyection', 'EmployeeController@getAcademicProyection');
    Route::post('workers/academic-proyection', 'EmployeeController@createAcademicProyection');
    Route::put('workers/academic-proyection/{id_project}', 'EmployeeController@updateAcademicProyection');

    Route::get('workers/academic-book', 'EmployeeController@getAcademicBook');
    Route::post('workers/academic-book', 'EmployeeController@createAcademicBook');
    Route::put('workers/academic-book/{id_book}', 'EmployeeController@updateAcademicBook');

    Route::get('workers/academic-asesoria', 'EmployeeController@getAcademicAsesoria');
    Route::post('workers/academic-asesoria', 'EmployeeController@createAcademicAsesoria');
    Route::put('workers/academic-asesoria/{id_asesoria}', 'EmployeeController@updateAcademicAsesoria');

    Route::get('workers/academic-membership', 'EmployeeController@getAcademicMembership');
    Route::post('workers/academic-membership', 'EmployeeController@createAcademicMembership');
    Route::put('workers/academic-membership/{id_membership}', 'EmployeeController@updateAcademicMembership');

    Route::get('workers/academic-category', 'EmployeeController@getAcademicCategory');
    Route::post('workers/academic-category', 'EmployeeController@createAcademicCategory');
    Route::put('workers/academic-category/{id_category}', 'EmployeeController@updateAcademicCategory');

    Route::get('workers/academic-regime', 'EmployeeController@getAcademicRegime');
    Route::post('workers/academic-regime', 'EmployeeController@createAcademicRegime');
    Route::put('workers/academic-regime/{id_regime}', 'EmployeeController@updateAcademicRegime');

    Route::get('workers/academic-hour', 'EmployeeController@getAcademicHour');
    Route::post('workers/academic-hour', 'EmployeeController@createAcademicHour');
    Route::put('workers/academic-hour/{id_hour}', 'EmployeeController@updateAcademicHour');

    Route::get('workers/academic-prize', 'EmployeeController@getAcademicPrize');
    Route::post('workers/academic-prize', 'EmployeeController@createAcademicPrize');
    Route::put('workers/academic-prize/{id_prize}', 'EmployeeController@updateAcademicPrize');

    Route::get('workers/academic-jury', 'EmployeeController@getAcademicJury');
    Route::post('workers/academic-jury', 'EmployeeController@createAcademicJury');
    Route::put('workers/academic-jury/{id_jury}', 'EmployeeController@updateAcademicJury');

    Route::get('workers/academic-profesional', 'EmployeeController@getAcademicProfesional');
    Route::post('workers/academic-profesional', 'EmployeeController@createAcademicProfesional');
    Route::put('workers/academic-profesional/{id_profesional}', 'EmployeeController@updateAcademicProfesional');

    Route::get('workers/academic-experience', 'EmployeeController@getAcademicExperience');
    Route::post('workers/academic-experience', 'EmployeeController@createAcademicExperience');
    Route::put('workers/academic-experience/{id_experience}', 'EmployeeController@updateAcademicExperience');

    Route::get('workers/academic-admin', 'EmployeeController@getAcademicAdmin');
    Route::post('workers/academic-admin', 'EmployeeController@createAcademicAdmin');
    Route::put('workers/academic-admin/{id_admin}', 'EmployeeController@updateAcademicAdmin');

    Route::get('workers/academic-language', 'EmployeeController@getAcademicLanguage');
    Route::post('workers/academic-language', 'EmployeeController@createAcademicLanguage');
    Route::put('workers/academic-language/{id_language}', 'EmployeeController@updateAcademicLanguage');

    Route::get('workers/get-person', 'EmployeeController@getPersonData');
    Route::post('workers/reg-person', 'EmployeeController@regPersona');
    Route::post('workers/upd-person', 'EmployeeController@updPersona');

    Route::get('workers/aut-diezmo/pdf', 'EmployeeController@autorizathionDiezmoPdf');
    Route::get('workers/aut-cuenta/pdf', 'EmployeeController@autorizathionCtaPdf');

    /**plantilla constrato */
    Route::get('template', 'ContractController@getPlantilla');
    Route::get('template/get-template', 'ContractController@getPlantillaContrato');
    Route::post('template', 'ContractController@createPlantilla');
    Route::put('template/u/{id_contrato_plantilla}', 'ContractController@updatePlantilla');
    Route::get('template/parameters', 'ContractController@getParametrosByTipo');

    //ARMAR EL PDF - Test
    //Route::get('template/test', 'ContractController@testPDFRENDER');
    //Route::get('template/parameters', 'ContractController@getParametros');
    //Route::post('template/parameters', 'ContractController@createParametros');
    //Route::put('template/parameters/u/{id_contrato_parametro}', 'ContractController@updateParametros');

    //Order Contract
    Route::post("order-contract/create", "ContractController@createOrderContract");
    Route::post("order-contract/update", "ContractController@updateOrderContract");
    Route::get("order-contract/get-generated", "ContractController@getGenerated");

    Route::get("order-contract/get-contract", "ContractController@getContract");
    Route::get("order-contract/get-contract-active-worker", "ContractController@getContractActiveWorker");
    Route::get("order-contract/get-contract-active", "ContractController@getContractActive");
    Route::post("order-contract/update-status", "ContractController@changestatus");
    Route::get('generate-contract/first', "ContractController@firstDataGenerate");
    Route::get('generate-contract/get-contract', "ContractController@getContractToGen");


    Route::get('generate-contract/test-txt', "ContractController@generateTxt");
    Route::get('generate-contract/get-info', "ContractController@getInfoContractExplicit");
});

Route::group(['prefix' => 'gth/request', 'namespace' => 'HumanTalentMgt'], function () {
    Route::get('personal/requests', 'RequestController@getSolicitudes');
    Route::get('personal/request', 'RequestController@getSolicitud');
    Route::get('personal/request-by-solicitud', 'RequestController@getSugerenciasBySolicitud');
    Route::get('personal/cantidad-request', 'RequestController@getCantidadSolicitud');
    Route::post('personal/request', 'RequestController@regRequest');
    Route::put('personal/request/{id_solic_reque}', 'RequestController@updRequest');
    Route::put('personal/request-candidate/{id_solic_req_candidato}', 'RequestController@updSuggestRequest');
    Route::post('personal/create-request-candidate', 'RequestController@regSuggestRequest');
    Route::delete('personal/request-candidate/{id_solic_req_candidato}', 'RequestController@deleteSuggestRequest');

    Route::get('personal/approved', 'RequestController@listApproved');
    Route::get('personal/selected-request', 'RequestController@selectRequest');
    Route::get("personal/contract/status", "RequestController@listStatusContract");
});

Route::group(['prefix' => 'gth/concept', 'namespace' => 'HumanTalentMgt'], function () {

    //concepto sunat
    Route::get('concept-sunat', 'ConceptController@ListSunatConcept');
    Route::get('concept-sunat/{id_concepto_planilla_sunat}', 'ConceptController@ShowSunatConcept');
    Route::post('concept-sunat', 'ConceptController@AddSunatConcept');
    Route::put('concept-sunat/{id_concepto_planilla_sunat}', 'ConceptController@UpdateSunatConcept');
    Route::delete('concept-sunat/{id_concepto_planilla_sunat}', 'ConceptController@DeleteSunatConcept');

    //concepto aps
    Route::get('concept-aps', 'ConceptController@ListApsConcept');

    // concepto
    Route::get('concept', 'ConceptController@ListConcept');
    Route::get('concept-xls', 'ConceptController@ListConceptXLS');
    Route::get('concept-type', 'ConceptController@ListConceptByType');
    Route::get('concept/{id_concepto_planilla}', 'ConceptController@ShowConcept');
    Route::post('concept', 'ConceptController@AddConcept');
    Route::put('concept/{id_concepto_planilla}', 'ConceptController@UpdateConcept');
    Route::delete('concept/{id_concepto_planilla}', 'ConceptController@DeleteConcept');
    Route::delete('concept-proc/{id_concepto_planilla_proc}', 'ConceptController@DeleteConceptProc');
    //asignacion de conceptos a grupo planilla

    Route::get('assign-concept-group', 'ConceptController@ListConceptPayrollGroup');
    Route::get('assign-concept-group-asisign', 'ConceptController@ListConceptPayrollGroupAssign');
    Route::get('assign-concept-group-show', 'ConceptController@showConceptPayrollGroupAssign');
    Route::put('assign-concept-group/{id_concepto_planilla}/{id_planilla_entidad}', 'ConceptController@UpdateConceptPayrollGroup');
    Route::post('assign-concept-group', 'ConceptController@AddConceptPayrollGroup');
    Route::delete('assign-concept-group/{id_planilla_entidad}/{id_concepto_planilla}', 'ConceptController@DeleteConceptPayrollGroup');
});


Route::group(['prefix' => 'gth/overtime', 'namespace' => 'HumanTalentMgt'], function () {

    Route::post('overtime-register', 'OvertimeController@addOvertimeRegister');
    Route::get('overtime-register', 'OvertimeController@listRegisterOvertime');
    Route::put('overtime-register/{id_sobretiempo}', 'OvertimeController@updateRegisterOvertime');
    Route::put('overtime-register/{id_sobretiempo}/refused', 'OvertimeController@updateRefusedOvertime');
});
Route::group(['prefix' => 'gth/assistance', 'namespace' => 'HumanTalentMgt'], function () {

    Route::post('type-assistance', 'AssistanceController@addTypeShedule');
    Route::post('type-assistance/detail', 'AssistanceController@addTypeSheduleDetails');
    Route::get('type-assistance', 'AssistanceController@listTypeShedule');
    Route::get('type-assistance/{id_tipo_horario}/details', 'AssistanceController@listTypeSheduleDetails');
    Route::delete('type-assistance/{id_dias}/details', 'AssistanceController@deleteTypeSheduleDetails');
    Route::delete('type-assistance/{id_tipo_horario}', 'AssistanceController@deleteTypeShedule');
    Route::get('type-assistance/{id_tipo_horario}', 'AssistanceController@showTypeShedule');
    Route::put('type-assistance/{id_tipo_horario}', 'AssistanceController@updateTypeShedule');

    Route::get('type-assistance/{id_tipo_horario}/{id_dias}/show', 'AssistanceController@listTypeSheduleDetailsShow');
    Route::put('type-assistance/{id_dias}/details', 'AssistanceController@updateTypeSheduleDetails');

    Route::get('control-assistance', 'AssistanceController@listControlAssist');
    Route::get('control-assistance/{id_asistencia}', 'AssistanceController@listControlAssistShow');
    Route::put('control-assistance/{id_asistencia}', 'AssistanceController@updateControlAssist');
    Route::post('control-assistance', 'AssistanceController@copyManualMarcation');
    Route::get('control-assistance/{id_asistencia}/marcation', 'AssistanceController@listManualMarcation');
    // fin Apis

    Route::get('list-trabajador-assistance', 'AssistanceController@listTrabajadorAsistenceControl');
});

Route::group(['prefix' => 'gth/concept', 'namespace' => 'HumanTalentMgt'], function () {

    //concepto sunat
    Route::get('concept-sunat', 'ConceptController@ListSunatConcept');
    Route::get('concept-sunat/{id_concepto_planilla_sunat}', 'ConceptController@ShowSunatConcept');
    Route::post('concept-sunat', 'ConceptController@AddSunatConcept');
    Route::put('concept-sunat/{id_concepto_planilla_sunat}', 'ConceptController@UpdateSunatConcept');
    Route::delete('concept-sunat/{id_concepto_planilla_sunat}', 'ConceptController@DeleteSunatConcept');

    //concepto aps
    Route::get('concept-aps', 'ConceptController@ListApsConcept');

    // concepto
    Route::get('concept', 'ConceptController@ListConcept');
    Route::get('concept-type', 'ConceptController@ListConceptByType');
    Route::get('concept/{id_concepto_planilla}', 'ConceptController@ShowConcept');
    Route::post('concept', 'ConceptController@AddConcept');
    Route::put('concept/{id_concepto_planilla}', 'ConceptController@UpdateConcept');
    Route::delete('concept/{id_concepto_planilla}', 'ConceptController@DeleteConcept');

    //asignacion de conceptos a grupo planilla

    Route::get('assign-concept-group', 'ConceptController@ListConceptPayrollGroup');
    Route::get('assign-concept-group-asisign', 'ConceptController@ListConceptPayrollGroupAssign');
    Route::post('assign-concept-group', 'ConceptController@AddConceptPayrollGroup');
    Route::delete('assign-concept-group/{id_planilla_entidad}/{id_concepto_planilla}', 'ConceptController@DeleteConceptPayrollGroup');
});

Route::group(['prefix' => 'gth/reports', 'namespace' => 'HumanTalentMgt'], function () {
    // Reports
    Route::get('reports-register-firm', 'ReportsController@reportRegisterFirm');
    Route::get('reports-salidas-mes', 'ReportsController@reportOuthinMonth');
    Route::get('calendar-holidays', 'ReportsController@calendarHolidays');
});

//Auth::routes();
//Route::get('/home', 'HomeController@index')->name('home');
//Ame del Peru


Route::group(['prefix' => 'financial', 'namespace' => 'Financial'], function () {
    Route::get('criterions-list', 'CriterionController@listOpt');
    Route::get('criterions-linean-hierarchy', 'CriterionController@criterionLinenalHerarchy');
    Route::get('criterions-list-criteries-semester', 'CriterionController@listOptCriterieSemester');
    Route::get('criterions-semester-list', 'CriterionController@getListCriteriaSemester');
    Route::get('criterions-list-criteries-afecta', 'CriterionController@getListCriteriaAfecta');
    Route::get('payment-plans', 'ProgramPaymentPlanController@listPaymentPlan');
    Route::get('program-payment-plans/{id_planpago_semestre}/details', 'ProgramPaymentPlanController@detailsPaymentPlan');
    Route::get('options', 'ProgramPaymentPlanController@options');
    Route::get('payment-plans-details', 'ProgramPaymentPlanController@paymentPlanDetails');
    Route::put('payment-plans-details/{id_planpago_semestre}', 'ProgramPaymentPlanController@updateStatusMatPlanPagoSemestre');
    Route::get('costs-criterion-student', 'CriterionSemesterCostController@detaile');


    Route::resource('payment-student-info', 'PaymentStudentInfoController');
    Route::get('prueba', 'PaymentStudentInfoController@prueba'); // prueba de descuentos en matricula
    Route::resource('criterions', 'CriterionController');
    Route::resource('teaching-level', 'TeachingLevelController');
    Route::resource('program-payment-plans', 'ProgramPaymentPlanController');

    Route::get('type-discount', 'CriterionController@getTypeDiscount');

    Route::get('plan-costs', 'PlanCostsController@planCostMain');
    Route::post('plan-costs', 'PlanCostsController@addPlanCosts');
    Route::post('plan-costs-update', 'PlanCostsController@updatePlanCosts');
    Route::post('plan-costs-massively', 'PlanCostsController@massivelyRegisterPlanCosts');


    Route::resource('criterions-semester', 'CriterionSemesterController');
    Route::resource('criterions-semester-seat', 'CriterionSemesterSeatController');

    Route::delete('seat-criterions-semester/{id}/{id_criterio_semestre}', 'CriterionSemesterSeatController@deleteseat');

    Route::get('student-balance', 'PaymentEnrollmentController@studentBalance');

    Route::post('copy-criterion', 'CriterionSemesterController@addCopyCriterioMatricula');

    Route::post('generate-contract/{id_alumno_contrato}', 'ContractEnrollmentController@generateContract');
});

Route::group(['prefix' => 'report', 'namespace' => 'Report\FinancesStudent'], function () {
    Route::get('sales/account-movements', 'SalesController@accountMovements');
    Route::get('sales/account-movements-v2', 'SalesController@accountMovementsV2');
    Route::get('payment-tracking/summary', 'ReportsController@paymentTrackingSummary');
    Route::get('payment-tracking/tracking', 'ReportsController@paymentTracking');
    Route::get('ammounts/summary-types', 'AmmountsController@ammountsSummary');
    Route::get('student-contract/{id_alumno_contrato}', 'StudentContractController@studentContract');
    // Route::get('student-contract/{id_alumno_contrato}', 'TestController@studentContract');
    Route::get('student-foto', 'StudentContractController@studentFoto');
    Route::get('student-contract-test/{id_alumno_contrato}', 'TestController@noOficial');
});


Route::group(['prefix' => 'academic', 'namespace' => 'Academic'], function () {

    Route::get('campus', 'ProgramController@listCampus');
    Route::get('semesters', 'ProgramController@listSemester');
    Route::get('tipo-requisito-beca', 'ProgramController@listRequstudentshipType');
    Route::get('teaching-levels', 'ProgramController@listTeachingLevel');
    Route::get('study-modality', 'ProgramController@studyModality');
    Route::get('contract-mode', 'ProgramController@contractMode');

    Route::get('programs-semester-criteries', 'ProgramController@listProgramsCriteriesSemester');

    Route::get('programs-semester-tree', 'ProgramController@programsSemesterTree');
    Route::get('programs-semester-contract-tree', 'ProgramController@programsSemesterContractTree');
    Route::get('plan-pago-semestre-cuota', 'ProgramController@listPlanPlagoSemestre');
    Route::get('type-contract', 'ProgramController@listTypeContract');

    Route::resource('programs', 'ProgramController');

    Route::get('plan-pago-cuota', 'ProgramController@getPlanPagoCuotaNew');
});

// Nuevo pago visa.

Route::group(['prefix' => 'visapayment', 'namespace' => 'Payonline'], function () {
    Route::get('shopping', 'VisapaymentController@shopping');
    Route::post('tokens', 'VisapaymentController@tokens')->name('visapayment/tokens');
    Route::get('tokensapp', 'VisapaymentController@tokensapp')->name('visapayment/tokensapp');
    Route::post('print', 'VisapaymentController@imprimir');
    Route::get('terminos', 'VisapaymentController@terminos');
    Route::get('apidoc', 'VisapaymentController@obtenerDocumento')->name('visapayment/apidoc');
});

// realizando prueba de cambio y performance repo git
// test route resolve add changes test , prueba resolviendo conflictos de branch

Route::group(['prefix' => 'financial-enrollment', 'namespace' => 'FinancialEnrollment'], function () {
    Route::resource('proforma-payment-student', 'ProformaPaymentStudent');
    Route::post('proforma-payment-validator', 'ProformaPaymentStudent@validationProforma');
    Route::post('print-ticket-contract', 'ProformaPaymentStudent@imprimeTicketContrato');
    Route::post('finish-contract', 'ProformaPaymentStudent@finishContract');
    Route::post('finish-contract-variation', 'ProformaPaymentStudent@finishContractVariation');
    Route::post('clean-legal-client', 'ProformaPaymentStudent@cleanLegalClient');
    Route::post('change-missionary', 'ProformaPaymentStudent@changeMissionary');
    Route::post('ticket', 'ProformaPaymentStudent@ticket');
    Route::get('criteria-semester-program-discounts/{id}', 'ProformaCriteriaSemProg@discounts');
    Route::get('programs-plan-student', 'ProformaCriteriaSemProg@programsPlanStudent');
    Route::get('check-discount-request', 'ProformaCriteriaSemProg@checkDiscountRequest');
});

Route::group(['prefix' => 'collection', 'namespace' => 'CollectionPlan'], function () {
    Route::resource('plan', 'CollectionPlanController');
    Route::post('plan-save-get', 'CollectionPlanController@createOrGetCollectionPlan');
    Route::resource('plan-indicators', 'CollectionPlanIndicatorController');
    Route::resource('client-financier', 'ClientFinancierController');
    Route::resource('financier', 'FinancierController');
    Route::get('financier-assigned', 'FinancierController@financialAssigned');
    Route::post('check-customer', 'ClientFinancierController@checkCustomer');
});


//nueva version
Route::group(['prefix' => 'visanet', 'namespace' => 'Payonline'], function () {
    Route::get('shopping', 'VisanetController@shopping');
    Route::get('expirado', 'VisanetController@expirado');
    Route::post('tokens', 'VisanetController@tokens')->name('visanet/tokens');
    //Route::get('tokensapp', 'VisanetController@tokensapp')->name('visapayment/tokensapp');
    Route::post('print', 'VisanetController@imprimir');
    Route::get('terminos', 'VisanetController@terminos');
    Route::get('apidoc', 'VisanetController@obtenerDocumento')->name('visanet/apidoc');
});

Route::group(['prefix' => 'call', 'namespace' => 'Calls'], function () {
    Route::post('call-phone', 'CallsController@callsCelphone');
    Route::post('call-response', 'Calls1Controller@callResponse');
});

Route::group(['prefix' => 'finances-student/strategys', 'namespace' => 'FinancesStudent'], function () {
    Route::post('strategy', 'StrategyController@saveStrategia');
    Route::get('strategy', 'StrategyController@listStrategia');
    Route::delete('strategy/{id_estrategia}', 'StrategyController@deleteStrategia');
    Route::put('strategy/{id_estrategia}', 'StrategyController@updateStrategia');
    Route::post('strategy/asignar', 'StrategyController@saveAsignarStrategia');
    Route::get('strategy/list-asignado', 'StrategyController@listStrategiaAsignada');
    Route::delete('strategy/asignado/{id_estrategia}', 'StrategyController@deleteStrategiaAsignada');
    Route::get('strategy/details', 'StrategyController@detailStrategiaAsignada');
    Route::put('strategy/ganador/{estado}', 'StrategyController@updateGandor');
});
// TraitRegistryRoutes::mapRegistryRoutes();
