<?php
include __DIR__ . './form.html';

$login = "turnir.moscow";
$amount = floatval(8000.00);
$pwd1 = "zK4gs3Bjf15wHRxBxg3y";
$signature = md5($login . ":" . $amount . ":" . $pwd1);

?>

    <input type="hidden" name= "MrchLogin"  value="turnir.moscow" />
    <input type="hidden" name="OutSum" value="8000" />
    <input type="hidden" name="Description" value="Certificate from Ratabor" />
    <input type="hidden" name="SignatureValue" value=<?php echo $signature;?> />
