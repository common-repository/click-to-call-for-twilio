<?php
/*
TWT outbound script
*/
$agentNumber = $_GET['agentNumber'];
$welcome = urldecode($_GET['welcome']);
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <?php 
    if( $welcome){
        echo '<Say>'. $welcome.'</Say>';
    }
    ?>
    <Dial>
        <Number url="screen_for_machine.php">
            <?php echo $agentNumber; ?>
        </Number>
    </Dial>
    <Say>Goodbye.</Say>
</Response>
