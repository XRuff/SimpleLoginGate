Simple Login Gate
======

Requirements
------------

Package requires PHP 7.0 or higher

- [nette/utils](https://github.com/nette/utils): "^3.0",
- [nette/security](https://github.com/nette/security): "^3.0",
- [nette/forms](https://github.com/nette/forms): "^3.0",
- [nette/application](https://github.com/nette/application): "^3.0",
- [nette/di](https://github.com/nette/di): "^3.0",
- [xruff/basedbmodel](https://github.com/xruff/basedbmodel): "^v3.0",
- [xruff/apputils](https://github.com/xruff/apputils): "^v3.0",
- [ramsey/uuid](https://github.com/ramsey/uuid): "^4.0",
- [tracy/tracy](https://github.com/tracy/tracy): "^2.7"


Installation
------------

The best way to install XRuff/SimpleLoginGate is using  [Composer](http://getcomposer.org/):

```sh
$ composer require xruff/simplelogingate
```

or add package to composer.json file

```json
{
    "require": {
        "xruff/simplelogingate": "^1.0"
    }
}

```


Documentation
------------

Register configuration in config.neon.

Config has two optional parameters - `nofityEmail` and `userManager`.

```yml
extensions:
    simpleLoginGate: XRuff\SimpleLoginGate\DI\SimpleLoginGateExtension

# and optional settings for custom templates
simpleLoginGate:
	#breadcrumbsTemplate: %appDir%/components/breadcrumbs.latte
	nofityEmail: igor@webengine.cz
	userManager: 'App\Model\UserManager'(..., @userIdentity)
	registrationFields:
		1:
			- firstname
			- username
			- password

```


Repository [https://github.com/XRuff/simplelogingate](https://github.com/XRuff/simplelogingate).