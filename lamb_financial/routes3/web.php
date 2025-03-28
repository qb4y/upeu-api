<?php

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


Route::get('/', function () {
    return view('welcome');
});




Route::get('socket', 'Payonline\McController@socket')->name('socket');

Route::get('exportexcel', 'HumanTalent\PaymentsController@exportexcel')->name('exportexcel');

//Route::resource('lamb','UserController');
//Route::get('pruebasDIR/{id_anho}/{mes}', 'HumanTalent\PaymentsController@directorioBoleta')->name('pruebasDIR');
//Route::get('pruebasCER', 'HumanTalent\PaymentsController@pruebas')->name('pruebasCER');
Route::get('envioRespuesta/{id_aplicacion}/{id_origen}', 'Payonline\VisaController@envioRespuesta')->name('envioRespuesta');


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

    Route::post('login_mobile', 'LoginLambController@login_mobile')->name('login_mobile');
    Route::post('user_modules', 'LoginLambController@userModules');
    Route::post('user_module_child', 'LoginLambController@userModuleChild');
    Route::post('usermodule', 'LoginLambController@userModuleOld'); // BORRAR para lamb antiguo
    Route::get('usermodule', 'LoginLambController@userModule');// para lamb nuevo
    Route::get('usermodule/{id}', 'LoginLambController@userModuleChildren');//no se usa comentarlo

    Route::post('usermodulechild', 'LoginLambController@userModuleChildren');
    Route::get('resetPassord/{email}', 'LoginLambController@resetPasswordSendMail');
    Route::get('resetPassordValida/{token}', 'LoginLambController@resetPasswordValidaToken');
    Route::put('resetPassord', 'LoginLambController@resetPassword');
//    Route::get('chulls', 'LoginLambController@prueba');
//    Route::get('chullsita/{id}', function ($id) {
//        //dd($id);
//    });
    //NUEVA CONGIFURACION PARA LISTAR
    Route::get('user-modules', 'LoginLambController@userModule')->middleware('resource:Auth/user-modules'); //LISTA DE ACCESOS
    Route::get('user-modules/{id_modulo}/actions', 'LoginLambController@userModuleActions'); //ACCIONES POR ROL Y ACCESOS
    Route::get('user-modules/{id_modulo}/children', 'LoginLambController@userModuleChildren');
    Route::post('login-oauthdj-movil', 'LoginLambController@LoginOauthdjMovil');
    Route::get('user-modules-movil', 'LoginLambController@UserModuleMenuMovil');
    Route::get('version-app/{code}', 'LoginLambController@VersionAppMovil');
    Route::get('user-info-device', 'LoginLambController@UserInfoDevice');
    Route::get('datos-persona', 'LoginLambController@DatosPersona');
    Route::post('change-password-movil', 'LoginLambController@ChangePasswordMovil');
    Route::post('logout', 'LoginLambController@LogoutSession');

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
    Route::post('user_data', 'SetupController@user_data');//desabilitar reemplazar
    Route::get('user-data', 'SetupController@userData');//Este methodo queda
    Route::get('months-entities', 'SetupController@getMonthEntity');
    Route::get('entities-enterprise', 'SetupController@listEntitiesEnterprise');
    Route::get('my-companies/{id_empresa}/entities', 'SetupController@listEntitiesEnterpriseByUser');
    Route::get('my-companies/{id_empresa}/verify-all-entities', 'SetupController@listEntitiesEnterpriseVerifyAllEntities');
    Route::get('my-companies', 'SetupController@getCompanyByUser');
    Route::get('my-companies/{id_empresa}/{id_entidad}/deptos', 'SetupController@listDeptosEntitiesByUser');
    Route::get('my-companies/{id_empresa}/{id_entidad}/verify-all-deptos', 'SetupController@listDeptosEntitiesByUserVerifyAll');

    //configuración de representante legal
    Route::get('get_tipe_doc_re', 'SetupController@listTipoDocRepre');
    Route::get('list_depto', 'SetupController@listDepto');
    Route::get('doc_representative', 'SetupController@listDocRepresentative');
    Route::post('doc_representative', 'SetupController@addDocRepresentative');
    Route::get('doc_representative/{id_entideplegal}', 'SetupController@showDocRepresentative');
    Route::post('doc_representative/{id_entideplegal}', 'SetupController@editDocRepresentative');
    Route::delete('doc_representative/{id_entideplegal}', 'SetupController@deleteDocRepresentative');
    Route::get('doc_representative_filter', 'SetupController@DocRepresentativeFilters');

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
    Route::get('modules/{id_modulo}', 'ModuloController@showModules');
    Route::post('modules', 'ModuloController@addModules');
    Route::put('modules/{id_modulo}', 'ModuloController@updateModules');
    Route::delete('modules/{id_modulo}', 'ModuloController@deleteModules');
    Route::get('modules/{id_modulo}/children', 'ModuloController@listModulesChildrens');

    Route::get('modules/{id_modulo}/actions', 'ModuloController@listModulesActions');
    Route::post('modules/{id_modulo}/actions', 'ModuloController@addModulesActions');
    Route::put('modules/{id_modulo}/actions/{id_accion}', 'ModuloController@updateModulesActions');
    Route::delete('modules/{id_modulo}/actions/{id_accion}', 'ModuloController@deleteModulesActions');
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
});

