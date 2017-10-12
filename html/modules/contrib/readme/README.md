
Table of Contents
-----------------

* Introduction
* Features
* Installation
* References
* Demo


Introduction
------------

Allows site builders and administrator to view a module's README file.


Features
--------

- Adds a link to a module's README file on Drupal's Extend page (/admin/modules)

- Converts a module's README.md (Markdown) file to HTML.
    
- Provides a `drush readme-export` command that converts a contrib module's 
  README.md (MarkDown) file to clean HTML that can be uploaded to the 
  contrib module's project page.

- Allows external applications to access a module's README file using a secure
  tokened URL.

Installation
------------

For MarkDown support you must either....

- Use [Composer Manager](https://www.drupal.org/project/composer_manager) 
  to install the PHP Markdown package.

- Manually, place the contents from <https://github.com/michelf/php-markdown> 
  into /libraries/markdown.


References
----------

- [Allow README.md to optionally render a project page](https://www.drupal.org/node/1674976)
- [README Template](https://www.drupal.org/node/2181737)


Author/Maintainer
-----------------

- [Jacob Rockowitz](http://drupal.org/user/371407)
