<?php
namespace App\Http\Data\Purchases;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use PDO;

use Illuminate\Database\Eloquent\Model;

class Test1 extends Model
{
    /* private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    } */
    public function __construct()
    {}
    /* listMyOperationsPending */
    protected $table = 'test1';
    public $timestamps = false;
    protected $dateFormat = 'U';
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';
    public $incrementing = false;

    protected $attributes = [
        'id_test' => 1,
        'nom' => "-",
    ];
}
