# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.2.1 - 2019-03-05

### Added

- [#33](https://github.com/zendframework/zend-expressive-session/pull/33) adds support for PHP 7.3.

### Changed

- [#34](https://github.com/zendframework/zend-expressive-session/pull/34) provides several performance optimizations in `Zend\Expressives\Session\LazySession`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.0 - 2018-10-30

### Added

- [#28](https://github.com/zendframework/zend-expressive-session/pull/28) adds a new interface, `SessionCookiePersistenceInterface`, defining:
  - the constant `SESSION_LIFETIME_KEY`
  - the method `persistSessionFor(int $duration) : void`, for developers to hint
    to the persistence engine how long a session should last
  - the method `getSessionLifetime() : int`, for persistence engines to
    determine if a specific session duration was requested

### Changed

- [#28](https://github.com/zendframework/zend-expressive-session/pull/28) updates both `Session` and `LazySession` to implement the new
  `SessionCookiePersistenceInterface.  If a `SessionCookiePersistenceInterface::SESSION_LIFETIME_KEY`
  is present in the initial session data provided to a `Session` instance, this
  value will be used to indicate the requested session duration; otherwise, zero
  is used, indicating the session should end when the browser is closed.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.0 - 2018-09-12

### Added

- [#27](https://github.com/zendframework/zend-expressive-session/pull/27) adds a new interface, `Zend\Expressive\Session\SessionIdentifierAwareInterface`.
  `SessionInterface` implementations should also implement this interface, and
  persistence implementations should only create and consume session
  implementations that implement it. The interface defines a single method,
  `getId()`, representing the identifier of a discovered session. This allows
  the identifier to be associated with its session data, ensuring that when
  concurrent requests are made, persistence operates on the correct identifier.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.0 - 2018-03-15

### Added

- [#18](https://github.com/zendframework/zend-expressive-session/pull/18) adds
  support for PSR-15 middleware.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#14](https://github.com/zendframework/zend-expressive-session/pull/14) and
  [#18](https://github.com/zendframework/zend-expressive-session/pull/18) remove
  support for http-interop/http-middleware and http-interop/http-server-middleware.

- [#5](https://github.com/zendframework/zend-expressive-session/pull/5) removes
  the method `LazySession::segment()`. This method was a remnant from a previous
  refactor, and not intended for the final API. Considering that `Session` does
  not implement the method, calling it would lead to a fatal error anyways.

### Fixed

- Nothing.

## 0.1.0 - 2017-10-10

Initial release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
