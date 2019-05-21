<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use AppBundle\Entity\OptionName;
use AppBundle\Entity\Parameter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MyPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['required'=>true])
            ->add('text', TextareaType::class, ['required'=>true])
//            ->add('user', null, ['required'=>true])
//            ->add('isValid', null, ['label' => 'Show this post'])
            ->add('category')
            ->add('options',null, ['mapped' => false]);
//            ->add('params',null, ['mapped' => 'false']);
//            ->add('options', CollectionType::class, [
//                'entry_type' => OptionName::class,
//                'mapped' => false,
//                'entry_options' => ['label' => false],
//                'allow_add' => true,
//                'allow_delete' => true,
//                'by_reference' => false,
//                'required' => true,
//                'label' => false
//            ]);

//            ->add('category', null, [
//                'required' => true,
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('child')
//                        ->join('child.parent', 'parent')
//                        ->where('parent.id > 0');
//                },
//            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Post',
            'allow_extra_fields' => true,
        ));
    }
}
