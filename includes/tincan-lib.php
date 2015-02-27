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

### tincan-lib.php
Extends TinCanPHP's RemoteLRS class to provide additional functions relating to Tin Can. 
Some of these functions are specific to the Tin Can Open Badges crossover. 

*/

namespace TinCan;

class ExtendedRemoteLRS extends RemoteLRS {
    /*
    Method used to retrieve a full Activity Object from the Activity Profile API. 
    This generic function should be added to TinCanPHP
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
            $response->content = new Activity(json_decode($response->content, true));
        }

        return $response;
    }

    /*
    Method used to query Statements. This is a modified version of queryStatements.
    See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/RemoteLRS.php#L345
    This version allows options to be passed through to the sendRequest method. 

    @method queryStatementsWithOptions 
    @param {Object} $query A query object as expected by the queryStatementsRequestParams function
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/RemoteLRS.php#L306
    @param {Object} $options An Options object as expected by the sendRequest function
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/RemoteLRS.php#L66
    @return {Object} $reponse The LRSResponse object returned. 
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/LRSResponse.php
    */
    public function queryStatementsWithOptions($query, $options) {
        $requestCfg = array(
            'params' => $this->_queryStatementsRequestParams($query),
        );

        $response = $this->sendRequest('GET', 'statements', $requestCfg, $options);

        if ($response->success) {
            $this->_queryStatementsResult($response);
        }

        return $response;
    }

    public function getStatementsWithUniqueActivitiesFromStatementQuery ($queryCFG, $options){
        $queryStatementsResponse = $this->queryStatements($queryCFG, $options);

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
            if (verifyBadgeStatement($queryStatement)["success"]) {
                $thisId = $queryStatement->getObject()->getId();
                if (!isset($unqiueStatements[$thisId])){
                    $unqiueStatements[$thisId] = $queryStatement;
                }
            }
        }

        return $unqiueStatements;
    }

    public function getBadgeClassesInLRS ()
    {
        //Note: a real system might additionally by authority
        $queryCFG = array(
            "verb" => new \TinCan\Verb(array("id"=> "http://standard.openbadges.org/xapi/verbs/created-badge-class.json")),
            "activity" => new \TinCan\Activity(array("id"=> "http://standard.openbadges.org/xapi/recipe/base/0")),
            "related_activities" => "true",
            //"limit" => 1, //Use this to test the "more" statements feature
            "format"=>"canonical",
            "attachments"=>"false"
        );

        $options = array(
            'headers' => array(
                'Accept-language: ' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] . ', *'
            )
        );

        return $this->getStatementsWithUniqueActivitiesFromStatementQuery($queryCFG, $options);
    }
}


