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

namespace VIB\FliesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * InjectionVialSimpleType class
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
        return "injectionvial_basic";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('setupDate', 'datepicker', array('label' => 'Injection date'))
                ->add('flipDate', 'datepicker', array(
                        'label' => 'Check date',
                        'required'  => false
                    )
                )
                ->add('injectionType', 'choice', array(
                        'choices' => array(
                            'phiC31'      => 'phiC31',
                            'P-element'   => 'P-element',
                            'piggyBac'    => 'piggyBac',
                            'Minos'       => 'Minos',
                            'phiC31 RMCE' => 'phiC31 RMCE',
                            'Flp RMCE'    => 'Flp RMCE',
                            'Cre RMCE'    => 'Cre RMCE'
                        ),
                        'label' => 'Injection type',
                    )
                )
                ->add('constructName', 'text', array(
                        'label' => 'Construct name',
                        'required' => true
                    )
                )
                ->add('targetStock', 'entity_typeahead', array(
                        'property'  => 'name',
                        'class'     => 'VIBFliesBundle:Stock',
                        'label'     => 'Target stock',
                        'required'  => false
                    )
                )
                ->add('targetStockVial', 'text_entity', array(
                        'property'  => 'id',
                        'class'     => 'VIBFliesBundle:StockVial',
                        'format'    => '%06d',
                        'required'  => false,
                        'label'     => 'Target stock source vial',
                        'attr' => array('class' => 'barcode'),
                        'widget_addon_append' => array(
                            'icon' => 'qrcode'
                        )
                    )
                )
                ->add('embryoCount', 'number', array(
                        'label' => 'Embryo count'
                    )
                )
                ->add('vendor', 'text', array(
                        'label' => 'Vendor',
                        'required' => false
                    )
                )
                ->add('receiptDate', 'datepicker', array(
                        'label' => 'Receipt date',
                        'required'  => false
                    )
                )
                ->add('orderNo', 'text', array(
                        'label' => 'Order number',
                        'required' => false
                    )
                )
                ->add('notes', 'textarea', array(
                        'label' => 'Notes',
                        'required' => false
                    )
                );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                 'inherit_data' => true
            )
        );
    }
}
