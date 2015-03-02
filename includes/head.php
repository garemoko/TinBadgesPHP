<!--
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
-->

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tin Badges PHP Prototype</title>
    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
      .panel { padding-bottom: 20px; }

      .panel-default {
        border-left:solid 3px #f05023;
        border-bottom:solid 3px #f05023;
        border-right:solid 1px #f05023;
        border-top:solid 1px #f05023;
      }

      #theStatements table { width: 100%; }

      .statement {margin:3px 0;}

      .statement table {width:100%;}
      .statementRow td {vertical-align:middle;padding:2px;}
      .statementRow td.date{width:140px;}

      .statementRow .date {font-size:.7em;color:#666666;}
      .statement .actor {color:#222222;}
      .statement .verb {font-weight:bold;}
      .statement .object {color:#222222;}
      .statement .score {font-weight:bold;}

      .statement {cursor:pointer;}
      .tc_rawdata {display:none; font-size:.8em; width:800px; word-wrap:break-word;}

      #statementsLoading { padding-left:20px; padding-top:10px; display:none; }
      #showAllStatements { display:none; margin-top:10px;}

      .open-badge-150 {height: 150px; width:150px;}
      .open-badge-100 {height: 100px; width:100px; margin:1px;}
      .open-badge-50 {height: 50px; width:50px; margin:1px;}
      .earn-btn {clear:both;margin:10px auto 0 auto;}

    </style>
  </head>
  <body>
    <div class="container">
        <h1>Tin Badges PHP Prototype</h1>
