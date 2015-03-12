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

### TinBadges/Util.php
Extends TinCanPHP's Util class to include language preferrence functions.

*/

namespace TinBadges;

class Util extends \TinCan\Util
{

    /*
    determine which language out of an available set the user prefers most 
    
    @method preferedLanguage returns the client's preferred language from a list of options. 
    @param $available_languages {Array} list of language-tag-strings (must be lowercase) that are available 
    @param $http_accept_language {String} a HTTP_ACCEPT_LANGUAGE string 
        (read from $_SERVER['HTTP_ACCEPT_LANGUAGE'] if left out) 
    */
    public function preferedLanguage($available_languages, $http_accept_language = "auto")
    {
        // if $http_accept_language was left out, read it from the HTTP-Header
        if ($http_accept_language == "auto") {
            $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        }

        preg_match_all(
            "/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?" .
            "(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i",
            $http_accept_language,
            $hits,
            PREG_SET_ORDER
        );

        // default language (in case of no hits) is the first in the array
        $bestlang = $available_languages[0];
        $bestqval = 0;

        foreach ($hits as $arr) {
            // read data from the array of this hit
            $langprefix = strtolower($arr[1]);
            if (!empty($arr[3])) {
                $langrange = $arr[3];
                $language = $langprefix . "-" . $langrange;
            } else {
                $language = $langprefix;
            }
            $qvalue = 1.0;
            if (!empty($arr[5])) {
                $qvalue = floatval($arr[5]);
            }

            // find q-maximal language
            if (in_array($language, $available_languages) && ($qvalue > $bestqval)) {
                $bestlang = $language;
                $bestqval = $qvalue;
            } elseif (in_array($langprefix, $available_languages) && (($qvalue*0.9) > $bestqval)) {
                // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does)
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
    public function getAppropriateLanguageMapValue($map)
    {
        //TODO: validate its not an empty map
        return $map[$this->preferedLanguage(array_keys($map))];
    }
}
