<?php
if( $_SERVER['REQUEST_METHOD']=='POST' && !empty( $_POST['task'] ) && $_POST['task']=='check' ){
    ob_clean();

    $result=null;

    function curl( $url=NULL, $options=NULL, $headers=false ){
        /* Initialise curl request object */
        $curl=curl_init();

        /* Define standard options */
        curl_setopt( $curl, CURLOPT_URL,trim( $url ) );
        curl_setopt( $curl, CURLOPT_AUTOREFERER, true );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $curl, CURLOPT_FAILONERROR, true );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLINFO_HEADER_OUT, false );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_BINARYTRANSFER, true );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 20 );
        curl_setopt( $curl, CURLOPT_TIMEOUT, 60 );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36' );
        curl_setopt( $curl, CURLOPT_MAXREDIRS, 10 );
        curl_setopt( $curl, CURLOPT_ENCODING, '' );

        /* Assign runtime parameters as options */
        if( isset( $options ) && is_array( $options ) ){
            foreach( $options as $param => $value ) curl_setopt( $curl, $param, $value );
        }

        if( $headers && is_array( $headers ) ){
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
        }

        /* Execute the request and store responses */
        $res=(object)array(
            'response'  =>  curl_exec( $curl ),
            'info'      =>  (object)curl_getinfo( $curl ),
            'errors'    =>  curl_error( $curl )
        );
        curl_close( $curl );
        return $res;
    }






    function checkvat( $code, $vatnumber, $timeout=30 ){
        $url='http://ec.europa.eu/taxation_customs/vies/services/checkVatService';

        $content = "<s11:Envelope xmlns:s11='http://schemas.xmlsoap.org/soap/envelope/'>
                <s11:Body>
                    <tns1:checkVat xmlns:tns1='urn:ec.europa.eu:taxud:vies:services:checkVat:types'>                                        
                        <tns1:countryCode>%s</tns1:countryCode>
                        <tns1:vatNumber>%s</tns1:vatNumber>
                    </tns1:checkVat>
                </s11:Body>
            </s11:Envelope>";

        $headers=array(
            'Content-Type'  =>  'text/xml; charset=utf-8',
            'SOAPAction'    =>  'checkVatService'
        );
        $options=array(
            CURLOPT_POST        =>  true,
            CURLOPT_POSTFIELDS  =>  sprintf ( $content, $code, $vatnumber )
        );
        return curl( $url, $options, $headers );
    }








    $code=$_POST['code'];
    $vatnumber=$_POST['vat'];

    /* check the VAT number etc */
    $obj=checkvat( $code, $vatnumber );

    /* if we received a valid response, process it */
    if( $obj->info->http_code==200 ){

        $dom=new DOMDocument;
        $dom->loadXML( $obj->response );

        $reqdate=$dom->getElementsByTagName('requestDate')->item(0)->nodeValue;
        $valid=$dom->getElementsByTagName('valid')->item(0)->nodeValue;
        $address=$dom->getElementsByTagName('address')->item(0)->nodeValue;

        $result=sprintf( 'VAT Number "%s" in Country-Code "%s" - Date: %s, Valid: %s, Address: %s', $vatnumber, $code, $reqdate, $valid, $address );
    }

    exit( $result );
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8' />
    <title>VAT checker</title>
    <script>

        const ajax=function( url, params, callback ){
            let xhr=new XMLHttpRequest();
            xhr.onload=function(){
                if( this.status==200 && this.readyState==4 )callback( this.response )
            };
            xhr.open( 'POST', url, true );
            xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
            xhr.send( buildparams( params ) );
        };

        const buildparams=function(p){/* construct payload/querystring from object */
            if( p && typeof( p )==='object' ){
                p=Object.keys( p ).map(function( k ){
                    return typeof( p[ k ] )=='object' ? buildparams( p[ k ] ) : [ encodeURIComponent( k ), encodeURIComponent( p[ k ] ) ].join('=')
                }).join('&');
            }
            return p;
        };




        document.addEventListener('DOMContentLoaded', ()=>{
            let form=document.forms.registration;
            form.bttn.addEventListener('click', e=>{
                let url=location.href;
                let params={
                    'task':'check',
                    'vat':form.vat.value,
                    'code':form.code.value
                };
                let callback=function(r){
                    document.querySelector('pre').innerHTML=r
                }
                ajax.call( this, url, params, callback );
            })
        });
    </script>
</head>
<body>
<form method='post' name='registration'>
    <!-- lots of other form elements -->


    <!--
        The details in the form (vat-number) is bogus
        and will not return live data...
    -->
    <label for='vat'><input type='text' name='vat' value='10758820' /></label>
    <label for='code'><input type='text' name='code' value='GB' /></label>
    <input type='button' value='Check VAT' name='bttn' />


</form>
<pre></pre>
</body>
</html>
