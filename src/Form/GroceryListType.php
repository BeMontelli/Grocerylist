<?php

namespace App\Form;

use App\Entity\GroceryList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\CallbackTransformer;

class GroceryListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class, [
                'empty_data' => '',
                'label' => 'List title'
            ])
            ->add('slug',TextType::class, [
                'empty_data' => '',
                'required' => false,
                'label' => 'Slug'
            ])
            ->add('publicSlug',TextType::class, [
                'empty_data' => null,
                'required' => false,
                'label' => 'Public slug',
                'help' => t('app.admin.lists.publicslug.form.help'),
            ])
            ->add('comments',TextareaType::class, [
                'empty_data' => '',
                'required' => false,
                'attr' => array('class' => 'txtarea__autoh'),
                'label' => 'Comments'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save List'
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT,$this->autoSlug(...))
            ->addEventListener(FormEvents::POST_SUBMIT,$this->autoTimestamps(...))
        ;
    }

    public function autoSlug(PreSubmitEvent $event) : void {
        $slugger = new AsciiSlugger();
        $data = $event->getData();

        $data['slug'] = $slugger->slug(strtolower($slugger->slug((!empty($data['slug'])) ? $data['slug'] : $data['title'])));
        
        $data['publicSlug'] = (!empty($data['publicSlug'])) ? $slugger->slug($slugger->slug(strtolower($data['publicSlug']))): null;

        $event->setData($data);
    }

    public function autoTimestamps(PostSubmitEvent $event) : void {
        $data = $event->getData();
        if(!$data instanceof GroceryList) return;

        if(!$data->getId()) $data->setCreatedAt(new \DateTimeImmutable());
        $data->setUpdatedAt(new \DateTimeImmutable());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GroceryList::class,
        ]);
    }
}
