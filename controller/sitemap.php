<?php

namespace Slrfw\App\Front\Controller;

use Slrfw\Library\Registry;

class Sitemap extends Main {

    private $_cache = null;

    public function start() {
        parent::start();
        $this->_cache = Registry::get("cache");
    }

    public function startAction() {
        global $pagesResult;
        $this->_view->main(false);


        $visible = TRUE;
        if (isset($_GET["visible"]) && $_GET["visible"] == 0) {
            $visible = FALSE;
        }


        $format = "xml";
        if (isset($_GET["json"]) && $_GET["json"] == 1) {
            $format = "json";
            $title = FALSE;
        }


        $this->_pages = array();


        $accueil = $this->_gabaritManager->getPage(ID_VERSION, ID_API, 1);
        $this->_pages[] = array(
            "title" => $accueil->getMeta("titre"),
            "visible" => $accueil->getMeta("visible"),
            "path" => '',
            "importance" => $accueil->getMeta("importance"),
            "lastmod" => substr($accueil->getMeta("date_modif"), 0, 10)
        );

        $this->_rubriques = $this->_gabaritManager->getList(ID_VERSION, ID_API, 0, array(6, 7, 8, 9, 10), $visible);


        foreach ($this->_rubriques as $ii => $rubrique) {
            $this->_pages[] = array(
                "title" => $rubrique->getMeta("titre"),
                "visible" => $rubrique->getMeta("visible"),
                "path" => $rubrique->getMeta('rewriting') . '.html',
                "importance" => $rubrique->getMeta('importance'),
                "lastmod" => substr($rubrique->getMeta('date_modif'), 0, 10)
            );




            $pages = $this->_gabaritManager->getList(ID_VERSION, ID_API, $rubrique->getMeta('id'), FALSE, $visible);



            $rubrique->setChildren($pages);
            foreach ($pages as $page) {
                $this->_pages[] = array(
                    "title" => $page->getMeta("titre"),
                    "visible" => $page->getMeta("visible"),
                    "path" => $rubrique->getMeta('rewriting') . '/' . $page->getMeta('rewriting') . '.html',
                    "importance" => $page->getMeta('importance'),
                    "lastmod" => substr($rubrique->getMeta("id_parent") == 8 ? $page->getMeta('date_crea') : $page->getMeta('date_modif'), 0, 10)
                );

                $pages = $this->_gabaritManager->getList(ID_VERSION, ID_API, $page->getMeta('id'), FALSE, $visible);


                $rubrique->setChildren($pages);
                foreach ($pages as $page) {
                    $this->_pages[] = array(
                        "title" => $page->getMeta("titre"),
                        "visible" => $page->getMeta("visible"),
                        "path" => $rubrique->getMeta('rewriting') . '/' . $page->getMeta('rewriting') . '.html',
                        "importance" => $page->getMeta('importance'),
                        "lastmod" => substr($rubrique->getMeta("id_parent") == 8 ? $page->getMeta('date_crea') : $page->getMeta('date_modif'), 0, 10)
                    );
                }
            }
        }

        if ($format == "xml")
            header("Content-Type: application/xml");
        $this->_view->pages = $this->_pages;

        if ($format == "json") {
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-type: application/json');

            $pages = $this->_pages;

            if (isset($_GET["term"]) && $_GET["term"] != "") {
                $term = $_GET["term"];
                $pagesResult = array();
                array_walk($pages, array($this, "filter"), $term);
                $pages = $pagesResult;
            }

            $this->_view->enable(false);

            foreach ($pages as $page) {
                $page["title"] = ($page["visible"] ? "&#10003;" : "&#10005;") . ' ' . $page["title"];
                if (isset($_GET["onlylink"]) && $_GET["onlylink"] == 1) {
                    $pagesClone[] = array(
                        $page["title"],
                        $page["path"],
                    );
                } else {
                    $pagesClone[] = $page;
                }
            }
            $pages = $pagesClone;
            echo json_encode($pages);
        }
    }

    function filter($page, $index, $searchString) {
        global $pagesResult;
        if (stripos($page["title"], $searchString) !== false) {
            $pagesResult[] = $page;
        }
    }

}

?>