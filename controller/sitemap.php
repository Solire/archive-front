<?php

namespace App\Front\Controller;

use Slrfw\Registry;

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
        
        //Si ids = *, on recupere tous les gabarits de niveau 0
        if($this->_appConfig->get('sitemap', 'ids') == "*") {
            $categoryIds = $this->_db->query("  
                SELECT *
                FROM `gab_gabarit`
                WHERE id <> 1 AND id <> 2
                    AND id_parent = 0")->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            $categoryIds = explode(',', $this->_appConfig->get('sitemap', 'ids'));
        }
        
        //On recupere les gabarits 
        $gabarits = $this->_db->query("  
                SELECT `gab_gabarit`.id, `gab_gabarit`.*
                FROM `gab_gabarit`
                WHERE id <> 1 AND id <> 2")->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
        
        $this->_rubriques = $this->_gabaritManager->getList(ID_VERSION, ID_API, 0, $categoryIds, $visible);

        //GABARIT NIVEAU 0
        foreach ($this->_rubriques as $ii => $rubrique) {
            if($gabarits[$rubrique->getMeta("id_gabarit")]["view"]
                    && $rubrique->getMeta("no_index") == 0
            ){
                $this->_pages[] = array(
                    "title" => $rubrique->getMeta("titre"),
                    "visible" => $rubrique->getMeta("visible"),
                    "path" => $rubrique->getMeta('rewriting') . $gabarits[$rubrique->getMeta("id_gabarit")]["extension"],
                    "importance" => $rubrique->getMeta('importance'),
                    "lastmod" => substr($rubrique->getMeta('date_modif'), 0, 10)
                );
            }

            //Récupération des enfants
            $pages = $this->_gabaritManager->getList(ID_VERSION, ID_API, $rubrique->getMeta('id'), FALSE, $visible);
            $rubrique->setChildren($pages);
            
            //GABARIT NIVEAU 1
            foreach ($pages as $page) {
                if($gabarits[$page->getMeta("id_gabarit")]["view"]
                        && $page->getMeta("no_index") == 0
                ){
                    $this->_pages[] = array(
                        "title" => $page->getMeta("titre"),
                        "visible" => $page->getMeta("visible"),
                        "path" => $rubrique->getMeta('rewriting') . '/' . $page->getMeta('rewriting') . $gabarits[$page->getMeta("id_gabarit")]["extension"],
                        "importance" => $page->getMeta('importance'),
                        "lastmod" => substr($page->getMeta('date_modif'), 0, 10)
                    );
                }
                
                //Récupération des enfants
                $sspages = $this->_gabaritManager->getList(ID_VERSION, ID_API, $page->getMeta('id'), FALSE, $visible);
                $page->setChildren($sspages);
                
                //GABARIT NIVEAU 2
                foreach ($sspages as $sspage) {
                    if($gabarits[$sspage->getMeta("id_gabarit")]["view"]
                            && $sspage->getMeta("no_index") == 0
                    ){
                        $this->_pages[] = array(
                            "title" => $sspage->getMeta("titre"),
                            "visible" => $sspage->getMeta("visible"),
                            "path" => $rubrique->getMeta('rewriting') . '/' . $page->getMeta('rewriting') . '/' . $sspage->getMeta('rewriting') . $gabarits[$sspage->getMeta("id_gabarit")]["extension"],
                            "importance" => $sspage->getMeta('importance'),
                            "lastmod" => substr($sspage->getMeta('date_modif'), 0, 10)
                        );
                    }
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
            
            $json = json_encode($pages);
            if (isset($_GET['tinymce'])) {
                echo 'var tinyMCELinkList = ' . $json . ';';
            } else {
                echo $json;
            }
        }
    }

    function filter($page, $index, $searchString) {
        global $pagesResult;
        if (stripos($page["title"], $searchString) !== false) {
            $pagesResult[] = $page;
        }
    }

}

