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

namespace VIB\FliesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * FlyCrossType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class FlyCrossType extends AbstractType
{
    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return "FlyCrossType";
    }
    
    /**
     * Build form
     *
     * @param Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('maleName', 'text', array('label' => 'Male name'))
                ->add('virginName', 'text', array('label' => 'Virgin name'))
                ->add('vial', new FlyVialType())
                ->add('male', 'text_entity', array(
                        'property'     => 'id',
                        'class' => 'VIBFliesBundle:FlyVial',
                        'required' => false))
                ->add('virgin', 'text_entity', array(
                        'property'     => 'id',
                        'class' => 'VIBFliesBundle:FlyVial',
                        'required' => false));
    }

    /**
     * Set default options
     * 
     * @param \Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VIB\FliesBundle\Entity\FlyCross',
        ));
    }
}

?>