<?php
/*
Plugin Name: Vimeography Theme: Harvestone
Plugin URI: https://vimeography.com/themes
Theme Name: Harvestone
Theme URI: https://vimeography.com/themes/harvestone
Version: 2.1
Description: Harvestone is the base gallery theme that comes prepackaged with Vimeography.
Author: Dave Kiss
Author URI: https://vimeography.com
Copyright: Dave Kiss
*/

if ( ! class_exists('Vimeography_Themes_Harvestone') ) {

  class Vimeography_Themes_Harvestone {

    /**
     * The current version of this theme
     *
     * @var string
     */
    public $version = '2.1';


    /**
     * Include this theme in the Vimeography theme loader.
     */
    public function __construct() {
      add_action('plugins_loaded', array( $this, 'load_theme' ) );
      add_action('wp_footer', array( $this, 'print_link_href_override' ), 99);
    }


    /**
     * Has to be public so the wp actions can reach it.
     * @return [type] [description]
     */
    public function load_theme() {
      do_action('vimeography/load-addon-plugin', __FILE__);
    }


    /**
     * Force every <a class="vimeography-link"> inside a Harvestone gallery
     * to display href="#" instead of the gallery/video query URL.
     *
     * The original href is preserved on a data-vimeography-href attribute
     * for debugging / extensibility. The click handler attached by
     * vue-router is unaffected — it calls event.preventDefault() before
     * the browser would follow the "#" anchor, so SPA navigation between
     * videos continues to work normally.
     *
     * A MutationObserver re-applies the rewrite when Vue re-renders the
     * gallery (reactivity, swiper slide changes, route updates).
     *
     * Scoped to `.vimeography-theme-harvestone` so other Vimeography
     * themes on the same page are not affected.
     */
    public function print_link_href_override() {
      ?>
<script>
(function () {
  var SELECTOR = '.vimeography-theme-harvestone a.vimeography-link';

  function rewrite(a) {
    if (!a || a.getAttribute('href') === '#') return;
    var current = a.getAttribute('href');
    if (current && current !== '#') {
      a.setAttribute('data-vimeography-href', current);
    }
    a.setAttribute('href', '#');
  }

  function scan(root) {
    if (!root || !root.querySelectorAll) return;
    var links = root.querySelectorAll(SELECTOR);
    for (var i = 0; i < links.length; i++) rewrite(links[i]);
  }

  function matches(el) {
    return el && el.matches && el.matches(SELECTOR);
  }

  function init() {
    scan(document);

    if (typeof MutationObserver === 'undefined') return;

    var observer = new MutationObserver(function (mutations) {
      for (var i = 0; i < mutations.length; i++) {
        var m = mutations[i];
        if (m.type === 'attributes' && matches(m.target)) {
          rewrite(m.target);
          continue;
        }
        for (var j = 0; j < m.addedNodes.length; j++) {
          var n = m.addedNodes[j];
          if (n.nodeType !== 1) continue;
          if (matches(n)) rewrite(n);
          scan(n);
        }
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['href']
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>
      <?php
    }

  }

  new Vimeography_Themes_Harvestone;
}