<?php

include_once '../vendor/autoload.php';

use BraienStorm\BinPacker\Model\Block;
use BraienStorm\BinPacker\Model\Node;
use BraienStorm\BinPacker\Model\Bin;
use BraienStorm\BinPacker\Helper\Visualizer;

$Boxes = [];
$data =  file_get_contents('ladak.json');
$a = json_decode($data, true);

foreach($a as $b){
    for ($i=0; $i <$b['componentPiece'] ; $i++) { 
        $Boxes[] = new Block($b['componentWidth'],$b['componentHeight'],true,$b['componentId']);
    }
}
$lemezek = [['witdh' => 1000,'height' =>2000],['witdh' => 2500,'height' =>1250],['witdh' => 3000,'height' =>1500]];

$i=0;
$sumused = 0;
$sumfree = 0;
while(sizeof($Boxes)>0)
{
    $bestBin = new Bin(1,1);
    foreach ($lemezek as $l) {
        $bin = new Bin($l['witdh'],$l['height']);    
        $bin->insertManyBlock($Boxes);
        if($bestBin->getStatistic()['score']<$bin->getStatistic()['score'])
        {
            $bestBin = $bin;            
        }    
        // rotated base sheet
        $bin = new Bin($l['height'],$l['witdh']);    
        $bin->insertManyBlock($Boxes);
        if($bestBin->getStatistic()['score']<$bin->getStatistic()['score'])
        {
            $bestBin = $bin;            
        }    

    }
    $Boxes = $bestBin->getUnpackedBlocks();
    if($bestBin->getWidth()>1){
        $sc = $bestBin->getStatistic();
        $visualizer = new Visualizer();
        $image = $visualizer->visualize($bestBin);
        $image->setFormat('jpg');
        $image->writeImage('bin'.$i.' '.$bestBin->getWidth().' X '.$bestBin->getHeight().'.jpg');
        $i++;
        $sumused = $sumused+$sc['used'];
        $sumfree = $sumfree+$sc['free'];
    }
}

echo 'SumUsedArrea:'.$sumused.PHP_EOL;
echo 'SumFreeArrea:'.$sumfree.PHP_EOL;