Route::group(['prefix' => 'setup', 'namespace' => 'Setup\Organization'], function () {
    Route::get('organization', 'OrganizationController@listOrganization');
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
    Route::post('areas-orders', 'OrganizationController@CreateOrUpdateAreaOrder');
    Route::get('areas', 'OrganizationController@listAreas');
    Route::get('mis-areas', 'OrganizationController@misAreas');
});

Route::group(['prefix' => 'setup', 'namespace' => 'Setup\Person'], function () {
    Route::get('reniec-persons', 'PersonController@getDataReniec');
    Route::get('sunat-persons', 'PersonController@getDataSunat');
    Route::post('natural-persons', 'PersonController@addNaturalPerson');
    Route::post('legal-persons', 'PersonController@addLegalPerson');
    Route::get('natural-persons', 'PersonController@listNaturalPersons');

    Route::get('legal-persons', 'PersonController@listLegalPersons');

    Route::get('legal-persons/and-natural-with-ruc', 'PersonController@listLegalPersonsAndNaturalWithRuc');
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
});
Route::group(['prefix' => 'accounting', 'namespace' => 'Accounting\Setup'], function () {
    Route::get('config-vouchers', 'AccountingController@listConfigVoucher');
    Route::get('config-vouchers/{id_tipoasiento}', 'AccountingController@showConfigVoucher');
    Route::post('config-vouchers', 'AccountingController@addConfigVoucher');
    Route::put('config-vouchers', 'AccountingController@updateConfigVoucher');
    Route::delete('config-vouchers/{year}/{entity}/{depto}/{id_tipoasiento}', 'AccountingController@deleteConfigVoucher');
    Route::get('tipo-asientos', 'AccountingController@listTipoAsiento');
    Route::get('tipo-asientos-voucher', 'AccountingController@showTipoAsientoVoucher');
    Route::get('depto-parents/{entity}', 'AccountingController@listDeptoParent');
    Route::get('tipo-comprobantes', 'AccountingController@listTipoComprobante');
    Route::get('tipo-plan', 'AccountingController@listTipoPlan');

    Route::get('type-vouchers', 'AccountingController@listTypeVoucher');
    Route::get('type-money', 'AccountingController@listTypeMoney');
    Route::get('type-restriction', 'AccountingContromy-bank-accountsller@listTypeRestriction');
    Route::get('type-igv', 'AccountingController@listTypeIGV');


    Route::get('config-periodos-status', 'AccountingController@showPeriodos');
    Route::post('config-periodos', 'AccountingController@addPeriodos');
    Route::get('config-periodos', 'AccountingController@listPeriodosMeses');
    Route::put('config-periodos', 'AccountingController@updatePeriodoMes');
    Route::put('config-periodos-close', 'AccountingControlfler@updatePeriodoClose');

    Route::get('automatic-vouchers', 'AccountingController@validarVoucherAutomatico');
    Route::get('vouchers', 'AccountingController@listVoucher');
    Route::get('my-vouchers', 'AccountingController@listMyVoucher');
    Route::delete('my-vouchers/{id_voucher}', 'AccountingController@deleteMyVoucher');
    Route::get('vouchers/{id_voucher}', 'AccountingController@showVoucher');
    Route::post('vouchers', 'AccountingController@addVoucher');
    Route::patch('vouchers/{id_voucher}', 'AccountingController@updateVoucher');
    Route::get('reports/vouchers', 'AccountingController@listVoucherModules');
    Route::get('reports/my-vouchers', 'AccountingController@listMyVoucherModules');
    Route::post('vouchers-purchases', 'AccountingController@addVoucherPurchases');
    Route::get('aasinet-my-vouchers', 'AccountingController@listVoucherModulesAasinet');

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

    Route::get('plan-accounting-enterprise', 'AccountingController@planAccountingEnterprise');
    Route::get('plan-accounting-enterprise/{id_cuentaempresarial}', 'AccountingController@showPlanAccountingEnterprise');
    Route::post('plan-accounting-enterprise', 'AccountingController@addPlanAccountingEnterprise');
    Route::put('plan-accounting-enterprise/{id_cuentaempresarial}', 'AccountingController@updatePlanAccountingEnterprise');
    Route::delete('plan-accounting-enterprise/{id_cuentaempresarial}', 'AccountingController@deletePlanAccountingEnterprise');
    Route::get('plan-accounting-denominational-search', 'AccountingController@listPlanAccountingDenominationalSearch');
    Route::get('plan-accounting-denominational/{id_tipoplan}', 'AccountingController@listPlanAccountingDenominational');

    Route::get('plan-accounting-equivalent/{id_cuentaempresarial}', 'AccountingController@showPlanAccountingEquivalent');
    Route::post('plan-accounting-equivalent', 'AccountingController@addPlanAccountingEquivalent');
    Route::put('plan-accounting-equivalent/{id_cuentaempresarial}', 'AccountingController@updatePlanAccountingEquivalent');

    Route::get('accounting-entry', 'AccountingController@listAccountingEntry');

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

    Route::get('tipo-cambio', 'AccountingController@listTipoCambio');
    Route::put('tipo-cambio/{anho}/{mes}', 'AccountingController@TipoCambio');
    Route::get('tipo-cambio/{fecha}', 'AccountingController@showTipoCambio');
    Route::put('tipo-cambio/{fecha}', 'AccountingController@updateTipoCambio');
    Route::get('indicador-lista', 'AccountingController@listIndicador');

    Route::get('asiento-depto-lista', 'AccountingController@listDeptoAsientoAccounting');
    Route::get('asiento-ctacte-lista', 'AccountingController@listCtaCteAsientoAccounting');

    Route::get('ctacte-accounting', 'AccountingController@listCtaCteAccounting');
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
    Route::post('group-account-details', 'AccountingController@addGroupAccountDetails');
    //Route::get('ctacte-accounting-group', 'AccountingController@listCtaCteAccountingGroup');
    Route::get('group-account-details', 'AccountingController@listGroupAccountDetails');
    Route::get('group-account-details-cte', 'AccountingController@listGroupAccountCTE');
    Route::delete('group-account-details/{id_cdetalle}', 'AccountingController@deleteGroupAccountDetails');

    Route::get('type-arrangements', 'AccountingController@listTypeArrangements');
    Route::get('arrangements', 'AccountingController@listArrangements');
    Route::post('arrangements', 'AccountingController@addArrangements');

    Route::get('external-system', 'AccountingController@listExternalSystem');
    Route::get('external-system/{id_sistemaexterno}', 'AccountingController@showExternalSystem');
    Route::post('external-system', 'AccountingController@addExternalSystem');
    Route::put('external-system/{id_sistemaexterno}', 'AccountingController@updateExternalSystem');
    Route::delete('external-system/{id_sistemaexterno}', 'AccountingController@deleteExternalSystem');
    Route::get('external-system-seat', 'AccountingController@listExternalSystemSeat');
    //ASINET
    Route::get('seat-aasinet', 'AccountingController@listSeatAaasinet');
    Route::post('seat-aasinet-upload', 'AccountingController@uploadSeatAaasinet');// se ha Cambiado a Post -  Para Mostrar los Mensajes

    Route::post('current-account-aasinet', 'AccountingController@createCurrentAccountAaasinet');

    Route::get('validate-vouchers', 'AccountingController@validateVoucher');

    //Listar Asitento
    Route::get('accounting-seat', 'AccountingController@listAccountingSeat');
    Route::get('accounting-seat/{id_asiento}', 'AccountingController@showAccountingSeat');
    Route::post('accounting-seat', 'AccountingController@addAccountingSeat');
    Route::put('accounting-seat/{id_asiento}', 'AccountingController@updateAccountingSeat');
    Route::delete('accounting-seat/{id_asiento}', 'AccountingController@deleteAccountingSeat');

});
Route::group(['prefix' => 'accounting/operations', 'namespace' => 'Accounting\Setup'], function () {
    Route::get('accounting-entry', 'AccountingController@listAccountingEntryModule');
    //Route::get('accounting-entry', 'AccountingController@listAccountingEntryModule');
    Route::post('vouchers-persons', 'AccountingController@assignVouchers');
    Route::get('vouchers-users', 'AccountingController@showVoucherModules');
});
Route::group(['prefix' => 'sales', 'namespace' => 'Sales'], function () {
    Route::post('article', 'ArticleController@showArticle');

    Route::get('natural-legal-person', 'SalesController@listPerson');
    Route::post('sales', 'SalesController@addSales');
    Route::get('sales/{id_venta}/details', 'SalesController@listSalesDetails');
    Route::post('sales/{id_venta}/details/', 'SalesController@addSalesDetails');
    Route::get('type-sales', 'SalesController@listTypeSales');
    Route::delete('sales/{id_venta}/details/{id_vdetalle}', 'SalesController@deleteSalesDetails');
    Route::put('sales/{id_venta}/details/{id_vdetalle}', 'SalesController@updateSalesDetails');
    Route::get('sales/{id_venta}', 'SalesController@showSales');
    Route::put('sales/{id_venta}', 'SalesController@updateSales');
    Route::get('natural-legal-person/{id_persona}', 'SalesController@listPersonSucursal');
    Route::delete('sales/{id_venta}/details', 'SalesController@deleteSalesDetailsAll');

    Route::get('type-transactions', 'SalesController@listTypeTransaccion');

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
    //CATEGORIAS DE CLIENTES
    Route::get('warehouses-politics-persons', 'PoliticsController@listPoliticsPersons');
    Route::post('warehouses-politics-persons', 'PoliticsController@addPoliticsPersons');
    Route::patch('warehouses-politics-persons', 'PoliticsController@updatePoliticsPersons');
    Route::put('warehouses-politics-persons/{id_politica}', 'PoliticsController@updatePoliticsPersonsAll');
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
    Route::get('my-notes', 'SalesController@listMyNotes');

    Route::get('transfers-imports', 'SalesController@listTransfersImports');
    Route::post('transfers-imports', 'SalesController@addTransfersImports');
    Route::put('transfers-imports', 'SalesController@updateTransfersImports');
    Route::delete('transfers-imports', 'SalesController@deleteTransfersImports');
    // cambios
    Route::get('transfers-imports-test', 'SalesController@listTransfersImports');

    //Notas de Credito / Debito
    Route::get('types-notes', 'SalesController@listTypesNotes');
    Route::post('notes', 'SalesController@addNotes');
    Route::post('notes/{id_nota}/details', 'SalesController@addNotasDetails');
    Route::put('notes/{id_nota}', 'SalesController@updateNotes');
    Route::get('notes/{id_nota}/details', 'SalesController@listNotasDetails');
    Route::delete('notes/{id_nota}/details/{id_vdetalle}', 'SalesController@deleteNotesDetails');
});
Route::group(['prefix' => 'sales/reports', 'namespace' => 'Sales'], function () {
    Route::get('sales', 'SalesController@salesBalances');
    Route::get('sales-record', 'SalesController@salesRecord');
    Route::get('sales-accounting-entry', 'SalesController@salesAccountingEntry');
    Route::get('sales-details', 'SalesController@salesDetails');
});
Route::group(['prefix' => 'services', 'namespace' => 'Services'], function () {
    Route::get('tipo-cambio/{anho?}/{mes?}', 'ServicesController@TipoCambio');
    Route::post('upload-images', 'ServicesController@uploadImage');
});
Route::group(['prefix' => 'treasury', 'namespace' => 'Treasury'], function () {
    //Route::get('deposit', 'BoxController@listPerson');
    //Route::post('deposit', 'BoxController@addSales');
    //Route::delete('sales/{id_venta}/details/{id_vdetalle}', 'SalesController@deleteSalesDetails');
    //Route::put('sales/{id_venta}/details/{id_vdetalle}', 'SalesController@updateSalesDetails');
    // Pagos varios
    Route::get('payments-vouchers', 'ExpensesController@listPaymentsVoucher');
    Route::get('payments-vouchers/to-vales', 'ExpensesController@listPaymentsVoucherToVales');
    Route::get('payments', 'ExpensesController@listPayments');
    Route::get('payments/to-vales', 'ExpensesController@listPaymentsToVales'); // Esto fué echo por la UPN by @vitmaraliaga
    Route::get('payments/{id_pago}/details', 'ExpensesController@listPaymentsDetails');
    Route::get('payments/{id_vale}/vale', 'ExpensesController@listPaymentsVale');
    Route::delete('payments/{id_pago}/details/{id_detalle}', 'ExpensesController@deletePaymentsDetails');

    Route::get('payments/{id_pago}', 'ExpensesController@showPayments');
    Route::post('payments', 'ExpensesController@addPayments');
    Route::put('payments/{id_pago}', 'ExpensesController@updatePayments');
    Route::delete('payments/{id_pago}', 'ExpensesController@deletePayments');
    Route::post('payments-expenses', 'ExpensesController@addPaymentsExpenses');
    Route::post('payments-expenses/upn', 'ExpensesController@addPaymentsExpensesUPN');
    Route::post('payments-providers', 'ExpensesController@addPaymentsProviders');
    Route::post('payments-providers/many', 'ExpensesController@addPaymentsProvidersMany');
    Route::post('payments-customers', 'ExpensesController@addPaymentsCustomers');
    Route::post('payments-expenses-seats/{id_pgasto}', 'ExpensesController@addPaymentsExpensesSeats');

    // Rendicion de vale como pago.
    Route::put('payments/{id_pago}/finalizar-rendicion-vale-upn', 'ExpensesController@finalizarRendicionValeUpn');


    Route::get('way-pay','IncomeController@listMedioPago')->middleware('resource:treasury/way-pay');
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

    Route::get('card-type', 'IncomeController@listCardType');
    Route::get('deposit-type', 'IncomeController@listDepositType');
    Route::post('deposit', 'IncomeController@addDeposit');
    Route::get('deposit', 'IncomeController@cashRegister');

    //Route::get('my-bank-accounts-', 'TreasuryController@listMyBankAccounts');

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
    Route::put('my-vales/{id_vale}/provision', 'ExpensesController@provisionarMyVale');
    Route::get('my-vales/cliente', 'ExpensesController@listValesCliente');
    Route::get('vales-proceso', 'ExpensesController@listValesProceso');
    Route::put('vales/{id_vale}', 'ExpensesController@autorizaVale');
    Route::patch('vales/{id_vale}', 'ExpensesController@autorizaVale');

    Route::post('vales/{id_vale}/pagar', 'ExpensesController@payVale');

    Route::get('vales/{id_vale}/deposits', 'ExpensesController@listValesDeposits');

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

    // execute process run

    Route::post('execute-operation-runprocess', 'ExpensesController@executeOperationProcessRun');

    Route::get('account-plan', 'ExpensesController@listAccountingPlan');
    Route::get('current-account', 'ExpensesController@listCurrentAccount');
    Route::get('department-account', 'ExpensesController@listDepartmentAccount');
    Route::get('deposits-imports', 'IncomeController@listDepositImports');
    Route::post('deposits-imports', 'IncomeController@addDepositImports');
    Route::put('deposits-imports', 'IncomeController@addDepositImportsFinish');

    //deductions
    Route::get('types-goods-services', 'ExpensesController@listTypesGoodsServices');
    Route::get('types-detraction-operations', 'ExpensesController@listTypesDetractionOperations');
    Route::get('deductions', 'ExpensesController@listDeductions');
    Route::post('deductions', 'ExpensesController@addDeductions');
    Route::get('deductions-summary', 'ExpensesController@listDeductionsSummary');

    //retentions
    Route::get('retentions-summary', 'ExpensesController@listRetentionsSummary');
    Route::get('retentions', 'ExpensesController@listRetentions');

    Route::post('retentions', 'ExpensesController@addRetentions');
    Route::post('retentions/by-upn', 'ExpensesController@addRetentionsByUPN'); // by @vitmar
    // Route::put('retentions/{id_retencion}/by-upn', 'ExpensesController@addRetentionsByUPN'); // by @vitmar

    Route::put('retentions/{id_retencion}', 'ExpensesController@updateRetentions');
    Route::get('retentions/{id_retencion}', 'ExpensesController@getRetentionById');
    Route::get('retentions/{id_retencion}/purchases', 'ExpensesController@listRetentionsPurchases');
    Route::post('retentions/{id_retencion}/purchases', 'ExpensesController@addRetentionsPurchases');
    Route::delete('retentions/{id_retencion}/purchases/{id_retdetalle}', 'ExpensesController@deleteRetentionsPurchases');
    //Depositos al Banco
    Route::get('bank-deposits', 'ExpensesController@listBankDeposits');
    Route::post('bank-deposits', 'ExpensesController@addBankDeposits');

    Route::get('box-deposits/to-vales', 'ExpensesController@listDepositsToValesUPN');
    Route::delete('box-deposits/{id_deposito}', 'ExpensesController@deleteDepositsToValesUPN');


    Route::get('exchange-rate-payment', 'ExpensesController@showExchangeRatePayment');

});

