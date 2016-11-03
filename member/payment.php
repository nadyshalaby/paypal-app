<?php
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Exception\PayPalConnectionException;

require '../src/start.php';

// instantiate essential object to process a payment
$payer = new Payer ;
$details = new Details ;
$amount = new Amount ;
$transaction = new Transaction ;
$payment = new Payment ;
$redirectUrls = new RedirectUrls ;

// Payer
$payer->setPaymentMethod('paypal');

// Details
$details->setShipping('2.00')
->setTax('0.00')
->setSubtotal('20.00');

// Amount
$amount->setCurrency('USD')
->setTotal('22.00')
->setDetails($details);

// Transaction
$transaction->setAmount($amount)
->setDescription('Membership');

//Payment
$payment->setIntent('sale')
->setPayer($payer)
->setTransactions([$transaction]);

// Redirect Urls
$redirectUrls
// url to redirect the user to, if the transaction is completed
->setReturnUrl('http://local.dev/paypal/paypal/pay.php?approved=true')
// url to redirect the user to, if the transaction is canceled
->setCancelUrl('http://local.dev/paypal/paypal/pay.php?approved=false');

// setting the redirect Urls
$payment->setRedirectUrls($redirectUrls);

try {
    // creating the actaul payment
    $payment->create($api);

    // Generate and store a hash to identify the users when they return for the payment process
    $hash = md5($payment->getId());
    $_SESSION['paypal_hash'] = $hash;

    // store the transaction information
    $store = $db->prepare('
            INSERT INTO transactions_paypal (user_id, payment_id, hash , completed)
            VALUES (:user_id, :payment_id , :hash, 0)
    ');

    //execute the prepared statement to store the transaction
    $store->execute([
        'user_id' => $_SESSION['user_id'],
        'payment_id' => $payment->getId(),
        'hash' => $hash,
    ]);
} catch (PayPalConnectionException $e) {
    header('Location: ../paypal/error.php');
}

// fetch the payment transaction url to complete the process
foreach ($payment->getLinks() as $link) {
    if($link->getRel() === 'approval_url'){
        $redirectUrl = $link->getHref();
        break;
    }
};
// redirect the user to this url to complete the payment
header('Location: ' . $redirectUrl);
