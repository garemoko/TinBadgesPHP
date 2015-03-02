<?php
/*
Copyright 2015 Rustici Software

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

### badges-lib.php
Functions relating to baking and signing of badges. 
*/


/*
Function used to translate a Tin Can Statement into an Open Badges Assertion. 

@function statementToAssertion 
@param {Object} $statement Statement object to be translated.
    See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/Statement.php
@return {Object} $assertion The LRSResponse object returned. 
*/
function statementToAssertion($statement){
    global $CFG;

    //TODO: validate that the actor uses mbox
    // TODO: support other IFIs

    $assertion = array(
        "uid" => $statement->getId(),
        "recipient" => array(
            "identity" => 'sha256$' . hash('sha256', substr($statement->getActor()->getMbox(),7) . $CFG->badge_salt),
            "type" => "email",
            "hashed" => true,
            "salt" => $CFG->badge_salt
        ),
        "badge" => $statement->getObject()->getDefinition()->getExtensions()->asVersion("1.0.0")["http://standard.openbadges.org/xapi/extensions/badgeclass.json"]["@id"],
        "verify" => array(
            "type" => "hosted",
            "url" => $statement->getResult()->getExtensions()->asVersion("1.0.0")["http://standard.openbadges.org/xapi/extensions/badgeassertion.json"]["@id"],
        ),
        "issuedOn" => $statement->getTimestamp()
        //TODO: (optional) "evidence" =>,
    );
    return $assertion;
}

function bakeBadge($imageURL, $assertion){
    $sourcePNG = file_get_contents($imageURL);

    $metadatahandler = new PNG_MetaDataHandler($sourcePNG);

    if ($metadatahandler->check_chunks("tEXt", "openbadge")) {//TODO: use iTXt (not currently supported by our library) instead
        return $metadatahandler->add_chunks("tEXt", "openbadges", json_encode($assertion, JSON_UNESCAPED_SLASHES));
    } else {
        //TODO: error - there's a problem with the input image. Is this already a baked Open Badge? It should be a normal png.
    }
}

function verifyBadgeStatement($statement){
    try {
        $certLocation = $statement->getContext()->getExtensions()->asVersion("1.0.0")["http://id.tincanapi.com/extension/jws-certificate-location"];
    } catch (Exception $exception) {
        return array(
            "success" => false, 
            "reason" => 'Certificate not found.'
        );
    }
    try {
        $certRaw = file_get_contents($certLocation);
        $cert = openssl_pkey_get_public(openssl_x509_read($certRaw));

        $verifyResponse = $statement->verify(
            array(
                "publicKey" => $cert
            )
        );
        $verifyResponse["cert"] = $certRaw;
        $verifyResponse["certLocation"] = $certLocation;
        return $verifyResponse;
    } catch (Exception $exception) {
        return array(
            "success" => false, 
            "reason" => 'Unknown error verifying certificate: '. $exception->getMessage()
        );
    }
}

/* 
  determine which language out of an available set the user prefers most 

  $available_languages     array with language-tag-strings (must be lowercase) that are available 
  $http_accept_language    a HTTP_ACCEPT_LANGUAGE string (read from $_SERVER['HTTP_ACCEPT_LANGUAGE'] if left out) 
*/ 
function prefered_language ($available_languages,$http_accept_language="auto") { 
    // if $http_accept_language was left out, read it from the HTTP-Header 
    if ($http_accept_language == "auto") $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : ''; 

    preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" . 
                   "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i", 
                   $http_accept_language, $hits, PREG_SET_ORDER); 

    // default language (in case of no hits) is the first in the array 
    $bestlang = $available_languages[0]; 
    $bestqval = 0; 

    foreach ($hits as $arr) { 
        // read data from the array of this hit 
        $langprefix = strtolower ($arr[1]); 
        if (!empty($arr[3])) { 
            $langrange = $arr[3]; 
            $language = $langprefix . "-" . $langrange; 
        } 
        else $language = $langprefix; 
        $qvalue = 1.0; 
        if (!empty($arr[5])) $qvalue = floatval($arr[5]); 

        // find q-maximal language  
        if (in_array($language,$available_languages) && ($qvalue > $bestqval)) { 
            $bestlang = $language; 
            $bestqval = $qvalue; 
        } 
        // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does) 
        else if (in_array($langprefix,$available_languages) && (($qvalue*0.9) > $bestqval)) { 
            $bestlang = $langprefix; 
            $bestqval = $qvalue*0.9; 
        } 
    } 
    return $bestlang; 
} 

function getAppropriateLanguageMapValue ($map){ //TODO: validate its not an empty map
    return $map[prefered_language(array_keys($map))];
}