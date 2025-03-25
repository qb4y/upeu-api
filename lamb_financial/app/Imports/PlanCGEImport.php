<?php

namespace App\Imports;


// use Illuminate\Support\Collection;
// use Maatwebsite\Excel\Concerns\ToCollection;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\Importable;
// use Illuminate\Support\Facades\Validator;
// use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
// use Illuminate\Support\Facades\DB;
// use App\Http\Data\EntidadConfig;
// use App\Models\Eliseo\CajaPago as EliseoCajaPago;
// use App\Models\Eliseo\CajaPagoGasto as EliseoCajaPagoGasto;
// use App\Http\Data\Treasure\CouponData;
// use App\Models\Eliseo\CajaPagoGastoAsiento as EliseoCajaPagoGastoAsiento;
// use Exception;

// class PlanCGEImport implements ToCollection, SkipsEmptyRows, WithHeadingRow
// {
    // use Importable;
    // public $id_pago;

    // public function __construct($id_pago)
    // {
    //     $this->id_pago = $id_pago;
    // }
    // /**
    // * @param Collection $collection
    // */
    // public function collection(Collection $collection)
    // {
    //     // Validar opcion
    //     $validator = Validator::make($collection->toArray(), [
    //         '*.opcion' => 'required',
    //     ]);
    //     if ($validator->fails())
    //     {
    //         throw new Exception($validator->messages()->first(), 1);
    //     }
        
    //     $cajaPagoGasto = null;
    //     foreach ($collection->toArray() as $value) {
    //         if($value['opcion'] === 'operacion') {
                
    //             // $validator = Validator::make($collection->toArray(), [
    //             $validator = Validator::make($value, [
    //                 'fecha' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
    //                 'importe_total' => 'required',
    //                 'detalle' => 'required',
    //             ]);
    //             if ($validator->fails())
    //             {
    //                 throw new Exception($validator->messages()->first(), 1);
    //             }

    //             $cajaPago = EliseoCajaPago::find($this->id_pago);
    //             if($cajaPago->estado === '1') {
    //                 throw new Exception("Alto! El registro ya fue finalizado.", 1);
    //             }
    //             $cogImporte = EntidadConfig::getImporte($cajaPago->id_moneda,
    //                                 strtotime($cajaPago->fecha),
    //                                 $value['importe_total'], 'venta');
    //             $validated['id_moneda'] = $cajaPago->id_moneda;
    //             $validated['tipocambio'] = $cajaPago->tipocambio;
    //             $validated['importe'] = $cogImporte->importe;
    //             $validated['importe_me'] = $cogImporte->importeMe;
    //             $validated['id_pago'] = $this->id_pago;
    //             $validated['fecha'] = $value['fecha'];
    //             $validated['detalle'] = $value['detalle'];
    //             $cajaPagoGasto = EliseoCajaPagoGasto::create($validated);

    //         } else if($value['opcion'] === 'asiento') {
    //             if($cajaPagoGasto) {
    //                 $validator = Validator::make($value, [
    //                     'nro_asiento' => 'required',
    //                     'cuenta' => 'required',
    //                     // 'cta_cte' => 'required',
    //                     'cta_cte' => 'nullable',
    //                     'fondo' => 'required',
    //                     'depto' => 'required',
    //                     'restriccion' => 'required',
    //                     'importe' => 'required',
    //                     'importe_me' => 'required',
    //                     'dc' => 'required',
    //                     'glosa' => 'required',
    //                 ]);
    //                 if ($validator->fails())
    //                 {
    //                     throw new Exception($validator->messages()->first(), 1);
    //                 }

    //                 $id_cuentaaasi = trim($value['cuenta']);
    //                 $id_cuentaaasi = trim($id_cuentaaasi, "\xC2\xA0\n");
    //                 $id_ctacte = trim($value['cta_cte']);
    //                 $id_ctacte = trim($id_ctacte, "\xC2\xA0\n");
    //                 // $id_cuentaaasi = '4121001';
    //                 $validaCta = CouponData::validateRequiereCtaCte($id_cuentaaasi);
                    
    //                 if($validaCta->valida === 'S'){
    //                     if (is_null($id_ctacte) || (trim($id_ctacte) == '')){
    //                         throw new Exception($validaCta->msm, 1);
    //                     }
    //                 }
    //                 if($validaCta->valida === 'N' && trim($id_ctacte) != '') {
    //                     throw new Exception($validaCta->msm, 1);
    //                 }    
                    
    //                 $ordenAsiento = DB::table('eliseo.CAJA_PAGO_GASTO_ASIENTO')
    //                         ->select('orden')  
    //                         ->where('id_pgasto', $cajaPagoGasto->id_pgasto)
    //                         ->where('nro_asiento', $value['nro_asiento'])
    //                         ->max('orden');
    //                 $ordenAsiento = $ordenAsiento ?? 0;
    //                 $ordenAsiento = $ordenAsiento + 1;
                    
    //                 $data['id_pgasto'] = $cajaPagoGasto->id_pgasto;
    //                 $data['id_cuentaaasi'] = $id_cuentaaasi;
    //                 $data['id_ctacte'] = $id_ctacte;
    //                 $data['id_fondo'] = $value['fondo'];
    //                 $data['id_depto'] = $value['depto'];
    //                 $data['id_restriccion'] = $value['restriccion'];
    //                 $data['importe'] = $value['importe'];
    //                 $data['importe_me'] = $value['importe_me'];
    //                 $data['dc'] = $value['dc'];
    //                 $data['descripcion'] = $value['glosa'];
    //                 $data['nro_asiento'] = $value['nro_asiento'];
    //                 $data['orden'] = $ordenAsiento;
    //                 // $data['importe_me'] = PagoGastoController::getImporteMe($cajaPagoGasto,$value['importe']);
    //                 $CajaPagoGastoAsiento = EliseoCajaPagoGastoAsiento::create($data);

    //             }                       
    //         }
    //     }

    // }
// }

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Hash;
  
class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new User([
            'name'     => $row['name'],
            'email'    => $row['email'], 
            'password' => Hash::make($row['password']),
        ]);
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'password' => 'required|min:5',
            'email' => 'required|email|unique:users'
        ];
    }
}