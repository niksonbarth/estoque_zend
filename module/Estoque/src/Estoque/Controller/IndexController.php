<?php

namespace Estoque\Controller;

use Doctrine\DBAL\Schema\View;
use Estoque\Entity\Categoria;
use Estoque\Entity\Produto;
use Estoque\Form\ProdutoForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class IndexController extends AbstractActionController
{

    public function IndexAction()
    {

        $pagina = $this->params()->fromRoute('page', 1);
        $qtdPorPagina = 1;
        $offset = ($pagina - 1) * $qtdPorPagina;

        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $respositorio = $entityManager->getRepository('Estoque\Entity\Produto');

        $produtos = $respositorio->getProdutosPaginados($qtdPorPagina, $offset);

        $view_params = array(
            'produtos' => $produtos,
            'qtdPorPagina' => $qtdPorPagina,
        );
        return new ViewModel($view_params);
    }

    public function CadastrarAction()
    {
        if (!$user = $this->identity())
            return $this->redirect()->toUrl('/Usuario');

        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $categoriaRepository = $entityManager->getRepository('Estoque\Entity\Categoria');
        $form = new ProdutoForm($entityManager);

        if ($this->request->isPost()) {
            $nome = $this->request->getPost('nome');
            $preco = $this->request->getPost('preco');
            $descricao = $this->request->getPost('descricao');
            $categoria = $categoriaRepository->find($this->request->getPost('categoria'));

            $produto = new Produto($nome, $preco, $descricao);
            $produto->setCategoria($categoria);
            $form->setInputFilter($produto->getInputFilter());
            $form->setData($this->request->getPost());

            if ($form->isValid())
            {
                $entityManager->persist($produto);
                $entityManager->flush();

                return $this->redirect()->toUrl('/Index');
            }
        }

        return new ViewModel(['form' => $form]);
    }

    public function RemoverAction()
    {
        $id = $this->params()->fromRoute('id');

        if ($this->request->isPost()) {
            $id = $this->request->getPost('id');

            $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $respositorio = $entityManager->getRepository('Estoque\Entity\Produto');
            $produto = $respositorio->find($id);

            $entityManager->remove($produto);
            $entityManager->flush();

            return $this->redirect()->toUrl('/Index');
        }

        return new ViewModel(['id' => $id]);
    }

    public function EditarAction()
    {
        $id = $this->params()->fromRoute('id');

        if (is_null($id)) {
            $id = $this->request->getPost('id');
        }

        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $respositorio = $entityManager->getRepository('Estoque\Entity\Produto');
        $produto = $respositorio->find($id);

        if ($this->request->isPost()) {
            $produto->setNome($this->request->getPost('nome'));
            $produto->setPreco($this->request->getPost('preco'));
            $produto->setDescricao($this->request->getPost('descricao'));

            $entityManager->persist($produto);
            $entityManager->flush();

            $this->flashMessenger()->addMessage('Produto alterado com sucesso!');

            return $this->redirect()->toUrl('/Index');
        }

        return new ViewModel(['produto' => $produto]);
    }

    public function ContatoAction()
    {

        if ($this->request->isPost()) {

            $nome = $this->request->getPost('nome');
            $email = $this->request->getPost('email');
            $msg = $this->request->getPost('msg');

            $msgHtml = "
                <b>Nome:</b> {$nome}, <br>
                <b>E-mail:</b> {$email}, <br>
                <b>Mensagem:</b> {$msg}, <br>
            ";

            $htmlPart = new MimePart($msgHtml);
            $htmlPart->type = 'text/html';

            $htmlMsg = new MimeMessage();
            $htmlMsg->addPart($htmlPart);

            $email = new Message();
            $email->addTo('niksonbarth@gmail.com');
            $email->setSubject('Contato feito pelo site');
            $email->addFrom('niksonbarth@gmail.com');
            $email->setBody($htmlMsg);

            $config = array(
                'host' => 'smtp.gmail.com',
                'connection_class' => 'login',
                'connection_config' => array(
                    'ssl' => 'tls',
                    'username' => '',
                    'password' => ''
                ),
                'port' => 587
            );

            $transport = new SmtpTransport();
            $options = new SmtpOptions($config);
            $transport->setOptions($options);

            $transport->send($email);

            $this->flashMessenger()->addMessage('Email enviado com sucesso');

            return $this->redirect()->toUrl('/Index');
        }

        return new ViewModel();
    }
}