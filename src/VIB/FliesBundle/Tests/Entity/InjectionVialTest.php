<?php

/*
 * Copyright 2013 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace VIB\FliesBundle\Tests\Entity;

use VIB\FliesBundle\Entity\Vial;
use VIB\FliesBundle\Entity\InjectionVial;
use VIB\FliesBundle\Entity\StockVial;
use VIB\FliesBundle\Entity\Stock;

class InjectionVialTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider vialProvider
     */
    public function testConstruct($vial)
    {

    }

    /**
     * @dataProvider vialProvider
     */
    public function testGetLabelText($vial)
    {
        $this->assertEquals("construct ➔ test stock", $vial->getLabelText());
    }
    
    /**
     * @dataProvider vialProvider
     */
    public function testGetAltLabelText($vial)
    {
        $this->assertEquals("construct ➔ test / test", $vial->getAltLabelText());
    }

    /**
     * @dataProvider vialProvider
     */
    public function testAddChild($vial)
    {
        $child = new InjectionVial();
        $vial->addChild($child);
        $this->assertEquals($vial->getInjectionType(), $child->getInjectionType());
        $this->assertEquals($vial->getConstructName(), $child->getConstructName());
        $this->assertEquals($vial->getTargetStock(), $child->getTargetStock());
        $this->assertEquals($vial->getTargetStockVial(), $child->getTargetStockVial());
        $this->assertEquals($vial->getEmbryoCount(), $child->getEmbryoCount());
        $this->assertEquals($vial->getVendor(), $child->getVendor());
        $this->assertEquals($vial->getOrderNo(), $child->getOrderNo());
    }

    /**
     * @dataProvider vialProvider
     */
    public function testParent($vial)
    {
        $parent = new InjectionVial();
        $vial->setParent($parent);
        $this->assertEquals($parent->getInjectionType(), $vial->getInjectionType());
        $this->assertEquals($parent->getConstructName(), $vial->getConstructName());
        $this->assertEquals($parent->getTargetStock(), $vial->getTargetStock());
        $this->assertEquals($parent->getTargetStockVial(), $vial->getTargetStockVial());
        $this->assertEquals($parent->getEmbryoCount(), $vial->getEmbryoCount());
        $this->assertEquals($parent->getVendor(), $vial->getVendor());
        $this->assertEquals($parent->getOrderNo(), $vial->getOrderNo());
    }

    /**
     * @dataProvider vialProvider
     */
    public function testGetType($vial)
    {
        $this->assertEquals("injection", $vial->getType());
    }

    /**
     * @dataProvider vialProvider
     */
    public function testGetName($vial)
    {
        $this->assertEquals("construct ➔ test stock", $vial->getLabelText());
    }

    /**
     * @dataProvider vialProvider
     */
    public function testGetProgress($vial)
    {
        $date = new \DateTime();
        $vial->setSetupDate($date);
        $this->assertEquals(0,$vial->getProgress());
        $date->sub(new \DateInterval('P13D'));
        $vial->setSetupDate($date);
        $this->assertEquals(1,$vial->getProgress());
    }

    /**
     * @dataProvider vialProvider
     */
    public function testGetDefaultFlipDate($vial)
    {
        $date = clone $this->date;
        $date->add(new \DateInterval('P13D'));
        $this->assertEquals($date,$vial->getDefaultFlipDate());
    }

    public function vialProvider()
    {
        $vial = new FakeInjectionVial();

        return array(array($vial));
    }

    protected function setUp()
    {
        $this->date = new \DateTime('2000-01-01 00:00:00');
    }
}

class FakeInjectionVial extends InjectionVial
{
    public function __construct(Vial $template = null, $flip = true)
    {
        parent::__construct($template, $flip);
        $this->id = 1;
        $this->setupDate = new \DateTime('2000-01-01 00:00:00');
        $this->targetStockVial = new StockVial();
        $this->targetStockVial->setStock(new Stock());
        $this->targetStockVial->getStock()->setName('test stock');
        $this->targetStockVial->getStock()->setGenotype('test / test');
        $this->embryoCount = 100;
        $this->constructName = 'construct';
    }
}
