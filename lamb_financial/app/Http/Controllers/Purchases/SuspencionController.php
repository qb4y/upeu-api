 <?php
/**
 * Created by PhpStorm.
 * User: UPN
 * Date: 4/03/2019
 * Time: 13:59
 */

namespace App\Http\Controllers\Purchases\Suspencion;
//use App\Http\Data\Purchases\SuspencionData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Data\GlobalMethods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\ORM\CompraSuspencion;
use App\ORM\ContaEntidadAnhoConfig;
use App\ORM\ContaEntidadDepto;
use App\ORM\Persona;
use PDO;

class SuspencionController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getCompraSuspencion()
    {
        $jResponse = GlobalMethods::authorizationLamb($this->request);
        $code   = $jResponse["code"];
        /*$valida = $jResponse["valida"];
        $id_entidad = $jResponse["id_entidad"];
        $id_depto = $jResponse["id_depto"];
        $id_user = $jResponse["id_user"];*/
       // if($valida=='SI') {
            $jResponse = [];
            $msn = "";
            try {
                $data=CompraSuspencion::All();
                
                if ($data) {
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'OK';
                    $jResponse['data'] = $data;
                    $code = "200";
                }else{
                    $jResponse['success'] = true;
                    $jResponse['message'] = 'The item does not exist';
                    $jResponse['data'] = [];
                    $code = "202";
                }

            } catch (Exception $e) {
                $jResponse['success'] = false;
                $jResponse['message'] = $msn;
                $jResponse['data'] = [];
                $code = "202";
            }
        //}
        return response()->json($jResponse,$code);
    }

    public function store()
    {
        return null;
    }

    public function update($id_compra) {
       return null;
    }

   

    public function destroy($id_compra) {
       return null;
    }

}
