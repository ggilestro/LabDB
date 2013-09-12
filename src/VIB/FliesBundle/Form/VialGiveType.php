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
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotNull;


/**
 * VialGiveType class
 *
 * @author Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 */
class VialGiveType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "vial_give";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('source', 'text_entity', array(
                        'property' => 'id',
                        'class'    => 'VIBFliesBundle:Vial',
                        'format'   => '%06d',
                        'label'    => 'Source',
                        'attr' => array('class' => 'barcode'),
                        'widget_addon' => array(
                            'icon' => 'qrcode',
                            'type' => 'append'),
                        'constraints' => array(
                            new NotNull())))
                ->add('user', 'user_typeahead', array(
                        'label'     => 'Recipient',
                        'required'  => true,
                        'widget_addon' => array(
                            'icon' => 'user',
                            'type' => 'append')))
                ->add('type', 'choice', array(
                        'choices'   => array('give' => 'Just give this vial',
                                             'flip' => 'Flip and give this vial',
                                             'flipped' => 'Flip this vial and give the flipped vial'),
                        'expanded'  => true,
                        'label'     => 'Flip',
                        'required'  => false,
                        'empty_value' => false,
                        'attr'      => array('class' => 'input-text')))
                ->add('size', 'choice', array(
                        'choices'   => array('small' => 'small',
                                             'medium' => 'medium',
                                             'large' => 'large'),
                        'expanded'  => true,
                        'label'     => 'Destination size',
                        'required'  => false,
                        'empty_value' => false,
                        'attr'      => array('class' => 'input-text')));
    }
}