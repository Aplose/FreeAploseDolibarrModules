<?php

require_once __DIR__ . '/../lib/dolibinance.lib.php';

/**
 * Contextes de hook utilisés : newpayment, paymentmethod, validpaymentmethod
 */
class ActionsDoliBinance {
    
    public function doActions($parameters, &$object, &$action, $hookmanager){
 //       echo 'PASSAGE doActions';
        
    }
    public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager){
        //obligatoire car pas de hook pour ajouter un nouveau payment en ligne...
        //le lien de paiement sera proposé avant les boutons.
        $useonlinepayment = (isModEnabled('paypal') || isModEnabled('stripe') || isModEnabled('paybox'));
        if(!$useonlinepayment){
            print '<br><!-- Link to pay -->'."\n";
            require_once DOL_DOCUMENT_ROOT.'/core/lib/payments.lib.php';
            print showOnlinePaymentUrl('invoice', $object->ref).'<br>';            
        }
    }
    //appelée dans newpayment
    public function doValidatePayment($parameters, &$object, &$action, $hookmanager){
//        echo 'PASSAGE doValidatePayment';
        
    }
    //appelée dans newpayment
    public function doPayment($parameters, &$object, &$action, $hookmanager){
 //       echo 'PASSAGE doPayment';
        //attention appelée deux fois...
        
        
        //deuxième fois :
	// This hook can be used to show the embedded form to make payments with external payment modules (ie Payzen, ...)
/*	$parameters = [
		'paymentmethod' => $paymentmethod,
		'amount' => $amount,
		'currency' => $currency,
		'tag' => GETPOST("tag", 'alpha'),
		'dopayment' => GETPOST('dopayment', 'alpha')
	];*/
        
        
    }
    //appelée dans newpayment
    public function doCheckStatus($parameters, &$object, &$action, $hookmanager){
//        echo 'PASSAGE doCheckStatus';
        
    }
    public function doAddButton($parameters, &$object, &$action, $hookmanager){
        global $conf, $langs;
        $dopayment_dolibinance = GETPOST('dopayment_dolibinance');
        if(empty($action)){
            $sql = "SELECT rowid, asset, network, address, active FROM " . $object->db->prefix() . "cryptodepositaddresses WHERE active = 1";
            $resql = $object->db->query($sql);
            if($resql){
                $nbRows =  $object->db->num_rows($resql);
                echo '<p>'.$langs->trans("DoliBinanceCryptoChoice").'</p>';

                echo '<select name="cryptoDepositAddressesId">';
                for($i=0;$i<$nbRows;$i++){
                    $obj = $object->db->fetch_object($resql);
                    if($i==0){
                        echo '<option value="'.$obj->rowid.'" selected>';
                    }else {
                        echo '<option value="'.$obj->rowid.'">';
                    }
                    echo $obj->asset.' '.$langs->trans("network").' '.$obj->network;
                    echo '</option>';
                }
                echo '</select>';
            }
            $object->db->free($resql);

            echo '<div id="div_dopayment_dolibinance" class="button buttonpayment">';
            echo '<span class="fa fa-btc"></span>';
            echo '<input type="hidden" name="action" id="action" value="dolibinanceShowPaymentForm">';
            echo '<input type="submit" id="dopayment_dolibinance" name="dopayment_dolibinance" value="'.$langs->trans("DoliBinanceNextStep").'"><br>';
            echo '<span class="buttonpaymentsmall">'.$langs->trans("DoliBinancePaymentButton").'</span>';
            echo '</div>';
        }else if ($action=='dolibinanceShowPaymentForm' && !empty ($dopayment_dolibinance)){
            $rowid = GETPOST('cryptoDepositAddressesId');
            $sql = "SELECT rowid, asset, network, address, active FROM " . $object->db->prefix() . "cryptodepositaddresses WHERE active = 1 and rowid=".$rowid;
            $resql = $object->db->query($sql);
            $obj = $object->db->fetch_object($resql);
            $object->db->free($resql);
            $asset = $obj->asset;
            $network = $obj->network;
            $address = $obj->address;
            //par rapport à l'objet, on va chercher le prix en crypto
            $symbol = $asset.$conf->global->MAIN_MONNAIE;
//            $reverseSymbol = $conf->global->MAIN_MONNAIE.$asset;
            $symbolPrice = getLastAveragePriceForSymbol($symbol);
            $amount = price2num(GETPOST('amount'));
            $amountToPay= $amount/$symbolPrice;
            
            echo '<p>'.$langs->trans("DoliBinanceYouPayWith").$asset.' '.$langs->trans("DoliBinanceOnTheNetWork").$network.'</p>';
            echo '<p>'.$langs->trans("DoliBinanceAddress").'<b>'.$address.'</b></p>';
            echo '<p>'.$langs->trans("DoliBinanceAmmount").$amountToPay.'</p>';
            echo '<p>'.$langs->trans("DoliBinanceWarning").'</p>';
            echo '<p>'.$langs->trans("DoliBinanceAskForPayment").'</p>';
            echo '<p><label for="doliBinanceTransaction">'.$langs->trans("DoliBinanceTransaction").'</label>';
            echo '<input type = "text" id = "doliBinanceTransaction" name = "doliBinanceTransaction"></p>';
            echo '<p><label for="doliBinanceFromAddress">'.$langs->trans("DoliBinanceFromAddress").'</label>';
            echo '<input type = "text" id = "doliBinanceFromAddress" name = "doliBinanceFromAddress" required></p>';
            echo '<div id="div_dopayment_dolibinance" class="button buttonpayment">';
            echo '<span class="fa fa-btc"></span>';
            echo '<input type="hidden" name="action" id="action" value="doliBinanceStorePayment">';
            echo '<input type="hidden" name="asset" value="'.$asset.'">';
            echo '<input type="hidden" name="network" value="'.$network.'">';
            echo '<input type="hidden" name="address" value="'.$address.'">';
            echo '<input type="hidden" name="amountToPay" value="'.$amountToPay.'">';
            echo '<input type="submit" id="dopayment_dolibinance" name="dopayment_dolibinance" value="'.$langs->trans("DoliBinanceStorePayment").'"><br>';
            echo '<span class="buttonpaymentsmall">'.$langs->trans("DoliBinancePaymentButton").'</span>';
            echo '</div>';
        }else if ($action=='doliBinanceStorePayment' && !empty ($dopayment_dolibinance)){
            //save the transaction
            require_once __DIR__ . '/transaction.class.php';
            $transaction = new Transaction($object->db);
            $transaction->fk_facture = $object->id;
            $transaction->transaction_hash = GETPOST('doliBinanceTransaction');
            $transaction->asset = GETPOST("asset");
            $transaction->network = GETPOST("network");
            $transaction->to_address = GETPOST("address");
            $transaction->from_address = GETPOST('doliBinanceFromAddress');
            $transaction->amount_to_pay = GETPOST("amountToPay");
            $transaction->creation_date = dol_now();
            $user = new User($object->db);
            $user->fetch($object->user_author);
            $transaction->create($user);

            echo '<p>'.$langs->trans("DoliBinancePaymentStoredMessage").'</p>';
            if(!empty($transaction->transaction_hash)){
                echo '<p>'.$langs->trans("DoliBinanceTransactionDisplay").$transaction->transaction_hash.'</p>';
            }
            echo '<p>'.$langs->trans("DoliBinanceFromAddressDisplay").$transaction->from_address.'</p>';
            echo '<div id="div_dopayment_dolibinance" class="button buttonpayment">';
            echo $langs->trans("DoliBinancePaymentStored").'<br>';
            echo '<span class="buttonpaymentsmall">'.$langs->trans("DoliBinancePaymentButton").'</span>';
            echo '</div>';
        }
    }
    
    public function getValidPayment($parameters, &$object, &$action, $hookmanager){
        $parameters['validpaymentmethod']['dolibinance'] = 'valid';
    }
}    
