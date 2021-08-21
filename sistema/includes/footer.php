<?php
    session_start();
    $permanece = '';      
    if($_SESSION['stay']) {
        $permanece = '   |   Permanece activo';
    }

?>

<div class="div-spacer"></div>
<footer>
    <span><small><?php echo fechaC().$permanece; ?></small></span>
</footer>