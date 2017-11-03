<?php

function read_client_data(){
    $socket = fopen("php://input","r");
    $client_data = "";

    while($data = fread($socket,1024)){
        $client_data .= $data;
    }

    fclose($socket);

    return $client_data;
}