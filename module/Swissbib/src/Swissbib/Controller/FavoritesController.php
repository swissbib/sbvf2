<?php
namespace Swissbib\Controller;

use Zend\View\Model\ViewModel;

use VuFind\Controller\AbstractBase as BaseController;
use VuFind\Record\Loader as RecordLoader;

use Swissbib\RecordDriver\SolrMarc;

/**
 * Serve holdings data (items and holdings) for solr records over ajax
 *
 */
class FavoritesController extends BaseController
{

	/**
	 * show list of already defined favorites
	 *
	 * @return ViewModel
	 */
	public function displayAction()
	{
		$viewModel	 = new ViewModel();



        //todo: besseres sessionhandling
        //ich verwende hier den object cache, der im filesystem gespeichert wird,
        //wir brauchen aber den user cache
        //wie bekomme ich den? Ich benötge gerade zviel Zeit dies nachzuschauen. - Merc!



        //facetquery   ->>> facet.query=institution:z01



        $cache = $this->getServiceLocator()->get('VuFind\CacheManager')
            ->getCache('object');

        if (!($results = $cache->getItem('favoriteInstitutions'))) {

            //format: library.id ## library.name ## library.identifier ## library.road ## library.zipCode ## library.town
            //don't frorget to escape "
            // if I'm not wrong library.id might be an internal unique identifier -> so I guess we could choose library.identifier as well

            $testInstitutions = array (
                                "3316@@Uni Zürich - Romanisches Seminar (UROSE) Zürichbergstrasse 8 8032 Zürich",
                                "2938@@Uni Zürich - Slavisches Seminar (Z18) Plattenstrasse 43 8032 Zürich",
                                "3032@@Uni Zürich - Soziologisches Institut (USIUZ) Andreasstrasse 15 8050 Zürich",
                                "3352@@Uni Zürich - Sprachenzentrum  (USUEZ) Rämistrasse 71 8006 Zürich",
                                "3368@@Uni Basel - Studienberatung (A366) Steinengraben 5 4051 Basel",
                                "3143@@Uni Basel - Theologische Fakultät (A252) Nadelberg 10 4051 Basel",
                                "3566@@Uni Basel - UB Hauptbibliothek (A100) Schönbeinstr. 18-20 4056 Basel",
                                "3531@@Uni Basel - UB Medizin (A140) Spiegelgasse 5 4051 Basel",
                                "3105@@Uni Basel - Unikliniken für Zahnmedizin (A260) Hebelstrasse 3 4056 Basel",
                                "3313@@Uni Zürich - Sprachenzentrum - SLZ (USLZ) Rämistrasse 74 J 15 8006 Zürich");

            //-> the real values should be a subset of our libadmin data


            $cache->setItem("favoriteInstitutions","loaded");
            $viewModel->setVariable("loadFavoriteInstitutions",$testInstitutions);


        }


        return $viewModel;

	}

}
