<?php
namespace App\Http\Data\HumanTalentMgt;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConceptData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }
    
    
    
}
?>

