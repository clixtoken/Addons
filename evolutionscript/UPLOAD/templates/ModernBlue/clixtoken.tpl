{include file="header.tpl"}

<style>

    @import url('https://fonts.googleapis.com/css?family=Ubuntu');
    
    
    .clixtoken_userData_listWrapper ul,
    .clixtoken_footer_credits ul,
    .clixtoken_completed_payouts ul,
    #clixtokenHeader h2,
    .clixtoken_wrapper p { margin: 0; padding: 0 }
    
    
    .clixtoken_wrapper,
    #clixtokenHeader,
    p.msgToUser,
    .clixtoken_footer_credits { padding: 1.618em }
    

    .clixtoken_wrapper a { color: #3498db }
    .clixtoken_wrapper a:hover { color: #2980b9 }
    

    .clixtoken_wrapper {
        
        font-family: 'Ubuntu', sans-serif;
        color: #74777e;
        font-size: 1em;
        width: 70%;
        margin: 0 auto;
        background: #fff;
        
    }
    
    
    #clixtokenHeader {
        
        margin: 0;
        font-size: 1.618em;
        font-weight: normal;
        line-height: 1.618;
        color: #fff;
        text-shadow: 1px 1px #000;
        background-image: url('https://clixtoken.io/images/planet.jpg');
        background-position: center;
        background-size: cover;
        
    }
    
    
    #clixtokenHeader h2,
    #clixtokenHeader p { letter-spacing: 1px }
    
    #clixtokenHeader h2 { margin-top: 1.618em }
    
    #clixtokenHeader p { margin-bottom: 1.618em }
    
    
    .clixtoken_userData_wrapper {
        
        border-bottom: 1px solid #9E9E9E;
        
    }
    

    .clixtoken_userData_listWrapper {
        
        border-left: 1px solid #9E9E9E;
        border-right: 1px solid #9E9E9E;
        border-bottom: 1px solid #9E9E9E;
        
    }
    
    
    .clixtoken_userData_listWrapper {
        padding: 1.618em 0 1.618em 0;
    }
    
    
    .clixtoken_content {
        
        font-size: 1.05em;
        text-align: left;
        border-left: 1px solid #9E9E9E;
        border-right: 1px solid #9E9E9E;
        
    }
    
    .first,
    .second,
    .third {
        padding: 1.618em 2.618em 1.618em 2.618em;
    }
    
    
    .msgToUser {
        background: #ecf0f1;
    }

    
    h2.clixtoken_faq {
        margin: 0 0 0.5em 0;
    }
    
    
    h2.clixtoken_completed_payouts {
        margin: 1.618em 0 0.5em 0;
    }
    
    
    .clixtoken_userData_listWrapper ul li,
    .clixtoken_footer_credits ul li,
    .clixtoken_completed_payouts ul li {
        
        display: inline-block;
        
    }
    
    
    .success,
    .error { font-weight: bold }
    
    .success { color: green }
    
    .error { color: red }


    /* FORM */
    .clixtoken_form_wrapper {
        width: 100%;
        height: 35px;
    }
    
    #clixtoken_conversion_form input[type=text],
    #clixtoken_conversion_form input[type=submit] {
        box-sizing: border-box;
        outline: none;
        border: none;
    }
    
    #clixtoken_conversion_form input[type=text] {
        background: #fff;
        color: #74777e;
        float: left;
        width: 75%;
        height: 35px;
        border-top: 1px solid #95a5a6;
        border-bottom: 1px solid #95a5a6;
        border-left: 1px solid #95a5a6;
    }
    
    #clixtoken_conversion_form input[type=submit] {
        background: #95a5a6;
        color: #fff;
        float: right;
        width: 25%;
        height: 35px;
    }
    
    .masonry { /* Masonry container */
        column-count: 4;
        column-gap: 1em;
    }
    
    .item { /* Masonry bricks or child elements */
        background-color: #eee;
        display: inline-block;
        margin: 0 0 1em;
        width: 100%;
    }
    
</style>

<div align="center" class="clixtoken_wrapper">
    
    <div class="clixtoken_userData_wrapper">
        
        <header id="clixtokenHeader">
            <h2>CLIXToken</h2>
            <p>One cryptocurrency for all Paid to Click websites</p>
        </header>
    
        <div>
        	
        	<div class="clixtoken_userData_listWrapper">
        	    <ul>
        	        <li><strong>Balance</strong>: ${$balance}</li> &vert;
        	        <li><strong>Minimum</strong>: ${$minimumBalanceRequired}</li> &vert;
        	        <li><strong>Multiplier</strong>: {$rate}</li>
        	    </ul>
        	</div>
        	
        	<div class="clixtoken_content">
        	    
        	    <div class="first">
        	    
                	<p>This page enables you to easily convert your USD balance to CLIXToken. 
                	On the above bar you can see your current <strong>account balance</strong>, 
                	the <strong>minimum balance</strong> you need to reach before you 
                	can redeem CLIXToken and the <strong>multiplier</strong> (set by the admin) 
                	which is used to determine the amount of CLIXToken you will receive 
                	(balance * multiplier = CLIXToken).
                	</p>
            	
            	</div>
            	
            	<div class="second">
            	
                	<p class="msgToUser">{$msgToUser}
                	</p>
                	
                	{$form}
            	
            	</div>
            	
            	<div class="third">

            	    <h2 class="clixtoken_faq">What is CLIXToken?</h2>
            	    <p class="clixtoken_faq">CLIXToken is a fully transparent and decentralized cryptocurrency 
            	    built on top of the powerful and proven <a href="https://www.ethereum.org" target="_blank">Ethereum network</a>. As such, 
            	    it follows the <a href="https://theethereum.wiki/w/index.php/ERC20_Token_Standard" target="_blank">ERC20 token standard</a> which allows us to develop all 
            	    sorts of cool decentralized Paid to Click related applications that run inside the Ethereum 
            	    Virtual Machine. Learn more about the team and what we are building 
            	    by visiting our website: <a href="https://clixtoken.io" target="_blank">clixtoken.io</a>
            	    </p>
            	    
            	    {$completedPayoutsTitle}
            	    {foreach $completedPayouts as $name}
                    <div>{$name}</div>
                    {/foreach}

        	    </div>
        	
        	</div>
        	
    	</div>
	
	</div>
	
	<div class="clixtoken_footer_credits">
	    <ul>
	        <li><a href="https://clixtoken.io" target="_blank">CLIXToken</a></li> &vert;
	        <li><a href="https://clixtoken.io/ClixToken-Whitepaper.pdf" target="_blank">WhitePaper</a></li> &vert;
	        <li><a href="https://etherscan.io/address/0x8A1A74f14Eb6a7dd1073BC20D78e22698e0Cdf6a" target="_blank">Smart Contract</a></li>
	    </ul>
	</div>

</div>

{include file="footer.tpl"}