# Meta Basket

MyMeta Basket is the world's first plug-and-play Wordpress/Enjin/Ethereum integration that allows you to start selling blockchain assets through your website within minutes. All you need is Wordpress, MyMeta Basket, and an Enjin subscription.

We originally created this integration so we could sell our own ERC-1155 tokens through our website, but after being so proud of the user experience we designed, we decided to make it available to every Wordpress site!

WE DO NOT USE THIS PLUGIN OURSELVES, THEREFORE WE CANNOT PROVIDE UPDATES OR ONGOING SUPPORT FOR IT.


### How to Install MyMeta Basket

INSTRUCTION VIDEO: https://www.loom.com/share/b419d72e720b4887830d2a14dd5f6c8b

IMPORTANT: You need an Enjin Platform account to install this plugin.

Step 1: Download the MyMeta Basket plugin. Clone or download this repo and upload it into the plugin folder inside your Wordpress installation.
Step 2: Log into WordPress and select Plugins
Step 3: Find the MyMeta Basket plugin and activate it
Step 4: Go to your MyMeta Basket Plugin settings and input your Enjin settings

You need an Enjin Platform account to complete this step.
You will need to input the following details into the Enjin Settings section:

* App ID
* App Secret
* Identity ID

You need to get these details from Enjin's GraphQL API, you can find the Mainnet, Testnet, and Jumpnet versions of this API at the following addresses:

Mainnet: https://cloud.enjin.io/
Testnet: https://kovan.cloud.enjin.io/
Jumpnet: https://jumpnet.cloud.enjin.io/

Step 5: Add your first blockchain-powered product
1. Click the ADD NEW item in the PRODUCTS menu.
2. Add your TOKEN ID and REQUEST TYPE into the GENERAL section of your PRODUCT DATA
3. The product's TOKEN/ITEM ID can be found in your Enjin Platform account, follow these links:

> Mainnet: https://cloud.enjin.io/
> Testnet: https://kovan.cloud.enjin.io/
> Jumpnet: https://jumpnet.cloud.enjin.io/


## How to Use MyMeta Basket

### Approving the Distribution of Tokens
Once you have installed MyMeta Basket and created a product you can start selling immediately.
Every time a token is sold, you will receive a notification to your Enjin Wallet.
You can approve these transactions manually through the REQUEST tab in your Enjin Wallet app.
If you want to automate this process you can set up a wallet daemon that approves your transactions for you.


### Troubleshooting
The MyMeta Basket plugin keeps a log of past and present transaction status.
You can find it in WooCommerce > Orders > Select Order.
The transaction status can display the following responses:

* PENDING: Transaction is created on the Enjin Platform, but has not yet been signed by the user/dev.
  * You have not authorized the transaction yet via your linked Enjin Wallet.
  * If the transaction is not progressing, open the requests tab and search for your transaction request.
  * If your transaction request fails, you can process it again or you can contact Enjin support to find out why
  * If you contact Enjin support, make sure to provide them with your "TP Transaction ID".


* TP_PROCESSING: Transaction has been signed and is waiting for the Enjin Platform to process the transaction for broadcast.
  * You have authorized your transaction and it is being processed by the Enjin Platform.
  * If your transaction request fails, you can process it again or you can contact Enjin support to find out why
  * If you contact Enjin support, make sure to provide them with your "TP Transaction ID".


* BROADCAST: Transaction has been signed and has been broadcast but has not yet been confirmed on the blockchain.
  * Your transaction is being processed by the Ethereum network.
  * You should have "Transaction ID" available which you can also copy into EnjinX if you want to see more information.
  * If your transaction request fails, you can process it again or you can contact Enjin support to find out why
  * If you contact Enjin support, make sure to provide them with your "TP Transaction ID" and your "Transaction ID".


* EXECUTED: The transaction has received confirmation on the blockchain and the Enjin Platform.
  * Your transaction was successful. Your customer now has their token and they are very happy.


* CANCELED_USER: The user has cancelled the PENDING transaction/not signed.
  * You have cancelled the transaction through your Enjin Wallet–or failed to sign it.
  * You can process it again or you can contact Enjin support to find out why
  * If you contact Enjin support, make sure to provide them with your "TP Transaction ID".


* FAILED: Transaction has failed on the Enjin Platform.
  * Something reached the Enjin Platform but something has gone wrong.
  * You can process it again or you can contact Enjin support to find out why
  * If you contact Enjin support, make sure to provide them with your "TP Transaction ID".


* DROPPED: Transaction was not mined on the blockchain and has since been dropped.
  * The transaction has failed on the Ethereum Blockchain.
  * You can process it again or you can contact Enjin support to find out why
  * If you contact Enjin support, make sure to provide them with your "TP Transaction ID" and your "Transaction ID".
 

If a transaction has failed and you wish to try to send it again, you can:
* Change the order's status to PROCESSING.
* Update the order by pressing the UPDATE button.

This will prompt the MyMeta Basket plugin to send the transaction again.
Be sure not to do this unnecessarily or your customers will receive twice as many tokens as they paid for.
