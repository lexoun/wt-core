<?php

$ico = $_REQUEST['ico'];

if(!empty($ico)){

    $handler = curl_init('https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty-res/'.$ico);
    curl_setopt($handler, CURLOPT_RETURNTRANSFER,1);
    $data = curl_exec($handler);
    curl_close($handler);

    if($data){
        $request = json_decode($data, true);
    }

//print_r($request);

    if($request)
    {

        if ($request['zaznamy']['0']['ico'] == $ico)
        {
            $result['ico'] = $request['zaznamy']['0']['ico'];
            $result['dic'] = 'CZ'.$request['zaznamy']['0']['ico'];
            $result['company'] = $request['zaznamy']['0']['obchodniJmeno'] != NULL ? $request['zaznamy']['0']['obchodniJmeno'] : '';
            $result['street'] = $request['zaznamy']['0']['sidlo']['nazevUlice'] != NULL ? $request['zaznamy']['0']['sidlo']['nazevUlice'] : '';
            $result['cp1']   = $request['zaznamy']['0']['sidlo']['cisloDomovni' != NULL ? $request['zaznamy']['0']['sidlo']['cisloDomovni'] : ''];
            $result['cp2']   = $request['zaznamy']['0']['sidlo']['cisloOrientacni'] != NULL ? $request['zaznamy']['0']['sidlo']['cisloOrientacni'] : '';
            if($result['cp2'] != ""){ $result['cp'] = $result['cp1']."/".$result['cp2']; }else{ $result['cp'] = $result['cp1']; }
            $result['city'] = $request['zaznamy']['0']['sidlo']['nazevObce'];
            $result['zipcode']  = $request['zaznamy']['0']['sidlo']['psc'] != NULL ? $request['zaznamy']['0']['sidlo']['psc'] : '';
            $result['status'] = 1;
        }
        else
        {
            $result['status']  = 'NotFound';
        }
    }
    else
    {
        $result['status']  = 'ARESUnavailable';
    }

    echo json_encode($result);

}