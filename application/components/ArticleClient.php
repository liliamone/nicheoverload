<?php

namespace TooMuchNiche\application\components;

defined('\ABSPATH') || exit;

use TooMuchNiche\application\Plugin;
use TooMuchNiche\application\admin\PluginAdmin;
use TooMuchNiche\application\admin\LicConfig;

use function TooMuchNiche\prnx;

/**
 * ArticleClient class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2025 keywordrush.com
 */
class ArticleClient
{
    private $data = array();

    public function requestData()
    {
        $this->data = array();

        if (!$data = NicheApi::get('/article'))
            return false;

        $this->data = $data;

        return $this->data;
    }

    public function getArticle()
    {
        if (!empty($this->data['article']))
            return $this->data['article'];
        else
            return false;
    }

    public function getComments()
    {
        if (!empty($this->data['article']['comments']))
            return $this->data['article']['comments'];
        else
            return array();
    }

    public function getTags()
    {
        if (!empty($this->data['article']['tags']))
            return $this->data['article']['tags'];
        else
            return array();
    }

    public function getSlug()
    {
        if (!empty($this->data['article']['slug']))
            return $this->data['article']['slug'];
        else
            return '';
    }

    public function getStat()
    {
        if (!empty($this->data['stat']))
            return $this->data['stat'];
        else
            return false;
    }

    public function getStatus()
    {
        if (!empty($this->data['status']))
            return $this->data['status'];
        else
            return false;
    }
}
