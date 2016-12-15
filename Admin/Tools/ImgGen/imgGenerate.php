<?php
    ob_start();
    
    if (!isset($_POST['data'], $_POST['yAxis'])) {
        echo 'missing data';
        exit;
    }
    
    $data = json_decode($_POST['data'], true);
    $yAxisLabel = $_POST['yAxis'];
    
    $imgDir   = 'imgs';
    $filename = md5($_POST['data'] . $_POST['yAxis']);
    $filePath = "$imgDir/$filename.jpg";
    
    if (file_exists($filePath)) {
        echo $filePath;
        exit;
    }
    
    
    
    /* * * * * * * * * * * *
     * Image Specifications
     */
    
    $imgHeight       = 400;
    $xSizeForEachCol = 80;
    $colWidth        = 50;
    $imgPadding      = 20;
    
    $legendLeftMargin  = 20;
    $legendBorderWidth = 2;
    $legendPadding     = 10;
    $legendLineHeight  = 20;
    
    $colLabelHeight = 15;
    
    
    
    /* * * * * * * * * * * * * * * * * * *
     * Calculate Data Ranges/Proportions
     */
    
    // get the range of the column heights, including error bars
    $barHeightMin = $barHeightMax = 0;
    
    foreach ($data as $colData) {
        if (!is_array($colData)) exit; // bad input
        
        $barHeightMax = max($barHeightMax, $colData['height'] + $colData['error']);
        $barHeightMin = min($barHeightMin, $colData['height'] - $colData['error']);
    }
    
    $barHeightRange = $barHeightMax - $barHeightMin;
    
    if ($barHeightRange === 0) exit;
    
    // calculate what the y-axis step size should be
    $stepSize = 1;
    
    while ($barHeightRange/$stepSize > 10) {
        $stepSize *= 10;
    }
    
    while ($barHeightRange/$stepSize < 1) {
        $stepSize /= 10;
    }
    
    $stepSize *= ceil($barHeightRange/$stepSize) / 10;
    
    $yAxisStart = 0;
    while ($yAxisStart > $barHeightMin) {
        $yAxisStart -= $stepSize;
    }
    
    $stepCount = 0;
    while ($yAxisStart + $stepSize*$stepCount < $barHeightMax) {
        ++$stepCount;
    }
    
    $yAxisEnd = $yAxisStart + $stepCount * $stepSize;
    
    $axisRange = $yAxisEnd - $yAxisStart;
    
    // calculate how much room we need for the Y-axis step labels
    $yAxisStepLabelWidth = 0;
    
    for ($i=0; $i<=$stepCount; ++$i) {
        $label = (string) ($yAxisStart + $i*$stepSize);
        $len   = strlen($label);
        $width = $len * 7;
        $yAxisStepLabelWidth = max($yAxisStepLabelWidth, $width);
    }
    
    $yAxisLabelWidth = 30;
    $yAxisStepWidth  = 5;
    
    
    $xSizeOfYAxis = $yAxisLabelWidth + $yAxisStepLabelWidth + 5 + $yAxisStepWidth;
    
    $pxRatioBeforeGraphPadding = $axisRange / ($imgHeight - 2*$imgPadding);
    
    // calculate area for column labels
    if (($yAxisStart + $yAxisEnd) > 0) {
        $putColLabelsBelow = true;
    } else {
        $putColLabelsBelow = false;
    }
    
    $graphBottomPadding = $graphTopPadding = 0;
    
    if ($putColLabelsBelow) {
        $colNameSpace = ($barHeightMin - $yAxisStart)/$pxRatioBeforeGraphPadding;
    } else {
        $colNameSpace = ($yAxisEnd - $barHeightMax)/$pxRatioBeforeGraphPadding;
    }
    
    if ($colNameSpace < $colLabelHeight) {
        $diff = $colLabelHeight - $colNameSpace;
        
        if ($putColLabelsBelow) {
            $graphBottomPadding = $diff;
        } else {
            $graphTopPadding = $diff;
        }
    }
    
    $pxRatio = $axisRange / ($imgHeight - 2*$imgPadding - $graphBottomPadding - $graphTopPadding);
    
    // calculate legend area
    $maxGroupNameLen = 0;
    foreach (array_keys($data) as $groupName) {
        $maxGroupNameLen = max($maxGroupNameLen, strlen($groupName));
    }
    
    $maxGroupNameLen += strlen((count($data)+1) . ': ');
    
    $legendTextWidth   = $maxGroupNameLen * 8;
    $legendTextHeight  = count($data)*$legendLineHeight;
    
    $xSizeForLegend = $legendLeftMargin
                    + 2*$legendBorderWidth
                    + 2*$legendPadding
                    + $legendTextWidth;
    
    $imgWidth = $xSizeOfYAxis + $xSizeForEachCol * count($data) + 2*$imgPadding + $xSizeForLegend;
    
    
    
    /* * * * * * * * *
     * Image Creation
     */
    
    $img = imagecreatetruecolor($imgWidth, $imgHeight);
    $colWhite = imagecolorallocate($img, 255, 255, 255);
    $colBlack = imagecolorallocate($img, 0,   0,   0);
    $colGray  = imagecolorallocate($img, 190, 190, 190);
    
    imagefill($img, 1, 1, $colWhite);
    imagerectangle($img, 0, 0, $imgWidth-1, $imgHeight-1, $colBlack);
    
    #### Graph
    $graphX1 = $imgPadding;
    $graphX2 = $graphX1 + $xSizeOfYAxis + $xSizeForEachCol * count($data);
    $graphY1 = $imgPadding + $graphTopPadding;
    $graphY2 = $imgHeight - $imgPadding - $graphBottomPadding;
    
    #### Y-axis
    // label Y-Axis
    $yAxisLabelY = ($graphY1 + $graphY2)/2 + strlen($yAxisLabel)*4.5;
    $yAxisLabelX = $graphX1;
    imagestringup($img, 5, $yAxisLabelX, $yAxisLabelY, $yAxisLabel, $colBlack);
    
    // draw the steps in the y-axis
    for ($i=0; $i<=$stepCount; ++$i) {
        $x1 = $graphX1 + $xSizeOfYAxis - $yAxisStepWidth;
        $y1 = $graphY2 - $i*$stepSize/$pxRatio;
        $x2 = $x1 + $yAxisStepWidth;
        $y2 = $y1;
        imageline($img, $x1, $y1, $x2, $y2, $colBlack);
        
        $stepLabel = $yAxisStart + $i*$stepSize;
        $labelWidth = strlen($stepLabel)*7;
        $labelOffset = $x1 - 5 - $labelWidth;
        imagestring($img, 3, $labelOffset, $y1-7, $stepLabel, $colBlack);
    }
    
    // draw the y-axis
    $x = $graphX1 + $xSizeOfYAxis;
    imageline($img, $x, $graphY1, $x, $graphY2, $colBlack);
    
    #### X-axis
    $xAxisX = $graphX1 + $xSizeOfYAxis;
    $xAxisY = $graphY2 - abs($yAxisStart)/$pxRatio;
    imageline($img, $xAxisX, $xAxisY, $graphX2, $xAxisY, $colBlack);
    
    #### Columns
    $colCount = 0;
    
    if ($putColLabelsBelow) {
        $colLabelY = $graphY1 + ($yAxisEnd - $barHeightMin)/$pxRatio + 5;
    } else {
        $colLabelY = $graphY1 + ($yAxisEnd - $barHeightMax)/$pxRatio - $colLabelHeight - 5;
    }
    
    foreach ($data as $category => $colData) {
        ## Column
        $colPadding = ($xSizeForEachCol - $colWidth) / 2;
        $colX1 = $xAxisX + $colCount*$xSizeForEachCol + $colPadding;
        $colX2 = $colX1 + $colWidth;
        $colY1 = $xAxisY - $colData['height']/$pxRatio;
        $colY2 = $xAxisY;
        $minX  = min($colX1, $colX2);
        $minY  = min($colY1, $colY2);
        
        if ($colData['height'] !== 0) {
            imagerectangle($img, $colX1, $colY1, $colX2, $colY2, $colBlack);
            imagefill($img, $minX+1, $minY+1, $colGray);
        }
        
        ## Error Bars
        if ($colData['error'] !== 0) {
            $errorBarX  = ($colX1 + $colX2) / 2;
            $errorBarY1 = $colY1 - $colData['error']/$pxRatio;
            $errorBarY2 = $colY1 + $colData['error']/$pxRatio;
            
            imageline($img, $errorBarX,   $errorBarY1, $errorBarX,   $errorBarY2, $colBlack);
            imageline($img, $errorBarX-5, $errorBarY1, $errorBarX+5, $errorBarY1, $colBlack);
            imageline($img, $errorBarX-5, $errorBarY2, $errorBarX+5, $errorBarY2, $colBlack);
        }
        
        ## ColName
        $colNum    = $colCount + 1;
        $colNumLen = strlen($colNum);
        $colX      = ($colX1 + $colX2)/2;
        $colLabelX = $colX - $colNumLen*3.5;
        imagestring($img, 4, $colLabelX, $colLabelY, $colNum, $colBlack);
        
        ## Cleanup/Advance
        ++$colCount;
    }
    
    #### Legend
    $legendX1 = $imgWidth - $imgPadding - $xSizeForLegend + $legendLeftMargin;
    $legendX2 = $legendX1 + $xSizeForLegend - $legendLeftMargin;
    $legendY1 = $imgPadding;
    $legendY2 = $legendY1 + 2*$legendBorderWidth + 2*$legendPadding + $legendTextHeight;
    
    for ($i=0; $i<$legendBorderWidth; ++$i) {
        imagerectangle($img, $legendX1+$i, $legendY1+$i, $legendX2-$i, $legendY2-$i, $colBlack);
    }
    
    $legendTextX = $legendX1 + $legendBorderWidth + $legendPadding;
    $legendTextY = $legendY1 + $legendBorderWidth + $legendPadding;
    
    $colNumFormat = '%\'. ' . strlen(count($data)+1) . 'd';
    
    foreach (array_keys($data) as $i => $groupName) {
        $colNum = sprintf($colNumFormat, $i+1);
        $text   = "$colNum: $groupName";
        imagestring($img, 4, $legendTextX, $legendTextY + $i*$legendLineHeight, $text, $colBlack);
    }
    
    
    
    /* * * * * * * * * * *
     * Save Image to File
     */
    
    if (error_get_last() === null) {
        if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);
        imagejpeg($img, $filePath);
        
        echo $filePath;
    } else {
        $error_messages = ob_get_contents();
        ob_end_clean();
        echo 'Error: ';
        echo $error_messages;
    }
    
