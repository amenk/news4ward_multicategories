<?php

/**
 * News4ward
 * a contentelement driven news/blog-system
 *
 * @author Christoph Wiechert <wio@psitrax.de>
 * @copyright 4ward.media GbR <http://www.4wardmedia.de>
 * @package news4ward_multicategories
 * @filesource
 * @licence LGPL
 */

namespace Psi\News4ward\Module;

class Multicategories extends Module
{

  /**
   * Template
   *
   * @var string
   */
  protected $strTemplate = 'mod_news4ward_multicategories';


  /**
   * Display a wildcard in the back end
   *
   * @return string
   */
  public function generate()
  {
    if(TL_MODE == 'BE') {
      $objTemplate = new \BackendTemplate('be_wildcard');

      $objTemplate->wildcard = '### News4ward Multicategories ###';
      $objTemplate->title = $this->headline;
      $objTemplate->id = $this->id;
      $objTemplate->link = $this->name;
      $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

      return $objTemplate->parse();
    }

    $this->news_archives = $this->sortOutProtected(deserialize($this->news4ward_archives));

    // Return if there are no archives
    if(!is_array($this->news_archives) || count($this->news_archives) < 1) {
      return '';
    }

    $strBuffer = parent::generate();

    if(count($this->Template->categories) == 0) {
      return '';
    }

    return $strBuffer;
  }


  /**
   * Generate module
   */
  protected function compile()
  {
    $objCats = $this->Database->execute('SELECT categories FROM tl_news4ward_article
                                         WHERE tl_news4ward_article.pid IN ('.implode(',', $this->news_archives).') AND categories <> ""');

    // just return if on empty result
    if(!$objCats->numRows) {
      $this->Template->categories = array();
      return;
    }

    // get jumpTo page
    $jumpTo = $GLOBALS['objPage']->row();
    if($this->jumpTo) {
      $objJumpTo = $this->Database->prepare('SELECT id,alias FROM tl_page WHERE id=?')->execute($this->jumpTo);
      if($jumpTo->numRows) {
        $jumpTo = $objJumpTo->row();
      }
    }

    $cats = array();
    while($objCats->next()) {
      $cats = array_merge($cats, deserialize($objCats->categories, true));
    }
    $cats = array_unique($cats);
    natcasesort($cats);

    $arrCats = array();
    foreach($cats as $cat) {
      $arr = array('category' => $cat);
      $arr['href'] = $this->generateFrontendUrl($jumpTo, '/cat/'.urlencode($cat));
      $arr['active'] = ($this->Input->get('cat') == $cat);

      // set active item for the active filter hinting
      if($this->Input->get('cat') == $cat) {
        if(!isset($GLOBALS['news4ward_filter_hint'])) {
          $GLOBALS['news4ward_filter_hint'] = array();
        }

        $GLOBALS['news4ward_filter_hint']['category'] = array
        (
          'hint'  => $this->news4ward_filterHint,
          'value' => $cat
        );
      }
      $arrCats[] = $arr;
    }

    $this->Template->categories = $arrCats;
  }


}
