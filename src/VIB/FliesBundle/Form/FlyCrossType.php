<?php

namespace VIB\FliesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FlyCrossType extends AbstractType
{
    public function getName()
    {
        return "FlyCrossType";
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('maleName', 'text')
                ->add('virginName', 'text')
                ->add('vial', new FlyVialSimpleType());
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'VIB\FliesBundle\Entity\FlyCross',
        );
    }
}

?>