<?php


namespace App\Http\Data\FinancesStudent\reports;


use Illuminate\Support\Facades\DB;

class EnrrolledPaymentData
{

    static function getEnrrolledPaymentPlan($params)
    {
        $qCiclo = "";
        if (isset($params['ciclo'])) {
            $ciclo = $params['ciclo'];
            $qCiclo = "AND A.CICLO in ($ciclo)";
        }
        //dd($params);
        $modeContracts = explode(",", $params['id_modo_contrato']);
        $modeStudes = explode(",", $params['id_modalidad_estudio']);
        // paStatements = implode(',', array_fill(0, sizeof($p), '?'));
        // dd($p, $modeContracts);
        $query = "SELECT * FROM (
                    SELECT COUNT(A.ID_PERSONA) AS CANT, A.NOMBRE_ESCUELA, C.CUOTAS
                    -- SELECT COUNT(A.ID_PERSONA) AS CANT,A.NOMBRE_FACULTAD,A.NOMBRE_ESCUELA,A.SEMESTRE ,C.CUOTAS
                    FROM DAVID.VW_ACAD_ALUMNO_CONTRATO A 
                    JOIN MAT_PLANPAGO_SEMESTRE B ON A.ID_PLANPAGO_SEMESTRE = B.ID_PLANPAGO_SEMESTRE 
                    JOIN MAT_PLANPAGO C ON B.ID_PLANPAGO = C.ID_PLANPAGO
                    WHERE A.ID_SEMESTRE = ?
                    AND A.ESTADO = '1'
                    AND A.ID_NIVEL_ENSENANZA = ?
                    AND A.ID_MODO_CONTRATO in (" . implode(',', $modeContracts) . ")
                    AND A.ID_MODALIDAD_ESTUDIO in (" . implode(',', $modeStudes) . ")
                    AND A.ID_SEDE = ?
                    $qCiclo
                    GROUP BY A.NOMBRE_ESCUELA,C.CUOTAS
                    --GROUP BY A.NOMBRE_FACULTAD,A.NOMBRE_ESCUELA,A.SEMESTRE,C.CUOTAS
                    ORDER BY NOMBRE_ESCUELA
                    )
                    PIVOT (SUM(CANT) AS CUOTA
                    FOR CUOTAS IN (1,2,3,4,5,6,7,8,9)
                    )
                    ORDER BY NOMBRE_ESCUELA";
        $rpta = DB::select($query, [
            $params['id_semestre'],
            $params['id_nivel_ensenanza'],
            $params['id_sede'],
        ]);
        //$keys = count($rpta) > 0 ? self::getSumColumns($rpta[0]) : [];
        return collect($rpta)->map(function ($item) {
            $item->total = self::getSumColumns($item);
            return $item;
        });
    }


    static function getCounterColumn($columns)
    {

        return array_filter($columns, function ($k) {
            return strpos($k, 'cuota') !== false;
        });
    }

    static function getSumColumns($instance)
    {
        $total = 0;
        $columns = self::getCounterColumn(array_keys((array)$instance));
        foreach ($columns as $col) {
            $total = $total + intval($instance->$col);

        }
        return $total;
    }

    static function getColumnsWithValue($data)
    {
        $extrem = ['nombre_escuela', 'total'];
        $keys = array_keys((array)$data[0]);
        $keys = array_diff($keys, $extrem);
        $nKeys = array();
        foreach ($data as $i => $instance) {
            foreach ($keys as $k) {
                if (!in_array($k, $nKeys) and isset($instance->$k))
                    array_push($nKeys, $k);
            }
        }
        sort($nKeys);
        array_unshift($nKeys, $extrem[0]);
        array_push($nKeys, $extrem[1]);
        return $nKeys;
    }

    static function getSummary($columns, $data)
    {
        $total = [];
        $porc = [];
        foreach ($columns as $c) {
            $t = 0;
            if ($c != 'nombre_escuela') {
                foreach ($data as $d) {
                    $t = $t + intval($d->$c);
                }
            } else {
                $t = 'total';
            }
            $total[$c] = $t;
        }
        $monTotal = isset($total['total']) ? $total['total'] : 1;
        foreach ($columns as $c) {
            $p = 0;
            if ($c != 'nombre_escuela') {
                $p = round(($total[$c] / $monTotal) * 100, 1);
            } else {
                $p = 'porcentaje';
            }
            $porc[$c] = $p;
        }


        return [
            'total' => $total,
            'porcent' => $porc,
            'other' => [$monTotal]
        ];
    }

}


