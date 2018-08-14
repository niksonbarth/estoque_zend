<?php

namespace Estoque\View\Helper;

use Zend\View\Helper\AbstractHelper;

class PaginationHelper extends AbstractHelper {

    private $url;
    private $totalProdutos;
    private $qtdPorPagina;

    public function __invoke($produtos, $qtdPorPagina, $url)
    {
        $this->url = $url;
        $this->totalProdutos = $produtos->count();
        $this->qtdPorPagina = $qtdPorPagina;

        return $this->gerarPaginacao();
    }

    private function gerarPaginacao(){

        $totalPaginas = ceil($this->totalProdutos / $this->qtdPorPagina);

        if($totalPaginas == 1)
            return;

        $html = "<ul class=\"nav nav-pills\">";
        $count = 1;

        while ($count <= $totalPaginas){
            $html .= "<li><a href=\"{$this->url}/{$count}\">{$count}</a></li>";
            $count++;
        }

        $html .= "</ul>";

        return $html;
    }
}