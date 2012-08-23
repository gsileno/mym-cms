<?php

  MyMinclude('core/MyMextention'); 

  // Plugin interface for mym
 
  class Highslide extends MyMextention {
    
    function htmlhead() {
      $urlpath = ROOT_URI."/".MYM_EXT_PATH."/".strtolower(__CLASS__);    
      $head = "";        
      
      ob_start();    

?>
<link rel="stylesheet" type="text/css" href="<?php print($urlpath); ?>/highslide.css" /> 
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="<?php print($urlpath); ?>/highslide-ie6.css" />
<![endif]-->  
<style type="text/css"> 
.highslide-dimming {
  background: black;
}
</style> 

<script type="text/javascript" src="<?php print($urlpath); ?>/highslide-with-gallery.js"></script> 
<script type="text/javascript"> 
hs.showCredits = 0;
 
hs.graphicsDir = '<?php print($urlpath); ?>/graphics/';
hs.align = 'center';
hs.transitions = ['expand', 'crossfade'];
hs.wrapperClassName = 'dark borderless floating-caption';
hs.fadeInOut = true;
hs.dimmingOpacity = .75;
 
// Add the controlbar
if (hs.addSlideshow) hs.addSlideshow ({
    interval: 5000,
    repeat: false,
    useControls: true,
    fixedControls: 'fit',
    overlayOptions: {
    opacity: .6,
    position: 'bottom center',
    hideOnMouseOut: true
  }
});
</script> 
<?php

       $head .= ob_get_contents();
       ob_end_clean();
        
       return $head;
    }
  
    function htmlcurrent() {
    }    
  
    function htmltop() {
    }    
    
    function htmlbottom() {
    }    

  }



?>


