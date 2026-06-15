<?php

require_once __DIR__ . '/../lib/dolibinance.lib.php';
require_once __DIR__ . '/transaction.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';


/**
 * Description of DoliBinanceJob
 *
 * @author oandrade
 */
class DoliBinanceJob {
    public function verifyBinancePayments(){       
        global $db, $lang, $user;
        //get deposit history
        $depositHistory = getDepostitHistoryWithSource();
        //get DoliBinance Transaction with status == 0 (not yet received)
        $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . 'dolibinance_transaction WHERE fk_statut = 0'; 
        $rsql = $db->query($sql);
        if (!$rsql){
            return 0;
        }
        //for each transaction stored, we search for a valid binance deposit
        while($object = $db->fetch_object($rsql)){
            $doliBinanceTransaction = new Transaction($db);
            $doliBinanceTransaction->fetch($object->rowid);
            for($i=0;$i<count($depositHistory);$i++){
                if($depositHistory[$i]['address']==$doliBinanceTransaction->to_address
                        && $depositHistory[$i]['sourceAddress']==$doliBinanceTransaction->from_address
                        && $depositHistory[$i]['amount']==$doliBinanceTransaction->amount_to_pay
                        && $depositHistory[$i]['coin']==$doliBinanceTransaction->asset
                        && $depositHistory[$i]['network']==$doliBinanceTransaction->network){
                    if($depositHistory[$i]['status']==1){
                        $doliBinanceTransaction->fk_statut = 1;
                        $doliBinanceTransaction->transaction_hash = $depositHistory[$i]['txId'];
                        $doliBinanceTransaction->update($user);
                        //TODO valider le paiment de la facture correspondante
                        $invoice = new Facture($db);
                        $invoice->fetch($doliBinanceTransaction->fk_facture);                        
                        $result = $invoice->setPaid($user);  
                        if($result){
                            //refresh the invoice
                            $invoice->fetch($invoice->id);
                            if ($invoice->note_private){
                                $invoice->note_private = $invoice->note_private . "\nPaid by DoliBinance Transaction Verification Job";
                            }else {
                                $invoice->note_private = "Paid by DoliBinance Transaction Verification Job";                            
                            }
                            $invoice->update($user);
                        }
                    }
                }
            }
        }
        return 0;
    }
}
