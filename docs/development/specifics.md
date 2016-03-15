**Development information specific to the project**

The development of ZCMS follows the typical Zend Framework 2 project framework. Never-the-less there are some fiew differences specific to the project that are outlined below.

- In the view files you have to use the view helper `$this->langUrl()` instead of the Zend Framework's `$this->url()` in order to preserve the navigation in the currenlty chosen language.  
  
This page will continue to be updated with the latest specifics that has to be taken into account while extending ZCMS.