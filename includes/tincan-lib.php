<?php

namespace TinCan;

class ExtendedRemoteLRS extends RemoteLRS {
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


