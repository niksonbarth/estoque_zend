<?php

namespace Estoque\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\Element;
use Zend\Form\Form;

class ProdutoForm extends Form
{
    public function __construct(ObjectManager $entityManager)
    {
        parent::__construct('formProduto');

        $this->add([
            'type' => 'Text',
            'name' => 'nome',
            'attributes' => [
                'class' => 'form-control'
            ]
        ]);

        $this->add([
            'type' => 'number',
            'name' => 'preco',
            'attributes' => [
                'class' => 'form-control'
            ]
        ]);

        $this->add([
            'type' => 'Textarea',
            'name' => 'descricao',
            'attributes' => [
                'class' => 'form-control'
            ]
        ]);

        $this->add(new Element\Csrf('csrf'));

        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'categoria',
            'options' => [
                'object_manager' => $entityManager,
                'target_class' => 'Estoque\Entity\Categoria',
                'property' => 'nome',
                'empty_option' => 'escolha uma categoria'
            ],
            'attributes' =>[
                'class' => 'form-control'            ]
        ]);
    }
}