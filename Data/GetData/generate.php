<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    session_start();
    if( !isset( $_SESSION['LoggedIn'] ) ) {
        header('Location: '.dirname('http://'.dirname( $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'] ) ).'/' );
        exit;
    }
    $root = '../../';
    require 'scan.php';
    
    $templateDirectory = 'SearchTemplates/';
    
    if (isset($_POST['searchTemplate']))
    {
        if (!is_dir($templateDirectory)) {
            mkdir($templateDirectory);
        }
        file_put_contents($templateDirectory . $_POST['searchTemplate'].'.JSON', json_encode($_POST));
    }
    
    $getExp = isset( $_POST['Experiment'] );
    
    $outputs        = isset( $_POST['Outputs'] )        ? $_POST['Outputs']     : array();
    $getExperiments = isset( $_POST['Experiments'] )    ? $_POST['Experiments'] : array();
    $getSessions    = isset( $_POST['Sessions'] )       ? $_POST['Sessions']    : array();
    $getConditions  = isset( $_POST['Conditions'] )     ? $_POST['Conditions']  : array();
    $getTrialTypes  = isset( $_POST['TrialTypes'] )     ? $_POST['TrialTypes']  : array();
    $getTimings     = isset( $_POST['Max Time'] )       ? $_POST['Max Time']    : array('numeric', 'nonnumeric');
    
    $getTrials = $_POST['Trials'] !== '' ? array_flip( RangeToArray( $_POST['Trials'] ) ) : false;
    
    // $completion = $_POST['Completion'];
    
    
    $outputColumns          = (isset($_POST['Columns'])                 AND isset($_POST['Experiment']))        ? array_flip( $_POST['Columns'] )                   : array();
    $statusBeginColumns     = (isset($_POST['Status_Begin_Columns'])    AND isset($_POST['Status_Begin']))      ? array_flip( $_POST['Status_Begin_Columns'] )      : array();
    $statusEndColumns       = (isset($_POST['Status_End_Columns'])      AND isset($_POST['Status_End']))        ? array_flip( $_POST['Status_End_Columns'] )        : array();
    $finalQuestionsColumns  = (isset($_POST['Final_Questions_Columns']) AND isset($_POST['Final_Questions']))   ? array_flip( $_POST['Final_Questions_Columns'] )   : array();
    $demographicsColumns    = (isset($_POST['Demographics_Columns'])    AND isset($_POST['Demographics']))      ? array_flip( $_POST['Demographics_Columns'] )      : array();
    $instructionsColumns    = (isset($_POST['Instructions_Columns'])    AND isset($_POST['Instructions']))      ? array_flip( $_POST['Instructions_Columns'] )      : array();
    
    /**
     * Preparing the Filters
     *
     * The filters are going to be prepared by creating an associative array.
     * The key of these filters will be the Column to look in, in the output files.
     * The value matched with this key will be an array of allowed values.
     * I like isset() more than in_array(), so the values will be the keys of their array.
     *
     * If we are not filtering this category, we simply won't create the array with that key
     */
    $fileFilters = array();
    if(isset( $_POST['Experiments'] ))  { $fileFilters['ExperimentName']    = array_flip( $_POST['Experiments'] ); }
    if(isset( $_POST['Conditions'] ))   { $fileFilters['Condition Number']  = array_flip( $_POST['Conditions'] ); }
    if(isset( $_POST['Sessions'] ))     { $fileFilters['Sessions']          = array_flip( $_POST['Sessions'] ); }
    if(isset( $_POST['Sessions'] ))     { $fileFilters['Sessions']          = array_flip( $_POST['Sessions'] ); }
    
    $rowFilters = array();
    if(isset( $_POST['TrialTypes'] ))   { $rowFilters['Procedure*Trial Type']   = array_flip( $_POST['TrialTypes'] ); }
    if( $_POST['Trials'] !== '' )       { $rowFilters['Trial']                  = array_flip( RangeToArray( $_POST['Trials'] ) ); }
    
    $getTimings = array_flip($getTimings);      // we will need to do special checks for this filter
    
    
    $allColumns = AddPrefixToArray( $expPrefix, $outputColumns )
                + AddPrefixToArray( $demographicsPrefix, $demographicsColumns )
                + AddPrefixToArray( $finalQuestionsPrefix, $finalQuestionsColumns ) 
                + AddPrefixToArray( $statusBeginPrefix, $statusBeginColumns ) 
                + AddPrefixToArray( $statusEndPrefix, $statusEndColumns ) 
                + AddPrefixToArray( $instructionsPrefix, $instructionsColumns );
    
    $ext = $_POST['File_Type'];
    if( $ext === 'browser' ) {
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="getdataStyling.css" rel="stylesheet" type="text/css" />
    <link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
    <script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>
    <title>Get Data</title>
</head>
<body>
    <table id="GetDataTable">
        <thead>
        <?php
        arrayToEcho( array_keys($allColumns), 'th' );
        ?>
        </thead>
        <tbody>
        <?php
    
    } else {
        $fileName = 'Data_'.date( 'm.d.y' ).'.'.$ext;
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$fileName);
        header("Content-Type: text/csv"); 
        header("Content-Transfer-Encoding: binary");
        arrayToEcho( array_keys($allColumns), $ext );
    }
    
    $path = $root.$dataF.$nonDebugF.$outputF;
    // $testHeader is set in scan.php, default 'Username'
    foreach( $users as $exps ) {
        foreach( $exps as $conds ) {
            foreach( $conds as $sessS ) {
                foreach( $sessS as $ids ) {
                    foreach( $ids as $id => $fileName ) {
                        
                        $output = array();
                        foreach( $extraFileMeta as $category => $fileMeta ) {
                            if( !isset( $IDs[$id][$category] ) ) {
                                continue;
                            } elseif( getArrayDims( $IDs[$id][$category] ) > 1 ) {
                                $temp = array();
                                foreach( $IDs[$id][$category] as $data ) {
                                    foreach( $data as $key => $value ) {
                                        $temp[$key][] = $value;
                                    }
                                }
                                foreach( $temp as &$col ) {
                                    $col = implode( '|', $col );
                                }
                                unset( $col );
                                $output += AddPrefixToArray( $fileMeta['Prefix'], $temp );
                            } else {
                                $output += AddPrefixToArray( $fileMeta['Prefix'], $IDs[$id][$category] );
                            }
                        }
                        
                        if( !$getExp ) {
                            arrayToEcho( SortArrayLikeArray($output, $allColumns), $ext );
                        } else {
                            $first = getFirstLine( $path.$fileName, $testHeader );
                            foreach( $fileFilters as $column => $allowed ) {
                                if( !isset( $allowed[ $first[$column] ] ) ) { continue 2; }
                            }
                            $d = getFirstLine( $path.$fileName, $testHeader, true );
                            $file = fopen( $path.$fileName, "r" );
                            $keys = fgetcsv($file, 0, $d);
                            while( ($line = fgetcsv($file, 0, $d)) !== false ) {
                                $row = array_combine_safely( $keys, $line );
                                foreach( $rowFilters as $column => $allowed ) {
                                    if( !isset( $allowed[ $row[$column] ] ) ) { continue; }
                                }
                                arrayToEcho( SortArrayLikeArray($output + AddPrefixToArray( $expPrefix, $row ), $allColumns), $ext );
                            }
                            fclose($file);
                        }
                        
                        
                    }
                }
            }
        }
    }
    
    if( $ext === 'browser' ) {
        ?>
        </tbody>
    </table>
    <script>
    var staticH = $("thead tr");
    var columns = staticH.children().length;
    $("thead").append('<tr class="thFreeze">'+staticH.html()+'</tr>');
    var frozenH = $(".thFreeze");
    for( var i=1; i<= columns; ++i ) {
        frozenH.children(":nth-child("+i+")").children().width( staticH.children(":nth-child("+i+")").children().width() );
    }
    $(window).scroll(function () {
        $('.thFreeze').css("left", parseInt($("body").css("margin-left"))-$(window).scrollLeft()+"px");
    });
    $.fn.hasScrollBar = function() {
        return this.get(0).scrollHeight > this.get(0).clientHeight;
    }
    var mouseX;
    var mouseY;
    $(document).mousemove( function(e) {
        mouseX = Math.min( window.innerWidth - e.pageX - 5, window.innerWidth - $(window).scrollLeft() - $(".GenTooltip").width() - 25 ); 
        mouseY = Math.min( e.pageY+5, window.innerHeight + $(window).scrollTop() - $(".GenTooltip").height() - 25 );
       $(".GenTooltip").css("top",mouseY+"px").css("right",mouseX+"px");
    });  
    $('th > div, td > div').filter(function() {
        return $(this).hasScrollBar();
    } ).hover(
        function() {
            $( "body" ).append( $( "<span class='GenTooltip' style='top: "+mouseY+"px; right: "+mouseX+"px'>"+this.innerHTML+"</span>" ) );
        },
        function() {
            $( "body" ).find( "span:last" ).remove();
        }
    );
    </script>
        <?php
    }
    
?>