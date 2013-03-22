<?php

/*
 * Copyright 2011 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
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

namespace VIB\FliesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation as Serializer;

use Symfony\Component\Validator\Constraints as Assert;

use \DateInterval;

/**
 * CrossVial class
 * 
 * @ORM\Entity(repositoryClass="VIB\FliesBundle\Repository\CrossVialRepository")
 * @Serializer\ExclusionPolicy("all")
 * 
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CrossVial extends Vial {
    
    /**
     * @ORM\Column(type="boolean")
     * @Serializer\Expose
     */
    protected $sterile;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose
     */
    protected $successful;
    
    /**
     * @ORM\ManyToOne(targetEntity="Vial", inversedBy="maleCrosses")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\NotBlank(message = "Male must be specified")
     */
    protected $male;
        
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     */
    protected $maleName;
    
    /**
     * @ORM\ManyToOne(targetEntity="Vial", inversedBy="virginCrosses")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\NotBlank(message = "Virgin must be specified")
     */
    protected $virgin;
        
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose
     */
    protected $virginName;
    
    /**
     * @ORM\OneToMany(targetEntity="Stock", mappedBy="sourceCross")
     */
    protected $stocks;

    

    /**
     * Construct CrossVial
     *
     * @param \VIB\FliesBundle\Entity\CrossVial $parent
     * @param boolean $flip
     */
    public function __construct(Vial $template = null, $flip = false) {
        parent::__construct($template, $flip);
        $this->setSuccessful(null);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function inheritFromTemplate(Vial $template = null) {
        parent::inheritFromTemplate($template);
        if ($template instanceof CrossVial) {
            $this->setMale($template->getMale());
            $this->setMaleName($template->getMaleName());
            $this->setVirgin($template->getVirgin());
            $this->setVirginName($template->getVirginName());
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function setTrashed($trashed) {
        parent::setTrashed($trashed);
        if ((! $trashed)&&($this->isSterile())) {
            $this->setSterile(false);
            $this->setSuccessful(null);
        }
    }
    
    /**
     * If cross sterile
     * 
     * @return boolean
     */
    public function isSterile() {
        return $this->hasProduced() ? false : $this->sterile;
    }

    /**
     * Set sterile
     * 
     * @param boolean $sterile
     */
    public function setSterile($sterile) {
        $this->sterile = $this->hasProduced() ? false : $sterile;
        if ($this->sterile) {
            $this->setSuccessful(false);
            $this->setTrashed(true);
        }
    }
    
    /**
     * Has this cross produced stocks or crosses
     * 
     * @return boolean
     */
    public function hasProduced() {
        return ((count($this->getStocks()) > 0)||(count($this->getCrosses()) > 0));
    }
    
    /**
     * Is cross successful
     * 
     * @return boolean|null
     */
    public function isSuccessful() {
        if ($this->isAlive()) {
            return $this->hasProduced() ? true : $this->successful;
        } else {
            if ($this->hasProduced()) {
                return true;
            } else {
                return $this->successful !== null ? $this->successful : false;
            }
        }
    }

    /**
     * Set successful
     * 
     * @param boolean|null $successful
     */
    public function setSuccessful($successful) {
        $this->successful = $this->hasProduced() ? true : $successful;
        if (($this->successful === true)||($this->successful === null)) {
            $this->setSterile(false);
        }
    }

    /**
     * Set outcome
     * 
     * @param string $outcome
     */
    public function setOutcome($outcome) {
        switch ($outcome) {
            case 'successful':
                $this->setSuccessful(true);
                break;
            case 'failed':
                $this->setSuccessful(false);
                break;
            case 'sterile':
                $this->setSterile(true);
                break;
            case 'undefined':
                $this->setSuccessful(null);
                break;
        }
    }
    
    /**
     * Get outcome
     * 
     * @return $string
     */
    public function getOutcome() {
        if ($this->isSterile()) {
            return 'sterile';
        } elseif ($this->isSuccessful() === true) {
            return 'successful';
        } elseif ($this->isSuccessful() === false) {
            return 'failed';
        } else {
            return 'undefined';
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLabelText()
    {
        return preg_replace(array('/\s?,\s?/','/\s?\;\s?/'),array(', ','; '),$this->getVirginName()) .
               " ☿ ✕ " .
               preg_replace(array('/\s?,\s?/','/\s?\;\s?/'),array(', ','; '),$this->getMaleName()) .
               " ♂";
    }
    
    /**
     * {@inheritdoc}
     */
    public function addChild(Vial $child = null) {
        parent::addChild($child);
        if ($child instanceof CrossVial) {
            $this->setMale($child->getMale());
            $this->setMaleName($child->getMaleName());
            $this->setVirgin($child->getVirgin());
            $this->setVirginName($child->getVirginName());
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function setParent(Vial $parent = null) {
        parent::setParent($parent);
        if ($parent instanceof CrossVial) {
            $this->setMale($parent->getMale());
            $this->setMaleName($parent->getMaleName());
            $this->setVirgin($parent->getVirgin());
            $this->setVirginName($parent->getVirginName());
        }
    }
    
    /**
     * {@inheritdoc}
     * 
     * @Assert\True(message = "Parent vial must hold a cross")
     */
    public function isParentValid() {
        return (null === $this->getParent())||($this->getType() == $this->getParent()->getType());
    }
    
    /**
     * {@inheritdoc}
     */
    public function getType() {
        return 'cross';
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->getVirginName() . " ☿ ✕ " . $this->getMaleName() . " ♂";
    }
    
    /**
     * Set male
     *
     * @param VIB\FliesBundle\Entity\Vial $male
     */
    public function setMale(Vial $male = null)
    {
        $this->male = $male;
        if ($this->male != null)
            if (($this->male instanceof StockVial)&&($this->maleName == ''))
                $this->maleName = $this->male->getStock()->getName();
    }

    /**
     * Get male
     *
     * @return VIB\FliesBundle\Entity\Vial
     */
    public function getMale()
    {
        return $this->male;
    }
    
    /**
     * Set maleName
     *
     * @param string $maleName
     */
    public function setMaleName($maleName)
    {
        if ($this->male != null)
            if (($this->male instanceof StockVial)&&($maleName == ''))
                $maleName = $this->male->getStock()->getGenotype();
        $this->maleName = $maleName;
    }

    /**
     * Get maleName
     *
     * @return string
     */
    public function getMaleName()
    {
        return $this->maleName;
    }

    /**
     * Check if male name is specified when male source is a cross
     * 
     * @Assert\True(message = "Male genotype must be specified")
     * 
     * @return boolean
     */
    public function isMaleValid() {
        return ! (($this->getMale() instanceof CrossVial)&&(trim($this->getMaleName()) == ""));
    }
    
    /**
     * Set virgin
     *
     * @param VIB\FliesBundle\Entity\Vial $virgin
     */
    public function setVirgin(Vial $virgin = null)
    {
        $this->virgin = $virgin;
        if ($this->virgin != null)
            if (($this->virgin instanceof StockVial)&&($this->virginName == ''))
                $this->virginName = $this->virgin->getStock()->getGenotype();
    }

    /**
     * Get virgin
     *
     * @return VIB\FliesBundle\Entity\Vial
     */
    public function getVirgin()
    {
        return $this->virgin;
    }
    
    /**
     * Set virginName
     *
     * @param string $virginName
     */
    public function setVirginName($virginName)
    {
        if ($this->virgin != null)
            if (($this->virgin instanceof StockVial)&&($virginName == ''))
                $virginName = $this->virgin->getStock()->getGenotype();
        $this->virginName = $virginName;
    }

    /**
     * Get maleName
     *
     * @return string
     */
    public function getVirginName()
    {
        return $this->virginName;
    }

    /**
     * Check if virgin name is specified when virgin source is a cross
     * 
     * @Assert\True(message = "Virgin genotype must be specified")
     * 
     * @return boolean
     */
    public function isVirginValid() {
        return ! (($this->getVirgin() instanceof CrossVial)&&(trim($this->getVirginName()) == ""));
    }
    
    /**
     * Get stocks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStocks()
    {
        return $this->stocks;
    }
    
    /**
     * Get default flip date
     * 
     * @return \DateTime
     */
    public function getDefaultFlipDate() {
        $interval = new DateInterval('P' . $this->getGenerationTime() . 'D');
        $setup = clone $this->getSetupDate();
        $setup->add($interval);
        return $setup;
    }
}
