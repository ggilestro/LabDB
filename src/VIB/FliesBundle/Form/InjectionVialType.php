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

namespace VIB\FliesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * InjectionVialType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class InjectionVialType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "injectionvial";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('setupDate', 'datepicker', array('label' => 'Injection date'))
                ->add('flipDate', 'datepicker', array('label' => 'Check date', 'required'  => false))
                ->add('injectionType', 'choice', array(
                        'choices'   => array('phiC31' => 'phiC31',
                                             'P-element' => 'P-element',
                                             'piggyBac' => 'piggyBac',
                                             'Minos' => 'Minos',
                                             'phiC31 RMCE' => 'phiC31 RMCE',
                                             'Flp RMCE' => 'Flp RMCE',
                                             'Cre RMCE' => 'Cre RMCE'),
                        'label'     => 'Injection type',
                        'attr'      => array('class' => 'input-text')))
                ->add('targetStock', 'entity_typeahead', array(
                        'property'  => 'name',
                        'class'     => 'VIBFliesBundle:Stock',
                        'label'     => 'Target stock',
                        'required'  => false))
                ->add('targetStockVial', 'text_entity', array(
                        'property'  => 'id',
                        'class'     => 'VIBFliesBundle:StockVial',
                        'format'    => '%06d',
                        'required'  => false,
                        'label'     => 'Target stock source vial',
                        'attr' => array('class' => 'barcode')))
                ->add('embryoCount', 'number', array(
                        'label' => 'Embryo count'))
                ->add('vendor', 'text', array(
                        'label' => 'Vendor',
                        'required' => false))
                ->add('receiptDate', 'datepicker', array('label' => 'Receipt date', 'required'  => false))
                ->add('orderNo', 'text', array(
                        'label' => 'Order number',
                        'required' => false))
                ->add('notes', 'textarea', array(
                        'label' => 'Notes',
                        'required' => false))
                ->add('size', 'choice', array(
                        'choices'   => array('small' => 'small',
                                             'medium' => 'medium',
                                             'large' => 'large'),
                        'expanded'  => true,
                        'label'     => 'Vial size',
                        'required'  => false,
                        'attr'      => array('class' => 'input-text')))
                ->add('trashed', 'checkbox', array(
                        'label' => '',
                        'required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VIB\FliesBundle\Entity\InjectionVial',
        ));
    }
}