Route::group(['prefix' => 'inventories', 'namespace' => 'Inventories'], function () {
    Route::get('show_article', 'ArticleController@listArticle');

    Route::post('article', 'ArticleController@showArticle');

    Route::get('warehouses', 'WarehousesController@listWarehouses');
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

    Route::get('measurement-units', 'WarehousesController@listMeasurementUnits');
    Route::get('measurement-units/{id_unidadmedida}', 'WarehousesController@showMeasurementUnits');
    Route::post('measurement-units', 'WarehousesController@addMeasurementUnits');
    Route::put('measurement-units/{id_unidadmedida}', 'WarehousesController@updateMeasurementUnits');
    Route::delete('measurement-units/{id_unidadmedida}', 'WarehousesController@deleteMeasurementUnits');

    Route::get('articles', 'WarehousesController@listArticles');
    Route::get('articles/{id_articulo}/children', 'WarehousesController@listArticlesChildren');
    Route::get('articles/{id_articulo}', 'WarehousesController@showArticles');
    Route::post('articles', 'WarehousesController@addArticles');
    Route::post('articles-upload', 'WarehousesController@addArticlesUpload');
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

});

Route::group(['prefix'=>'purchases','namespace'=>'Purchases'],function() {
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
    Route::get('order-all-detail/{id_pedido}', 'PurchasesController@getAllOrderDetail');


    Route::get('orders-purchases', 'PurchasesController@listOrdersPurchases');
    Route::get('orders-purchases/{id_pcompra}', 'PurchasesController@showOrdersPurchases');
    Route::post('orders-purchases', 'PurchasesController@addOrdersPurchases');
    Route::delete('orders-purchases/{id_pcompra}', 'PurchasesController@deleteOrdersPurchases');

    // Route::get('purchases', 'PurchasesController@listPurchasesOrders');
    Route::get('purchases/{id_compra}', 'PurchasesController@showPurchases');
    Route::post('purchases', 'PurchasesController@addPurchases');
    // Route::delete('purchases-orders/{id_orden}', 'PurchasesController@deletePurchasesOrders');
    // Route::post('provisions/{id_pedido}/finalizer', 'PurchasesController@addFinalizer');
    // Route::patch('purchases-finalizer', 'PurchasesController@endPurchases');
    Route::patch('purchases-end/{id_compra}', 'PurchasesController@execPurchasesEnd');
    // Route::post('provisions/voucher/{id_compra}/accounting-seat-generate', 'PurchasesController@addAccountingSeatGenerate');
    // Route::post('purchases-seat-generate', 'PurchasesController@execPurchasesSeatGenerate');

    Route::get('purchases-details', 'PurchasesController@listPurchasesDetails');
    // Route::get('purchases-details/{id_compra}', 'PurchasesController@showPurchases');
    Route::post('purchases-details', 'PurchasesController@addPurchasesDetails');
    // Route::delete('purchases-orders/{id_orden}', 'PurchasesController@deletePurchasesOrders');
    Route::delete('purchases-details/{id_detalle}', 'PurchasesController@deletePurchasesDetails');
    Route::put('purchases-details', 'PurchasesController@putPurchasesDetails');
    Route::patch('purchases-details/{id_detalle}', 'PurchasesController@patchPurchasesDetails');
    Route::post('purchases/{id_compra}/details', 'PurchasesController@addPurchasesDetailsGenerate');

    Route::get('purchases/{id_compra}/seats-acounting', 'PurchasesController@listPurchasesSeatsAcounting');

    Route::get('purchases-typeigv', 'PurchasesController@listPurchasesTypeigv');

    Route::get('purchases-seats', 'PurchasesController@listPurchasesSeats');
    Route::post('purchases-seats', 'PurchasesController@addPurchasesSeats');
    // Route::put('provisions/voucher/{id_compra}/accounting-seat/{id_casiento}', 'PurchasesController@updateAccountingSeat');
    Route::delete('purchases-seats/{id_pasiento}', 'PurchasesController@deletePurchasesSeats');
    //Route::post('purchases-seats-generate', 'PurchasesController@execPurchasesSeatsGenerate'); // OK Jose
    Route::post('purchases-seats-generate', 'PurchasesController@addSeatsPurchases'); // New Marlo
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
    Route::put('purchases-orders-details/{id_odetalle}', 'PurchasesController@updatePurchasesOrdersDetails');
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

    //voucher Automaitoc
    Route::get('automatic-vouchers', 'PurchasesController@showVoucherAutomatico');
});
Route::group(['prefix'=>'purchases/setup','namespace'=>'Purchases\Setup'],function() {
    Route::get('types-templates', 'SetupController@listTypesTemplates');
    Route::get('types-templates/{id_tipoplantilla}', 'SetupController@showTypesTemplates');
    Route::post('types-templates', 'SetupController@addTypesTemplates');
    Route::put('types-templates/{id_tipoplantilla}', 'SetupController@updateTypesTemplates');
    Route::delete('types-templates/{id_tipoplantilla}', 'SetupController@deleteTypesTemplates');

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
});



