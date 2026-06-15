# DOLIBINANCE FOR DOLIBARR

![Screenshot dolibinance](img/screenshot_dolibinance.png?raw=true "DoliBinance")

## Features

This module connects your Binance account to your Dolibarr. If you don't have one yet, follow this link: [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280).  
This allows you to easily add Bitcoin payment and accept any other cryptocurrency supported by Binance.  
When a customer wants to pay with cryptocurrency, the module uses the current 24-hour smoothed average price of the cryptocurrency for the payment of the invoice, as well as the receiving address of your Binance wallet. The customer then enters the transaction details they have performed: transaction ID, sending address, amount. As soon as the deposit arrives in your Binance account, the invoice is marked as "paid."  
You can monitor the balances of your cryptocurrency assets in your Binance Spot wallet directly within Dolibarr without needing to use the Binance application.

## Coming Soon

User permissions have not been developed in this version and will be added in the future. All features are accessible to any Dolibarr user, but significant actions require administrative privileges.  
Based on accounting needs identified during module usage, relevant elements will be automatically created in Dolibarr.  
Please don't hesitate to provide your feedback on this matter to [Olivier Andrade Sanchez](mailto:oandrade@aplose.fr?subject=[DoliBinanceRequest]-).

## Configuration

### Dictionary

To offer your customers cryptocurrency payment, you need to add the necessary information to the "Binance Payment Address Directory" (Home->Configuration->Dictionary). This includes the receiving addresses for the cryptocurrencies you wish to accept for invoice payments.  
![Screenshot dictionnary 1](img/doc-010-dictionnary.png?raw=true "Dictionnary 1")  
In this directory, please add the information from your various receiving addresses created in Binance ([refer to the documentation Binance for this](https://www.binance.com/fr/support/faq/comment-d%C3%A9poser-des-cryptos-sur-binance-115003764971)).  
For example, here, three addresses have been entered (including two for Bitcoin on two different networks).  
![Screenshot dictionnary 2](img/doc-011-dictionnary.png?raw=true "Dictionnary 2")  
As you can see, you can enable or disable cryptocurrencies without deleting the configuration.

### Settings Page

Using this module requires having a Binance account.  
Why Binance? Because it is the world's largest cryptocurrency exchange. However, please note that you do not have the private and public keys for the corresponding wallet, so it is recommended to use this platform only for sending and receiving payments or for trading.  
If you want to secure your cryptos, prefer a "Hard wallet" like a Ledger key ([read the article here](https://www.cryptocolo.fr/2023/02/08/not-your-keys-not-your-coins/)).  
Feel free to use our Binance referral link: [https://accounts.binance.com/register?ref=385030280](https://accounts.binance.com/register?ref=385030280)  
There are many benefits to becoming our referrals (including the use of a specific trading bot, among other advantages; contact us for more information).

#### API Key Configuration

First, create your API keys on Binance by following this documentation (only provide read permissions to them): [Create API Keys on Binance](https://www.binance.com/fr/support/faq/comment-cr%C3%A9er-des-cl%C3%A9s-api-sur-binance-360002502072)  
Then, enter your API keys in the module's configuration page:  
![Screenshot setup](img/doc-020-setup.png?raw=true "Setup")  
You'll notice that the module provides you with the current price of Bitcoin, the reference cryptocurrency.

## Usage

### Invoicing

Your standard invoices can now be paid in cryptocurrencies!  
You can use the payment link provided by Dolibarr and displayed on your invoice:  
![Screenshot use 1](img/doc-030-use.png?raw=true "Use 1")  
This URL can be directly integrated into your email templates using the substitution variable: \__ONLINE_PAYMENT_TEXT_AND_URL__.

### Payment Page: Step 1

When your customer follows this link, they arrive at Dolibarr's online payment page. During the first step, they choose their preferred Crypto/Network pair from the provided list:  
![Screenshot use 2](img/doc-040-use.png?raw=true "Use 2")

### Payment Page: Step 2

In the second step, the current amount to be paid in the chosen cryptocurrency is presented.  
The customer will need to enter the details of the transaction they have or will perform (the transaction ID is not mandatory and will be obtained later on Binance automatically).  
![Screenshot use 3](img/doc-050-use.png?raw=true "Use 3")

### Payment Page: Step 3

In the final step, the payment record is presented.  
![Screenshot use 4](img/doc-060-use.png?raw=true "Use 4")

### Binance Wallet

You can check the balances of your cryptocurrencies in your Binance Spot wallet by using the "Binance Wallet" link in the left menu:  
![Screenshot use 5](img/doc-070-use.png?raw=true "Use 5")

### Deposit History in Your Wallet

You can view the 90-day history of deposits made to the deposit addresses you created on Binance by using the "Deposit History in Your Wallet" menu link.  
A status of "1" indicates a validated transaction (and is therefore taken into account by the DoliBinance job):  
![Screenshot use 6](img/doc-080-use.png?raw=true "Use 6")

### Transactions

The records of the payment page made by your customers are listed here, and a status of "1" indicates a completed transaction, received on Binance, and the corresponding invoice is marked as "Paid."  
![Screenshot use 7](img/doc-090-use.png?raw=true "Use 7")

### Transaction Validation Job (Automated Tasks)

Blockchain transactions can take several minutes to be validated by the corresponding network, so it is not possible to leave your customer waiting at the payment page.  
We have chosen a deferred processing of the transaction by regularly validating deposits made to your receiving addresses.  
The processing is initiated as frequently as every minute if your configuration allows it (make sure the crontab is active on your server):  
![Screenshot use 8](img/doc-100-use.png?raw=true "Use 8")  

The job can be modified like any automated task in Dolibarr, and you can edit it to run only once an hour, for example. You can also choose to launch it manually:  
![Screenshot use 9](img/doc-110-use.png?raw=true "Use 9")  

Once the transaction is validated on the network and the job is executed, the corresponding invoice is automatically marked as "Paid," and a private note is added to indicate DoliBinance's action:  
![Screenshot use 10](img/doc-120-use.png?raw=true "Use 10")

  

## Stay in Touch!

This module is an initial release that will evolve and improve based on your feedback. Please feel free to share your ideas and feedback with us at [oandrade@aplose.fr](mailto:oandrade@aplose.fr).  

## Translations

Translations can be completed manually by editing the files in the "langs" directories.

## Licenses

### Main Code

GPLv3.

### Documentation

All texts and readmes are under the GFDL license.