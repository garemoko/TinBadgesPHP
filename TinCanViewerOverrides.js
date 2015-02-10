TINCAN.Viewer.prototype.renderStatements = function (statements) {
    var allStmtStr,
        i,
        dt,
        aDate,
        stmtStr,
        stmt,
        verb,
        objDesc,
        answer,
        activityType;


    function truncateString (str, length) {
        if (str === null || str.length < 4 || str.length <= length) {
            return str;
        }
        return str.substr(0, length - 3) + '...';
    }

    function getResponseText (stmt) {
        var response,
            objDef,
            componentName = null,
            components,
            responses,
            responseStr = [],
            first = true,
            responseId,
            i,
            j,
            source,
            target,
            responseParts;

        if (stmt.result === null || stmt.result.response === null) {
            return "";
        }
        response = stmt.result.response;

        if (stmt.target === null ||
            stmt.target.objectType !== "Activity" ||
            stmt.target.definition === null ||
            stmt.target.definition.type !== "cmi.interaction" ||
            stmt.target.definition.interactionType === null
        ) {
            return response;
        }
        objDef = stmt.target.definition;

        // TODO: move the splitting on [,] of the values into TinCanJS
        if (objDef.interactionType === "matching") {
            if (objDef.source !== null &&
                objDef.source.length > 0 &&
                objDef.target !== null &&
                objDef.target.length > 0
            ) {
                source = objDef.source;
                target = objDef.target;

                responses = response.split("[,]");

                for (i = 0; i < responses.length; i += 1) {
                    responseParts = responses[i].split("[.]");

                    for (j = 0; j < source.length; j += 1) {
                        if (responseParts[0] === source[j].id) {
                            if (!first) {
                                responseStr.push(", ");
                            }
                            responseStr.push(source[j].getLangDictionaryValue("description"));
                            first = false;
                        }
                    }
                    for (j = 0; j < target.length; j += 1) {
                        if (responseParts[1] === target[j].id) {
                            responseStr.push(" -> ");
                            responseStr.push(target[j].getLangDictionaryValue("description"));
                        }
                    }
                }
            }
        } else {
            if (objDef.interactionType === "choice" || objDef.interactionType === "sequencing") {
                componentName = "choices";
            }
            else if (objDef.interactionType === "likert") {
                componentName = "scale";
            }
            else if (objDef.interactionType === "performance") {
                componentName = "steps";
            }

            if (componentName !== null) {
                components = objDef[componentName];

                if (components !== null && components.length > 0){
                    responses = response.split("[,]");

                    for (i = 0; i < responses.length; i += 1) {
                        for (j = 0; j < components.length; j += 1) {
                            responseId = responses[i];
                            if (objDef.interactionType === "performance"){
                                responseId = responses[i].split("[.]")[0];
                            }
                            if (responseId === components[j].id) {
                                if (!first) {
                                    responseStr.push(", ");
                                }
                                responseStr.push(components[j].getLangDictionaryValue("description"));

                                if (objDef.interactionType === "performance") {
                                    responseStr.push(" -> ");
                                    responseStr.push(responses[i].split("[.]")[1]);
                                }
                                first = false;
                            }
                        }
                    }
                }
            }
        }

        if (responseStr.length > 0) {
            return responseStr.join("");
        }

        return response;
    }

    function displayBagde(statementId){
        return function(badgePNG) {
          //display image
          console.log(badgePNG);
          $("[tcid='" + statementId + "']").append("<img class='open-badge-50' src='data:image/png;base64," + badgePNG + "' />");
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
                if (activityType !== null && (activityType === "question" || activityType.indexOf("interaction") >= 0)) {
                    if (stmt.result !== null) {
                        if (stmt.result.success !== null) {
                            verb = (stmt.result.success ? "correctly " : "incorrectly ") + verb;
                        }
                        if (stmt.result.response !== null) {
                            answer = " with response '" + this.escapeHTML(truncateString(getResponseText(stmt), 30)) + "' ";
                        }
                    }
                }
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
                $.each(rawStatementObj.attachments, function(index, attachment){
                    if (attachment.usageType == "http://standard.openbadges.org/xapi/attachment/badge.json"){
                        //TODO: validate the image type is image/PNG
                        //Try to get image from content (via PHP as attachments not supported by TinCanJS yet)
                        $.get( "resources/attached-badge.php?statement=" + stmt.id, displayBagde(stmt.id))
                          .fail(function() {
                            //TODO: if content not found, try fileurl if present
                          })
                    }
                })
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