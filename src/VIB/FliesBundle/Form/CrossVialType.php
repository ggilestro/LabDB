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
 * CrossVialType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class CrossVialType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "crossvial";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('basic', new Type\CrossVialType(), array(
                        'horizontal' => false,
                        'label_render' => false,
                        'widget_form_group' => false
                    )
                )
                ->add('sourceVial', 'text_entity', array(
                        'property'  => 'id',
                        'class'     => 'VIBFliesBundle:CrossVial',
                        'format'    => '%06d',
                        'label'     => 'Flipped from',
                        'attr'      => array('class' => 'barcode'),
                        'widget_addon_append' => array(
                            'icon' => 'qrcode'
                        ),
                        'required'  => false
                    )
                )
                ->add('options', new Type\VialOptionsType(), array(
                        'horizontal' => false,
                        'label_render' => false,
                        'widget_form_group' => false
                    )
                )
                ->add('outcome', 'choice', array(
                        'choices' => array(
                            'successful' => 'Successful',
                            'failed'     => 'Failed',
                            'sterile'    => 'Sterile',
                            'undefined'  => 'Undefined'
                        ),
                        'empty_value' => false,
                        'expanded'    => true,
                        'label'       => 'Outcome',
                        'required'    => false,
                    )
                )
                ->add('trashed', 'checkbox', array(
                        'label'    => '',
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
            'data_class' => 'VIB\FliesBundle\Entity\CrossVial',
            'error_mapping' => array(
                'basic.maleValid' => 'maleName',
                'basic.virginValid' => 'virginName')));
    }
}
