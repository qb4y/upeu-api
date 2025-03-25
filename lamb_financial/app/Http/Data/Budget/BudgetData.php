<?php
namespace App\Http\Data\Budget;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
class BudgetData extends Controller{
    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public static function listActivityEvento($id_proyecto,$id_evento,$id_presupuesto){
        if($id_presupuesto==0){
            $query = "SELECT 
                        0 AS ID_PRESUPUESTO_DET,
                        0 AS ID_PRESUPUESTO ,
                        A.ID_ACTIVIDAD,
                        A.ID_EVENTO,
                        A.ID_DEPTO,
                        A.ID_DEPTO as ID_DEPTO_ASIENTO,
                        CASE WHEN A.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END AS TIPO,
                        A.NOMBRE AS DESCRIPCION,
                        0.00 as CANTIDAD,
                        0.00 as PUNIDAD,
                        0.00 as A ,
                        0.00 as B,
                        0.00 as C ,
                        0.00 as D ,
                        0.00 as E ,
                        0.00 as F ,
                        0.00 as G ,
                        0.00 as TOTAL,
                        A.ESDESCUENTO,
                        A.ID_TIPOPLAN,
                        CASE WHEN A.ID_CTACTE IS NOT NULL THEN A.ID_CUENTAAASI||'/'||A.ID_CTACTE ELSE A.ID_CUENTAAASI END ID_CUENTAAASI,
                        A.ID_RESTRICCION,
                        A.ID_CTACTE,
                        A.ID_TIPOCTACTE,
                        D.NOMBRE AS DEPARTAMENTO,
                        C.NOMBRE AS CTACTABLE,
                        'N' AS ELIMINAR
                    FROM PSTO_ACTIVIDAD A INNER JOIN PSTO_EVENTO E ON A.ID_EVENTO=E.ID_EVENTO
                    LEFT JOIN CONTA_ENTIDAD_DEPTO D ON D.ID_ENTIDAD=A.ID_ENTIDAD AND D.ID_DEPTO=A.ID_DEPTO
                    LEFT JOIN CONTA_CTA_DENOMINACIONAL C ON C.ID_TIPOPLAN=A.ID_TIPOPLAN AND C.ID_CUENTAAASI=A.ID_CUENTAAASI AND C.ID_RESTRICCION=A.ID_RESTRICCION  
                    WHERE A.ID_EVENTO=".$id_evento."
                    AND E.ID_PROYECTO=".$id_proyecto."
                    AND A.ESTADO='1'
                    ORDER BY A.TIPO DESC,A.ESDESCUENTO DESC, A.ID_ACTIVIDAD";
        }else{
            $query = "SELECT 
                    D.ID_PRESUPUESTO_DET,
                    D.ID_PRESUPUESTO,
                    D.ID_ENTIDAD,
                    P.ID_EVENTO,
                    D.ID_DEPTO,
                    D.ID_DEPTO_ASIENTO,
                    CASE WHEN D.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END AS TIPO,
                    D.DESCRIPCION,
                    D.CANTIDAD,
                    D.PUNIDAD,
                    D.A,
                    D.B,
                    D.C,
                    D.D,
                    D.E,
                    D.F,
                    D.G,
                    D.TOTAL,
                    A.ID_TIPOPLAN,
                    CASE WHEN A.ID_CTACTE IS NOT NULL THEN A.ID_CUENTAAASI||'/'||A.ID_CTACTE ELSE A.ID_CUENTAAASI END ID_CUENTAAASI,
                    A.ID_RESTRICCION,
                    A.ID_CTACTE,
                    A.ID_TIPOCTACTE,
                    DE.NOMBRE AS DEPARTAMENTO,
                    C.NOMBRE AS CTACTABLE,
                    'N' AS ELIMINAR
                FROM PSTO_PRESUPUESTO P, PSTO_PRESUPUESTO_DET D, PSTO_ACTIVIDAD A,CONTA_ENTIDAD_DEPTO DE,CONTA_CTA_DENOMINACIONAL C
                WHERE P.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_ACTIVIDAD=A.ID_ACTIVIDAD
                AND DE.ID_ENTIDAD=A.ID_ENTIDAD AND DE.ID_DEPTO=D.ID_DEPTO
                AND C.ID_TIPOPLAN=A.ID_TIPOPLAN AND C.ID_CUENTAAASI=A.ID_CUENTAAASI AND C.ID_RESTRICCION=A.ID_RESTRICCION 
                AND P.ID_PRESUPUESTO=".$id_presupuesto."
                ORDER BY D.TIPO DESC,A.ESDESCUENTO,D.ID_ACTIVIDAD,D.ID_PRESUPUESTO_DET";
        }
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listTotalPresupuesto($id_presupuesto){
        
            $query = "SELECT 
                    SUM(CASE WHEN D.TIPO='I' THEN D.TOTAL ELSE 0 END) AS INGRESO,
                    SUM(CASE WHEN D.TIPO='G' THEN D.TOTAL ELSE 0 END) AS GASTO,
                    SUM(CASE WHEN D.TIPO='I' THEN D.TOTAL ELSE (-1)*D.TOTAL END) AS RESULTADO
                FROM PSTO_PRESUPUESTO P, PSTO_PRESUPUESTO_DET D, PSTO_ACTIVIDAD A
                WHERE P.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_ACTIVIDAD=A.ID_ACTIVIDAD
                AND P.ID_PRESUPUESTO=".$id_presupuesto;
      
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listBudget($id_entidad,$id_depto_padre,$id_anho,$id_area_padre,$id_area,$id_depto,$id_proyecto ,$id_evento,$id_pstonegocio,$id_eje,$estado){
        
        /*
         *           "a.total_ingreso", 
                    "a.total_gasto",
         */
        $query=DB::table("psto_presupuesto as a");
        $query->join("psto_presupuesto_det as b","b.id_presupuesto","=","a.id_presupuesto");
        $query->join("psto_evento as d","d.id_evento","=","a.id_evento");
        $query->join("psto_negocio as e","e.id_pstonegocio","=","a.id_pstonegocio");
        $query->join("psto_proyecto as f","f.id_proyecto","=","d.id_proyecto");
        $query->join("vw_area_depto as x","x.id_entidad","=",DB::raw("b.id_entidad and x.id_depto=b.id_depto_asiento"));
        $query->leftjoin("psto_eje as c","c.id_eje","=","a.id_eje");
        $query->select("a.id_presupuesto", 
                    "a.id_entidad",  
                    "a.id_anho",  
                    "a.id_evento",  
                    "a.id_pstonegocio",  
                    "a.id_eje", 
                    "a.id_depto", 
                    "a.id_persona", 
                    "a.descripcion", 
                    "a.estado",
                    DB::raw("to_char(a.fecha,'DD/MM/YYYY') as fecha"), 
                    DB::raw("coalesce(sum((case when b.tipo='I' then b.total else 0 end)),0) as total_ingreso,coalesce(sum((case when b.tipo='G' then b.total else 0 end)),0) as total_gasto"),
                    DB::raw("case when a.estado='2' then 'Aprobado' when a.estado='0' then 'Anulado' else 'Registrado' end as estado_desc"),
                    "c.nombre as eje",
                    "d.nombre as evento",
                    "e.nombre as negocio",
                    "f.nombre as proyecto"
                    );
        $query->where('a.id_entidad', $id_entidad);
        $query->where('a.id_anho', $id_anho);
        $query->where('a.id_depto', $id_depto_padre);
        $query->groupBy("a.id_presupuesto", 
                        "a.id_entidad",  
                        "a.id_anho",  
                        "a.id_evento",  
                        "a.id_pstonegocio",  
                        "a.id_eje", 
                        "a.id_depto", 
                        "a.id_persona", 
                        "a.descripcion", 
                        DB::raw("to_char(a.fecha,'DD/MM/YYYY')"), 
                        "a.total_ingreso", 
                        "a.total_gasto",
                        "a.estado",
                        "c.nombre",
                        "d.nombre",
                        "e.nombre",
                        "f.nombre");
        if(strlen($id_depto)>0){
            $query->where("x.id_depto",$id_depto);
        }else{
            if(strlen($id_area)>0){
                $query->where("x.id_area",$id_area);
                
            }else{
                if(strlen($id_area_padre)>0){
                    $query->where("x.id_area_padre",$id_area_padre);
                }
            }
        }
        if(strlen($id_pstonegocio)>0){
            $query->where('a.id_pstonegocio','=',$id_pstonegocio);
        }
        if(strlen($id_eje)>0){
            $query->where('a.id_eje','=',$id_eje);
        }
        if(strlen($estado)>0){
            $query->where('a.estado','=',$estado);
        }
        if(strlen($id_evento)>0){
            $query->where('a.id_evento','=',$id_evento);
        }
        if(strlen($id_proyecto)>0){
            $query->where('d.id_proyecto','=',$id_proyecto);
        }
        
        $query->orderBy('a.id_presupuesto', 'desc');

        $oQuery = $query->paginate(20); 
        
               
        return $oQuery;
        
    }
    public static function showBudget($id_presupuesto){
        
        
        $query = "SELECT 
                    a.ID_PRESUPUESTO, 
                    a.ID_ENTIDAD, 
                    a.ID_ANHO, 
                    a.ID_EVENTO, 
                    a.ID_PSTONEGOCIO, 
                    a.ID_EJE, 
                    a.ID_DEPTO, 
                    a.ID_PERSONA, 
                    a.DESCRIPCION, 
                    a.FECHA, 
                    a.TOTAL_INGRESO, 
                    a.TOTAL_GASTO, 
                    a.ESTADO,
                    c.NOMBRE as EJE,
                    d.NOMBRE as EVENTO,
                    e.NOMBRE as NEGOCIO,
                    f.NOMBRE as PROYECTO,
                    CASE WHEN  a.ESTADO='2' THEN 'Aprobado' WHEN a.ESTADO='0' THEN 'Anulado' ELSE 'Registrado' END AS ESTADO_DESC,
                    A.MOTIVO,
                    d.ID_AUXILIAR,
                    x.NOMBRE as AUXILIAR,
                    x.URL_REP
                FROM PSTO_PRESUPUESTO a INNER JOIN PSTO_EJE c
                ON a.ID_EJE=c.ID_EJE
                INNER JOIN PSTO_EVENTO d
                ON a.ID_EVENTO=d.ID_EVENTO
                INNER JOIN PSTO_NEGOCIO e
                ON a.ID_PSTONEGOCIO=e.ID_PSTONEGOCIO 
                INNER JOIN PSTO_PROYECTO f
                ON d.ID_PROYECTO=f.ID_PROYECTO
                LEFT JOIN PSTO_AUXILIAR x
                ON x.ID_AUXILIAR=d.ID_AUXILIAR
                WHERE a.ID_PRESUPUESTO=".$id_presupuesto." 
                ORDER BY a.ID_PRESUPUESTO DESC";
        $oQuery = DB::select($query);        
        return $oQuery;
        
    }
    public static function validateBudget($id_evento){
        $query = "SELECT 
                        ID_AUXILIAR
                FROM PSTO_EVENTO
                WHERE ID_AUXILIAR IS NOT NULL
                AND ID_EVENTO=".$id_evento;  
        $oQuery = DB::select($query);
        $nerror=0;
        $merror="";
        if(count($oQuery)>0){
            $nerror=1;
            $merror="El Presupuesto se genera mediante un auxiliar";
        }
        if($nerror==0){
            $query = "SELECT 
                            ID_CUENTAAASI,ID_DEPTO
                    FROM PSTO_ACTIVIDAD
                    WHERE (ID_CUENTAAASI IS NULL OR ID_DEPTO IS NULL)
                    AND ESTADO='1'
                    AND ID_EVENTO=".$id_evento;  
            $oQuery = DB::select($query);

            if(count($oQuery)>0){
                $nerror=1;
                $merror="Falta Asignar Cta Cta Denominacional / Departamento";
            }
        }
        $respuesta = [
                'nerror' => $nerror,
                'merror' => $merror
            ];
        return $respuesta;
    }
    public static function addBudget($id_entidad,$id_anho,$id_area,$id_evento,$id_pstonegocio,$id_eje,$id_depto,$id_persona,$descripcion){
        
        $query = "SELECT 
                        NOMBRE
                FROM PSTO_EVENTO
                WHERE ID_EVENTO=".$id_evento;  
        $oQuery = DB::select($query);
        $descripcion = "";
        foreach ($oQuery as $key => $item){
            if(strlen($descripcion)==0){
                $descripcion  = $item->nombre ; 
            }
                           
        }
        
        
        $query = "SELECT 
                        COALESCE(MAX(ID_PRESUPUESTO),0)+1 ID_PRESUPUESTO
                FROM PSTO_PRESUPUESTO ";  
        $oQuery = DB::select($query);
        $id_presupuesto = 0;
        foreach ($oQuery as $key => $item){
            $id_presupuesto  = $item->id_presupuesto ;                
        }
        if($id_presupuesto==0){
            $id_presupuesto = 1;
        }
        $fecha=date("d/m/Y");
        $data= DB::table('PSTO_PRESUPUESTO')->insert(
            array('ID_PRESUPUESTO'=>$id_presupuesto,
                'ID_ENTIDAD' => $id_entidad,
                'ID_ANHO'=> $id_anho,                
                'ID_EVENTO'=> $id_evento,
                'ID_PSTONEGOCIO'=> $id_pstonegocio,
                'ID_EJE'=> $id_eje,
                'ID_DEPTO'=> $id_depto,
                'ID_PERSONA'=> $id_persona,
                'DESCRIPCION'=> $descripcion,
                'FECHA'=> $fecha,
                'ESTADO'=>'1'
                )
        );   
        
        $query = "SELECT 
                    ID_PRESUPUESTO, 
                    ID_ENTIDAD,
                    ID_ANHO, 
                    ID_EVENTO,
                    ID_PSTONEGOCIO,
                    ID_EJE,
                    ID_DEPTO,
                    ID_PERSONA, 
                    DESCRIPCION,
                    FECHA,
                    TOTAL_INGRESO,
                    TOTAL_GASTO
                    ESTADO 
            FROM PSTO_PRESUPUESTO
            WHERE ID_PRESUPUESTO=".$id_presupuesto."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function updateBudget($id_presupuesto,$id_area,$id_pstonegocio,$id_eje,$id_depto,$descripcion){
        
       
        
        $query = "UPDATE PSTO_PRESUPUESTO SET 
                    ID_EJE = ".$id_eje.",
                    ID_PSTONEGOCIO = ".$id_pstonegocio.",
                    DESCRIPCION = '".$descripcion."',
                    ID_DEPTO = '".$id_depto."'
              WHERE ID_PRESUPUESTO = ".$id_presupuesto;
        DB::update($query);
       
        
        $query = "SELECT 
                    ID_PRESUPUESTO, 
                    ID_ENTIDAD,
                    ID_ANHO, 
                    ID_EVENTO,
                    ID_PSTONEGOCIO,
                    ID_EJE,
                    ID_DEPTO,
                    ID_PERSONA, 
                    DESCRIPCION,
                    FECHA,
                    TOTAL_INGRESO,
                    TOTAL_GASTO
                    ESTADO 
            FROM PSTO_PRESUPUESTO
            WHERE ID_PRESUPUESTO=".$id_presupuesto."";
        $oQuery = DB::select($query);
        return $oQuery;
    }
    public static function deleteBudget($id_presupuesto,$id_persona){
        
        
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_ELIMINAR_PRESUPUESTO_USER(:P_ID_PRESUPUESTO,:P_ID_PERSONA); end;");
        $stmt->bindParam(':P_ID_PRESUPUESTO', $id_presupuesto, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->execute();  
                 
    }
    public static function estadoBudget($id_presupuesto,$estado,$motivo,$id_user){
        
       

        $query = "UPDATE PSTO_ASIENTO SET ESTADO='".$estado."'
                WHERE ID_PRESUPUESTO = ".$id_presupuesto."";
        
        DB::update($query);
        
         $query = "SELECT 
                    MOTIVO 
            FROM PSTO_PRESUPUESTO
            WHERE ID_PRESUPUESTO=".$id_presupuesto."";
        $oQuery = DB::select($query);
        
        $motivoact='';
        $mot=$estado.'-'.$motivo.'('.$id_user.')';
        foreach($oQuery as $row){
            $motivoact=$row->motivo;
        }
        
        if(strlen($motivoact)>0){
           $mot= $motivoact.', '.$estado.'-'.$motivo.'('.$id_user.')';
        }
        
        $query = "UPDATE PSTO_PRESUPUESTO SET ESTADO='".$estado."',MOTIVO='".substr($mot,0,500)."'
                  WHERE ID_PRESUPUESTO = ".$id_presupuesto." 
                  ";
        DB::update($query);
    }
    public static function addBudgetByAuxiliar($id_entidad,$id_depto_padre,$id_anho,$id_persona,$id_pstnegocio,$id_eje,$id_auxiliar){
        
        $nerror=0;
        $msgerror="";
        
        for($i=1;$i<=200;$i++){
            $msgerror.="0";
        }
        
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_PREGRADO_PRESUPUESTO(
                                    :P_ID_ENTIDAD, 
                                    :P_ID_DEPTO_PADRE, 
                                    :P_ID_ANHO, 
                                    :P_ID_PERSONA,
                                    :P_ID_PSTONEGOCIO,
                                    :P_ID_EJE,
                                    :P_ID_AUXILIAR,
                                    :P_ERROR,
                                    :P_MSGERROR
                                    ); end;");
        $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_DEPTO_PADRE', $id_depto_padre, PDO::PARAM_STR);
        $stmt->bindParam(':P_ID_ANHO', $id_anho, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERSONA', $id_persona, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PSTONEGOCIO', $id_pstnegocio, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_EJE', $id_eje, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_AUXILIAR', $id_auxiliar, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();  
        
        $return=[
            'nerror'=>$nerror,
            'msgerror'=>$msgerror
        ];
        
        return $return;
        
    }
     
    public static function listBudgetDetail($id_presupuesto){
        
        $query = "SELECT 
                        ID_EVENTO
                FROM PSTO_PRESUPUESTO
                WHERE ID_PRESUPUESTO=".$id_presupuesto;  
        $oQuery = DB::select($query);
        $id_evento = 0;
        foreach ($oQuery as $key => $item){
            $id_evento = $item->id_evento;                
        }

        $query = "SELECT 
                        COALESCE(B.ID_PRESUPUESTO_DET,0) AS ID_PRESUPUESTO_DET,
                        COALESCE(B.ID_PRESUPUESTO,0) AS ID_PRESUPUESTO,
                        A.ID_ACTIVIDAD,
                        A.ID_EVENTO,
                        B.ID_DEPTO,
                        B.ID_DEPTO_ASIENTO,
                        CASE WHEN coalesce(B.TIPO,A.TIPO)='I' THEN 'Ingreso' ELSE 'Gasto' END AS TIPO,
                        COALESCE(B.DESCRIPCION,A.NOMBRE) AS DESCRIPCION,
                        COALESCE(B.CANTIDAD,0) AS  CANTIDAD,
                        COALESCE(B.PUNIDAD,A.IMPORTEUNIT1) AS  PUNIDAD,
                        COALESCE(B.A,0) AS  A,
                        COALESCE(B.B ,0) AS  B,
                        COALESCE(B.C ,0) AS  C, 
                        COALESCE(B.D ,0) AS  D,
                        COALESCE(B.E ,0) AS  E,
                        COALESCE(B.F ,0) AS  F,
                        COALESCE(B.G ,0) AS  G,
                        COALESCE(B.TOTAL,0) AS  TOTAL,
                        A.ESDESCUENTO,
                        COALESCE(B.ID_TIPOPLAN,A.ID_TIPOPLAN) AS ID_TIPOPLAN,
                        COALESCE(B.ID_CUENTAAASI,A.ID_CUENTAAASI) AS ID_CUENTAAASI,
                        COALESCE(B.ID_RESTRICCION,A.ID_RESTRICCION) AS ID_RESTRICCION,
                        COALESCE(B.ID_ENTIDAD,A.ID_ENTIDAD) AS ID_ENTIDAD,
                        COALESCE(B.ID_CTACTE,A.ID_CTACTE) AS ID_CTACTE,
                        COALESCE(B.ID_TIPOCTACTE,A.ID_TIPOCTACTE) AS ID_TIPOCTACTE
                FROM PSTO_ACTIVIDAD A
                LEFT JOIN PSTO_PRESUPUESTO_DET B
                ON A.ID_ACTIVIDAD=B.ID_ACTIVIDAD
                AND B.ID_PRESUPUESTO=".$id_presupuesto." 
                WHERE A.ID_EVENTO=".$id_evento."
                AND (CASE WHEN B.ID_PRESUPUESTO_DET IS NOT NULL THEN '1' ELSE A.ESTADO END)='1'
                ORDER BY coalesce(B.TIPO,A.TIPO) DESC,A.ESDESCUENTO DESC, A.ID_ACTIVIDAD,B.ID_PRESUPUESTO_DET";
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listBudgetDetailRep($id_presupuesto,$id_area,$id_area_padre,$id_depto){
        
        $query = "SELECT 
                        COALESCE(E.ID_AUXILIAR,0) AS ID_AUXILIAR
                FROM PSTO_PRESUPUESTO P, PSTO_EVENTO E
                WHERE P.ID_EVENTO=E.ID_EVENTO
                AND P.ID_PRESUPUESTO=".$id_presupuesto;  
        $oQuery = DB::select($query);
        $id_auxiliar = 0;
        foreach ($oQuery as $key => $item){
            $id_auxiliar = $item->id_auxiliar;                
        }
        $where ="";
        if(strlen($id_depto)>0){
            $where=" and x.id_depto='".$id_depto."' ";
        }else{
            if(strlen($id_area)>0){
                $where=" and x.id_area=".$id_area." ";
            }else{
                if(strlen($id_area_padre)>0){
                    $where=" and x.id_area_padre=".$id_area_padre." ";
                }
            }
        }
        if($id_auxiliar==0){
        $query = "SELECT 
                    D.ID_PRESUPUESTO_DET,
                    D.ID_PRESUPUESTO,
                    D.ID_ENTIDAD,
                    A.ESDESCUENTO,
                    D.ID_CTACTE,
                    D.DESCRIPCION,
                    D.ID_ACTIVIDAD,
                    D.ID_DEPTO,
                    D.ID_DEPTO_ASIENTO,
                    CASE WHEN D.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END AS TIPO,
                    D.CANTIDAD,
                    D.PUNIDAD,
                    D.A,
                    D.B,
                    D.C,
                    D.D,
                    D.E,
                    D.F,
                    D.G,
                    D.TOTAL,
                    D.ID_CUENTAAASI,
                    X.DEPARTAMENTO
                FROM PSTO_PRESUPUESTO P, PSTO_PRESUPUESTO_DET D, PSTO_ACTIVIDAD A,VW_AREA_DEPTO X
                WHERE P.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_ACTIVIDAD=A.ID_ACTIVIDAD
                AND D.ID_ENTIDAD=X.ID_ENTIDAD 
                AND D.ID_DEPTO_ASIENTO=X.ID_DEPTO
                AND P.ID_PRESUPUESTO=".$id_presupuesto."
                ".$where."
                ORDER BY D.TIPO DESC,coalesce(A.ESDESCUENTO,'N'),D.ID_ACTIVIDAD";
        }else{
            $query = "SELECT 
                        0 AS ID_PRESUPUESTO_DET,
                        D.ID_PRESUPUESTO,
                        D.ID_ENTIDAD,
                        CASE WHEN D.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END AS TIPO,
                        A.ESDESCUENTO,
                        '' AS ID_CTACTE,
                        D.DESCRIPCION,
                        D.ID_ACTIVIDAD,
                        '' AS ID_DEPTO,
                        '' AS ID_DEPTO_ASIENTO,
                        0 AS CANTIDAD,
                        0 AS PUNIDAD,
                        0 AS A,
                        0 AS B,
                        0 AS C,
                        0 AS D,
                        0 AS E,
                        0 AS F,
                        0 AS G,
                        SUM(D.TOTAL)  AS TOTAL,
                        '' AS ID_CUENTAAASI,
                        '' AS DEPARTAMENTO
                    FROM PSTO_PRESUPUESTO P, PSTO_PRESUPUESTO_DET D, PSTO_ACTIVIDAD A,VW_AREA_DEPTO X
                    WHERE P.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                    AND D.ID_ACTIVIDAD=A.ID_ACTIVIDAD
                    AND D.ID_ENTIDAD=X.ID_ENTIDAD 
                    AND D.ID_DEPTO_ASIENTO=X.ID_DEPTO
                    AND P.ID_PRESUPUESTO=".$id_presupuesto."
                    ".$where."
                    GROUP BY 
                    CASE WHEN D.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END,
                    D.DESCRIPCION,
                    D.ID_ACTIVIDAD,
                    A.ESDESCUENTO,
                    D.ID_PRESUPUESTO,
                    D.ID_ENTIDAD
                    ORDER BY TIPO DESC,coalesce(A.ESDESCUENTO,'N'),D.ID_ACTIVIDAD";
        }
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listBudgetDetailTotal($id_presupuesto,$id_area,$id_area_padre,$id_depto){
        
        $where ="";
        if(strlen($id_depto)>0){
            $where=" and x.id_depto='".$id_depto."' ";
        }else{
            if(strlen($id_area)>0){
                $where=" and x.id_area=".$id_area." ";
            }else{
                if(strlen($id_area_padre)>0){
                    $where=" and x.id_area_padre=".$id_area_padre." ";
                }
            }
        }
        $query = "SELECT
                        COALESCE(SUM(CASE WHEN D.TIPO='I' THEN D.TOTAL ELSE 0 END),0) AS INGRESO,
                        COALESCE(SUM(CASE WHEN D.TIPO='G' THEN D.TOTAL ELSE 0 END),0) AS GASTO,
                        COALESCE(SUM(CASE WHEN D.TIPO='I' THEN D.TOTAL ELSE 0 END),0)-COALESCE(SUM(CASE WHEN D.TIPO='G' THEN D.TOTAL ELSE 0 END),0) AS SALDO
                FROM PSTO_PRESUPUESTO P, PSTO_PRESUPUESTO_DET D, PSTO_ACTIVIDAD A,VW_AREA_DEPTO X
                WHERE P.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_ACTIVIDAD=A.ID_ACTIVIDAD
                AND D.ID_ENTIDAD=X.ID_ENTIDAD 
                AND D.ID_DEPTO_ASIENTO=X.ID_DEPTO
                ".$where."
                AND P.ID_PRESUPUESTO=".$id_presupuesto;
       
        $oQuery = DB::select($query);        
        return $oQuery;

    }
    public static function listBudgetDetailDist($id_presupuesto,$id_area,$id_area_padre,$id_depto){
        
        $where ="";
        if(strlen($id_depto)>0){
            $where=" and x.id_depto='".$id_depto."' ";
        }else{
            if(strlen($id_area)>0){
                $where=" and x.id_area=".$id_area." ";
            }else{
                if(strlen($id_area_padre)>0){
                    $where=" and x.id_area_padre=".$id_area_padre." ";
                }
            }
        }
        $query = "SELECT 
                    X.DESCRIPCION,
                    X.TIPO,
                    X.ESDESCUENTO,
                    X.ID_ACTIVIDAD,
                    X.TIPO_DESC,
                    SUM(X.TOTAL) AS TOTAL,
                    SUM(X.MES1) AS MES1,
                    SUM(X.MES2) AS MES2,
                    SUM(X.MES3) AS MES3,
                    SUM(X.MES4) AS MES4,
                    SUM(X.MES5) AS MES5,
                    SUM(X.MES6) AS MES6,
                    SUM(X.MES7) AS MES7,
                    SUM(X.MES8) AS MES8,
                    SUM(X.MES9) AS MES9,
                    SUM(X.MES10) AS MES10,
                    SUM(X.MES11) AS MES11,
                    SUM(X.MES12) AS MES12
                    FROM (
                    SELECT 
                        D.DESCRIPCION,
                        D.TIPO,
                        COALESCE(A.ESDESCUENTO,'N') AS ESDESCUENTO,
                        D.ID_ACTIVIDAD,
                        CASE WHEN D.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END AS TIPO_DESC,
                        sum(D.TOTAL) as TOTAL,
                        SUM(CASE WHEN Y.ID_MES=1 THEN Y.IMPORTE ELSE 0 END ) AS MES1,
                        SUM(CASE WHEN Y.ID_MES=2 THEN Y.IMPORTE ELSE 0 END ) AS MES2,
                        SUM(CASE WHEN Y.ID_MES=3 THEN Y.IMPORTE ELSE 0 END ) AS MES3,
                        SUM(CASE WHEN Y.ID_MES=4 THEN Y.IMPORTE ELSE 0 END ) AS MES4,
                        SUM(CASE WHEN Y.ID_MES=5 THEN Y.IMPORTE ELSE 0 END ) AS MES5,
                        SUM(CASE WHEN Y.ID_MES=6 THEN Y.IMPORTE ELSE 0 END ) AS MES6,
                        SUM(CASE WHEN Y.ID_MES=7 THEN Y.IMPORTE ELSE 0 END ) AS MES7,
                        SUM(CASE WHEN Y.ID_MES=8 THEN Y.IMPORTE ELSE 0 END ) AS MES8,
                        SUM(CASE WHEN Y.ID_MES=9 THEN Y.IMPORTE ELSE 0 END ) AS MES9,
                        SUM(CASE WHEN Y.ID_MES=10 THEN Y.IMPORTE ELSE 0 END ) AS MES10,
                        SUM(CASE WHEN Y.ID_MES=11 THEN Y.IMPORTE ELSE 0 END ) AS MES11,
                        SUM(CASE WHEN Y.ID_MES=12 THEN Y.IMPORTE ELSE 0 END ) AS MES12
                    FROM PSTO_PRESUPUESTO P, PSTO_PRESUPUESTO_DET D, PSTO_ACTIVIDAD A,VW_AREA_DEPTO X,PSTO_PRESUPUESTO_DET_DIST Y
                    WHERE P.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                    AND D.ID_PRESUPUESTO=Y.ID_PRESUPUESTO
                    AND D.ID_PRESUPUESTO_DET=Y.ID_PRESUPUESTO_DET
                    AND D.ID_ACTIVIDAD=A.ID_ACTIVIDAD
                    AND D.ID_ENTIDAD=X.ID_ENTIDAD 
                    AND D.ID_DEPTO_ASIENTO=X.ID_DEPTO
                    AND P.ID_PRESUPUESTO=".$id_presupuesto." 
                    ".$where." 
                    GROUP BY D.DESCRIPCION,
                        D.TIPO,
                        COALESCE(A.ESDESCUENTO,'N'),
                        D.ID_ACTIVIDAD,
                        CASE WHEN D.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END
                    ORDER BY D.TIPO DESC,COALESCE(A.ESDESCUENTO,'N'),D.ID_ACTIVIDAD
                    )X
                    GROUP BY X.DESCRIPCION,
                    X.TIPO,
                    X.ESDESCUENTO,
                    X.ID_ACTIVIDAD,
                    X.TIPO_DESC
                    ORDER BY X.TIPO DESC,X.ESDESCUENTO,X.ID_ACTIVIDAD ";


        $oQuery = DB::select($query);     
        $datos=array();
        

        
        $totaling=0;
        $totalegr=0;
        $mes1i=0;
        $mes2i=0;
        $mes3i=0;
        $mes4i=0;
        $mes5i=0;
        $mes6i=0;
        $mes7i=0;
        $mes8i=0;
        $mes9i=0;
        $mes10i=0;
        $mes11i=0;
        $mes12i=0;
        
        $mes1e=0;
        $mes2e=0;
        $mes3e=0;
        $mes4e=0;
        $mes5e=0;
        $mes6e=0;
        $mes7e=0;
        $mes8e=0;
        $mes9e=0;
        $mes10e=0;
        $mes11e=0;
        $mes12e=0;

        $i=1;
        foreach($oQuery as  $key =>  $item){
            $datmes = (array)$item;
            
            $fila=array();
            $fila["num"]=$i;
            $fila["descripcion"]=$item->descripcion;
            $fila["tipo_desc"]=$item->tipo_desc;
            $fila["tipo"]=$item->tipo;
            $fila["esdecuento"]=$item->esdescuento;
            $fila["id_actividad"]=$item->id_actividad;
            $fila["total"]=$item->total;
            $fila["mes1"]=$item->mes1;
            $fila["mes2"]=$item->mes2;
            $fila["mes3"]=$item->mes3;
            $fila["mes4"]=$item->mes4;
            $fila["mes5"]=$item->mes5;
            $fila["mes6"]=$item->mes6;
            $fila["mes7"]=$item->mes7;
            $fila["mes8"]=$item->mes8;
            $fila["mes9"]=$item->mes9;
            $fila["mes10"]=$item->mes10;
            $fila["mes11"]=$item->mes11;
            $fila["mes12"]=$item->mes12;
            if($item->tipo=="I"){
                $totaling=$totaling + $item->total;
                $mes1i=$mes1i + $item->mes1;
                $mes2i=$mes2i + $item->mes2;
                $mes3i=$mes3i + $item->mes3;
                $mes4i=$mes4i + $item->mes4;
                $mes5i=$mes5i + $item->mes5;
                $mes6i=$mes6i + $item->mes6;
                $mes7i=$mes7i + $item->mes7;
                $mes8i=$mes8i + $item->mes8;
                $mes9i=$mes9i + $item->mes9;
                $mes10i=$mes10i + $item->mes10;
                $mes11i=$mes11i + $item->mes11;
                $mes12i=$mes12i + $item->mes12;
            }else{
                $totalegr=$totalegr + $item->total;
                $mes1e=$mes1e + $item->mes1;
                $mes2e=$mes2e + $item->mes2;
                $mes3e=$mes3e + $item->mes3;
                $mes4e=$mes4e + $item->mes4;
                $mes5e=$mes5e + $item->mes5;
                $mes6e=$mes6e + $item->mes6;
                $mes7e=$mes7e + $item->mes7;
                $mes8e=$mes8e + $item->mes8;
                $mes9e=$mes9e + $item->mes9;
                $mes10e=$mes10e + $item->mes10;
                $mes11e=$mes11e + $item->mes11;
                $mes12e=$mes12e + $item->mes12;
            }
            
            $datos[]=$fila;
            $i++;
        }
        //total ingreso
        $fila=array();
        $fila["num"]='';
        $fila["descripcion"]="Total Ingreso";
        $fila["tipo_desc"]="";
        $fila["tipo"]="";
        $fila["esdecuento"]="";
        $fila["id_actividad"]=0;
        $fila["total"]=$totaling;
        $fila["mes1"]=$mes1i;
        $fila["mes2"]=$mes2i;
        $fila["mes3"]=$mes3i;
        $fila["mes4"]=$mes4i;
        $fila["mes5"]=$mes5i;
        $fila["mes6"]=$mes6i;
        $fila["mes7"]=$mes7i;
        $fila["mes8"]=$mes8i;
        $fila["mes9"]=$mes9i;
        $fila["mes10"]=$mes10i;
        $fila["mes11"]=$mes11i;
        $fila["mes12"]=$mes12i;
        $datos[]=$fila;
        
        //total Gasto
        $fila=array();
        $fila["num"]='';
        $fila["descripcion"]="Total Gasto";
        $fila["tipo_desc"]="";
        $fila["tipo"]="";
        $fila["esdecuento"]="";
        $fila["id_actividad"]=0;
        $fila["total"]=$totalegr;
        $fila["mes1"]=$mes1e;
        $fila["mes2"]=$mes2e;
        $fila["mes3"]=$mes3e;
        $fila["mes4"]=$mes4e;
        $fila["mes5"]=$mes5e;
        $fila["mes6"]=$mes6e;
        $fila["mes7"]=$mes7e;
        $fila["mes8"]=$mes8e;
        $fila["mes9"]=$mes9e;
        $fila["mes10"]=$mes10e;
        $fila["mes11"]=$mes11e;
        $fila["mes12"]=$mes12e;
        $datos[]=$fila;
        
        //total 
        $fila=array();
        $fila["num"]='';
        $fila["descripcion"]="Total";
        $fila["tipo_desc"]="";
        $fila["tipo"]="";
        $fila["esdecuento"]="";
        $fila["id_actividad"]=0;
        $fila["total"]=$totaling-$totalegr;
        $fila["mes1"]=$mes1i-$mes1e;
        $fila["mes2"]=$mes2i-$mes2e;
        $fila["mes3"]=$mes3i-$mes3e;
        $fila["mes4"]=$mes4i-$mes4e;
        $fila["mes5"]=$mes5i-$mes5e;
        $fila["mes6"]=$mes6i-$mes6e;
        $fila["mes7"]=$mes7i-$mes7e;
        $fila["mes8"]=$mes8i-$mes8e;
        $fila["mes9"]=$mes9i-$mes9e;
        $fila["mes10"]=$mes10i-$mes10e;
        $fila["mes11"]=$mes11i-$mes11e;
        $fila["mes12"]=$mes12i-$mes12e;
        $datos[]=$fila;
        
        return $datos;
      

    }
    /*public static function listBudgetDetailDist($id_presupuesto,$id_area,$id_area_padre,$id_depto){
        
        $where ="";
        if(strlen($id_depto)>0){
            $where=" and x.id_depto='".$id_depto."' ";
        }else{
            if(strlen($id_area)>0){
                $where=" and x.id_area=".$id_area." ";
            }else{
                if(strlen($id_area_padre)>0){
                    $where=" and x.id_area_padre=".$id_area_padre." ";
                }
            }
        }
        $query = "SELECT * FROM(
              SELECT
                  X.DESCRIPCION,
                  X.TIPO_DESC,
                  X.ID_MES,
                  X.TIPO,
                  X.ESDESCUENTO,
                  X.ID_ACTIVIDAD,
                  SUM(X.TOTAL) AS TOTAL,
                  SUM(X.IMPORTE) AS IMPORTE
              FROM(
                SELECT 
                    D.DESCRIPCION,
                    D.TIPO,
                    COALESCE(A.ESDESCUENTO,'N') AS ESDESCUENTO,
                    D.ID_ACTIVIDAD,
                    CASE WHEN D.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END AS TIPO_DESC,
                    D.TOTAL,
                    'M'||Y.ID_MES as ID_MES,
                    SUM(Y.IMPORTE)AS IMPORTE
                FROM PSTO_PRESUPUESTO P, PSTO_PRESUPUESTO_DET D, PSTO_ACTIVIDAD A,VW_AREA_DEPTO X,PSTO_PRESUPUESTO_DET_DIST Y
                WHERE P.ID_PRESUPUESTO=D.ID_PRESUPUESTO
                AND D.ID_PRESUPUESTO=Y.ID_PRESUPUESTO
                AND D.ID_PRESUPUESTO_DET=Y.ID_PRESUPUESTO_DET
                AND D.ID_ACTIVIDAD=A.ID_ACTIVIDAD
                AND D.ID_ENTIDAD=X.ID_ENTIDAD 
                AND D.ID_DEPTO_ASIENTO=X.ID_DEPTO
                AND P.ID_PRESUPUESTO=".$id_presupuesto." 
                ".$where."
                GROUP BY D.DESCRIPCION,
                    D.TIPO,
                    COALESCE(A.ESDESCUENTO,'N'),
                    D.ID_ACTIVIDAD,
                    CASE WHEN D.TIPO='I' THEN 'Ingreso' ELSE 'Gasto' END,
                    D.TOTAL,
                    Y.ID_MES
                ORDER BY D.TIPO DESC,COALESCE(A.ESDESCUENTO,'N'),D.ID_ACTIVIDAD,Y.ID_MES
              )X GROUP BY  X.DESCRIPCION,
                  X.TIPO_DESC,
                  X.ID_MES,
                  X.TIPO,
                  X.ESDESCUENTO,
                  X.ID_ACTIVIDAD
               ORDER BY X.TIPO DESC,X.ESDESCUENTO,X.ID_ACTIVIDAD,X.ID_MES
            ) PIVOT  (
              sum(IMPORTE) 
              FOR ID_MES IN('M1','M2','M3','M4','M5','M6','M7','M8','M9','M10','M11','M12')
            )
            ORDER BY  TIPO DESC,ESDESCUENTO,ID_ACTIVIDAD";
        $oQuery = DB::select($query);     
        $datos=array();
        
        $sql="SELECT ID_MES,lower(SIGLAS) as SIGLAS FROM CONTA_MES ORDER BY ID_MES ";
        $datames = DB::select($sql);
        
        $totaling=0;
        $totalegr=0;
        //dd($oQuery);
        $totalingm=array();
        $totalegrm=array();
        $i=1;
        foreach($oQuery as  $key =>  $item){
            $datmes = (array)$item;
            
            $fila=array();
            $fila["num"]=$i;
            $fila["descripcion"]=$item->descripcion;
            $fila["tipo_desc"]=$item->tipo_desc;
            $fila["tipo"]=$item->tipo;
            $fila["esdecuento"]=$item->esdescuento;
            $fila["id_actividad"]=$item->id_actividad;
            $fila["total"]=$item->total;
            //dd($datmes);
            foreach($datames as $row){
                $importe=0;
                if(is_numeric($datmes["'m".$row->id_mes."'"])){
                    $importe=$datmes["'m".$row->id_mes."'"];
                }else{
                    $importe=0;
                }
                $fila[$row->siglas]=$datmes["'m".$row->id_mes."'"];//$importe;
                
                if(!isset($totalingm[$row->id_mes])){
                    $totalingm[$row->id_mes]=0;
     
                }
                if(!isset($totalegrm[$row->id_mes])){
                    $totalegrm[$row->id_mes]=0;
                }
                
                if($item->tipo=="I"){
                    
                    $totalingm[$row->id_mes]=$totalingm[$row->id_mes]+$importe;
            
                }else{
                    
                     $totalegrm[$row->id_mes]=$totalegrm[$row->id_mes]+$importe;
                        
                }
            }
            
            if($item->tipo=="I"){
                $totaling=$totaling + $item->total;
            }else{
                $totalegr=$totalegr + $item->total;
            }
            
            $datos[]=$fila;
            $i++;
        }
        //total ingreso
        $fila=array();
        $fila["num"]='';
        $fila["descripcion"]="Total Ingreso";
        $fila["tipo_desc"]="";
        $fila["tipo"]="";
        $fila["esdecuento"]="";
        $fila["id_actividad"]=0;
        $fila["total"]=$totaling;
        foreach($datames as $row){
            if($totalingm[$row->id_mes]==0){
                $fila[$row->siglas]="";
            }else{
                $fila[$row->siglas]=$totalingm[$row->id_mes];
            }
            
        }
        $datos[]=$fila;
        
        //total Gasto
        $fila=array();
        $fila["num"]='';
        $fila["descripcion"]="Total Gasto";
        $fila["tipo_desc"]="";
        $fila["tipo"]="";
        $fila["esdecuento"]="";
        $fila["id_actividad"]=0;
        $fila["total"]=$totalegr;
        foreach($datames as $row){
            if($totalegrm[$row->id_mes]==0){
                $fila[$row->siglas]="";
            }else{
                $fila[$row->siglas]=$totalegrm[$row->id_mes];
            }
            
        }
        $datos[]=$fila;
        
        //total 
        $fila=array();
        $fila["num"]='';
        $fila["descripcion"]="Total";
        $fila["tipo_desc"]="";
        $fila["tipo"]="";
        $fila["esdecuento"]="";
        $fila["id_actividad"]=0;
        $fila["total"]=$totaling-$totalegr;
        foreach($datames as $row){
            if(($totalingm[$row->id_mes] - $totalegrm[$row->id_mes])==0){
                $fila[$row->siglas]="";
            }else{
                $fila[$row->siglas]=$totalingm[$row->id_mes] - $totalegrm[$row->id_mes];
            }

        }
        $datos[]=$fila;
        
        return $datos;
      

    }*/
    public static function addBudgetDetail($id_presupuesto,$id_entidad,$detail){
        
        $pdo = DB::getPdo();
        foreach($detail as $items){
            $id_actividad=$items->id_actividad ; 
            $descripcion=$items->descripcion;
            $cantidad=$items->cantidad;
            $punidad=$items->punidad;
            $id_depto=$items->id_depto;
            $a=$items->a;
            $b=$items->b;
            $c=$items->c;
            $d=$items->d;
            $e=$items->e;
            $f=$items->f;
            $g=$items->g;
         
            if(strlen($cantidad)==0){
                $cantidad =0;  
            }
            if(strlen($punidad)==0){
                $punidad =0;  
            }
            if(strlen($a)==0){
                $a =0;  
            }
            if(strlen($b)==0){
                $b=0;  
            }
            if(strlen($c)==0){
                $c=0;  
            }
            if(strlen($d)==0){
                $d=0;  
            }
            if(strlen($e)==0){
                $e=0;  
            }
            if(strlen($f)==0){
                $f=0;  
            }
            if(strlen($g)==0){
                $g=0;  
            }
        
            $total =  $cantidad + $punidad + $a + $b + $c + $d + $e + $f + $g;   
            
            if($total<>0){
                $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_ADD_BUDGET_DETAILS(
                                            :P_ID_PRESUPUESTO, 
                                            :P_ID_ACTIVIDAD, 
                                            :P_ID_ENTIDAD, 
                                            :P_ID_DEPTO,
                                            :P_ID_DEPTO_ASIENTO,
                                            :P_DESCRIPCION,
                                            :P_CANTIDAD,
                                            :P_PUNIDAD,
                                            :P_A,
                                            :P_B,
                                            :P_C,
                                            :P_D,
                                            :P_E,
                                            :P_F,
                                            :P_G); end;");
                $stmt->bindParam(':P_ID_PRESUPUESTO', $id_presupuesto, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ACTIVIDAD', $id_actividad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_ENTIDAD', $id_entidad, PDO::PARAM_INT);
                $stmt->bindParam(':P_ID_DEPTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_ID_DEPTO_ASIENTO', $id_depto, PDO::PARAM_STR);
                $stmt->bindParam(':P_DESCRIPCION', $descripcion, PDO::PARAM_STR);
                $stmt->bindParam(':P_CANTIDAD', $cantidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_PUNIDAD', $punidad, PDO::PARAM_STR);
                $stmt->bindParam(':P_A', $a, PDO::PARAM_STR);
                $stmt->bindParam(':P_B', $b, PDO::PARAM_STR);
                $stmt->bindParam(':P_C', $c, PDO::PARAM_STR);
                $stmt->bindParam(':P_D', $d, PDO::PARAM_STR);
                $stmt->bindParam(':P_E', $e, PDO::PARAM_STR);
                $stmt->bindParam(':P_F', $f, PDO::PARAM_STR);
                $stmt->bindParam(':P_G', $g, PDO::PARAM_STR);
                $stmt->execute();  
            }             
        }
        
        //distribucion
         $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_GEN_PRESUPUESTO_DET_DIST(
                                        :P_ID_PRESUPUESTO
                                        ); end;");
         $stmt->bindParam(':P_ID_PRESUPUESTO', $id_presupuesto, PDO::PARAM_INT);
           
         $stmt->execute(); 
        
    }
    public static function deleteBudgetDetail($id_presupuesto){
        $query = "DELETE FROM PSTO_PRESUPUESTO_DET
                WHERE ID_PRESUPUESTO = ".$id_presupuesto;
        DB::delete($query);
    }
    
    public static function estadoAsiento($id_asiento,$estado){
        
            
        
        $query = "UPDATE PSTO_ASIENTO SET ESTADO='".$estado."'
                WHERE ID_ASIENTO =".$id_asiento."";
        
        DB::delete($query);
    }
    public static function deleteAsiento($id_asiento){
        
        
        $query = "DELETE FROM PSTO_ASIENTO_DET
                WHERE ID_ASIENTO =".$id_asiento;
        DB::delete($query);
        
        
        $query = "DELETE FROM PSTO_ASIENTO
                WHERE ID_ASIENTO = ".$id_asiento;
        DB::delete($query);
        
                
    }
    
    public static  function addAsiento($id_presupuesto){
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_ASIENTO_PRESUPUESTO(:P_ID_PRESUPUESTO); end;");
        $stmt->bindParam(':P_ID_PRESUPUESTO', $id_presupuesto, PDO::PARAM_INT);
        $stmt->execute();  
    }
    public static  function validBudget($id_presupuesto){
        $error=0;
        $msgerror='';
        $err="";
        for($h=1;$h<=200;$h++){
            $err.="0";
        }
        $msgerror=$err;
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_VALIDA_PRESUPUESTO(:P_ID_PRESUPUESTO,:P_ERROR,:P_MSGERROR); end;");
        $stmt->bindParam(':P_ID_PRESUPUESTO', $id_presupuesto, PDO::PARAM_INT);
        $stmt->bindParam(':P_ERROR', $error, PDO::PARAM_INT);
        $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
        $stmt->execute();  
        
        if($msgerror==$err){
            $msgerror="0";
        }
        return $msgerror;
    }
    public static  function validGenBudget($id_evento,$detail){
        $nerror=0;
        $msgerror='';
        $err="";
        
        $total = 0;
        foreach($detail as $items){
            $cantidad=$items->cantidad;
            $punidad=$items->punidad;
            $a=$items->a;
            $b=$items->b;
            $c=$items->c;
            $d=$items->d;
            $e=$items->e;
            $f=$items->f;
            $g=$items->g;
         
            if(strlen($cantidad)==0){
                $cantidad =0;  
            }
            if(strlen($punidad)==0){
                $punidad =0;  
            }
            if(strlen($a)==0){
                $a =0;  
            }
            if(strlen($b)==0){
                $b=0;  
            }
            if(strlen($c)==0){
                $c=0;  
            }
            if(strlen($d)==0){
                $d=0;  
            }
            if(strlen($e)==0){
                $e=0;  
            }
            if(strlen($f)==0){
                $f=0;  
            }
            if(strlen($g)==0){
                $g=0;  
            }
        
            $importe =  $cantidad + $punidad + $a + $b + $c + $d + $e + $f + $g;
            
            $total = $total + $importe;
        }
        
        if($total<>0){
            for($h=1;$h<=200;$h++){
                $err.="0";
            }
            $msgerror=$err;
            $pdo = DB::getPdo();
            $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_VALIDA_GEN_PRESUPUESTO(:P_ID_EVETO,:P_ERROR,:P_MSGERROR); end;");
            $stmt->bindParam(':P_ID_EVETO', $id_evento, PDO::PARAM_INT);
            $stmt->bindParam(':P_ERROR', $nerror, PDO::PARAM_INT);
            $stmt->bindParam(':P_MSGERROR', $msgerror, PDO::PARAM_STR);
            $stmt->execute(); 
            
            $return=[
                'nerror'=>$nerror,
                'msgerror'=>$msgerror
            ];
        }else{
            $return=[
                'nerror'=>1,
                'msgerror'=>'Importe total de Presupuesto no debe se igual a 0(cero)'
            ]; 
        }
        
        
        return $return;
    }
    public static function addPresupuestoAsieto($id_presupuesto,$id_user){
        
        $error=0;
        $msgerror=" ";
        
               
        $pdo = DB::getPdo();
        $stmt = $pdo->prepare("begin PKG_PRESUPUESTO.SP_ASIENTO_PRESUPUESTO(
                                    :P_ID_PRESUPUESTO,
                                    :P_ID_PERSONA
                                    ); end;");
        $stmt->bindParam(':P_ID_PRESUPUESTO', $id_presupuesto, PDO::PARAM_INT);
        $stmt->bindParam(':P_ID_PERSONA', $id_user, PDO::PARAM_INT);
        $stmt->execute(); 
    }
    public static function listMyEvents($id_entidad,$id_anho,$id_depto){
        $query = "select
                c.id_evento,
                d.nombre as evento
                from PSTO_PRESUPUESTO a,PSTO_PRESUPUESTO_DET b,PSTO_ACTIVIDAD c, psto_evento d
                where a.ID_PRESUPUESTO=b.ID_PRESUPUESTO
                and b.ID_ACTIVIDAD=c.ID_ACTIVIDAD
                and c.ID_EVENTO=d.ID_EVENTO
                and a.id_entidad = ".$id_entidad."
                and a.ID_ANHO = ".$id_anho."
                and b.id_depto_asiento='".$id_depto."'
                group by c.id_evento,d.nombre ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listMyEventsActivities($id_entidad,$id_anho,$id_evento,$id_depto){
        $query = "select
                    c.id_evento,
                    d.nombre as evento,
                    b.id_actividad,
                    c.nombre as actividad,
                    b.id_depto_asiento,
                    a.id_anho
                    from PSTO_PRESUPUESTO a,PSTO_PRESUPUESTO_DET b,PSTO_ACTIVIDAD c, psto_evento d
                    where a.ID_PRESUPUESTO=b.ID_PRESUPUESTO
                    and b.ID_ACTIVIDAD=c.ID_ACTIVIDAD
                    and c.ID_EVENTO=d.ID_EVENTO
                    and a.id_entidad= ".$id_entidad."
                    and a.ID_ANHO = ".$id_anho."
                    and c.id_evento = ".$id_evento."
                    and b.id_depto_asiento='".$id_depto."' ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
    public static function listMyEventsActivitiesSearch($id_entidad,$id_anho,$id_depto,$actividad){
        $query = "select
                    c.id_evento,
                    d.nombre as evento,
                    b.id_actividad,
                    c.nombre as actividad,
                    b.id_depto_asiento,
                    a.id_anho,
                    b.total
                    from PSTO_PRESUPUESTO a,PSTO_PRESUPUESTO_DET b,PSTO_ACTIVIDAD c, psto_evento d
                    where a.ID_PRESUPUESTO=b.ID_PRESUPUESTO
                    and b.ID_ACTIVIDAD=c.ID_ACTIVIDAD
                    and c.ID_EVENTO=d.ID_EVENTO
                    and a.id_entidad= ".$id_entidad."
                    and a.ID_ANHO = ".$id_anho."
                    and b.id_depto ='".$id_depto."' 
                    and upper(c.nombre) like upper('%".$actividad."%') ";
        $oQuery = DB::select($query);        
        return $oQuery;
    }
}
?>

