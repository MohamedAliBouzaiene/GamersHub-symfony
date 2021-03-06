<?php

namespace App\Form;

use App\Entity\Mission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('badge',FileType::class,array("mapped" => false,))
            ->add('description', TextareaType::class)
            ->add('prize')
            ->add('attribute',ChoiceType::class, [
                'choices'  => [
                    'likes' => 'games'
                ]
            ])
            ->add('operator',ChoiceType::class, [
                'choices'  => [
                    'equal to' => '==',
                    'greater than' => '>',
                    'greater or equal to' => '>=',
                    'less than' => '<',
                    'less or equal to' => '<=',
                    'not equal to' => '!='
                ]
            ])
            ->add('variable')
            ->add('submit', SubmitType::class,['attr'=>['class'=>'btn btn-success']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
