<?php

// This is required for all custom addons
include('developer.php');

// If the user is logged in
if( $_SESSION["logged"] == "yes" ) {

    // Check whether CLIXToken is installed first
	$check = $db->fetchOne("SELECT COUNT(*) AS NUM FROM addon WHERE name='clixtoken'");
	
	if($check == '0') {
	    
	    // CLIXToken is not installed, redirect user back to index.php
    	header("location: index.php");
    	exit();
    	
	}

	// Fetch settings from database
	$settings = $db->fetchRow("SELECT * FROM clixtoken_settings");
	
	// Set variables 
	$rate = $settings['rate'];
	$minimumBalanceRequired = $settings['minimum'];
	$username = $user_info['username'];
	$balance = $user_info['money'];
	$id = $user_info['id'];
	$conversion = $rate * $balance;
	$date = date("Y-m-d H:i:s");
	
    // Allow only 1 pending request at a time per user
    $checkPendingPayout = $db->fetchRow("SELECT * FROM clixtoken_pending WHERE user ='$username'");
    
    if(!empty($checkPendingPayout)) {
        
        // Tell the user that they already have a pending request 
        $msgToUser = "You already have a pending request from <strong>".$checkPendingPayout['date']."</strong> 
        in the amount of <strong>".$checkPendingPayout['amount']." CLIXToken.</strong>
        <br>
        The admin has to process this first before you can request more.
        ";
	    
        // Don't show a form
        $form = "";
        
    } else {
        
        // The user has no pending requests!
	
    	// If the user has enough balance
    	if($balance >= $minimumBalanceRequired) {
    	    
    	    // Tell the user what to do
            $msgToUser = "Please enter your <strong>Ethereum address</strong> in the field below to submit your request in the amount of <strong>$conversion CLIXToken</strong> for the admin to process.
            <br><br>Note: This will reset your account balance ($$balance) to zero!";
    	    
        	// Build the HTML conversion form
        	$form = "
        	<div class=\"clixtoken_form_wrapper\">
                <form method=\"post\" id=\"clixtoken_conversion_form\" action=\"clixtoken.php\">
                    <input type=\"hidden\" name=\"clixtoken_convert\" value=\"clixtoken_convert\"/>
                    <input type=\"text\" name=\"clixtoken_wallet\" value=\"\" required/>
                    <input type=\"submit\" name=\"submit\" value=\"Submit\"/>
                </form>
            </div>
        	";
    	
        	// If user submits the form
            if($input->p["clixtoken_convert"] == "clixtoken_convert") {
                
                /* Check if the Ethereum address is in the correct format.
                Note: this check is very basic and doesn't guarantee that the address exists 
                or that you can send anything to it (ie. if it's a contract or a wallet) */
                if(preg_match("/^(0x)?[0-9a-f]{40}$/i", $input->p["clixtoken_wallet"])) {
                    
                    // The address looks valid so we assign it to a variable
                    $clixtoken_wallet = $input->p["clixtoken_wallet"];
    
                    // Reset user's balance
                    $db->query("UPDATE members SET money = 0 WHERE id = '$id'");
                    
                    // Create array for database
                    $set = array(
                        'id' => '',
                        'user' => $username,
                        'wallet' => $clixtoken_wallet,
                        'amount' => $conversion,
                        'date' => $date
                    );
                    
                    // Insert array into a database table (pending payouts displayed in the admin panel)
                    $db->insert('clixtoken_pending', $set);
                    
                    // Show a 'success' message to let the user know everything worked
                    $msgToUser = "<span class=\"success\">Success! Your request for $conversion CLIXToken has been sent to the admin.</span>";
                    
                    $form = "";
    
                } else {
                    
                    // The address provided is not in the correct format
                    $msgToUser = "<span class=\"error\">Invalid address! Try again.</span>";
                    
                }
    
                // I saw this being done in another script???
                unset($_POST);
            }

    	} else {
    	    
    	    // The user does NOT have enough balance!
    	    
    	    // Calculate how much the user has left to earn before they can convert
    	    $moneyLeftToEarn = $minimumBalanceRequired - $balance;
    	    
            $msgToUser = "Unfortunately you do not yet meet the minimum $$minimumBalanceRequired balance required. 
            You need to <strong>earn at least $$moneyLeftToEarn more</strong> before you can convert your balance into CLIXToken.";
            
        	$form = "";
    	    
    	}
	
    }
    
    // Check if the user has completed requests
    $checkCompletedPayout = $db->query("SELECT * FROM clixtoken_completed WHERE user = '$username' ORDER BY id DESC");
    
    if(!empty($checkCompletedPayout)) {
        
        
        
        while ($completedPayoutsArray = $db->fetch_array($checkCompletedPayout)) {
            
            $completedPayoutsTitle = "<h2 class=\"clixtoken_completed_payouts\">Completed requests</h2>";
            
            $completedAmount = $completedPayoutsArray['amount'];
            $completedDate = $completedPayoutsArray['date'];
            $completedTxHash = $completedPayoutsArray['txHash'];
            
            $completedPayouts[] = "<div class=\"clixtoken_completed_payouts\">
            <ul>
                <li><strong>Amount:</strong> $completedAmount CLIXToken</li> -
                <li><strong>Date:</strong> $completedDate</li> -
                <li><a href=\"https://etherscan.io/tx/$completedTxHash\" target=\"_blank\">View on Etherscan</a></li>
            </ul>
            </div>
            ";
    
        }

        $smarty->assign("completedPayoutsTitle", $completedPayoutsTitle);
        $smarty->assign("completedPayouts", $completedPayouts);

    }
    
	// Assign smarties 
	$smarty->assign("rate", $rate);
	$smarty->assign("balance", $balance);
	$smarty->assign("minimumBalanceRequired", $minimumBalanceRequired);
	$smarty->assign("msgToUser", $msgToUser);
	$smarty->assign("form", $form);
    
    // Display template file
    $smarty->display("clixtoken.tpl");
	
} else {
    
    // The user is not logged it. Redirect back to index.php
	header("location: index.php");
	exit();
	
}
?>