<?php
namespace BraienStorm\BinPacker\Helper;

class Visualizer
{
    private $image;
    public function visualize(Bin $bin)
    {
        $this->image = new \Imagick();
        $this->image->newImage($bin->getWidth(), $bin->getHeight(), new \ImagickPixel('white'));

        $draw = new \ImagickDraw();

        $draw->setFillColor(new \ImagickPixel('whitesmoke'));
        $draw->setStrokeColor(new \ImagickPixel('black'));
        // fill bin 
        
        $draw->rectangle(0,0,$bin->getWidth(),$bin->getHeight());
        

        $draw->setFillColor(new \ImagickPixel('white'));

        $node = $bin->getNode();
        $this->drawBlock($draw,$node);
        $this->image->drawImage($draw);
        $drawTxt = new \ImagickDraw();
        $this->drawText($drawTxt,$node);
        $this->image->drawImage($drawTxt);

        return $this->image;
    }
    private function findBestFontsize(\ImagickDraw $draw, Block $block,$text,$maxsite = 180){

        $angle = 0;
        if($block->getWidth()<$block->getHeight())
        {
            $imageWidth = $block->getHeight()-20;
            $imageHeight = $block->getWidth()-20;    
            $angle=90;
        }else{
            $imageWidth = $block->getWidth()-20;
            $imageHeight = $block->getHeight()-20;
        }
        $fontSize = $maxsite;

        $lines = explode( "\n", $text );
        $lineCount = count( $lines );
        do{
            $textHeight=0<
            $fit=false;
            $draw->setFontSize( $fontSize );
            $fontMetrics = $this->image->queryFontMetrics( $draw, $lines[0], true );
            $fitline = 0;
            if($fontMetrics['textHeight'] *$lineCount<$imageHeight){
               foreach ($lines as $line) {
                {
                    $fontMetrics = $this->image->queryFontMetrics( $draw, $line, true );
                    if($fontMetrics['textWidth']<$imageWidth)
                    {
                        $fitline++;    
                    }
                }
                if($fitline == $lineCount)
                {
                    $fit = true;     
                    $textHeight = $fontMetrics['textHeight'];
                }
               }
            }
            if($fit==false)
            {
                $fontSize--; 
            }

        }while($fit == false);
        return ['angle' =>$angle,'fontsize' =>$fontSize,'textHeight' =>$textHeight];
    }
    private function drawBlock(\ImagickDraw $draw,Node $node){
        if($node == null)
            return;
        if($node->isUsed())
        {
            $block = $node->getBlock();

            if($block->getWidth()>$node->getWidth() || $block->getHeight()>$node->getHeight()) 
            {
                $block->rotate();
            }
            $draw->rectangle($node->getX(), $node->getY(), $node->getX() + $block->getWidth(), $node->getY() + $block->getHeight());
            $this->drawBlock($draw,$node->getRight());
            $this->drawBlock($draw,$node->getDown());
        }
    }
    private function drawText(\ImagickDraw $draw,Node $node){
        if($node == null)
            return;
        if($node->isUsed())
        {
            $block = $node->getBlock();

            if($block->getWidth()>$node->getWidth() || $block->getHeight()>$node->getHeight()) 
            {
                $block->rotate();
            }
            $txt = $block->getId()."\n".sprintf('%s x %s', $block->getWidth(), $block->getHeight());
            $lines = explode( "\n", $txt );
            $lineCount = count( $lines );
            $a = $this->findBestFontsize($draw,$block,$txt);
            $draw->setFontSize( $a['fontsize'] );
            if($a['angle'] == 0)
            {
                $i=1;
                foreach ($lines as $line) {
                    $this->image->annotateImage($draw,$node->getX()+10, $node->getY()+$a['textHeight']*$i+10,$a['angle'], $line);
                    $i++;
                }
            }
            if($a['angle'] == 90)
            {
                $i=1;
                foreach ($lines as $line) {
                    $this->image->annotateImage($draw,$node->getX()+($a['textHeight']*$lineCount-$a['textHeight']*$i)+10, $node->getY()+10,$a['angle'], $line);
                    $i++;
                }
            }
            $this->drawText($draw,$node->getRight());
            $this->drawText($draw,$node->getDown());
        }
    }
}
