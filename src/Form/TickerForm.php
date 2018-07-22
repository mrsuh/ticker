<?php

namespace App\Form;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TickerForm extends AbstractType
{
    private $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'project',
            EntityType::class,
            [
                'choices'      => $this->projectRepository->findAll(),
                'class'        => Project::class,
                'required'     => true,
                'choice_label' => 'name'
            ]
        )->add(
            'name',
            TextType::class,
            [
                'required' => true,
            ]
        )->add(
            'submit',
            SubmitType::class
        );
    }

    public function getName()
    {
        return 'ticker';
    }
}
