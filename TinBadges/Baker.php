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

### TinBadges/Baker.php
Open Badges helper class.

*/

namespace TinBadges;

class Baker
{
    /*
    function to bake a given assertion into a given image. Uses bakerlib.php.

    @param $imageURL {String} URL of the source image to bake the Badge from.
    @param $assertion {Object} Assertion object to bake into the Badge. 
    @return {PNG image} Baked Open Badge.
    */
    public function bake($imageURL, $assertion)
    {
        $sourcePNG = file_get_contents($imageURL);

        $metadatahandler = new \PNG_MetaDataHandler($sourcePNG);

        if ($metadatahandler->check_chunks("tEXt", "openbadge")) {
        //TODO: use iTXt (not currently supported by our library) instead
            return $metadatahandler->add_chunks("tEXt", "openbadges", json_encode($assertion, JSON_UNESCAPED_SLASHES));
        } else {
            //TODO: error - there's a problem with the input image.
                //Is this already a baked Open Badge? It should be a normal png.
        }
    }

    /*
    Function used to translate a Tin Can Statement into an Open Badges Assertion. 

    @function statementToAssertion 
    @param {Object} Statement Object
    @return {Object} $assertion The OB assertion based on the statement
    */
    public function statementToAssertion($statement)
    {
        global $CFG;
        //TODO: validate that the actor uses mbox
            //TODO: support other IFIs

        $assertion = array(
            "uid" => $statement->getId(),
            "recipient" => array(
                "identity" => 'sha256$' . hash('sha256', substr($statement->getActor()->getMbox(), 7) . $CFG->badge_salt),
                "type" => "email",
                "hashed" => true,
                "salt" => $CFG->badge_salt
            ),
            "badge" => $statement->getObject()->getDefinition()->getExtensions()
                ->asVersion("1.0.0")["http://specification.openbadges.org/xapi/extensions/badgeclass.json"]["@id"],
            "verify" => array(
                "type" => "hosted",
                "url" => $statement->getResult()->getExtensions()
                    ->asVersion("1.0.0")["http://specification.openbadges.org/xapi/extensions/badgeassertion.json"]["@id"],
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
    public function verifyBadgeStatement($statement)
    {
        try {
            $certLocation = $statement->getContext()->getExtensions()
                ->asVersion("1.0.0")["http://id.tincanapi.com/extension/jws-certificate-location"];
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
