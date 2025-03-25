<?php

namespace Financial\Routes\Setup;

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

trait TraitRegistryRoutes
{
    public static function mapRegistryRoutes()
    {
        Route::group(['prefix' => 'registry', 'namespace' => 'Setup\Person'], function () {
            Route::get('get-info', 'RegistryController@getInfoPersonas');
            Route::get('getTipoDocumento', 'RegistryController@getTipoDocumento');
            Route::get('getEstadoCivil', 'RegistryController@getEstadoCivil');
            Route::get('getTipoPais', 'RegistryController@getTipoPais');
            Route::get('getComprobateReg', 'RegistryController@getComprobateReg');
            Route::post('updatePerson', 'RegistryController@updatePerson');
            Route::post('regPersona', 'RegistryController@registrarPersona');
            Route::post('regAlumno', 'RegistryController@registrarAlumno');
            Route::post('regDocente', 'RegistryController@registrarDocente');
            Route::post('resetPassword', 'RegistryController@resetPassword');
        });
    }
}
