<?php

/*
### stream.php
Displays a stream of badge statements including the badge image and signed statement verification. 
*/

/*
TODO: I had to remove JQuery UI from the viewer as it didn't work with bootstrap; I now need to make the statement 
collpase work again and generally make the page look nicer.

TODO: display images attached to statements

TODO: display an icon indicating whether or not the statement has been signed and if the signature has been verified. 

*/
?>

<h3>Badge Stream</h3>
<p>
    All statements about badges for all users are shown below. 
</p>

<script src="TinCanStatementViewer/scripts/jquery-1.6.3.min.js"></script>
<script src="TinCanStatementViewer/scripts/tabs.js"></script>
<script src="TinCanStatementViewer/scripts/base64.js"></script>
<script src="TinCanJS/build/tincan-min.js"></script>
<script src="TinCanStatementViewer/scripts/TinCanQueryUtils.js"></script>
<script src="TinCanStatementViewer/scripts/TinCanViewer.js"></script>

<div id="searchBox" style="display: none;">
    <input type="hidden" id="version" value="<?php echo $CFG->version ?>" />
    <input type="hidden" id="agentProperty" value="mbox" />
    <input id="agentValue" type="hidden" value="">
    <input id="verb1" type="hidden" value="">
    <input id="activityId1" type="hidden" value="http://standard.openbadges.org/xapi/recipe/base/0">
    <input id="format" type="hidden" value="exact">
    <input id="since1" type="hidden" value="">
    <input id="until1" type="hidden" value="">
    <input id="registration1" type="hidden" value="">
    <input id="relatedAgents" type="hidden">
    <input id="relatedActivities" type="checkbox" checked>

    <textarea readonly="true" id="TCAPIQueryText"></textarea>

</div>
<div id='theStatements'></div>
<div id='statementsLoading'>
    <img src="TinCanStatementViewer/img/loading.gif" alt="Loading">
</div>
<button id='showAllStatements'>More...</button>


<script>
    function Config() {
        "use strict";
    }
    Config.endpoint = "<?php echo $CFG->endpoint ?>";
    Config.authUser = "<?php echo $CFG->readonly_login ?>";
    Config.authPassword = "<?php echo $CFG->readonly_pass ?>";
    Config.actor = { "mbox":["<?php echo $userEmail; ?>"], "name":["<?php echo $userName ?>"] };

    $(document).ready(function(){
        TC_VIEWER = new TINCAN.Viewer();
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

        $("#version").change(
            function (e) {
                var version = $(e.target.options[e.target.selectedIndex]).val(),
                    searchBoxTable = $("#searchBoxTable"),
                    advancedSearchTable = $("#advancedSearchTable"),
                    searchBoxTable1 = $("#searchBoxTable1"),
                    advancedSearchTable1 = $("#advancedSearchTable1");

                if (version === "0.9" || version === "0.95" || version === "0.95 + 0.9") {
                    if (searchBoxTable1.is(":visible")) {
                        searchBoxTable1.toggle("slow");
                        searchBoxTable.toggle("slow");

                        if (advancedSearchTable1.is(":visible")) {
                            advancedSearchTable1.toggle("slow");
                            advancedSearchTable.toggle("slow");
                        }
                    }
                }
                else {
                    if (searchBoxTable.is(":visible")) {
                        searchBoxTable.toggle("slow");
                        searchBoxTable1.toggle("slow");

                        if (advancedSearchTable.is(":visible")) {
                            advancedSearchTable.toggle("slow");
                            advancedSearchTable1.toggle("slow");
                        }
                    }
                }

                doRefresh();
            }
        );

        $("#showAdvancedOptions").click(
            function () {
                var version = $("#version").val(),
                    node;

                if (version === "0.9" || version === "0.95" || version === "0.95 + 0.9") {
                    node = $("#advancedSearchTable");
                }
                else {
                    node = $("#advancedSearchTable1");
                }

                node.toggle(
                    'slow',
                    function () {
                        var visible = node.is(":visible"),
                            text = (visible ? "Hide" : "Show") + " Advanced Options";

                        $("#showAdvancedOptions").html(text);
                    }
                );
            }
        );

        $("#showQuery").click(
            function () {
                $("#TCAPIQuery").toggle(
                    'slow',
                    function () {
                        var visible = $("#TCAPIQuery").is(":visible"),
                            text = (visible ? "Hide" : "Show") + " TCAPI Query";
                        $("#showQuery").html(text);
                    }
                );
            }
        );

        TC_VIEWER.searchStatements();
    });
</script>