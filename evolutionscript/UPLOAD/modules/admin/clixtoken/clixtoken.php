<?php

// Set the version of the script
$version = "0.0.1";

// Check to see if CLIXToken is installed first
$check = $db->fetchOne("SELECT COUNT(*) AS NUM FROM addon WHERE name='clixtoken'");

if($check == '0') {
    
    // CLIXToken is not installed. Look for an install request
	if($_REQUEST['install'] == '1') {
	    
	    // Install request found!
	    
	    // Add a new entry in the 'addon' table within the database
		$set = array( "name" => "clixtoken" );
		$db->insert("addon", $set);
		
		// Create table for pending payouts
		$db->query("
    		CREATE TABLE IF NOT EXISTS clixtoken_pending (
    		id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    		user varchar(50) NOT NULL,
    		wallet varchar(255) NOT NULL,
    		amount varchar(255) NOT NULL,
    		date DATETIME(0) NOT NULL
		)");
		
		// Create table for completed payouts
		$db->query("
    		CREATE TABLE IF NOT EXISTS clixtoken_completed (
    		id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    		user varchar(50) NOT NULL,
    		wallet varchar(255) NOT NULL,
    		amount varchar(255) NOT NULL,
    		txHash varchar(255) NOT NULL,
    		date DATETIME(0) NOT NULL
		)");

        // Create table for settings
		$db->query("
    		CREATE TABLE IF NOT EXISTS clixtoken_settings (
    		id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    		rate varchar(255) NOT NULL,
    		minimum varchar(255) NOT NULL
		)");
		
		// Create array with default settings
		$set = array(
		    'rate' => 1,
		    'minimum' => 2
		);
		
		// Insert array with default settings into the database 
		$db->insert('clixtoken_settings', $set);
		
		// Redirect the user to the newly installed module and die
		echo "<script>location.href=\"./?view=addon_modules&module=clixtoken\";</script>";
		die();
		
	}
	
	// Display welcome message 
	echo "
	Welcome to the CLIXToken installation!<br>
	Please click <a href=\"?view=addon_modules&module=clixtoken&install=1\">Install</a> to install CLIXToken version $version.
	";

} else {
    
    // CLIXToken appears to be installed so let's check for an uninstall request
	if($_REQUEST['install'] == '2') {
	    
	    // Uninstall requested. Drop everything!!!
		$db->query("DROP TABLE clixtoken_pending");
		$db->query("DROP TABLE clixtoken_completed");
		$db->query("DROP TABLE clixtoken_settings");
		$db->delete("addon", "name='clixtoken'");
		
		// Redirect user back the welcome page and exit
		echo "<script>location.href=\"./?view=addon_modules&module=clixtoken\";</script>";
		exit();
		
	}
	
	/* The following if statement checks whether the hidden form was submitted. 
	It should in theory only be submitted after a successful token transaction 
	using MetaMask. */
	if($input->p['completed'] == 'completed') {

	    // Set variables
	    $wallet = trim($input->p['wallet']);
	    $amount = trim($input->p['amount']);
	    $txHash = $input->p['txHash'];
	    $date = date("Y-m-d H:i:s");

	    /* Check if the txHash is somehow already in the database.
	    This can happen, for example, when the admin makes a transaction and then refreshes the page 
	    causing the form to resubmit with the same data. */
	    $doubleCheck = $db->fetchRow("SELECT * FROM clixtoken_completed WHERE txHash = '$txHash'");
	    
	    // If doubleCheck returns nothing continue with the following
	    if(empty($doubleCheck)) {
	        
	        // Fetch the username from the database based on wallet and amount 
	        $user = $db->fetchOne("SELECT user FROM clixtoken_pending WHERE wallet ='$wallet' AND amount = '$amount'");
	        
	        // Make sure the user was found
	        if(!empty($user)) {
	    
    	    // Create array for database
    	    $set = array(
                'id' => '',
                'user' => $user,
                'wallet' => $wallet,
                'amount' => $amount,
                'txHash' => $txHash,
                'date' => $date
            );
            
            // Insert the array into database
            $db->insert('clixtoken_completed', $set);
    
            // Delete the entry from the 'clixtoken_pending' table
    	    $db->query("DELETE FROM clixtoken_pending WHERE wallet = '$wallet' AND amount = '$amount'");
    	    
	        }
	    
	    }
	}
	
}

// Get settings variables from database
$clixtoken_settings = $db->fetchRow("SELECT * FROM clixtoken_settings");

$paginator = new Pagination("clixtoken_pending", $cond);
$paginator->setOrders('amount', 'DESC');
$paginator->setPage($input->gc['page']);
//$paginator->allowedfield($allowed);
$paginator->setNewOrders($input->gc['orderby'], $input->gc['sortby']);
$paginator->setLink("./?view=addon_modules&module=clixtoken&");
$q = $paginator->getQuery();

?>

<div class="widget-content">
	<?php
	
	// Check if the user submitted the update settings form 
	if( $input->p["update"] == "edit_clixtoken" ) {
		
		// Replace commas, when found, with points instead 
		$clixtoken_rate = str_replace(',', '.', $input->p["rate"]);
		$clixtoken_minimum_balance = str_replace(',', '.', $input->p["minimumBalanceRequired"]);
		
		// Check if the inputs are numbers, fail otherwise  
		if (is_numeric($clixtoken_rate) && is_numeric($clixtoken_minimum_balance)) {
			
			// Create an array to insert into the clixtoken_settings table
			$data = array( 
    			"rate" => $clixtoken_rate,
    			"minimum" => $clixtoken_minimum_balance
			);
			
			// Insert the new settings into the clixtoken_settings table
			$insert = $db->update("clixtoken_settings", $data, "id=1");
			
			echo "<p align=\"center\">New settings saved!</p>";
			echo "<p align=\"center\"><a href=\"./?view=addon_modules&module=clixtoken\">Return</a></p>";
			
		} else {
			echo "<p align=\"center\">Error: Both the conversion rate and minimum balance required have to be numbers!</p>";
			echo "<p align=\"center\"><a href=\"./?view=addon_modules&module=clixtoken\">Try again</a></p>";
		}
		
		unset($_POST);
		
	} else {
	    
	    // User has not submitted the update settings form, continue with the script 
	    
	?>
	
    <div id="tabs">
        
    	<ul>
    		<li><a href="#tabs-1">Pending requests</a></li>
    		<li><a href="#tabs-2">Settings</a></li>
    		<li><a href="#tabs-3">Readme</a></li>
    	</ul>
    	
    	<div id="tabs-1">
    
    		<h3>CLIXToken transfer form</h3>
    		<span id="tokens"></span>
    		
    		<div id="Transfer">
    		    
        		<div style="width: 70%;">
        		    
        			<div style="width: 100%; margin: 0 auto; padding: 1em 1em 0.5em 1em"> 
        				<label style="width: 100%;">Ethereum address</label>
        				<input style="width: 100%;" id="address" type="text" placeholder="">
        			</div>
        			
        			<div style="width: 100%; margin: 0 auto; padding: 0.5em 1em 0.5em 1em">
        				<label style="width: 100%;">Amount</label>
        				<input style="width: 100%;" id="amount" type="text" placeholder="">
        			</div>
        			
        			<div style="width: 100%; margin: 0 auto; padding: 0.5em 1em 1em 1em">
                        <button id="transfer">Send</button>
        			</div>
        			
        			<div><p id="transactions"></p></div>
        			
                </div>
            
    		</div>
    
    	<div>
    	    
        	<table width="100%" class="widget-tbl">
        	    
        		<tr class="titles">
        			<td>User</td>
        			<td>Ethereum address</td>
        			<td>Amount</td>
        			<td>Date</td>
        			<td>ID</td>
        		</tr>
        		
        	<?php
        	while($r=$db->fetch_array($q)) {
        	$tr = ($tr=='tr1'?'tr2':'tr1');
        	?>
        	
        	<tr class="<?=$tr;?> normal_linetbl">
        	    
            	<td align="center"><?php echo $r['user'];?></td>
            	<td align="center"><? echo $r['wallet'];?></td>
            	<td align="center"><?php echo $r['amount'];?></td>
            	<td align="center"><?php echo $r['date'];?></td>
            	<td align="center"><?php echo $r['id'];?></td>
        	
        	</tr>
        	
        	<?
        	}
        	
        	if($paginator->totalResults() == 0) { ?>
            	<tr><td colspan="8" align="center">Records not found</td></tr> 
            <? } ?>
        	
        	</table>
        	
        	<div style="margin-top:10px">
        	<input type="button" value="&larr; Prev Page" <?=($paginator->totalPages() == 1 || $paginator->getPage()==1?'disabled class="btn-disabled"':'onclick="location.href=\''.$paginator->prevpage().'\';";');?> />
        	<input type="button" value="Next Page &rarr;" <?=($paginator->totalPages() == 0 || $paginator->totalPages() == $paginator->getPage()?'disabled class="btn-disabled"':'onclick="location.href=\''.$paginator->nextpage().'\';";');?> />
        	<? if($paginator->totalPages() > 1){ ?>
        	<div style="float:right">
        	Jump to page: 
        	<select name="p" style="min-width:inherit;" id="pagid" onchange="gotopage(this.value)">
        	<?
        	for($i=1;$i<=$paginator->totalPages();$i++) {
        		if($i == $paginator->getPage()) {
        			echo '<option selected value="'.$paginator->gotopage($i).'">'.$i.'</option>';
        		} else {
        		echo '<option value="'.$paginator->gotopage($i).'">'.$i.'</option>';
        		}
        	}
        	?>
        	</select> 
        	<script type="text/javascript">
        	function gotopage(pageid){
        	location.href=pageid;
        	}
        	</script>
        	</div> 
        	<div class="clear"></div>
        	<? } ?>
        	</div>
        	
    	</div>
    	</div>
    	
    	<div id="tabs-2">
    		<form method="post" id="frm4" action="./?view=addon_modules&module=clixtoken">
    			<input type="hidden" name="update" value="edit_clixtoken" />
    			<table width="100%" class="widget-tbl">
    				<tr>
        				<td align="right" width="300">Conversion rate</td>
        				<td><input type="text" name="rate" value="<?php echo $clixtoken_settings['rate'] ?>"/></td>
        				<td>This is a multiplier used to calculate the amount of CLIXToken a user will receive using a formula: <strong>Conversion rate * userEarningsInUSD = CLIXToken</strong>. 
        				Default is 1 which means 1 USD earned on your website converts to 1 CLIXToken. If you had a conversion rate of 0,01 and a user had a balance of $5 then the script would convert user's earnings like so: 0.01 * 5 = 0.05 CLIXToken. Or with the rate set to 100 it would mean a user would receive 500 CLIXToken (100 * 5) and so on. You can set the conversion rate to anything you like.</td>
        			</tr>
        			<tr>
        			    <td align="right" width="300">Minimum balance required</td>
        			    <td><input type="text" name="minimumBalanceRequired" value="<?php echo $clixtoken_settings['minimum'] ?>"/></td>
        			    <td>The minimum balance a user must reach before they are allowed to convert their balance in CLIXToken and submit the payout request. Default is 2 dollars.</td>
        			</tr>
    				<td></td>
    				<td>
    				<input type="submit" name="submit" value="Save" />
    				</td>
    				</tr>
    			</table>
    		</form>  
    	</div>
    	
    	<div id="tabs-3">
    	    
    	    <h3>Requirements & info</h3>
    		<p>In order for this module to work you need to be logged in to your <strong><a href="https://metamask.io" target="_blank" rel="nofollow">MetaMask</a></strong>
    		account.<br><br>
    		Since every transaction that happens on the Ethereum Blockchain requires Gas (Ether paid to miners for confirming transactions), you need to have enough ETH in 
    		your balance to successfully make a transaction.<br>
    		Sometimes MetaMask will use a sensible amount of Gas by default but it's always better to check the safe minimum over at <strong><a href="https://ethgasstation.info" target="_blank" rel="nofollow">ETH Gas Station</a></strong>
    		<br><br>
    		If you use too little Gas your transaction may get stuck and may take a long time to confirm. It may also fail to confirm at all!
    		</p>
    		
    	    <br><br>
    	    
    		<h3>How to send CLIXToken</h3>
    		<p>Use the form under 'Pending requests' tab to send CLIXToken to your members.<br><br>
    		1. In the 'Ethereum address' field enter the Ethereum address of your user<br>
    		2. In the 'Amount' field enter the amount of CLIXToken they requested<br>
    		3. Click on the 'Send' button<br>
    		4. A MetaMask window should pop up asking to confirm the transaction and cover the necessary Gas (in ETH)<br>
    		5. Hit 'Submit' and if all goes well, MetaMask should close<br>
    		6. The page should 'refresh' and the user's request should now be removed from the list<br>
    		7. Well done! The CLIXToken transaction was successful<br>
    		8. It may take some time for the miners to confirm this transaction, this is perfectly normal<br>
    		</p>
    	</div>
    	<?php } ?>
    	
    </div>

    </div class="credits">
    	<a href="http://clixtoken.io">CLIXToken 2018</a> &vert; <a href="https://metamask.io">MetaMask</a>
    </div>

<script>
if (typeof web3 !== 'undefined') {
    web3 = new Web3(web3.currentProvider);
}

web3.eth.defaultAccount = web3.eth.getAccounts[0];

var contractAddress = '0x8A1A74f14Eb6a7dd1073BC20D78e22698e0Cdf6a';

var coinContract = web3.eth.contract([
{
	"constant":true,
	"inputs":[{"name":"_owner","type":"address"}],
	"name":"balanceOf",
	"outputs":[{"name":"","type":"uint256"}],
	"payable":false,
	"stateMutability":"view",
	"type":"function"
},

{
	"constant":false,
	"inputs":[{"name":"_to","type":"address"},{"name":"_amount","type":"uint256"}],
	"name":"transfer",
	"outputs":[{"name":"success","type":"bool"}],
	"payable":false,
	"stateMutability":"nonpayable",
	"type":"function"
}

]);

var decimalHolder = 1000000000000000000;		

var coin = coinContract.at(contractAddress);

var balance = 0;

var account = web3.eth.getAccounts[0];
var accountInterval = setInterval(function() {
	if (web3.eth.accounts[0] !== account) {
		account = web3.eth.accounts[0];
		coin.balanceOf(account, function(error, result){
			if(!error)
			{
				balance = Math.floor(result/decimalHolder);
				$("#tokens").html('Your balance: ' + balance + ' CLIXToken');
			}
		});
	}
}, 100);

$("#transfer").click(function() {
    
	//user-friendly checks
	if (!web3.isAddress($('#address').val())) {
		alert('Address is invalid!');
		return;	
	}

	if ($("#amount").val() <= 0) {
		alert('Amount cannot be negative or zero!');
		return;	
	}

	if ($('#amount').val() <= 0 || $('#amount').val() > balance) {
		alert('The amount you are trying to send is more than your balance!');
		return;	
	}

	coin.transfer($("#address").val(), $("#amount").val() * decimalHolder, function(error, result) {
		if (!error) {
		    
        var form = document.createElement("form");
        
        var element0 = document.createElement("input");
        var element1 = document.createElement("input");
        var element2 = document.createElement("input");
        var element3 = document.createElement("input");
        
        form.method = "POST";
        form.action = "";
        
        element0.name="completed";
        element0.value="completed";
        element0.type="hidden";
        form.appendChild(element0);
        
        element1.name="wallet";
        element1.value="" + $("#address").val() + "";
        element1.type = "hidden";
        form.appendChild(element1);
        
        element2.name="amount";
        element2.value="" + $("#amount").val() + "";
        element2.type = "hidden";
        form.appendChild(element2);
        
        element3.name="txHash";
        element3.value=result;
        element3.type = "hidden";
        form.appendChild(element3);
        
        document.body.appendChild(form);
        form.submit();
        
			$("#transactions").prepend("Transfer to " + $("#address").val() + " is successful.<br>TxHash: <a target=\"_blank\" href=\"https://etherscan.io/tx/" + result + "\">"+result+"</a><br>");
		} else {
			alert("Transfer could not completed. Please try again later");
		}
	});
	
});

$("#address").on("input",function () {	
	if (web3.isAddress($('#address').val())) {
		$('#address').css('border-color','green');
	}
	else {
		$('#address').css('border-color','red');
	}
})

$("#amount").on("input",function () {	
	if ($('#amount').val() > 0 && $('#amount').val() <= balance) {
		$('#amount').css('border-color','green');
	}
	else {
		$('#amount').css('border-color','red');
	}
})

</script>