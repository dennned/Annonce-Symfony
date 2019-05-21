<?php

namespace AppBundle\Form;

use AppBundle\Entity\OptionName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParameterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category')
            ->add('nameEn')
            ->add('nameFr')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Integer' => 'Integer',
                    'String' => 'String',
                    'Date' => 'Date',
                ]
            ])
            ->add('tag', ChoiceType::class, [
                'choices' => [
                    'Input' => 'Input',
                    'Select' => 'Select',
                    'Multiselect' => 'Multiselect',
                ]
            ])
            ->add('isFilter', null, ['label' => 'Show in filters'])
            ->add('filtersIndex', null, ['label' => 'Index in filters'])

            ->add('isColumn', null, ['label' => 'Show in columns'])
            ->add('columnsIndex', null, ['label' => 'Index in columns'])

            ->add('isPost', null, ['label' => 'Show in posts'])
            ->add('postsIndex', null, ['label' => 'Index in posts'])

            ->add('isRange', null, ['label' => 'Is range in filters'])
            ->add('isBold', null, ['label' => 'Is bold in posts'])

            ->add('options', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => true,
                'label' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Parameter'
        ));
    }
}
