<?php

/**
 * @param string $priceXMLFile
 * @return array|false
 */
function PrimaweraPriceQtyUpdate(string $priceXMLFile)
{
    $updatedProductIDs = [];
    if(empty($priceXMLFile)) return false;

    $allUpdateArray = PrimaweraAllProductGetPrices($priceXMLFile);

    if(!count($allUpdateArray)) return false;

    $eans = array_keys($allUpdateArray);

    $allProductIDs = PrimaweraGetProductIDsByEANs($eans);
    foreach ($allProductIDs as $productID) {
        if(is_numeric($productID)){

            $productEAN = get_field('primawera_ean', $productID);

            if(!empty($productEAN)){
                $productObj = wc_get_product($productID);
                $primaweraInfo = $allUpdateArray[$productEAN];

                if(is_numeric($primaweraInfo['price'])) {
                    $productObj->set_regular_price($primaweraInfo['price']);
                }

                if(is_numeric($primaweraInfo['qty'])) {
                    $productObj->set_stock_quantity($primaweraInfo['qty']);
                    $stock_status = ($primaweraInfo['qty'] > 0) ? 'instock' : 'outofstock';
                    $productObj->set_stock_status($stock_status);
                }

                $updatedProductIDs[] = $productID;

                unset($productObj);
            }


        }

    }
    return $updatedProductIDs;
}
