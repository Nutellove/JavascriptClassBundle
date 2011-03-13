Provides a _very_ basic Javascript-ORM-like-kinda-thing for your Symfony2 Project.

Features
========

- Generated Javascript (Mootools) Classes for painless XHR sync
- Uses YAML Doctrine Schema => easy configuration
- Unit tested and functionally tested (bazinga ! welcome to hell...)

Installation
============

    git submodule add https://github.com/Nutellove/JavascriptClassBundle.git src/Nutellove/JavascriptClassBundle

Configuration
=============

In your yaml Doctrine metadata configuration, use the options/js field attribute, as in the example for the field name :

    fields:
      name:
        type: string
        length: 50
        options:
          js: read

Possible values for `js` are :

* for reading only :
  * read
  * r
* for writing only :
  * write
  * w
* for reading and writing :
  * readwrite
  * rw

Note that the ID field is always automatically read-only.

The generated javascript classes will provide accessors and mutators according to the above configuration.
In this example, the `getName()` method will be defined but not the `setName(value)`.