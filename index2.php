<?php
include_once ('./vendor/autoload.php');
use FTX\FTX;

// Unauthenticated
$ftx = FTX::create();

// Authenticated
$ftx = FTX::create('***************', '****************')->onSubaccount('test');

//$markets = $ftx->markets()->all();
//$orderbook = $ftx->markets()->orderbook('BTC-PERP', 100);

$birhafta = time()-60*60*24*7;

$futures =$ftx->futures()->all();
//$fundingRates=$ftx->futures()->fundingRates();
//$subaccounts=$ftx->subaccounts()->all();
//$transfer=$ftx->subaccounts()->transfer('USDT', 1, 'lena', 'test');
$account = $ftx->account()->positions();



$dibDegerler = file_get_contents('./coin.json');
$dibDegerler = json_decode($dibDegerler,true);
$dibToplam = [];
$dibOrt = [];
foreach ($dibDegerler as $k=>$v){

    foreach ($v as $k2=>$v2){
        if(empty($dibToplam[$k])){$dibToplam[$k]=0;}
        $dibToplam[$k]+=$v2;
    }
    if(empty($dibOrt[$k])){$dibOrt[$k]=0;}
    $dibOrt[$k] = round($dibToplam[$k]/count($v),6);
}




$fiyat = 10;
$coin = [];
if($futures['success']){
    foreach ($futures['result'] as $k=>$v){
        if($v['upperBound'] && $v['last'] && str_contains($v['name'],'-PERP') ){
            if($v['last']<1){
                $yuzde = round(1000-($v['lowerBound']*1000/$v['last']),6);
                $yuzde2 = round(1000-($v['last']*1000/$v['upperBound']),6);
                $dibOrtYuzde = round(1000-($dibOrt[$v['name']]*1000/$v['last']),6);
            }else{
                $yuzde = round(100-($v['lowerBound']*100/$v['last']),6);
                $yuzde2 = round(100-($v['last']*100/$v['upperBound']),6);
                $dibOrtYuzde = round(100-($dibOrt[$v['name']]*100/$v['last']),6);
            }



            $coin[$k] = $v;
            $coin[$k]['yuzde']=$yuzde;
            $coin[$k]['yuzde2']=$yuzde2;
            $coin[$k]['dibOrtYuzde']=$dibOrtYuzde;
            $coin[$k]['dibOrtUzakligi']=$dibOrtYuzde-$yuzde;



        }
    }
    usort($coin, "cmp");


    foreach ($coin as $k=>$v){
        $peakPoint = round($v['last']+(($v['last']/100)*$fiyat),6);
        $size = dolar($fiyat,$peakPoint);
        echo '<div';
        if(round($v['dibOrtUzakligi'],0)<1){
            echo ' style="background:yellow" '.$v['dibOrtUzakligi'].'';
        }
        echo '>';
        echo $v['name']."<br>".$v['yuzde']."<br>".$v['dibOrtYuzde']."<br>".$v['dibOrtUzakligi']."<br>";
        echo '</div>';
        //haftalık dib yaz

        if($account['success']){
            if(!in_array($v['name'],array_column($account["result"], 'future'))){

            }
        }



        $candles = $ftx->markets()->candles($v['name'], 86400, 35,new \DateTime(date('Y-m-d h:i:s',$birhafta)), new \DateTime(date('Y-m-d h:i:s',time())));


        if($candles['success']){
            foreach ($candles['result'] as $k2=>$v2){
                $low[$v['name']][] =$v2['low'];
            }
        }






        //LONG

//$buy = $ftx->orders()->create(['market' => $v['name'], 'type' => 'limit', 'price' => $peakPoint, 'size' => $size, 'side' => 'buy', 'reduceOnly' => true])->place();
//$sell = $ftx->orders()->create(['market' => $v['name'], 'type' => 'limit', 'price' => round(($peakPoint/100)*101,6), 'size' => $size, 'side' => 'sell', 'reduceOnly' => true])->place();
//$stop = $ftx->conditionalOrders()->create(['market' => $v['name'], 'type' => 'stop', 'triggerPrice' => round(($peakPoint/101)*100,6), 'size' => $size, 'side' => 'sell', 'reduceOnly' => true])->place();



        //SHORT
    }

    file_put_contents("coin.json",json_encode($low));

}



function dolar($dolar,$kactan){
    return round($dolar/$kactan,6);
}
function cmp($a, $b)
{
    return strcmp($a["dibOrtUzakligi"], $b["dibOrtUzakligi"]);
}
