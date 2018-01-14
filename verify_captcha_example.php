<?php

/**
 * require AdetoSDK.php class
 */
require_once 'src/AdetoSDK.php';

try {

    /**
     * Instantiate our object
     */
    $adeto = new Adeto($_SERVER['REQUEST_METHOD']);

    /**
     * Replace YOUR_PUBLISHER_KEY with your own publisher key
     */
    $adeto->publisherKey = "YOUR_PUBLISHER_KEY";

    /**
     * Replace YOUR_SECRET_KEY with your own secret key
     */
    $adeto->secretKey = "YOUR_SECRET_KEY";

    /**
     * Setting variables we need to verify a Captcha
     * Variables we need:
     * adetoImageName, adetoHash, adetoUserInputValue
     * These inputs are generated automatically in our Captcha div.
     *
     * This function checks if we have received all the needed variables
     * and throws an exception if some variables are not present
     */
    $adeto->checkIfReceivedAllVariables($adeto->method);

    /**
     * adetoImageName
     */
    $adeto->imageName = $_POST['adetoImageName'];

    /**
     * adetoHash
     */
    $adeto->hash = $_POST['adetoHash'];

    /**
     * adetoUserInputValue
     */
    $adeto->userInputValue = $_POST['adetoUserInputValue'];

    /**
     * Optional custom id if you have sent your request using a custom id
     * If you have not sent your request with a customId just ignore this
     * $adeto->customId = 8;
     */

    /**
     * Sending verification request using post() method
     */
    $results = $adeto->verify()->post();

    /**
     * In the example above, we used post() method. a curl() method is also available
     * So you can also use curl(), example:
     * $results = $adeto->verify()->curl();
     */

    /**
     * Results as JSON
     */
    $results = json_decode($results);

    /**
     * The final results
     * It's whether 'true' or 'false'
     */
    if ($results->status == 'true')
    {
        echo 'Captcha was RIGHT ;-)';

    } else {

        echo 'Captcha was WRONG :(';

    }

    /**
     * If there is an error in your request, you can get it using:
     * echo $results->message;
     * also var_dump($results) might help sometimes
     */

} catch (Exception $e) {

    echo 'Error: ' . $e->getMessage();

}
