<?php

/*
### badges-lib.php
Functions relating to baking and signing of badges. 
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