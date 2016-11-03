<?php
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

require '../src/start.php';

if (isset($_GET['approved'])) {
    $approved = $_GET['approved'] === 'true';

    // check if the user completed the payment
    if ($approved) {

        // fetch the payerId from the approval_url
        $payerId = $_GET['PayerID'];

        //Get the payment id from the table
        $paymentId = $db->prepare('
            SELECT payment_id
            FROM transactions_paypal
            WHERE hash = :hash
        ');

        // get the payment id from the database
        $paymentId->execute([
            'hash' => $_SESSION['paypal_hash']
        ]);

        $paymentId = $paymentId->fetchObject()->payment_id;

        // fetch the payment information
        $payment = Payment::get($paymentId ,$api);

        // set the payer id that we want to charge
        $paymentExecution = new PaymentExecution;
        $paymentExecution->setPayerId($payerId);

        // charge the user
        $payment->execute($paymentExecution ,$api);

        // update the transaction row to set completed to 1
        $updateTransaction = $db->prepare("
            UPDATE transactions_paypal
            SET completed = 1
            WHERE payment_id = :payment_id
        ");

        $updateTransaction->execute([
            'payment_id' => $paymentId
        ]);

        // update the user record and set him as a member
        $updateMember = $db->prepare("
            UPDATE users
            SET member = 1
            WHERE id = :id
        ");

        $updateMember->execute([
            'id' => $_SESSION['user_id']
        ]);

        //clear the Transaction data from session
        unset($_SESSION['paypal_hash']);

        header('Location: ../paypal/completed.php');
    }else {
        header('Location: canceled.php');
    }
}
