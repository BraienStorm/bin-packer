<?php
namespace BraienStorm\BinPacker\Model;

class Bin
{
    /**
     * @var int|float|string
     */
    private $height;

    /**
     * @var int|float|string
     */
    private $width;

    /**
     * @var bool
     */
    private $growthAllowed;

    /**
     * @var Node
     * root node
     */
    private $node;

    /**
     * @var int
     */
    private $usedArrea;
    /**
     * @var int
     */
    private $freeArrea;
    /**
     * @var float
     */
    private $score;

    private array $blocks = [];
    public function __construct($width, $height, bool $growthAllowed = false)
    {
        if (!is_numeric($width)) {
            throw new \InvalidArgumentException(sprintf('Bin width must be numeric, "%s" given', $width));
        }

        if (!is_numeric($height)) {
            throw new \InvalidArgumentException(sprintf('Bin height must be numeric, "%s" given', $height));
        }

        $this->width = $width;
        $this->height = $height;
        // set root node 
        $this->node = new Node(0,0,$width,$height);
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(?Node $node): self
    {
        $this->node = $node;

        return $this;
    }
    public function getUnpackedBlocks()
    {
        return $this->blocks;   
    }
    public function getStatistic()
    {   
        $this->usedArrea = 0;
        $this->calcUsedArrea($this->getNode());
        $this->freeArrea = ($this->width*$this->height)-$this->usedArrea;
        $this->score = $this->usedArrea/($this->width*$this->height);
        return ['score' => $this->score,'used' => $this->usedArrea,'free' => $this->freeArrea,'binWidth' => $this->width,'binHeight' => $this->height];
    } 
    private function calcUsedArrea(Node $node)
    {
        if($node == null)
            return;
        if($node->isUsed())
            {
                $block = $node->getBlock();
                $this->usedArrea = $this->usedArrea+($block->getWidth()*$block->getHeight());
                $this->calcUsedArrea($node->getRight());
                $this->calcUsedArrea($node->getDown());
            }

    }
    public function insertBlock(Block $block)
    {
        $node = $this->findNodeWithRotation($this->node, $block);
        if ($node !== null) {
            $node->setBlock($block); 
            $this->splitNode($node, $block->getWidth(), $block->getHeight());
        }else{
            $this->blocks[] = $block;
        }
        foreach($this->blocks as $key => $b)
        {
            $node = $this->findNodeWithRotation($this->node, $b);
            if ($node !== null) {
                $node->setBlock($b); 
                $this->splitNode($node, $b->getWidth(), $b->getHeight());
                unset($this->blocks[$key]);
            }
        }
    }
    public function insertManyBlock($blocks){
        foreach($blocks as  $b)
        {
            $this->blocks[] = $b;
        }
        $this->Calculate($this->node);
    }
    private function Calculate($node)
    {
        if($node == null)
            return;
        if(!$node->isUsed())
        {
            $score1 = $this->findBestFitt($node);
            $id = $score1['id'];
            if($id != null){
                //echo $id.PHP_EOL;
                $b = $this->blocks[$id];
              
                $node->setBlock($b); 
                $this->splitNode($node, $this->blocks[$id]->getWidth(), $this->blocks[$id]->getHeight());
                unset($this->blocks[$id]);
                $this->Calculate($node->getRight());
                $this->Calculate($node->getDown());
            }
        }else{
            $this->Calculate($node->getRight());
            $this->Calculate($node->getDown());
           
        }
    }

    private function findBestFitt(Node $n){       
        $bestid = null; 
        $bestdh= null;
        $bestdw= null;

        $score = null;
        foreach($this->blocks as $key => $b)
        {
            if($b->getWidth()>$n->getWidth() || $b->getHeight()>$n->getHeight()) 
            {
                $this->blocks[$key]->rotate();
            }
            if($b->getWidth()<= $n->getWidth() && $b->getHeight()<=$n->getHeight()) 
            {
                if($bestid == null)
                {
                    $bestid = $key;     
                    $betsarrea = $b->getArrea();
                    $bestdh= $n->getHeight()-$b->getHeight();
                    $bestdw=  $n->getWidth()-$b->getWidth();
                }else{
                    $arrea = $b->getArrea();
                    
                    $dh = $n->getHeight()-$b->getHeight();
                    $dw = $n->getWidth()-$b->getWidth();
                    if($n->getHeight()>$n->getWidth())
                    {
                        if($bestdh>$dh)
                        {   
                            $bestdh= $b->getHeight()-$b->getHeight();
                            $bestdw=  $n->getWidth()-$b->getWidth();
                            $bestid = $key;     
                        }
                    }else{
                        if($bestdw>$dw)
                        {
                            $bestdh= $b->getHeight()-$b->getHeight();
                            $bestdw=  $n->getWidth()-$b->getWidth();
                            $bestid = $key;     
                        }
                    }
                }
            }
        }
        if($bestid != null)
        {
            $score = $this->blocks[$bestid]->getArrea()/$n->getArrea();
        }
        return ['id'=>$bestid,'Score'=>$score];
    }
    private function findNodeWithRotation(Node $root, Block $block)
    {
        if (null === $node = $this->findNode($root, $block->getWidth(), $block->getHeight())) {
            if ($block->isRotatable()) {
                $block->rotate();

                if (null === $node = $this->findNode($root, $block->getWidth(), $block->getHeight())) {
                    $block->rotate(); 
                }
            }
        }
        return $node;
    }
    private function findNode(Node $node, $w, $h): ?Node
    {
        if ($node->isUsed()) {
            return $this->findNode($node->getRight(), $w, $h) ?: $this->findNode($node->getDown(), $w, $h);
        } elseif ($w <= $node->getWidth() && $h <= $node->getHeight()) {
            return $node;
        }
        return null;
    }
    private function splitNode(Node $node, $w, $h)
    {
        $node->setUsed(true);
        $node->setDown(new Node($node->getX(), $node->getY() + $h, $node->getWidth(), $node->getHeight() - $h));
        $node->setRight(new Node($node->getX() + $w, $node->getY(), $node->getWidth() - $w, $h));
        $node->setWidth($w);
        $node->setHeight($h);
        return $node;
    }

}
