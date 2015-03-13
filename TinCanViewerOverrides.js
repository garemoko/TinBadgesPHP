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


### TinCanViewerOverrides.js
Overrides to functions within the TinCanStatementViewer submodules to:

1. Allow the statement viewer to display correctly within the frame of this prototype.
2. Remove the configuration settings and replace with fixed configuration settings
suitable for this prototype.
3. Add additional functionality such as the display of Badge images and Statement signature
verification results. 
4. Remove some code for handling parts of statements that don't feature in the Open Badges recipe
*/

TINCAN.Viewer.prototype.renderStatements = function (statements) {
    "use strict";
    var allStmtStr,
        i,
        stmtStr,
        stmt,
        verb,
        answer,
        activityType,
        rawStatementObj;

    function displayBadge(statementId){
        return function(badgePNG) {
          //display image
            var statmentObj = $("[tcid='" + statementId + "']");
            var imgHTML = "<img class='open-badge-50' src='data:image/png;base64," + badgePNG + "' />";
            if (statmentObj.has( ".verify-label" ).length){
                statmentObj.children( ".verify-label" ).before(imgHTML);
            } else {
                statmentObj.append(imgHTML);
            }
        };
    }

    function renderVerification(statementId){
        //TODO: render a revealable raw certifcate box using encodeURIComponent(verifyResult.cert)
        return function(verifyResult) {
            if (verifyResult.success) {
                var verifyLabel = $("<span class='label label-success verify-label'>Signature Verified: </span>"),
                verifyURLLabel = $("<span class='label label-default'>"+ verifyResult.certLocation + "</span>");
                $("[tcid='" + statementId + "']").append(verifyLabel);
                $("[tcid='" + statementId + "']").append(verifyURLLabel);
                $("[tcid_data='" + statementId + "']").append("<pre>"+verifyResult.cert+"</pre>");
            } else {
                $("[tcid='" + statementId + "']").append("<span class='label label-danger verify-label'>Invalid Signature</span>");
            }
        };
    }

    function renderUnableToVerify(statementId){
        //TODO: render a revealable raw certifcate box using verifyResult.cert
        return function() {
            $("[tcid='" + statementId + "']").append("<span class='label label-warning verify-label'>Unable to verify signature.</span>");
        };
    }

    function processAttachment(index, attachment){
        if (attachment.usageType === "http://standard.openbadges.org/xapi/attachment/badge.json"){
            //TODO: validate the image type is image/PNG
            //Try to get image from content (via PHP as attachments not supported by TinCanJS yet)
            $.get( "resources/attached-badge.php?statement=" + stmt.id, displayBadge(stmt.id))
              .fail(function() {
                //TODO: if content not found, try fileurl if present
              });
        } else if (attachment.usageType === "http://adlnet.gov/expapi/attachments/signature"){
            $.get( "resources/verify-signed-statement.php?statement=" + stmt.id, renderVerification(stmt.id))
              .fail(renderUnableToVerify(stmt.id));
        }
    }

    allStmtStr = [];
    allStmtStr.push("<table>");

    for (i = 0; i < statements.length; i += 1) {
        stmtStr = [];
        stmt = statements[i];
        //this.log("-------------------------------" + stmt.id + "-------------------------------");

        stmtStr.push("<tr class='statementRow'>");
        stmtStr.push("<td class='date'><div class='statementDate'>" + (stmt.stored !== null ? stmt.stored.replace('Z','') : "") + "</div></td>");
        stmtStr.push("<td>");
        stmtStr.push("<div class='statement unwired' tcid='" + stmt.id + "'>");

        try {
            stmtStr.push(
                "<span class='actor'>" + 
                    (stmt.actor !== null ? this.renderActor(stmt.actor) : "No Actor") + 
                "</span> ");

            if (stmt.context !== null &&
                stmt.context.extensions !== null &&
                typeof stmt.context.extensions.verb !== "undefined"
            ) {
                verb = stmt.context.extensions.verb;
            } else {
                verb = stmt.verb + "";
            }

            if (verb === "interacted") {
                verb = "interacted with";
            } else if (stmt.inProgress === true) {
                verb = verb + " (in progress)";
            }

            answer = null;

            if (typeof stmt.target.definition !== "undefined" && stmt.target.definition !== null) {
                activityType = stmt.target.definition.type;
            }

            stmtStr.push(" <span class='verb'>" + this.escapeHTML(verb) + "</span> ");
            stmtStr.push(" <span class='object'>'" + this.escapeHTML(stmt.target) + "'</span> ");
            stmtStr.push(answer !== null ? answer : "");

            if (stmt.result !== null && stmt.result.score !== null) {
                if (stmt.result.score.scaled !== null) {
                    stmtStr.push(" with score <span class='score'>" + Math.round((stmt.result.score.scaled * 100.0)) + "%</span>");
                } else if (stmt.result.score.raw !== null) {
                    stmtStr.push(" with score <span class='score'>" + stmt.result.score.raw + "</span>");
                }
            }
            rawStatementObj = JSON.parse(stmt.originalJSON); //Until TinCanJS supports attachments
            if (rawStatementObj.hasOwnProperty("attachments") && rawStatementObj.attachments !== {}){
                $.each(rawStatementObj.attachments, processAttachment);
            }
        }
        catch (error) {
            this.log("Error occurred while trying to display statement with id " + stmt.id + ": " + error.message);
            //this.log("-------------------------------" + stmt.id + "-------------------------------");
            stmtStr.push("<span class='stId'>" + stmt.id + "</span>");
        }
        stmtStr.push("</div>");

        if (this.includeRawData) {
            stmtStr.push("<div class='tc_rawdata' tcid_data='" + stmt.id + "'>");
            stmtStr.push("<pre>" + stmt.originalJSON + "</pre>");
            stmtStr.push("</div>");
        }

        stmtStr.push("</td></tr>");
        allStmtStr.push(stmtStr.join(''));
        //this.log("-------------------------------" + stmt.id + "-------------------------------");
    }
    allStmtStr.push("</table>");

    return allStmtStr.join('');
};

TINCAN.Viewer.prototype.pageInitialize = function () {
    "use strict";
    var tcViewer = this,
    doRefresh = function () {
        $("#statementsLoading").show();
        $("#showAllStatements").hide();
        $("#noStatementsMessage").hide();
        $("#theStatements").empty();
        tcViewer.searchStatements();
    };

    $("#statementsLoading").show();
    $("#showAllStatements").hide();
    $("#noStatementsMessage").hide();

    $("#refreshStatements").click(doRefresh);

    $("#showAllStatements").click(
        function () {
            $("#statementsLoading").show();
            TC_VIEWER.getMoreStatements();
        }
    );
};