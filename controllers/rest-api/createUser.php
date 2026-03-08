<?php
///////////////////////////////////////////////////////////////////////////////////
//                                                                               //
// This is using a sample local WordPress Install and is not production safe     //
// It uses the  REST and Basic Auth plugins                                      //
//                                                                               //
///////////////////////////////////////////////////////////////////////////////////
// setup user name and password
$username = 'admin';
$password = 'password';
// the standard end point for posts in an initialised Curl
$process = curl_init('http://blogimport.dev/wp-json/wp/v2/posts');
// create an array of data to use, this is basic - see other examples for more complex inserts
$data = array('slug' => 'rest_insert' , 'title' => 'REST API insert' , 'content' => 'The content of our stuff', 'excerpt' => 'smaller' );
$data_string = json_encode($data);
// create the options starting with basic authentication
curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($process, CURLOPT_TIMEOUT, 30);
curl_setopt($process, CURLOPT_POST, 1);
// make sure we are POSTing
curl_setopt($process, CURLOPT_CUSTOMREQUEST, "POST");
// this is the data to insert to create the post
curl_setopt($process, CURLOPT_POSTFIELDS, $data_string);
// allow us to use the returned data from the request
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
// we are sending json
curl_setopt($process, CURLOPT_HTTPHEADER, array(                                                                          
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
);
// process the request
$return = curl_exec($process);
curl_close($process);
// This buit is to show you on the screen what the data looks like returned and then decoded for PHP use
echo '<h2>Results</h2>';
print_r($return);
echo '<h2>Decoded</h2>';
$result = json_decode($return, true);
print_r($result);