<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;

class Kq2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kq2 = DB::select("select accepted.KQ2_ID_MEDIO_ACCESO, accepted.KQ2_ID_MEDIO_ACCESO_DES, accepted.MONTOA, accepted.TXSA, rejected.MONTOR, rejected.TXSR FROM 
        (select main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES, sum(main.MONTO1) AS MONTOA, count(*) as TXSA 
        from medioacceso as kq2 inner join test as main on kq2.KQ2_ID_MEDIO_ACCESO = main.KQ2_ID_MEDIO_ACCESO
        where main.CODIGO_RESPUESTA < '010' group by main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES) as accepted
        inner join
        (select main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES, sum(main.MONTO1) AS MONTOR, count(*) as TXSR 
        from medioacceso as kq2 inner join test as main on kq2.KQ2_ID_MEDIO_ACCESO = main.KQ2_ID_MEDIO_ACCESO
        where main.CODIGO_RESPUESTA >= '010' group by main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES) as rejected 
        on accepted.KQ2_ID_MEDIO_ACCESO = rejected.KQ2_ID_MEDIO_ACCESO ORDER BY accepted.KQ2_ID_MEDIO_ACCESO");
        $array = json_decode(json_encode($kq2), true); //Codificar un array asociativo
        $answer = array();
        foreach($array as $key => $data){
            $answer[$key] = new stdClass();
            $answer[$key] -> ID = $data['KQ2_ID_MEDIO_ACCESO'];
            $answer[$key] -> Description = $data['KQ2_ID_MEDIO_ACCESO_DES'];
        }
        $arrayJson = json_decode(json_encode($answer), true);
        return $arrayJson;
    }

    public function filterKq2(Request $request){

        $values = array();
        $labels = ['main.KQ2_ID_MEDIO_ACCESO', 'main.CODIGO_RESPUESTA', 'main.ENTRY_MODE', 'main.ID_COMER', 'main.TERM_COMER', 
        'main.FIID_COMER', 'main.FIID_TERM','main.LN_COMER', 'main.LN_TERM', 'main.FIID_TARJ', 'main.LN_TARJ'];
        
        $values[0] = $request -> kq2;
        $values[1] = $request -> codeResponse;
        $values[2] = $request -> entryMode;
        $values[3] = $request -> ID_Comer;
        $values[4] = $request -> Term_Comer;
        $values[5] = $request -> Fiid_Comer;
        $values[6] = $request -> Fiid_Term;
        $values[7] = $request -> Ln_Comer;
        $values[8] = $request -> Ln_Term;
        $values[9] = $request -> Fiid_Card;
        $values[10] = $request -> Ln_Card;  

        $array = array();
        $response = array();
        $arrayValues = array();
        $totalArrayValues = array();
        $answer = array();
        $totalTX = 0;
        //Query en caso de que no exista alg??n filtro
        $queryOutFilters = "select accepted.KQ2_ID_MEDIO_ACCESO, accepted.KQ2_ID_MEDIO_ACCESO_DES, accepted.MONTOA, accepted.TXSA, rejected.MONTOR, rejected.TXSR FROM 
        (select main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES, sum(main.MONTO1) AS MONTOA, count(*) as TXSA 
        from medioacceso as kq2 inner join test as main on kq2.KQ2_ID_MEDIO_ACCESO = main.KQ2_ID_MEDIO_ACCESO
        where main.CODIGO_RESPUESTA < '010' group by main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES) as accepted
        inner join
        (select main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES, sum(main.MONTO1) AS MONTOR, count(*) as TXSR 
        from medioacceso as kq2 inner join test as main on kq2.KQ2_ID_MEDIO_ACCESO = main.KQ2_ID_MEDIO_ACCESO
        where main.CODIGO_RESPUESTA >= '010' group by main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES) as rejected 
        on accepted.KQ2_ID_MEDIO_ACCESO = rejected.KQ2_ID_MEDIO_ACCESO";

        //Query modificado para obtener los valores decuerdo al filtro
        $firstQuery = "select accepted.KQ2_ID_MEDIO_ACCESO, accepted.KQ2_ID_MEDIO_ACCESO_DES, accepted.MONTOA, accepted.TXSA, rejected.MONTOR, rejected.TXSR FROM 
        (select main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES, sum(main.MONTO1) AS MONTOA, count(*) as TXSA 
        from medioacceso as kq2 inner join test as main on kq2.KQ2_ID_MEDIO_ACCESO = main.KQ2_ID_MEDIO_ACCESO
        where main.CODIGO_RESPUESTA < '010' and ";
        $secondQuery = " group by main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES) as accepted
        inner join
        (select main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES, sum(main.MONTO1) AS MONTOR, count(*) as TXSR 
        from medioacceso as kq2 inner join test as main on kq2.KQ2_ID_MEDIO_ACCESO = main.KQ2_ID_MEDIO_ACCESO
        where  main.CODIGO_RESPUESTA >= '010' and "; 
        $thirthQuery = " group by main.KQ2_ID_MEDIO_ACCESO, kq2.KQ2_ID_MEDIO_ACCESO_DES) as rejected 
        on accepted.KQ2_ID_MEDIO_ACCESO = rejected.KQ2_ID_MEDIO_ACCESO";

        //Eliminar los filtros que no han sido elegidos
        for($key = 0; $key < 11; $key++){
            if(empty($values[$key])){
                unset($values[$key]);
                unset($labels[$key]);
            }
        }
        $filteredValues = array_values($values);
        $filteredLabels = array_values($labels);

        if(empty($filteredValues)){
            $response = DB::select($queryOutFilters);
            $array = json_decode(json_encode($response), true);
        }else{
            if(count($filteredValues) <= 1){
                for($i = 0; $i < count($filteredValues); $i++){
                    for($j = 0; $j < count($filteredValues[$i]); $j++){
                        $response = array_merge($response, DB::select($firstQuery.$filteredLabels[$i]." = ?".
                        $secondQuery.$filteredLabels[$i]." = ?".$thirthQuery,
                        [$filteredValues[$i][$j], $filteredValues[$i][$j]]));
                    }
                }
                $array = json_decode(json_encode($response), true);
            }else{
                //Ingresar todos los valores elegidos en el filtro dentro de un solo arreglo. (Valores para la consulta)
                for($i = 0; $i < count($filteredValues); $i++){
                    for($j = 0; $j < count($filteredValues[$i]); $j++){
                        array_push($arrayValues, $filteredValues[$i][$j]);
                    }
                }
                $z = 1; //Variable 'controladora' de el largo del query
                //Constructor del query (Varias consultas al mismo tiempo)
                for($i = 0; $i < count($filteredValues); $i++){
                    for($j = 0; $j < count($filteredValues[$i]); $j++){
                        if($j == count($filteredValues[$i]) -1){
                            if($j == 0){
                                if($z == count($arrayValues)){
                                    $firstQuery .= "(".$filteredLabels[$i]." = ?)";
                                    $secondQuery .= "(".$filteredLabels[$i]." = ?)";
                                }else{
                                    $firstQuery .= "(".$filteredLabels[$i]." = ?) and ";
                                    $secondQuery .= "(".$filteredLabels[$i]." = ?) and ";
                                }
                                $z++;
                            }else{
                                if($z == count($arrayValues)){
                                    $firstQuery .= $filteredLabels[$i]." = ?)";
                                    $secondQuery .= $filteredLabels[$i]." = ?)";
                                    $z = 1;
                                }else{
                                    $firstQuery .= $filteredLabels[$i]." = ?) and ";
                                    $secondQuery .= $filteredLabels[$i]." = ?) and ";
                                    $z++;
                                }
                            }
                        }else{
                            if($j == 0){
                                $firstQuery .= "(".$filteredLabels[$i]." = ? or ";
                                $secondQuery .= "(".$filteredLabels[$i]." = ? or ";
                                $z++;
                            }else{
                                $firstQuery .= $filteredLabels[$i]." = ? or ";
                                $secondQuery .= $filteredLabels[$i]." = ? or ";
                                $z++;
                            }
                        }
                    }
                }
                //Consulta del query obtenido por los filtros y los valores elegidos
                //Se llena un nuevo arreglo con los valores obtenidos por el filtro pero duplicados,
                //esto para la ejecuci??n del query (se requiere duplicado de los datos)
                $totalArrayValues = array_values($arrayValues); 
                for($i = 0; $i < count($arrayValues); $i++){
                    array_push($totalArrayValues, $arrayValues[$i]);
                }
                $response = DB::select($firstQuery.$secondQuery.$thirthQuery, [...$totalArrayValues]);
                $array = json_decode(json_encode($response), true);
            }
        }

        foreach($array as $key => $data){
            $totalTX += $data['TXSA'] + $data['TXSR'];
        }

        foreach($array as $key => $data){
            $answer[$key] = new stdClass();
            $answer[$key] -> ID = $data['KQ2_ID_MEDIO_ACCESO'];
            $answer[$key] -> Description = $data['KQ2_ID_MEDIO_ACCESO_DES'];
            $answer[$key] -> TX_Accepted = number_format($data['TXSA']);
            $answer[$key] -> TX_Rejected = number_format($data['TXSR']);
            //Separaci??n decimal y entero de ambos montos
            $decAccepted = substr($data['MONTOA'], strlen($data['MONTOA'])-2, 2);
            $intAccepted = substr($data['MONTOA'], 0, strlen($data['MONTOA'])-2);
            $answer[$key] -> accepted_Amount = '$'.number_format($intAccepted.".".$decAccepted, 2);
            $decRejected = substr($data['MONTOR'], strlen($data['MONTOR'])-2, 2);
            $intRejected = substr($data['MONTOR'], 0, strlen($data['MONTOR'])-2);
            $answer[$key] -> rejected_Amount = '$'.number_format($intRejected.".".$decRejected, 2);
            $answer[$key] -> percenTX_Accepted = round((($data['TXSA'] / $totalTX) * 100), 2).'%';
            $answer[$key] -> percenTX_Rejected = round((($data['TXSR'] / $totalTX) * 100), 2).'%';
        }
        $arrayJson = json_decode(json_encode($answer), true);
        return $arrayJson;
    }
}
