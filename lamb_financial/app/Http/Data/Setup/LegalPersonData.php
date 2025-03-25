<?php
/**
 * Created by PhpStorm.
 * User: Raul Jonatan ( @Julnarot )
 * Date: 8/23/21
 * Time: 3:49 PM
 */

namespace App\Http\Data\Setup;

use App\Models\LegalPerson;

class LegalPersonData
{
    public static function updateDataById($id, $data)
    {
        LegalPerson::where('id_ruc', $id)->update($data);

//        foreach (array_keys($data) as $key) {
//            $instance->$key = $data[$key];
//        }
//        $instance->save();
        return LegalPerson::find($id);
    }
}