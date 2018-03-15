# zend-expressive-session

[![Build Status](https://secure.travis-ci.org/zendframework/zend-expressive-session.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-expressive-session)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-expressive-session/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-expressive-session?branch=master)

This library provides session handling middleware for PSR-7 applications, using
an adapter-based approach that will allow usage of ext-session, JWT, or other
approaches.

## Installation

Run the following to install this library:

```bash
$ composer require zendframework/zend-expressive-session
```

However, the package is not immediately useful unless you have a persistence
adapter. If you are okay with using ext-session, you can install the following
package as well:

```bash
$ composer require zendframework/zend-expressive-session-ext
```

## Documentation

Documentation is [in the doc tree](docs/book/), and can be compiled using [mkdocs](http://www.mkdocs.org):

```bash
$ mkdocs build
```

You may also [browse the documentation online](https://docs.zendframework.com/zend-expressive-session/).
