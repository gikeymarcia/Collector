<?php
    $tools = getTools();
    $logoutUrl = $_PATH->get('root') . '/Admin/logout.php';
    
    $options = '';
    
    if ($tool === false) {
        $options .= '<option selected disabled hidden value="">Choose a tool</option>';
    }
    
    foreach ($tools as $toolOption) {
        $selected = ($tool === $toolOption) ? 'selected' : '';
        $path     = "$rootUrl/Admin/Tools/$toolOption";
        
        $options .= "<option $selected value='$path'>$toolOption</option>";
    }
    
?>
<div id="ToolsNavBar">
    <h1><?= $title ?></h1>
    <a id="LogOut" href="<?= $logoutUrl ?>">Logout</a>
    <select name="CollectorToolSelection" class="collectorInput" id="CollectorToolSelection">
        <?= $options ?>
    </select>
</div>
<?php
    unset($tools, $logoutUrl, $options, $toolOption, $selected);
