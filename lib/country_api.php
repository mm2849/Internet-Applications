<?php

function search_name($name){
    $data = ["mode" => "country", "name" => $name];
    $endpoint = "https://basic-country-city-information.p.rapidapi.com/?mode=country&code=DEU";
    $isRapidAPI = true;
    $rapidAPIHost = "basic-country-city-information.p.rapidapi.com";
    $result = get($endpoint, "COUNTRY_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
    if (isset($result["country"])) {
        $quote = $result["country"];
        $quote = array_reduce(
            array_keys($quote),
            function ($temp, $key) use ($quote) {
                $k = explode(" ", $key)[0];
                
                $temp[$k] = str_replace('km2', '', $quote[$key]);
                return $temp;
            }
        );
        $result = [$quote];
        
    }
    
    return $result;
}