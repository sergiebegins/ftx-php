<?php
include_once ('./vendor/autoload.php');
use FTX\FTX;

// Unauthenticated
$ftx = FTX::create();

// Authenticated
$ftx = FTX::create('*****************', '***************')->onSubaccount('test');

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

                $coin[$k] = $v;
                $coin[$k]['dibOrt']=$dibOrt[$v['name']];
                $coin[$k]['dibOrtUzakligi']=$v['last']-$dibOrt[$v['name']];
                $coin[$k]['dibGunluk']=$v['last']-$v['lowerBound'];
                $yuzde = round(100-($v['lowerBound']*100/$v['last']),6);
                $coin[$k]['yuzde']=$yuzde;
            }


    }
    usort($coin, "cmp");


    foreach ($coin as $k=>$v){
        $peakPoint = round($v['last']+(($v['last']/100)*$fiyat),6);
        $size = dolar($fiyat,$peakPoint);



        if(!empty($dibDegerler[$v['name']])){
            foreach ($dibDegerler[$v['name']] as $k4=>$v4){
                $dibYuzde = round(100-($v4*100/$v['last']),6);
                if($dibYuzde<1){
                    echo $v['name'].'-'.$k4.'------'.$dibYuzde.'---------';
                }
            }
            echo '<br>';
        }




        //LONG

//$buy = $ftx->orders()->create(['market' => $v['name'], 'type' => 'limit', 'price' => $peakPoint, 'size' => $size, 'side' => 'buy', 'reduceOnly' => true])->place();
//$sell = $ftx->orders()->create(['market' => $v['name'], 'type' => 'limit', 'price' => round(($peakPoint/100)*101,6), 'size' => $size, 'side' => 'sell', 'reduceOnly' => true])->place();
//$stop = $ftx->conditionalOrders()->create(['market' => $v['name'], 'type' => 'stop', 'triggerPrice' => round(($peakPoint/101)*100,6), 'size' => $size, 'side' => 'sell', 'reduceOnly' => true])->place();



        //SHORT
    }



}



function dolar($dolar,$kactan){
    return round($dolar/$kactan,6);
}
function cmp($a, $b)
{
    return strcmp($a["yuzde"], $b["yuzde"]);
}
