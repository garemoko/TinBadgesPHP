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
                    'Accept-language:' . $_SERVER['HTTP_ACCEPT_LANGUAGE']
                )
            )
        );

        if ($response->success) {
            $response->content = new Activity(json_decode($response->content, true));
        }

        return $response;
    }
}