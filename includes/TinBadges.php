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

### TinBadges.php
Extends TinCanPHP to provide additional functions. 
Some of these functions are specific to the Tin Can Open Badges crossover, others are more generic. 

*/

namespace TinBadges;

class RemoteLRS extends \TinCan\RemoteLRS {
    /*
    Method used to retrieve a full Activity Object from the Activity Profile API. 
    This generic function should be added to TinCanPHP.
    See: https://github.com/RusticiSoftware/TinCanPHP/issues/24

    @method retrieveFullActivityObject 
    @param {String} $activityid The activity id for the activity to retrieve
    @return {Object} $reponse The LRSResponse object returned. 
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/LRSResponse.php
    */
    public function retrieveFullActivityObject($activityid) {
        $response = $this->sendRequest(
            'GET',
            'activities',
            array(
                'params' => array(
                    'activityId' => $activityid,
                ),
                'ignore404' => true,
                'headers' => array(
                    'Accept-language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . ', *'
                )
            )
        );

        if ($response->success) {
            $response->content = new \TinCan\Activity(json_decode($response->content, true));
        }

        return $response;
    }

    /*
    Method used to query Statements. This is a modified version of queryStatements.
    See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/RemoteLRS.php#L345
    This version allows options to be passed through to the sendRequest method. 

    /*
    Method used to query Statements, but only returns the most recent statement about each activity returned
    by the LRS; older statements abotu each activity are removed. 
    This function also cycles through the more links to get a complete set of statements. 

    @method getStatementsWithUniqueActivitiesFromStatementQuery 
    @param {Object} $query A query object as expected by the queryStatementsRequestParams function
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/RemoteLRS.php#L306
    @param {Object} $options An Options object as expected by the sendRequest function
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/RemoteLRS.php#L66
    @return {Array} $unqiueStatements An array of Statement objects.
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/Statement.php
    */
    public function getStatementsWithUniqueActivitiesFromStatementQuery($query) {
        $baker = new Baker();

        $queryStatementsResponse = $this->queryStatements($query);

        $queryStatements = $queryStatementsResponse->content->getStatements();
        $moreStatementsURL = $queryStatementsResponse->content->getMore(); 

        while (!is_null($moreStatementsURL)){
            $moreStatementsResponse = $lrs->moreStatements($moreStatementsURL);
            $moreStatements = $moreStatementsResponse->content->getStatements();
            $moreStatementsURL = $moreStatementsResponse->content->getMore(); 

            //Note: due to the structure of the arrays, array_merge does not work as expected. 
            foreach ($moreStatements as $moreStatement){ 
                array_push($queryStatements, $moreStatement);
            }
        }

        //get the most recent earn of each badge
        $unqiueStatements = array();
        foreach ($queryStatements as $queryStatement){
            if ($baker->verifyBadgeStatement($queryStatement)["success"]) {
                $thisId = $queryStatement->getObject()->getId();
                if (!isset($unqiueStatements[$thisId])){
                    $unqiueStatements[$thisId] = $queryStatement;
                }
            }
        }

        return $unqiueStatements;
    }

    /*
    Method used to fecth a list of all canonical 'created-badge-class' statements in order to list all the badge
    classes stored in the LRS. Only returns the most recent statement about each badge class maatching the verb. 

    @method getBadgeClassesInLRS 
    @return {Array} $unqiueStatements An array of Statement objects.
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/Statement.php
    */
    public function getBadgeClassesInLRS() {
        //Note: a real system might additionally by authority
        $queryCFG = array(
            "verb" => new \TinCan\Verb(array("id"=> "http://standard.openbadges.org/xapi/verbs/created-badge-class.json")),
            "activity" => new \TinCan\Activity(array("id"=> "http://standard.openbadges.org/xapi/recipe/base/0")),
            "related_activities" => "true",
            //"limit" => 1, //Use this to test the "more" statements feature
            "format"=>"canonical",
            "attachments"=>"false",
            'headers' => array(
                'Accept-language: ' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] . ', *'
            )
        );

        return $this->getStatementsWithUniqueActivitiesFromStatementQuery($queryCFG);
    }

    //Private function from TinCanPHP - modified
    private function _queryStatementsRequestParams($query) {
        $result = array();

        foreach (array('agent') as $k) {
            if (isset($query[$k])) {
                $result[$k] = json_encode($query[$k]->asVersion($this->version));
            }
        }
        foreach (
            array(
                'verb',
                'activity',
            ) as $k
        ) {
            if (isset($query[$k])) {
                $result[$k] = $query[$k]->getId();
            }
        }
        foreach (
            array(
                'ascending',
                'related_activities',
                'related_agents',
                'attachments',
            ) as $k
        ) {
            if (isset($query[$k])) {
                $result[$k] = $query[$k] ? 'true' : 'false';
            }
        }
        foreach (
            array(
                'registration',
                'since',
                'until',
                'limit',
                'format',
                'headers', //+
                'params', //+
            ) as $k
        ) {
            if (isset($query[$k])) {
                $result[$k] = $query[$k];
            }
        }

        return $result;
    }

}

class Util extends \TinCan\Util {

    /* 
    determine which language out of an available set the user prefers most 
    
    @method prefered_language returns the client's preferred language from a list of options. 
    @param $available_languages {Array} list of language-tag-strings (must be lowercase) that are available 
    @param $http_accept_language {String} a HTTP_ACCEPT_LANGUAGE string (read from $_SERVER['HTTP_ACCEPT_LANGUAGE'] if left out) 
    */ 
    public function prefered_language($available_languages,$http_accept_language="auto") { 
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

    /* 
    return the most appropriate option from a language map for the current client
    
    @method getAppropriateLanguageMapValue returns the client's preferred language from a list of options. 
    @param $map {Object} Language map object to select a language from.
    @return {String} String of text in the client's preferred language.
    */ 
    public function getAppropriateLanguageMapValue($map){ //TODO: validate its not an empty map
        return $map[$this->prefered_language(array_keys($map))];
    }
}

/*
    Class containing functions related to Open Badges.
*/

class Baker {
    /*
    function to bake a given assertion into a given image. Uses bakerlib.php.

    @param $imageURL {String} URL of the source image to bake the Badge from.
    @param $assertion {Object} Assertion object to bake into the Badge. 
    @return {PNG image} Baked Open Badge.
    */
    public function bake($imageURL, $assertion){
        $sourcePNG = file_get_contents($imageURL);

        $metadatahandler = new \PNG_MetaDataHandler($sourcePNG);

        if ($metadatahandler->check_chunks("tEXt", "openbadge")) {//TODO: use iTXt (not currently supported by our library) instead
            return $metadatahandler->add_chunks("tEXt", "openbadges", json_encode($assertion, JSON_UNESCAPED_SLASHES));
        } else {
            //TODO: error - there's a problem with the input image. Is this already a baked Open Badge? It should be a normal png.
        }
    }

    /*
    Function used to translate a Tin Can Statement into an Open Badges Assertion. 

    @function statementToAssertion 
    @param {Object} Statement Object
    @return {Object} $assertion The OB assertion based on the statement
    */
    public function statementToAssertion($statement){
        global $CFG;
        //TODO: validate that the actor uses mbox
            //TODO: support other IFIs

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

    /*
    Function used to translate a Tin Can Statement into an Open Badges Assertion. 

    @function verifyBadgeStatement 
    @param {Object} Statement Object
    @return {Object} $verifyResponse results of verification
    */
    public function verifyBadgeStatement($statement){
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
}


