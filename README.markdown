## Services_Exceptional

Services_Exceptional is a wrapper for the API of http://getexceptional.com/.

### Installation

 * git clone git@github.com:till/exceptional-php.git
 * cd exceptional-php
 * pear install package.xml
 
### Usage is really simple

 * either `require_once 'Services/Exceptional.php`; or create an autoloader
 * code:
    <?php
    $exceptional = new Services_Exceptional('YOUR-API-KEY');
 * Done!