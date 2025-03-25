<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

    function totalSaldoAnt($data, $key) {
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->saldo_anterior;
            }
        }
        return $total;
    }
    function totalPto($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->pto_gasto;
            }
        }
        return $total;
    }
    function totalEject($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->eje_gasto;
            }
        }
        return $total;
    }
     function totalSaldo($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->saldo;
            }
        }
        return $total;
    }

     function totalSaldoAcum($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->saldo_acumulado;
            }
        }
        return $total;
    }

    
     function totalSaldoPpto($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto){
                $total = $total + $value->saldo_ppto_anual;
            }
        }
        return $total;
    }

    function totalGenAnt($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->saldo_anterior;
        });
        return $total;
    }
     function totalGenPto($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->pto_gasto;
        });
        return $total;
    }
     function totalGenEject($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->eje_gasto;
        });
        return $total;
    }
     function totalGenSaldo($data){
        $total = 0;

        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->saldo;
        });
        return $total;
    }

     function totalGenAcum($data){
        $total = 0;

        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->saldo_acumulado;
        });
        return $total;
    }

     function totalGenPptoAnual($data){
        $total = 0;

        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->saldo_ppto_anual;
        });
        return $total;
    }

// add for budget balance
    function totalSaldoAnt_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto_pa){
                $total = $total + $value->saldo_anterior;
            }
        }
        return $total;
    }
    function totalPtoIngreso_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto_pa){
                $total = $total + $value->pto_ingresos;
            }
        }
        return $total;
    }
     function totalPtoGasto_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            if($key === $value->id_depto_pa){
                $total = $total + $value->pto_gasto;
            }
        }
        return $total;
    }
     function totalEjectIngreso_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            if($key === $value->id_depto_pa){
                $total = $total + $value->eje_ingresos;
            }
        }
        return $total;
    }

     function totalEjectGasto_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            if($key === $value->id_depto_pa){
                $total = $total + $value->eje_gastos;
            }
        }
        return $total;
    }

     function totalSaldo_($data, $key){
        $total = 0;
        foreach ($data['items'] as $keys => $value){
            /// print($value->id_depto);
            if($key === $value->id_depto_pa){
                $total = $total + $value->saldo;
            }
        }
        return $total;
    }


     function totalGenPtoIng($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->pto_ingresos;
        });
        return $total;
    }
     function totalGenPtoGastos($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->pto_gasto;
        });
        return $total;
    }
     function totalGenEjecIng($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->eje_ingresos;
        });
        return $total;
    }
     function totalGenEjecGastos($data){
        $total = 0;
        $total = collect($data['items'])->reduce(function($carry, $item){
            return $carry + $item->eje_gastos;
        });
        return $total;
    }
