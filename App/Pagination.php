<?php

namespace App;


class Pagination
{
    protected $element;
    protected $per_page;
    protected $current;
    protected $total;
    protected $range;

    /**
     * Pagination constructor.
     * @param array $items
     * @param int $per_page
     * @param int $current
     * @param int $total
     * @param int $range
     */

    public function __construct($per_page = 10, $current = 1, $total = 1, $range = 1)
    {
        $this->per_page = $per_page;
        $this->current = isset($_GET['page']) ? ((int) $_GET['page']) : $current;
        $this->total = $total;
        $this->range = $range;

        return $this;
    }

    /**
     * @param int $per_page
     * @return $this
     *
     * Устанавливает, сколько элементов будет отображаться на странице
     */

    public function setPerPage($per_page = 10)
    {
        $this->per_page = $per_page;

        return $this;
    }

    /**
     * @param int $total
     * @return $this
     *
     * Устанавливает общее количество элементов
     */

    public function setTotal($total = 1)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @param int $range
     * @return $this
     *
     * Устанавливает разрыв, по которому будут проставляться ...
     */

    public function setRange($range = 1)
    {
        $this->range = $range;

        return $this;
    }

    /**
     * @return int
     *
     * Возвращает текущий индекс
     */

    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * @return $this
     *
     * Устанавливает текущий индекс
     */

    public function setCurrent()
    {
        if(!is_null($_GET['page'])) {
            $this->current = (int) $_GET['page'];
        } else {
            $this->current = 1;
        }

        return $this;
    }

    /**
     * @return int
     *
     * Возвращает номер следующей страницы
     */

    public function nextPage()
    {
        return $this->getCurrent() < $this->totalPages() ? ($this->getCurrent()+1) : $this->getCurrent();
    }

    /**
     * @return int
     *
     * Возвращает номер предыдущей страницы
     */

    public function prevPage(){
        return $this->getCurrent() > 1 ? ($this->getCurrent()-1) : $this->getCurrent();
    }

    /**
     * @return int
     *
     * общее количество страниц
     */

    public function totalPages()
    {
        return (int) ceil($this->total / $this->per_page);
    }

    /**
     * @return bool
     *
     * Возвращает, есть ли у текущего индекса предыдущие
     */

    public function hasPrev()
    {
        return $this->current > 1;
    }

    /**
     * @return bool
     *
     * Возвращает, есть ли у текущего индекса следующие
     */

    public function hasNext()
    {
        return $this->current < $this->totalPages();
    }

    /**
     * @return int
     *
     * Возвращает разрыв между элементами в начале
     */

    public function rangeStart()
    {
        return ($this->current - $this->range) > 0 ? $this->current - $this->range : 1;
    }

    /**
     * @return int
     *
     * Возвращает разрыв между элементами в конце
     */

    public function rangeEnd(){
        return ($this->current + $this->range) < $this->totalPages() ? $this->current + $this->range : $this->totalPages();
    }

    /**
     * @return array
     *
     * Устанавливает нумерацию страниц
     */

    public function pages()
    {
        $pages = [];

        for($i = $this->rangeStart(); $i <= $this->rangeEnd(); $i++) {
            $pages[] = $i;
        }

        if(!count($pages)) {
            $pages[] = 1;
        }

        return $pages;
    }

    /**
     * @return bool
     *
     * Возвращает true, по которому скрываются <<
     */

    public function hasFirst()
    {
        return $this->rangeStart() !== 1;
    }

    /**
     * @return bool
     * Возвращает true, по которому скрываются >>
     */

    public function hasLast()
    {
        return $this->rangeEnd() < $this->totalPages();
    }

    /**
     * @return string
     *
     * Генерирует html-структуру для пагинации
     */

    public function html()
    {
        $html = '';
        $pages = $this->pages();
        $page = null;

        $html .= '<ul class="pagination">';

        if($this->hasPrev()) {
            $html .= '<li class="page-item prev"><a class="page-link" href="' . domain() . '/' . request()->uri() . '?page=' . $this->prevPage() . '">&#171;</a></li>';
        }

        if($this->hasFirst()) {
            $html .= '<li class="page-item ' . ($this->getCurrent() === 1 ? 'active' : '') . ' first"><a class="page-link" href="'. domain() . '/' . request()->uri() . '?page=1' .'">1</a></li>';

            if(($this->getCurrent() - $this->range) > 2) {
                $html .= '<li><a class="page-link" href="javascript:void(0);">' . '...' . '</a></li>';
            }
        }

        for($i = 0; $i < count($pages); $i++) {
            $page = $pages[$i];

            $html .= '<li class="page-item ' . ($this->getCurrent() === $page ? 'active' : '') . ' first"><a class="page-link" href="'. domain() . '/' . request()->uri() . '?page=' . $page . '">' . $page . '</a></li>';
        }

        if($this->hasLast()) {
            if((1 + ($this->getCurrent() + $this->range)) < $this->totalPages()) {
                $html .= '<li><a class="page-link" href="javascript:void(0);">' . '...' . '</a></li>';
            }

            $html .= '<li class="page-item ' . ($this->getCurrent() === $this->totalPages() ? 'active' : '') . ' last"><a class="page-link" href="'. domain() . '/' . request()->uri() . '?page=' . $this->totalPages() . '">' . $this->totalPages() . '</a></li>';
        }

        if($this->hasNext()) {
            $html .= '<li class="page-item next"><a class="page-link" href="'. domain() . '/' . request()->uri() .'?page=' . $this->nextPage() . '">&#187;</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }
}