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

### TinBadges/RemoteLRS.php
Extends TinCanPHP's RemoteLRS to provide additional functions. 
Some of these functions are specific to the Tin Can Open Badges crossover, others are more generic. 

*/

namespace TinBadges;

class RemoteLRS extends \TinCan\RemoteLRS
{
    /*
    Method used to retrieve a full Activity Object from the Activity Profile API. 
    This generic function should be added to TinCanPHP.
    See: https://github.com/RusticiSoftware/TinCanPHP/issues/24

    @method retrieveFullActivityObject 
    @param {String} $activityid The activity id for the activity to retrieve
    @return {Object} $reponse The LRSResponse object returned. 
        See https://github.com/RusticiSoftware/TinCanPHP/blob/master/src/LRSResponse.php
    */
    public function retrieveFullActivityObject($activityid)
    {
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
    public function getStatementsWithUniqueActivitiesFromStatementQuery($query)
    {
        $baker = new Baker();

        $queryStmtsResponse = $this->queryStatements($query);

        $queryStatements = $queryStmtsResponse->content->getStatements();
        $moreStatementsURL = $queryStmtsResponse->content->getMore();

        while (!is_null($moreStatementsURL)) {
            $moreStmtsResponse = $this->moreStatements($moreStatementsURL);
            $moreStatements = $moreStmtsResponse->content->getStatements();
            $moreStatementsURL = $moreStmtsResponse->content->getMore();

            //Note: due to the structure of the arrays, array_merge does not work as expected.
            foreach ($moreStatements as $moreStatement) {
                array_push($queryStatements, $moreStatement);
            }
        }

        //get the most recent earn of each badge
        $unqiueStatements = array();
        foreach ($queryStatements as $queryStatement) {
            if ($baker->verifyBadgeStatement($queryStatement)["success"]) {
                $thisId = $queryStatement->getObject()->getId();
                if (!isset($unqiueStatements[$thisId])) {
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
    public function getBadgeClassesInLRS()
    {
        //Note: a real system might additionally by authority
        $queryCFG = array(
            "verb" => new \TinCan\Verb(
                array(
                    "id"=> "http://standard.openbadges.org/xapi/verbs/created-badge-class.json"
                )
            ),
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
    private function _queryStatementsRequestParams($query)
    {
        $result = array();

        foreach (array('agent') as $k) {
            if (isset($query[$k])) {
                $result[$k] = json_encode($query[$k]->asVersion($this->version));
            }
        }
        foreach (array('verb','activity') as $k) {
            if (isset($query[$k])) {
                $result[$k] = $query[$k]->getId();
            }
        }
        foreach (array(
            'ascending','
            related_activities',
            'related_agents',
            'attachments'
            ) as $k) {
            if (isset($query[$k])) {
                $result[$k] = $query[$k] ? 'true' : 'false';
            }
        }
        foreach (array(
                'registration',
                'since',
                'until',
                'limit',
                'format',
                'headers', //+
                'params', //+
            ) as $k) {
            if (isset($query[$k])) {
                $result[$k] = $query[$k];
            }
        }

        return $result;
    }
}
