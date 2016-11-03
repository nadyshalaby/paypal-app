<?php

require 'src/start.php';

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>paypal</title>
    </head>
    <body>
        <?php if ($user->member): ?>
            <p>
                You are a member!
            </p>
        <?php else: ?>
            <p>
                You are not a member, <a href="member/payment.php">Become a member.</a>
            </p>
        <?php endif; ?>
    </body>
</html>
