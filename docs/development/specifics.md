**Development information specific to the project**

The development of ZCMS follows the typical Zend Framework 3 project framework. Never-the-less there are some fiew differences specific to the project that are outlined below.
  
In order to preserve the navigation in the currently chosen language:

- In the view files you have to use the view helper `$this->langUrl()` instead of the Zend Framework's `$this->url()`
- In the controllers use `$this->redir()` instead of `$this->redirect()` using it in the same fashion.
- In the view files there is an additional view helper `$this->corePath()` pointing to the `public_html/core` folder
  
This page will continue to be updated with the latest specifics that has to be taken into account while extending ZCMS.