<?php declare(strict_types=1);

final class App
{
    public $transaction;
    public $rate;
    public $amount;

    public function loadTransactions(){
        global $argv;
        $transactions = explode("\n", file_get_contents($argv[1]));
        foreach ($transactions as $transaction){
            $data = $this->executeTransaction($transaction);
            $this->printData($data);
        }
    }

    public static function executeTransaction($transaction){
        if (!empty($transaction)){
            $app = new App();
            $app->setTransaction($transaction);
            return $app->binResult();
        }else{
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid transaction format',
                    $transaction
                )
            );
        }
    }

    private function setTransaction($transaction){
        $this->transaction = json_decode($transaction);
    }

    private function binResult(){
        $binResults = file_get_contents('https://lookup.binlist.net/' .$this->transaction->bin);
        if ($binResults && !empty($binResults)){
            $binResults = json_decode($binResults);
            return $this->getRate($binResults);
        }else{
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid transaction format',
                    $this->transaction->bin
                )
            );
        }
    }

    private function getRate($binResults){
        $rate = @json_decode(file_get_contents('https://api.exchangeratesapi.io/latest'), true)['rates'][$this->transaction->currency];
        $this->rate = floatval($rate);
        return $this->fixAmount($binResults);
    }

    private function fixAmount($binResults){
        $amount = $this->transaction->amount;
        if( $this->rate > 0 || $this->transaction->currency != 'EUR' ){
            $amount = $amount / $this->rate;
        }
        $amount = $amount * ($this->isEu($binResults->country->alpha2) ? 0.01 : 0.02);

        /** Alternative Solution As BinResult's json response(if the response currency data is accurate) **/
        // $amount = $amount * ($binResults->country->currency == 'EUR' ? 0.01 : 0.02);

        $this->amount = number_format((float)$amount, 2, '.', '');
        return $this->getAmount();
    }

    private function isEu($cc){
        $euCountryCodes = ['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR',
            'HU', 'IE','IT','LT','LU','LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'];
        return in_array($cc, $euCountryCodes);
    }

    private function getAmount(){
        return $this->amount;
    }

    private function printData($data){
        echo $data;
        print "\n";
    }
}