Route::group(['prefix'=>'gth','namespace'=>'HumanTalent'],function() {
   Route::get('validardocumento', 'SignatureControlle@validarview');
   Route::post('validardocumento', 'SignatureControlle@validarDocumento')->name('gth/validardocumento');
});




Route::group(['prefix'=>'humantalent','namespace'=>'HumanTalent'],function() {


    Route::get('process-tickets', 'PaymentsController@listProcessTicket');
    Route::post('process-tickets', 'PaymentsController@generatePaymentsTickets');
    Route::post('process-tickets-notice', 'PaymentsController@sendEmail');
    Route::get('process-tickets-persona', 'SignatureControlle@personacertificado');


    Route::get('process-tickets-pdfweb', 'ServiceapiController@previapdfweb');
    Route::get('process-tickets-lstprevia', 'ServiceapiController@listaPrevia');



    Route::post('process-tickets/copy', 'PaymentsController@linkBoletaPDF');
    Route::post('process-tickets/delete', 'PaymentsController@unlinkBoletaPDF');

    Route::get('payments-tracings', 'PaymentsController@listPaymentTracing');
    Route::delete('payments-tracings/{id}', 'PaymentsController@deletePaymentTicket');

    Route::get('payments-process', 'PaymentsController@listProcesos');

    Route::get('payments-tickets-worker-anhos', 'PaymentsController@anhoPayments');
    Route::get('payments-tickets-worker', 'PaymentsController@listPaymentTicket');
    Route::post('payments-tickets-worker-show', 'PaymentsController@showBoletaPDF');
    Route::put('payments-tickets-worker/{clave}', 'PaymentsController@updateBoletaPDF');
    Route::get('payments-tickets-worker-download', 'PaymentsController@downloadBoletaPDF');
    Route::get('payments-tickets-worker-display', 'PaymentsController@displayBoletaPDF');
    Route::post('payments-tickets-worker-email', 'PaymentsController@emailBoletaPDF');





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
    Route::get('employee-file','ReporteController@fichapersonadni')->middleware('resource:humantalent/employee-file');


    Route::get('statement-afpnet', 'ReporteController@afpnet');
    Route::get('statement-afpnet-pdf', 'ReporteController@getPdfAFPNET');
    Route::get('statement-taxdistribution', 'ReporteController@taxdistribution');
    Route::get('statement-taxdistribution-pdf', 'ReporteController@getPdfTaxDistribution');


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

    //Route::get('empleado-by-entity', 'ReporteController@getEmpleadoByEntity');

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

    Route::get('admission-prices','CWController@precioAdmision')->middleware('resource:cw/admission-prices');
    Route::get('thesis-prices','CWController@thesisAdmision')->middleware('resource:cw/thesis-prices');
    Route::get('students-epg','CWController@showStudentsEPG')->middleware('resource:cw/students-epg');
    Route::get('students-semipresencial','CWController@showStudentsSemi')->middleware('resource:cw/students-semipresencial');
    Route::get('students','CWController@showStudents')->middleware('resource:cw/students');
    //datos para egresados
    Route::get('datos-egresados','CWController@showDNIPerson')->middleware('resource:cw/datos-egresados');
    Route::get('types-assistance','CWController@listTypesAssistance');
    Route::get('my-assists','CWController@showMyAssists');
    //Registrar Datos y Obetener Datos
    Route::post('registrar-datos','CWController@addRegistroDatos')->middleware('resource:cw/registrar-datos');


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
    Route::post('persons_year', 'OptionsController@persons_year');
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
    Route::get('my-events-activities-search', 'BudgetController@listMyEventsActivitiesSearch');


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
Route::group(['prefix'=>'interconexion','namespace'=>'Payonline'],function() {
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

Route::group(['prefix'=>'aps','namespace'=>'APS'],function() {

    Route::get('test', 'APSController@test');
});

Route::group(['prefix'=>'orders','namespace'=>'Orders'],function() {
    Route::get('orders-areas', 'OrdersController@listOrdersAreas');
    Route::post('orders', 'APSController@addOrders');
    Route::put('orders/{id_pedido}', 'OrdersController@updateOrders');
    Route::get('orders-articles', 'OrdersController@listWarehousesArticles');
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
});

Route::group(['prefix'=>'orders/reports','namespace'=>'Orders'],function() {
    Route::get('report-areas', 'OrdersController@listReportsAreas');
    Route::get('report-my-orders', 'OrdersController@genereReportMyOrders');
    Route::get('report-area-order', 'OrdersController@listOrderArea');
    Route::get('report-attentionorders-area', 'OrdersController@listOrdersByAreaDestino');

    Route::get('orders-imports', 'OrdersController@listOrdersDash');
});

Route::group(['prefix'=>'schools','namespace'=>'Schools'],function() {
    // Route::get('persons', 'SchoolsController@listPersons');
    // Route::get('persons/{id_persona}', 'SchoolsController@showPersons');
    // Route::post('persons', 'SchoolsController@addPersons');
    // Route::put('persons/{id_persona}', 'SchoolsController@updatePersons');
    // Route::delete('persons/{id_persona}', 'SchoolsController@deletePersons');
    // Route::patch('persons/{id_persona}', 'SchoolsController@updatePersons');

    Route::get('students/responsibles/proformas', 'SchoolsController@listSRProformas');

    // Route::get('students-manager', 'SchoolsController@listStudentsManager');// BORRAR CASCADA(funcion listStudentsManager)
    Route::get('employees', 'SchoolsController@listEmployees');

    Route::get('persons-manager', 'SchoolsController@listPersonsManager');
    Route::get('persons-manager-search', 'SchoolsController@listPersonsManagerSearch');
    Route::get('persons-manager/{id_persona}', 'SchoolsController@showPersonsManager');
    Route::post('persons-manager', 'SchoolsController@addPersonsManager');
    Route::put('persons-manager/{id_persona}', 'SchoolsController@editPersonsManager');

    Route::get('proformas-by-manager', 'SchoolsController@listProformasByManager');
    Route::get('proformas-report/{id_proforma}', 'SchoolsController@showProformasReport');
    Route::post('proformas', 'SchoolsController@addProformas');
    Route::delete('proformas/{id_proforma}', 'SchoolsController@deleteProformas');

    Route::get('proformas-details', 'SchoolsController@listProformasDetails');
    Route::post('proformas-details', 'SchoolsController@addProformasDetails');
    Route::delete('proformas-details/{id_pdetalle}', 'SchoolsController@deleteProformasDetails');

    Route::get('reservations', 'SchoolsController@listReservations');// ???
    Route::post('reservations', 'SchoolsController@addReservations');
    Route::delete('reservations/{id_reserva}', 'SchoolsController@deleteReservations');

    Route::get('responsibles', 'SchoolsController@listResponsibles');// <----
    Route::post('responsibles', 'SchoolsController@addResponsibles');
    Route::delete('responsibles/{id_responsable}', 'SchoolsController@deleteResponsibles');

    Route::get('schedules-meet', 'SchoolsController@listSchedulesMeet');// ???2
    Route::post('schedules-meet', 'SchoolsController@addSchedulesMeet');
    Route::delete('schedules-meet/{id_hcita}', 'SchoolsController@deleteSchedulesMeet');

    Route::get('meets', 'SchoolsController@listMeets');// ???3
    Route::post('meets', 'SchoolsController@addMeets');
    Route::delete('meets/{id_cita}', 'SchoolsController@deleteMeets');

    Route::get('agreements', 'SchoolsController@listAgreements');// ???4
    Route::post('agreements', 'SchoolsController@addAgreements');
    Route::delete('agreements/{id_acuerdo}', 'SchoolsController@deleteAgreements');

    Route::get('config-stage', 'SchoolsController@listConfigStage');

    Route::get('stage-grade', 'SchoolsController@listStageGrade');

    Route::get('criterions-typesdiscount', 'SchoolsController@listCriterionsTypesdiscount');
    Route::get('criterions-typespay', 'SchoolsController@listCriterionsTypespay');

    Route::get('types-pay-in-period', 'SchoolsController@listTypesPayInPeriod');

    Route::get('types-phone', 'SchoolsController@listTypesPhone');
    Route::get('types-address', 'SchoolsController@listTypesAddress');
    Route::get('types-virtual', 'SchoolsController@listTypesVirtual');
});
Route::group(['prefix'=>'schools/setup','namespace'=>'Schools\Setup'],function() {
    Route::get('stages', 'SetupController@listStages');
    Route::get('stages/{id_nivel}', 'SetupController@showStages');
    Route::post('stages', 'SetupController@addStages');
    Route::put('stages/{id_nivel}', 'SetupController@updateStages');

    Route::get('grades', 'SetupController@listGrades');
    Route::get('grades/{id_grado}', 'SetupController@showGrades');
    Route::post('grades', 'SetupController@addGrades');
    Route::put('grades/{id_grado}', 'SetupController@updateGrades');

    Route::get('typediscounts', 'SetupController@listTypeDiscounts');
    Route::get('typediscounts/{id_tipodescuento}', 'SetupController@showTypeDiscounts');
    Route::post('typediscounts', 'SetupController@addTypeDiscounts');
    Route::put('typediscounts/{id_tipodescuento}', 'SetupController@updateTypeDiscounts');

    Route::get('typepayments', 'SetupController@listTypePayments');
    Route::get('typepayments/{id_tipopago}', 'SetupController@showTypePayments');
    Route::post('typepayments', 'SetupController@addTypePayments');
    Route::put('typepayments/{id_tipopago}', 'SetupController@updateTypePayments');

    Route::get('configs', 'SetupController@listConfigs');
    Route::get('configs/{id_config}', 'SetupController@showConfigs');
    Route::post('configs', 'SetupController@addConfigs');
    Route::put('configs/{id_config}', 'SetupController@updateConfigs');

    // Route::get('stages-grades', 'SchoolsController@listStageGrade');
    Route::get('stages-grades', 'SetupController@listStagesGrades');
    Route::get('stages-grades/{id_ngrado}', 'SetupController@showStagesGrades');
    Route::post('stages-grades', 'SetupController@addStagesGrades');
    Route::put('stages-grades/{id_ngrado}', 'SetupController@updateStagesGrades');

    Route::get('vacants', 'SetupController@listVacants');
    Route::get('vacants/{id_vacante}', 'SetupController@showVacants');
    Route::post('vacants', 'SetupController@addVacants');
    Route::put('vacants/{id_vacante}', 'SetupController@updateVacants');

    Route::get('criterions', 'SetupController@listCriterions');
    Route::get('criterions/{id_criterio}', 'SetupController@showCriterions');
    Route::post('criterions', 'SetupController@addCriterions');
    Route::put('criterions/{id_criterio}', 'SetupController@updateCriterions');
});

Route::group(['prefix' => 'finances-student', 'namespace' => 'FinancesStudent'], function () {
    Route::get('students', 'StudentsController@listStudents');
    Route::get('students/{codigo}', 'StudentsController@showStudents');
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
    Route::post('profile-position', 'ConfigurationController@AddProfilePosition');
    Route::get('profile-position', 'ConfigurationController@ListProfilePositions');
    Route::delete('profile-position/{id_perfil_puesto}', 'ConfigurationController@DeleteProfilePositions');
    Route::get('profile-position/{id_perfil_puesto}', 'ConfigurationController@ShowListProfilePositions');

    Route::put('profile-position/{id_perfil_puesto}', 'ConfigurationController@updateSituationEducation');

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



});

Route::group(['prefix' => 'gth/payroll', 'namespace' => 'HumanTalentMgt'], function () {

    //escala salarial
    Route::get('salary-scale', 'PayrollController@ListSalaryScale');
    Route::post('salary-scale', 'PayrollController@AddSalaryScale');
    Route::put('salary-scale/{id_salary_scale}', 'PayrollController@UpdateSalaryScale');
    Route::delete('salary-scale/{id_salary_scale}', 'PayrollController@DeleteSalaryScale');




});
//Auth::routes();
//Route::get('/home', 'HomeController@index')->name('home');
//Ame del Peru
?>


